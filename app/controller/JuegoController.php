<?php

namespace app\controller;

use Redirect;
use Request;
class JuegoController
{
    private $model;
    private $render;

    public function __construct($model, $render)
    {
        $this->model = $model;
        $this->render = $render;
    }

    public function ruleta()
    {
        if (!isset($_SESSION['usuario'])) {
            Redirect::to('?controller=login&method=show');
        }

        $data["usuario"] = $_SESSION['usuario'];
        $data["categorias"] = $this->model->getCategorias();
        $data["puntos"] = $_SESSION['puntos'] ?? 0;
        $this->render->renderizaArcade("ruleta", $data);
    }

    public function girar()
    {
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            exit;
        }

        $categoria = $this->model->sortearCategoria();
        $_SESSION['categoria_actual'] = $categoria["nombre"];
        $_SESSION['pregunta_actual'] = $this->model->getPreguntaAleatoria($categoria["nombre"]);
        $_SESSION['respondida'] = false;

        header("Content-Type: application/json");
        echo json_encode(["categoria" => $categoria["nombre"]]);
        exit;
    }

    public function pregunta()
    {
        if (!isset($_SESSION['usuario'])) {
            Redirect::to('?controller=login&method=show');
            return;
        }

        $pregunta = $_SESSION['pregunta_actual'] ?? null;
        if ($pregunta === null || ($_SESSION['respondida'] ?? true)) {
            Redirect::to('?controller=juego&method=ruleta');
            return;
        }

        $opciones = [];
        foreach ($pregunta["opciones"] as $indice => $texto) {
            $opciones[] = ["indice" => $indice, "texto" => $texto];
        }

        $data["categoria"] = $_SESSION['categoria_actual'];
        $data["texto"] = $pregunta["texto"];
        $data["opciones"] = $opciones;
        $data["puntos"] = $_SESSION['puntos'] ?? 0;
        $this->render->renderizaArcade("pregunta", $data);
    }

    public function responder()
    {
        header("Content-Type: application/json");
        $pregunta = $_SESSION['pregunta_actual'] ?? null;

        if (!isset($_SESSION['usuario']) || $pregunta === null || ($_SESSION['respondida'] ?? true)) {
            http_response_code(400);
            echo json_encode(["error" => "Pregunta no disponible"]);
            exit;
        }

        $elegida = (int) Request::post('opcion', -1);
        $_SESSION['respondida'] = true;

        $acerto = $elegida === $pregunta["correcta"];
        if ($acerto) {
            $_SESSION['puntos'] = ($_SESSION['puntos'] ?? 0) + 1;
        }

        echo json_encode([
            "acerto" => $acerto,
            "correcta" => $pregunta["correcta"],
            "puntos" => $_SESSION['puntos'] ?? 0,
        ]);
        exit;
    }
}