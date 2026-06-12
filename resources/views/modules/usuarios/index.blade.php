@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1>Usuarios</h1>
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> Nuevo usuario</a>
  </div>

  <section class="section">
    <div class="card"><div class="card-body">
      <table class="table table-hover">
        <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th class="text-center">Activo</th><th class="text-center">Acciones</th></tr></thead>
        <tbody id="tbody-usuarios">
          @foreach($items as $item)
            <tr>
              <td>{{ $item->name }}</td>
              <td>{{ $item->email }}</td>
              <td><span class="badge bg-primary">{{ $roles[$item->rol] ?? $item->rol }}</span></td>
              <td class="text-center">
                <div class="form-check form-switch d-inline-block">
                  <input class="form-check-input toggle-activo-usuario" type="checkbox" data-id="{{ $item->id }}" {{ $item->activo ? 'checked' : '' }} {{ auth()->id() === $item->id ? 'disabled' : '' }}>
                </div>
              </td>
              <td class="text-center">
                <button type="button" class="btn btn-sm btn-secondary btn-cambiar-pass" data-id="{{ $item->id }}" title="Cambiar contraseña"><i class="bi bi-key"></i></button>
                <a href="{{ route('usuarios.edit', $item->id) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div></div>
  </section>
</main>

<div class="modal fade" id="cambiar_password" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="frmPassword">
        <div class="modal-header">
          <h5 class="modal-title">Cambiar contraseña</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="id_usuario">
          <label class="form-label">Nueva contraseña (mínimo 6)</label>
          <input type="password" id="password" class="form-control" required minlength="6">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-warning">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('.toggle-activo-usuario').forEach(el => {
    el.addEventListener('change', function () {
      const id = this.dataset.id;
      const estado = this.checked ? 1 : 0;
      $.ajax({
        url: '/usuarios/cambiar-estado/' + id,
        type: 'PATCH',
        data: { estado },
        success: () => Swal.fire('OK', 'Estado actualizado', 'success'),
        error: (xhr) => Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo cambiar', 'error'),
      });
    });
  });

  document.querySelectorAll('.btn-cambiar-pass').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('id_usuario').value = btn.dataset.id;
      new bootstrap.Modal(document.getElementById('cambiar_password')).show();
    });
  });

  document.getElementById('frmPassword').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('id_usuario').value;
    const password = document.getElementById('password').value;
    $.ajax({
      url: '/usuarios/cambiar-password/' + id,
      type: 'PATCH',
      data: { password },
      success: () => {
        Swal.fire('OK', 'Contraseña actualizada', 'success');
        bootstrap.Modal.getInstance(document.getElementById('cambiar_password')).hide();
        document.getElementById('frmPassword').reset();
      },
      error: () => Swal.fire('Error', 'No se pudo actualizar', 'error'),
    });
  });
</script>
@endpush
