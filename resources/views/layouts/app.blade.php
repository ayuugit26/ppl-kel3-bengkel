<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Sistem Manajemen Bengkel</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/stisla@2.3.0/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/stisla@2.3.0/assets/css/components.css">
</head>

<body>
  <div id="app">
    <div class="main-wrapper">
      <div class="navbar-bg" style="height: 70px; background-color: #6777ef;"></div>
      
      <nav class="navbar navbar-expand-lg main-navbar">
        <form class="form-inline mr-auto">
          <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
          </ul>
          <span class="text-white font-weight-bold h5 mb-0">Sentosa Motor</span>
        </form>
      </nav>

      <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
          <div class="sidebar-brand">
            <a href="{{ route('bengkel.index') }}"><i class="fas fa-wrench text-primary mr-2"></i>SENTOSA MOTOR</a>
          </div>
          <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('bengkel.index') }}">BM</a>
          </div>
          <ul class="sidebar-menu mt-3">
            <li class="menu-header">Menu</li>
            <li class="{{ request()->routeIs('bengkel.index') ? 'active' : '' }}">
              <a class="nav-link" href="{{ route('bengkel.index') }}">
                <i class="fas fa-list-ol"></i> <span>Antrean</span>
              </a>
            </li>
            @auth
              <li class="{{ request()->routeIs('bengkel.admin') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('bengkel.admin') }}">
                  <i class="fas fa-tools"></i> <span>Admin & Mekanik</span>
                </a>
              </li>
              <li class="{{ request()->routeIs('bengkel.kasir', 'kasir.struk') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('bengkel.kasir') }}">
                  <i class="fas fa-cash-register"></i> <span>Kasir</span>
                </a>
              </li>
              <li>
                <form action="{{ route('logout') }}" method="POST" class="px-3 py-2">
                  @csrf
                  <button type="submit" class="btn btn-link nav-link p-0">
                    <i class="fas fa-sign-out-alt"></i> <span>Keluar</span>
                  </button>
                </form>
              </li>
            @else
              <li class="{{ request()->routeIs('login') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('login') }}">
                  <i class="fas fa-sign-in-alt"></i> <span>Login Petugas</span>
                </a>
              </li>
            @endauth
          </ul>
        </aside>
      </div>

      <div class="main-content">
        <section class="section">
          @yield('content')
        </section>
      </div>

      <footer class="main-footer">
        <div class="footer-left">
          Copyright &copy; 2026 <div class="bullet"></div> PPL Kelompok 3 Bengkel
        </div>
      </footer>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/stisla@2.3.0/assets/js/stisla.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/stisla@2.3.0/assets/js/scripts.js"></script>
</body>
</html>