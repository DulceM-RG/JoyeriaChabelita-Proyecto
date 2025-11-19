-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-11-2025 a las 20:02:59
-- Versión del servidor: 11.5.2-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `joyeriachabelitaproy`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `idCategoria` int(11) NOT NULL,
  `nombre` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`idCategoria`, `nombre`) VALUES
(1, 'Anillo '),
(2, 'Cadena'),
(3, 'Pulso'),
(4, 'Broquel'),
(5, 'Arete Violador'),
(6, 'Arete Asa'),
(7, 'Filigrana'),
(8, 'Argolla matrimonial'),
(9, 'Reloj'),
(10, 'Dije'),
(11, 'Esclava'),
(12, 'Medalla Religiosa'),
(13, 'Gargantilla'),
(14, 'Tobillera'),
(15, 'Pulsera');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `idCliente` int(11) NOT NULL,
  `idTipoCliente` int(11) NOT NULL,
  `nombre` varchar(15) DEFAULT NULL,
  `apellidoPaterno` varchar(15) DEFAULT NULL,
  `apellidoMaterno` varchar(15) DEFAULT NULL,
  `telefono` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`idCliente`, `idTipoCliente`, `nombre`, `apellidoPaterno`, `apellidoMaterno`, `telefono`) VALUES
(1, 1, NULL, NULL, NULL, NULL),
(2, 2, 'Josefa', 'Hernandez', 'Garcia', '5551234562'),
(3, 2, 'Josefa', 'Perez', 'Martinez', '9541234563'),
(4, 2, 'Ana', 'Lopez', 'Ruiz', '2811234564'),
(5, 2, 'Luis', 'Martinez', 'Santos', '9511234565'),
(6, 2, 'Pedro', 'Jimenez', 'Guzman', '9511234566'),
(7, 2, 'Sofia', 'Castro', 'Flores', '5551234567'),
(8, 2, 'Miguel', 'Sanchez', 'Ortega', '5551234568'),
(9, 2, 'Laura', 'Vargas', 'Morales', '9711234569'),
(10, 2, 'Jorge', 'Ramirez', 'Rios', '5551234570'),
(11, 2, 'Daniel', 'Torres', 'Vega', '9541234571'),
(12, 2, 'Andrea', 'Cruz', 'Rojas', '5551234572'),
(13, 2, 'Ricardo', 'Diaz', 'Nunez', '6141234573'),
(14, 2, 'Sandra', 'Alvarez', 'Mendoza', '4491234574'),
(15, 2, 'Fernando', 'Gutierrez', 'Salinas', '5551234575'),
(35, 2, 'Dulce', 'Rodriguez', 'Garcia', '9514488557');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `credenciales`
--

CREATE TABLE `credenciales` (
  `idControl` varchar(20) NOT NULL,
  `idEmpleado` int(11) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `intentosFallidos` int(11) DEFAULT 0,
  `fechaCreacion` date DEFAULT curdate(),
  `ultimoCambio` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `activo` enum('Activo','Baja') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `credenciales`
--

INSERT INTO `credenciales` (`idControl`, `idEmpleado`, `contrasena`, `intentosFallidos`, `fechaCreacion`, `ultimoCambio`, `activo`) VALUES
('A25101313', 13, '3456', 0, '2025-10-13', '2025-10-13 11:00:00', 'Activo'),
('C25101414', 14, '4567', 0, '2025-10-14', '2025-10-14 11:10:00', 'Activo'),
('G25100101', 1, '1234', 0, '2025-10-01', '2025-10-01 09:00:00', 'Activo'),
('V25100202', 2, '$2y$10$SMWuB0bbA60D8eAzlgeovuxW4eGqoEy7MdTskzhrkNi5KLqslVtg6', 0, '2025-10-02', '2025-11-11 19:40:11', 'Activo'),
('V25100303', 3, '3456', 0, '2025-10-03', '2025-10-03 09:20:00', 'Activo'),
('V25100404', 4, '4567', 0, '2025-10-04', '2025-10-04 09:30:00', 'Activo'),
('V25100505', 5, '5678', 0, '2025-10-05', '2025-10-05 09:40:00', 'Activo'),
('V25100606', 6, '6789', 0, '2025-10-06', '2025-10-06 09:50:00', 'Activo'),
('V25100707', 7, '7890', 0, '2025-10-07', '2025-10-07 10:00:00', 'Activo'),
('V25100808', 8, '8901', 0, '2025-10-08', '2025-10-08 10:10:00', 'Activo'),
('V25100909', 9, '9012', 0, '2025-10-09', '2025-10-09 10:20:00', 'Activo'),
('V25101010', 10, '0123', 0, '2025-10-10', '2025-10-10 10:30:00', 'Activo'),
('V25101111', 11, '1234', 0, '2025-10-11', '2025-10-11 10:40:00', 'Activo'),
('V25101212', 12, '2345', 0, '2025-10-12', '2025-10-12 10:50:00', 'Activo'),
('V25101515', 15, '5678', 0, '2025-10-15', '2025-10-15 11:20:00', 'Activo'),
('V25102116', 16, '$2y$10$J.x8zauS2gXhh6ixARCh8.T9MCUFKRb7NNHZbAw6ODjWCPIgSzBrG', 0, '2025-10-21', '2025-10-21 04:50:34', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `idDireccion` int(11) NOT NULL,
  `nombreCalle` varchar(100) NOT NULL,
  `numeroCalle` int(11) NOT NULL,
  `localidad` varchar(100) NOT NULL,
  `codigoPostal` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `direccion`
--

INSERT INTO `direccion` (`idDireccion`, `nombreCalle`, `numeroCalle`, `localidad`, `codigoPostal`) VALUES
(1, 'Calle de Los Libres', 123, 'Centro', NULL),
(2, 'Calle Macedonio Alcalá', 456, 'Centro Histórico', NULL),
(3, 'Calle García Vigil', 789, 'Centro', NULL),
(4, 'Avenida Universidad', 101, 'Ex-Hacienda Candiani', NULL),
(5, 'Calle Manuel Sabino Crespo', 202, 'Reforma', NULL),
(6, 'Avenida Eduardo Mata', 303, 'Del Maestro', NULL),
(7, 'Calle Porfirio Díaz', 404, 'Centro', NULL),
(8, 'Calle de la Noria', 505, 'Centro', NULL),
(9, 'Boulevard Eduardo Vasconcelos', 606, 'Figueroa', NULL),
(10, 'Calle Hidalgo', 707, 'San Felipe del Agua', NULL),
(11, 'Avenida Juárez', 808, 'Centro', NULL),
(12, 'Calle Constitución', 909, 'Centro', NULL),
(13, 'Calle Independencia', 111, 'Centro', NULL),
(14, 'Prolongación de Benito Juárez', 222, 'Xochimilco', NULL),
(15, 'Calle de Madero', 333, 'Centro Histórico', NULL),
(16, 'Reforma', 7, 'Cuilapam', '71406');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

CREATE TABLE `empleado` (
  `idEmpleado` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `apellidoPaterno` varchar(30) NOT NULL,
  `apellidoMaterno` varchar(30) NOT NULL,
  `telefono` bigint(10) NOT NULL,
  `idPuesto` int(11) NOT NULL,
  `idDireccion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `empleado`
--

INSERT INTO `empleado` (`idEmpleado`, `nombre`, `apellidoPaterno`, `apellidoMaterno`, `telefono`, `idPuesto`, `idDireccion`) VALUES
(1, 'Carlos', 'Gomez', 'Lopez', 9511234560, 1, 1),
(2, 'Maria', 'Hernandez', 'Garcia', 9511234562, 2, 2),
(3, 'Juan', 'Perez', 'Martinez', 9541234563, 2, 3),
(4, 'Ana', 'Lopez', 'Ruiz', 2811234564, 2, 4),
(5, 'Luis', 'Martinez', 'Santos', 9511234565, 2, 5),
(6, 'Pedro', 'Jimenez', 'Guzman', 9511234566, 2, 6),
(7, 'Sofia', 'Castro', 'Flores', 9511234567, 2, 7),
(8, 'Miguel', 'Sanchez', 'Ortega', 9511234568, 2, 8),
(9, 'Laura', 'Vargas', 'Morales', 9511234569, 2, 9),
(10, 'Jorge', 'Ramirez', 'Rios', 9511234570, 2, 10),
(11, 'Daniel', 'Torres', 'Vega', 9511234571, 2, 11),
(12, 'Andrea', 'Cruz', 'Rojas', 9711234572, 2, 12),
(13, 'Ricardo', 'Diaz', 'Nunez', 9511234573, 3, 13),
(14, 'Sandra', 'Alvarez', 'Mendoza', 9511234574, 4, 14),
(15, 'Fernando', 'Gutierrez', 'Salinas', 9511234575, 2, 15),
(16, 'Dulce María', 'Rodríguez', 'García', 9514488557, 2, 16);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingreso`
--

CREATE TABLE `ingreso` (
  `idIngreso` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `importeTotal` decimal(9,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `ingreso`
--

INSERT INTO `ingreso` (`idIngreso`, `fecha`, `importeTotal`) VALUES
(101, '2024-11-01', 15000.50),
(102, '2024-11-02', 25000.00),
(103, '2024-11-03', 18000.75),
(104, '2025-11-12', 68250.00),
(105, '2025-11-17', 10500.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `folio` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `montoTotal` decimal(10,2) DEFAULT NULL,
  `idEmpleado` int(11) DEFAULT NULL,
  `rfc` varchar(13) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`folio`, `fecha`, `montoTotal`, `idEmpleado`, `rfc`) VALUES
(1, '2024-11-01', 5000.00, 1, 'JY0001234A12'),
(2, '2024-11-02', 3500.00, 2, 'JY0001235B13'),
(3, '2024-11-03', 12000.00, 3, 'JY0001236C14'),
(4, '2024-11-04', 7500.50, 4, 'JY0001237D15'),
(5, '2024-11-05', 9200.75, 5, 'JY0001238E16'),
(6, '2024-11-06', 4100.00, 6, 'JY0001239F17'),
(7, '2024-11-07', 6700.30, 7, 'JY0001240G18'),
(8, '2024-11-08', 18000.00, 8, 'JY0001241H19'),
(9, '2024-11-09', 2900.10, 9, 'JY0001242I20'),
(10, '2024-11-10', 15000.25, 10, 'JY0001243J21'),
(11, '2024-11-11', 5600.50, 11, 'JY0001244K22'),
(12, '2024-11-12', 3300.00, 12, 'JY0001245L23'),
(13, '2024-11-13', 4500.90, 13, 'JY0001246M24'),
(14, '2024-11-14', 11200.50, 14, 'JY0001247N25'),
(15, '2024-11-15', 2300.00, 15, 'JY0001248O26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `idProducto` varchar(20) NOT NULL,
  `idCategoria` int(11) DEFAULT NULL,
  `stock` int(11) NOT NULL,
  `kilataje` enum('10K','14K') DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `precioUnitario` decimal(8,2) DEFAULT NULL,
  `gramos` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`idProducto`, `idCategoria`, `stock`, `kilataje`, `descripcion`, `precioUnitario`, `gramos`) VALUES
('P001', 1, 35, '14K', 'Anillo solitario de compromiso', 5250.00, 5.25),
('P002', 2, 30, '10K', 'Cadena cubana diamantada en oro blanco', 15750.00, 15.75),
('P003', 3, 20, '14K', 'Pulso torsal', 12600.00, 6.30),
('P004', 4, 10, '10K', 'Dormilonas', 1000.00, 1.00),
('P005', 5, 25, '14K', 'Arete violador en forma de flor', 9000.00, 4.50),
('P006', 6, 18, '14K', 'Arete asa mariposa florentino', 12000.00, 6.00),
('P007', 7, 40, '10K', 'Filigrana oaxaqueña artesanal', 7100.00, 7.10),
('P008', 8, 35, '14K', 'Argolla matrimonial clásica', 16000.25, 8.00),
('P009', 9, 12, '14K', 'Reloj oro florentino de 14K', 73600.00, 36.80),
('P010', 10, 8, '14K', 'Dije con diseño corazon', 5000.00, 2.50),
('P011', 11, 15, '10K', 'Esclava tejido chino', 6500.00, 6.50),
('P012', 12, 22, '14K', 'Medalla virgen guadalupe con zirconia', 17000.00, 8.50),
('P013', 13, 5, '10K', 'Gargantilla de oro con detalles florales', 12750.00, 12.75),
('P014', 14, 7, '14K', 'Tobillera de oro con piedras pequeñas', 11200.00, 5.25),
('P015', 15, 10, '10K', 'Pulsera con diseño minimalista', 6800.00, 6.90);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productopedido`
--

CREATE TABLE `productopedido` (
  `idProductoPedido` bigint(20) NOT NULL,
  `idProducto` varchar(20) DEFAULT NULL,
  `folio` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `costoUnitario` decimal(8,2) DEFAULT NULL,
  `importe` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `productopedido`
--

INSERT INTO `productopedido` (`idProductoPedido`, `idProducto`, `folio`, `cantidad`, `costoUnitario`, `importe`) VALUES
(1, 'P001', 1, 3, 5250.00, 15750.00),
(2, 'P002', 1, 2, 15750.00, 31500.00),
(3, 'P003', 2, 1, 12600.00, 12600.00),
(4, 'P004', 2, 6, 1000.00, 6000.00),
(5, 'P005', 3, 2, 9000.00, 18000.00),
(6, 'P006', 3, 1, 12000.00, 12000.00),
(7, 'P007', 4, 4, 7100.00, 28400.00),
(8, 'P008', 4, 2, 16000.25, 32000.50),
(9, 'P009', 5, 1, 73600.00, 73600.00),
(10, 'P010', 5, 5, 5000.00, 25000.00),
(11, 'P011', 6, 3, 6500.00, 19500.00),
(12, 'P012', 6, 1, 17000.00, 17000.00),
(13, 'P013', 7, 2, 12750.00, 25500.00),
(14, 'P014', 7, 1, 11200.00, 11200.00),
(15, 'P015', 8, 4, 6800.00, 27200.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productoventa`
--

CREATE TABLE `productoventa` (
  `idProductoVenta` bigint(20) NOT NULL,
  `idVenta` int(11) DEFAULT NULL,
  `idProducto` varchar(20) DEFAULT NULL,
  `costo` decimal(8,2) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `importe` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `productoventa`
--

INSERT INTO `productoventa` (`idProductoVenta`, `idVenta`, `idProducto`, `costo`, `cantidad`, `importe`) VALUES
(1, 1, 'P001', 5250.00, 2, 10500.00),
(2, 1, 'P002', 15750.00, 1, 15750.00),
(3, 2, 'P003', 12600.00, 3, 37800.00),
(4, 2, 'P004', 1000.00, 5, 5000.00),
(5, 3, 'P005', 9000.00, 2, 18000.00),
(6, 3, 'P006', 12000.00, 1, 12000.00),
(7, 4, 'P007', 7100.00, 4, 28400.00),
(8, 4, 'P008', 16000.25, 1, 16000.25),
(9, 5, 'P009', 73600.00, 1, 73600.00),
(10, 5, 'P010', 5000.00, 3, 15000.00),
(11, 6, 'P011', 6500.00, 2, 13000.00),
(12, 6, 'P012', 17000.00, 1, 17000.00),
(13, 7, 'P013', 12750.00, 1, 12750.00),
(14, 7, 'P014', 11200.00, 2, 22400.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `rfc` varchar(13) NOT NULL,
  `razonSocial` varchar(100) NOT NULL,
  `telefono` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`rfc`, `razonSocial`, `telefono`) VALUES
('JY0001234A12', 'Joyeros del Oro S.A. de C.V.', 9511234561),
('JY0001235B13', 'Gemas y Minerales Oaxaca S.A.', 9511234562),
('JY0001236C14', 'Filigranas Oaxaqueñas S.A.', 9511234563),
('JY0001237D15', 'Oro y Plata de la Mixteca S.A.', 9511234564),
('JY0001238E16', 'Joyas del Valle Oaxaqueño S.A.', 9511234565),
('JY0001239F17', 'Diseños Artesanales S.A. de C.V.', 9511234566),
('JY0001240G18', 'Alhajas del Sol S.A. de C.V.', 9511234567),
('JY0001241H19', 'Oaxaqueña de Joyeros S.A.', 9511234568),
('JY0001242I20', 'Arte en Filigrana S.A.', 9511234569),
('JY0001243J21', 'Joyería Colonial S.A.', 9511234570),
('JY0001244K22', 'Oro Puro de Oaxaca S.A.', 9511234571),
('JY0001245L23', 'Brillante Oaxaca S.A.', 9511234572),
('JY0001246M24', 'El Tesoro del Oro S.A.', 9511234573),
('JY0001247N25', 'Joyas del Edén S.A. de C.V.', 9511234574),
('JY0001248O26', 'Gemas y Joyas de la Sierra S.A.', 9511234575);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puestoempleado`
--

CREATE TABLE `puestoempleado` (
  `idPuesto` int(11) NOT NULL,
  `puesto` varchar(50) NOT NULL,
  `sueldo` decimal(7,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `puestoempleado`
--

INSERT INTO `puestoempleado` (`idPuesto`, `puesto`, `sueldo`) VALUES
(1, 'Gerente', 5000.00),
(2, 'Venta', 4000.00),
(3, 'Almacén', 8000.00),
(4, 'Contador', 10000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipocliente`
--

CREATE TABLE `tipocliente` (
  `idTipoCliente` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `tipocliente`
--

INSERT INTO `tipocliente` (`idTipoCliente`, `tipo`) VALUES
(1, 'Publico'),
(2, 'Mayorista');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta`
--

CREATE TABLE `venta` (
  `idVenta` int(11) NOT NULL,
  `fechaVenta` date DEFAULT NULL,
  `idEmpleado` int(11) DEFAULT NULL,
  `idCliente` int(11) DEFAULT NULL,
  `IdIngreso` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `venta`
--

INSERT INTO `venta` (`idVenta`, `fechaVenta`, `idEmpleado`, `idCliente`, `IdIngreso`) VALUES
(1, '2024-11-01', 2, 1, 101),
(2, '2024-11-01', 2, 2, 101),
(3, '2024-11-01', 3, 3, 101),
(4, '2024-11-02', 4, 1, 102),
(5, '2024-11-02', 5, 1, 102),
(6, '2024-11-02', 2, 1, 102),
(7, '2024-11-03', 6, 8, 103),
(8, '2024-11-03', 6, 4, 103),
(9, '2024-11-03', 7, 1, 103),
(10, '2025-11-12', 2, 1, 104),
(11, '2025-11-12', 2, 1, 104),
(12, '2025-11-12', 2, 1, 104),
(13, '2025-11-12', 2, 1, 104),
(14, '2025-11-12', 2, 1, 104),
(15, '2025-11-12', 2, 1, 104),
(16, '2025-11-12', 2, 1, 104),
(17, '2025-11-12', 2, 1, 104),
(18, '2025-11-12', 2, 1, 104),
(19, '2025-11-12', 2, 1, 104),
(20, '2025-11-12', 2, 1, 104),
(21, '2025-11-12', 2, 1, 104),
(22, '2025-11-12', 2, 1, 104),
(23, '2025-11-17', 2, 1, 105),
(24, '2025-11-17', 2, 1, 105);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`idCategoria`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idCliente`),
  ADD UNIQUE KEY `telefono` (`telefono`),
  ADD KEY `fkTipoCliente` (`idTipoCliente`);

--
-- Indices de la tabla `credenciales`
--
ALTER TABLE `credenciales`
  ADD PRIMARY KEY (`idControl`),
  ADD KEY `idEmpleado` (`idEmpleado`);

--
-- Indices de la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`idDireccion`);

--
-- Indices de la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD PRIMARY KEY (`idEmpleado`),
  ADD UNIQUE KEY `telefono` (`telefono`),
  ADD KEY `fkPuestoEmpleado` (`idPuesto`),
  ADD KEY `fkDireccion` (`idDireccion`);

--
-- Indices de la tabla `ingreso`
--
ALTER TABLE `ingreso`
  ADD PRIMARY KEY (`idIngreso`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`folio`),
  ADD KEY `fkEmpleado` (`idEmpleado`),
  ADD KEY `fkProveedor` (`rfc`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`idProducto`),
  ADD KEY `fkCategoria` (`idCategoria`);

--
-- Indices de la tabla `productopedido`
--
ALTER TABLE `productopedido`
  ADD PRIMARY KEY (`idProductoPedido`),
  ADD KEY `fkProductos` (`idProducto`),
  ADD KEY `fkPedido` (`folio`);

--
-- Indices de la tabla `productoventa`
--
ALTER TABLE `productoventa`
  ADD PRIMARY KEY (`idProductoVenta`),
  ADD KEY `fkVenta` (`idVenta`),
  ADD KEY `fkProducto` (`idProducto`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`rfc`),
  ADD UNIQUE KEY `telefono` (`telefono`),
  ADD UNIQUE KEY `telefono_2` (`telefono`);

--
-- Indices de la tabla `puestoempleado`
--
ALTER TABLE `puestoempleado`
  ADD PRIMARY KEY (`idPuesto`);

--
-- Indices de la tabla `tipocliente`
--
ALTER TABLE `tipocliente`
  ADD PRIMARY KEY (`idTipoCliente`);

--
-- Indices de la tabla `venta`
--
ALTER TABLE `venta`
  ADD PRIMARY KEY (`idVenta`),
  ADD KEY `fkEmpleadoPedido` (`idEmpleado`),
  ADD KEY `fkCliente` (`idCliente`),
  ADD KEY `fkIngreso` (`IdIngreso`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `idCategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idCliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `idDireccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `idEmpleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `ingreso`
--
ALTER TABLE `ingreso`
  MODIFY `idIngreso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `folio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `productopedido`
--
ALTER TABLE `productopedido`
  MODIFY `idProductoPedido` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `productoventa`
--
ALTER TABLE `productoventa`
  MODIFY `idProductoVenta` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `puestoempleado`
--
ALTER TABLE `puestoempleado`
  MODIFY `idPuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tipocliente`
--
ALTER TABLE `tipocliente`
  MODIFY `idTipoCliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `venta`
--
ALTER TABLE `venta`
  MODIFY `idVenta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `fkTipoCliente` FOREIGN KEY (`idTipoCliente`) REFERENCES `tipocliente` (`idTipoCliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `credenciales`
--
ALTER TABLE `credenciales`
  ADD CONSTRAINT `credenciales_ibfk_1` FOREIGN KEY (`idEmpleado`) REFERENCES `empleado` (`idEmpleado`);

--
-- Filtros para la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD CONSTRAINT `fkDireccion` FOREIGN KEY (`idDireccion`) REFERENCES `direccion` (`idDireccion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkPuestoEmpleado` FOREIGN KEY (`idPuesto`) REFERENCES `puestoempleado` (`idPuesto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `fkEmpleado` FOREIGN KEY (`idEmpleado`) REFERENCES `empleado` (`idEmpleado`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkProveedor` FOREIGN KEY (`rfc`) REFERENCES `proveedor` (`rfc`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `fkCategoria` FOREIGN KEY (`idCategoria`) REFERENCES `categoria` (`idCategoria`);

--
-- Filtros para la tabla `productopedido`
--
ALTER TABLE `productopedido`
  ADD CONSTRAINT `fkPedido` FOREIGN KEY (`folio`) REFERENCES `pedido` (`folio`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkProductos` FOREIGN KEY (`idProducto`) REFERENCES `producto` (`idProducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `productoventa`
--
ALTER TABLE `productoventa`
  ADD CONSTRAINT `fkProducto` FOREIGN KEY (`idProducto`) REFERENCES `producto` (`idProducto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkVenta` FOREIGN KEY (`idVenta`) REFERENCES `venta` (`idVenta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `venta`
--
ALTER TABLE `venta`
  ADD CONSTRAINT `fkCliente` FOREIGN KEY (`idCliente`) REFERENCES `cliente` (`idCliente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkEmpleadoPedido` FOREIGN KEY (`idEmpleado`) REFERENCES `empleado` (`idEmpleado`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkIngreso` FOREIGN KEY (`IdIngreso`) REFERENCES `ingreso` (`idIngreso`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
