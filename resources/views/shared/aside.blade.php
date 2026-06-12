@php
  $user = auth()->user();
  $rol = $user?->rol;
  $esAdmin = $user?->esAdmin();
  $esCajero = $user?->esCajero();
  $esAlmacen = $user?->esAlmacen();
@endphp
<aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('home') ? '' : 'collapsed' }}" href="{{ route('home') }}">
        <i class="bi bi-grid"></i><span>Dashboard</span>
      </a>
    </li>

    @if($esAdmin || $esCajero)
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#ventas-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-cart-check"></i><span>Ventas</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="ventas-nav" class="nav-content collapse {{ request()->is('ventas*') || request()->is('detalle*') || request()->is('cierres*') || request()->is('gastos*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
        <li><a href="{{ route('ventas-nueva') }}" class="{{ request()->routeIs('ventas-nueva') ? 'active' : '' }}"><i class="bi bi-cash-coin"></i><span>Nueva Venta (POS)</span></a></li>
        <li><a href="{{ route('ventas.pendientes') }}" class="{{ request()->routeIs('ventas.pendientes') ? 'active' : '' }}"><i class="bi bi-hourglass-split"></i><span>Pedidos pendientes</span></a></li>
        <li><a href="{{ route('detalle-venta') }}" class="{{ request()->routeIs('detalle-venta') ? 'active' : '' }}"><i class="bi bi-receipt"></i><span>Historial de Ventas</span></a></li>
        <li><a href="{{ route('gastos') }}" class="{{ request()->routeIs('gastos*') ? 'active' : '' }}"><i class="bi bi-wallet2"></i><span>Gastos urgentes</span></a></li>
        <li><a href="{{ route('cierres') }}" class="{{ request()->routeIs('cierres') ? 'active' : '' }}"><i class="bi bi-cash-stack"></i><span>Cierres de Caja</span></a></li>
      </ul>
    </li>
    @endif

    @if($esAdmin || $esAlmacen)
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#almacen-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-boxes"></i><span>Almacén</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="almacen-nav" class="nav-content collapse {{ request()->is('productos*') || request()->is('categorias*') || request()->is('combos*') || request()->is('inventario*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
        <li><a href="{{ route('productos') }}" class="{{ request()->routeIs('productos*') ? 'active' : '' }}"><i class="bi bi-box-seam"></i><span>Productos</span></a></li>
        <li><a href="{{ route('combos') }}" class="{{ request()->routeIs('combos*') ? 'active' : '' }}"><i class="bi bi-stars"></i><span>Combos / Promos</span></a></li>
        <li><a href="{{ route('categorias') }}" class="{{ request()->routeIs('categorias*') ? 'active' : '' }}"><i class="bi bi-tags"></i><span>Categorías</span></a></li>
        <li><a href="{{ route('inventario') }}" class="{{ request()->routeIs('inventario') ? 'active' : '' }}"><i class="bi bi-arrow-left-right"></i><span>Movimientos</span></a></li>
        <li><a href="{{ route('inventario.stock-bajo') }}" class="{{ request()->routeIs('inventario.stock-bajo') ? 'active' : '' }}"><i class="bi bi-exclamation-triangle"></i><span>Stock Bajo</span></a></li>
      </ul>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#reportes-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-file-earmark-bar-graph"></i><span>Reportes</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="reportes-nav" class="nav-content collapse {{ request()->is('reportes*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
        <li><a href="{{ route('reportes') }}" class="{{ request()->routeIs('reportes') ? 'active' : '' }}"><i class="bi bi-grid-3x3-gap"></i><span>Inicio</span></a></li>
        <li><a href="{{ route('reportes.ventas-diarias') }}" class="{{ request()->routeIs('reportes.ventas-diarias') ? 'active' : '' }}"><i class="bi bi-calendar-day"></i><span>Ventas diarias</span></a></li>
        <li><a href="{{ route('reportes.ventas-rango') }}" class="{{ request()->routeIs('reportes.ventas-rango') ? 'active' : '' }}"><i class="bi bi-calendar-range"></i><span>Ventas por rango</span></a></li>
        <li><a href="{{ route('reportes.productos-vendidos') }}" class="{{ request()->routeIs('reportes.productos-vendidos') ? 'active' : '' }}"><i class="bi bi-trophy"></i><span>Productos vendidos</span></a></li>
        <li><a href="{{ route('reportes.stock-bajo') }}" class="{{ request()->routeIs('reportes.stock-bajo') ? 'active' : '' }}"><i class="bi bi-exclamation-triangle"></i><span>Stock bajo</span></a></li>
        <li><a href="{{ route('reportes.ingresos-dia') }}" class="{{ request()->routeIs('reportes.ingresos-dia') ? 'active' : '' }}"><i class="bi bi-graph-up"></i><span>Ingresos por día</span></a></li>
        <li><a href="{{ route('reportes.cierres') }}" class="{{ request()->routeIs('reportes.cierres') ? 'active' : '' }}"><i class="bi bi-cash-stack"></i><span>Historial cierres</span></a></li>
      </ul>
    </li>
    @endif

    <li class="nav-item">
      <a class="nav-link collapsed" href="{{ route('clientes') }}">
        <i class="bi bi-people"></i><span>Clientes</span>
      </a>
    </li>

    @if($esAdmin)
    <li class="nav-item">
      <a class="nav-link collapsed" href="{{ route('usuarios') }}">
        <i class="bi bi-person-badge"></i><span>Usuarios</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('empresa.*') ? '' : 'collapsed' }}" href="{{ route('empresa.edit') }}">
        <i class="bi bi-building"></i><span>Empresa</span>
      </a>
    </li>
    @endif

  </ul>

</aside>
