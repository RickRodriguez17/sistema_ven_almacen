@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Reportes</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Reportes</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row g-3">
      @php
        $cards = [
          ['route' => 'reportes.ventas-diarias', 'icon' => 'bi-calendar-day', 'title' => 'Ventas diarias', 'desc' => 'Detalle de ventas de un día específico'],
          ['route' => 'reportes.ventas-rango', 'icon' => 'bi-calendar-range', 'title' => 'Ventas por rango', 'desc' => 'Reporte entre dos fechas'],
          ['route' => 'reportes.productos-vendidos', 'icon' => 'bi-trophy', 'title' => 'Productos vendidos', 'desc' => 'Ranking de productos en un periodo'],
          ['route' => 'reportes.stock-bajo', 'icon' => 'bi-exclamation-triangle', 'title' => 'Stock bajo', 'desc' => 'Productos por debajo del mínimo'],
          ['route' => 'reportes.ingresos-dia', 'icon' => 'bi-graph-up', 'title' => 'Ingresos por día', 'desc' => 'Evolución diaria de ingresos'],
          ['route' => 'reportes.cierres', 'icon' => 'bi-cash-stack', 'title' => 'Cierres de caja', 'desc' => 'Historial de cierres por cajero'],
        ];
      @endphp
      @foreach($cards as $c)
        <div class="col-md-6 col-lg-4">
          <a href="{{ route($c['route']) }}" class="text-decoration-none">
            <div class="card h-100 report-card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="report-icon"><i class="bi {{ $c['icon'] }}"></i></div>
                  <div class="ms-3">
                    <h5 class="card-title mb-1">{{ $c['title'] }}</h5>
                    <small class="text-muted">{{ $c['desc'] }}</small>
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
      @endforeach
    </div>
  </section>
</main>
@endsection
