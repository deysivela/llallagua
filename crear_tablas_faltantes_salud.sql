-- ====================================================================
-- SCRIPT SQL SEGURO: SOLO CREA LAS TABLAS FALTANTES DE SALUD Y SUS REGISTROS
-- NO BORRA NI REEMPLAZA NINGUNA TABLA DE LA BASE DE DATOS EXISTENTE
-- ====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `salud_archivos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ruta relativa en disco public',
  `clave_thumb` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta miniatura',
  `clave_opt` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta versión optimizada max 1600px',
  `nombre_original` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tamano` bigint(20) unsigned NOT NULL COMMENT 'Bytes',
  `ancho` smallint(5) unsigned DEFAULT NULL,
  `alto` smallint(5) unsigned DEFAULT NULL,
  `subido_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `salud_archivos_clave_unique` (`clave`),
  KEY `salud_archivos_subido_por_index` (`subido_por`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `salud_establecimientos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('hospital','centro_salud','rehabilitacion') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivel` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zona` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL,
  `responsable` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `atiende_24h` tinyint(1) NOT NULL DEFAULT '0',
  `foto_fachada_id` int(10) unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `salud_establecimientos_slug_unique` (`slug`),
  KEY `salud_establecimientos_foto_fachada_id_foreign` (`foto_fachada_id`),
  CONSTRAINT `salud_establecimientos_foto_fachada_id_foreign` FOREIGN KEY (`foto_fachada_id`) REFERENCES `salud_archivos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `salud_telefonos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `establecimiento_id` int(10) unsigned NOT NULL,
  `numero` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `etiqueta` enum('fijo','celular','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fijo',
  `orden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `salud_telefonos_establecimiento_id_foreign` (`establecimiento_id`),
  CONSTRAINT `salud_telefonos_establecimiento_id_foreign` FOREIGN KEY (`establecimiento_id`) REFERENCES `salud_establecimientos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `salud_horarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `establecimiento_id` int(10) unsigned NOT NULL,
  `dias` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `nota` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `salud_horarios_establecimiento_id_foreign` (`establecimiento_id`),
  CONSTRAINT `salud_horarios_establecimiento_id_foreign` FOREIGN KEY (`establecimiento_id`) REFERENCES `salud_establecimientos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `salud_servicios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `establecimiento_id` int(10) unsigned NOT NULL,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` enum('medicina_general','odontologia','farmacia','vacunacion','fisioterapia','laboratorio','especialidades','emergencias','programas','fonoaudiologia','trabajo_social','enfermeria','imagenologia','telesalud','otro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'otro',
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `orden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `salud_servicios_establecimiento_id_foreign` (`establecimiento_id`),
  CONSTRAINT `salud_servicios_establecimiento_id_foreign` FOREIGN KEY (`establecimiento_id`) REFERENCES `salud_establecimientos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `salud_secciones_detalle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `establecimiento_id` int(10) unsigned NOT NULL,
  `titulo` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `texto` text COLLATE utf8mb4_unicode_ci,
  `items` text COLLATE utf8mb4_unicode_ci,
  `orden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `salud_secciones_detalle_establecimiento_id_foreign` (`establecimiento_id`),
  CONSTRAINT `salud_secciones_detalle_establecimiento_id_foreign` FOREIGN KEY (`establecimiento_id`) REFERENCES `salud_establecimientos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `salud_cronogramas_mensuales` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `establecimiento_id` int(10) unsigned NOT NULL,
  `servicio_id` int(10) unsigned DEFAULT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cronograma_atencion',
  `mes` date NOT NULL COMMENT 'Primer día del mes (YYYY-MM-01)',
  `fecha_fin` date DEFAULT NULL,
  `archivo_id` int(10) unsigned DEFAULT NULL,
  `titulo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nota` text COLLATE utf8mb4_unicode_ci,
  `publicado` tinyint(1) NOT NULL DEFAULT '0',
  `subido_por` int(10) unsigned DEFAULT NULL,
  `eliminado` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `salud_cronogramas_mensuales_establecimiento_id_foreign` (`establecimiento_id`),
  KEY `salud_cronogramas_mensuales_servicio_id_foreign` (`servicio_id`),
  KEY `salud_cronogramas_mensuales_archivo_id_foreign` (`archivo_id`),
  CONSTRAINT `salud_cronogramas_mensuales_archivo_id_foreign` FOREIGN KEY (`archivo_id`) REFERENCES `salud_archivos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salud_cronogramas_mensuales_establecimiento_id_foreign` FOREIGN KEY (`establecimiento_id`) REFERENCES `salud_establecimientos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salud_cronogramas_mensuales_servicio_id_foreign` FOREIGN KEY (`servicio_id`) REFERENCES `salud_servicios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `salud_cronogramas_filas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `establecimiento_id` int(10) unsigned NOT NULL,
  `dia_o_fecha` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `servicio` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profesional` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horario` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `salud_cronogramas_filas_establecimiento_id_foreign` (`establecimiento_id`),
  CONSTRAINT `salud_cronogramas_filas_establecimiento_id_foreign` FOREIGN KEY (`establecimiento_id`) REFERENCES `salud_establecimientos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `salud_profesionales` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `establecimiento_id` int(10) unsigned NOT NULL,
  `servicio_id` int(10) unsigned DEFAULT NULL,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `especialidad` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `franja` enum('08:00-20:00','20:00-08:00','08:00-08:00','otro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `franja_texto` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `salud_profesionales_establecimiento_id_foreign` (`establecimiento_id`),
  KEY `salud_profesionales_servicio_id_foreign` (`servicio_id`),
  CONSTRAINT `salud_profesionales_establecimiento_id_foreign` FOREIGN KEY (`establecimiento_id`) REFERENCES `salud_establecimientos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salud_profesionales_servicio_id_foreign` FOREIGN KEY (`servicio_id`) REFERENCES `salud_servicios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `salud_auditoria` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `accion` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad_id` int(10) unsigned DEFAULT NULL,
  `antes` text COLLATE utf8mb4_unicode_ci,
  `despues` text COLLATE utf8mb4_unicode_ci,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `salud_auditoria_usuario_id_index` (`usuario_id`),
  KEY `salud_auditoria_entidad_entidad_id_index` (`entidad`,`entidad_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `salud_usuario_establecimiento` (
  `user_id` int(10) unsigned NOT NULL,
  `establecimiento_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`establecimiento_id`),
  KEY `salud_usuario_establecimiento_establecimiento_id_foreign` (`establecimiento_id`),
  CONSTRAINT `salud_usuario_establecimiento_establecimiento_id_foreign` FOREIGN KEY (`establecimiento_id`) REFERENCES `salud_establecimientos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salud_usuario_establecimiento_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fotografías de fachada y archivos de cronogramas
REPLACE INTO `salud_archivos` (`id`, `clave`, `clave_thumb`, `clave_opt`, `nombre_original`, `mime`, `tamano`, `ancho`, `alto`, `subido_por`, `created_at`) VALUES
(1, 'server/salud/archivos/W6Sh8BfbsgK2LMhZDkKz4CRql6YVncWUIyO72mDD.png', NULL, NULL, 'rehabilitacion.png', 'image/png', 2514162, 1350, 1032, 1, '2026-11-06 20:34:42'),
(2, 'server/salud/archivos/cYnPhzwxLP1GL1A4sHcbqvolSIKex5XxvEW5f1oT.jpg', NULL, NULL, 'medicina.jpg', 'image/jpeg', 353497, 1600, 1204, 1, '2026-11-06 21:08:56'),
(3, 'server/salud/archivos/KU5vU0zw6cKMV3zQHZy9xZrEmsWbE7Xw4ElfjAVu.jpg', NULL, NULL, 'fachada-csa-sakamarka.jpg', 'image/jpeg', 226585, 1200, 1600, 1, '2026-11-06 21:38:27'),
(4, 'server/salud/archivos/wktK7co56I0eA0nWrMjBcol5Q7LJzSirgU5blrGq.jpg', NULL, NULL, 'fachada-csa-catavi.jpg', 'image/jpeg', 97987, 681, 306, 1, '2026-11-06 21:38:27'),
(5, 'server/salud/archivos/RBaJYJpqXbBD9bmj5HjAKw0HEUBhYUPzrWA2V2s4.jpg', NULL, NULL, 'catavi-cronograma-medicina-oct-2026.jpg', 'image/jpeg', 276735, 1517, 1152, 1, '2026-11-06 21:38:27'),
(6, 'server/salud/archivos/NSSRLCteRo3C8VMpdZOr8nyrHPIkQGZhV0tmOqNJ.jpg', NULL, NULL, 'catavi-cronograma-odontologia-oct-2026.jpg', 'image/jpeg', 144934, 1600, 900, 1, '2026-11-06 21:38:27'),
(7, 'server/salud/archivos/ksJxYbDyjt3YVkyKH88FGanbX6fq9PmOpkCqu0c7.jpg', NULL, NULL, 'catavi-cronograma-farmacia-oct-2026.jpg', 'image/jpeg', 165044, 1600, 900, 1, '2026-11-06 21:38:27'),
(8, 'server/salud/archivos/pf5NQioL2yRqYbuzhXFZajc88GLH74EyzXjj0zDz.jpg', NULL, NULL, 'catavi-cronograma-enfermeria-dentro-oct-2026.jpg', 'image/jpeg', 200290, 1600, 900, 1, '2026-11-06 21:38:27'),
(9, 'server/salud/archivos/FEP1rtbYr2v4d4gqhGrJOiOQxk6tT6NpoctsSkYG.jpg', NULL, NULL, 'catavi-cronograma-enfermeria-fuera-oct-2026.jpg', 'image/jpeg', 190131, 1600, 900, 1, '2026-11-06 21:38:27'),
(10, 'server/salud/archivos/nBBxh3sU6I4UU63xoqvkEZiDxx9egmh0pHqzFi8Z.jpg', NULL, NULL, 'fachada-csa-siglo-xx.jpg', 'image/jpeg', 209840, 1600, 1204, 1, '2026-11-06 21:38:27'),
(11, 'server/salud/archivos/UeTNHKssSb43kQW1MPkTmOApqTWr8OQg0p1SxTjF.jpg', NULL, NULL, 'siglo-xx-cronograma-medicina-oct-2026.jpg', 'image/jpeg', 198537, 1600, 1204, 1, '2026-11-06 21:38:27'),
(12, 'server/salud/archivos/JkIrpfKriphf8SBVhi8z8L1AIS9nLpwHg1zR3nq7.jpg', NULL, NULL, 'siglo-xx-cronograma-farmacia-oct-2026.jpg', 'image/jpeg', 112016, 1600, 1204, 1, '2026-11-06 21:38:27'),
(13, 'server/salud/archivos/niIdQmgPWBMS1ZRCHjRlbVsAAJtUJDyNbpPz8qjA.jpg', NULL, NULL, 'siglo-xx-cronograma-enfermeria-fuera-oct-2026.jpg', 'image/jpeg', 157648, 1600, 1204, 1, '2026-11-06 21:38:27'),
(14, 'server/salud/archivos/AFJUBKjMkZd6x9HbHaNhuUBDhg71RmpDVxUNfw10.jpg', NULL, NULL, 'siglo-xx-cronograma-enfermeria-dentro-oct-2026.jpg', 'image/jpeg', 162881, 1600, 1204, 1, '2026-11-06 21:38:27'),
(15, 'server/salud/archivos/Hw98EjdG8lZhlRd73q0KOoyt7ZimMiK2NissE0DC.jpg', NULL, NULL, 'siglo-xx-cronograma-odontologia-oct-2026.jpg', 'image/jpeg', 112931, 1600, 1204, 1, '2026-11-06 21:38:27'),
(16, 'server/salud/archivos/h1oKmFQJRShX3CsrCk26I3i4GWSbzaANGxbWIkQa.jpg', NULL, NULL, 'siglo-xx-cronograma-estadistica-oct-2026.jpg', 'image/jpeg', 128990, 1600, 1204, 1, '2026-11-06 21:38:27'),
(17, 'server/salud/archivos/2e2QWHpfCmvfQ9Pev14A6bP7CZweOh01z4TcR8AX.png', NULL, NULL, 'segundonivel.png', 'image/png', 263122, 611, 200, 1, '2026-11-06 22:25:38'),
(18, 'server/salud/archivos/m0HKiFJ8YQRmQfSjbnGmfBEeh4s1wF0lqKElF6Jr.jpg', NULL, NULL, 'centro.jpg', 'image/jpeg', 232204, 1280, 960, 1, '2026-11-06 22:26:21');

REPLACE INTO `salud_establecimientos` (`id`, `slug`, `nombre`, `tipo`, `nivel`, `zona`, `direccion`, `latitud`, `longitud`, `responsable`, `descripcion`, `atiende_24h`, `foto_fachada_id`, `activo`) VALUES (1, 'hospital-madre-obrera', 'Hospital de Segundo Nivel "Madre Obrera"', 'hospital', '2.º nivel', 'Viluyo', 'Carretera Llallagua–Uncía (diagonal Jaime Mendoza), zona Viluyo', NULL, NULL, 'Dr. David Richard Velasco Coca, Director Ejecutivo', NULL, 1, 17, 1);
INSERT IGNORE INTO `salud_telefonos` (`id`, `establecimiento_id`, `numero`, `etiqueta`, `orden`) VALUES (1, 1, '(02) 5024-4271', 'fijo', 0);
INSERT IGNORE INTO `salud_horarios` (`id`, `establecimiento_id`, `dias`, `hora_inicio`, `hora_fin`, `nota`) VALUES (1, 1, '[1,2,3,4,5]', '08:30', '12:30', 'Consulta mañana');
INSERT IGNORE INTO `salud_horarios` (`id`, `establecimiento_id`, `dias`, `hora_inicio`, `hora_fin`, `nota`) VALUES (2, 1, '[1,2,3,4,5]', '14:30', '18:30', 'Consulta tarde');
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (1, 1, 'Emergencias y urgencias', 'emergencias', NULL, 0);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (2, 1, 'Laboratorio', 'laboratorio', NULL, 1);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (3, 1, 'Medicina interna', 'medicina_general', NULL, 2);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (4, 1, 'Pediatría y neonatología', 'especialidades', NULL, 3);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (5, 1, 'Traumatología y ortopedia', 'especialidades', NULL, 4);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (6, 1, 'Cirugía general', 'especialidades', NULL, 5);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (7, 1, 'Ginecología y obstetricia', 'especialidades', NULL, 6);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (8, 1, 'Terapia intensiva', 'especialidades', NULL, 7);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (9, 1, 'Gastroenterología', 'especialidades', NULL, 8);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (10, 1, 'Neumología', 'especialidades', NULL, 9);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (11, 1, 'Nutrición', 'especialidades', NULL, 10);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (12, 1, 'Odontología', 'odontologia', NULL, 11);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (13, 1, 'Anestesiología', 'especialidades', NULL, 12);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (14, 1, 'Fisioterapia y kinesiología', 'fisioterapia', NULL, 13);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (15, 1, 'Imagenología – ecografía', 'imagenologia', NULL, 14);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (16, 1, 'Imagenología – rayos X', 'imagenologia', NULL, 15);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (17, 1, 'Enfermería', 'enfermeria', NULL, 16);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (18, 1, 'Telesalud', 'telesalud', NULL, 17);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (1, 1, 'Médicos por especialidad', NULL, '["Ginecología: Acha, Salazar, Ramirez, Cussi, Angulo, Pérez, Sánchez, Araoz","Pediatría: Suárez, Tarqui, Mamani, Apaz, Andia, Tola","Traumatología: Alas, Veramendi, Zepita","Anestesiología: Tinta, Virhuez, Arce, Calustro, Ortuño, Zenteno","Cirugía general: Iriarte, Gambarte, Chile, Cordero, Quispe, Ugarte","Medicina interna: Flores, Jiménez, Callisaya, Conde, Torrico, Denver","Gastroenterología: Dra. Quiroga","Emergencias (médicos de turno): Gallardo, Moya, Mena, Aguilar, Cepeda, Cruz, Velásquez, Serrudo"]', 0);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (2, 1, 'Requisitos para la atención', NULL, '["Particulares: solo carnet de identidad"]', 1);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (3, 1, 'Afiliación al SUS', NULL, '["Acudir primero al centro de salud de primer nivel más cercano a su domicilio (puerta de entrada al SUS)","Boleta de referencia del centro de salud, boleta de reconsulta o epicrisis"]', 2);

REPLACE INTO `salud_establecimientos` (`id`, `slug`, `nombre`, `tipo`, `nivel`, `zona`, `direccion`, `latitud`, `longitud`, `responsable`, `descripcion`, `atiende_24h`, `foto_fachada_id`, `activo`) VALUES (2, 'csa-madre-obrera', 'Centro de Salud Ambulatorio Madre Obrera', 'centro_salud', '1.er nivel', 'Zona 2, Plaza de Armas', 'Av. 10 de Noviembre, Zona 2 (Plaza de Armas), a 1 km de la ciudad', -18.4232015, -66.5867663, 'Dra. Jenny Espejo Flores', NULL, 0, 18, 1);
INSERT IGNORE INTO `salud_horarios` (`id`, `establecimiento_id`, `dias`, `hora_inicio`, `hora_fin`, `nota`) VALUES (3, 2, '[1,2,3,4,5]', '08:00', '20:00', NULL);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (19, 2, 'Programa de Tuberculosis', 'programas', NULL, 0);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (20, 2, 'Programa de la Mujer', 'programas', NULL, 1);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (21, 2, 'ITS / VIH-SIDA', 'programas', NULL, 2);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (22, 2, 'Bono Juana Azurduy', 'programas', NULL, 3);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (23, 2, 'PAI (vacunas)', 'vacunacion', NULL, 4);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (24, 2, 'Crecimiento y desarrollo (CCD)', 'programas', NULL, 5);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (25, 2, 'DIT', 'programas', NULL, 6);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (26, 2, 'Lactancia materna', 'programas', NULL, 7);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (27, 2, 'Zoonosis', 'programas', NULL, 8);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (28, 2, 'Adolescentes', 'programas', NULL, 9);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (29, 2, 'Educación para la vida', 'programas', NULL, 10);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (30, 2, 'Escuelas saludables', 'programas', NULL, 11);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (31, 2, 'Triaje y emergencias', 'emergencias', NULL, 12);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (4, 2, 'Requisitos para la atención', NULL, '["Carnet de identidad y\\/o carnet del SUS"]', 0);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (5, 2, 'Afiliación al SUS', NULL, '["No tener otro seguro de salud (CNS, Caja Petrolera, etc.)","Cédula de identidad"]', 1);

INSERT IGNORE INTO `salud_establecimientos` (`id`, `slug`, `nombre`, `tipo`, `nivel`, `zona`, `direccion`, `latitud`, `longitud`, `responsable`, `descripcion`, `atiende_24h`, `foto_fachada_id`, `activo`) VALUES (3, 'csa-sakamarka', 'Centro de Salud Ambulatorio Sakamarka', 'centro_salud', '1.er nivel', 'Distrito de Sakamarka, Zona 11', 'Zona 11, calle 2 de Agosto, entre Av. Ejército y San Jorge', -18.4267793, -66.5680393, 'Dr. Elmer Oporto Cordel', NULL, 0, 3, 1);
INSERT IGNORE INTO `salud_horarios` (`id`, `establecimiento_id`, `dias`, `hora_inicio`, `hora_fin`, `nota`) VALUES (4, 3, '[1,2,3,4,5]', '08:30', '12:30', 'Horario habitual (mañana)');
INSERT IGNORE INTO `salud_horarios` (`id`, `establecimiento_id`, `dias`, `hora_inicio`, `hora_fin`, `nota`) VALUES (5, 3, '[1,2,3,4,5]', '14:30', '18:30', 'Horario habitual (tarde)');
INSERT IGNORE INTO `salud_horarios` (`id`, `establecimiento_id`, `dias`, `hora_inicio`, `hora_fin`, `nota`) VALUES (6, 3, '[1,2,3,4,5]', '08:30', '16:30', 'Horario continuo con instructiva');
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (32, 3, 'Medicina general', 'medicina_general', NULL, 0);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (33, 3, 'Odontología general', 'odontologia', NULL, 1);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (34, 3, 'Farmacia', 'farmacia', NULL, 2);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (35, 3, 'Aplicación de vacunas', 'vacunacion', NULL, 3);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (36, 3, 'Aplicación de sueros, inyectables y curaciones', 'enfermeria', NULL, 4);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (6, 3, 'Días y horario de atención por servicio', NULL, '["Consultorio médico 2, odontología, farmacia y enfermería: lunes a viernes de 08:30 a 12:30 (turno mañana) y de 14:30 a 16:30 (turno tarde).","Consultorio médico 1: lunes a viernes de 09:00 a 12:00 (mañana) y turno tarde desde las 14:30."]', 0);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (7, 3, 'Requisitos para recibir la atención', NULL, '["Cédula de identidad y estar registrado en el SUS (el registro debe pertenecer al municipio de Llallagua).","Menores de 4 meses: portar únicamente su cartón de recién nacido.","Pacientes con otro tipo de seguro: portar cédula de identidad y realizar la cancelación del servicio.","Por emergencia y por única vez se puede atender a pacientes con SUS de otro municipio."]', 1);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (8, 3, 'Afiliación al SUS y programas de salud', NULL, '["Cédula de identidad vigente.","No contar con otro tipo de seguro de salud (Caja Nacional de Salud, Caja Bancaria, Seguro Universitario, etc.)."]', 2);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (9, 3, 'Actividades y campañas de salud', 'Las actividades y campañas fuera del establecimiento dependen de las instructivas de la Red de Salud Municipal SAFCI – Llallagua, SEDES Potosí, Red de Salud Uncía y el planificador anual.', '["Población dirigida: toda la población en general y grupos específicos según el programa a cargo (ej. Programa de Adolescentes, Programa de Lactancia Materna, etc.).","Requisitos de participación: sin requisitos, solo asistir a la actividad (en unidades educativas participan estudiantes, plantel docente y, con autorización de la dirección, padres de familia).","Responsables: los responsables municipales de cada programa y, en ferias de salud distritales, el responsable del centro de salud junto con todo el personal."]', 3);

INSERT IGNORE INTO `salud_establecimientos` (`id`, `slug`, `nombre`, `tipo`, `nivel`, `zona`, `direccion`, `latitud`, `longitud`, `responsable`, `descripcion`, `atiende_24h`, `foto_fachada_id`, `activo`) VALUES (4, 'csa-catavi', 'Centro de Salud Ambulatorio Catavi', 'centro_salud', '1.er nivel', 'Catavi, Zona Central', 'Zona Central, Av. Bioquímica, frente a la U.E. Ayacucho', -18.41353, -66.56387, 'Dra. Norma Flores Apaza', NULL, 0, 4, 1);
INSERT IGNORE INTO `salud_telefonos` (`id`, `establecimiento_id`, `numero`, `etiqueta`, `orden`) VALUES (2, 4, '2582-2490', 'fijo', 0);
INSERT IGNORE INTO `salud_horarios` (`id`, `establecimiento_id`, `dias`, `hora_inicio`, `hora_fin`, `nota`) VALUES (7, 4, '[1,2,3,4,5]', '08:00', '20:00', 'Medicina general (12 hrs con turnos)');
INSERT IGNORE INTO `salud_horarios` (`id`, `establecimiento_id`, `dias`, `hora_inicio`, `hora_fin`, `nota`) VALUES (8, 4, '[1,2,3,4,5]', '08:00', '14:00', 'Odontología (turno mañana)');
INSERT IGNORE INTO `salud_horarios` (`id`, `establecimiento_id`, `dias`, `hora_inicio`, `hora_fin`, `nota`) VALUES (9, 4, '[1,2,3,4,5]', '14:00', '20:00', 'Odontología (turno tarde)');
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (37, 4, 'Medicina general', 'medicina_general', NULL, 0);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (38, 4, 'Odontología', 'odontologia', NULL, 1);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (39, 4, 'Enfermería', 'enfermeria', NULL, 2);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (40, 4, 'Farmacia', 'farmacia', NULL, 3);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (41, 4, 'Admisión, información y estadística', 'otro', NULL, 4);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (42, 4, 'Programa de adolescentes', 'programas', NULL, 5);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (43, 4, 'Programa de tuberculosis', 'programas', NULL, 6);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (44, 4, 'Atención a la mujer en edad fértil', 'programas', NULL, 7);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (45, 4, 'Violencia', 'programas', NULL, 8);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (46, 4, 'Enfermedades no transmisibles', 'programas', NULL, 9);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (47, 4, 'Promoción de la salud', 'programas', NULL, 10);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (48, 4, 'Zoonosis', 'programas', NULL, 11);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (49, 4, 'VIH / ITS', 'programas', NULL, 12);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (50, 4, 'PAI (vacunas)', 'vacunacion', NULL, 13);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (51, 4, 'Crecimiento y desarrollo (CCD)', 'programas', NULL, 14);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (10, 4, 'Requisitos para recibir la atención', NULL, '["Carnet del SUS y carnet de identidad.","El paciente debe contar con adscripción en el municipio de Llallagua."]', 0);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (11, 4, 'Información y requisitos para el Seguro Único de Salud (SUS)', NULL, '["Contar con el carnet de identidad.","Boleta de agua, luz o gas."]', 1);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (12, 4, 'Campañas y actividades', 'El Centro de Salud Ambulatorio Catavi elabora el cronograma mensual por servicios donde se detallan los días, turnos y fechas de atención.', NULL, 2);

REPLACE INTO `salud_establecimientos` (`id`, `slug`, `nombre`, `tipo`, `nivel`, `zona`, `direccion`, `latitud`, `longitud`, `responsable`, `descripcion`, `atiende_24h`, `foto_fachada_id`, `activo`) VALUES (5, 'csa-siglo-xx', 'Centro de Salud Ambulatorio Siglo XX', 'centro_salud', '1.er nivel', 'Localidad Siglo XX, Campamento 6 B', 'Campamento 6 B, Calle Campero s/n', -18.421879, -66.589474, 'Dr. Gustavo Vasquez Quiñones', NULL, 0, 10, 1);
INSERT IGNORE INTO `salud_telefonos` (`id`, `establecimiento_id`, `numero`, `etiqueta`, `orden`) VALUES (3, 5, '2-582-0058', 'fijo', 0);
INSERT IGNORE INTO `salud_horarios` (`id`, `establecimiento_id`, `dias`, `hora_inicio`, `hora_fin`, `nota`) VALUES (10, 5, '[1,2,3,4,5]', '08:00', '12:00', 'Turno mañana');
INSERT IGNORE INTO `salud_horarios` (`id`, `establecimiento_id`, `dias`, `hora_inicio`, `hora_fin`, `nota`) VALUES (11, 5, '[1,2,3,4,5]', '14:00', '18:00', 'Turno tarde');
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (52, 5, 'Servicio de Medicina', 'medicina_general', NULL, 0);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (53, 5, 'Servicio de Odontología', 'odontologia', NULL, 1);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (54, 5, 'Servicio de Enfermería (CCDD, PAI, DIT, Enfermería, Tuberculosis)', 'enfermeria', NULL, 2);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (55, 5, 'Servicio de Farmacia', 'farmacia', NULL, 3);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (56, 5, 'Servicio de Estadística, Admisiones e Informática', 'otro', NULL, 4);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (13, 5, 'Requisitos para recibir la atención', NULL, '["La atención se realiza sin discriminación alguna a todo paciente que acude al establecimiento, tenga seguro a corto plazo o no.","Carnet de identidad.","Formulario del SUS."]', 0);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (14, 5, 'Información y requisitos para el Seguro Universal de Salud (SUS)', NULL, '["Fotocopia de C.I.","Fotocopia de boleta de luz o agua (según normativa, el paciente debe afiliarse en el centro de salud más cercano a su domicilio, sin limitar la atención en los diferentes centros de salud del municipio)."]', 1);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (15, 5, 'Actividades y campañas de salud', 'Las actividades y campañas se realizan según la planificación de los diferentes programas de la Red Municipal SAFCI Llallagua durante toda la gestión.', NULL, 2);

REPLACE INTO `salud_establecimientos` (`id`, `slug`, `nombre`, `tipo`, `nivel`, `zona`, `direccion`, `latitud`, `longitud`, `responsable`, `descripcion`, `atiende_24h`, `foto_fachada_id`, `activo`) VALUES (6, 'centro-rehabilitacion-llallagua', 'Centro de Rehabilitación Llallagua', 'rehabilitacion', 'GAM Llallagua', 'Distrito Catavi, Zona 12 de Febrero', 'Calle 12 de Febrero, Distrito Catavi', NULL, NULL, 'Lic. Hugo Quispe Mamani', 'El Centro de Rehabilitación Llallagua cuenta con un equipo multidisciplinario encargado de brindar atención integral a personas con discapacidad y población en general, mediante servicios de promoción, prevención, evaluación, tratamiento y rehabilitación.', 0, 1, 1);
INSERT IGNORE INTO `salud_telefonos` (`id`, `establecimiento_id`, `numero`, `etiqueta`, `orden`) VALUES (4, 6, '79310691', 'celular', 0);
INSERT IGNORE INTO `salud_telefonos` (`id`, `establecimiento_id`, `numero`, `etiqueta`, `orden`) VALUES (5, 6, '72213455', 'celular', 1);
INSERT IGNORE INTO `salud_horarios` (`id`, `establecimiento_id`, `dias`, `hora_inicio`, `hora_fin`, `nota`) VALUES (12, 6, '[1,2,3,4,5]', '08:30', '12:30', 'Turno de la mañana');
INSERT IGNORE INTO `salud_horarios` (`id`, `establecimiento_id`, `dias`, `hora_inicio`, `hora_fin`, `nota`) VALUES (13, 6, '[1,2,3,4,5]', '14:00', '18:30', 'Turno de la tarde');
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (57, 6, 'Medicina', 'medicina_general', 'Responsable: Dra. Wendy Nadeska Paco.\n• Evaluación y valoración médica integral.\n• Consulta médica general.\n• Valoración del estado de salud de las personas con discapacidad.\n• Identificación de necesidades de rehabilitación.\n• Seguimiento y control médico.\n• Orientación sobre prevención de enfermedades y complicaciones.\n• Derivación a establecimientos de mayor complejidad cuando corresponda.', 0);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (58, 6, 'Enfermería', 'enfermeria', 'Responsable: Lic. Ana Espinoza.\n• Control de signos vitales.\n• Control de presión arterial, frecuencia cardíaca, frecuencia respiratoria y temperatura.\n• Atención y orientación de enfermería.\n• Promoción de hábitos saludables.\n• Educación para la prevención de enfermedades.\n• Apoyo en actividades de promoción y prevención de la salud.\n• Seguimiento y orientación a pacientes y familiares.', 1);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (59, 6, 'Fisioterapia', 'fisioterapia', 'Responsables: Lic. Jeaniz Felipez, Lic. Brisan Montaño, Lic. Mariela Machaca y Lic. Hugo Quispe.\n• Evaluación y valoración fisioterapéutica.\n• Elaboración de planes individualizados de tratamiento.\n• Rehabilitación de alteraciones musculoesqueléticas.\n• Tratamiento de lesiones neurológicas.\n• Rehabilitación de personas con discapacidad física.\n• Terapia para mejorar la movilidad articular, fuerza muscular y equilibrio.\n• Reeducación de la marcha y entrenamiento funcional.\n• Ejercicios terapéuticos y técnicas de rehabilitación.\n• Prevención de complicaciones secundarias a la discapacidad.\n• Educación al paciente y familiares sobre ejercicios domiciliarios.', 2);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (60, 6, 'Fonoaudiología', 'fonoaudiologia', 'Responsable: Lic. Xiomara Benavides.\n• Evaluación del lenguaje y la comunicación.\n• Evaluación de alteraciones del habla.\n• Intervención en dificultades del desarrollo del lenguaje.\n• Estimulación del lenguaje y la comunicación.\n• Rehabilitación de alteraciones de la articulación y pronunciación.\n• Intervención en dificultades de la comunicación asociadas a trastornos del neurodesarrollo.\n• Orientación a padres y familiares.\n• Seguimiento de la evolución de los pacientes.', 3);
INSERT IGNORE INTO `salud_servicios` (`id`, `establecimiento_id`, `nombre`, `categoria`, `descripcion`, `orden`) VALUES (61, 6, 'Trabajo social', 'trabajo_social', 'Responsable: Lic. Jhemina Cruz.\n• Evaluación sociofamiliar de personas con discapacidad.\n• Orientación sobre derechos y beneficios de las personas con discapacidad.\n• Información sobre trámites y documentación relacionada con discapacidad.\n• Orientación sobre el acceso al Sistema Único de Salud (SUS).\n• Coordinación con instituciones y organizaciones sociales.\n• Seguimiento de casos sociales.\n• Visitas domiciliarias.\n• Promoción de la inclusión familiar, educativa y comunitaria.\n• Sensibilización sobre los derechos de las personas con discapacidad.', 4);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (16, 6, 'Requisitos para recibir atención', NULL, '["Cédula de identidad del paciente.","Carnet del Sistema Único de Salud (SUS), cuando corresponda.","Referencia médica.","Estudios complementarios anteriores (radiografías, ecografías, tomografías, laboratorios u otros), cuando corresponda.","Informes médicos y antecedentes de tratamientos previos.","En caso de menores de edad, acudir acompañado de su padre, madre o tutor.","Documentación relacionada con discapacidad, si corresponde.","La atención estará sujeta a la valoración del profesional y a los procedimientos establecidos por el establecimiento de salud."]', 0);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (17, 6, 'Información sobre el Seguro Universal de Salud (SUS) y programas de salud', 'El Centro de Rehabilitación Llallagua orienta a la población sobre el acceso a los servicios de salud y los beneficios del Sistema Único de Salud (SUS), conforme a la normativa vigente:', '["Requisitos para acceder al SUS.","Derechos de los usuarios de los servicios de salud.","Atención integral a personas con discapacidad.","Prevención y promoción de la salud.","Programas de salud vigentes.","Referencia y contrarreferencia cuando corresponda."]', 1);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (18, 6, 'Actividades y campañas de salud', 'El Centro de Rehabilitación Llallagua desarrolla actividades de promoción, prevención, sensibilización e inclusión social, dirigidas a personas con discapacidad, familias y población en general:', '["Campañas de sensibilización sobre los derechos de las personas con discapacidad.","Jornadas de prevención de la discapacidad.","Actividades de promoción de la salud y prevención de enfermedades.","Charlas educativas sobre rehabilitación y cuidado de la salud.","Actividades de sensibilización sobre el buen trato e inclusión social.","Caminatas y ferias de sensibilización por fechas conmemorativas relacionadas con la salud.","Campañas de prevención y cuidado de la salud visual.","Visitas domiciliarias y seguimiento a personas con discapacidad.","Actividades de orientación a familiares y cuidadores.","Coordinación interinstitucional para fortalecer la rehabilitación y la inclusión comunitaria."]', 2);
INSERT IGNORE INTO `salud_secciones_detalle` (`id`, `establecimiento_id`, `titulo`, `texto`, `items`, `orden`) VALUES (19, 6, 'Observaciones generales', 'El Centro de Rehabilitación Llallagua desarrolla sus actividades mediante un equipo multidisciplinario, brindando servicios orientados a mejorar la calidad de vida, autonomía funcional, inclusión y participación social de las personas con discapacidad y población en general. Asimismo, se continúa fortaleciendo la coordinación con el Gobierno Autónomo Municipal de Llallagua, establecimientos de salud, organizaciones sociales e instituciones públicas y privadas, con la finalidad de ampliar el acceso a los servicios de rehabilitación.', NULL, 3);
INSERT IGNORE INTO `salud_cronogramas_filas` (`id`, `establecimiento_id`, `dia_o_fecha`, `servicio`, `profesional`, `horario`, `observaciones`, `orden`) VALUES (1, 6, 'Lunes a viernes', 'Medicina', 'Dra. Wendy Nadeska Paco', '09:00–12:00 / 14:00–17:00', 'Consultorio médico', 0);
INSERT IGNORE INTO `salud_cronogramas_filas` (`id`, `establecimiento_id`, `dia_o_fecha`, `servicio`, `profesional`, `horario`, `observaciones`, `orden`) VALUES (2, 6, 'Lunes a viernes', 'Enfermería', 'Lic. Ana Espinoza', '09:00–12:00 / 14:00–17:00', 'Área de enfermería', 1);
INSERT IGNORE INTO `salud_cronogramas_filas` (`id`, `establecimiento_id`, `dia_o_fecha`, `servicio`, `profesional`, `horario`, `observaciones`, `orden`) VALUES (3, 6, 'Lunes a viernes', 'Fisioterapia', 'Lic. Jeaniz Felipez', '09:00–12:00 / 14:00–17:00', 'Área de fisioterapia', 2);
INSERT IGNORE INTO `salud_cronogramas_filas` (`id`, `establecimiento_id`, `dia_o_fecha`, `servicio`, `profesional`, `horario`, `observaciones`, `orden`) VALUES (4, 6, 'Lunes a viernes', 'Fisioterapia', 'Lic. Brisan Montaño', '08:30–12:30 / 14:30–18:30', 'Área de fisioterapia', 3);
INSERT IGNORE INTO `salud_cronogramas_filas` (`id`, `establecimiento_id`, `dia_o_fecha`, `servicio`, `profesional`, `horario`, `observaciones`, `orden`) VALUES (5, 6, 'Lunes a viernes', 'Fisioterapia', 'Lic. Mariela Machaca', '09:00–12:00 / 14:00–17:00', 'Área de fisioterapia', 4);
INSERT IGNORE INTO `salud_cronogramas_filas` (`id`, `establecimiento_id`, `dia_o_fecha`, `servicio`, `profesional`, `horario`, `observaciones`, `orden`) VALUES (6, 6, 'Lunes a viernes', 'Fisioterapia', 'Lic. Hugo Quispe', '09:00–12:00 / 14:00–17:00', 'Área de fisioterapia', 5);
INSERT IGNORE INTO `salud_cronogramas_filas` (`id`, `establecimiento_id`, `dia_o_fecha`, `servicio`, `profesional`, `horario`, `observaciones`, `orden`) VALUES (7, 6, 'Lunes a viernes', 'Trabajo Social', 'Lic. Jhemina Cruz', '09:00–12:00 / 14:00–17:00', 'Área de trabajo social', 6);
INSERT IGNORE INTO `salud_cronogramas_filas` (`id`, `establecimiento_id`, `dia_o_fecha`, `servicio`, `profesional`, `horario`, `observaciones`, `orden`) VALUES (8, 6, 'Lunes a viernes', 'Fonoaudiología', 'Lic. Xiomara Benavides', '14:30–18:30', 'Área de fonoaudiología', 7);

-- Cronogramas mensuales y roles de turnos subidos
REPLACE INTO `salud_cronogramas_mensuales` (`id`, `establecimiento_id`, `servicio_id`, `tipo`, `mes`, `fecha_fin`, `archivo_id`, `titulo`, `nota`, `publicado`, `subido_por`, `eliminado`, `created_at`, `updated_at`) VALUES
(1, 6, 57, 'cronograma_atencion', '2026-11-01', NULL, 2, 'medicina noviembre', NULL, 1, 1, 0, '2026-11-06 21:08:56', '2026-11-06 21:55:55'),
(2, 4, 37, 'cronograma_atencion', '2026-10-01', NULL, 5, 'Cronograma Servicio de Medicina', 'Consultorios 1, 2, 3, Comunidad y Emergencias.', 1, 1, 0, '2026-11-06 21:38:27', '2026-11-06 21:55:55'),
(3, 4, 38, 'cronograma_atencion', '2026-10-01', NULL, 6, 'Rol de Turnos Servicio de Odontología', 'Trabajo en horario discontinuo a dos turnos: 08:00 a 14:00 y 14:00 a 20:00.', 1, 1, 0, '2026-11-06 21:38:27', '2026-11-06 21:55:55'),
(4, 4, 40, 'cronograma_atencion', '2026-10-01', NULL, 7, 'Rol de Turnos de Farmacia', 'Dra. Nivia Esther Huallpa Gabriel — Mañana: 08:00 a 12:00 / Tarde: 14:00 a 18:00.', 1, 1, 0, '2026-11-06 21:38:27', '2026-11-06 21:55:55'),
(5, 4, 39, 'cronograma_atencion', '2026-10-01', NULL, 8, 'Rol de Turnos en Consultorio Externo (Dentro de Servicios)', 'Servicios de Enfermería, P.A.I. y C y D.', 1, 1, 0, '2026-11-06 21:38:27', '2026-11-06 21:55:55'),
(6, 4, 39, 'cronograma_atencion', '2026-10-01', NULL, 9, 'Rol de Turnos Fuera de Servicio (Enfermería)', 'Atención en zonas y urbanizaciones del distrito Catavi.', 1, 1, 0, '2026-11-06 21:38:27', '2026-11-06 21:55:55'),
(7, 5, 52, 'cronograma_atencion', '2026-10-01', NULL, 11, 'Cronograma del Servicio de Medicina', 'Consultorios 1 y 2, y atención en comunidad.', 1, 1, 0, '2026-11-06 21:38:27', '2026-11-06 21:55:55'),
(8, 5, 55, 'cronograma_atencion', '2026-10-01', NULL, 12, 'Rol de Turno Servicio Farmacia', 'Dra. Yolanda Castro Sandoval — Mañana: 09:00 a 12:00 / Tarde: 14:30 a 17:30.', 1, 1, 0, '2026-11-06 21:38:27', '2026-11-06 21:55:55'),
(9, 5, 54, 'cronograma_atencion', '2026-10-01', NULL, 13, 'Rol de Turnos de Enfermería (Fuera del Servicio)', 'Resp. de Enfermería: Lic. Dora S. Guarachi Catari — Campamentos y urbanizaciones.', 1, 1, 0, '2026-11-06 21:38:27', '2026-11-06 21:55:55'),
(10, 5, 54, 'cronograma_atencion', '2026-10-01', NULL, 14, 'Rol de Turnos de Enfermería (Dentro del Servicio)', 'Resp. de Enfermería: Lic. Dora S. Guarachi Catari — C.C.D., P.A.I., Enfermería y Triaje DIT.', 1, 1, 0, '2026-11-06 21:38:27', '2026-11-06 21:55:55'),
(11, 5, 53, 'cronograma_atencion', '2026-10-01', NULL, 15, 'Rol de Turnos Servicio de Odontología', 'Dr. Gustavo Vásquez Quiñones — Mañana: 09:00 a 12:00 / Tarde: 14:30 a 17:30.', 1, 1, 0, '2026-11-06 21:38:27', '2026-11-06 21:55:55'),
(12, 5, 56, 'cronograma_atencion', '2026-10-01', NULL, 16, 'Cronograma de Actividades Servicio de Estadística', 'Ing. Zaid Pillco Janco — Trabajo en horario de oficina mañana y tarde (08:00 a 12:00 y 14:00 a 18:00).', 1, 1, 0, '2026-11-06 21:38:27', '2026-11-06 21:55:55');

-- Permisos del módulo Salud (solo se insertan si no existen en permissions)
INSERT INTO `permissions` (`name`, `slug`, `description`, `created_at`, `updated_at`) SELECT 'Ver módulo Salud', 'salud.index', 'Lista y accede al módulo de salud', NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `slug` = 'salud.index');
INSERT INTO `permissions` (`name`, `slug`, `description`, `created_at`, `updated_at`) SELECT 'Editar establecimientos', 'salud.edit', 'Edita datos de establecimientos de salud', NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `slug` = 'salud.edit');
INSERT INTO `permissions` (`name`, `slug`, `description`, `created_at`, `updated_at`) SELECT 'Eliminar establecimientos', 'salud.destroy', 'Elimina establecimientos de salud', NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `slug` = 'salud.destroy');
INSERT INTO `permissions` (`name`, `slug`, `description`, `created_at`, `updated_at`) SELECT 'Gestionar cronogramas', 'salud.cronogramas', 'Sube y gestiona cronogramas del módulo salud', NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `slug` = 'salud.cronogramas');
INSERT INTO `permissions` (`name`, `slug`, `description`, `created_at`, `updated_at`) SELECT 'Ver auditoría Salud', 'salud.auditoria', 'Consulta el registro de auditoría del módulo salud', NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `slug` = 'salud.auditoria');

SET FOREIGN_KEY_CHECKS = 1;
