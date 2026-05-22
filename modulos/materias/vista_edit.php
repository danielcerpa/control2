<?php include 'includes/header.php'; ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard"><span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span></a></li>
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>materias">Materias</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>
</nav>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><span class="material-symbols-outlined mr-2" style="font-size:28px;">edit</span>Editar Materia</h1>
        <p>Actualice la información de: <strong><?php echo e($datos['nombre']); ?></strong></p>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger shadow-sm" style="border-radius:12px;">
        <div class="d-flex align-items-center mb-2">
            <span class="material-symbols-outlined mr-2">error</span>
            <strong>Corrige los siguientes errores:</strong>
        </div>
        <ul class="mb-0 pr-4">
            <?php foreach ($errors as $e): ?>
                <li><?php echo e($e); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-header bg-white font-weight-bold pt-3 pb-2">
                <span class="material-symbols-outlined mr-2 text-warning" style="font-size:20px; vertical-align:middle;">edit_note</span>
                Modificar Asignatura
            </div>
            <div class="card-body">
                <form method="post" action="<?php echo BASE_URL; ?>materias/edit/<?php echo $datos['id_materia']; ?>" class="check-dirty">

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Clave <span class="text-danger">*</span></label>
                            <input type="text" name="clave" class="form-control" value="<?php echo e($datos['clave']); ?>" maxlength="20" required style="border-radius:8px;">
                        </div>
                        <div class="col-md-8 form-group">
                            <label class="small font-weight-bold">Nombre de la Materia <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo e($datos['nombre']); ?>" maxlength="100" required pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Solo letras y espacios" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');" style="border-radius:8px;">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Horas Asignadas <span class="text-muted" style="font-weight:400;">(por semana, mín. 2 — máx. 6)</span></label>
                            <input type="number" name="horas" class="form-control" value="<?php echo e($datos['horas'] ?: 4); ?>" min="2" max="6" required oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="border-radius:8px;">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Grado</label>
                            <select name="grado" class="form-control" style="border-radius:8px;">
                                <option value="">Seleccionar grupo</option>
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php if ($datos['grado'] == $i) echo 'selected'; ?>><?php echo $i; ?>° Grado</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label class="small font-weight-bold">Ciclo Escolar <span class="text-danger">*</span> (Obligatorio)</label>
                            <select name="ciclo_id" class="form-control" required style="border-radius:8px;">
                                <option value="">Seleccionar ciclo escolar</option>
                                <?php foreach ($ciclos as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"
                                        <?php if (($datos['ciclo_escolar'] ?? '') == $c['id'] || ($datos['ciclo_id'] ?? '') == $c['id']) echo 'selected'; ?>>
                                        <?php echo e($c['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Estado</label>
                            <select name="estado" class="form-control" style="border-radius:8px;">
                                <option value="Activo" <?php if ($datos['estado'] === 'Activo') echo 'selected'; ?>>Activo</option>
                                <option value="Inactivo" <?php if ($datos['estado'] === 'Inactivo') echo 'selected'; ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4" style="border-style:dashed;">
                    <h6 class="font-weight-bold text-warning mb-3"><span class="material-symbols-outlined mr-2" style="font-size:20px; vertical-align:middle;">schedule</span>Horario y Asignación</h6>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-secondary text-uppercase" style="letter-spacing:1px;">Docente</label>
                            <select name="docente_id" class="form-control select2" style="border-radius:8px;">
                                <option value="">Seleccionar Profesor...</option>
                                <?php foreach ($docentes as $d): ?>
                                    <option value="<?php echo $d['id_profesor']; ?>" <?php if (($datos['docente_id'] ?? $datos['id_profesor']) == $d['id_profesor']) echo 'selected'; ?>><?php echo e($d['nombre_completo']) . ' - ' . e($d['numero_empleado']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-secondary text-uppercase" style="letter-spacing:1px;">Grupo</label>
                            <select name="grupo_id" class="form-control" style="border-radius:8px;">
                                <option value="">Seleccionar Grupo...</option>
                                <?php foreach ($grupos as $g): ?>
                                    <option value="<?php echo $g['id_grupo'] ?? $g['id']; ?>" <?php if (($datos['grupo_id'] ?? $datos['id_grupo']) == ($g['id_grupo'] ?? $g['id'])) echo 'selected'; ?>><?php echo e($g['nombre'] ?? ($g['grado'].$g['seccion'])) . ($g['turno'] ? ' - ' . ucfirst(strtolower($g['turno'])) : ''); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-secondary text-uppercase" style="letter-spacing:1px;">Salón</label>
                            <select name="salon_id" class="form-control" style="border-radius:8px;">
                                <option value="">Seleccionar Salón...</option>
                                <?php foreach ($salones as $s): ?>
                                    <option value="<?php echo $s['id_salon']; ?>" <?php if (($datos['salon_id'] ?? $datos['id_salon']) == $s['id_salon']) echo 'selected'; ?>><?php echo e($s['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php
                        $horarios_map = [];
                        if(!empty($datos['horarios'])) {
                            foreach($datos['horarios'] as $h) {
                                $horarios_map[strtoupper($h['dia'])] = [
                                    'hi' => substr($h['hora_inicio'], 0, 5),
                                    'hf' => substr($h['hora_fin'], 0, 5)
                                ];
                            }
                        }
                    ?>
                    <div class="mb-3">
                        <label class="small font-weight-bold text-secondary text-uppercase mb-2" style="letter-spacing:1px;">Horario de Clases (Seleccionadas: <span id="horas_seleccionadas" class="text-primary font-weight-bold">0</span> hrs)</label>
                        <?php foreach(['Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'] as $d): 
                            $upperD = strtoupper($d);
                            if (isset($datos['dias'])) {
                                $checked = in_array($d, $datos['dias']) ? 'checked' : '';
                                $hi_val = $datos['hora_inicio'][$d] ?? '';
                                $hf_val = $datos['hora_fin'][$d] ?? '';
                            } else {
                                $checked = isset($horarios_map[$upperD]) ? 'checked' : '';
                                $hi_val = $checked ? $horarios_map[$upperD]['hi'] : '';
                                $hf_val = $checked ? $horarios_map[$upperD]['hf'] : '';
                            }
                            $disabled = $checked ? '' : 'disabled';
                            $required = $checked ? 'required' : '';
                        ?>
                        <div class="row align-items-center mb-2">
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="dias[]" value="<?php echo $d; ?>" class="custom-control-input dia-checkbox" id="edit_chk_<?php echo $d; ?>" <?php echo $checked; ?>>
                                    <label class="custom-control-label pt-1" for="edit_chk_<?php echo $d; ?>" style="cursor:pointer;"><?php echo $d; ?></label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted mb-0">Hora Inicio</label>
                                <input type="time" name="hora_inicio[<?php echo $d; ?>]" class="form-control time-input" id="hi_<?php echo $d; ?>" value="<?php echo $hi_val; ?>" style="border-radius:8px;" <?php echo $disabled; ?> <?php echo $required; ?>>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted mb-0">Hora Fin</label>
                                <input type="time" name="hora_fin[<?php echo $d; ?>]" class="form-control time-input" id="hf_<?php echo $d; ?>" value="<?php echo $hf_val; ?>" style="border-radius:8px;" <?php echo $disabled; ?> <?php echo $required; ?>>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <small class="text-muted mt-2 d-block">Nota: Si se seleccionan 2 días, ambos deben tener el mismo horario. Si se seleccionan 3 días, los dos primeros deben coincidir.</small>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const checkboxes = document.querySelectorAll('.dia-checkbox');
                        const timeInputs = document.querySelectorAll('.time-input');
                        
                        function calculateHours() {
                            let totalMinutes = 0;
                            checkboxes.forEach(chk => {
                                if(chk.checked) {
                                    const day = chk.value;
                                    const hi = document.getElementById('hi_' + day).value;
                                    const hf = document.getElementById('hf_' + day).value;
                                    if(hi && hf) {
                                        const d1 = new Date("2000-01-01 " + hi);
                                        const d2 = new Date("2000-01-01 " + hf);
                                        if(d2 > d1) {
                                            totalMinutes += (d2 - d1) / 60000;
                                        }
                                    }
                                }
                            });
                            const totalHours = totalMinutes / 60;
                            let html = totalHours % 1 === 0 ? totalHours : totalHours.toFixed(1);
                            document.getElementById('horas_seleccionadas').textContent = html;
                        }

                        checkboxes.forEach(chk => {
                            chk.addEventListener('change', function() {
                                const day = this.value;
                                const hi = document.getElementById('hi_' + day);
                                const hf = document.getElementById('hf_' + day);
                                if(this.checked) {
                                    hi.disabled = false;
                                    hi.required = true;
                                    hf.disabled = false;
                                    hf.required = true;
                                } else {
                                    hi.disabled = true;
                                    hi.required = false;
                                    hi.value = "";
                                    hf.disabled = true;
                                    hf.required = false;
                                    hf.value = "";
                                }
                                calculateHours();
                            });
                        });
                        
                        timeInputs.forEach(input => {
                            input.addEventListener('input', calculateHours);
                        });

                        // Calculate initially
                        calculateHours();
                    });
                    </script>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>materias" class="btn btn-outline-secondary" style="border-radius:8px; padding:10px 20px;">
                            <span class="material-symbols-outlined mr-1" style="font-size:20px; vertical-align:middle;">arrow_back</span> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary" style="background:#197fe6; border:none; border-radius:8px; padding:10px 30px; font-weight:600;">
                            <span class="material-symbols-outlined mr-1" style="font-size:20px; vertical-align:middle;">save</span> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>