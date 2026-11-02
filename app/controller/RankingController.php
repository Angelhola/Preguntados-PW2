<?php
namespace app\controller;
class RankingController
{
    private $model;
    private $renderer;

    public function __construct($model, $renderer)
    {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function show() {
        $jugadores = $this->model->obtenerRanking();
        $idActual = $_SESSION['id'] ?? null;

        foreach ($jugadores as $i => &$j) {
            $j['posicion'] = $i + 1;
            $j['clase'] = $i < 3 ? 'top-' . ($i + 1) : '';
            if ($j['id'] == $idActual) {
                $j['clase'] .= ' ranking-yo';
            }
        }
        unset($j);

        $this->renderer->renderizaArcade('ranking', [
            'jugadores'    => $jugadores,
            'hayJugadores' => !empty($jugadores),
        ]);
    }
}