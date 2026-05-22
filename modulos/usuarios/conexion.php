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
        $st = $this->db->query("
            SELECT u.*,
                   CASE 
                       WHEN a.id_alumno IS NOT NULL THEN 'alumno'
                       WHEN p.id_profesor IS NOT NULL THEN 'docente'
                       ELSE 'admin'
                   END as rol
            FROM usuarios u
            LEFT JOIN alumnos a ON u.id_usuario = a.id_usuario
            LEFT JOIN profesores p ON u.id_usuario = p.id_usuario
            ORDER BY u.nombre_usuario
        ");
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
        // Borrado lógico: Cambiar el estado del usuario a 0 (Inactivo)
        $st = $this->db->prepare("UPDATE usuarios SET estado = 0 WHERE id_usuario = ?");
        $success = $st->execute([$id]);

        if ($success) {
            // Desactivar también en cascada en las tablas de alumnos y profesores vinculados a este usuario
            $this->db->prepare("UPDATE alumnos SET estado = 0 WHERE id_usuario = ?")->execute([$id]);
            $this->db->prepare("UPDATE profesores SET estado = 0 WHERE id_usuario = ?")->execute([$id]);
        }

        return $success;
    }
}
