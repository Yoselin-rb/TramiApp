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

-- ---------------------------------------------------------
-- Tabla favoritos
-- Guarda qué trámites marcó como favorito cada usuario
-- (se llena/vacía cuando el usuario toca la estrella ⭐ de un trámite)
-- `tramite_id` es un identificador corto y fijo para cada trámite,
-- por ejemplo: 'ute-factura', 'cedula', 'butia', 'gubuy', 'licencia'
-- ---------------------------------------------------------
CREATE TABLE `favoritos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` INT(11) NOT NULL,
  `tramite_id` VARCHAR(50) NOT NULL,
  `fecha_agregado` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_tramite` (`usuario_id`, `tramite_id`),
  CONSTRAINT `fk_favoritos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;