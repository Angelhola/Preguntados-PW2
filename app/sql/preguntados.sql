-- Base de datos del ejemplo "Partido Despedida de Messi"
-- Importar completa antes de correr los PHP

CREATE DATABASE IF NOT EXISTS preguntados CHARACTER SET utf8mb4;
USE preguntados;

DROP TABLE IF EXISTS usuario;

CREATE TABLE usuario (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         usuario VARCHAR(50) NOT NULL UNIQUE,
                         password VARCHAR(32) NOT NULL,
                         email VARCHAR(100),
                         nombre_completo VARCHAR(100),
                         genero VARCHAR(20),
                         foto_perfil VARCHAR(255), ciudadVARCHAR(100),
                     ,paisVARCHAR(100),regionVARCHAR(100));

INSERT INTO usuario (usuario, password) VALUES
    ('test', MD5('1234'));