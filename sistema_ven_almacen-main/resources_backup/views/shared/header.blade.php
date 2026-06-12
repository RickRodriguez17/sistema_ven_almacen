<header id="header" class="header fixed-top d-flex align-items-center">

  <div class="d-flex align-items-center justify-content-between">
    <a href="{{ route('home') }}" class="logo d-flex align-items-center">
      <i class="bi bi-shop fs-3 text-primary me-2"></i>
      @if(!empty($negocio['logo_path']))
        <img src="{{ asset('storage/' . $negocio['logo_path']) }}" alt="Logo" style="height:32px;" class="me-2">
      @endif
      <span class="d-none d-lg-block">{{ $negocio['nombre'] }}</span>
    </a>
    <i class="bi bi-list toggle-sidebar-btn"></i>
  </div>

  <nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">

      @auth
      @if(auth()->user()->esAdmin() || auth()->user()->esAlmacen())
      <li class="nav-item dropdown pe-3">
        <a class="nav-link nav-icon" href="{{ route('inventario.stock-bajo') }}" title="Stock bajo">
          <i class="bi bi-exclamation-triangle text-warning fs-5"></i>
        </a>
      </li>
      @endif

      <li class="nav-item dropdown pe-3">
        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle fs-3 text-primary"></i>
          <span class="d-none d-md-block dropdown-toggle ps-2">{{ Auth::user()->name }}</span>
        </a>

        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
          <li class="dropdown-header">
            <h6>{{ Auth::user()->name }}</h6>
            <span class="badge bg-primary">{{ \App\Models\User::ROLES[Auth::user()->rol] ?? Auth::user()->rol }}</span>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit" class="dropdown-item d-flex align-items-center">
                <i class="bi bi-box-arrow-right"></i>
                <span>Cerrar sesión</span>
              </button>
            </form>
          </li>
        </ul>
      </li>
      @endauth

    </ul>
  </nav>

</header>
