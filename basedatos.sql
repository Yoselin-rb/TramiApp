-- =========================================================
--  Base de datos para el sistema de LOGIN / REGISTRO de TramiApp
--  Copiar y pegar en phpMyAdmin -> pestaña SQL -> Continuar
-- =========================================================

CREATE DATABASE IF NOT EXISTS `tramiapp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `tramiapp`;

-- ---------------------------------------------------------
-- Tabla usuarios
-- Login    -> correo + password
-- Registro -> nombre + fecha_nacimiento + correo + password
-- ---------------------------------------------------------
CREATE TABLE `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  `fecha_nacimiento` DATE NOT NULL,
  `correo` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
