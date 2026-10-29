<?php

namespace app\model;

class JuegoModel
{
    public function getCategorias()
    {
        return [
            ["nombre" => "Historia", "color" => "#ffe600"],
            ["nombre" => "Ciencia", "color" => "#00e5ff"],
            ["nombre" => "Deportes", "color" => "#ff0055"],
            ["nombre" => "Geografía", "color" => "#39ff14"],
            ["nombre" => "Arte", "color" => "#b026ff"],
            ["nombre" => "Entretenimiento", "color" => "#ff8800"],
        ];
    }

    public function sortearCategoria()
    {
        $categorias = $this->getCategorias();
        return $categorias[array_rand($categorias)];
    }
    private function getPreguntas()
    {
        return [
            "Historia" => [
                ["texto" => "¿En qué año llegó Colón a América?", "opciones" => ["1492", "1500", "1453", "1810"], "correcta" => 0],
                ["texto" => "¿En qué año se declaró la independencia argentina?", "opciones" => ["1810", "1816", "1853", "1776"], "correcta" => 1],
            ],
            "Ciencia" => [
                ["texto" => "¿Cuál es el planeta más grande del sistema solar?", "opciones" => ["Saturno", "Marte", "Júpiter", "Neptuno"], "correcta" => 2],
                ["texto" => "¿Qué gas necesitamos para respirar?", "opciones" => ["Nitrógeno", "Oxígeno", "Helio", "Hidrógeno"], "correcta" => 1],
            ],
            "Deportes" => [
                ["texto" => "¿Cuántos jugadores de un equipo hay en la cancha en fútbol?", "opciones" => ["9", "10", "11", "12"], "correcta" => 2],
                ["texto" => "¿Qué deporte se juega en Wimbledon?", "opciones" => ["Golf", "Tenis", "Rugby", "Cricket"], "correcta" => 1],
            ],
            "Geografía" => [
                ["texto" => "¿Cuál es la capital de Argentina?", "opciones" => ["Córdoba", "Rosario", "Buenos Aires", "Mendoza"], "correcta" => 2],
                ["texto" => "¿Cuál es el río más largo de Sudamérica?", "opciones" => ["Amazonas", "Paraná", "Orinoco", "Uruguay"], "correcta" => 0],
            ],
            "Arte" => [
                ["texto" => "¿Quién pintó la Mona Lisa?", "opciones" => ["Picasso", "Van Gogh", "Leonardo da Vinci", "Dalí"], "correcta" => 2],
                ["texto" => "¿Quién pintó La noche estrellada?", "opciones" => ["Van Gogh", "Monet", "Goya", "Rembrandt"], "correcta" => 0],
            ],
            "Entretenimiento" => [
                ["texto" => "¿Quién lleva el Anillo Único hasta Mordor?", "opciones" => ["Frodo", "Bilbo", "Gandalf", "Aragorn"], "correcta" => 0],
                ["texto" => "¿De qué color es Pac-Man?", "opciones" => ["Rojo", "Azul", "Amarillo", "Verde"], "correcta" => 2],
            ],
        ];
    }

    public function getPreguntaAleatoria($categoria)
    {
        $preguntas = $this->getPreguntas();
        $lista = $preguntas[$categoria] ?? [];
        if (empty($lista)) {
            return null;
        }
        return $lista[array_rand($lista)];
    }
}