<?php
require_once 'config/init.php';

$db = db_connect();

echo "ALUMNOS:\n";
$st = $db->query("SELECT id_alumno, nombre, ruta_foto FROM alumnos");
print_r($st->fetchAll(PDO::FETCH_ASSOC));

echo "PROFESORES:\n";
$st = $db->query("SELECT id_profesor, nombre_completo, ruta_foto FROM profesores");
print_r($st->fetchAll(PDO::FETCH_ASSOC));
