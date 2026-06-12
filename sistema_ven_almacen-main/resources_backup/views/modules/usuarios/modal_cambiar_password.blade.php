
<form id="frmPassword" onsubmit="return cambio_password()">
<div class="modal fade" id="cambiar_password" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Escribe la nueva contraseña</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="id_usuario" name="id_usuario">
        
        <label>Contraseña nueva</label>
        <input type="password" class="form-control" 
               id="password" name="password" required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cerrar
        </button>
        <button type="submit" class="btn btn-warning">
            Guardar Cambios
        </button>
      </div>
    </div>
  </div>
</div>
</form>
