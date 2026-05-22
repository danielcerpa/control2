<?php
// app/Controllers/SalonesController.php

class SalonesController extends Controller
{
    /** @var Salon */
    private $salonModel;

    public function __construct()
    {
        require_perm('salones');
        $this->salonModel = new Salon();
    }

    public function index()
    {
        $filtros = [
            'q' => isset($_GET['q']) ? trim($_GET['q']) : '',
        ];

        $salones = $this->salonModel->getAll();

        if ($filtros['q']) {
            $q = strtolower($filtros['q']);
            $salones = array_filter($salones, function ($s) use ($q) {
                return strpos(strtolower($s['nombre']),   $q) !== false
                    || strpos(strtolower($s['edificio']), $q) !== false
                    || strpos(strtolower($s['tipo']),     $q) !== false;
            });
        }

        $this->view('salones/index', [
            'salones' => $salones,
            'filtros' => $filtros,
        ]);
    }


    public function create()
    {
        $edificios_validos = ['Edificio Principal', 'Edificio A', 'Edificio B', 'Edificio C', 'Anexo', 'Laboratorios'];
        $errors = [];
        $datos  = [
            'nombre' => '', 'edificio' => '', 'tipo' => 'Aula',
            'capacidad' => '', 'estado' => 'Activo', 'descripcion' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'nombre'      => trim($_POST['nombre']      ?? ''),
                'edificio'    => trim($_POST['edificio']    ?? ''),
                'tipo'        => $_POST['tipo']             ?? 'Aula',
                'capacidad'   => (int) ($_POST['capacidad'] ?? 0),
                'estado'      => $_POST['estado']           ?? 'Activo',
                'descripcion' => strip_tags(trim($_POST['descripcion'] ?? '')),
            ];

            if ($datos['nombre'] === '') {
                $errors[] = 'El nombre del salón es obligatorio.';
            } elseif (!preg_match('/^[A-Za-z0-9\-\s]+$/', $datos['nombre'])) {
                $errors[] = 'El nombre solo debe aceptar letras, números, espacios y "-".';
            }

            if ($datos['edificio'] === '' || !in_array($datos['edificio'], $edificios_validos)) {
                $errors[] = 'Debe seleccionar un edificio válido.';
            }

            if ($datos['capacidad'] <= 0) {
                $errors[] = 'La capacidad debe ser mayor a 0.';
            } elseif ($datos['capacidad'] > 40) {
                $errors[] = 'La capacidad máxima de cada salón es de 40 alumnos.';
            }

            if (empty($errors)) {
                $db = db_connect();
                $st = $db->prepare("SELECT id_salon FROM salones WHERE nombre = ?");
                $st->execute([$datos['nombre']]);
                if ($st->fetch()) {
                    $errors[] = 'Ya existe un salón con ese nombre.';
                }
            }

            if (empty($errors)) {
                $this->salonModel->create($datos);
                redirect(BASE_URL . 'salones', 'Salón creado correctamente');
            }
        }

        $this->view('salones/create', [
            'errors' => $errors,
            'datos'  => $datos,
            'edificios_validos' => $edificios_validos,
        ]);
    }

    /**
     * @param int|string $id
     */
    public function edit($id)
    {
        $edificios_validos = ['Edificio Principal', 'Edificio A', 'Edificio B', 'Edificio C', 'Anexo', 'Laboratorios'];
        $salon = $this->salonModel->getById($id);
        if (!$salon) {
            header('Location: ' . BASE_URL . 'salones');
            exit;
        }

        $errors = [];
        $datos  = $salon;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = array_merge($salon, [
                'nombre'      => trim($_POST['nombre']      ?? ''),
                'edificio'    => trim($_POST['edificio']    ?? ''),
                'tipo'        => $_POST['tipo']             ?? 'Aula',
                'capacidad'   => (int) ($_POST['capacidad'] ?? 0),
                'estado'      => $_POST['estado']           ?? 'Activo',
                'descripcion' => strip_tags(trim($_POST['descripcion'] ?? '')),
            ]);

            if ($datos['nombre'] === '') {
                $errors[] = 'El nombre del salón es obligatorio.';
            } elseif (!preg_match('/^[A-Za-z0-9\-\s]+$/', $datos['nombre'])) {
                $errors[] = 'El nombre solo debe aceptar letras, números, espacios y "-".';
            }

            if ($datos['edificio'] === '' || !in_array($datos['edificio'], $edificios_validos)) {
                $errors[] = 'Debe seleccionar un edificio válido.';
            }

            if ($datos['capacidad'] <= 0) {
                $errors[] = 'La capacidad debe ser mayor a 0.';
            } elseif ($datos['capacidad'] > 40) {
                $errors[] = 'La capacidad máxima de cada salón es de 40 alumnos.';
            }

            if (empty($errors)) {
                $db = db_connect();
                $st = $db->prepare("SELECT id_salon FROM salones WHERE nombre = ? AND id_salon != ?");
                $st->execute([$datos['nombre'], $id]);
                if ($st->fetch()) {
                    $errors[] = 'Ya existe un salón con ese nombre.';
                }
            }

            if (empty($errors)) {
                $this->salonModel->update($id, $datos);
                redirect(BASE_URL . 'salones', 'Se guardaron correctamente los cambios');
            }
        }

        $this->view('salones/edit', [
            'errors' => $errors,
            'datos'  => $datos,
            'edificios_validos' => $edificios_validos,
        ]);
    }

    /**
     * @param int|string $id
     */
    public function delete($id)
    {
        try {
            $this->salonModel->delete($id);
            redirect(BASE_URL . 'salones', 'Salón eliminado correctamente');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), '1451') !== false) {
                redirect(BASE_URL . 'salones', delete_error_msg($e), 'danger');
            }
            throw $e;
        }
    }
}
