<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\SensorLog;
use App\Models\GpsLog;
use App\Models\SosEvent;
use App\Models\Notification;
use App\Models\User;

class ApiController extends Controller
{
    public function logData(Request $request)
    {
        $validated = $request->validate([
            'mac_address' => 'required|string',
            'distance_cm' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'sos_status' => 'nullable|string|in:active,inactive',
        ]);

        // Find device by MAC address
        $device = Device::where('mac_address', $validated['mac_address'])->first();
        if (!$device) {
            // Fallback to the first available active device
            $device = Device::first();
            if (!$device) {
                return response()->json(['error' => 'Device not found and no default device exists.'], 404);
            }
        }

        $response = ['status' => 'success', 'logged' => []];

        // 1. Log Distance Sensor Data
        if ($request->has('distance_cm') && $validated['distance_cm'] !== null) {
            $dist = $validated['distance_cm'];
            $obstacle = ($dist > 0 && $dist <= 100) ? 'yes' : 'no';
            
            SensorLog::create([
                'id_device' => $device->id_device,
                'distance_cm' => $dist,
                'obstacle_detected' => $obstacle,
                'recorded_at' => now(),
            ]);
            $response['logged'][] = 'sensor_log';
        }

        // 2. Log GPS Coordinates
        if ($request->has('latitude') && $request->has('longitude') && 
            $validated['latitude'] !== null && $validated['longitude'] !== null) {
            
            GpsLog::create([
                'id_device' => $device->id_device,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'accuracy_m' => 3.0,
                'recorded_at' => now(),
            ]);
            $response['logged'][] = 'gps_log';
        }

        // 3. Log SOS Event
        if ($request->has('sos_status')) {
            if ($validated['sos_status'] === 'active') {
                // Check if there is already an active SOS
                $activeSosExists = SosEvent::where('id_device', $device->id_device)
                    ->where('status', 'active')
                    ->exists();

                if (!$activeSosExists) {
                    $lat = $validated['latitude'] ?? ($device->gpsLogs()->orderBy('recorded_at', 'desc')->value('latitude') ?? -6.200000);
                    $lng = $validated['longitude'] ?? ($device->gpsLogs()->orderBy('recorded_at', 'desc')->value('longitude') ?? 106.816666);

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

                    // Create log notifications
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
                    $response['logged'][] = 'sos_activated';
                }
            } else {
                // Resolve active SOS events
                $activeSosEvents = SosEvent::where('id_device', $device->id_device)
                    ->where('status', 'active')
                    ->get();

                foreach ($activeSosEvents as $sos) {
                    \App\Services\TelegramService::resolveSosAlert($device, $sos);
                    $sos->update([
                        'status' => 'resolved',
                        'resolved_at' => now(),
                    ]);
                }
                $response['logged'][] = 'sos_deactivated';
            }
        }

        return response()->json($response);
    }
}
