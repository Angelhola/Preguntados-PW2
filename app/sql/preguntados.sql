-- Base de datos del ejemplo "Partido Despedida de Messi"
-- Importar completa antes de correr los PHP

CREATE DATABASE IF NOT EXISTS preguntados CHARACTER SET utf8mb4;
USE preguntados;

CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(32) NOT NULL
);

INSERT INTO usuario (usuario, password) VALUES
('test', MD5('1234'));
