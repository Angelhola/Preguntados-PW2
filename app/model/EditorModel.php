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
                p.fecha_creacion, c.nombre AS categoria, u.usuario AS creador
         FROM pregunta p
         LEFT JOIN categoria c ON c.id = p.categoria_id
         LEFT JOIN usuario u ON u.id = p.creador_id
         WHERE p.id = $id"
        );
    }
}