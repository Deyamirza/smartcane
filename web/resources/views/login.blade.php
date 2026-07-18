<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Cane Monitoring System</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #00a896;
            --primary-dark: #008f80;
            --primary-light: #e6f7f4;
            --bg-gradient: linear-gradient(135deg, #eef9f8 0%, #def3f0 100%);
            --card-shadow: 0 15px 35px rgba(0, 0, 0, 0.06), 0 5px 15px rgba(0, 0, 0, 0.04);
            --text-color: #0f172a;
            --text-muted: #64748b;
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

        .login-container {
            background: #ffffff;
            width: 100%;
            max-width: 950px;
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            display: flex;
            flex-direction: row;
            min-height: 550px;
        }

        /* Left Split Panel */
        .login-sidebar {
            flex: 1;
            background: linear-gradient(180deg, #05232d 0%, #0d3a47 100%);
            color: #ffffff;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .logo-container {
            width: 90px;
            height: 90px;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 44px;
            color: #ffffff;
            margin-bottom: 24px;
            box-shadow: 0 8px 20px rgba(0, 168, 150, 0.3);
        }

        .sidebar-title {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            color: #ffffff;
            text-transform: uppercase;
        }

        .sidebar-subtitle {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: var(--primary-color);
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .sidebar-desc {
            font-size: 13px;
            font-weight: 400;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.7);
            max-width: 300px;
        }

        /* Right Panel */
        .login-form-container {
            flex: 1.2;
            background-color: #f0fbf9;
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 35px 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card-lock-badge {
            width: 54px;
            height: 54px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 168, 150, 0.25);
        }

        .welcome-back {
            text-align: center;
            margin-bottom: 25px;
        }

        .welcome-back h2 {
            font-size: 20px;
            color: var(--text-color);
            margin-bottom: 6px;
            font-weight: 700;
        }

        .welcome-back p {
            font-size: 13px;
            color: var(--text-muted);
        }

        .form-group {
            margin-bottom: 18px;
            position: relative;
            width: 100%;
        }

        .form-group i.input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            transition: color 0.3s;
        }

        .form-group i.toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            cursor: pointer;
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 13px 40px 13px 45px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
            color: var(--text-color);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 168, 150, 0.1);
        }

        .form-control:focus + i.input-icon {
            color: var(--primary-color);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 13px;
            width: 100%;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            cursor: pointer;
            color: var(--text-color);
        }

        .checkbox-container input {
            margin-right: 8px;
            accent-color: var(--primary-color);
            width: 16px;
            height: 16px;
        }

        .forgot-password {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.3s;
        }

        .forgot-password:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: var(--primary-color);
            border: none;
            color: white;
            font-size: 15px;
            font-weight: 700;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(0, 168, 150, 0.15);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 6px 15px rgba(0, 168, 150, 0.25);
        }

        .error-message {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #b91c1c;
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 13px;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .error-message i {
            margin-right: 8px;
            font-size: 15px;
        }

        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 11px;
            color: var(--text-muted);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            .login-sidebar {
                padding: 40px 20px;
                min-height: 220px;
            }
            .logo-container {
                width: 70px;
                height: 70px;
                font-size: 34px;
                margin-bottom: 15px;
            }
            .sidebar-title {
                font-size: 20px;
            }
            .sidebar-subtitle {
                font-size: 13px;
                margin-bottom: 10px;
            }
            .sidebar-desc {
                display: none;
            }
            .login-form-container {
                padding: 30px 20px;
            }
            .login-card {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Sidebar Split -->
        <div class="login-sidebar">
            <div class="logo-container">
                <i class="fa-solid fa-blind"></i>
            </div>
            <h1 class="sidebar-title">Smart Cane</h1>
            <p class="sidebar-subtitle">Monitoring System</p>
            <hr style="width: 40px; border: 1.5px solid rgba(255, 255, 255, 0.15); margin-bottom: 20px; border-radius: 2px;">
            <p class="sidebar-desc">Sistem Monitoring Tongkat Pintar Berbasis Internet of Things (IoT)</p>
        </div>

        <!-- Form Split -->
        <div class="login-form-container">
            <div class="login-card">
                <div class="card-lock-badge">
                    <i class="fa-solid fa-lock"></i>
                </div>
                
                <div class="welcome-back">
                    <h2>Selamat Datang Kembali!</h2>
                    <p>Silakan login untuk melanjutkan</p>
                </div>

                <!-- Error Alerts -->
                @if ($errors->any())
                    <div class="error-message">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" style="width: 100%;">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="username" class="form-control" placeholder="Username" required value="{{ old('username') }}" autocomplete="off">
                        <i class="fa-regular fa-user input-icon"></i>
                    </div>

                    <div class="form-group">
                        <input type="password" id="password-field" name="password" class="form-control" placeholder="Password" required>
                        <i class="fa-regular fa-lock input-icon"></i>
                        <i class="fa-regular fa-eye-slash toggle-password" onclick="togglePasswordVisibility()"></i>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-container">
                            <input type="checkbox" name="remember">
                            <span>Ingat saya</span>
                        </label>
                        <a href="#" class="forgot-password" onclick="alert('Silakan hubungi Administrator untuk mereset kata sandi Anda.')">Lupa password?</a>
                    </div>

                    <button type="submit" class="btn-login">LOGIN</button>
                </form>

                <div style="text-align: center; margin-top: 15px; font-size: 13px; color: var(--text-muted);">
                    Belum memiliki akun? <a href="{{ route('register') }}" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Daftar disini</a>
                </div>

                <div class="footer">
                    &copy; 2024 Smart Cane Monitoring System
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password-field');
            const toggleIcon = document.querySelector('.toggle-password');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>
