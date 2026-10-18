<?php

namespace app\controller;

use Redirect;
use Request;

class LoginController
{
    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function login()
    {
        $usuario = $this->model->autenticar(Request::post('usuario'), Request::post('password'));

        if ($usuario === null) {
            Redirect::to('?controller=evento&method=login&login_error=1');
        }

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario'] = $usuario['usuario'];
        Redirect::to('?controller=evento&method=home');
    }

    public function logout()
    {
        $_SESSION = [];
        session_destroy();
        Redirect::to('?controller=evento&method=login');
    }
}
