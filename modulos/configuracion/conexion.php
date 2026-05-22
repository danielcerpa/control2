<?php

class Configuracion
{
    private $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    /** Obtiene todos los registros de configuración */
    public function getAll()
    {
        $st = $this->db->query("SELECT clave, valor, descripcion FROM configuracion ORDER BY clave");
        return $st->fetchAll();
    }

    /** Obtiene el valor de una clave específica */
    public function get($clave)
    {
        $st = $this->db->prepare("SELECT valor FROM configuracion WHERE clave = ?");
        $st->execute([$clave]);
        $row = $st->fetch();
        return $row ? $row['valor'] : null;
    }

    /** Actualiza el valor de una clave */
    public function set($clave, $valor)
    {
        $st = $this->db->prepare(
            "INSERT INTO configuracion (clave, valor) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)"
        );
        return $st->execute([$clave, $valor]);
    }
}
