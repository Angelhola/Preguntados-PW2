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