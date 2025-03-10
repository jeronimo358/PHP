-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 09-03-2025 a las 19:04:30
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12
DROP DATABASE hoteldb;
CREATE DATABASE hoteldb;
USE hoteldb;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `hoteldb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitaciones`
--

CREATE TABLE `habitaciones` (
  `ID_habitaciones` int(11) NOT NULL,
  `Tipo` varchar(50) DEFAULT NULL,
  `Precio_noche` decimal(10,2) DEFAULT NULL,
  `Estado` enum('Disponible','Ocupada','Reservada') DEFAULT NULL,
  `Numero_camas` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitaciones`
--

INSERT INTO `habitaciones` (`ID_habitaciones`, `Tipo`, `Precio_noche`, `Estado`, `Numero_camas`) VALUES
(2, 'Doble', 90.00, 'Disponible', 2),
(3, 'Doble', 85.00, 'Disponible', 2),
(4, 'Triple', 120.00, 'Disponible', 3),
(5, 'Suite', 200.00, 'Disponible', 2),
(6, 'Suite', 180.00, 'Disponible', 2),
(9, 'Doble', 95.00, 'Disponible', 2),
(10, 'Suite', 250.00, 'Disponible', 2),
(11, 'Familiar', 120.00, 'Disponible', 4),
(12, 'Triple', 135.00, 'Disponible', 3),
(13, 'Familiar', 135.00, 'Disponible', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `ID_reservas` int(11) NOT NULL,
  `ID_usuarios` int(11) NOT NULL,
  `ID_habitaciones` int(11) NOT NULL,
  `Fecha_check_in` date DEFAULT NULL,
  `Fecha_check_out` date DEFAULT NULL,
  `Estado_reserva` enum('Confirmada','Cancelada','Pendiente') DEFAULT NULL,
  `Total_reserva` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `ID_servicios` int(11) NOT NULL,
  `Nombre` varchar(100) DEFAULT NULL,
  `Descripcion` text DEFAULT NULL,
  `Precio` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`ID_servicios`, `Nombre`, `Descripcion`, `Precio`) VALUES
(1, 'Wi-Fi', 'Conexion a internet en todo el hotel', 5.00),
(2, 'Desayuno', 'Buffet libre para el desayuno', 12.00),
(3, 'Spa', 'Masajes y tratamientos de relajacion', 60.00),
(4, 'Gimnasio', 'Acceso al gimnasio durante la estancia', 15.00),
(5, 'Cena especial', 'Menu gourmet para la cena', 30.00),
(6, 'Parking', 'Estacionamiento en el hotel', 10.00),
(7, 'Traslado al aeropuerto', 'Servicio de transporte privado al aeropuerto', 40.00),
(8, 'Padel', '1 hora de padel con monitor', 15.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios_reservas`
--

CREATE TABLE `servicios_reservas` (
  `ID_servicio_reserva` int(11) NOT NULL,
  `ID_reservas` int(11) DEFAULT NULL,
  `ID_servicios` int(11) DEFAULT NULL,
  `Cantidad` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajadores`
--

CREATE TABLE `trabajadores` (
  `ID_trabajador` int(11) NOT NULL,
  `Nombre` varchar(100) DEFAULT NULL,
  `Apellido` varchar(100) DEFAULT NULL,
  `Cargo` varchar(100) DEFAULT NULL,
  `Telefono` varchar(15) DEFAULT NULL,
  `Fecha_ingreso` date DEFAULT NULL,
  `Salario` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `trabajadores`
--

INSERT INTO `trabajadores` (`ID_trabajador`, `Nombre`, `Apellido`, `Cargo`, `Telefono`, `Fecha_ingreso`, `Salario`) VALUES
(1, 'Carlos', 'Gomez', 'Recepcionista', '5551234567', '2023-01-15', 1200.00),
(2, 'Ana', 'Martinez', 'Camarera', '5559876543', '2024-02-10', 900.00),
(5, 'Sofia', 'Castro', 'Gerente', '5553142569', '2020-11-30', 2200.00),
(6, 'Luis', 'Perez', 'Camarero', '5559871254', '2024-01-18', 950.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `ID_usuarios` int(11) NOT NULL,
  `Nombre` varchar(100) DEFAULT NULL,
  `Apellido` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Telefono` varchar(15) DEFAULT NULL,
  `Direccion` varchar(255) DEFAULT NULL,
  `Admin` enum('Si','No') DEFAULT 'No',
  `Contrasena` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`ID_usuarios`, `Nombre`, `Apellido`, `Email`, `Telefono`, `Direccion`, `Admin`, `Contrasena`) VALUES
(11, 'prueba', '1', 'prueba@gmail.com', '777444111', 'calle francia nº7', 'No', '$2y$10$QFxT.OHFGNZKKcRorK5PVOhm280Eqbbj8lNYJrAoBIcbwqIgfomD.'),
(12, 'joel', 'flores', 'joel@gmail.com', '456987452', 'calle Alemania nº3', 'No', '$2y$10$S0k2fkWyGHvketNPsj58k.BW0epJbeJZejBOpguZsECYj3ozreM9u'),
(13, 'Francisco', 'Rodriguez', 'Francisco@gmail.com', '999666558', 'calle murillo nº4', 'No', '$2y$10$/Y9jakCd7Hau..JBy8rYqOSFR3G6DLOycUQSS8awUThq0ryaatXDO'),
(14, 'Manuel', 'Pigne', 'manuel@gmail.com', '852369741', 'Calle Italia n5', 'No', '$2y$10$m87I5QMzmj8TroQ9lktFleJCK/z5mp.8kSImFUkBOWL2l0gh3wlPa'),
(15, 'Jeronimo', 'Racero', 'jeronimo@gmail.com', '654654654', 'Calle Nuevas Poblciones n9', 'Si', '$2y$10$Z8OUvtypgPdX.im.QrvSeu8DBdSgLZBcs6df8HKPpbb3lb.FL353W');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD PRIMARY KEY (`ID_habitaciones`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`ID_reservas`),
  ADD KEY `reservas_ibfk_1` (`ID_usuarios`),
  ADD KEY `reservas_ibfk_2` (`ID_habitaciones`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`ID_servicios`);

--
-- Indices de la tabla `servicios_reservas`
--
ALTER TABLE `servicios_reservas`
  ADD PRIMARY KEY (`ID_servicio_reserva`),
  ADD KEY `ID_reserva` (`ID_reservas`),
  ADD KEY `ID_servicio` (`ID_servicios`);

--
-- Indices de la tabla `trabajadores`
--
ALTER TABLE `trabajadores`
  ADD PRIMARY KEY (`ID_trabajador`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`ID_usuarios`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `ID_habitaciones` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `ID_reservas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `ID_servicios` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `servicios_reservas`
--
ALTER TABLE `servicios_reservas`
  MODIFY `ID_servicio_reserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `trabajadores`
--
ALTER TABLE `trabajadores`
  MODIFY `ID_trabajador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `ID_usuarios` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`ID_usuarios`) REFERENCES `usuarios` (`ID_usuarios`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`ID_habitaciones`) REFERENCES `habitaciones` (`ID_habitaciones`) ON DELETE CASCADE;

--
-- Filtros para la tabla `servicios_reservas`
--
ALTER TABLE `servicios_reservas`
  ADD CONSTRAINT `servicios_reservas_ibfk_1` FOREIGN KEY (`ID_reservas`) REFERENCES `reservas` (`ID_reservas`) ON DELETE CASCADE,
  ADD CONSTRAINT `servicios_reservas_ibfk_2` FOREIGN KEY (`ID_servicios`) REFERENCES `servicios` (`ID_servicios`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
