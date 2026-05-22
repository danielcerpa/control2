<?php include dirname(__FILE__) . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 font-weight-bold">Configuración de la Institución</h1>
        <p class="text-muted mb-0 small">Personaliza los datos que aparecen en documentos y reportes</p>
    </div>
    <span class="material-symbols-outlined" style="font-size:36px; color:#197fe6;">settings</span>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <form method="POST" action="<?php echo BASE_URL; ?>configuracion" id="form-config" novalidate>

            <!-- Tarjeta: Datos de la Institución -->
            <div class="card shadow-sm mb-4" style="border-radius:12px;">
                <div class="card-header d-flex align-items-center" style="padding:16px 20px;">
                    <span class="material-symbols-outlined mr-2" style="color:#197fe6; font-size:20px;">domain</span>
                    <strong>Datos Generales</strong>
                </div>
                <div class="card-body" style="padding:24px;">

                    <!-- Nombre de la institución -->
                    <div class="form-group">
                        <label class="font-weight-bold small text-uppercase text-muted" for="nombre_institucion">
                            Nombre de la Institución <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="nombre_institucion" name="nombre_institucion"
                               class="form-control form-control-lg"
                               placeholder="Ej: Escuela Secundaria Técnica #12"
                               value="<?php echo e($config['nombre_institucion'] ?? ''); ?>"
                               minlength="3"
                               maxlength="150"
                               required
                               pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s#.,\-]{3,150}"
                               oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s0-9#.,\-]/g, '');"
                               title="De 3 a 150 caracteres. Solo letras, números, espacios y puntuación básica (#, ., , y -).">
                        <div class="invalid-feedback">El nombre de la institución es obligatorio, debe tener entre 3 y 150 caracteres y no contener caracteres especiales.</div>
                        <small class="form-text text-muted">Aparecerá en el encabezado de boletas y reportes. Solo letras, números y puntuación básica.</small>
                    </div>

                    <div class="row">
                        <!-- CCT -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small text-uppercase text-muted" for="cct_institucion">
                                    Clave de Centro de Trabajo (CCT)
                                </label>
                                <input type="text" id="cct_institucion" name="cct_institucion"
                                       class="form-control"
                                       placeholder="Ej: 09DST0001W"
                                       value="<?php echo e($config['cct_institucion'] ?? ''); ?>"
                                       maxlength="10"
                                       pattern="[A-Za-z0-9]{10}"
                                       oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0,10);"
                                       title="Debe tener exactamente 10 caracteres alfanuméricos">
                                <div class="invalid-feedback">La CCT debe tener exactamente 10 caracteres alfanuméricos (letras y números).</div>
                            </div>
                        </div>
                        <!-- Turno -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small text-uppercase text-muted" for="turno_institucion">
                                    Turno
                                </label>
                                <select id="turno_institucion" name="turno_institucion" class="form-control">
                                    <option value="MATUTINO"   <?php echo ($config['turno_institucion'] ?? '') === 'MATUTINO'   ? 'selected' : ''; ?>>Matutino</option>
                                    <option value="VESPERTINO" <?php echo ($config['turno_institucion'] ?? '') === 'VESPERTINO' ? 'selected' : ''; ?>>Vespertino</option>
                                    <option value="NOCTURNO"   <?php echo ($config['turno_institucion'] ?? '') === 'NOCTURNO'   ? 'selected' : ''; ?>>Nocturno</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="form-group">
                        <label class="font-weight-bold small text-uppercase text-muted" for="direccion_institucion">
                            Dirección
                        </label>
                        <input type="text" id="direccion_institucion" name="direccion_institucion"
                               class="form-control"
                               placeholder="Ej: Calle Reforma #45, Col. Centro"
                               value="<?php echo e($config['direccion_institucion'] ?? ''); ?>"
                               maxlength="200"
                               pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s#.,\-]{0,200}"
                               oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s0-9#.,\-]/g, '');"
                               title="Máximo 200 caracteres. Solo letras, números, espacios y puntuación básica (#, ., , y -).">
                        <div class="invalid-feedback">La dirección supera los 200 caracteres o contiene caracteres especiales no permitidos.</div>
                    </div>

                    <div class="row">
                        <!-- Teléfono -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small text-uppercase text-muted" for="telefono_institucion">
                                    Teléfono
                                </label>
                                <input type="tel" id="telefono_institucion" name="telefono_institucion"
                                       class="form-control"
                                       placeholder="Ej: 4761234567"
                                       value="<?php echo e($config['telefono_institucion'] ?? ''); ?>"
                                       maxlength="10"
                                       pattern="[0-9]{10}"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);"
                                       title="Exactamente 10 dígitos numéricos">
                                <div class="invalid-feedback">El teléfono debe tener exactamente 10 dígitos numéricos.</div>
                                <small class="form-text text-muted">10 dígitos sin espacios ni guiones.</small>
                            </div>
                        </div>
                        <!-- Correo -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small text-uppercase text-muted" for="correo_institucion">
                                    Correo Electrónico
                                </label>
                                <input type="email" id="correo_institucion" name="correo_institucion"
                                       class="form-control"
                                       placeholder="Ej: contacto@escuela.edu.mx"
                                       value="<?php echo e($config['correo_institucion'] ?? ''); ?>"
                                       maxlength="100"
                                       pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                                       oninput="this.value = this.value.replace(/[^a-zA-Z0-9@._\-+]/g, '');"
                                       title="Debe ser un correo electrónico válido">
                                <div class="invalid-feedback">Ingresa un correo electrónico válido (máximo 100 caracteres).</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista previa -->
            <div class="card shadow-sm mb-4" style="border-radius:12px;">
                <div class="card-header d-flex align-items-center" style="padding:16px 20px;">
                    <span class="material-symbols-outlined mr-2" style="color:#10b981; font-size:20px;">preview</span>
                    <strong>Vista Previa del Encabezado</strong>
                </div>
                <div class="card-body preview-paper" style="padding:24px; text-align:center; border-radius: 0 0 12px 12px;">
                    <div id="preview-nombre" style="font-size:1.4rem; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:#1e293b;">
                        <?php echo e($config['nombre_institucion'] ?? 'Nombre de la Institución'); ?>
                    </div>
                    <div id="preview-sub" class="text-muted small mt-1">
                        <?php echo e($config['turno_institucion'] ?? ''); ?>
                        <?php if (!empty($config['cct_institucion'])): ?>· CCT: <?php echo e($config['cct_institucion']); ?><?php endif; ?>
                    </div>
                    <div class="mt-2" style="font-size:0.85rem; border-top:1px solid #000; border-bottom:1px solid #000; display:inline-block; padding:4px 24px; font-weight:bold; letter-spacing:1px;">
                        BOLETA OFICIAL DE CALIFICACIONES
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-lg px-5" style="border-radius:8px; font-weight:600;">
                    <span class="material-symbols-outlined mr-1" style="font-size:18px; vertical-align:middle;">save</span>
                    Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Validación con Bootstrap + HTML5 ──────────────────────────────────────────
(function () {
    'use strict';
    var form = document.getElementById('form-config');
    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
})();

// ── Vista previa en tiempo real ───────────────────────────────────────────────
function updatePreview() {
    var nombre = document.getElementById('nombre_institucion').value || 'Nombre de la Institución';
    var turno  = document.getElementById('turno_institucion').value;
    var cct    = document.getElementById('cct_institucion').value.trim();

    document.getElementById('preview-nombre').textContent = nombre;
    var sub = turno;
    if (cct) sub += ' · CCT: ' + cct;
    document.getElementById('preview-sub').textContent = sub;
}

document.getElementById('nombre_institucion').addEventListener('input', updatePreview);
document.getElementById('turno_institucion').addEventListener('change', updatePreview);
document.getElementById('cct_institucion').addEventListener('input', updatePreview);
</script>

<?php include dirname(__FILE__) . '/../../includes/footer.php'; ?>
