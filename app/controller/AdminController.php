<?php

namespace app\controller;

class AdminController
{
    private $model;
    private $render;
    public function __construct($model, $render){
        $this->model = $model;
        $this->render = $render;
    }
    public function panel() {
        // Validar que sea admin
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            Redirect::to('?controller=login&method=show');
            return;
        }

        $data["metricas"] = $this->model->getMetricasGenerales();
        $data["por_edad"] = $this->model->getUsuariosPorEdad();

        $this->render->renderizaArcade("panelAdmin", $data);
    }
}