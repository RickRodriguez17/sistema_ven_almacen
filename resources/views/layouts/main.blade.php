<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('titulo', config('negocio.nombre'))</title>

  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css2?family=Coiny&family=Mulish:wght@300;400;600;700;800&display=swap" rel="stylesheet">

  <link href="{{ asset('NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('NiceAdmin/assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
  <link href="{{ asset('NiceAdmin/assets/vendor/simple-datatables/style.css') }}" rel="stylesheet">

  <link href="{{ asset('NiceAdmin/assets/css/style.css') }}" rel="stylesheet">
  <link href="{{ asset('css/tema.css') }}" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" referrerpolicy="no-referrer" />

  <style>
    :root {
      --color-primario: #4154f1;
      --color-acento: #ff6b35;
      --color-fondo: #f6f9ff;
    }
    .btn-pos {
      background: linear-gradient(135deg, var(--color-acento), #ff8c5a);
      color: #fff;
      border: none;
    }
    .btn-pos:hover { color: #fff; opacity: 0.92; }
    .badge-stock-bajo { background: #ffc107; color: #000; }
    .badge-sin-stock { background: #dc3545; }
    .pos-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; }
    @media (max-width: 991px) { .pos-grid { grid-template-columns: 1fr; } }
    .producto-card {
      cursor: pointer;
      transition: transform 0.15s ease, box-shadow 0.15s ease;
      border: 1px solid #e6e9f4;
      border-radius: 12px;
      overflow: hidden;
      background: #fff;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    .producto-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(65,84,241,0.15); }
    .producto-card.sin-stock { opacity: 0.55; cursor: not-allowed; }
    .producto-card .img-wrap {
      aspect-ratio: 4/3;
      background: #f6f9ff center/cover no-repeat;
      display: flex; align-items: center; justify-content: center;
      font-size: 2.5rem; color: #c8cce3;
    }
    .producto-card .body { padding: 0.75rem; flex: 1; display: flex; flex-direction: column; }
    .producto-card .nombre { font-weight: 600; font-size: 0.95rem; color: #012970; line-height: 1.2; }
    .producto-card .precio { font-size: 1.15rem; color: var(--color-acento); font-weight: 700; margin-top: 0.4rem; }
    .producto-card .stock { font-size: 0.78rem; color: #666; }
    .carrito-vacio { color: #aab; text-align: center; padding: 2rem 0.5rem; }
    .carrito-item { display: flex; gap: 0.5rem; padding: 0.55rem 0; border-bottom: 1px dashed #e6e9f4; }
    .carrito-item .info { flex: 1; min-width: 0; }
    .carrito-item .nombre { font-weight: 600; font-size: 0.9rem; color: #012970; }
    .qty-control { display: inline-flex; align-items: center; gap: 0.25rem; }
    .qty-control button { width: 28px; height: 28px; border: 1px solid #d8dbed; background: #fff; border-radius: 6px; }
    .qty-control input { width: 44px; text-align: center; border: 1px solid #d8dbed; border-radius: 6px; height: 28px; padding: 0; }
    .total-box { font-size: 1.6rem; font-weight: 700; color: var(--color-acento); }
    .pago-rapido button { width: 48%; margin: 0.25rem 1%; }
  </style>
</head>

<body>

  @include('shared.header')
  @include('shared.aside')

  @yield('contenido')

  @include('shared.footer')

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="{{ asset('NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
  <script src="{{ asset('NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('NiceAdmin/assets/vendor/chart.js/chart.umd.js') }}"></script>
  <script src="{{ asset('NiceAdmin/assets/vendor/echarts/echarts.min.js') }}"></script>
  <script src="{{ asset('NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js') }}"></script>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js" referrerpolicy="no-referrer"></script>
  <script src="{{ asset('NiceAdmin/assets/js/main.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    if (window.jQuery) {
      $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': window.csrfToken } });
    }

    @if(session('success'))
      Swal.fire({ icon: 'success', title: 'Éxito', text: @json(session('success')), confirmButtonText: 'Aceptar' });
    @endif

    @if(session('error'))
      Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')), confirmButtonText: 'Aceptar' });
    @endif
  </script>

  @stack('scripts')
</body>

</html>
