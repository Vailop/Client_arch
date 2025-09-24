-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-09-2025 a las 23:55:46
-- Versión del servidor: 11.3.2-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `admon_arch`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbc_perfiles`
--

CREATE TABLE `tbc_perfiles` (
  `IdPerfil` int(11) NOT NULL,
  `DesPerfil` varchar(50) DEFAULT NULL,
  `Estatus` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Volcado de datos para la tabla `tbc_perfiles`
--

INSERT INTO `tbc_perfiles` (`IdPerfil`, `DesPerfil`, `Estatus`) VALUES
(1, 'Administrador', 1),
(2, 'Cliente', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbp_desarrollos`
--

CREATE TABLE `tbp_desarrollos` (
  `IdDesarrollo` int(11) NOT NULL,
  `Nombre_Desarrollo` varchar(255) DEFAULT NULL,
  `Descripcion` varchar(500) DEFAULT NULL,
  `RutaLogo` varchar(500) DEFAULT NULL,
  `Estatus` int(11) DEFAULT NULL,
  `RutaImagenes` varchar(500) DEFAULT NULL,
  `UrlVideo` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Volcado de datos para la tabla `tbp_desarrollos`
--

INSERT INTO `tbp_desarrollos` (`IdDesarrollo`, `Nombre_Desarrollo`, `Descripcion`, `RutaLogo`, `Estatus`, `RutaImagenes`, `UrlVideo`) VALUES
(1, 'San Pedro De Los Pinos', 'Es un desarrollo exclusivo que cuenta con 137 departamentos que van desde los 43m2 hasta 257m2, distribuidos en 31 niveles de uno, dos y tres recámaras, con acabados de lujo que cuentan con los más altos estándares de calidad.', 'images/san_pedro_de_los_pinos/fachada.webp', 1, 'assets/images/Desarrollos/San_pedro_de_los_pinos', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbp_usuarios`
--

CREATE TABLE `tbp_usuarios` (
  `IdUsuario` int(11) NOT NULL,
  `Nombre` varchar(500) NOT NULL,
  `RFC` varchar(13) NOT NULL,
  `Contrasena` varchar(1000) NOT NULL,
  `Correo_electronico` varchar(255) DEFAULT NULL,
  `Telefono` varchar(25) DEFAULT NULL,
  `Estatus` int(11) DEFAULT NULL,
  `IdPerfil` int(11) DEFAULT 2,
  `Avatar` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Volcado de datos para la tabla `tbp_usuarios`
--

INSERT INTO `tbp_usuarios` (`IdUsuario`, `Nombre`, `RFC`, `Contrasena`, `Correo_electronico`, `Telefono`, `Estatus`, `IdPerfil`, `Avatar`) VALUES
(1, 'Jorge Alberto López Villeda', 'LOVJ850730F96', 'LOVJ850730F97', 'vil_vailop@hotmail.com', '5525641799', 1, 2, 'images/avatars/avatar_hombre.png'),
(2, 'Maria Eugenia Arce Salgado', 'LOVJ850730F98', 'LOVJ850730F99', 'vil_vailop@hotmail.com', '5531408505', 1, 1, 'images/avatars/avatar_mujer.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbr_avance_desarrollo`
--

CREATE TABLE `tbr_avance_desarrollo` (
  `IdAvance` bigint(20) NOT NULL,
  `IdDesarrollo` int(11) NOT NULL,
  `Categoria` varchar(120) NOT NULL,
  `ValorActual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ValorObjetivo` decimal(10,2) NOT NULL DEFAULT 1.00,
  `Peso` decimal(6,3) NOT NULL DEFAULT 0.000,
  `Unidad` varchar(30) DEFAULT NULL,
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbr_avance_desarrollo`
--

INSERT INTO `tbr_avance_desarrollo` (`IdAvance`, `IdDesarrollo`, `Categoria`, `ValorActual`, `ValorObjetivo`, `Peso`, `Unidad`, `UpdatedAt`) VALUES
(1, 1, 'Planificación', 1.00, 1.00, 0.100, 'fase', '2025-09-23 02:58:25'),
(2, 1, 'Excavación y cimentación', 0.50, 1.00, 0.150, 'fase', '2025-09-23 02:58:25'),
(3, 1, 'Pilares / estructura', 0.20, 1.00, 0.250, 'fase', '2025-09-23 02:58:25'),
(4, 1, 'Instalaciones', 0.00, 1.00, 0.200, 'fase', '2025-09-23 02:58:25'),
(5, 1, 'Acabados interiores y exteriores', 0.00, 1.00, 0.150, 'fase', '2025-09-23 02:58:25'),
(6, 1, 'Equipamiento', 0.00, 1.00, 0.100, 'fase', '2025-09-23 02:58:25'),
(7, 1, 'Inspección y pruebas', 0.00, 1.00, 0.030, 'fase', '2025-09-23 02:58:25'),
(8, 1, 'Entrega', 0.00, 1.00, 0.020, 'fase', '2025-09-23 02:58:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbr_desarrollos_costo_mensual`
--

CREATE TABLE `tbr_desarrollos_costo_mensual` (
  `Id` int(11) NOT NULL,
  `IdDesarrollo` int(11) DEFAULT NULL,
  `M2Mensual` decimal(10,2) DEFAULT NULL,
  `Mes` int(11) DEFAULT NULL,
  `Anio` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Volcado de datos para la tabla `tbr_desarrollos_costo_mensual`
--

INSERT INTO `tbr_desarrollos_costo_mensual` (`Id`, `IdDesarrollo`, `M2Mensual`, `Mes`, `Anio`) VALUES
(1, 1, 46138.95, 1, 2025),
(2, 1, 47117.04, 2, 2025),
(3, 1, 48095.13, 3, 2025),
(4, 1, 49073.22, 4, 2025),
(5, 1, 50051.32, 5, 2025),
(6, 1, 51029.41, 6, 2025),
(7, 1, 52007.50, 7, 2025),
(8, 1, 52985.59, 8, 2025),
(9, 1, 53963.68, 9, 2025);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbr_pagos`
--

CREATE TABLE `tbr_pagos` (
  `IdPago` int(11) NOT NULL,
  `IdUsuario` int(11) NOT NULL,
  `IdDesarrollo` int(11) NOT NULL,
  `Dpto` varchar(20) NOT NULL,
  `IdCliente` int(11) DEFAULT NULL,
  `m2inicial` decimal(10,2) DEFAULT NULL,
  `m2actual` decimal(10,2) DEFAULT NULL,
  `Precio_Compraventa` decimal(12,2) DEFAULT NULL,
  `Precio_Actual` decimal(12,2) DEFAULT NULL,
  `FechaPago` date DEFAULT NULL,
  `Monto` decimal(12,2) DEFAULT NULL,
  `Estatus` tinyint(1) DEFAULT NULL,
  `Concepto` varchar(120) DEFAULT NULL,
  `Notas` varchar(255) DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdateAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbr_pagos`
--

INSERT INTO `tbr_pagos` (`IdPago`, `IdUsuario`, `IdDesarrollo`, `Dpto`, `IdCliente`, `m2inicial`, `m2actual`, `Precio_Compraventa`, `Precio_Actual`, `FechaPago`, `Monto`, `Estatus`, `Concepto`, `Notas`, `CreatedAt`, `UpdateAt`, `UpdatedAt`) VALUES
(36, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2024-11-14', 20000.00, 2, 'Mensualidad 1', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:47', '2025-09-24 02:33:47'),
(37, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2024-12-14', 20000.00, 2, 'Mensualidad 2', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:50', '2025-09-24 02:33:50'),
(38, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-01-14', 20000.00, 2, 'Mensualidad 3', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:52', '2025-09-24 02:33:52'),
(39, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-02-14', 20000.00, 2, 'Mensualidad 4', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:54', '2025-09-24 02:33:54'),
(40, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-03-14', 20000.00, 2, 'Mensualidad 5', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:56', '2025-09-24 02:33:56'),
(41, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-04-14', 20000.00, 2, 'Mensualidad 6', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:59', '2025-09-24 02:33:59'),
(42, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-05-14', 20000.00, 2, 'Mensualidad 7', NULL, '2025-09-24 02:33:19', '2025-09-24 02:34:01', '2025-09-24 02:34:01'),
(43, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-06-14', 20000.00, 2, 'Mensualidad 8', NULL, '2025-09-24 02:33:19', '2025-09-24 02:34:03', '2025-09-24 02:34:03'),
(44, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-07-14', 20000.00, 2, 'Mensualidad 9', NULL, '2025-09-24 02:33:19', '2025-09-24 02:34:07', '2025-09-24 02:34:07'),
(45, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-08-14', 20000.00, 2, 'Mensualidad 10', NULL, '2025-09-24 02:33:19', '2025-09-24 02:34:09', '2025-09-24 02:34:09'),
(46, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-09-14', 20000.00, 1, 'Mensualidad 11', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(47, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-10-14', 20000.00, 1, 'Mensualidad 12', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(48, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-11-14', 20000.00, 1, 'Mensualidad 13', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(49, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-12-14', 20000.00, 1, 'Mensualidad 14', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(50, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-01-14', 20000.00, 1, 'Mensualidad 15', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(51, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-02-14', 20000.00, 1, 'Mensualidad 16', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(52, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-03-14', 20000.00, 1, 'Mensualidad 17', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(53, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-04-14', 20000.00, 1, 'Mensualidad 18', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(54, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-05-14', 20000.00, 1, 'Mensualidad 19', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(55, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-06-14', 20000.00, 1, 'Mensualidad 20', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(56, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-07-14', 20000.00, 1, 'Mensualidad 21', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(57, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-08-14', 20000.00, 1, 'Mensualidad 22', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(58, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-09-14', 20000.00, 1, 'Mensualidad 23', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(59, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-10-14', 20000.00, 1, 'Mensualidad 24', NULL, '2025-09-24 02:33:20', '2025-09-24 02:33:20', NULL),
(60, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-11-14', 20000.00, 1, 'Mensualidad 25', NULL, '2025-09-24 02:33:20', '2025-09-24 02:33:20', NULL),
(61, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-12-14', 20000.00, 1, 'Mensualidad 26', NULL, '2025-09-24 02:33:20', '2025-09-24 02:33:20', NULL),
(62, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2027-01-14', 20000.00, 1, 'Mensualidad 27', NULL, '2025-09-24 02:33:20', '2025-09-24 02:33:20', NULL),
(63, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2027-02-14', 20000.00, 1, 'Mensualidad 28', NULL, '2025-09-24 02:33:20', '2025-09-24 02:33:20', NULL),
(64, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2027-03-14', 20000.00, 1, 'Mensualidad 29', NULL, '2025-09-24 02:33:20', '2025-09-24 02:33:20', NULL),
(65, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2027-04-14', 20000.00, 1, 'Mensualidad 30', NULL, '2025-09-24 02:33:20', '2025-09-24 02:33:20', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbr_usuario_desarrollos`
--

CREATE TABLE `tbr_usuario_desarrollos` (
  `Id` int(11) NOT NULL,
  `IdUsuario` int(11) NOT NULL,
  `IdDesarrollo` int(11) NOT NULL,
  `Dpto` varchar(10) NOT NULL,
  `File_Comprobante` varchar(255) DEFAULT NULL,
  `File_Planos` varchar(255) DEFAULT NULL,
  `File_Avance_Obra` varchar(255) DEFAULT NULL,
  `M2Inicial` decimal(8,2) NOT NULL,
  `Fecha_Firma` date DEFAULT NULL,
  `Vigencia` date DEFAULT NULL,
  `Estatus` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Volcado de datos para la tabla `tbr_usuario_desarrollos`
--

INSERT INTO `tbr_usuario_desarrollos` (`Id`, `IdUsuario`, `IdDesarrollo`, `Dpto`, `File_Comprobante`, `File_Planos`, `File_Avance_Obra`, `M2Inicial`, `Fecha_Firma`, `Vigencia`, `Estatus`) VALUES
(1, 1, 1, '201', 'SP201.pdf', 'SP201P.pdf', 'SP703Estufa.pdf', 128.00, '2024-11-14', '2027-04-01', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tbc_perfiles`
--
ALTER TABLE `tbc_perfiles`
  ADD PRIMARY KEY (`IdPerfil`);

--
-- Indices de la tabla `tbp_desarrollos`
--
ALTER TABLE `tbp_desarrollos`
  ADD PRIMARY KEY (`IdDesarrollo`);

--
-- Indices de la tabla `tbp_usuarios`
--
ALTER TABLE `tbp_usuarios`
  ADD PRIMARY KEY (`IdUsuario`);

--
-- Indices de la tabla `tbr_avance_desarrollo`
--
ALTER TABLE `tbr_avance_desarrollo`
  ADD PRIMARY KEY (`IdAvance`),
  ADD UNIQUE KEY `uk_dev_cat` (`IdDesarrollo`,`Categoria`),
  ADD KEY `idx_dev` (`IdDesarrollo`);

--
-- Indices de la tabla `tbr_desarrollos_costo_mensual`
--
ALTER TABLE `tbr_desarrollos_costo_mensual`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `tbr_pagos`
--
ALTER TABLE `tbr_pagos`
  ADD PRIMARY KEY (`IdPago`),
  ADD KEY `idx_usuario_fecha` (`IdUsuario`,`FechaPago`),
  ADD KEY `idx_ud_dpto_fecha` (`IdUsuario`,`IdDesarrollo`,`Dpto`,`FechaPago`),
  ADD KEY `idx_pagos_usuario` (`IdUsuario`),
  ADD KEY `idx_pagos_desarrollo` (`IdDesarrollo`,`Dpto`),
  ADD KEY `idx_pagos_fecha` (`FechaPago`);

--
-- Indices de la tabla `tbr_usuario_desarrollos`
--
ALTER TABLE `tbr_usuario_desarrollos`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `uk_ud_unidad` (`IdUsuario`,`IdDesarrollo`,`Dpto`),
  ADD KEY `idx_ud_usuario` (`IdUsuario`),
  ADD KEY `idx_ud_desarrollo` (`IdDesarrollo`,`Dpto`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tbc_perfiles`
--
ALTER TABLE `tbc_perfiles`
  MODIFY `IdPerfil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbp_desarrollos`
--
ALTER TABLE `tbp_desarrollos`
  MODIFY `IdDesarrollo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbp_usuarios`
--
ALTER TABLE `tbp_usuarios`
  MODIFY `IdUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbr_avance_desarrollo`
--
ALTER TABLE `tbr_avance_desarrollo`
  MODIFY `IdAvance` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tbr_desarrollos_costo_mensual`
--
ALTER TABLE `tbr_desarrollos_costo_mensual`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `tbr_pagos`
--
ALTER TABLE `tbr_pagos`
  MODIFY `IdPago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT de la tabla `tbr_usuario_desarrollos`
--
ALTER TABLE `tbr_usuario_desarrollos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
