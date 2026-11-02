<?php
namespace app\model;
class RankingModel {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function obtenerRanking() {
        $sql = "SELECT id, usuario AS nombre, COALESCE(puntaje, 0) AS puntos, foto_perfil AS avatar
        FROM usuario
        ORDER BY puntos DESC
        LIMIT 10";
        return $this->database->queryAll($sql);
    }

}
