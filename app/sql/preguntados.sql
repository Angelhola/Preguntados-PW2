-- Base de datos del ejemplo "Partido Despedida de Messi"

-- Importar completa antes de correr los PHP

CREATE DATABASE IF NOT EXISTS preguntados CHARACTER SET utf8mb4;

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
                         puntaje INT
);

INSERT INTO usuario (usuario, password) VALUES
    ('test', MD5('1234'));