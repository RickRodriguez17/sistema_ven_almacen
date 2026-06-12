@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">

  <div class="pagetitle d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h1>Hola, {{ auth()->user()->name }}</h1>

      <small class="text-muted">
        <i class="bi bi-calendar3"></i>
        <span id="fechaHora"></span>
        ·
        <span class="badge bg-primary">
          {{ \App\Models\User::ROLES[auth()->user()->rol] ?? auth()->user()->rol }}
        </span>
      </small>
    </div>

    <nav>
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">{{ $negocio['nombre'] }}</li>
        <li class="breadcrumb-item active">Inicio</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">

    @if(auth()->user()->esAdmin() || auth()->user()->esCajero())
    <div class="row g-3 mb-2">

      <div class="col-md-6">
        @if($cierreAbierto)

          <a href="{{ route('ventas-nueva') }}" class="quick-action quick-action-success d-block">

            <div class="d-flex align-items-center">

              <div class="qa-icon">
                <i class="bi bi-cart-check"></i>
              </div>

              <div class="ms-3">
                <h3 class="mb-0">Continuar vendiendo</h3>

                <small>
                  Turno abierto desde
                  {{ $cierreAbierto->fecha_apertura->format('H:i') }}
                </small>
              </div>

              <i class="bi bi-arrow-right ms-auto fs-3"></i>

            </div>

          </a>

        @else

          <a href="{{ route('cierres.iniciar.form') }}" class="quick-action quick-action-primary d-block">

            <div class="d-flex align-items-center">

              <div class="qa-icon">
                <i class="bi bi-play-circle"></i>
              </div>

              <div class="ms-3">
                <h3 class="mb-0">Iniciar ventas</h3>
                <small>Abrir caja y empezar el turno</small>
              </div>

              <i class="bi bi-arrow-right ms-auto fs-3"></i>

            </div>

          </a>

        @endif
      </div>

      <div class="col-md-6">

        @if($cierreAbierto)

          <a href="{{ route('cierres.cerrar.form') }}" class="quick-action quick-action-danger d-block">

            <div class="d-flex align-items-center">

              <div class="qa-icon">
                <i class="bi bi-stop-circle"></i>
              </div>

              <div class="ms-3">
                <h3 class="mb-0">Cerrar ventas</h3>
                <small>Generar resumen y cuadre del turno</small>
              </div>

              <i class="bi bi-arrow-right ms-auto fs-3"></i>

            </div>

          </a>

        @else

          <a href="{{ route('cierres') }}" class="quick-action quick-action-secondary d-block">

            <div class="d-flex align-items-center">

              <div class="qa-icon">
                <i class="bi bi-clock-history"></i>
              </div>

              <div class="ms-3">
                <h3 class="mb-0">Historial de cierres</h3>
                <small>Ver cierres anteriores</small>
              </div>

              <i class="bi bi-arrow-right ms-auto fs-3"></i>

            </div>

          </a>

        @endif

      </div>

    </div>
    @endif

    <div class="row">

      <div class="col-xxl-3 col-md-6">

        <div class="card info-card sales-card">

          <div class="card-body">

            <h5 class="card-title">
              Ventas <span>| Hoy</span>
            </h5>

            <div class="d-flex align-items-center">

              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-cart"></i>
              </div>

              <div class="ps-3">

                <h6>
                  {{ $negocio['moneda'] }}
                  {{ number_format($ventasHoy, 2) }}
                </h6>

                <span class="text-muted small pt-2 ps-1">
                  {{ $cantidadVentasHoy }} ventas
                </span>

              </div>

            </div>

          </div>

        </div>

      </div>

      <div class="col-xxl-3 col-md-6">

        <div class="card info-card revenue-card">

          <div class="card-body">

            <h5 class="card-title">
              Productos <span>| Vendidos hoy</span>
            </h5>

            <div class="d-flex align-items-center">

              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-bag-check"></i>
              </div>

              <div class="ps-3">
                <h6>{{ $productosVendidosHoy }} unidades</h6>
              </div>

            </div>

          </div>

        </div>

      </div>

      @if(!auth()->user()->esCajero())
      <div class="col-xxl-3 col-md-6">

        <div class="card info-card customers-card">

          <div class="card-body">

            <h5 class="card-title">
              Ventas <span>| Este mes</span>
            </h5>

            <div class="d-flex align-items-center">

              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-currency-dollar"></i>
              </div>

              <div class="ps-3">

                <h6>
                  {{ $negocio['moneda'] }}
                  {{ number_format($ventasMes, 2) }}
                </h6>

              </div>

            </div>

          </div>

        </div>

      </div>
      @endif

      <div class="col-xxl-3 col-md-6">

        <div class="card info-card" style="background: linear-gradient(135deg, #fff3cd 0%, #ffe39c 100%);">

          <div class="card-body">

            <h5 class="card-title">
              Stock bajo <span>| Alerta</span>
            </h5>

            <div class="d-flex align-items-center">

              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background:#ffc107;">
                <i class="bi bi-exclamation-triangle text-white"></i>
              </div>

              <div class="ps-3">

                <h6>{{ $stockBajo->count() }} productos</h6>

                @if(auth()->user()->esAdmin() || auth()->user()->esAlmacen())
                  <a href="{{ route('inventario.stock-bajo') }}" class="small">
                    Ver detalles
                  </a>
                @endif

              </div>

            </div>

          </div>

        </div>

      </div>

      <div class="col-12 col-lg-8">

        <div class="card">

          <div class="card-body">

            <h5 class="card-title">
              Ventas últimos 7 días
            </h5>

            <div id="chartVentas" style="min-height: 320px;"></div>

          </div>

        </div>

      </div>

      <div class="col-12 col-lg-4">

        <div class="card">

          <div class="card-body">

            <h5 class="card-title">
              Productos Vendidos Hoy
            </h5>

            @if($topProductos->isEmpty())

              <p class="text-muted small">
                Aún no hay ventas registradas hoy.
              </p>

            @else

              <ol class="list-group list-group-numbered" id="listProductosHoy">

                @foreach($topProductos as $idx => $tp)

                  <li class="list-group-item d-flex justify-content-between align-items-start {{ $idx >= 10 ? 'd-none productos-extra' : '' }}">

                    <div class="ms-2 me-auto">

                      <div class="fw-bold">
                        {{ $tp->nombre }}
                      </div>

                      <small class="text-muted">
                        {{ $tp->total_vendido }} unidades
                      </small>

                    </div>

                    <span class="badge bg-primary rounded-pill">
                      {{ $negocio['moneda'] }}
                      {{ number_format($tp->total_ingreso, 2) }}
                    </span>

                  </li>

                @endforeach

              </ol>

              @if($topProductos->count() > 10)
                <button type="button" class="btn btn-sm btn-link mt-2 w-100" id="btnVerMasProductos">
                  <i class="bi bi-chevron-down"></i> Ver todos ({{ $topProductos->count() }})
                </button>
              @endif

            @endif

          </div>

        </div>

      </div>

      @if($stockBajo->count())

      <div class="col-12">

        <div class="card">

          <div class="card-body">

            <h5 class="card-title">
              Productos con stock bajo
            </h5>

            <div class="table-responsive">

              <table class="table table-sm mb-0">

                <thead>
                  <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Stock</th>
                    <th>Mínimo</th>
                  </tr>
                </thead>

                <tbody>

                  @foreach($stockBajo as $p)

                  <tr>

                    <td>{{ $p->nombre }}</td>

                    <td>{{ $p->categoria?->nombre }}</td>

                    <td>
                      <span class="badge {{ $p->cantidad <= 0 ? 'badge-sin-stock' : 'badge-stock-bajo' }}">
                        {{ $p->cantidad }}
                      </span>
                    </td>

                    <td>{{ $p->stock_minimo }}</td>

                  </tr>

                  @endforeach

                </tbody>

              </table>

            </div>

          </div>

        </div>

      </div>

      @endif

    </div>

  </section>

</main>
@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', () => {

    function actualizarFechaHora() {

        const ahora = new Date();

        const opciones = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };

        const fechaFormateada = ahora.toLocaleDateString('es-BO', opciones);

        document.getElementById('fechaHora').innerText = fechaFormateada;
    }

    actualizarFechaHora();

    setInterval(actualizarFechaHora, 1000);

    const btnVerMas = document.getElementById('btnVerMasProductos');
    if (btnVerMas) {
        btnVerMas.addEventListener('click', function() {
            document.querySelectorAll('.productos-extra').forEach(el => el.classList.toggle('d-none'));
            const expanded = !document.querySelector('.productos-extra.d-none');
            this.innerHTML = expanded
                ? '<i class="bi bi-chevron-up"></i> Ver menos'
                : '<i class="bi bi-chevron-down"></i> Ver todos ({{ $topProductos->count() }})';
        });
    }

    const data = @json($ventasUltimos7Dias);

    const categorias = data.map(d => d.fecha);
    const totales = data.map(d => Number(d.total));

    new ApexCharts(document.querySelector("#chartVentas"), {

        chart: {
            type: 'area',
            height: 320,
            toolbar: {
                show: false
            }
        },

        series: [{
            name: 'Ventas {{ $negocio['moneda'] }}',
            data: totales
        }],

        xaxis: {
            categories: categorias
        },

        stroke: {
            curve: 'smooth',
            width: 2
        },

        colors: ['#EA3323'],

        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.5,
                opacityTo: 0.05
            }
        },

        dataLabels: {
            enabled: false
        }

    }).render();

});
</script>

@endpush