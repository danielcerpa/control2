<?php
require_once 'config/init.php';
$pdo = db_connect();

$st = $pdo->query("SELECT * FROM profesor_materia WHERE id_profesor = 9 OR id_profesor = 12");
print_r($st->fetchAll(PDO::FETCH_ASSOC));
