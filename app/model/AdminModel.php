<?php

namespace app\model;

class AdminModel
{
    private $database;

    function __construct($database) {
        $this->database = $database;
    }
    private function getDesde($periodo) {
        switch ($periodo) {
            case 'dia':    return date('Y-m-d 00:00:00');
            case 'semana': return date('Y-m-d 00:00:00', strtotime('monday this week'));
            case 'mes':    return date('Y-m-01 00:00:00');
            case 'anio':   return date('Y-01-01 00:00:00');
            default:       return null; // histórico
        }
    }

    // $periodo ya viene validado con lista blanca en el controlador
    private function where($campo, $periodo) {
        $desde = $this->getDesde($periodo);
        return $desde ? " AND $campo >= '$desde'" : "";
    }

    private function total($sql) {
        return $this->database->queryAll($sql)[0]['total'] ?? 0;
    }

    public function getMetricasGenerales($periodo = null) {
        return [
            "jugadores" => $this->total(
                "SELECT COUNT(*) AS total FROM usuario WHERE rol = 'jugador'"),

            "partidas" => $this->total(
                "SELECT COUNT(*) AS total FROM partida WHERE 1=1"
                . $this->where('fecha_inicio', $periodo)),

            // preguntas disponibles en el juego
            "preguntas" => $this->total(
                "SELECT COUNT(*) AS total FROM pregunta WHERE estado = 'activa'"),

            // preguntas creadas por usuarios (las del seed tienen creador_id NULL)
            "preguntas_creadas" => $this->total(
                "SELECT COUNT(*) AS total FROM pregunta WHERE creador_id IS NOT NULL"
                . $this->where('fecha_creacion', $periodo)),

            "usuarios_nuevos" => $this->total(
                "SELECT COUNT(*) AS total FROM usuario WHERE rol = 'jugador'"
                . $this->where('fecha_registro', $periodo)),
        ];
    }

    public function getPorcentajeCorrectasPorUsuario($periodo = null) {
        $sql = "SELECT u.usuario AS usuario,
                       COUNT(*) AS respondidas,
                       ROUND(SUM(rp.es_correcta) * 100 / COUNT(*), 1) AS porcentaje
                FROM respuesta_partida rp
                JOIN partida p ON p.id = rp.partida_id
                JOIN usuario u ON u.id = p.usuario_id
                WHERE 1=1 " . $this->where('p.fecha_inicio', $periodo) . "
                GROUP BY u.id, u.usuario
                ORDER BY porcentaje DESC";
        return $this->database->queryAll($sql);
    }


    public function getUsuariosPorPais($periodo = null) {
        $sql = "SELECT COALESCE(NULLIF(TRIM(pais), ''), 'Sin especificar') AS pais,
                   COUNT(*) AS cantidad
            FROM usuario
            WHERE rol = 'jugador' " . $this->where('fecha_registro', $periodo) . "
            GROUP BY 1
            ORDER BY cantidad DESC";
        return $this->database->queryAll($sql);
    }

    public function getUsuariosPorSexo($periodo = null) {
        $sql = "SELECT COALESCE(NULLIF(TRIM(genero), ''), 'Sin especificar') AS sexo,
                   COUNT(*) AS cantidad
            FROM usuario
            WHERE rol = 'jugador' " . $this->where('fecha_registro', $periodo) . "
            GROUP BY 1";
        return $this->database->queryAll($sql);
    }
    public function getUsuariosPorEdad($periodo = null) {
        $sql = "SELECT CASE
                       WHEN (YEAR(CURDATE()) - anio_nacimiento) < 18 THEN 'Menores'
                       WHEN (YEAR(CURDATE()) - anio_nacimiento) BETWEEN 18 AND 64 THEN 'Medio'
                       ELSE 'Jubilados'
                   END AS grupo,
                   COUNT(*) AS cantidad
            FROM usuario
            WHERE rol = 'jugador' AND anio_nacimiento IS NOT NULL "
            . $this->where('fecha_registro', $periodo) . "
            GROUP BY 1";
        return $this->database->queryAll($sql);
    }
}