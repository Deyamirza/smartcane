<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\SensorLog;
use App\Models\GpsLog;
use App\Models\SosEvent;
use App\Models\Device;

class RealtimeController extends Controller
{
    public function stream(Request $request)
    {
        $lastSensorId = (int) $request->query('lastSensorId', 0);
        $lastGpsId = (int) $request->query('lastGpsId', 0);
        $lastSosId = (int) $request->query('lastSosId', 0);

        $payload = [];

        $device = Device::where('status', 'active')->first();
        if ($device) {
            // 1. Check for new Sensor Log
            $newSensor = SensorLog::where('id_device', $device->id_device)
                ->where('id_sensor', '>', $lastSensorId)
                ->orderBy('id_sensor', 'asc')
                ->first();
            if ($newSensor) {
                $lastSensorId = $newSensor->id_sensor;
                $payload['sensor'] = [
                    'id_sensor' => $newSensor->id_sensor,
                    'distance_cm' => $newSensor->distance_cm,
                    'obstacle_detected' => $newSensor->obstacle_detected,
                    'recorded_at' => $newSensor->recorded_at,
                ];
            }

            // 2. Check for new GPS Log
            $newGps = GpsLog::where('id_device', $device->id_device)
                ->where('id_gps', '>', $lastGpsId)
                ->orderBy('id_gps', 'asc')
                ->first();
            if ($newGps) {
                $lastGpsId = $newGps->id_gps;
                $payload['gps'] = [
                    'id_gps' => $newGps->id_gps,
                    'latitude' => $newGps->latitude,
                    'longitude' => $newGps->longitude,
                    'recorded_at' => $newGps->recorded_at,
                ];
            }

            // 3. Check for new SOS Event
            $newSos = SosEvent::where('id_device', $device->id_device)
                ->where('id_sos', '>', $lastSosId)
                ->orderBy('id_sos', 'asc')
                ->first();
            if ($newSos) {
                $lastSosId = $newSos->id_sos;
                $payload['sos'] = [
                    'id_sos' => $newSos->id_sos,
                    'latitude' => $newSos->latitude,
                    'longitude' => $newSos->longitude,
                    'status' => $newSos->status,
                    'triggered_at' => $newSos->triggered_at,
                ];
            }

            $latestSensor = SensorLog::where('id_device', $device->id_device)->orderBy('recorded_at', 'desc')->first();
            $latestGps = GpsLog::where('id_device', $device->id_device)->orderBy('recorded_at', 'desc')->first();
            $latestSos = SosEvent::where('id_device', $device->id_device)->orderBy('triggered_at', 'desc')->first();
            
            // Calculate device connection state (online if active in last 60 seconds)
            $isOnline = false;
            if ($latestSensor || $latestGps) {
                $lastTime = max(
                    $latestSensor ? strtotime($latestSensor->recorded_at) : 0,
                    $latestGps ? strtotime($latestGps->recorded_at) : 0
                );
                if ((time() - $lastTime) < 60) {
                    $isOnline = true;
                }
            }

            $payload['currentState'] = [
                'distance' => $latestSensor ? $latestSensor->distance_cm : 'N/A',
                'obstacle' => $latestSensor ? $latestSensor->obstacle_detected : 'no',
                'latitude' => $latestGps ? $latestGps->latitude : -6.200000,
                'longitude' => $latestGps ? $latestGps->longitude : 106.816666,
                'sos_status' => ($latestSos && $latestSos->status === 'active') ? 'Aktif' : 'Tidak Aktif',
                'sos_id' => $latestSos ? $latestSos->id_sos : null,
                'is_online' => $isOnline,
                'time' => $latestSensor ? date('H:i:s', strtotime($latestSensor->recorded_at)) : ($latestGps ? date('H:i:s', strtotime($latestGps->recorded_at)) : 'N/A'),
                'date' => $latestSensor ? date('d M Y', strtotime($latestSensor->recorded_at)) : ($latestGps ? date('d M Y', strtotime($latestGps->recorded_at)) : 'N/A'),
            ];
        } else {
            // Default offline state if no active device is selected
            $payload['currentState'] = [
                'distance' => 'N/A',
                'obstacle' => 'no',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'sos_status' => 'Tidak Aktif',
                'sos_id' => null,
                'is_online' => false,
                'time' => 'N/A',
                'date' => 'N/A',
            ];
        }

        return response()->json([
            'lastSensorId' => $lastSensorId,
            'lastGpsId' => $lastGpsId,
            'lastSosId' => $lastSosId,
            'data' => $payload
        ]);
    }
}
