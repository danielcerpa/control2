<?php

define('BASE_URL', '/control2/');

date_default_timezone_set('America/Mexico_City');

// Reporte de errores (en producción cambiar a 0)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helpers
require_once dirname(__FILE__) . '/db.php';
require_once dirname(__FILE__) . '/auth.php';

// Función utilitaria: escapar salida HTML
function e($str)
{
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// Función utilitaria: redirección con mensaje flash
function redirect($url, $msg = '', $tipo = 'success')
{
    if ($msg) {
        $_SESSION['flash_msg']  = $msg;
        $_SESSION['flash_tipo'] = $tipo;
    }
    header('Location: ' . $url);
    exit;
}

// Genera mensaje amigable cuando un registro no se puede eliminar por FK
function delete_error_msg(PDOException $e)
{
    $tabla_map = [
        'materias'      => 'Materias',
        'materia_horarios' => 'Horarios de Materia',
        'inscripciones' => 'Inscripciones de Alumnos',
        'calificaciones'=> 'Calificaciones',
        'alumno_grupo'  => 'Asignaciones de Grupo',
        'horarios'      => 'Horarios',
        'grupos'        => 'Grupos',
        'alumnos'       => 'Alumnos',
        'profesores'    => 'Docentes',
        'salones'       => 'Salones',
        'ciclos_escolares' => 'Ciclos Escolares',
    ];

    $tabla_legible = 'otro módulo';
    if (preg_match('/a foreign key constraint fails \([^.]*\.`([^`]+)`/i', $e->getMessage(), $m)) {
        $tabla_legible = $tabla_map[$m[1]] ?? ucfirst($m[1]);
    }

    return "No se puede eliminar este registro porque está siendo utilizado en: {$tabla_legible}. Elimine primero los registros relacionados.";
}

// Recoger mensaje flash y limpiarlo
function get_flash()
{
    if (!empty($_SESSION['flash_msg'])) {
        $msg  = $_SESSION['flash_msg'];
        $tipo = isset($_SESSION['flash_tipo']) ? $_SESSION['flash_tipo'] : 'success';
        unset($_SESSION['flash_msg'], $_SESSION['flash_tipo']);
        return array('msg' => $msg, 'tipo' => $tipo);
    }
    return null;
}

// Formatear fecha MySQL → dd/mm/yyyy
function fmt_fecha($mysql_date)
{
    if (!$mysql_date) return '—';
    $parts = explode('-', $mysql_date);
    if (count($parts) < 3) return $mysql_date;
    return $parts[2] . '/' . $parts[1] . '/' . $parts[0];
}

// Retorna el ciclo escolar activo como array, o [] si no hay ninguno
function ciclo_activo()
{
    static $ciclo = null;
    if ($ciclo !== null) return $ciclo;

    try {
        $pdo = db_connect();
        $st  = $pdo->prepare("SELECT id, nombre, fecha_inicio, fecha_fin, estado
                               FROM ciclos_escolares
                               WHERE estado = 'Activo'
                               ORDER BY fecha_inicio DESC
                               LIMIT 1");
        $st->execute();
        $ciclo = $st->fetch() ?: [];
    } catch (Exception $e) {
        $ciclo = [];
    }

    return $ciclo;
}

// Obtiene el valor de una clave de configuración con caché estático
function get_config($clave, $reset = false)
{
    static $cache = null;

    if ($reset || $cache === null) {
        $cache = [];
        try {
            $pdo = db_connect();
            $st  = $pdo->query("SELECT clave, valor FROM configuracion");
            while ($row = $st->fetch()) {
                $cache[$row['clave']] = $row['valor'];
            }
        } catch (Exception $e) {
            // Tabla aún no existe o error: devolver null silenciosamente
        }
    }

    if ($clave === null) return $cache;
    return $cache[$clave] ?? null;
}

