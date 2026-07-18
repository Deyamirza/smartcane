<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Device;
use App\Models\SosEvent;

class TelegramService
{
    /**
     * Send SOS alert to Telegram.
     *
     * @param Device $device
     * @param float $latitude
     * @param float $longitude
     * @param int $sosId
     * @return int|null Telegram message ID if sent successfully
     */
    public static function sendSosAlert(Device $device, $latitude, $longitude, $sosId)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (!$token || !$chatId) {
            Log::warning("Telegram Bot Token atau Chat ID belum dikonfigurasi di file .env");
            return null;
        }

        $deviceName = $device->device_name;
        $username = $device->user ? $device->user->username : 'Tidak diketahui';
        $time = now()->format('d M Y H:i:s');
        $mapsUrl = "https://www.google.com/maps?q={$latitude},{$longitude}";

        $text = "🚨 *PANGGILAN DARURAT (SOS)* 🚨\n\n" .
                "Pengguna tongkat pintar membutuhkan bantuan segera!\n\n" .
                "👤 *Nama Pengguna:* {$username}\n" .
                "📱 *Perangkat:* {$deviceName}\n" .
                "📍 *Lokasi:* {$latitude}, {$longitude}\n" .
                "⏰ *Waktu:* {$time}\n\n" .
                "🗺️ *Google Maps:* [Buka Peta]({$mapsUrl})";

        try {
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => false,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $messageId = $data['result']['message_id'] ?? null;
                Log::info("Notifikasi Telegram SOS berhasil dikirim. Message ID: {$messageId}");
                return $messageId;
            } else {
                Log::error("Gagal mengirim notifikasi Telegram: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Error saat mengirim notifikasi Telegram: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Update an existing SOS alert on Telegram to mark it as resolved.
     *
     * @param Device $device
     * @param SosEvent $sos
     * @return bool
     */
    public static function resolveSosAlert(Device $device, SosEvent $sos)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');
        $messageId = $sos->telegram_message_id;

        if (!$token || !$chatId || !$messageId) {
            return false;
        }

        $deviceName = $device->device_name;
        $username = $device->user ? $device->user->username : 'Tidak diketahui';
        $triggerTime = \Carbon\Carbon::parse($sos->triggered_at)->format('d M Y H:i:s');
        $resolvedTime = now()->format('d M Y H:i:s');
        $mapsUrl = "https://www.google.com/maps?q={$sos->latitude},{$sos->longitude}";

        $text = "✅ *SOS SELESAI DITANGANI* ✅\n\n" .
                "Keadaan darurat untuk pengguna ini telah diselesaikan.\n\n" .
                "👤 *Nama Pengguna:* {$username}\n" .
                "📱 *Perangkat:* {$deviceName}\n" .
                "⏰ *Waktu Kejadian:* {$triggerTime}\n" .
                "⏰ *Selesai Pada:* {$resolvedTime}\n\n" .
                "🗺️ *Lokasi Sebelumnya:* [Buka Peta]({$mapsUrl})";

        try {
            $response = Http::post("https://api.telegram.org/bot{$token}/editMessageText", [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful()) {
                Log::info("Notifikasi Telegram SOS berhasil diperbarui ke status Selesai.");
                return true;
            } else {
                Log::error("Gagal memperbarui notifikasi Telegram: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Error saat memperbarui notifikasi Telegram: " . $e->getMessage());
        }

        return false;
    }
}
