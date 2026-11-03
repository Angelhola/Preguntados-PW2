CREATE DATABASE IF NOT EXISTS preguntados CHARACTER SET utf8mb4;
use preguntados;
DROP TABLE IF EXISTS usuario;

CREATE TABLE usuario (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         usuario VARCHAR(50) NOT NULL UNIQUE,
                         password VARCHAR(32) NOT NULL,
                         email VARCHAR(100),
                         nombre_completo VARCHAR(100),
                         anio_nacimiento INT,
                         genero VARCHAR(20),
                         foto_perfil VARCHAR(255),
                         ciudad VARCHAR(100),
                         pais VARCHAR(100),
                         region VARCHAR(100),
                         verificado TINYINT NOT NULL DEFAULT 0,
                         token_verificacion VARCHAR(64),
                         puntaje INT
);

INSERT INTO usuario (usuario, password) VALUES
    ('test', MD5('1234'));

DESCRIBE usuario;
ALTER TABLE usuario
ADD COLUMN rol ENUM('jugador','editor','administrador') NOT NULL DEFAULT 'jugador';
ALTER TABLE usuario
    ADD COLUMN fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
UPDATE usuario
SET rol = 'administrador'
WHERE usuario = 'test';
CREATE TABLE IF NOT EXISTS pregunta(
id INT AUTO_INCREMENT PRIMARY KEY, texto TEXT NOT NULL,
opcion_1 VARCHAR(255) NOT NULL,
opcion_2 VARCHAR(255) NOT NULL,
opcion_3 VARCHAR(255) NOT NULL,
opcion_correcta VARCHAR(255) NOT NULL,
estado ENUM('pendiente', 'activa', 'dada_de_baja') DEFAULT 'pendiente',
creador_id INT,
fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (creador_id) REFERENCES usuario(id));
CREATE TABLE IF NOT EXISTS reporte_pregunta (
                                                id INT AUTO_INCREMENT PRIMARY KEY,
                                                pregunta_id INT,
                                                usuario_id INT,
                                                motivo VARCHAR(255),
                                                estado ENUM('pendiente', 'resuelta') DEFAULT 'pendiente',
                                                fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                                FOREIGN KEY (pregunta_id) REFERENCES pregunta(id),
                                                FOREIGN KEY (usuario_id) REFERENCES usuario(id)
);
CREATE TABLE IF NOT EXISTS partida (
                                       id INT AUTO_INCREMENT PRIMARY KEY,
                                       usuario_id INT,
                                       fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                       puntaje_obtenido INT DEFAULT 0,
                                       FOREIGN KEY (usuario_id) REFERENCES usuario(id)
);

CREATE TABLE IF NOT EXISTS respuesta_partida (
                                                 id INT AUTO_INCREMENT PRIMARY KEY,
                                                 partida_id INT,
                                                 pregunta_id INT,
                                                 es_correcta TINYINT(1) NOT NULL,
                                                 FOREIGN KEY (partida_id) REFERENCES partida(id),
                                                 FOREIGN KEY (pregunta_id) REFERENCES pregunta(id)
);
CREATE TABLE IF NOT EXISTS categoria (
                                         id INT AUTO_INCREMENT PRIMARY KEY,
                                         nombre VARCHAR(50) NOT NULL UNIQUE,
                                         color VARCHAR(20) DEFAULT '#ffffff'
);
ALTER TABLE pregunta
    ADD COLUMN categoria_id INT,
    ADD FOREIGN KEY (categoria_id) REFERENCES categoria(id);
INSERT INTO categoria (nombre, color) VALUES
                                          ('Historia', '#ffe600'),
                                          ('Ciencia', '#00e5ff'),
                                          ('Deportes', '#ff0055'),
                                          ('Geografía', '#ffe600'),
                                          ('Arte', '#00e5ff'),
                                          ('Cine y TV', '#ff0055');
UPDATE usuario
SET verificado = 1, rol = 'administrador'
WHERE usuario = 'test';
INSERT INTO usuario (usuario, password, verificado, rol)
VALUES ('editor', MD5('1234'), 1, 'editor');
UPDATE categoria SET nombre = 'Geografia' WHERE color = '#ffe600' AND nombre <> 'Historia';
SELECT id, nombre FROM categoria;
-- Ejemplo para insertar categorías si no las tenés
INSERT INTO categoria (id, nombre) VALUES
                                       (1, 'Historia'),
                                       (2, 'Ciencia'),
                                       (3, 'Deportes'),
                                       (4, 'Geografía'),
                                       (5, 'Arte'),
                                       (6, 'Cine y TV');

-- Ejemplo para insertar preguntas asociadas
INSERT INTO pregunta (texto, opcion_1, opcion_2, opcion_3, opcion_correcta, categoria_id, estado) VALUES
                                                                                                      ('¿En qué año llegó Colón a América?', '1500', '1453', '1810', '1492', 1, 'activa'),
                                                                                                      ('¿Cuál es el planeta más grande del sistema solar?', 'Saturno', 'Marte', 'Neptuno', 'Júpiter', 2, 'activa'),
                                                                                                      ('¿Cuántos jugadores de un equipo hay en la cancha en fútbol?', '9', '10', '12', '11', 3, 'activa');
-- 1. Asegurar que existan las categorías (por si acaso)
INSERT INTO categoria (id, nombre) VALUES
                                       (4, 'Geografía'),
                                       (5, 'Arte'),
                                       (6, 'Cine y TV')
ON DUPLICATE KEY UPDATE nombre = nombre;

-- 2. Insertar las preguntas mapeando correctamente las opciones incorrectas y la correcta
INSERT INTO pregunta (texto, opcion_1, opcion_2, opcion_3, opcion_correcta, categoria_id, estado) VALUES
-- Geografía
('¿Cuál es la capital de Argentina?', 'Córdoba', 'Rosario', 'Mendoza', 'Buenos Aires', 4, 'activa'),
('¿Cuál es el río más largo de Sudamérica?', 'Paraná', 'Orinoco', 'Uruguay', 'Amazonas', 4, 'activa'),

-- Arte
('¿Quién pintó la Mona Lisa?', 'Picasso', 'Van Gogh', 'Dalí', 'Leonardo da Vinci', 5, 'activa'),
('¿Quién pintó La noche estrellada?', 'Monet', 'Goya', 'Rembrandt', 'Van Gogh', 5, 'activa'),

-- Cine y TV
('¿Quién lleva el Anillo Único hasta Mordor?', 'Bilbo', 'Gandalf', 'Aragorn', 'Frodo', 6, 'activa'),
('¿De qué color es Pac-Man?', 'Rojo', 'Azul', 'Verde', 'Amarillo', 6, 'activa');