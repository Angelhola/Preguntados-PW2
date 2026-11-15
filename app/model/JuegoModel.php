<?php

namespace app\model;

class JuegoModel
{

    private $database;

    function __construct($database) {
        $this->database = $database;
    }

    public function sumarPuntaje(string $usuario, int $puntos)
    {
        $sql = "UPDATE usuario
            SET puntaje = COALESCE(puntaje, 0) + ?
            WHERE usuario = ?";

        return $this->database->executePrepared($sql, [$puntos, $usuario]);
    }

    public function registrarPartida(int $idUsuario)
    {
        $sql = "INSERT INTO partida (usuario_id) VALUES (?)";
        return $this->database->executePrepared($sql, [$idUsuario]);
    }

    public function getCategorias(){
        $sql="SELECT id,nombre,color FROM categoria";
        return $this->database->queryAll("SELECT * FROM categoria");
    }


    public function sortearCategoria()
    {
        $categorias = $this->getCategorias();
        return $categorias[array_rand($categorias)];
    }
//    private function getPreguntas()
//    {
//        return [
//            "Historia" => [
//                ["texto" => "¿En qué año llegó Colón a América?", "opciones" => ["1492", "1500", "1453", "1810"], "correcta" => 0],
//                ["texto" => "¿En qué año se declaró la independencia argentina?", "opciones" => ["1810", "1816", "1853", "1776"], "correcta" => 1],
//            ],
//            "Ciencia" => [
//                ["texto" => "¿Cuál es el planeta más grande del sistema solar?", "opciones" => ["Saturno", "Marte", "Júpiter", "Neptuno"], "correcta" => 2],
//                ["texto" => "¿Qué gas necesitamos para respirar?", "opciones" => ["Nitrógeno", "Oxígeno", "Helio", "Hidrógeno"], "correcta" => 1],
//            ],
//            "Deportes" => [
//                ["texto" => "¿Cuántos jugadores de un equipo hay en la cancha en fútbol?", "opciones" => ["9", "10", "11", "12"], "correcta" => 2],
//                ["texto" => "¿Qué deporte se juega en Wimbledon?", "opciones" => ["Golf", "Tenis", "Rugby", "Cricket"], "correcta" => 1],
//            ],
//            "Geografía" => [
//                ["texto" => "¿Cuál es la capital de Argentina?", "opciones" => ["Córdoba", "Rosario", "Buenos Aires", "Mendoza"], "correcta" => 2],
//                ["texto" => "¿Cuál es el río más largo de Sudamérica?", "opciones" => ["Amazonas", "Paraná", "Orinoco", "Uruguay"], "correcta" => 0],
//            ],
//            "Arte" => [
//                ["texto" => "¿Quién pintó la Mona Lisa?", "opciones" => ["Picasso", "Van Gogh", "Leonardo da Vinci", "Dalí"], "correcta" => 2],
//                ["texto" => "¿Quién pintó La noche estrellada?", "opciones" => ["Van Gogh", "Monet", "Goya", "Rembrandt"], "correcta" => 0],
//            ],
//            "Cine y TV" => [
//                ["texto" => "¿Quién lleva el Anillo Único hasta Mordor?", "opciones" => ["Frodo", "Bilbo", "Gandalf", "Aragorn"], "correcta" => 0],
//                ["texto" => "¿De qué color es Pac-Man?", "opciones" => ["Rojo", "Azul", "Amarillo", "Verde"], "correcta" => 2],
//            ],
//        ];
//    }

    public function getPreguntaAleatoria($categoria, $excluirTexto = null)
    {
//        $preguntas = $this->getPreguntas();
//        $lista = $preguntas[$categoria] ?? [];
//
//        $disponibles = [];
//        foreach ($lista as $pregunta) {
//            if ($pregunta["texto"] !== $excluirTexto) {
//                $disponibles[] = $pregunta;
//            }
//        }
//        if (empty($disponibles)) {
//            $disponibles = $lista;
//        }
//        if (empty($disponibles)) {
//            return null;
//        }
//
//        return $this->mezclarOpciones($disponibles[array_rand($disponibles)]);
//    }
        $cat = addslashes($categoria);
        $filas = $this->database->queryAll(
            "SELECT p.id ,p.texto, p.opcion_1, p.opcion_2, p.opcion_3, p.opcion_correcta
         FROM pregunta p
         JOIN categoria c ON c.id = p.categoria_id
         WHERE p.estado = 'activa' AND c.nombre = '$cat'"
        );

        $lista = [];
        foreach ($filas as $f){
            $opciones_temporales = [
                $f["opcion_1"],
                $f["opcion_2"],
                $f["opcion_3"],
                $f["opcion_correcta"]
            ];

            $lista[] = [
                "id" => $f["id"],
                "texto"    => $f["texto"],
                "opciones" => $opciones_temporales,
                "correcta" => 3, // Como la pusimos última en el array temporal, su índice inicial es 3
            ];
        }
        $disponibles = [];
        foreach ($lista as $pregunta) {
            if ($pregunta["texto"] !== $excluirTexto) {
                $disponibles[] = $pregunta;
            }
        }
        if (empty($disponibles)) {
            $disponibles = $lista;
        }
        if (empty($disponibles)) {
            return null;
        }

        return $this->mezclarOpciones($disponibles[array_rand($disponibles)]);
    }

    private function mezclarOpciones($pregunta)
    {
        $opciones = [];
        foreach ($pregunta["opciones"] as $indice => $texto) {
            $opciones[] = ["texto" => $texto, "correcta" => $indice === $pregunta["correcta"]];
        }
        shuffle($opciones);

        $pregunta["opciones"] = [];
        foreach ($opciones as $nuevoIndice => $opcion) {
            $pregunta["opciones"][] = $opcion["texto"];
            if ($opcion["correcta"]) {
                $pregunta["correcta"] = $nuevoIndice;
            }
        }
        return $pregunta;
    }

    public function reportarPregunta(int $preguntaId, int $usuarioId, string $motivo): bool
    {
        $existe = $this->database->queryOne(
            "SELECT id FROM reporte_pregunta
         WHERE pregunta_id = $preguntaId AND usuario_id = $usuarioId AND estado = 'pendiente'"
        );
        if ($existe !== null) {
            return false;
        }
        $this->database->executePrepared(
            "INSERT INTO reporte_pregunta (pregunta_id, usuario_id, motivo) VALUES (?, ?, ?)",
            [$preguntaId, $usuarioId, mb_substr($motivo, 0, 255)]
        );
        return true;
    }

    public function getPreguntasReportadas(int $minimo = 1)
    {
        return $this->database->queryAll(
            "SELECT p.id, p.texto, p.opcion_correcta, c.nombre AS categoria,
                COUNT(r.id) AS reportes,
                GROUP_CONCAT(r.motivo SEPARATOR ' | ') AS motivos
         FROM pregunta p
         JOIN reporte_pregunta r ON r.pregunta_id = p.id AND r.estado = 'pendiente'
         LEFT JOIN categoria c ON c.id = p.categoria_id
         WHERE p.estado = 'activa'
         GROUP BY p.id, p.texto, p.opcion_correcta, c.nombre
         HAVING COUNT(r.id) >= $minimo
         ORDER BY reportes DESC"
        );
    }

    public function darDeBajaPregunta(int $preguntaId): bool
    {
        $filas = $this->database->executePrepared(
            "UPDATE pregunta SET estado = 'dada_de_baja' WHERE id = ?",
            [$preguntaId]
        );
        $this->database->executePrepared(
            "UPDATE reporte_pregunta SET estado = 'resuelta' WHERE pregunta_id = ?",
            [$preguntaId]
        );
        return $filas > 0;
    }

    public function reportesRecientes(int $usuarioId): int
    {
        $f = $this->database->queryOne(
            "SELECT COUNT(*) AS total FROM reporte_pregunta
         WHERE usuario_id = $usuarioId AND fecha > NOW() - INTERVAL 1 DAY"
        );
        return (int) $f['total'];
    }
}