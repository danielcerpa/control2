<?php include 'includes/header.php'; ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard"><span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span></a></li>
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>docentes">Docentes</a></li>
        <li class="breadcrumb-item active">Nuevo Docente</li>
    </ol>
</nav>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><span class="material-symbols-outlined mr-2" style="font-size:28px;">person_add</span>Nuevo Docente</h1>
        <p>Complete todos los campos requeridos (<span class="text-danger">*</span>)</p>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger shadow-sm" style="border-radius:12px;">
        <div class="d-flex align-items-center mb-2">
            <span class="material-symbols-outlined mr-2">error</span>
            <strong>Corrige los siguientes errores:</strong>
        </div>
        <ul class="mb-0 pl-4">
            <?php foreach ($errors as $e): ?>
                <li><?php echo e($e); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?php echo BASE_URL; ?>docentes/create" class="check-dirty">
    <div class="row">
        <!-- ── COLUMNA IZQUIERDA ── -->
        <div class="col-md-8">

            <!-- Datos generales -->
            <div class="card mb-4 border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-header bg-white font-weight-bold"><span class="material-symbols-outlined mr-2" style="font-size:20px; vertical-align:middle;">badge</span> Datos del Docente</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">N° Empleado <span class="text-danger">*</span></label>
                            <input type="text" name="num_empleado" class="form-control"
                                value="<?php echo e($datos['numero_empleado']); ?>" maxlength="20" required oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="border-radius:8px;">
                            <small class="text-muted">Actualmente hay <strong><?php echo $total_docentes ?? 0; ?></strong> docente(s) registrado(s)</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">CURP <span class="text-danger">*</span></label>
                            <input type="text" name="curp" class="form-control" style="text-transform:uppercase; border-radius:8px;"
                                value="<?php echo e($datos['curp']); ?>" maxlength="18" required pattern="^[A-Za-z]{4}\d{6}[HMhm][A-Za-z]{5}[A-Za-z0-9]\d$" title="CURP de 18 caracteres (ej. ROPE950812HDFRNR08)" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Estado</label>
                            <select name="estado" class="form-control" style="border-radius:8px;">
                                <option value="Activo"   <?php if ($datos['estado'] === 'Activo')   echo 'selected'; ?>>Activo</option>
                                <option value="Inactivo" <?php if ($datos['estado'] === 'Inactivo') echo 'selected'; ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Nombre(s) <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control"
                                value="<?php echo e($datos['nombre_completo']); ?>" maxlength="60" required style="border-radius:8px;">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Apellido Paterno <span class="text-danger">*</span></label>
                            <input type="text" name="apellido_p" class="form-control"
                                value="<?php echo e($datos['apellido_paterno']); ?>" maxlength="60" required style="border-radius:8px;">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Apellido Materno</label>
                            <input type="text" name="apellido_m" class="form-control"
                                value="<?php echo e($datos['apellido_materno']); ?>" maxlength="60" style="border-radius:8px;">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control"
                                value="<?php echo e($datos['email']); ?>" maxlength="120" required style="border-radius:8px;">
                            <small class="text-muted">Autogenerado sugerido</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" name="telefono" class="form-control"
                                value="<?php echo e($datos['telefono']); ?>" required minlength="10" maxlength="10" pattern="[0-9]{10}" title="Debe contener exactamente 10 dígitos" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" style="border-radius:8px;">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Grado de Estudios <span class="text-danger">*</span></label>
                            <select name="grado_estudio" class="form-control" required style="border-radius:8px;">
                                <option value="">Seleccionar...</option>
                                <?php foreach (array('Licenciatura', 'Especialidad', 'Maestría', 'Doctorado', 'Posgrado') as $opt): ?>
                                    <option value="<?php echo $opt; ?>" <?php if ($datos['grado_estudio'] === $opt) echo 'selected'; ?>><?php echo $opt; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Domicilio</label>
                        <textarea name="domicilio" class="form-control" rows="2"
                            data-maxlength="200" style="border-radius:8px;" placeholder="Ingrese el domicilio..."><?php echo e($datos['domicilio'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Acceso -->
            <div class="card mb-4 border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-header bg-white font-weight-bold"><span class="material-symbols-outlined mr-2" style="font-size:20px; vertical-align:middle;">key</span> Credenciales de Acceso</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Usuario <span class="text-danger">*</span></label>
                            <input type="text" name="login_id" id="login_id" class="form-control"
                                value="<?php echo e($datos['login_id']); ?>" maxlength="30" required style="border-radius:8px;">
                            <small class="text-muted">Autogenerado sugerido</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" maxlength="100" required style="border-radius:8px;">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password2" class="form-control" maxlength="100" required style="border-radius:8px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── COLUMNA DERECHA: FOTO ── -->
        <div class="col-md-4">
            <div class="card mb-4 border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-header bg-white font-weight-bold"><span class="material-symbols-outlined mr-2" style="font-size:20px; vertical-align:middle;">photo_camera</span> Foto del Docente</div>
                <div class="card-body text-center">
                    <div id="foto-preview-wrap" class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                        style="width:150px; height:150px; border:2px dashed #cbd5e1; border-radius:12px; cursor:pointer; overflow:hidden; background:#f8fafc;">
                        <span class="material-symbols-outlined text-muted" style="font-size:48px;">add_a_photo</span>
                    </div>
                    <small class="text-muted d-block mb-3">Clic en el recuadro para subir foto</small>
                    <input type="file" id="foto_file" accept="image/*" class="d-none">
                    <input type="hidden" name="foto_base64" id="foto_base64" value="">
                    <button type="button" id="btn-quitar-foto" class="btn btn-sm btn-outline-danger" style="border-radius:20px; display: none !important;">Quitar foto</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones -->
    <div class="d-flex justify-content-between mb-5">
        <a href="<?php echo BASE_URL; ?>docentes" class="btn btn-outline-secondary" style="border-radius:8px; padding:10px 25px;">
            <span class="material-symbols-outlined mr-1" style="font-size:20px; vertical-align:middle;">arrow_back</span> Cancelar
        </a>
        <button type="submit" class="btn btn-primary" style="background:#197fe6; border:none; border-radius:8px; padding:10px 30px; font-weight:600;">
            <span class="material-symbols-outlined mr-1" style="font-size:20px; vertical-align:middle;">save</span> Guardar Docente
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wrap = document.getElementById('foto-preview-wrap');
        const fileInput = document.getElementById('foto_file');
        const base64Input = document.getElementById('foto_base64');
        const btnQuitar = document.getElementById('btn-quitar-foto');

        // Autogeneración de Email y Usuario
        const nombreInput = document.querySelector('input[name="nombre"]');
        const apellidoPInput = document.querySelector('input[name="apellido_p"]');
        const apellidoMInput = document.querySelector('input[name="apellido_m"]');
        const emailInput = document.getElementById('email');
        const loginIdInput = document.getElementById('login_id');

        let isManualOverride = false;

        function updateCredentials() {
            if (isManualOverride) return; // No sobreescribir si el usuario lo editó a mano

            let nombre = nombreInput.value.trim().split(' ')[0]; // Solo el primer nombre
            let apP = apellidoPInput.value.trim().charAt(0);
            let apM = apellidoMInput.value.trim().charAt(0);

            // Quitar acentos
            nombre = nombre.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            apP = apP.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            apM = apM.normalize("NFD").replace(/[\u0300-\u036f]/g, "");

            // Dar formato: NombreAAM
            if(nombre) nombre = nombre.charAt(0).toUpperCase() + nombre.slice(1).toLowerCase();
            if(apP) apP = apP.toUpperCase();
            if(apM) apM = apM.toUpperCase();

            let baseName = nombre + apP + apM;
            
            if (baseName.length > 0) {
                // Removemos espacios intermedios y caracteres especiales que pudieran quedar
                baseName = baseName.replace(/[^a-zA-Z0-9]/g, '');
                emailInput.value = baseName + '@gmail.com';
                loginIdInput.value = baseName;
            } else {
                emailInput.value = '';
                loginIdInput.value = '';
            }
        }

        nombreInput.addEventListener('input', updateCredentials);
        apellidoPInput.addEventListener('input', updateCredentials);
        apellidoMInput.addEventListener('input', updateCredentials);

        emailInput.addEventListener('input', () => isManualOverride = true);
        loginIdInput.addEventListener('input', () => isManualOverride = true);

        wrap.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                if (file.size > 10 * 1024 * 1024) {
                    alert('La imagen es muy grande (máx 10MB)');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    wrap.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">`;
                    base64Input.value = e.target.result;
                    btnQuitar.style.setProperty('display', 'inline-flex', 'important');
                }
                reader.readAsDataURL(file);
            }
        });

        btnQuitar.addEventListener('click', function() {
            wrap.innerHTML = '<span class="material-symbols-outlined text-muted" style="font-size:48px;">add_a_photo</span>';
            base64Input.value = '';
            fileInput.value = '';
            this.style.setProperty('display', 'none', 'important');
        });
    });
</script>

<?php include 'includes/footer.php'; ?>