<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun | Hendra Otomotif</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/stisla@2.3.0/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/stisla@2.3.0/assets/css/components.css">
</head>
<body class="login-page">
    <main class="login-screen">
        <div class="login-panel">
            <div class="login-brand text-center mb-4"><i class="fas fa-wrench mr-2"></i>HENDRA OTOMOTIF</div>
            <div class="card card-primary login-card">
                <div class="card-header"><h4 class="w-100 text-center">Daftar Akun Pelanggan</h4></div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                    @endif
                    <form action="{{ route('register.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Nama</label>
                            <input id="name" name="name" class="form-control" value="{{ old('name') }}" maxlength="255" required autofocus autocomplete="name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label for="password">Kata sandi</label>
                            <input id="password" type="password" name="password" class="form-control" minlength="8" required autocomplete="new-password">
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Ulangi kata sandi</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" minlength="8" required autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-user-plus mr-1"></i>Daftar</button>
                    </form>
                    <div class="text-center mt-3"><a href="{{ route('login') }}">Sudah punya akun? Masuk</a></div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
