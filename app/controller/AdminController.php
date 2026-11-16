<?php
namespace app\controller;
use Redirect;
use Request;
class AdminController
{
    private $model;
    private $render;

    public function __construct($model, $render)
    {
        $this->model = $model;
        $this->render = $render;
    }

    public function panel()
    {
        if (($_SESSION['rol'] ?? '') !== 'administrador') {
            header('Location: /');
            exit;
        }

        $textos = [
            'dia' => 'Hoy', 'semana' => 'Esta semana',
            'mes' => 'Este mes', 'anio' => 'Este año'
        ];
        $periodo = array_key_exists($_GET['periodo'] ?? '', $textos) ? $_GET['periodo'] : '';

        $paises = $this->model->getUsuariosPorPais($periodo);
        $sexos = $this->model->getUsuariosPorSexo($periodo);
        $edades = $this->model->getUsuariosPorEdad($periodo);
        $correctas = $this->model->getPorcentajeCorrectasPorUsuario($periodo);

        $data = [
            'periodo_texto' => $textos[$periodo] ?? 'Histórico',
            'fecha_impresion' => date('d/m/Y H:i'),
            'metricas' => $this->model->getMetricasGenerales($periodo),
            'correctas' => $correctas,
            'por_pais' => $paises,
            'por_sexo' => $sexos,
            'por_edad' => $edades,
            'sel_dia' => $periodo === 'dia',
            'sel_semana' => $periodo === 'semana',
            'sel_mes' => $periodo === 'mes',
            'sel_anio' => $periodo === 'anio',
            'json_paises' => json_encode($paises),
            'json_sexos' => json_encode($sexos),
            'json_edades' => json_encode($edades),
            'json_correctas' => json_encode(array_slice($correctas, 0, 10)),
        ];

        $this->render->renderizaArcade("panelAdmin", $data);
    }

    public function reportes()
    {
        $this->panel();
    }
}