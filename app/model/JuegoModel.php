<?php

namespace app\model;

class JuegoModel
{
    private $database;

    public function __construct($database)
    {
        $this->database =$database;
    }

    public function sumarPuntaje(string $usuario, int$puntos)
    {
        $sql = "UPDATE usuario
                SET puntaje = COALESCE(puntaje, 0) + ?
                WHERE usuario = ?";

        return $this->database->executePrepared($sql, [$puntos,$usuario]);
    }

    public function getCategorias()
    {
        return $this->database->queryAll("SELECT id, nombre, color FROM categoria");
    }

    public function sortearCategoria()
    {
        $categorias =$this->getCategorias();
        return $categorias[array_rand($categorias)];
    }

    public function getPreguntaAleatoria($categoria,$excluirTexto = null)
    {
        $cat = addslashes($categoria);
        $filas =$this->database->queryAll(
            "SELECT p.id, p.texto, p.opcion_1, p.opcion_2, p.opcion_3, p.opcion_correcta
             FROM pregunta p
             JOIN categoria c ON c.id = p.categoria_id
             WHERE p.estado = 'activa' AND c.nombre = '$cat'"
        );

        $lista = [];
        foreach ($filas as$f) {
            $opciones_temporales = [$f["opcion_1"],
                $f["opcion_2"],
                $f["opcion_3"],
                $f["opcion_correcta"]
            ];

            $lista[] = [
                "id"       => $f["id"],
                "texto"    => $f["texto"],
                "opciones" => $opciones_temporales,
                "correcta" => 3,
            ];
        }

        $disponibles = [];
        foreach ($lista as$pregunta) {
            if ($pregunta["texto"] !== $excluirTexto) {
                $disponibles[] =$pregunta;
            }
        }
        if (empty($disponibles)) {
            $disponibles =$lista;
        }
        if (empty($disponibles)) {
            return null;
        }

        return $this->mezclarOpciones($disponibles[array_rand($disponibles)]);
    }

    private function mezclarOpciones($pregunta)
    {
        $opciones = [];
        foreach ($pregunta["opciones"] as $indice =>$texto) {
            $opciones[] = ["texto" => $texto, "correcta" => $indice ===$pregunta["correcta"]];
        }
        shuffle($opciones);

        $pregunta["opciones"] = [];
        foreach ($opciones as $nuevoIndice =>$opcion) {
            $pregunta["opciones"][] = $opcion["texto"];
            if ($opcion["correcta"]) {
                $pregunta["correcta"] = $nuevoIndice;
            }
        }
        return $pregunta;
    }

    public function reportarPregunta(int $preguntaId, int $usuarioId, string$motivo): bool
    {
        $existe =$this->database->queryOne(
            "SELECT id FROM reporte_pregunta
             WHERE pregunta_id = $preguntaId AND usuario_id =$usuarioId AND estado = 'pendiente'"
        );
        if ($existe !== null) { return false; }$this->database->executePrepared(
        "INSERT INTO reporte_pregunta (pregunta_id, usuario_id, motivo) VALUES (?, ?, ?)",
        [$preguntaId, $usuarioId, mb_substr($motivo, 0, 255)]
    );
        return true;
    }

    public function reportesRecientes(int $usuarioId): int
    {
        $f =$this->database->queryOne(
            "SELECT COUNT(*) AS total FROM reporte_pregunta
             WHERE usuario_id = $usuarioId AND fecha > NOW() - INTERVAL 1 DAY"
        );
        return (int) $f['total'];
    }

    public function crearPartida(int $usuarioId): int
    {
        $this->database->executePrepared(
            "INSERT INTO partida (usuario_id, puntaje_obtenido) VALUES (?, 0)",
            [$usuarioId]
        );
        $res =$this->database->queryOne("SELECT LAST_INSERT_ID() AS id");
        return (int) ($res['id'] ?? 0);
    }

    public function finalizarPartida(int $partidaId, int$puntaje): void
    {
        $this->database->executePrepared(
            "UPDATE partida SET puntaje_obtenido = ? WHERE id = ?",
            [$puntaje,$partidaId]
        );
    }

    public function registrarRespuestaPartida(int $partidaId, int $preguntaId, bool$esCorrecta): void
    {
        $correctaInt =$esCorrecta ? 1 : 0;
        $this->database->executePrepared(
            "INSERT INTO respuesta_partida (partida_id, pregunta_id, es_correcta) VALUES (?, ?, ?)",
            [$partidaId, $preguntaId,$correctaInt]
        );
    }
}
