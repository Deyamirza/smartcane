@extends('layouts.app')

@section('title', 'Riwayat Data')

@section('styles')
<style>
    .riwayat-header {
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .riwayat-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-main);
    }

    .riwayat-header p {
        font-size: 14px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    /* Filter Panel */
    .filter-card {
        background-color: #ffffff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: var(--card-shadow);
        margin-bottom: 25px;
    }

    .filter-form {
        display: flex;
        align-items: flex-end;
        gap: 15px;
        flex-wrap: wrap;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
    }

    .form-control {
        padding: 10px 14px;
        border: 1.5px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        color: var(--text-main);
        outline: none;
        background-color: #fff;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        border-color: var(--primary-color);
    }

    

    .btn-filter {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 11px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.2s;
    }

    .btn-filter:hover {
        background-color: var(--primary-dark);
    }

    .btn-reset {
        background-color: #f1f5f9;
        color: var(--text-muted);
        border: 1px solid var(--border-color);
        padding: 11px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.2s, color 0.2s;
    }

    .btn-reset:hover {
        background-color: #e2e8f0;
        color: var(--text-main);
    }

    /* Action Map Button */
    .btn-action-map {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background-color 0.2s;
    }

    .btn-action-map:hover {
        background-color: var(--primary-dark);
        color: white;
    }

    /* Action Delete Button */
    .btn-action-delete {
        background-color: var(--danger-color);
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background-color 0.2s;
    }

    .btn-action-delete:hover {
        background-color: #b91c1c;
    }

    /* Table Styling */
    .table-container {
        background-color: #ffffff;
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        overflow-x: auto;
        margin-bottom: 25px;
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 14px;
    }

    .table-custom th {
        background-color: var(--primary-color);
        color: #ffffff;
        padding: 16px 20px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .table-custom td {
        padding: 14px 20px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-main);
    }

    .table-custom tr:last-child td {
        border-bottom: none;
    }

    .table-custom tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge.danger {
        background-color: #fee2e2;
        color: #ef4444;
    }

    .badge.success {
        background-color: #d1fae5;
        color: #059669;
    }

    .badge.neutral {
        background-color: #f1f5f9;
        color: #64748b;
    }

    /* Custom Laravel Pagination override styles */
    .pagination-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 10px;
    }

    .pagination-info {
        font-size: 13px;
        color: var(--text-muted);
    }

    .pagination-links {
        display: flex;
        gap: 5px;
    }

    .pagination-links a, .pagination-links span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        color: var(--text-muted);
        background-color: white;
        border: 1px solid var(--border-color);
        transition: all 0.2s ease;
    }

    .pagination-links a:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        background-color: var(--primary-light);
    }

    .pagination-links .active {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .pagination-links .disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>
@endsection

@section('content')

    <!-- Header Section -->
    <div class="riwayat-header">
        <div>
            <h2>Riwayat Data</h2>
            <p>Daftar data pembacaan sensor dan koordinat GPS Smart Cane.</p>
        </div>
        <div>
            <form action="{{ route('riwayat.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua data riwayat? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                <button type="submit" class="btn-clear-all" style="background-color: var(--danger-color); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background-color 0.2s;">
                    <i class="fa-solid fa-trash-can"></i> Hapus Semua Riwayat
                </button>
            </form>
        </div>
    </div>

    <!-- Success Message -->
    @if (session('success'))
        <div class="alert-success" style="background-color: #d1fae5; border-left: 4px solid var(--success-color); color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 500;">
            <i class="fa-solid fa-circle-check" style="margin-right: 8px;"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Filter Card -->
    <div class="filter-card">
        <form class="filter-form" action="{{ route('riwayat') }}" method="GET">
            <div class="form-group">
                <label for="start_date">Mulai Tanggal</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $start_date }}">
            </div>
            <div class="form-group">
                <label for="end_date">Sampai Tanggal</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $end_date }}">
            </div>
            <div class="form-group">
                <label for="sos_status">Status SOS</label>
                <select id="sos_status" name="sos_status" class="form-control" style="min-width: 150px; height: 41px;">
                    <option value="">Semua</option>
                    <option value="aktif" {{ $sos_status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="tidak_aktif" {{ $sos_status === 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-filter">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if($start_date || $end_date || $sos_status)
                    <a href="{{ route('riwayat') }}" class="btn-reset">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Logs -->
    <div class="table-container">
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Waktu</th>
                    <th>Jarak Hambatan (cm)</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Status SOS</th>
                    <th>Status Sistem</th>
                    <th style="width: 120px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $index => $log)
                    <tr>
                        <td>{{ ($logs->currentPage() - 1) * $logs->perPage() + $index + 1 }}</td>
                        <td>{{ date('d/m/Y H:i:s', strtotime($log->recorded_at)) }}</td>
                        <td>
                            <strong style="color: {{ $log->distance_cm <= 100 && $log->distance_cm > 0 ? '#ef4444' : '#0f172a' }};">
                                {{ round($log->distance_cm) }} cm
                            </strong>
                        </td>
                        <td>{{ sprintf('%.6f', $log->latitude) }}</td>
                        <td>{{ sprintf('%.6f', $log->longitude) }}</td>
                        <td>
                            @if ($log->sos_status === 'Aktif')
                                <span class="badge danger">Aktif</span>
                            @else
                                <span class="badge neutral">Tidak Aktif</span>
                            @endif
                        </td>
                        <td>
                            @if ($log->sos_status === 'Aktif')
                                <span class="badge danger">Darurat</span>
                            @else
                                <span class="badge success">Normal</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                <a href="https://www.google.com/maps?q={{ $log->latitude }},{{ $log->longitude }}" target="_blank" class="btn-action-map">
                                    <i class="fa-solid fa-map-location-dot"></i> Lihat Peta
                                </a>
                                <form action="{{ route('riwayat.delete', $log->id_sensor) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus log data ini?')" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action-delete">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            <i class="fa-regular fa-folder-open" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                            Belum ada data log perekaman.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($logs->total() > 0)
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan {{ ($logs->currentPage() - 1) * $logs->perPage() + 1 }} - 
                {{ min($logs->currentPage() * $logs->perPage(), $logs->total()) }} dari 
                {{ $logs->total() }} data
            </div>
            
            <div class="pagination-links">
                {{-- Previous Page Link --}}
                @if ($logs->onFirstPage())
                    <span class="disabled">&laquo;</span>
                @else
                    <a href="{{ $logs->previousPageUrl() . ($start_date ? '&start_date='.$start_date : '') . ($end_date ? '&end_date='.$end_date : '') }}">&laquo;</a>
                @endif

                {{-- Page Links --}}
                @php
                    $startPage = max(1, $logs->currentPage() - 2);
                    $endPage = min($logs->lastPage(), $logs->currentPage() + 2);
                @endphp

                @if ($startPage > 1)
                    <a href="{{ $logs->url(1) . ($start_date ? '&start_date='.$start_date : '') . ($end_date ? '&end_date='.$end_date : '') }}">1</a>
                    @if ($startPage > 2)
                        <span class="disabled">...</span>
                    @endif
                @endif

                @foreach (range($startPage, $endPage) as $page)
                    @if ($page == $logs->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $logs->url($page) . ($start_date ? '&start_date='.$start_date : '') . ($end_date ? '&end_date='.$end_date : '') }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($endPage < $logs->lastPage())
                    @if ($endPage < $logs->lastPage() - 1)
                        <span class="disabled">...</span>
                    @endif
                    <a href="{{ $logs->url($logs->lastPage()) . ($start_date ? '&start_date='.$start_date : '') . ($end_date ? '&end_date='.$end_date : '') }}">{{ $logs->lastPage() }}</a>
                @endif

                {{-- Next Page Link --}}
                @if ($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() . ($start_date ? '&start_date='.$start_date : '') . ($end_date ? '&end_date='.$end_date : '') }}">&raquo;</a>
                @else
                    <span class="disabled">&raquo;</span>
                @endif
            </div>
        </div>
    @endif

@endsection
