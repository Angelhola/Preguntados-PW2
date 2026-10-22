<?php

namespace app\controller;

use Redirect;
use Request;

class LoginController
{
    private $model;

    private $render;

    public function __construct($model, $render)
    {
        $this->model = $model;
        $this->render = $render;
    }

    public function login()
    {
        $usuario = $this->model->autenticar(Request::post('usuario'), Request::post('password'));

        if ($usuario === null) {
            Redirect::to('?controller=login&method=show&login_error=1');
        }

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario'] = $usuario['usuario'];
        Redirect::to('?controller=evento&method=home');
    }

    public function show()
    {
        $data = [];
        $this->render->renderiza("login", $data);
    }

    public function register()
    {
        $data = [];
        $this->render->renderiza("register", $data);
    }

    public function registrar()
    {
        $usuario = Request::post('usuario');
        $email = Request::post('email');
        $password = Request::post('password');
        $nombreCompleto = Request::post('nombreCompleto');
        $genero = Request::post('genero');
        $pais = Request::post('pais');
        $ciudad = Request::post('ciudad');
        $region = Request::post('region');


        $foto = null;
        if (isset($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['fotoPerfil']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $foto = uniqid('perfil_') . '.' . $ext;
                move_uploaded_file($_FILES['fotoPerfil']['tmp_name'], 'public/img/perfiles/' . $foto);
            }
        }

        $this->model->registrar($usuario, $email, $password, $nombreCompleto, $genero,  $foto, $ciudad,$pais, $region);
        Redirect::to('?controller=login&method=show');
    }

    public function home()
    {
        $data = [];
        $this->render->renderiza("home", $data);
    }

    public function logout()
    {
        $_SESSION = [];
        session_destroy();
        Redirect::to('?controller=login&method=show');
    }
}
