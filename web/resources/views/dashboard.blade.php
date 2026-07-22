@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
<!-- Leaflet.js CSS for Map -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<style>
    .dashboard-header {
        margin-bottom: 25px;
    }

    .dashboard-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-main);
    }

    .dashboard-header p {
        font-size: 14px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    /* Status Cards Grid */
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    @media (max-width: 1200px) {
        .cards-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .cards-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .cards-grid {
            grid-template-columns: 1fr;
        }
    }

    .status-card {
        background-color: #ffffff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: var(--card-shadow);
        display: flex;
        align-items: center;
        gap: 15px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .status-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-shadow-hover);
    }

    .card-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .card-icon.proximity {
        background-color: #e6f7f4;
        color: #00a896;
    }

    .card-icon.gps {
        background-color: #e0f2fe;
        color: #0284c7;
    }

    .card-icon.sos {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .card-icon.system {
        background-color: #e0e7ff;
        color: #4f46e5;
    }

    .card-icon.time {
        background-color: #fffbeb;
        color: #d97706;
    }

    .card-info {
        display: flex;
        flex-direction: column;
    }

    .card-info .label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-info .value {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-main);
        margin: 4px 0;
    }

    .card-info .sub-desc {
        font-size: 11px;
        font-weight: 500;
        color: var(--text-muted);
    }

    /* Pulse effects for Alert states */
    .pulse-danger {
        animation: pulse-danger-animation 1.5s infinite alternate;
        border: 1px solid rgba(239, 68, 68, 0.4);
    }

    @keyframes pulse-danger-animation {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        100% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    }

    .pulse-safe {
        border: 1px solid var(--border-color);
    }

    /* Main Grid Panels */
    .dashboard-panels {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
        margin-bottom: 30px;
    }

    @media (max-width: 992px) {
        .dashboard-panels {
            grid-template-columns: 1fr;
        }
    }

    .panel-card {
        background-color: #ffffff;
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .panel-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .panel-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-main);
    }

    /* Map container styling */
    .map-container {
        height: 450px;
        width: 100%;
        background-color: #e2e8f0;
    }

    /* Details Panel */
    .info-list {
        list-style: none;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        padding-bottom: 12px;
        border-bottom: 1px dashed var(--border-color);
        font-size: 14px;
    }

    .info-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-item .info-label {
        font-weight: 500;
        color: var(--text-muted);
    }

    .info-item .info-val {
        font-weight: 600;
        color: var(--text-main);
    }

    .badge-status {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-status.active {
        background-color: #d1fae5;
        color: #065f46;
    }

    .badge-status.inactive {
        background-color: #f1f5f9;
        color: #475569;
    }

    /* Visual alert banner */
    .sos-banner {
        background-color: #fef2f2;
        border-left: 5px solid var(--danger-color);
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .sos-banner-left {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #991b1b;
    }

    .sos-banner-left i {
        font-size: 24px;
        animation: flash 1s infinite alternate;
    }

    @keyframes flash {
        0% { opacity: 0.3; }
        100% { opacity: 1; }
    }

    .sos-banner-left h4 {
        font-size: 15px;
        font-weight: 700;
    }

    .sos-banner-left p {
        font-size: 13px;
        margin-top: 2px;
    }

    .btn-sos-resolve {
        background-color: var(--danger-color);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .btn-sos-resolve:hover {
        background-color: #b91c1c;
    }
</style>
@endsection

@section('content')

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h2>Dashboard</h2>
        <p>Monitoring Smart Cane secara real-time melalui jaringan IoT.</p>
    </div>

    <!-- Active SOS Alert Banner -->
    <div id="sos-alert-banner" class="sos-banner" style="display: none;">
        <div class="sos-banner-left">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>
                <h4>DARURAT: Tombol SOS Ditekan!</h4>
                <p>Pengguna tongkat pintar memerlukan bantuan segera. Lokasi terdeteksi di peta.</p>
            </div>
        </div>
        <form id="sos-resolve-form" action="" method="POST">
            @csrf
            <button type="submit" class="btn-sos-resolve">Tandai Selesai</button>
        </form>
    </div>

    <!-- Status Cards Grid -->
    <div class="cards-grid">
        <!-- 1. Jarak Hambatan Card -->
        <div class="status-card {{ $latestSensor && $latestSensor->distance_cm <= 100 && $latestSensor->distance_cm > 0 ? 'pulse-danger' : 'pulse-safe' }}" id="card-proximity">
            <div class="card-icon proximity">
                <i class="fa-solid fa-rss" style="transform: rotate(45deg);"></i>
            </div>
            <div class="card-info">
                <span class="label">Jarak Hambatan</span>
                <span class="value" id="val-distance">
                    {{ $latestSensor ? round($latestSensor->distance_cm) . ' cm' : '0 cm' }}
                </span>
                <span class="sub-desc" id="lbl-distance-status" style="color: {{ $latestSensor && $latestSensor->distance_cm <= 100 && $latestSensor->distance_cm > 0 ? '#ef4444' : 'var(--text-muted)' }};">
                    {{ $latestSensor && $latestSensor->distance_cm <= 100 && $latestSensor->distance_cm > 0 ? 'Bahaya' : 'Aman' }}
                </span>
            </div>
        </div>

        <!-- 2. Lokasi Card -->
        <div class="status-card" id="card-gps">
            <div class="card-icon gps">
                <i class="fa-solid fa-location-dot"></i>
            </div>
            <div class="card-info">
                <span class="label">Lokasi (GPS)</span>
                <span class="value" id="val-gps" style="font-size: 13px; font-weight: 700; margin: 4px 0; word-break: break-all;">
                    {{ $latestGps ? sprintf('%.6f, %.6f', $latestGps->latitude, $latestGps->longitude) : '-6.200000, 106.816666' }}
                </span>
                <span class="sub-desc">Latitude, Longitude</span>
            </div>
        </div>

        <!-- 3. Status SOS Card -->
        <div class="status-card {{ $latestSos && $latestSos->status === 'active' ? 'pulse-danger' : 'pulse-safe' }}" id="card-sos">
            <div class="card-icon sos" style="font-weight: 700; font-size: 12px; {{ $latestSos && $latestSos->status === 'active' ? 'background-color: #ef4444; color: white;' : '' }}">
                @if ($latestSos && $latestSos->status === 'active')
                    <i class="fa-solid fa-bell"></i>
                @else
                    SOS
                @endif
            </div>
            <div class="card-info">
                <span class="label">Status SOS</span>
                <span class="value" id="val-sos" style="color: {{ $latestSos && $latestSos->status === 'active' ? '#ef4444' : 'var(--text-main)' }};">
                    {{ $latestSos && $latestSos->status === 'active' ? 'Aktif!' : 'Tidak Aktif' }}
                </span>
                <span class="sub-desc" id="lbl-sos-status" style="color: {{ $latestSos && $latestSos->status === 'active' ? '#ef4444' : 'var(--text-muted)' }};">
                    {{ $latestSos && $latestSos->status === 'active' ? 'Darurat' : 'Normal' }}
                </span>
            </div>
        </div>

        <!-- 4. Status Sistem Card -->
        <div class="status-card" id="card-system">
            <div class="card-icon system">
                <i class="fa-solid fa-wifi"></i>
            </div>
            <div class="card-info">
                <span class="label">Status Sistem</span>
                <span class="value" id="val-system">
                    {{ $isOnline ? 'Online' : 'Offline' }}
                </span>
                <span class="sub-desc" id="lbl-system-status">
                    {{ $isOnline ? 'Terhubung' : 'Terputus' }}
                </span>
            </div>
        </div>

        <!-- 5. Waktu Terakhir Card -->
        <div class="status-card" id="card-time">
            <div class="card-icon time">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="card-info">
                <span class="label">Waktu Terakhir</span>
                <span class="value" id="val-time">
                    @php
                        $latestTime = 'N/A';
                        if ($latestSensor || $latestGps) {
                            $ts = max(
                                $latestSensor ? strtotime($latestSensor->recorded_at) : 0,
                                $latestGps ? strtotime($latestGps->recorded_at) : 0
                            );
                            if ($ts > 0) $latestTime = date('H:i:s', $ts);
                        }
                    @endphp
                    {{ $latestTime }}
                </span>
                <span class="sub-desc" id="lbl-time-status">
                    @php
                        $latestDate = 'N/A';
                        if ($latestSensor || $latestGps) {
                            $ts = max(
                                $latestSensor ? strtotime($latestSensor->recorded_at) : 0,
                                $latestGps ? strtotime($latestGps->recorded_at) : 0
                            );
                            if ($ts > 0) {
                                $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                $latestDate = date('d', $ts) . ' ' . $months[date('n', $ts) - 1] . ' ' . date('Y', $ts);
                            }
                        }
                    @endphp
                    {{ $latestDate }}
                </span>
            </div>
        </div>
    </div>

    <!-- Main Grid Section -->
    <div class="dashboard-panels">
        <!-- Map Panel -->
        <div class="panel-card" id="map">
            <div class="panel-header">
                <h3><i class="fa-solid fa-map" style="color: var(--primary-color); margin-right: 8px;"></i> Lokasi Pengguna</h3>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button onclick="locateBrowser()" style="background-color: var(--primary-color); color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; transition: background-color 0.2s; display: flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-location-arrow"></i> Deteksi Lokasi Saya
                    </button>
                    <span style="font-size: 12px; color: var(--text-muted);">Diperbarui secara real-time</span>
                </div>
            </div>
            <div class="map-container" id="gps-map"></div>
        </div>

        <!-- Device Info Panel -->
        <div class="panel-card">
            <div class="panel-header" style="border-bottom: none; padding-bottom: 0;">
                <h3 style="font-size: 16px; font-weight: 600; color: var(--text-main); font-family: 'Inter', sans-serif;">Informasi Perangkat</h3>
            </div>
            <div style="padding: 20px 20px 25px 20px; padding-top: 15px;">
                <div style="display: grid; grid-template-columns: 130px 20px 1fr; row-gap: 12px; font-size: 14px; color: var(--text-main); font-family: 'Inter', sans-serif;">
                    <div>ID Perangkat</div>
                    <div>:</div>
                    <div id="info-id" style="font-weight: 600;">
                        {{ $device ? $device->device_name : 'SMARTCANE-001' }}
                    </div>

                    <div>ESP32</div>
                    <div>:</div>
                    <div id="info-esp32" style="font-weight: 600; color: {{ $isOnline ? '#059669' : '#dc2626' }};">
                        {{ $isOnline ? 'Terhubung' : 'Terputus' }}
                    </div>


                    <div>Sinyal GPS</div>
                    <div>:</div>
                    <div id="info-gps" style="font-weight: 600; color: {{ $isOnline ? '#059669' : '#dc2626' }};">
                        {{ $isOnline ? 'Baik' : 'Tidak Ada Sinyal' }}
                    </div>

                    <div>Koneksi Internet</div>
                    <div>:</div>
                    <div id="info-internet" style="font-weight: 600; color: {{ $isOnline ? '#059669' : '#dc2626' }};">
                        {{ $isOnline ? 'Stabil' : 'Terputus' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<!-- Leaflet JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
    // Initial Map Setup
    let initialLat = {{ $latestGps ? $latestGps->latitude : -6.200000 }};
    let initialLng = {{ $latestGps ? $latestGps->longitude : 106.816666 }};

    const map = L.map('gps-map').setView([initialLat, initialLng], 16);

    // OpenStreetMap Layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Customized Pulse Marker Style
    const pulsingIcon = L.divIcon({
        className: 'gps-pulse-marker',
        html: `
            <div style="position: relative; width: 20px; height: 20px;">
                <div style="position: absolute; width: 100%; height: 100%; border-radius: 50%; background-color: #2563eb; opacity: 0.7; transform: scale(1); animation: markerPulse 1.8s infinite ease-out;"></div>
                <div style="position: absolute; width: 10px; height: 10px; border-radius: 50%; background-color: #2563eb; top: 5px; left: 5px; border: 2px solid white;"></div>
            </div>
            <style>
                @keyframes markerPulse {
                    0% { transform: scale(0.6); opacity: 0.9; }
                    100% { transform: scale(3.5); opacity: 0; }
                }
            </style>
        `,
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });

    const marker = L.marker([initialLat, initialLng], { icon: pulsingIcon }).addTo(map);
    marker.bindPopup("<b>Posisi Pengguna Smart Cane</b>").openPopup();

    // Browser Geolocation Fallback: If no real GPS has been recorded yet (or matches default/testing fallback), use browser location
    let isFallback = (initialLat === -6.200000 && initialLng === 106.816666) || 
                      (initialLat === -6.211500 && initialLng === 106.822400);
    
    if (isFallback) {
        setTimeout(locateBrowser, 500);
    }

    function locateBrowser() {
    console.log("Browser GPS dimatikan. Menggunakan GPS Smart Cane ESP32.");
}
    // ID Tracking variables for AJAX Polling
    let lastSensorId = {{ $latestSensor ? $latestSensor->id_sensor : 0 }};
    let lastGpsId = {{ $latestGps ? $latestGps->id_gps : 0 }};
    let lastSosId = {{ $latestSos ? $latestSos->id_sos : 0 }};

    function pollRealtimeData() {
        const url = `{{ route('realtime.stream') }}?lastSensorId=${lastSensorId}&lastGpsId=${lastGpsId}&lastSosId=${lastSosId}`;
        
        fetch(url)
            .then(response => response.json())
            .then(payload => {
                // Sync tracker indices
                lastSensorId = payload.lastSensorId;
                lastGpsId = payload.lastGpsId;
                lastSosId = payload.lastSosId;

                const data = payload.data;
                
                // 1. Update Distance Logs
                if (data.sensor) {
                    const distance = Math.round(data.sensor.distance_cm);
                    document.getElementById('val-distance').innerText = distance + ' cm';
                    const statusLbl = document.getElementById('lbl-distance-status');
                    const cardProx = document.getElementById('card-proximity');

                    if (distance > 0 && distance <= 100) {
                        statusLbl.innerText = 'Bahaya';
                        statusLbl.style.color = '#ef4444';
                        cardProx.classList.add('pulse-danger');
                        cardProx.classList.remove('pulse-safe');
                    } else {
                        statusLbl.innerText = 'Aman';
                        statusLbl.style.color = 'var(--text-muted)';
                        cardProx.classList.remove('pulse-danger');
                        cardProx.classList.add('pulse-safe');
                    }
                }

                // 2. Update GPS position
                if (data.gps) {
                    const lat = parseFloat(data.gps.latitude);
                    const lng = parseFloat(data.gps.longitude);
                    document.getElementById('val-gps').innerText = lat.toFixed(6) + ', ' + lng.toFixed(6);
                    
                    // Move marker on map
                    const newLatLng = new L.LatLng(lat, lng);
                    marker.setLatLng(newLatLng);
                    map.panTo(newLatLng);
                }

                // Helper to translate Month names to Indonesian
                function formatIndoDate(dateStr) {
                    if (!dateStr || dateStr === 'N/A') return '-';
                    const months = {
                        'Jan': 'Januari', 'Feb': 'Februari', 'Mar': 'Maret', 'Apr': 'April',
                        'May': 'Mei', 'Jun': 'Juni', 'Jul': 'Juli', 'Aug': 'Agustus',
                        'Sep': 'September', 'Oct': 'Oktober', 'Nov': 'November', 'Dec': 'Desember'
                    };
                    const parts = dateStr.split(' ');
                    if (parts.length === 3) {
                        const monthEng = parts[1];
                        const monthIndo = months[monthEng] || monthEng;
                        return `${parts[0]} ${monthIndo} ${parts[2]}`;
                    }
                    return dateStr;
                }

                // 3. Update SOS Events state, System states & last updated times
                if (data.currentState) {
                    const state = data.currentState;
                    
                    // Sync last updated time & date
                    if (state.time && state.time !== 'N/A') {
                        document.getElementById('val-time').innerText = state.time;
                    }
                    if (state.date && state.date !== 'N/A') {
                        document.getElementById('lbl-time-status').innerText = formatIndoDate(state.date);
                    }

                    // Sync Connection status based on actual device activity
                    const valSystem = document.getElementById('val-system');
                    const lblSystem = document.getElementById('lbl-system-status');
                    
                    const infoEsp32 = document.getElementById('info-esp32');
                    const infoGps = document.getElementById('info-gps');
                    const infoInternet = document.getElementById('info-internet');

                    if (state.is_online) {
                        valSystem.innerText = 'Online';
                        lblSystem.innerText = 'Terhubung';
                        if (infoEsp32) {
                            infoEsp32.innerText = 'Terhubung';
                            infoEsp32.style.color = '#059669';
                        }
                        if (infoGps) {
                            infoGps.innerText = 'Baik';
                            infoGps.style.color = '#059669';
                        }
                        if (infoInternet) {
                            infoInternet.innerText = 'Stabil';
                            infoInternet.style.color = '#059669';
                        }
                    } else {
                        valSystem.innerText = 'Offline';
                        lblSystem.innerText = 'Terputus';
                        if (infoEsp32) {
                            infoEsp32.innerText = 'Terputus';
                            infoEsp32.style.color = '#dc2626';
                        }
                        if (infoGps) {
                            infoGps.innerText = 'Tidak Ada Sinyal';
                            infoGps.style.color = '#dc2626';
                        }
                        if (infoInternet) {
                            infoInternet.innerText = 'Terputus';
                            infoInternet.style.color = '#dc2626';
                        }
                    }

                    // Sync SOS Status dynamically (resolves state changes instantly)
                    const sosVal = document.getElementById('val-sos');
                    const sosLbl = document.getElementById('lbl-sos-status');
                    const cardSos = document.getElementById('card-sos');
                    const cardSosIcon = document.querySelector('#card-sos .card-icon');
                    const banner = document.getElementById('sos-alert-banner');
                    const badgeCount = document.getElementById('sos-badge-count');

                    if (state.sos_status === 'Aktif') {
                        sosVal.innerText = 'Aktif!';
                        sosVal.style.color = '#ef4444';
                        sosLbl.innerText = 'Darurat!';
                        sosLbl.style.color = '#ef4444';
                        cardSos.classList.add('pulse-danger');
                        if (cardSosIcon) {
                            cardSosIcon.innerHTML = '<i class="fa-solid fa-bell"></i>';
                            cardSosIcon.style.backgroundColor = '#ef4444';
                            cardSosIcon.style.color = 'white';
                        }

                        banner.style.display = 'flex';
                        if (state.sos_id) {
                            document.getElementById('sos-resolve-form').action = `/sos/resolve/${state.sos_id}`;
                        }

                        if (badgeCount) {
                            badgeCount.innerText = '1';
                            badgeCount.style.display = 'flex';
                        }
                    } else {
                        sosVal.innerText = 'Tidak Aktif';
                        sosVal.style.color = 'var(--text-main)';
                        sosLbl.innerText = 'Normal';
                        sosLbl.style.color = 'var(--text-muted)';
                        cardSos.classList.remove('pulse-danger');
                        if (cardSosIcon) {
                            cardSosIcon.innerHTML = 'SOS';
                            cardSosIcon.style.backgroundColor = '#fee2e2';
                            cardSosIcon.style.color = '#dc2626';
                        }
                        
                        banner.style.display = 'none';
                        if (badgeCount) {
                            badgeCount.style.display = 'none';
                        }
                    }
                }

                // Loop next poll in 2 seconds
                setTimeout(pollRealtimeData, 2000);
            })
            .catch(error => {
                console.error("Polling error:", error);
                
                // Show offline fallback
                const valSystem = document.getElementById('val-system');
                const lblSystem = document.getElementById('lbl-system-status');
                
                const infoEsp32 = document.getElementById('info-esp32');
                const infoGps = document.getElementById('info-gps');
                const infoWifi = document.getElementById('info-wifi');
                const infoMqtt = document.getElementById('info-mqtt');

                valSystem.innerText = 'Offline';
                lblSystem.innerText = 'Terputus';
                if (infoEsp32) infoEsp32.innerText = 'Terputus';
                if (infoGps) infoGps.innerText = 'Tidak Ada Sinyal';
                if (infoWifi) infoWifi.innerText = 'Terputus';
                if (infoMqtt) infoMqtt.innerText = 'Terputus';
                
                // Retry in 5 seconds on error
                setTimeout(pollRealtimeData, 5000);
            });
    }

    // Start Polling
    setTimeout(pollRealtimeData, 1000);

    // On Load, double check active SOS to display the warning banner
    @if ($latestSos && $latestSos->status === 'active')
        document.getElementById('sos-alert-banner').style.display = 'flex';
        document.getElementById('sos-resolve-form').action = "{{ route('sos.resolve', $latestSos->id_sos) }}";
        document.getElementById('sos-badge-count').innerText = '1';
        document.getElementById('sos-badge-count').style.display = 'flex';
        document.getElementById('card-sos').classList.add('pulse-danger');
    @endif
</script>
@endsection
