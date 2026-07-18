# Script to helper erase ESP32 flash memory on Windows
# Antigravity AI Assistant

$esptoolPath = "C:\Users\lenovo\AppData\Local\Arduino15\packages\esp32\tools\esptool_py\5.3.0\esptool.exe"
if (-not (Test-Path $esptoolPath)) {
    # Fallback to 4.5.1 if 5.3.0 is not found
    $esptoolPath = "C:\Users\lenovo\AppData\Local\Arduino15\packages\esp32\tools\esptool_py\4.5.1\esptool.exe"
}

if (-not (Test-Path $esptoolPath)) {
    Write-Error "esptool.exe tidak ditemukan di folder Arduino15 Anda."
    exit 1
}

# Ambil semua port serial yang aktif
$ports = [System.IO.Ports.SerialPort]::GetPortNames() | Sort-Object -Unique

if ($ports.Count -eq 0) {
    Write-Warning "Tidak ada port COM (serial device) yang terdeteksi. Hubungkan ESP32 Anda terlebih dahulu."
    exit 1
}

# Gunakan COM3 jika ada, jika tidak gunakan port pertama yang ditemukan
$portToUse = "COM3"
if ($ports -notcontains "COM3") {
    $portToUse = $ports[0]
}

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "PENGHAPUSAN MEMORI FLASH ESP32 (ERASE FLASH)" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "Menggunakan esptool: $esptoolPath"
Write-Host "Port COM Terdeteksi: $($ports -join ', ')"
Write-Host "Port COM yang dipilih: $portToUse"
Write-Host ""
Write-Host "PENTING: Pastikan Anda telah MENUTUP Serial Monitor di Arduino IDE" -ForegroundColor Yellow
Write-Host "agar port tidak terkunci saat proses penghapusan." -ForegroundColor Yellow
Write-Host ""

Read-Host "Tekan ENTER untuk memulai proses penghapusan..."

Write-Host "Menjalankan perintah erase_flash..." -ForegroundColor Green
& $esptoolPath --chip esp32 --port $portToUse --baud 921600 erase_flash

Write-Host ""
Write-Host "Proses selesai! Sekarang silakan buka Arduino IDE Anda," -ForegroundColor Cyan
Write-Host "lalu lakukan UPLOAD ulang program Anda dengan konfigurasi:" -ForegroundColor Cyan
Write-Host "  - Flash Mode: DIO" -ForegroundColor Cyan
Write-Host "  - Partition Scheme: Default 4MB dengan SPIFFS" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
