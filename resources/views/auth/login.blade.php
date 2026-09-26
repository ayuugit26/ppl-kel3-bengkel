<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Petugas | Sentosa Motor</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/stisla@2.3.0/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/stisla@2.3.0/assets/css/components.css">
    <style>
        body.login-page {
            min-height: 100vh;
            background: #f4f6f9;
        }

        .login-screen {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .login-panel {
            width: 100%;
            max-width: 420px;
        }

        .login-brand {
            margin-bottom: 28px;
            color: #34395e;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
        }

        .login-brand i {
            margin-right: 8px;
            color: #6777ef;
        }

        .login-card {
            margin-bottom: 0;
        }

        .login-footer {
            margin-top: 24px;
            color: #98a6ad;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body class="login-page">
    <main class="login-screen">
        <div class="login-panel">
            <div class="login-brand"><i class="fas fa-wrench"></i>SENTOSA MOTOR</div>

            <div class="card card-primary login-card">
                <div class="card-header">
                    <h4 class="w-100 text-center">Login Petugas</h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form action="{{ route('login.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus autocomplete="username">
                        </div>
                        <div class="form-group">
                            <label for="password">Kata sandi</label>
                            <input id="password" type="password" name="password" class="form-control" required autocomplete="current-password">
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input id="remember" type="checkbox" name="remember" value="1" class="custom-control-input">
                                <label class="custom-control-label" for="remember">Ingat saya</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt mr-1"></i>Masuk
                        </button>
                    </form>
                </div>
            </div>

            <div class="login-footer">Copyright &copy; 2026 &middot; PPL Kelompok 3 Bengkel</div>
        </div>
    </main>
</body>
</html>
