@extends('layouts.app')

@section('title', 'Status SOS')

@section('styles')
<style>
    .sos-header {
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .sos-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-main);
    }

    .sos-header p {
        font-size: 14px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    /* Message Alert */
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

    /* SOS Events List Table */
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
        background-color: #991b1b; /* Red header for SOS warnings */
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
        background-color: #fcf8f8;
    }

    /* Badge SOS */
    .badge-sos {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-sos.active {
        background-color: #fee2e2;
        color: #ef4444;
        animation: flash-red 1s infinite alternate;
    }

    .badge-sos.resolved {
        background-color: #d1fae5;
        color: #059669;
    }

    @keyframes flash-red {
        0% { opacity: 0.6; }
        100% { opacity: 1; }
    }

    .btn-resolve {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .btn-resolve:hover {
        background-color: var(--primary-dark);
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
    <div class="sos-header">
        <div>
            <h2>Status & Kejadian SOS</h2>
            <p>Riwayat pemicuan tombol panik/SOS darurat pada Smart Cane.</p>
        </div>
        <div>
            <form action="{{ route('sos.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua data kejadian SOS? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                <button type="submit" class="btn-clear-all" style="background-color: var(--danger-color); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background-color 0.2s;">
                    <i class="fa-solid fa-trash-can"></i> Hapus Semua Kejadian SOS
                </button>
            </form>
        </div>
    </div>

    <!-- Success Message -->
    @if (session('success'))
        <div class="alert-success">
            <i class="fa-solid fa-circle-check" style="margin-right: 8px;"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Table SOS Alerts -->
    <div class="table-container">
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Waktu Ditekan</th>
                    <th>Waktu Selesai</th>
                    <th>Koordinat Saat SOS</th>
                    <th>Status Kejadian</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sosEvents as $index => $event)
                    <tr>
                        <td>{{ ($sosEvents->currentPage() - 1) * $sosEvents->perPage() + $index + 1 }}</td>
                        <td>{{ date('d/m/Y H:i:s', strtotime($event->triggered_at)) }}</td>
                        <td>
                            {{ $event->resolved_at ? date('d/m/Y H:i:s', strtotime($event->resolved_at)) : '-' }}
                        </td>
                        <td>
                            <a href="https://www.openstreetmap.org/?mlat={{ $event->latitude }}&mlon={{ $event->longitude }}#map=18/{{ $event->latitude }}/{{ $event->longitude }}" target="_blank" style="color: #2563eb; text-decoration: none; font-weight: 500;">
                                <i class="fa-solid fa-map-pin"></i> {{ sprintf('%.6f, %.6f', $event->latitude, $event->longitude) }}
                            </a>
                        </td>
                        <td>
                            @if ($event->status === 'active')
                                <span class="badge-sos active">Menunggu Penanganan</span>
                            @else
                                <span class="badge-sos resolved">Telah Ditangani</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                @if ($event->status === 'active')
                                    <form action="{{ route('sos.resolve', $event->id_sos) }}" method="POST" style="margin: 0; display: inline;">
                                        @csrf
                                        <button type="submit" class="btn-resolve">
                                            <i class="fa-solid fa-check"></i> Selesaikan
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size: 13px; color: var(--text-muted); font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                                        Selesai <i class="fa-solid fa-circle-check" style="color: var(--success-color);"></i>
                                    </span>
                                @endif
                                <form action="{{ route('sos.delete', $event->id_sos) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kejadian SOS ini?')" style="margin: 0; display: inline;">
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
                        <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            <i class="fa-solid fa-shield-halved" style="font-size: 32px; margin-bottom: 10px; display: block; color: var(--success-color);"></i>
                            Aman! Tidak ada riwayat kejadian darurat SOS.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($sosEvents->total() > 0)
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan {{ ($sosEvents->currentPage() - 1) * $sosEvents->perPage() + 1 }} - 
                {{ min($sosEvents->currentPage() * $sosEvents->perPage(), $sosEvents->total()) }} dari 
                {{ $sosEvents->total() }} data
            </div>
            
            <div class="pagination-links">
                {{-- Previous Page Link --}}
                @if ($sosEvents->onFirstPage())
                    <span class="disabled">&laquo;</span>
                @else
                    <a href="{{ $sosEvents->previousPageUrl() }}">&laquo;</a>
                @endif

                {{-- Page Links --}}
                @foreach ($sosEvents->getUrlRange(1, $sosEvents->lastPage()) as $page => $url)
                    @if ($page == $sosEvents->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($sosEvents->hasMorePages())
                    <a href="{{ $sosEvents->nextPageUrl() }}">&raquo;</a>
                @else
                    <span class="disabled">&raquo;</span>
                @endif
            </div>
        </div>
    @endif

@endsection
