<?php
// app/Models/Usuario.php

class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getAll()
    {
        $st = $this->db->query("SELECT * FROM usuarios ORDER BY nombre_usuario");
        return $st->fetchAll();
    }

    public function getById($id)
    {
        $st = $this->db->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
        $st->execute([$id]);
        return $st->fetch();
    }

    public function getByNombreUsuario($nombre_usuario)
    {
        $st = $this->db->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ?");
        $st->execute([$nombre_usuario]);
        return $st->fetch();
    }

    public function create($datos, $contrasena)
    {
        $st = $this->db->prepare(
            "INSERT INTO usuarios (nombre_usuario, contrasena, estado)
             VALUES (?,?,?)"
        );
        $success = $st->execute(array(
            $datos['nombre_usuario'],
            $contrasena,
            $datos['estado'] ?? 1
        ));
        if ($success) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function update($id, $datos, $contrasena = null)
    {
        if ($contrasena) {
            $st = $this->db->prepare(
                "UPDATE usuarios SET nombre_usuario=?, contrasena=?, estado=?
                 WHERE id_usuario=?"
            );
            return $st->execute(array(
                $datos['nombre_usuario'],
                $contrasena,
                $datos['estado'],
                $id
            ));
        } else {
            $st = $this->db->prepare(
                "UPDATE usuarios SET nombre_usuario=?, estado=?
                 WHERE id_usuario=?"
            );
            return $st->execute(array(
                $datos['nombre_usuario'],
                $datos['estado'],
                $id
            ));
        }
    }

    public function delete($id)
    {
        $st = $this->db->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        return $st->execute([$id]);
    }
}
