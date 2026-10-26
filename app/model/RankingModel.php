<?php
class RankingModel {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function obtenerRanking() {
        $sql = "SELECT usuario AS nombre, puntaje AS puntos FROM usuario ORDER BY usuario DESC";

        return $this->database->query($sql);
    }

}
