<?php include 'includes/header.php'; ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard"><span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span></a></li>
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>salones">Salones</a></li>
        <li class="breadcrumb-item active">Borrar Salón</li>
    </ol>
</nav>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><span class="material-symbols-outlined mr-2 text-danger" style="font-size:28px;">delete</span>Borrar Salón</h1>
        <p>Selecciona un salón para eliminarlo definitivamente del sistema</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
    <div class="card-body">
        <div class="autocomplete-wrap" style="position:relative;">
            <input type="text" id="salon_search" class="form-control" placeholder="Escribe el nombre del salón o edificio para ELIMINAR..." autocomplete="off"
                   style="height:54px; border:2px solid #ef4444; border-radius:10px; font-size:15px; padding:12px 16px;">
            <div id="salon_results" class="autocomplete-results"></div>
        </div>
    </div>
</div>

<div id="form_container" class="opacity-50" style="pointer-events: none;">
    <form id="delete_form" method="get" action="#">
        <div class="card border-0 shadow-sm border-danger mb-4" style="border-radius:12px;">
            <div class="card-header bg-white font-weight-bold text-danger"><span class="material-symbols-outlined mr-2" style="font-size:20px; vertical-align:middle;">warning</span> Confirmación de Borrado</div>
            <div class="card-body">
                <p class="text-muted mb-4">Por favor verifica que la información mostrada corresponda al salón que deseas eliminar. Esta acción no se puede deshacer.</p>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="small font-weight-bold">Nombre del Salón</label>
                        <input type="text" id="f_nombre" class="form-control" readonly style="border-radius:8px; background-color:#f8fafc;">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="small font-weight-bold">Edificio/Área</label>
                        <input type="text" id="f_edificio" class="form-control" readonly style="border-radius:8px; background-color:#f8fafc;">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="small font-weight-bold">Tipo de Espacio</label>
                        <input type="text" id="f_tipo" class="form-control" readonly style="border-radius:8px; background-color:#f8fafc;">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="small font-weight-bold">Capacidad Máxima (alumnos)</label>
                        <input type="number" id="f_capacidad" class="form-control" readonly style="border-radius:8px; background-color:#f8fafc;">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="small font-weight-bold">Estado</label>
                        <input type="text" id="f_estado" class="form-control" readonly style="border-radius:8px; background-color:#f8fafc;">
                    </div>
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold">Descripción / Equipamiento</label>
                    <textarea id="f_descripcion" class="form-control" rows="2" readonly style="border-radius:8px; background-color:#f8fafc;"></textarea>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between mb-5">
            <a href="<?php echo BASE_URL; ?>salones" class="btn btn-outline-secondary" style="border-radius:8px; padding:10px 25px;">
                <span class="material-symbols-outlined mr-1" style="font-size:20px; vertical-align:middle;">arrow_back</span> Cancelar
            </a>
            <button type="submit" class="btn btn-danger" onclick="return confirm('¿ESTÁS COMPLETAMENTE SEGURO de eliminar este Salón?');" style="border-radius:8px; padding:10px 30px; font-weight:600;">
                <span class="material-symbols-outlined mr-1" style="font-size:20px; vertical-align:middle;">delete_forever</span> Eliminar Definitivamente
            </button>
        </div>
    </form>
</div>

<style>
.autocomplete-results { position:absolute; z-index:1050; width:100%; max-height:250px; overflow-y:auto; background:#fff; border:1px solid #e2e8f0; border-top:none; border-radius:0 0 10px 10px; box-shadow:0 4px 12px rgba(0,0,0,.1); display:none; }
.autocomplete-results .ac-item { padding:10px 16px; cursor:pointer; font-size:14px; border-bottom:1px solid #f1f5f9; transition:background .15s; }
.autocomplete-results .ac-item:hover { background:#fee2e2; color:#ef4444; }
.autocomplete-results .ac-empty { padding:10px 16px; color:#94a3b8; font-size:14px; }
.opacity-50 { opacity: 0.3; }
</style>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    var items = <?php echo json_encode(array_values(array_map(function($s) {
        return ['id' => $s['id_salon'], 'label' => $s['nombre'] . ' (' . $s['edificio'] . ')', 'data' => $s];
    }, $salones)), JSON_HEX_TAG | JSON_HEX_APOS); ?> || [];

    var $input = $('#salon_search');
    var $results = $('#salon_results');

    $input.on('input', function() {
        var q = this.value.toLowerCase().trim();
        $results.empty();
        if (!q) { $results.hide(); resetForm(); return; }
        var matches = items.filter(function(i) { return i.label.toLowerCase().indexOf(q) !== -1; });
        if (!matches.length) {
            $results.html('<div class="ac-empty">Sin resultados</div>').show();
            return;
        }
        matches.forEach(function(item) {
            $('<div class="ac-item text-danger"></div>').attr('data-id', item.id).text(item.label).appendTo($results);
        });
        $results.show();
    });

    $results.on('click', '.ac-item', function() {
        var id = $(this).data('id');
        var item = items.find(function(i) { return i.id == id; });
        $input.val($(this).text());
        $results.hide();
        if (!item) return;
        var s = item.data;
        
        $('#f_nombre').val(s.nombre);
        $('#f_edificio').val(s.edificio);
        $('#f_tipo').val(s.tipo);
        $('#f_capacidad').val(s.capacidad);
        $('#f_estado').val(s.estado);
        $('#f_descripcion').val(s.descripcion);

        $('#delete_form').attr('action', '<?php echo BASE_URL; ?>salones/delete/' + id);
        $('#form_container').removeClass('opacity-50').css('pointer-events', 'auto');
    });

    function resetForm() {
        $('#form_container').addClass('opacity-50').css('pointer-events', 'none');
        $('#delete_form')[0].reset();
    }

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.autocomplete-wrap').length) $results.hide();
    });
});
</script>
