@extends('layouts.login')

@section('titulo', 'Iniciar sesión - ' . ($negocio['nombre'] ?? config('negocio.nombre')))

@section('contenido')
<main>
  <div class="container">
    <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

            <div class="d-flex justify-content-center py-4">
              <span class="login-brand">
                @if(!empty($negocio['logo_path']))
                  <img src="{{ asset('storage/' . $negocio['logo_path']) }}" alt="Logo" style="height:48px;" class="me-2">
                @else
                  <i class="bi bi-shop me-2"></i>
                @endif
                {{ $negocio['nombre'] ?? config('negocio.nombre') }}
              </span>
            </div>

            <div class="card mb-3 login-card">
              <div class="card-body">

                <div class="pt-4 pb-2 text-center">
                  <h5 class="card-title fs-4">Bienvenido</h5>
                  <p class="text-muted small">Ingresa tus credenciales para continuar</p>
                </div>

                @if ($errors->any())
                  <div class="alert alert-danger">
                    <ul class="mb-0 small">
                      @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif

                <form class="row g-3" method="POST" action="{{ route('logear') }}" novalidate>
                  @csrf

                  <div class="col-12">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                      <input type="email" name="email" class="form-control" id="email" value="{{ old('email') }}" required autofocus>
                    </div>
                  </div>

                  <div class="col-12">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-lock"></i></span>
                      <input type="password" name="password" class="form-control" id="password" required>
                    </div>
                  </div>

                  <div class="col-12 form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                    <label class="form-check-label small" for="remember">Recordarme</label>
                  </div>

                  <div class="col-12">
                    <button class="btn btn-primary w-100" type="submit">Ingresar</button>
                  </div>
                </form>

              </div>
            </div>

          </div>
        </div>
      </div>
    </section>
  </div>
</main>
@endsection
