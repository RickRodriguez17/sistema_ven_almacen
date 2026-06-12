@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1>Categorías</h1>
    <a href="{{ route('categorias.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nueva categoría</a>
  </div>

  <section class="section">
    <div class="card"><div class="card-body">
      <table class="table table-hover">
        <thead><tr><th>Nombre</th><th class="text-center">Productos</th><th class="text-center">Acciones</th></tr></thead>
        <tbody>
          @forelse($items as $item)
            <tr>
              <td>{{ $item->nombre }}</td>
              <td class="text-center"><span class="badge bg-info">{{ $item->productos_count }}</span></td>
              <td class="text-center">
                <a href="{{ route('categorias.edit', $item->id) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                <a href="{{ route('categorias.show', $item->id) }}" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-center py-4 text-muted">No hay categorías.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div></div>
  </section>
</main>
@endsection
