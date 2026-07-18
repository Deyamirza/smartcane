<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Smart Cane Monitoring</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #00a896;
            --primary-dark: #008f80;
            --primary-light: #e6f7f4;
            --bg-color: #f8fafc;
            --sidebar-width: 260px;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03), 0 0 0 1px rgba(0, 0, 0, 0.03);
            --card-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.03);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --success-color: #10b981;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar Container */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #05232d 0%, #0d3a47 100%);
            color: #ffffff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .brand-logo-circle {
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #ffffff;
            flex-shrink: 0;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.5px;
            color: #ffffff;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-weight: 600;
            font-size: 9px;
            letter-spacing: 0.5px;
            color: var(--primary-color);
            line-height: 1.2;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
        }

        .sidebar-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar-item a:hover {
            background-color: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }

        .sidebar-item.active a {
            background-color: var(--primary-color);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 168, 150, 0.25);
        }

        /* Main Content Container */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Top Header */
        .top-header {
            background-color: #ffffff;
            height: 70px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 90;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        .toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            color: var(--text-main);
            cursor: pointer;
        }

        .header-title h1 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
        }

        .header-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-badge {
            position: relative;
            cursor: pointer;
            font-size: 20px;
            color: var(--text-muted);
            transition: color 0.2s;
        }

        .notification-badge:hover {
            color: var(--primary-color);
        }

        .notification-badge .badge-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--danger-color);
            color: white;
            font-size: 10px;
            font-weight: 700;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 8px;
            background-color: #f8fafc;
            border: 1px solid var(--border-color);
            transition: background-color 0.2s;
        }

        .user-dropdown:hover {
            background-color: #f1f5f9;
        }

        .user-dropdown img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-dropdown span {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-main);
        }

        .user-dropdown i {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Page Content Area */
        .content-area {
            padding: 30px;
            flex: 1;
        }

        /* Responsive Layouts */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
            }
            .toggle-sidebar {
                display: block;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo-circle">
                <i class="fa-solid fa-blind"></i>
            </div>
            <div class="brand-text">
                <span class="brand-title">SMART CANE</span>
                <span class="brand-subtitle">MONITORING SYSTEM</span>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li class="sidebar-item {{ Route::is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <i class="fa-solid fa-house"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="sidebar-item {{ Route::is('riwayat') ? 'active' : '' }}">
                <a href="{{ route('riwayat') }}">
                    <i class="fa-solid fa-file-lines"></i>
                    <span>Riwayat Data</span>
                </a>
            </li>
            <li class="sidebar-item {{ Route::is('sos') ? 'active' : '' }}">
                <a href="{{ route('sos') }}">
                    <i class="fa-solid fa-bell"></i>
                    <span>Status SOS</span>
                </a>
            </li>
            <li class="sidebar-item {{ Route::is('pengaturan') ? 'active' : '' }}">
                <a href="{{ route('pengaturan') }}">
                    <i class="fa-solid fa-gear"></i>
                    <span>Pengaturan</span>
                </a>
            </li>
            <li class="sidebar-item" style="margin-top: auto; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 10px;">
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: #fca5a5;">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Header -->
        <header class="top-header">
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="toggle-sidebar" id="sidebarCollapse">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="header-title">
                    <h1>Smart Cane Monitoring System</h1>
                </div>
            </div>

            <div class="header-profile">
                <!-- SOS Notification Bell -->
                <a href="{{ route('sos') }}" class="notification-badge" id="sos-bell" style="margin-right: 5px;">
                    <i class="fa-solid fa-bell"></i>
                    <span class="badge-count" style="display: none;" id="sos-badge-count">0</span>
                </a>

                <div class="user-dropdown">
                    <span style="color: var(--text-muted); font-size: 13px; font-weight: 500;">Akun Pengguna</span>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->username) }}&background=00a896&color=fff" alt="Avatar">
                    <i class="fa-solid fa-chevron-down" style="font-size: 10px; color: var(--text-muted);"></i>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="content-area">
            @yield('content')
        </main>
    </div>

    <script>
        // Responsive sidebar toggler
        const sidebar = document.getElementById('sidebar');
        const sidebarCollapse = document.getElementById('sidebarCollapse');

        if(sidebarCollapse) {
            sidebarCollapse.addEventListener('click', (e) => {
                e.stopPropagation();
                sidebar.classList.toggle('active');
            });
            
            document.addEventListener('click', (e) => {
                if (sidebar.classList.contains('active') && !sidebar.contains(e.target) && e.target !== sidebarCollapse) {
                    sidebar.classList.remove('active');
                }
            });
        }

        // Set active menu item on client-side
        function updateActiveMenu() {
            if (window.location.pathname.endsWith('/dashboard') || window.location.pathname === '/') {
                document.querySelectorAll('.sidebar-item').forEach(li => li.classList.remove('active'));
                const dashboardMenu = document.querySelector('a[href="{{ route("dashboard") }}"]');
                if (dashboardMenu) {
                    dashboardMenu.parentElement.classList.add('active');
                }
            }
        }
        window.addEventListener('load', updateActiveMenu);
    </script>
    
    @yield('scripts')
</body>
</html>
