<?php

namespace app\model;
class LoginModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function autenticar($usuario, $password)
    {
        $passwordHash = md5($password);
        $sql = "SELECT id, usuario FROM usuario WHERE usuario = '$usuario' AND password = '$passwordHash' AND verificado = 1";
        return $this->database->queryOne($sql);
    }

    public function existeUsuario($usuario, $email)
    {$sql = "SELECT id FROM usuario WHERE usuario = '$usuario' OR email = '$email'";
        $resultado = $this->database->queryOne($sql);

        return $resultado !== null && $resultado !== false;
    }

    public function registrar($usuario, $email, $password, $nombreCompleto, $anio, $genero, $foto, $ciudad, $pais, $region, $token)
    {
        $passwordHash = md5($password);
        $sql = "INSERT INTO usuario (usuario, email, password, nombre_completo, anio_nacimiento, genero, foto_perfil, ciudad, pais, region, token_verificacion)
            VALUES ('$usuario', '$email', '$passwordHash', '$nombreCompleto', $anio, '$genero', '$foto', '$ciudad', '$pais', '$region', '$token')";
        return $this->database->execute($sql);
    }

    public function verificar($token)
    {
        $sql = "UPDATE usuario SET verificado = 1, token_verificacion = NULL WHERE token_verificacion = '$token'";
        return $this->database->execute($sql);
    }
    public function getUsuarioByUsername($usuario)
{
    $sql = "SELECT * FROM usuario WHERE usuario = '$usuario'";
    return $this->database->queryOne($sql);
}
}
