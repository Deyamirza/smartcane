@extends('layouts.app')

@section('title', 'Pengaturan')

@section('styles')
<style>
    .pengaturan-header {
        margin-bottom: 25px;
    }

    .pengaturan-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-main);
    }

    .pengaturan-header p {
        font-size: 14px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    /* Success Alert */
    .alert-success {
        background-color: #d1fae5;
        border-left: 4px solid var(--success-color);
        color: #065f46;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        font-weight: 500;
    }

    /* Grid Layout */
    .settings-grid {
        display: grid;
        grid-template-columns: 1.2fr 1.8fr;
        gap: 25px;
    }

    @media (max-width: 992px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }
    }

    .settings-card {
        background-color: #ffffff;
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .settings-card-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        background-color: #fafbfd;
    }

    .settings-card-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-main);
    }

    .settings-card-body {
        padding: 20px;
    }

    /* Form control spacing */
    .form-group {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
    }

    .form-control {
        padding: 12px 14px;
        border: 1.5px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        color: var(--text-main);
        outline: none;
        background-color: #fff;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(0, 168, 150, 0.1);
    }

    .btn-save {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
        align-self: flex-start;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-save:hover {
        background-color: var(--primary-dark);
    }

    /* Users list styling */
    .user-list-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .user-list-table th {
        background-color: #f8fafc;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-color);
        font-weight: 600;
        color: var(--text-muted);
        text-align: left;
    }

    .user-list-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
    }

    .user-list-table tr:last-child td {
        border-bottom: none;
    }

    .role-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .role-badge.admin {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .role-badge.family {
        background-color: #f1f5f9;
        color: #475569;
    }
</style>
@endsection

@section('content')

    <!-- Header Section -->
    <div class="pengaturan-header">
        <h2>Pengaturan Sistem</h2>
        <p>Konfigurasi parameter perangkat ESP32 dan akun pemantauan.</p>
    </div>

    <!-- Success Message -->
    @if (session('success'))
        <div class="alert-success">
            <i class="fa-solid fa-circle-check" style="margin-right: 8px;"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="settings-grid">
        <!-- Device settings form -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h3><i class="fa-solid fa-microchip" style="color: var(--primary-color); margin-right: 8px;"></i> Konfigurasi Perangkat</h3>
            </div>
            <div class="settings-card-body">
                <form action="{{ route('pengaturan.update') }}" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                    @csrf
                    <div class="form-group">
                        <label for="device_name">Nama Perangkat</label>
                        <input type="text" id="device_name" name="device_name" class="form-control" value="{{ $device ? $device->device_name : '' }}" required>
                    </div>

                    <div class="form-group">
                        <label for="mac_address">Alamat MAC (MAC Address)</label>
                        <input type="text" id="mac_address" name="mac_address" class="form-control" value="{{ $device ? $device->mac_address : '' }}" required placeholder="AA:BB:CC:DD:EE:FF">
                    </div>

                    <div class="form-group">
                        <label for="status">Status Pendaftaran</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="active" {{ $device && $device->status === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ $device && $device->status === 'inactive' ? 'selected' : '' }}>Non-aktif</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <!-- Authorized Users list -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h3><i class="fa-solid fa-users" style="color: var(--primary-color); margin-right: 8px;"></i> Daftar Pengguna Berwenang</h3>
            </div>
            <div class="settings-card-body" style="padding: 0;">
                <table class="user-list-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role / Hak Akses</th>
                            <th>Tanggal Pembuatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td style="font-weight: 600; color: var(--text-main);">
                                    {{ $user->username }}
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->role === 'admin')
                                        <span class="role-badge admin">Administrator</span>
                                    @else
                                        <span class="role-badge family">Pendamping / Keluarga</span>
                                    @endif
                                </td>
                                <td style="color: var(--text-muted); font-size: 13px;">
                                    {{ $user->created_at->format('d M Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
