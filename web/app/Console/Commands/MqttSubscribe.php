<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\Device;
use App\Models\SensorLog;
use App\Models\GpsLog;
use App\Models\SosEvent;
use App\Models\Notification;
use App\Models\User;
use Exception;

class MqttSubscribe extends Command
{
    protected $signature = 'mqtt:subscribe';
    protected $description = 'Subscribe to MQTT broker for Smart Cane sensor and GPS data';

    public function handle()
    {
        $server   = env('MQTT_HOST', '127.0.0.1');
        $port     = (int) env('MQTT_PORT', 1883);

        while (true) {
            $clientId = 'laravel_smartcane_client_' . uniqid();
            $this->info("Connecting to MQTT broker {$server}:{$port}...");

            try {
                $mqtt = new MqttClient($server, $port, $clientId);

                $settings = (new ConnectionSettings)
                    ->setKeepAliveInterval(60)
                    ->setLastWillTopic('laravel/status')
                    ->setLastWillMessage('client_disconnected')
                    ->setLastWillQualityOfService(0);

                $mqtt->connect($settings, true);
                $this->info("Connected successfully! Subscribing to topics...");

                // Subscribe to Alerts Topic
                $mqtt->subscribe('esp32/tracker/alerts', function ($topic, $message) {
                    $this->info("Received alert on topic [{$topic}]: {$message}");
                    $this->handleAlertMessage($message);
                }, 0);

                // Subscribe to GPS Topic
                $mqtt->subscribe('esp32/tracker/gps', function ($topic, $message) {
                    $this->info("Received GPS on topic [{$topic}]: {$message}");
                    $this->handleGpsMessage($message);
                }, 0);

                // Let's loop forever to listen for messages
                $mqtt->loop(true);

            } catch (Exception $e) {
                $this->error("MQTT Error: " . $e->getMessage());
                $this->info("Retrying connection in 5 seconds...");
                sleep(5);
            }
        }
    }

    private function getActiveDevice()
    {
        $device = Device::where('status', 'active')->first();
        if (!$device) {
            $device = Device::first();
        }
        return $device;
    }

    private function handleAlertMessage($message)
    {
        $data = json_decode($message, true);
        if (!$data || !isset($data['status'])) {
            return;
        }

        $device = $this->getActiveDevice();
        if (!$device) {
            $this->warn("No active device to associate the alert with.");
            return;
        }

        $status = $data['status'];

        if ($status === 'SOS_ACTIVE') {
            // Get latest GPS position from logs if available
            $latestGps = GpsLog::where('id_device', $device->id_device)
                ->orderBy('recorded_at', 'desc')
                ->first();

            $lat = $latestGps ? $latestGps->latitude : -6.200000;
            $lng = $latestGps ? $latestGps->longitude : 106.816666;

            // Create SOS event
            $sos = SosEvent::create([
                'id_device' => $device->id_device,
                'latitude' => $lat,
                'longitude' => $lng,
                'status' => 'active',
                'triggered_at' => now(),
            ]);

            // Send actual Telegram Alert
            $messageId = \App\Services\TelegramService::sendSosAlert($device, $lat, $lng, $sos->id_sos);
            if ($messageId) {
                $sos->update(['telegram_message_id' => $messageId]);
            }

            // Try sending a Telegram notification log
            $users = User::all();
            foreach ($users as $user) {
                Notification::create([
                    'id_sos' => $sos->id_sos,
                    'id_user' => $user->id_user,
                    'telegram_chat_id' => env('TELEGRAM_CHAT_ID', '123456789'),
                    'delivery_status' => $messageId ? 'sent' : 'failed',
                    'sent_at' => now(),
                ]);
            }
            $this->info("Saved SOS event and notifications in database.");
        } 
        elseif ($status === 'SOS_DEACTIVATED') {
            // Resolve all active SOS events
            $activeSosEvents = SosEvent::where('id_device', $device->id_device)
                ->where('status', 'active')
                ->get();

            foreach ($activeSosEvents as $sos) {
                \App\Services\TelegramService::resolveSosAlert($device, $sos);
                $sos->update([
                    'status' => 'resolved',
                    'resolved_at' => now()
                ]);
            }
            $this->info("Marked active SOS events as resolved.");
        } 
        elseif ($status === 'PROXIMITY_ALARM' || $status === 'DISTANCE_UPDATE') {
            $distance = isset($data['distance']) ? $data['distance'] : 0;
            $obstacle = ($distance > 0 && $distance <= 100) ? 'yes' : 'no';
            SensorLog::create([
                'id_device' => $device->id_device,
                'distance_cm' => $distance,
                'obstacle_detected' => $obstacle,
                'recorded_at' => now(),
            ]);
            $this->info("Saved sensor log (distance: {$distance}cm, obstacle: {$obstacle}).");
        }
    }

    private function handleGpsMessage($message)
    {
        $data = json_decode($message, true);
        if (!$data || !isset($data['lat']) || !isset($data['lng'])) {
            return;
        }

        $device = $this->getActiveDevice();
        if (!$device) {
            $this->warn("No active device to associate the GPS with.");
            return;
        }

        GpsLog::create([
            'id_device' => $device->id_device,
            'latitude' => $data['lat'],
            'longitude' => $data['lng'],
            'accuracy_m' => 3.0, // Default accuracy
            'recorded_at' => now(),
        ]);
        $this->info("Saved GPS log (lat: {$data['lat']}, lng: {$data['lng']}).");
    }
}
