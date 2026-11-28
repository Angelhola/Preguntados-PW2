<?php

namespace app\controller;

use Redirect;
use Request;

class JuegoController
{
    private const SEGUNDOS_PREGUNTA = 20;
    private const SEGUNDOS_LIMITE = 22;
    private const SEGUNDOS_CONTINUAR = 10;
    private const MARGEN_CONTINUAR = 3;
    private const PRECIO_VIDA_EXTRA = 50;
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
            Redirect::to('login/show');
            return;
        }

        $this->resolverPerdidaPendiente();
        unset($_SESSION['pregunta_actual']);

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

        $this->resolverPerdidaPendiente();
        if (empty($_SESSION['partida_id'])) {
            $_SESSION['partida_id'] = $this->model->crearPartida((int) $_SESSION['usuario_id']);
        }

        $usuarioId = (int) $_SESSION['usuario_id'];

        $categoria = $this->model->sortearCategoria();
        $_SESSION['categoria_actual'] = $categoria["nombre"];
        $_SESSION['pregunta_actual'] = $this->model->getPreguntaAleatoria(
            $categoria["nombre"],
            $usuarioId,
            $_SESSION['ultima_pregunta'] ?? null
        );
        $_SESSION['ultima_pregunta'] = $_SESSION['pregunta_actual']["texto"] ?? null;

        if (!empty($_SESSION['pregunta_actual']["id"])) {
            $this->model->marcarComoVista($usuarioId, (int) $_SESSION['pregunta_actual']["id"]);
        }

        $_SESSION['respondida'] = false;
        $_SESSION['pregunta_inicio'] = null;

        header("Content-Type: application/json");
        echo json_encode(["categoria" => $categoria["nombre"]]);
        exit;
    }

    public function pregunta()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        if (!isset($_SESSION['usuario'])) {
            Redirect::to('login/show');
            return;
        }

        $this->resolverPerdidaPendiente();

        $pregunta = $_SESSION['pregunta_actual'] ?? null;
        if ($pregunta === null || ($_SESSION['respondida'] ?? true)) {
            Redirect::to('juego/ruleta');
            return;
        }

        if (empty($_SESSION['pregunta_inicio'])) {
            $_SESSION['pregunta_inicio'] = time();
        }

        $opciones = [];
        foreach ($pregunta["opciones"] as $indice => $texto) {
            $opciones[] = ["indice" => $indice, "texto" => $texto];
        }

        $data["categoria"] = $_SESSION['categoria_actual'];
        $data["texto"] = $pregunta["texto"];
        $data["opciones"] = $opciones;
        $data["puntos"] = $_SESSION['puntos'] ?? 0;
        $transcurrido = time() - $_SESSION['pregunta_inicio'];
        $data["restante"] = max(0, self::SEGUNDOS_PREGUNTA - $transcurrido);

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
        $transcurrido = time() - ($_SESSION['pregunta_inicio'] ?? 0);
        $fueraDeTiempo = $transcurrido > self::SEGUNDOS_LIMITE;
        $acerto = !$fueraDeTiempo && $elegida === $pregunta["correcta"];
        $final = $_SESSION['puntos'] ?? 0;
        $puedeContinuar = false;

        if (!empty($_SESSION['partida_id']) && isset($pregunta["id"])) {
            $this->model->registrarRespuestaPartida(
                (int) $_SESSION['partida_id'],
                (int) $pregunta["id"],
                $acerto
            );
        }

        if ($acerto) {
            $_SESSION['puntos'] = $final + 1;
            $this->model->sumarPuntaje($_SESSION['usuario'], 1);
        } elseif ($final > 0 && empty($_SESSION['vida_extra_usada'])) {
            $puedeContinuar = true;
            $_SESSION['perdida_pendiente'] = time();
        } else {
            $this->finalizarPartida();
        }

        echo json_encode([
            "acerto" => $acerto,
            "correcta" => $pregunta["correcta"],
            "puntos" => $_SESSION['puntos'],
            "final" => $final,
            "puedeContinuar" => $puedeContinuar,
        ]);
        exit;
    }

    public function continuar()
    {
        header("Content-Type: application/json");

        $perdioEn = $_SESSION['perdida_pendiente'] ?? null;

        if (!isset($_SESSION['usuario']) || $perdioEn === null || !empty($_SESSION['vida_extra_usada'])) {
            echo json_encode(["ok" => false]);
            exit;
        }

        if (time() - $perdioEn > self::SEGUNDOS_CONTINUAR + self::MARGEN_CONTINUAR) {
            $this->finalizarPartida();
            echo json_encode(["ok" => false]);
            exit;
        }

        $_SESSION['vida_extra_pagada'] = self::PRECIO_VIDA_EXTRA;
        $_SESSION['vida_extra_usada'] = true;
        unset($_SESSION['perdida_pendiente']);

        echo json_encode(["ok" => true]);
        exit;
    }

    public function terminar()
    {
        header("Content-Type: application/json");
        if (isset($_SESSION['usuario'])) {
            $this->resolverPerdidaPendiente();
        }
        echo json_encode(["ok" => true]);
        exit;
    }

    private function resolverPerdidaPendiente()
    {
        if (isset($_SESSION['perdida_pendiente'])) {
            $this->finalizarPartida();
        }
    }

    private function finalizarPartida()
    {
        if (!empty($_SESSION['partida_id'])) {
            $this->model->finalizarPartida(
                (int) $_SESSION['partida_id'],
                (int) ($_SESSION['puntos'] ?? 0)
            );
        }
        $_SESSION['puntos'] = 0;
        unset(
            $_SESSION['partida_id'],
            $_SESSION['perdida_pendiente'],
            $_SESSION['vida_extra_usada'],
            $_SESSION['vida_extra_pagada'],
            $_SESSION['pregunta_actual']
        );
    }

    public function reportar()
    {
        header("Content-Type: application/json");
        $pregunta = $_SESSION['pregunta_actual'] ?? null;
        $usuarioId = $_SESSION['usuario_id'] ?? null;

        if ($usuarioId === null || $pregunta === null || !isset($pregunta["id"])) {
            http_response_code(400);
            echo json_encode(["ok" => false, "mensaje" => "No hay pregunta para reportar"]);
            exit;
        }

        if ($this->model->reportesRecientes((int) $usuarioId) >= 5) {
            echo json_encode(["ok" => false, "mensaje" => "Llegaste al límite de reportes por hoy"]);
            exit;
        }

        $nuevo = $this->model->reportarPregunta(
            (int) $pregunta["id"],
            (int) $usuarioId,
            Request::post('motivo', 'Sin motivo')
        );
        echo json_encode([
            "ok" => true,
            "mensaje" => $nuevo ? "Gracias, recibimos tu reporte" : "Ya reportaste esta pregunta",
        ]);
        exit;
    }
}