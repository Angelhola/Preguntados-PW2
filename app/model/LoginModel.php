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

        // A propósito sin prepared statements: lo arreglamos en la clase de refactor
        $sql = "SELECT id, usuario FROM usuario WHERE usuario = '$usuario' AND password = '$passwordHash'";

        return $this->database->queryOne($sql);
    }

//    public function registrar($usuario, $email, $password)
//    {
//        $passwordHash = md5($password);
//
//        $sql = "INSERT INTO usuario (usuario, email, password) VALUES ('$usuario', '$email', '$passwordHash')";
//
//        return $this->database->execute($sql);
        public function registrar($usuario, $email, $password, $nombreCompleto, $anio_nacimiento ,$genero, $foto,$ciudad,$pais,$region){
        $passwordHash = md5($password);
        $sql = "INSERT INTO usuario (usuario, email, password, nombre_completo, genero, foto_perfil,ciudad,pais,region) VALUES ('$usuario', '$email', '$passwordHash', '$nombreCompleto','$anio_nacimiento', '$genero', '$foto','$ciudad','$pais','$region')";
        return $this->database->execute($sql);
    }
}
