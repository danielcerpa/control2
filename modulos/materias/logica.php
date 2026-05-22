<?php
// app/Controllers/MateriasController.php

class MateriasController extends Controller
{
    /** @var Materia */
    private $materiaModel;
    /** @var CicloEscolar */
    private $cicloModel;
    /** @var Docente */
    private $docenteModel;
    /** @var Salon */
    private $salonModel;
    /** @var Grupo */
    private $grupoModel;

    public function __construct()
    {
        require_perm('materias');
        $this->materiaModel = new Materia();
        $this->cicloModel   = new CicloEscolar();
        $this->docenteModel = new Docente();
        $this->salonModel   = new Salon();
        $this->grupoModel   = new Grupo();
    }

    public function index()
    {
        $filtros = [
            'q'      => isset($_GET['q'])      ? trim($_GET['q'])      : '',
            'estado' => isset($_GET['estado']) ? trim($_GET['estado']) : '',
        ];

        $u = session_user();
        $materias = $this->materiaModel->getAll();
        
        if ($u['rol'] === 'profesor' && $u['entidad_id']) {
            $materias = array_filter($materias, function ($m) use ($u) {
                return $m['id_profesor'] == $u['entidad_id'];
            });
        }

        if ($filtros['q']) {
            $q = strtolower($filtros['q']);
            $materias = array_filter($materias, function ($m) use ($q) {
                return strpos(strtolower($m['nombre']), $q) !== false
                    || strpos(strtolower($m['clave']),  $q) !== false
                    || strpos(strtolower($m['area']),   $q) !== false;
            });
        }
        if ($filtros['estado']) {
            $materias = array_filter($materias, function ($m) use ($filtros) {
                return $m['estado'] === $filtros['estado'];
            });
        }

        $ciclos_map = [];
        foreach ($this->cicloModel->getAll() as $c) {
            $ciclos_map[$c['id']] = $c['nombre'];
        }

        $grupos_map = [];
        foreach ($this->grupoModel->getAll() as $g) {
            $grupos_map[$g['id_grupo']] = $g['nombre'];
        }

        $salones_map = [];
        foreach ($this->salonModel->getAll() as $s) {
            $salones_map[$s['id_salon']] = $s['nombre'];
        }

        $this->view('materias/index', [
            'materias'   => $materias,
            'filtros'    => $filtros,
            'ciclos_map' => $ciclos_map,
            'grupos_map' => $grupos_map,
            'salones_map'=> $salones_map,
        ]);
    }


    public function create()
    {
        $u = session_user();
        if ($u['rol'] !== 'admin' && $u['rol'] !== 'director') {
            redirect(BASE_URL . 'dashboard', 'No tiene privilegios para realizar esta acción.', 'danger');
        }

        $errors = [];
        $datos  = [
            'clave' => '', 'nombre' => '', 'area' => '',
            'horas' => 4, 'grado' => '', 'ciclo_id' => '',
            'estado' => 'Activo', 'descripcion' => '',
            'docente_id' => '', 'salon_id' => '', 'grupo_id' => '',
            'dias' => [], 'horas_inicio' => [], 'horas_fin' => []
        ];
        $ciclos   = $this->cicloModel->getAll();
        $docentes = $this->docenteModel->getAll();
        // Filtrar solo docentes activos para asignar
        $docentes = array_filter($docentes, function($d) { return $d['estado'] == 1; });
        $salones  = $this->salonModel->getAll();
        $grupos   = $this->grupoModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'clave'       => trim($_POST['clave']       ?? ''),
                'nombre'      => trim($_POST['nombre']      ?? ''),
                'area'        => trim($_POST['area']        ?? ''),
                'horas'       => (int) ($_POST['horas']     ?? 4),
                'grado'       => $_POST['grado']            ?? '',
                'ciclo_id'    => $_POST['ciclo_id']         ?? '',
                'estado'      => $_POST['estado']           ?? 'Activo',
                'descripcion' => strip_tags(trim($_POST['descripcion'] ?? '')),
                'docente_id'  => $_POST['docente_id']       ?? '',
                'salon_id'    => $_POST['salon_id']         ?? '',
                'grupo_id'    => $_POST['grupo_id']         ?? '',
                'dias'         => $_POST['dias']            ?? [],
                'hora_inicio'  => $_POST['hora_inicio']     ?? [],
                'hora_fin'     => $_POST['hora_fin']        ?? [],
            ];

            if (empty($datos['clave'])) {
                $errors[] = 'La clave de la materia es obligatoria.';
            } elseif (!preg_match('/^[a-zA-Z0-9\-]+$/', $datos['clave'])) {
                $errors[] = 'La clave solo puede contener letras, números y guiones.';
            }

            if (empty($datos['ciclo_id'])) {
                $errors[] = 'El ciclo escolar es obligatorio.';
            }

            if ($datos['nombre'] === '') {
                $errors[] = 'El nombre de la materia es obligatorio.';
            } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $datos['nombre'])) {
                $errors[] = 'El nombre de la materia solo puede contener letras y acentos.';
            }

            if ($datos['horas'] < 2 || $datos['horas'] > 6) {
                $errors[] = 'Las horas asignadas deben ser mínimo 2 y máximo 6.';
            }

            if (empty($datos['dias'])) {
                $errors[] = 'Debe seleccionar al menos un día para el horario.';
            } elseif (count($datos['dias']) > 3) {
                $errors[] = 'Solo puede seleccionar hasta un máximo de 3 días.';
            }

            $hi_arr = [];
            $hf_arr = [];
            $total_horas = 0;

            if (empty($errors) && !empty($datos['dias'])) {
                foreach ($datos['dias'] as $i => $dia) {
                    $hi = $datos['hora_inicio'][$dia] ?? '';
                    $hf = $datos['hora_fin'][$dia] ?? '';

                    if (empty($hi) || empty($hf)) {
                        $errors[] = "Debe establecer la hora de inicio y fin para el día $dia.";
                        break;
                    }
                    if (strtotime($hi) >= strtotime($hf)) {
                        $errors[] = "La hora de inicio debe ser anterior a la hora de fin para el día $dia.";
                        break;
                    }

                    $hi_arr[] = $hi;
                    $hf_arr[] = $hf;

                    $mins = (strtotime($hf) - strtotime($hi)) / 60;
                    $total_horas += ($mins / 60);

                    // Validación de reglas estrictas de coincidencia de horas
                    if ($i == 1) { // Segundo día
                        if ($hi !== $hi_arr[0] || $hf !== $hf_arr[0]) {
                            $errors[] = "Si selecciona 2 o más días, el primer y segundo día deben coincidir exactamente en su horario.";
                            break;
                        }
                    }
                }

                if (empty($errors)) {
                    if (abs($total_horas - $datos['horas']) > 0.01) { // margen de error por flotantes
                        $errors[] = sprintf(
                            'El horario suma %.1f hora(s) semanales, pero la materia tiene asignadas %d hora(s). Ajuste los horarios.',
                            $total_horas, $datos['horas']
                        );
                    }
                }
            }

            // Validación de cruce de horarios
            if (empty($errors) && !empty($datos['dias'])) {
                $db = db_connect();
                
                foreach ($datos['dias'] as $index => $dia) {
                    $hi = $hi_arr[$index];
                    $hf = $hf_arr[$index];

                    // Verificar choque de salón
                    if (!empty($datos['salon_id'])) {
                        $st = $db->prepare("SELECT m.nombre, mh.hora_inicio, mh.hora_fin FROM materias m JOIN materia_horarios mh ON m.id_materia = mh.id_materia WHERE m.id_salon = ? AND mh.dia = ? AND (mh.hora_inicio < ? AND mh.hora_fin > ?)");
                        $st->execute([$datos['salon_id'], strtoupper($dia), $hf, $hi]);
                        if ($choque = $st->fetch()) {
                            $errors[] = "El salón ya está ocupado el día $dia de " . substr($choque['hora_inicio'], 0, 5) . " a " . substr($choque['hora_fin'], 0, 5) . " por la materia '{$choque['nombre']}'.";
                            break;
                        }
                    }
                    // Verificar choque de grupo
                    if (!empty($datos['grupo_id'])) {
                        $st = $db->prepare("SELECT m.nombre, mh.hora_inicio, mh.hora_fin FROM materias m JOIN materia_horarios mh ON m.id_materia = mh.id_materia WHERE m.id_grupo = ? AND mh.dia = ? AND (mh.hora_inicio < ? AND mh.hora_fin > ?)");
                        $st->execute([$datos['grupo_id'], strtoupper($dia), $hf, $hi]);
                        if ($choque = $st->fetch()) {
                            $errors[] = "El grupo ya tiene clase el día $dia de " . substr($choque['hora_inicio'], 0, 5) . " a " . substr($choque['hora_fin'], 0, 5) . " por la materia '{$choque['nombre']}'.";
                            break;
                        }
                    }
                    // Verificar choque de docente
                    if (!empty($datos['docente_id'])) {
                        $st = $db->prepare("SELECT m.nombre, mh.hora_inicio, mh.hora_fin FROM materias m JOIN materia_horarios mh ON m.id_materia = mh.id_materia WHERE m.id_profesor = ? AND mh.dia = ? AND (mh.hora_inicio < ? AND mh.hora_fin > ?)");
                        $st->execute([$datos['docente_id'], strtoupper($dia), $hf, $hi]);
                        if ($choque = $st->fetch()) {
                            $errors[] = "El docente ya tiene clase el día $dia de " . substr($choque['hora_inicio'], 0, 5) . " a " . substr($choque['hora_fin'], 0, 5) . " en la materia '{$choque['nombre']}'.";
                            break;
                        }
                    }
                }
            }

            if (empty($errors)) {
                try {
                    $newId = $this->materiaModel->create($datos);
                    $this->materiaModel->syncHorarios($newId, $datos['dias'], $hi_arr, $hf_arr);
                    redirect(BASE_URL . 'materias', 'Materia registrada correctamente.');
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
                        $errors[] = 'La clave de la materia ya está en uso por otra materia.';
                    } else {
                        throw $e;
                    }
                }
            }
        }

        $this->view('materias/create', [
            'errors'   => $errors,
            'datos'    => $datos,
            'ciclos'   => $ciclos,
            'docentes' => $docentes,
            'salones'  => $salones,
            'grupos'   => $grupos,
        ]);
    }

    /**
     * @param int|string $id
     */
    public function edit($id)
    {
        $u = session_user();
        if ($u['rol'] !== 'admin' && $u['rol'] !== 'director') {
            redirect(BASE_URL . 'dashboard', 'No tiene privilegios para realizar esta acción.', 'danger');
        }

        $materia = $this->materiaModel->getById($id);
        if (!$materia) {
            header('Location: ' . BASE_URL . 'materias');
            exit;
        }

        $errors = [];
        $datos  = $materia;
        $ciclos   = $this->cicloModel->getAll();
        $docentes = $this->docenteModel->getAll();
        // Filtrar solo docentes activos para asignar, o mantener al asignado actualmente aunque esté inactivo
        $docentes = array_filter($docentes, function($d) use ($materia) { 
            return $d['estado'] == 1 || $d['id_profesor'] == $materia['id_profesor']; 
        });
        $salones  = $this->salonModel->getAll();
        $grupos   = $this->grupoModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'id_materia'  => $id,
                'clave'       => trim($_POST['clave']       ?? ''),
                'nombre'      => trim($_POST['nombre']      ?? ''),
                'area'        => trim($_POST['area']        ?? ''),
                'horas'       => (int) ($_POST['horas']     ?? 4),
                'grado'       => $_POST['grado']            ?? '',
                'ciclo_id'    => $_POST['ciclo_id']         ?? '',
                'estado'      => $_POST['estado']           ?? 'Activo',
                'descripcion' => strip_tags(trim($_POST['descripcion'] ?? '')),
                'docente_id'  => $_POST['docente_id']       ?? '',
                'salon_id'    => $_POST['salon_id']         ?? '',
                'grupo_id'    => $_POST['grupo_id']         ?? '',
                'dias'         => $_POST['dias']            ?? [],
                'hora_inicio'  => $_POST['hora_inicio']     ?? [],
                'hora_fin'     => $_POST['hora_fin']        ?? [],
            ];

            if (empty($datos['clave'])) {
                $errors[] = 'La clave de la materia es obligatoria.';
            } elseif (!preg_match('/^[a-zA-Z0-9\-]+$/', $datos['clave'])) {
                $errors[] = 'La clave solo puede contener letras, números y guiones.';
            }

            if (empty($datos['ciclo_id'])) {
                $errors[] = 'El ciclo escolar es obligatorio.';
            }

            if ($datos['nombre'] === '') {
                $errors[] = 'El nombre de la materia es obligatorio.';
            } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $datos['nombre'])) {
                $errors[] = 'El nombre de la materia solo puede contener letras y acentos.';
            }

            if ($datos['horas'] < 2 || $datos['horas'] > 6) {
                $errors[] = 'Las horas asignadas deben ser mínimo 2 y máximo 6.';
            }

            if (empty($datos['dias'])) {
                $errors[] = 'Debe seleccionar al menos un día para el horario.';
            } elseif (count($datos['dias']) > 3) {
                $errors[] = 'Solo puede seleccionar hasta un máximo de 3 días.';
            }

            $hi_arr = [];
            $hf_arr = [];
            $total_horas = 0;

            if (empty($errors) && !empty($datos['dias'])) {
                foreach ($datos['dias'] as $i => $dia) {
                    $hi = $datos['hora_inicio'][$dia] ?? '';
                    $hf = $datos['hora_fin'][$dia] ?? '';

                    if (empty($hi) || empty($hf)) {
                        $errors[] = "Debe establecer la hora de inicio y fin para el día $dia.";
                        break;
                    }
                    if (strtotime($hi) >= strtotime($hf)) {
                        $errors[] = "La hora de inicio debe ser anterior a la hora de fin para el día $dia.";
                        break;
                    }

                    $hi_arr[] = $hi;
                    $hf_arr[] = $hf;

                    $mins = (strtotime($hf) - strtotime($hi)) / 60;
                    $total_horas += ($mins / 60);

                    // Validación de reglas estrictas de coincidencia de horas
                    if ($i == 1) { // Segundo día
                        if ($hi !== $hi_arr[0] || $hf !== $hf_arr[0]) {
                            $errors[] = "Si selecciona 2 o más días, el primer y segundo día deben coincidir exactamente en su horario.";
                            break;
                        }
                    }
                }

                if (empty($errors)) {
                    if (abs($total_horas - $datos['horas']) > 0.01) { // margen de error por flotantes
                        $errors[] = sprintf(
                            'El horario suma %.1f hora(s) semanales, pero la materia tiene asignadas %d hora(s). Ajuste los horarios.',
                            $total_horas, $datos['horas']
                        );
                    }
                }
            }

            // Validación de cruce de horarios (excluyendo la materia actual)
            if (empty($errors) && !empty($datos['dias'])) {
                $db = db_connect();
                
                foreach ($datos['dias'] as $index => $dia) {
                    $hi = $hi_arr[$index];
                    $hf = $hf_arr[$index];

                    // Verificar choque de salón
                    if (!empty($datos['salon_id'])) {
                        $st = $db->prepare("SELECT m.nombre, mh.hora_inicio, mh.hora_fin FROM materias m JOIN materia_horarios mh ON m.id_materia = mh.id_materia WHERE m.id_materia != ? AND m.id_salon = ? AND mh.dia = ? AND (mh.hora_inicio < ? AND mh.hora_fin > ?)");
                        $st->execute([$id, $datos['salon_id'], strtoupper($dia), $hf, $hi]);
                        if ($choque = $st->fetch()) {
                            $errors[] = "El salón ya está ocupado el día $dia de " . substr($choque['hora_inicio'], 0, 5) . " a " . substr($choque['hora_fin'], 0, 5) . " por la materia '{$choque['nombre']}'.";
                            break;
                        }
                    }
                    // Verificar choque de grupo
                    if (!empty($datos['grupo_id'])) {
                        $st = $db->prepare("SELECT m.nombre, mh.hora_inicio, mh.hora_fin FROM materias m JOIN materia_horarios mh ON m.id_materia = mh.id_materia WHERE m.id_materia != ? AND m.id_grupo = ? AND mh.dia = ? AND (mh.hora_inicio < ? AND mh.hora_fin > ?)");
                        $st->execute([$id, $datos['grupo_id'], strtoupper($dia), $hf, $hi]);
                        if ($choque = $st->fetch()) {
                            $errors[] = "El grupo ya tiene clase el día $dia de " . substr($choque['hora_inicio'], 0, 5) . " a " . substr($choque['hora_fin'], 0, 5) . " por la materia '{$choque['nombre']}'.";
                            break;
                        }
                    }
                    // Verificar choque de docente
                    if (!empty($datos['docente_id'])) {
                        $st = $db->prepare("SELECT m.nombre, mh.hora_inicio, mh.hora_fin FROM materias m JOIN materia_horarios mh ON m.id_materia = mh.id_materia WHERE m.id_materia != ? AND m.id_profesor = ? AND mh.dia = ? AND (mh.hora_inicio < ? AND mh.hora_fin > ?)");
                        $st->execute([$id, $datos['docente_id'], strtoupper($dia), $hf, $hi]);
                        if ($choque = $st->fetch()) {
                            $errors[] = "El docente ya tiene clase el día $dia de " . substr($choque['hora_inicio'], 0, 5) . " a " . substr($choque['hora_fin'], 0, 5) . " en la materia '{$choque['nombre']}'.";
                            break;
                        }
                    }
                }
            }

            if (empty($errors)) {
                try {
                    $this->materiaModel->update($id, $datos);
                    $this->materiaModel->syncHorarios($id, $datos['dias'], $hi_arr, $hf_arr);
                    redirect(BASE_URL . 'materias', 'Materia actualizada correctamente.');
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
                        $errors[] = 'La clave de la materia ya está en uso por otra materia.';
                    } else {
                        throw $e;
                    }
                }
            }
        }

        $this->view('materias/edit', [
            'errors'   => $errors,
            'datos'    => $datos,
            'ciclos'   => $ciclos,
            'docentes' => $docentes,
            'salones'  => $salones,
            'grupos'   => $grupos,
        ]);
    }

    /**
     * @param int|string $id
     */
    public function delete($id)
    {
        $u = session_user();
        if ($u['rol'] !== 'admin' && $u['rol'] !== 'director') {
            redirect(BASE_URL . 'dashboard', 'No tiene privilegios para realizar esta acción.', 'danger');
        }

        try {
            $this->materiaModel->delete($id);
            redirect(BASE_URL . 'materias', 'Materia eliminada correctamente');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), '1451') !== false) {
                redirect(BASE_URL . 'materias', delete_error_msg($e), 'danger');
            }
            throw $e;
        }
    }
}
