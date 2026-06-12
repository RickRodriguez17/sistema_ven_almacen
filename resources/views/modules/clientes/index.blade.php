@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1>Clientes</h1>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo cliente</a>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <table class="table datatable table-hover">
          <thead><tr><th>Nombre</th><th>Apellido</th><th>Teléfono</th><th>Email</th><th class="text-center">Acciones</th></tr></thead>
          <tbody>
            @forelse($items as $item)
              <tr>
                <td>{{ $item->nombre }}</td>
                <td>{{ $item->apellido }}</td>
                <td>{{ $item->telefono ?? '—' }}</td>
                <td>{{ $item->email ?? '—' }}</td>
                <td class="text-center">
                  <a href="{{ route('clientes.edit', $item->id) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                  <form action="{{ route('clientes.destroy', $item->id) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="button" class="btn btn-sm btn-danger btn-confirmar-eliminar"><i class="bi bi-trash"></i></button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center py-4 text-muted">No hay clientes.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </section>
</main>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('.btn-confirmar-eliminar').forEach(btn => {
    btn.addEventListener('click', function () {
      const form = this.closest('form');
      Swal.fire({title:'¿Eliminar?', icon:'warning', showCancelButton:true, confirmButtonText:'Sí', confirmButtonColor:'#dc3545'})
        .then(r => { if (r.isConfirmed) form.submit(); });
    });
  });
</script>
@endpush
