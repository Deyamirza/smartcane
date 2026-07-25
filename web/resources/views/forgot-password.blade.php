<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Smart Cane Monitoring System</title>
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
            --border-color: #e2e8f0;
            --error-color: #ef4444;
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

        .container {
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
        .sidebar {
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
            color: #94a3b8;
        }

        /* Form Split */
        .form-container {
            flex: 1.2;
            padding: 50px 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card-icon-badge {
            width: 54px;
            height: 54px;
            background: var(--primary-light);
            color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 24px;
        }

        .title-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .title-section h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .title-section p {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Form styling */
        .form-group {
            position: relative;
            margin-bottom: 20px;
            width: 100%;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 46px;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            font-size: 14px;
            color: var(--text-color);
            outline: none;
            background: #f8fafc;
            transition: border-color 0.2s, background-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(0, 168, 150, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-control:focus + .input-icon {
            color: var(--primary-color);
        }

        /* Button styling */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 168, 150, 0.15);
            transition: background-color 0.2s, transform 0.1s, box-shadow 0.2s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 6px 16px rgba(0, 168, 150, 0.25);
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        /* Error Alerts */
        .error-message {
            background-color: #fef2f2;
            border-left: 4px solid var(--error-color);
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .back-to-login {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .back-to-login a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .back-to-login a:hover {
            color: var(--primary-dark);
        }

        .footer {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                min-height: auto;
            }
            .sidebar {
                padding: 40px 20px;
            }
            .sidebar-desc {
                display: none;
            }
            .form-container {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Sidebar Split -->
        <div class="sidebar">
            <div class="logo-container">
                <i class="fa-solid fa-blind"></i>
            </div>
            <h1 class="sidebar-title">Smart Cane</h1>
            <p class="sidebar-subtitle">Monitoring System</p>
            <hr style="width: 40px; border: 1.5px solid rgba(255, 255, 255, 0.15); margin-bottom: 20px; border-radius: 2px;">
            <p class="sidebar-desc">Sistem Pemulihan Akun Mandiri Pendamping Tongkat Pintar</p>
        </div>

        <!-- Form Split -->
        <div class="form-container">
            <div class="card">
                <div class="card-icon-badge">
                    <i class="fa-solid fa-key"></i>
                </div>
                
                <div class="title-section">
                    <h2>Lupa Kata Sandi?</h2>
                    <p>Masukkan Username dan Email terdaftar Anda untuk memverifikasi kepemilikan akun.</p>
                </div>

                <!-- Error Alerts -->
                @if ($errors->any())
                    <div class="error-message">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('password.verify') }}" method="POST" style="width: 100%;">
                    @csrf
                    
                    <div class="form-group">
                        <input type="text" name="username" class="form-control" placeholder="Username" required value="{{ old('username') }}" autocomplete="off">
                        <i class="fa-regular fa-user input-icon"></i>
                    </div>

                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Alamat Email" required value="{{ old('email') }}" autocomplete="off">
                        <i class="fa-regular fa-envelope input-icon"></i>
                    </div>

                    <button type="submit" class="btn-submit">VERIFIKASI AKUN</button>
                </form>

                <div class="back-to-login">
                    Kembali ke halaman <a href="{{ route('login') }}">Login</a>
                </div>

                <div class="footer">
                    &copy; 2024 Smart Cane Monitoring System
                </div>
            </div>
        </div>
    </div>

</body>
</html>
