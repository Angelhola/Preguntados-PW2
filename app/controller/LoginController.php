<?php

namespace app\controller;

use Redirect;
use Request;
use Mailer;

require_once __DIR__ . '/../../helper/Mailer.php';

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
    public function inicio()
    {
        $this->render->renderizaArcade("inicio", []);
    }

    public function show()
    {$this->render->renderizaArcade("login", [
        'error'   => Request::get('login_error') !== null ? 'Usuario, contraseña incorrectos o cuenta sin verificar' : '',
        'mensaje' => Request::get('registrado') !== null ? 'Revisá tu mail para activar la cuenta' : ''
    ]);
    }

    public function register()
    {
        $this->render->renderizaArcade("register", [ 'error'=> Request::get('erorr','')]);
    }

    public function registrar()
    {
        $usuario = Request::post('usuario');
        $email = Request::post('email');
        $password = Request::post('password');
        $passwordRepetida = Request::post('passwordRepetida', '');
        $anio = Request::post('anioNacimiento', '');
        $nombreCompleto = Request::post('nombreCompleto');
        $genero = Request::post('genero');
        $pais = Request::post('pais');
        $ciudad = Request::post('ciudad');
        $region = Request::post('region');

        $error = null;
        if ($usuario === '' || $email === '' || $password === '' || $nombreCompleto === '') {
            $error = 'Completá todos los campos obligatorios';
        } elseif ($password !== $passwordRepetida) {
            $error = 'Las contraseñas no coinciden';
        } elseif (!ctype_digit($anio) || $anio < 1900 || $anio > date('Y')) {
            $error = 'El año de nacimiento no es válido';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El email no es válido';
        } elseif ($ciudad === '' || $pais === '') {
            $error = 'Elegí tu ciudad de la lista de sugerencias';
        } elseif ($this->model->existeUsuario($usuario, $email)) {
            $error = 'El usuario o el email ya están registrados';
        }

        if ($error !== null) {
            Redirect::to('?controller=login&method=register&error=' . urlencode($error));
        }

        $foto = null;
        if (isset($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['fotoPerfil']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $foto = uniqid('perfil_') . '.' . $ext;
                move_uploaded_file($_FILES['fotoPerfil']['tmp_name'], 'public/img/perfiles/' . $foto);
            }
        }
        $token = bin2hex(random_bytes(16));

        $this->model->registrar($usuario, $email, $password, $nombreCompleto,$anio, $genero,  $foto, $ciudad,$pais, $region,$token);
        Mailer::enviarVerificacion($email, $nombreCompleto, $token);
        Redirect::to('?controller=login&method=show&registrado=1');
    }
    public function verificar()
    {
        $token = Request::get('token', '');
        $ok = $token !== '' && $this->model->verificar($token) > 0;

        $this->render->renderizaArcade("login", [
            'mensaje' => $ok ? 'Cuenta activada, ya podés iniciar sesión' : '',
            'error'   => $ok ? '' : 'El link es inválido o ya fue usado'
        ]);
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


        public function perfil()
    {
        $nombreUsuario = $_SESSION['usuario'] ?? null;
        $usuarioData = $this->model->getUsuarioByUsername($nombreUsuario);
        $emailAMostrar = !empty($usuarioData['email']) ? $usuarioData['email'] : 'pendiente@mail.com';
        $data = [];
        $data["usuario"] = $_SESSION['usuario'] ?? null;
        $data["email"] = $emailAMostrar;
        $data["verificado"] = $usuarioData['verificado'] ?? 0; //para que el botón de verificar sepa si mostrarse o no
        $data["logros"] = [
            ["nombre" => "Primera partida", "icono" => "★", "estado" => "desbloqueado"],
            ["nombre" => "10 aciertos seguidos", "icono" => "🔥", "estado" => "bloqueado"],
            ["nombre" => "Ranking top 10", "icono" => "👑", "estado" => "bloqueado"],
        ];

        $this->render->renderizaArcade("perfil", $data);
    }


    public function game()
    {
        $data = [];
        $this->render->renderizaArcade("game", $data);
    }

    public function ranking()
    {
        $data = [];
        $this->render->renderizaArcade("ranking", $data);
    }
}
