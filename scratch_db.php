<?php
require 'config/db.php';

try {
    $db = db_connect();
    $db->exec("DELETE FROM calificaciones;");
    echo "Calificaciones borradas.<br>";

    // Agregar UNIQUE KEY si no existe
    try {
        $db->exec("ALTER TABLE calificaciones ADD UNIQUE KEY uq_insc_parcial (id_inscripcion, etiqueta_periodo);");
        echo "UNIQUE KEY agregado correctamente.";
    } catch (PDOException $e) {
        if ($e->getCode() == '42000' && strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "UNIQUE KEY ya existía, sin cambios.";
        } else {
            throw $e;
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
