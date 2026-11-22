<?php
namespace app\model;
class RankingModel {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function obtenerRanking() {
        $sql = "SELECT u.id,
                   u.usuario AS nombre,
                   u.foto_perfil AS avatar,
                   SUM(p.puntaje_obtenido) AS puntos
            FROM partida p
            JOIN usuario u ON u.id = p.usuario_id
            WHERE p.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
            GROUP BY u.id, u.usuario, u.foto_perfil
            ORDER BY puntos DESC, u.usuario ASC
            LIMIT 10";
        return $this->database->queryAll($sql);
    }

}
