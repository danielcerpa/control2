<?php
require_once 'config/db.php';
try {
    $db = db_connect();
    $db->exec("ALTER TABLE alumnos ADD COLUMN fecha_ingreso DATE NULL AFTER fecha_nac;");
    echo "Columna fecha_ingreso añadida exitosamente.";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') { // Column already exists
        echo "La columna ya existe.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
