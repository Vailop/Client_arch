-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: admon_arch2
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `archivos`
--

DROP TABLE IF EXISTS `archivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `archivos` (
  `id_archivo` int(11) NOT NULL AUTO_INCREMENT,
  `id_tipo_archivo` int(11) NOT NULL,
  `id_registro_asociado` int(11) NOT NULL,
  `tabla_asociada` enum('pagos','desarrollos') NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `nombre_original` varchar(255) DEFAULT NULL,
  `extension` varchar(10) NOT NULL,
  `comentarios` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) NOT NULL,
  PRIMARY KEY (`id_archivo`),
  KEY `id_tipo_archivo` (`id_tipo_archivo`),
  KEY `usuario_creacion` (`usuario_creacion`),
  CONSTRAINT `archivos_ibfk_1` FOREIGN KEY (`id_tipo_archivo`) REFERENCES `catalogo_tipos_archivo` (`id_tipo_archivo`),
  CONSTRAINT `archivos_ibfk_2` FOREIGN KEY (`usuario_creacion`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `archivos`
--

LOCK TABLES `archivos` WRITE;
/*!40000 ALTER TABLE `archivos` DISABLE KEYS */;
/*!40000 ALTER TABLE `archivos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditoria`
--

DROP TABLE IF EXISTS `auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria` (
  `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `tipo_evento` varchar(50) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `tabla_afectada` varchar(100) DEFAULT NULL,
  `id_registro_afectado` int(11) DEFAULT NULL,
  `detalles_cambio` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detalles_cambio`)),
  `ip_origen` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_auditoria`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `auditoria_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria`
--

LOCK TABLES `auditoria` WRITE;
/*!40000 ALTER TABLE `auditoria` DISABLE KEYS */;
/*!40000 ALTER TABLE `auditoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `avance_desarrollo`
--

DROP TABLE IF EXISTS `avance_desarrollo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avance_desarrollo` (
  `id_avance` int(11) NOT NULL AUTO_INCREMENT,
  `id_desarrollo` int(11) NOT NULL,
  `id_categoria_avance` int(11) NOT NULL,
  `valor_actual` decimal(10,2) DEFAULT 0.00,
  `valor_objetivo` decimal(10,2) DEFAULT 1.00,
  `peso` decimal(10,2) DEFAULT NULL,
  `unidad` varchar(50) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `usuario_modificacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_avance`),
  UNIQUE KEY `id_desarrollo` (`id_desarrollo`,`id_categoria_avance`),
  KEY `id_categoria_avance` (`id_categoria_avance`),
  CONSTRAINT `avance_desarrollo_ibfk_1` FOREIGN KEY (`id_desarrollo`) REFERENCES `desarrollos` (`id_desarrollo`),
  CONSTRAINT `avance_desarrollo_ibfk_2` FOREIGN KEY (`id_categoria_avance`) REFERENCES `catalogo_avance_desarrollo` (`id_categoria_avance`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avance_desarrollo`
--

LOCK TABLES `avance_desarrollo` WRITE;
/*!40000 ALTER TABLE `avance_desarrollo` DISABLE KEYS */;
INSERT INTO `avance_desarrollo` VALUES (1,2,1,1.00,1.00,0.10,'fase','2025-09-25 03:09:25',NULL,'2025-09-25 03:09:25',NULL),(2,2,2,0.50,1.00,0.15,'fase','2025-09-25 03:09:25',NULL,'2025-09-25 03:09:25',NULL),(3,2,3,0.20,1.00,0.25,'fase','2025-09-25 03:09:25',NULL,'2025-09-25 03:09:25',NULL),(4,2,4,0.00,1.00,0.20,'fase','2025-09-25 03:09:25',NULL,'2025-09-25 03:09:25',NULL),(5,2,5,0.00,1.00,0.15,'fase','2025-09-25 03:09:25',NULL,'2025-09-25 03:09:25',NULL),(6,2,6,0.00,1.00,0.10,'fase','2025-09-25 03:09:25',NULL,'2025-09-25 03:09:25',NULL),(7,2,7,0.00,1.00,0.03,'fase','2025-09-25 03:09:25',NULL,'2025-09-25 03:09:25',NULL),(8,2,8,0.00,1.00,0.02,'fase','2025-09-25 03:09:25',NULL,'2025-09-25 03:09:25',NULL);
/*!40000 ALTER TABLE `avance_desarrollo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogo_avance_desarrollo`
--

DROP TABLE IF EXISTS `catalogo_avance_desarrollo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catalogo_avance_desarrollo` (
  `id_categoria_avance` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `orden` int(11) NOT NULL,
  PRIMARY KEY (`id_categoria_avance`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogo_avance_desarrollo`
--

LOCK TABLES `catalogo_avance_desarrollo` WRITE;
/*!40000 ALTER TABLE `catalogo_avance_desarrollo` DISABLE KEYS */;
INSERT INTO `catalogo_avance_desarrollo` VALUES (1,'Planificación',NULL,1),(2,'Excavación y cimentación',NULL,2),(3,'Pilares / estructura',NULL,3),(4,'Instalaciones',NULL,4),(5,'Acabados interiores y exteriores',NULL,5),(6,'Equipamiento',NULL,6),(7,'Inspección y pruebas',NULL,7),(8,'Entrega',NULL,8);
/*!40000 ALTER TABLE `catalogo_avance_desarrollo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogo_estado_pago`
--

DROP TABLE IF EXISTS `catalogo_estado_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catalogo_estado_pago` (
  `id_estado_pago` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_estado_pago`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogo_estado_pago`
--

LOCK TABLES `catalogo_estado_pago` WRITE;
/*!40000 ALTER TABLE `catalogo_estado_pago` DISABLE KEYS */;
/*!40000 ALTER TABLE `catalogo_estado_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogo_tipos_archivo`
--

DROP TABLE IF EXISTS `catalogo_tipos_archivo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catalogo_tipos_archivo` (
  `id_tipo_archivo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_tipo_archivo`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogo_tipos_archivo`
--

LOCK TABLES `catalogo_tipos_archivo` WRITE;
/*!40000 ALTER TABLE `catalogo_tipos_archivo` DISABLE KEYS */;
/*!40000 ALTER TABLE `catalogo_tipos_archivo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `desarrollo_costo_mensual`
--

DROP TABLE IF EXISTS `desarrollo_costo_mensual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `desarrollo_costo_mensual` (
  `id_costo_mensual` int(11) NOT NULL AUTO_INCREMENT,
  `id_desarrollo` int(11) NOT NULL,
  `anio` smallint(6) NOT NULL,
  `mes` tinyint(4) NOT NULL,
  `m2_mensual` decimal(10,2) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `usuario_modificacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_costo_mensual`),
  UNIQUE KEY `id_desarrollo` (`id_desarrollo`,`anio`,`mes`),
  CONSTRAINT `desarrollo_costo_mensual_ibfk_1` FOREIGN KEY (`id_desarrollo`) REFERENCES `desarrollos` (`id_desarrollo`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `desarrollo_costo_mensual`
--

LOCK TABLES `desarrollo_costo_mensual` WRITE;
/*!40000 ALTER TABLE `desarrollo_costo_mensual` DISABLE KEYS */;
INSERT INTO `desarrollo_costo_mensual` VALUES (1,2,2025,1,4613895.00,'2025-09-24 20:37:20',NULL,'2025-09-25 03:33:32',NULL),(2,2,2025,2,47117.04,'2025-09-24 20:37:20',NULL,'2025-09-25 03:33:32',NULL),(3,2,2025,3,48095.13,'2025-09-24 20:37:20',NULL,'2025-09-25 03:33:32',NULL),(4,2,2025,4,49073.22,'2025-09-24 20:37:20',NULL,'2025-09-25 03:33:32',NULL),(5,2,2025,5,50051.32,'2025-09-24 20:37:20',NULL,'2025-09-25 03:33:32',NULL),(6,2,2025,6,51029.41,'2025-09-24 20:37:20',NULL,'2025-09-25 03:33:32',NULL),(7,2,2025,7,52007.50,'2025-09-24 20:37:20',NULL,'2025-09-25 03:33:32',NULL),(8,2,2025,8,52985.59,'2025-09-24 20:37:20',NULL,'2025-09-25 03:33:32',NULL),(9,2,2025,9,53963.68,'2025-09-24 20:37:20',NULL,'2025-09-25 03:33:32',NULL),(10,1,2025,1,40000.00,'2025-09-25 08:58:32',NULL,'2025-09-25 08:58:32',NULL),(11,1,2025,2,42000.00,'2025-09-25 08:58:32',NULL,'2025-09-25 08:58:32',NULL),(12,1,2025,3,45000.00,'2025-09-25 08:58:32',NULL,'2025-09-25 08:58:32',NULL),(13,1,2025,4,48000.00,'2025-09-25 08:58:32',NULL,'2025-09-25 08:58:32',NULL),(14,1,2025,5,51000.00,'2025-09-25 08:58:32',NULL,'2025-09-25 08:58:32',NULL),(15,1,2025,6,51000.00,'2025-09-25 08:58:32',NULL,'2025-09-25 08:58:32',NULL),(16,1,2025,7,54000.00,'2025-09-25 08:58:32',NULL,'2025-09-25 08:58:32',NULL),(17,1,2025,8,56000.00,'2025-09-25 08:58:32',NULL,'2025-09-25 08:58:32',NULL),(18,1,2025,9,56000.00,'2025-09-25 08:58:32',NULL,'2025-09-25 08:58:32',NULL),(19,1,2025,10,60000.00,'2025-09-25 08:58:32',NULL,'2025-09-25 08:58:32',NULL);
/*!40000 ALTER TABLE `desarrollo_costo_mensual` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `desarrollos`
--

DROP TABLE IF EXISTS `desarrollos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `desarrollos` (
  `id_desarrollo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `imagen_principal` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `usuario_modificacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_desarrollo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `desarrollos`
--

LOCK TABLES `desarrollos` WRITE;
/*!40000 ALTER TABLE `desarrollos` DISABLE KEYS */;
INSERT INTO `desarrollos` VALUES (1,'SAN PEDRO ','HERMOSO DEPARTAMENTO','SON PEDRO',NULL,1,'2025-09-24 20:31:48',NULL,'2025-09-24 20:31:48',NULL),(2,'HUIXQUILUCAN','ESTUPENDO DEPARTAMENTO CON VISTA ','HUIXQUILUCAN',NULL,1,'2025-09-24 20:31:48',NULL,'2025-09-24 20:31:48',NULL);
/*!40000 ALTER TABLE `desarrollos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_pago`
--

DROP TABLE IF EXISTS `detalles_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalles_pago` (
  `id_detalles_pago` int(11) NOT NULL AUTO_INCREMENT,
  `id_pago` int(11) NOT NULL,
  `precio_compraventa` decimal(10,2) DEFAULT NULL,
  `precio_actual` decimal(10,2) DEFAULT NULL,
  `m2_inicial` decimal(10,2) DEFAULT NULL,
  `m2_actual` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_detalles_pago`),
  KEY `id_pago` (`id_pago`),
  CONSTRAINT `detalles_pago_ibfk_1` FOREIGN KEY (`id_pago`) REFERENCES `pagos` (`id_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_pago`
--

LOCK TABLES `detalles_pago` WRITE;
/*!40000 ALTER TABLE `detalles_pago` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_notificacion`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_desarrollo` int(11) NOT NULL,
  `departamento_no` varchar(50) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `id_estado_pago` int(11) NOT NULL,
  `comentarios_cliente` text DEFAULT NULL,
  `comentarios_admin` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `usuario_modificacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pago`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_desarrollo` (`id_desarrollo`),
  KEY `id_estado_pago` (`id_estado_pago`),
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`id_desarrollo`) REFERENCES `desarrollos` (`id_desarrollo`),
  CONSTRAINT `pagos_ibfk_3` FOREIGN KEY (`id_estado_pago`) REFERENCES `catalogo_estado_pago` (`id_estado_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programar_pagos`
--

DROP TABLE IF EXISTS `programar_pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programar_pagos` (
  `id_cronograma_pago` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_desarrollo` int(11) NOT NULL,
  `departamento_no` varchar(50) NOT NULL,
  `monto_esperado` decimal(10,2) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `id_pago_realizado` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_cronograma_pago`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_desarrollo` (`id_desarrollo`),
  KEY `id_pago_realizado` (`id_pago_realizado`),
  CONSTRAINT `programar_pagos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `programar_pagos_ibfk_2` FOREIGN KEY (`id_desarrollo`) REFERENCES `desarrollos` (`id_desarrollo`),
  CONSTRAINT `programar_pagos_ibfk_3` FOREIGN KEY (`id_pago_realizado`) REFERENCES `pagos` (`id_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programar_pagos`
--

LOCK TABLES `programar_pagos` WRITE;
/*!40000 ALTER TABLE `programar_pagos` DISABLE KEYS */;
INSERT INTO `programar_pagos` VALUES (1,2,1,'15',3000.00,'2025-09-27',NULL),(2,2,1,'15',3000.00,'2025-10-27',NULL),(3,2,1,'15',3000.00,'0025-11-27',NULL);
/*!40000 ALTER TABLE `programar_pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recuperacion_password`
--

DROP TABLE IF EXISTS `recuperacion_password`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recuperacion_password` (
  `id_recuperacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiracion` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id_recuperacion`),
  UNIQUE KEY `token` (`token`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `recuperacion_password_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recuperacion_password`
--

LOCK TABLES `recuperacion_password` WRITE;
/*!40000 ALTER TABLE `recuperacion_password` DISABLE KEYS */;
/*!40000 ALTER TABLE `recuperacion_password` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin',NULL,'2025-09-24 20:27:26','2025-09-24 20:27:26'),(2,'cliente',NULL,'2025-09-24 20:27:26','2025-09-24 20:27:26');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `id_rol` int(11) NOT NULL,
  `rfc` varchar(13) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido_paterno` varchar(100) NOT NULL,
  `apellido_materno` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `usuario_modificacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `rfc` (`rfc`),
  UNIQUE KEY `email` (`email`),
  KEY `id_rol` (`id_rol`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,1,'GUMA910522','CARLOS','LARA','FERNANDEZ','alo@gmail.com','$2y$10$LdyxtTf46mwe09AWoenI7O7ITMixkF.JBUXFj9A5r3jNpSgRXrEPK','5555555555',1,'2025-09-24 20:30:11',NULL,'2025-09-24 21:02:12',NULL),(2,2,'SALK921224','KARLA','SORIA','GUERRERO','kar@gmail.com','$2y$10$LdyxtTf46mwe09AWoenI7O7ITMixkF.JBUXFj9A5r3jNpSgRXrEPK','5555555555',1,'2025-09-24 20:30:11',NULL,'2025-09-24 23:01:33',NULL),(3,2,'LOVJ850730F96','Ar','Sl','KP','kp@gmail.com','$2y$10$8pNBVOHztCkh3WYo9SzlU.suZba0VeBj9XyNwmlRNEc7f9g78pfnG','5555555555',1,'2025-09-25 22:51:29',NULL,'2025-09-25 22:51:29',NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios_desarrollos`
--

DROP TABLE IF EXISTS `usuarios_desarrollos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios_desarrollos` (
  `id_usuario_desarrollo` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_desarrollo` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  `departamento_no` varchar(50) NOT NULL,
  PRIMARY KEY (`id_usuario_desarrollo`),
  UNIQUE KEY `id_usuario` (`id_usuario`,`id_desarrollo`),
  KEY `id_desarrollo` (`id_desarrollo`),
  CONSTRAINT `usuarios_desarrollos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `usuarios_desarrollos_ibfk_2` FOREIGN KEY (`id_desarrollo`) REFERENCES `desarrollos` (`id_desarrollo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios_desarrollos`
--

LOCK TABLES `usuarios_desarrollos` WRITE;
/*!40000 ALTER TABLE `usuarios_desarrollos` DISABLE KEYS */;
INSERT INTO `usuarios_desarrollos` VALUES (1,2,1,'2025-09-24 20:32:38',NULL,'15'),(2,2,2,'2025-09-24 20:32:38',NULL,'12'),(3,1,2,'2025-09-24 20:32:38',NULL,'16'),(4,3,2,'2025-09-25 22:55:25',NULL,'140');
/*!40000 ALTER TABLE `usuarios_desarrollos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'admon_arch2'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-25 23:01:45
