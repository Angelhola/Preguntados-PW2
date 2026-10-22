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
