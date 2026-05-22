<?php
$host = 'localhost:3307';
$db   = 'control2';
$user = 'root';
$pass = 'C3cyt3g21';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("ALTER TABLE profesores ADD COLUMN email VARCHAR(120) AFTER telefono;");
    echo "Column added successfully.";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') { // Duplicate column name
        echo "Column already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
