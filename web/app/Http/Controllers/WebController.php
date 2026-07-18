<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Device;
use App\Models\SensorLog;
use App\Models\GpsLog;
use App\Models\SosEvent;

class WebController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('login');
    }

    /**
     * Handle authentication.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Sesuai schema, password disimpan di password_hash.
        // Laravel Auth menggunakan username/email. Kita akan cari user manual
        $user = User::where('username', $credentials['username'])->first();

        if ($user && Hash::check($credentials['password'], $user->password_hash)) {
            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'username' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('username');
    }

    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('register');
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:admin,family',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Akun berhasil dibuat!');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Show the monitoring dashboard.
     */
    public function dashboard()
    {
        $device = Device::where('status', 'active')->first();
        if (!$device) {
            $device = Device::first();
        }

        // Get recent states
        $latestSensor = $device ? SensorLog::where('id_device', $device->id_device)->orderBy('recorded_at', 'desc')->first() : null;
        $latestGps = $device ? GpsLog::where('id_device', $device->id_device)->orderBy('recorded_at', 'desc')->first() : null;
        $latestSos = $device ? SosEvent::where('id_device', $device->id_device)->orderBy('triggered_at', 'desc')->first() : null;

        // Calculate device connection state
        $isOnline = false;
        if ($latestSensor || $latestGps) {
            $lastTime = max(
                $latestSensor ? strtotime($latestSensor->recorded_at) : 0,
                $latestGps ? strtotime($latestGps->recorded_at) : 0
            );
            // Connected if there was communication in the last 60 seconds
            if ((time() - $lastTime) < 60) {
                $isOnline = true;
            }
        }

        return view('dashboard', [
            'device' => $device,
            'latestSensor' => $latestSensor,
            'latestGps' => $latestGps,
            'latestSos' => $latestSos,
            'isOnline' => $isOnline,
        ]);
    }

    /**
     * Show the historical log of data.
     */
    public function riwayat(Request $request)
    {
        $device = Device::first();
        
        $query = SensorLog::query();
        if ($device) {
            $query->where('id_device', $device->id_device);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date) {
            $query->whereBetween('recorded_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        // Filter by SOS Status
        if ($request->has('sos_status') && $request->sos_status) {
            if ($request->sos_status === 'aktif') {
                $query->whereExists(function ($q) {
                    $q->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('sos_events')
                      ->whereColumn('sos_events.id_device', 'sensor_logs.id_device')
                      ->whereColumn('sos_events.triggered_at', '<=', 'sensor_logs.recorded_at')
                      ->where(function ($sub) {
                          $sub->whereColumn('sos_events.resolved_at', '>=', 'sensor_logs.recorded_at')
                              ->orWhereNull('sos_events.resolved_at');
                      });
                });
            } elseif ($request->sos_status === 'tidak_aktif') {
                $query->whereNotExists(function ($q) {
                    $q->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('sos_events')
                      ->whereColumn('sos_events.id_device', 'sensor_logs.id_device')
                      ->whereColumn('sos_events.triggered_at', '<=', 'sensor_logs.recorded_at')
                      ->where(function ($sub) {
                          $sub->whereColumn('sos_events.resolved_at', '>=', 'sensor_logs.recorded_at')
                              ->orWhereNull('sos_events.resolved_at');
                      });
                });
            }
        }

        $logs = $query->orderBy('recorded_at', 'desc')->paginate(10);

        // Populate with closest GPS coords and SOS event status at that time
        foreach ($logs as $log) {
            $closestGps = GpsLog::where('id_device', $log->id_device)
                ->where('recorded_at', '<=', $log->recorded_at)
                ->orderBy('recorded_at', 'desc')
                ->first();

            if (!$closestGps) {
                $closestGps = GpsLog::where('id_device', $log->id_device)
                    ->orderBy('recorded_at', 'asc')
                    ->first();
            }

            $log->latitude = $closestGps ? $closestGps->latitude : -6.200000;
            $log->longitude = $closestGps ? $closestGps->longitude : 106.816666;

            $activeSos = SosEvent::where('id_device', $log->id_device)
                ->where('triggered_at', '<=', $log->recorded_at)
                ->where(function ($q) use ($log) {
                    $q->where('resolved_at', '>=', $log->recorded_at)
                      ->orWhereNull('resolved_at');
                })
                ->exists();

            $log->sos_status = $activeSos ? 'Aktif' : 'Tidak Aktif';
        }

        return view('riwayat', [
            'logs' => $logs,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'sos_status' => $request->sos_status,
        ]);
    }

    /**
     * Show SOS events list.
     */
    public function sos()
    {
        $device = Device::first();
        $sosEvents = SosEvent::where('id_device', $device->id_device ?? 0)
            ->orderBy('triggered_at', 'desc')
            ->paginate(10);

        return view('sos', [
            'sosEvents' => $sosEvents,
        ]);
    }

    /**
     * Resolve/Acknowledge an SOS event.
     */
    public function resolveSos($id)
    {
        $sos = SosEvent::findOrFail($id);
        $sos->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        // Kirim perintah MQTT ke ESP32 agar mematikan mode SOS pada alat
        try {
            $server   = 'broker.emqx.io';
            $port     = 1883;
            $clientId = 'laravel_command_publisher_' . uniqid();
            
            $mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
            $mqtt->connect();
            $mqtt->publish('esp32/tracker/commands', json_encode(['command' => 'RESOLVE_SOS']), 0);
            $mqtt->disconnect();
        } catch (\Exception $e) {
            \Log::error("Gagal mengirim perintah MQTT ke ESP32: " . $e->getMessage());
        }

        return back()->with('success', 'Kejadian SOS berhasil ditandai selesai.');
    }

    /**
     * Show device and user settings page.
     */
    public function pengaturan()
    {
        $device = Device::first();
        $users = User::all();

        return view('pengaturan', [
            'device' => $device,
            'users' => $users,
        ]);
    }

    /**
     * Update settings.
     */
    public function updatePengaturan(Request $request)
    {
        $device = Device::first();
        
        $validated = $request->validate([
            'device_name' => 'required|string|max:255',
            'mac_address' => 'required|string|max:255',
            'status' => 'required|string|in:active,inactive',
        ]);

        if ($device) {
            $device->update([
                'device_name' => $validated['device_name'],
                'mac_address' => $validated['mac_address'],
                'status' => $validated['status'],
            ]);
        }

        return back()->with('success', 'Pengaturan perangkat berhasil diperbarui.');
    }

    /**
     * Delete a single sensor log record.
     */
    public function deleteLog($id)
    {
        $log = SensorLog::findOrFail($id);
        $log->delete();

        return back()->with('success', 'Data log perekaman berhasil dihapus.');
    }

    /**
     * Clear all sensor logs.
     */
    public function clearLogs()
    {
        SensorLog::query()->delete();

        return back()->with('success', 'Semua data riwayat berhasil dihapus.');
    }

    /**
     * Delete a single SOS event record.
     */
    public function deleteSos($id)
    {
        $event = SosEvent::findOrFail($id);
        $event->delete();

        return back()->with('success', 'Data kejadian SOS berhasil dihapus.');
    }

    /**
     * Clear all SOS events.
     */
    public function clearSos()
    {
        SosEvent::query()->delete();

        return back()->with('success', 'Semua data kejadian SOS berhasil dihapus.');
    }
}
