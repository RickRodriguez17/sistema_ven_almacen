@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1>Gastos de Caja</h1>
    <a href="{{ route('gastos.create') }}" class="btn btn-danger">
      <i class="bi bi-plus-circle"></i> Nuevo gasto urgente
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-2">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-2">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <section class="section">
    <div class="card">
      <div class="card-body">
        @if(!$abierto)
          <div class="alert alert-warning mb-3">
            <i class="bi bi-exclamation-triangle"></i>
            No tienes un turno abierto. Para registrar gastos
            <a href="{{ route('cierres.iniciar.form') }}">inicia un turno</a>.
          </div>
        @endif

        <p class="text-muted small">
          Estos gastos se descuentan del efectivo esperado al cerrar el turno.
        </p>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Cajero</th>
                <th>Turno</th>
                <th class="text-end">Monto</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @forelse($items as $g)
                <tr>
                  <td>{{ $g->created_at->format('d/m/Y H:i') }}</td>
                  <td>{{ $g->concepto }}</td>
                  <td>{{ $g->user?->name }}</td>
                  <td>
                    @if($g->cierreCaja)
                      <a href="{{ route('cierres.show', $g->cierreCaja->id) }}">
                        #{{ str_pad($g->cierreCaja->id, 5, '0', STR_PAD_LEFT) }}
                      </a>
                      @if($g->cierreCaja->estaAbierto())
                        <span class="badge bg-warning text-dark">Abierto</span>
                      @else
                        <span class="badge bg-secondary">Cerrado</span>
                      @endif
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td class="text-end fw-bold text-danger">
                    − {{ $negocio['moneda'] }} {{ number_format($g->monto, 2) }}
                  </td>
                  <td class="text-center">
                    @php
                      $puedeBorrar = auth()->user()->esAdmin()
                        || ($g->user_id === auth()->id() && $g->cierreCaja && $g->cierreCaja->estaAbierto());
                    @endphp
                    @if($puedeBorrar)
                      <form method="POST" action="{{ route('gastos.destroy', $g->id) }}"
                            class="d-inline form-borrar-gasto">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-sm btn-outline-danger btn-borrar-gasto"
                                data-monto="{{ number_format($g->monto, 2) }}"
                                data-concepto="{{ $g->concepto }}">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">No hay gastos registrados.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{ $items->links() }}
      </div>
    </div>
  </section>
</main>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-borrar-gasto').forEach(btn => btn.addEventListener('click', async () => {
  const r = await Swal.fire({
    title: '¿Eliminar gasto?',
    html: `<strong>${btn.dataset.concepto}</strong><br>Monto: {{ $negocio['moneda'] }} ${btn.dataset.monto}`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Eliminar',
    confirmButtonColor: '#d33',
    cancelButtonText: 'Cancelar',
  });
  if (r.isConfirmed) btn.closest('form').submit();
}));
</script>
@endpush
