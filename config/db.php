<?php
define('DB_HOST', 'localhost:3307');
define('DB_NAME', 'control_escolar');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8');

function db_connect()
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    try {
        $dsn = 'mysql:host=' . DB_HOST
            . ';dbname=' . DB_NAME
            . ';charset=' . DB_CHARSET;
        $options = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        );
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // En producción no mostrar el error al usuario
        die('<div style="padding:20px;background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;font-family:Arial">'
            . '<strong>Error de conexión a la base de datos.</strong><br>'
            . 'Verifique la configuración en config/db.php<br>'
            . '<small>' . htmlspecialchars($e->getMessage()) . '</small>'
            . '</div>');
    }
    return $pdo;
}
