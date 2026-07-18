<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Smart Cane Monitoring System</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #ccfbf1;
            --bg-gradient: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%);
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            --text-color: #1f2937;
            --text-muted: #6b7280;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: #ffffff;
            width: 100%;
            max-width: 900px;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            display: flex;
            flex-direction: row;
            min-height: 580px;
        }

        /* Left Split Panel */
        .register-sidebar {
            flex: 1.1;
            background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
            color: #ffffff;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .register-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath fill-rule='evenodd' d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm56-76c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm76 14c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM39 17c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm54 51c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM46 68c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z'/%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.8;
        }

        .logo-container {
            font-size: 64px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.1);
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-title {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .sidebar-subtitle {
            font-size: 14px;
            font-weight: 300;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.8);
            max-width: 280px;
        }

        /* Right Panel */
        .register-form-container {
            flex: 1.2;
            padding: 40px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .form-header h2 {
            font-size: 24px;
            color: var(--text-color);
            margin-bottom: 8px;
            font-weight: 700;
        }

        .form-header p {
            font-size: 14px;
            color: var(--text-muted);
        }

        .form-group {
            margin-bottom: 15px;
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
            color: var(--text-color);
            background-color: white;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
        }

        .form-control:focus + i {
            color: var(--primary-color);
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            color: white;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(13, 148, 136, 0.3);
        }

        .error-message {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #b91c1c;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
        }

        .error-message i {
            margin-right: 8px;
            font-size: 16px;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .register-container {
                flex-direction: column;
            }
            .register-sidebar {
                padding: 30px 20px;
                min-height: 150px;
            }
            .logo-container {
                width: 60px;
                height: 60px;
                font-size: 30px;
                margin-bottom: 10px;
            }
            .sidebar-title {
                font-size: 20px;
            }
            .register-form-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="register-container">
        <!-- Sidebar Split -->
        <div class="register-sidebar">
            <div class="logo-container">
                <i class="fa-solid fa-walking-cane"></i>
            </div>
            <h1 class="sidebar-title">Smart Cane</h1>
            <p class="sidebar-subtitle">Sistem Monitoring Tongkat Pintar Berbasis Internet of Things (IoT)</p>
        </div>

        <!-- Form Split -->
        <div class="register-form-container">
            <div class="form-header">
                <h2>Daftar Akun Baru</h2>
                <p>Buat akun keluarga atau administrator baru.</p>
            </div>

            <!-- Error Alerts -->
            @if ($errors->any())
                <div class="error-message">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="form-group">
                    <input type="text" name="username" class="form-control" placeholder="Username" required value="{{ old('username') }}" autocomplete="off">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Alamat Email" required value="{{ old('email') }}" autocomplete="off">
                    <i class="fa-solid fa-envelope"></i>
                </div>

                <div class="form-group">
                    <select name="role" class="form-control" required style="padding-left: 45px;">
                        <option value="" disabled selected>Pilih Hak Akses / Role</option>
                        <option value="family" {{ old('role') === 'family' ? 'selected' : '' }}>Keluarga / Pendamping (Family)</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator (Admin)</option>
                    </select>
                    <i class="fa-solid fa-users-gear"></i>
                </div>

                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password (Minimal 6 karakter)" required>
                    <i class="fa-solid fa-lock"></i>
                </div>

                <div class="form-group">
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Konfirmasi Password" required>
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <button type="submit" class="btn-register">Daftar Akun</button>
            </form>

            <div class="login-link">
                Sudah memiliki akun? <a href="{{ route('login') }}">Login disini</a>
            </div>
        </div>
    </div>

</body>
</html>
