<?php
try {
    $dsn = 'mysql:host=127.0.0.1;port=3307;dbname=control2;charset=utf8';
    $pdo = new PDO($dsn, 'root', 'C3cyt3g21', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("ALTER TABLE materias ADD COLUMN clave VARCHAR(20) UNIQUE DEFAULT NULL AFTER id_materia;");
    echo "Success";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Already exists";
    } else {
        echo $e->getMessage();
    }
}
