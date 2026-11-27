<?php
namespace app\model;

class EditorModel {
    private $database;

    function __construct($database) {
        $this->database = $database;
    }
    public function getCategorias() {
        return $this->database->queryAll("SELECT id, nombre FROM categoria ORDER BY nombre");
    }

    public function guardarPregunta($texto, $opcion_1, $opcion_2, $opcion_3, $opcion_correcta, $creador_id, $categoria_id) {
        $sql = "INSERT INTO pregunta (texto, opcion_1, opcion_2, opcion_3, opcion_correcta, estado, creador_id, categoria_id, fecha_creacion)
            VALUES (?, ?, ?, ?, ?, 'pendiente', ?, ?, NOW())";
        return $this->database->executePrepared($sql, [$texto, $opcion_1, $opcion_2, $opcion_3, $opcion_correcta, $creador_id, $categoria_id]);
    }

    public function cambiarEstadoPregunta($pregunta_id,$nuevoEstado) {
        // Recibe ID y nuevo estado ('activa', 'dada_de_baja') para dar de alta/baja
        $sql = "UPDATE pregunta SET estado = ? WHERE id = ?";
        return $this->database->executePrepared($sql, [$nuevoEstado, $pregunta_id]);

    }
    public function getTodasLasPreguntas() {
        return $this->database->queryAll("SELECT id, texto, estado FROM pregunta ORDER BY id DESC");
    }
    public function getPregunta($id) {
        $id = (int) $id;
        return $this->database->queryOne(
            "SELECT p.id, p.texto, p.opcion_1, p.opcion_2, p.opcion_3, p.opcion_correcta, p.estado,
        p.fecha_creacion, p.categoria_id, c.nombre AS categoria, u.usuario AS creador
         FROM pregunta p
         LEFT JOIN categoria c ON c.id = p.categoria_id
         LEFT JOIN usuario u ON u.id = p.creador_id
         WHERE p.id = $id"
        );
    }
    public function getPreguntasReportadas(int $minimo)
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

    public function resolverReportes(int $preguntaId, bool $bloquear): bool
    {
        if ($bloquear) {
            $this->database->executePrepared(
                "UPDATE pregunta SET estado = 'dada_de_baja' WHERE id = ?", [$preguntaId]);
        }
        $filas = $this->database->executePrepared(
            "UPDATE reporte_pregunta SET estado = 'resuelta'
         WHERE pregunta_id = ? AND estado = 'pendiente'", [$preguntaId]);
        return $filas > 0;
    }
    public function actualizarPregunta(int $id, string $texto, string $op1, string $op2, string $op3, string $correcta, ?int $categoriaId): void
    {
        $this->database->executePrepared(
            "UPDATE pregunta
         SET texto = ?, opcion_1 = ?, opcion_2 = ?, opcion_3 = ?, opcion_correcta = ?, categoria_id = ?
         WHERE id = ?",
            [$texto, $op1, $op2, $op3, $correcta, $categoriaId, $id]
        );
    }

    public function getPreguntasPaginadas(int $limite, int $offset) {
        return $this->database->queryAll(
            "SELECT id, texto, estado FROM pregunta ORDER BY id DESC LIMIT $limite OFFSET $offset"
        );
    }

    public function contarPreguntas(): int {
        $fila = $this->database->queryOne("SELECT COUNT(*) AS total FROM pregunta");
        return (int) $fila['total'];
    }
}