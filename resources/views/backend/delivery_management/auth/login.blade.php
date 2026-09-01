<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delivery Man Login - {{ gen_setting()->site_title ?? 'BanglaSoft' }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header img {
            max-height: 60px;
            margin-bottom: 15px;
        }
        .login-header h2 {
            color: #333;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 5px;
            padding: 12px;
            border: 1px solid #ddd;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            font-weight: 600;
        }
        .btn-login:hover {
            background-color: #0056b3;
        }
        .alert {
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                @if(gen_setting()->site_logo)
                    <img src="{{ asset('logo/' . gen_setting()->site_logo) }}" alt="Logo">
                @endif
                <h2>Delivery Man Login</h2>
                <p class="text-muted">Sign in to access your dashboard</p>
            </div>

            @if(session('not_permitted'))
                <div class="alert alert-danger">
                    {{ session('not_permitted') }}
                </div>
            @endif

            <form method="POST" action="{{ route('delivery-man.login.post') }}">
                @csrf
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember Me</label>
                </div>
                <button type="submit" class="btn btn-login">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
