@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">

  <div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
      <h1><i class="bi bi-stars"></i> Combos / Promos</h1>
      <p class="text-muted small mb-0">Crea combos y promociones para tu menú</p>
    </div>
    <a href="{{ route('combos.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> Nuevo combo
    </a>
  </div>

  <section class="section">
    <div class="row g-3">
      @forelse($items as $combo)
        <div class="col-md-4 col-lg-3">
          <div class="card admin-card h-100 {{ ! $combo->activo ? 'no-disponible' : '' }}">
            <div class="admin-card-img" @if($combo->imagenUrl()) style="background-image:url('{{ $combo->imagenUrl() }}'); background-size:cover; background-position:center;" @endif>
              @if(! $combo->imagenUrl())<i class="bi bi-stars" style="font-size:3rem; color:#aaa;"></i>@endif
              @if(! $combo->activo)<span class="admin-card-tag bg-secondary">Inactivo</span>@endif
            </div>
            <div class="card-body">
              <h6 class="mb-1">{{ $combo->nombre }}</h6>
              @if($combo->codigo)<small class="text-muted d-block">{{ $combo->codigo }}</small>@endif
              <ul class="list-unstyled small text-muted mb-2">
                @foreach($combo->items as $ci)
                  <li>{{ $ci->cantidad }}× {{ $ci->producto?->nombre ?? '—' }}</li>
                @endforeach
              </ul>
              <div class="fs-5 fw-bold text-danger mb-2">{{ $negocio['moneda'] }} {{ number_format($combo->precio, 2) }}</div>
              <div class="d-flex gap-1">
                <a href="{{ route('combos.edit', $combo->id) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                  <i class="bi bi-pencil"></i>
                </a>
                <form method="POST" action="{{ route('combos.destroy', $combo->id) }}" class="d-inline" onsubmit="return confirm('¿Eliminar combo?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="alert alert-info">No hay combos creados todavía. <a href="{{ route('combos.create') }}">Crear el primero</a>.</div>
        </div>
      @endforelse
    </div>
  </section>

</main>
@endsection
