<?php

namespace app\model;

class AdminModel
{
    private $database;

    function __construct($database) {
        $this->database = $database;
    }
    public function getMetricasGenerales() {
    // Cantidad de jugadores
    $jugadores = $this->database->queryAll("SELECT COUNT(*) as total FROM usuario WHERE rol = 'jugador'")[0]['total'] ?? 0;

    // Partidas jugadas
    $partidas = $this->database->queryAll("SELECT COUNT(*) as total FROM partida")[0]['total'] ?? 0;

    // Preguntas totales en el juego
    $preguntas = $this->database->queryAll("SELECT COUNT(*) as total FROM pregunta")[0]['total'] ?? 0;

    return [
        "jugadores" => $jugadores,
        "partidas" => $partidas,
        "preguntas" => $preguntas
    ];
}

    public function getUsuariosPorEdad() {
        // Agrupación por grupos de edad usando anio_nacimiento (calculado al 2026)
        $sql = "SELECT 
                    CASE 
                        WHEN (2026 - anio_nacimiento) < 18 THEN 'Menores'
                        WHEN (2026 - anio_nacimiento) BETWEEN 18 AND 64 THEN 'Medio'
                        ELSE 'Jubilados'
                    END AS grupo,
                    COUNT(*) AS cantidad
                FROM usuario 
                WHERE anio_nacimiento IS NOT NULL 
                GROUP BY grupo";
        return $this->database->queryAll($sql);
    }
}