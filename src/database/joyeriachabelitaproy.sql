/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.5.2-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: joyeriachabelitadb
-- ------------------------------------------------------
-- Server version	11.5.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `categoria`
--

DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categoria` (
  `idCategoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(25) NOT NULL,
  PRIMARY KEY (`idCategoria`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categoria`
--

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
INSERT INTO `categoria` VALUES
(1,'Anillo '),
(2,'Cadena'),
(3,'Pulso'),
(4,'Broquel'),
(5,'Arete Violador'),
(6,'Arete Asa'),
(7,'Filigrana'),
(8,'Argolla matrimonial'),
(9,'Reloj'),
(10,'Dije'),
(11,'Esclava'),
(12,'Medalla Religiosa'),
(13,'Gargantilla'),
(14,'Tobillera'),
(15,'Pulsera');
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cliente`
--

DROP TABLE IF EXISTS `cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cliente` (
  `idCliente` int(11) NOT NULL AUTO_INCREMENT,
  `idTipoCliente` int(11) NOT NULL,
  `nombre` varchar(15) DEFAULT NULL,
  `apellidoPaterno` varchar(10) DEFAULT NULL,
  `apellidoMaterno` varchar(10) DEFAULT NULL,
  `telefono` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`idCliente`),
  UNIQUE KEY `telefono` (`telefono`),
  KEY `fkTipoCliente` (`idTipoCliente`),
  CONSTRAINT `fkTipoCliente` FOREIGN KEY (`idTipoCliente`) REFERENCES `tipocliente` (`idTipoCliente`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente`
--

LOCK TABLES `cliente` WRITE;
/*!40000 ALTER TABLE `cliente` DISABLE KEYS */;
INSERT INTO `cliente` VALUES
(1,1,NULL,NULL,NULL,NULL),
(2,2,'Maria','Hernandez','Garcia','5551234562'),
(3,2,'Juan','Perez','Martinez','9541234563'),
(4,2,'Ana','Lopez','Ruiz','2811234564'),
(5,2,'Luis','Martinez','Santos','9511234565'),
(6,2,'Pedro','Jimenez','Guzman','9511234566'),
(7,2,'Sofia','Castro','Flores','5551234567'),
(8,2,'Miguel','Sanchez','Ortega','5551234568'),
(9,2,'Laura','Vargas','Morales','9711234569'),
(10,2,'Jorge','Ramirez','Rios','5551234570'),
(11,2,'Daniel','Torres','Vega','9541234571'),
(12,2,'Andrea','Cruz','Rojas','5551234572'),
(13,2,'Ricardo','Diaz','Nunez','6141234573'),
(14,2,'Sandra','Alvarez','Mendoza','4491234574'),
(15,2,'Fernando','Gutierrez','Salinas','5551234575');
/*!40000 ALTER TABLE `cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credenciales`
--

DROP TABLE IF EXISTS `credenciales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credenciales` (
  `idControl` varchar(20) NOT NULL,
  `idEmpleado` int(11) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `intentosFallidos` int(11) DEFAULT 0,
  `fechaCreacion` date DEFAULT (current_date()),
  `ultimoCambio` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `activo` ENUM('Activo','Baja') DEFAULT 'Activo',
  PRIMARY KEY (`idControl`),
  KEY `idEmpleado` (`idEmpleado`),
  CONSTRAINT `credenciales_ibfk_1` FOREIGN KEY (`idEmpleado`) REFERENCES `empleado` (`idEmpleado`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credenciales`
--

LOCK TABLES `credenciales` WRITE;

INSERT INTO `credenciales` 
(`idControl`, `idEmpleado`, `contrasena`, `intentosFallidos`, `fechaCreacion`, `ultimoCambio`, `activo`) VALUES
('G25100101', 1, '1234', 0, '2025-10-01', '2025-10-01 09:00:00', 'Activo'),
('V25100202', 2, '2345', 0, '2025-10-02', '2025-10-02 09:10:00', 'Activo'),
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
('A25101313', 13, '3456', 0, '2025-10-13', '2025-10-13 11:00:00', 'Activo'),
('C25101414', 14, '4567', 0, '2025-10-14', '2025-10-14 11:10:00', 'Activo'),
('V25101515', 15, '5678', 0, '2025-10-15', '2025-10-15 11:20:00', 'Activo');

UNLOCK TABLES;

--
-- Table structure for table `direccion`
--

DROP TABLE IF EXISTS `direccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `direccion` (
  `idDireccion` int(11) NOT NULL AUTO_INCREMENT,
  `nombreCalle` varchar(100) NOT NULL,
  `numeroCalle` int(11) NOT NULL,
  `localidad` varchar(100) NOT NULL,
  `codigoPostal` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`idDireccion`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direccion`
--

LOCK TABLES `direccion` WRITE;
/*!40000 ALTER TABLE `direccion` DISABLE KEYS */;
INSERT INTO `direccion` VALUES
(1,'Calle de Los Libres',123,'Centro',NULL),
(2,'Calle Macedonio AlcalÃ¡',456,'Centro HistÃ³rico',NULL),
(3,'Calle GarcÃ­a Vigil',789,'Centro',NULL),
(4,'Avenida Universidad',101,'Ex-Hacienda Candiani',NULL),
(5,'Calle Manuel Sabino Crespo',202,'Reforma',NULL),
(6,'Avenida Eduardo Mata',303,'Del Maestro',NULL),
(7,'Calle Porfirio DÃ­az',404,'Centro',NULL),
(8,'Calle de la Noria',505,'Centro',NULL),
(9,'Boulevard Eduardo Vasconcelos',606,'Figueroa',NULL),
(10,'Calle Hidalgo',707,'San Felipe del Agua',NULL),
(11,'Avenida JuÃ¡rez',808,'Centro',NULL),
(12,'Calle ConstituciÃ³n',909,'Centro',NULL),
(13,'Calle Independencia',111,'Centro',NULL),
(14,'ProlongaciÃ³n de Benito JuÃ¡rez',222,'Xochimilco',NULL),
(15,'Calle de Madero',333,'Centro HistÃ³rico',NULL);
/*!40000 ALTER TABLE `direccion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleado`
--

DROP TABLE IF EXISTS `empleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `empleado` (
  `idEmpleado` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) NOT NULL,
  `apellidoPaterno` varchar(30) NOT NULL,
  `apellidoMaterno` varchar(30) NOT NULL,
  `telefono` bigint(10) NOT NULL,
  `idPuesto` int(11) NOT NULL,
  `idDireccion` int(11) NOT NULL,
  PRIMARY KEY (`idEmpleado`),
  UNIQUE KEY `telefono` (`telefono`),
  
  KEY `fkPuestoEmpleado` (`idPuesto`),
  KEY `fkDireccion` (`idDireccion`),
  CONSTRAINT `fkDireccion` FOREIGN KEY (`idDireccion`) REFERENCES `direccion` (`idDireccion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkPuestoEmpleado` FOREIGN KEY (`idPuesto`) REFERENCES `puestoempleado` (`idPuesto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleado`
--

LOCK TABLES `empleado` WRITE;
/*!40000 ALTER TABLE `empleado` DISABLE KEYS */;
INSERT INTO `empleado` VALUES
(1,'Carlos','Gomez','Lopez',9511234560,1,1),
(2,'Maria','Hernandez','Garcia',9511234562,2,2),
(3,'Juan','Perez','Martinez',9541234563,2,3),
(4,'Ana','Lopez','Ruiz',2811234564,2,4),
(5,'Luis','Martinez','Santos',9511234565,2,5),
(6,'Pedro','Jimenez','Guzman',9511234566,2,6),
(7,'Sofia','Castro','Flores',9511234567,2,7),
(8,'Miguel','Sanchez','Ortega',9511234568,2,8),
(9,'Laura','Vargas','Morales',9511234569,2,9),
(10,'Jorge','Ramirez','Rios',9511234570,2,10),
(11,'Daniel','Torres','Vega',9511234571,2,11),
(12,'Andrea','Cruz','Rojas',9711234572,2,12),
(13,'Ricardo','Diaz','Nunez',9511234573,3,13),
(14,'Sandra','Alvarez','Mendoza',9511234574,4,14),
(15,'Fernando','Gutierrez','Salinas',9511234575,2,15);
/*!40000 ALTER TABLE `empleado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingreso`
--

DROP TABLE IF EXISTS `ingreso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ingreso` (
  `idIngreso` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `importeTotal` decimal(9,2) NOT NULL,
  PRIMARY KEY (`idIngreso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingreso`
--

LOCK TABLES `ingreso` WRITE;
/*!40000 ALTER TABLE `ingreso` DISABLE KEYS */;
INSERT INTO `ingreso` VALUES
(101,'2024-11-01',15000.50),
(102,'2024-11-02',25000.00),
(103,'2024-11-03',18000.75);
/*!40000 ALTER TABLE `ingreso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedido`
--

DROP TABLE IF EXISTS `pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedido` (
  `folio` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `montoTotal` decimal(10,2) DEFAULT NULL,
  `idEmpleado` int(11) DEFAULT NULL,
  `rfc` varchar(13) DEFAULT NULL,
  PRIMARY KEY (`folio`),
  KEY `fkEmpleado` (`idEmpleado`),
  KEY `fkProveedor` (`rfc`),
  CONSTRAINT `fkEmpleado` FOREIGN KEY (`idEmpleado`) REFERENCES `empleado` (`idEmpleado`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkProveedor` FOREIGN KEY (`rfc`) REFERENCES `proveedor` (`rfc`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedido`
--

LOCK TABLES `pedido` WRITE;
/*!40000 ALTER TABLE `pedido` DISABLE KEYS */;
INSERT INTO `pedido` VALUES
(1,'2024-11-01',5000.00,1,'JY0001234A12'),
(2,'2024-11-02',3500.00,2,'JY0001235B13'),
(3,'2024-11-03',12000.00,3,'JY0001236C14'),
(4,'2024-11-04',7500.50,4,'JY0001237D15'),
(5,'2024-11-05',9200.75,5,'JY0001238E16'),
(6,'2024-11-06',4100.00,6,'JY0001239F17'),
(7,'2024-11-07',6700.30,7,'JY0001240G18'),
(8,'2024-11-08',18000.00,8,'JY0001241H19'),
(9,'2024-11-09',2900.10,9,'JY0001242I20'),
(10,'2024-11-10',15000.25,10,'JY0001243J21'),
(11,'2024-11-11',5600.50,11,'JY0001244K22'),
(12,'2024-11-12',3300.00,12,'JY0001245L23'),
(13,'2024-11-13',4500.90,13,'JY0001246M24'),
(14,'2024-11-14',11200.50,14,'JY0001247N25'),
(15,'2024-11-15',2300.00,15,'JY0001248O26');
/*!40000 ALTER TABLE `pedido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producto`
--

DROP TABLE IF EXISTS `producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `producto` (
  `idProducto` varchar(20) NOT NULL,
  `idCategoria` int(11) DEFAULT NULL,
  `stock` int(11) NOT NULL,
  `kilataje` enum('10K','14K') DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `precioUnitario` decimal(8,2) DEFAULT NULL,
  `gramos` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`idProducto`),
  KEY `fkCategoria` (`idCategoria`),
  CONSTRAINT `fkCategoria` FOREIGN KEY (`idCategoria`) REFERENCES `categoria` (`idCategoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto`
--

LOCK TABLES `producto` WRITE;
/*!40000 ALTER TABLE `producto` DISABLE KEYS */;
INSERT INTO `producto` VALUES
('P001',1,50,'14K','Anillo solitario de compromiso',5250.00,5.25),
('P002',2,30,'10K','Cadena cubana diamantada en oro blanco',15750.00,15.75),
('P003',3,20,'14K','Pulso torsal',12600.00,6.30),
('P004',4,10,'10K','Dormilonas',1000.00,1.00),
('P005',5,25,'14K','Arete violador en forma de flor',9000.00,4.50),
('P006',6,18,'14K','Arete asa mariposa florentino',12000.00,6.00),
('P007',7,40,'10K','Filigrana oaxaqueÃ±a artesanal',7100.00,7.10),
('P008',8,35,'14K','Argolla matrimonial clÃ¡sica',16000.25,8.00),
('P009',9,12,'14K','Reloj oro florentino de 14K',73600.00,36.80),
('P010',10,8,'14K','Dije con diseÃ±o corazon',5000.00,2.50),
('P011',11,15,'10K','Esclava tejido chino',6500.00,6.50),
('P012',12,22,'14K','Medalla virgen guadalupe con zirconia',17000.00,8.50),
('P013',13,5,'10K','Gargantilla de oro con detalles florales',12750.00,12.75),
('P014',14,7,'14K','Tobillera de oro con piedras pequeÃ±as',11200.00,5.25),
('P015',15,10,'10K','Pulsera con diseÃ±o minimalista',6800.00,6.90);
/*!40000 ALTER TABLE `producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productopedido`
--

DROP TABLE IF EXISTS `productopedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productopedido` (
  `idProductoPedido` bigint(20) NOT NULL AUTO_INCREMENT,
  `idProducto` varchar(20) DEFAULT NULL,
  `folio` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `costoUnitario` decimal(8,2) DEFAULT NULL,
  `importe` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`idProductoPedido`),
  KEY `fkProductos` (`idProducto`),
  KEY `fkPedido` (`folio`),
  CONSTRAINT `fkPedido` FOREIGN KEY (`folio`) REFERENCES `pedido` (`folio`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkProductos` FOREIGN KEY (`idProducto`) REFERENCES `producto` (`idProducto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productopedido`
--

LOCK TABLES `productopedido` WRITE;
/*!40000 ALTER TABLE `productopedido` DISABLE KEYS */;
INSERT INTO `productopedido` VALUES
(1,'P001',1,3,5250.00,15750.00),
(2,'P002',1,2,15750.00,31500.00),
(3,'P003',2,1,12600.00,12600.00),
(4,'P004',2,6,1000.00,6000.00),
(5,'P005',3,2,9000.00,18000.00),
(6,'P006',3,1,12000.00,12000.00),
(7,'P007',4,4,7100.00,28400.00),
(8,'P008',4,2,16000.25,32000.50),
(9,'P009',5,1,73600.00,73600.00),
(10,'P010',5,5,5000.00,25000.00),
(11,'P011',6,3,6500.00,19500.00),
(12,'P012',6,1,17000.00,17000.00),
(13,'P013',7,2,12750.00,25500.00),
(14,'P014',7,1,11200.00,11200.00),
(15,'P015',8,4,6800.00,27200.00);
/*!40000 ALTER TABLE `productopedido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productoventa`
--

DROP TABLE IF EXISTS `productoventa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productoventa` (
  `idProductoVenta` bigint(20) NOT NULL AUTO_INCREMENT,
  `idVenta` int(11) DEFAULT NULL,
  `idProducto` varchar(20) DEFAULT NULL,
  `costo` decimal(8,2) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `importe` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`idProductoVenta`),
  KEY `fkVenta` (`idVenta`),
  KEY `fkProducto` (`idProducto`),
  CONSTRAINT `fkProducto` FOREIGN KEY (`idProducto`) REFERENCES `producto` (`idProducto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkVenta` FOREIGN KEY (`idVenta`) REFERENCES `venta` (`idVenta`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productoventa`
--

LOCK TABLES `productoventa` WRITE;
/*!40000 ALTER TABLE `productoventa` DISABLE KEYS */;
INSERT INTO `productoventa` VALUES
(1,1,'P001',5250.00,2,10500.00),
(2,1,'P002',15750.00,1,15750.00),
(3,2,'P003',12600.00,3,37800.00),
(4,2,'P004',1000.00,5,5000.00),
(5,3,'P005',9000.00,2,18000.00),
(6,3,'P006',12000.00,1,12000.00),
(7,4,'P007',7100.00,4,28400.00),
(8,4,'P008',16000.25,1,16000.25),
(9,5,'P009',73600.00,1,73600.00),
(10,5,'P010',5000.00,3,15000.00),
(11,6,'P011',6500.00,2,13000.00),
(12,6,'P012',17000.00,1,17000.00),
(13,7,'P013',12750.00,1,12750.00),
(14,7,'P014',11200.00,2,22400.00),
(15,8,'P015',6800.00,3,20400.00);
/*!40000 ALTER TABLE `productoventa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedor`
--

DROP TABLE IF EXISTS `proveedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proveedor` (
  `rfc` varchar(13) NOT NULL,
  `razonSocial` varchar(100) NOT NULL,
  `telefono` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`rfc`),
  UNIQUE KEY `telefono` (`telefono`),
  UNIQUE KEY `telefono_2` (`telefono`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedor`
--

LOCK TABLES `proveedor` WRITE;
/*!40000 ALTER TABLE `proveedor` DISABLE KEYS */;
INSERT INTO `proveedor` VALUES
('JY0001234A12','Joyeros del Oro S.A. de C.V.',9511234561),
('JY0001235B13','Gemas y Minerales Oaxaca S.A.',9511234562),
('JY0001236C14','Filigranas OaxaqueÃ±as S.A.',9511234563),
('JY0001237D15','Oro y Plata de la Mixteca S.A.',9511234564),
('JY0001238E16','Joyas del Valle OaxaqueÃ±o S.A.',9511234565),
('JY0001239F17','DiseÃ±os Artesanales S.A. de C.V.',9511234566),
('JY0001240G18','Alhajas del Sol S.A. de C.V.',9511234567),
('JY0001241H19','OaxaqueÃ±a de Joyeros S.A.',9511234568),
('JY0001242I20','Arte en Filigrana S.A.',9511234569),
('JY0001243J21','JoyerÃ­a Colonial S.A.',9511234570),
('JY0001244K22','Oro Puro de Oaxaca S.A.',9511234571),
('JY0001245L23','Brillante Oaxaca S.A.',9511234572),
('JY0001246M24','El Tesoro del Oro S.A.',9511234573),
('JY0001247N25','Joyas del EdÃ©n S.A. de C.V.',9511234574),
('JY0001248O26','Gemas y Joyas de la Sierra S.A.',9511234575);
/*!40000 ALTER TABLE `proveedor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `puestoempleado`
--

DROP TABLE IF EXISTS `puestoempleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `puestoempleado` (
  `idPuesto` int(11) NOT NULL AUTO_INCREMENT,
  `puesto` varchar(50) NOT NULL,
  `sueldo` decimal(7,2) DEFAULT NULL,
  PRIMARY KEY (`idPuesto`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `puestoempleado`
--

LOCK TABLES `puestoempleado` WRITE;
/*!40000 ALTER TABLE `puestoempleado` DISABLE KEYS */;
INSERT INTO `puestoempleado` VALUES
(1,'Gerente',5000.00),
(2,'Venta',4000.00),
(3,'AlmacÃ©n',8000.00),
(4,'Contador',10000.00);
/*!40000 ALTER TABLE `puestoempleado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipocliente`
--

DROP TABLE IF EXISTS `tipocliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipocliente` (
  `idTipoCliente` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) NOT NULL,
  PRIMARY KEY (`idTipoCliente`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipocliente`
--

LOCK TABLES `tipocliente` WRITE;
/*!40000 ALTER TABLE `tipocliente` DISABLE KEYS */;
INSERT INTO `tipocliente` VALUES
(1,'Publico'),
(2,'Mayorista');
/*!40000 ALTER TABLE `tipocliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `venta`
--

DROP TABLE IF EXISTS `venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `venta` (
  `idVenta` int(11) NOT NULL AUTO_INCREMENT,
  `fechaVenta` date DEFAULT NULL,
  `idEmpleado` int(11) DEFAULT NULL,
  `idCliente` int(11) DEFAULT NULL,
  `IdIngreso` int(11) DEFAULT NULL,
  PRIMARY KEY (`idVenta`),
  KEY `fkEmpleadoPedido` (`idEmpleado`),
  KEY `fkCliente` (`idCliente`),
  KEY `fkIngreso` (`IdIngreso`),
  CONSTRAINT `fkCliente` FOREIGN KEY (`idCliente`) REFERENCES `cliente` (`idCliente`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkEmpleadoPedido` FOREIGN KEY (`idEmpleado`) REFERENCES `empleado` (`idEmpleado`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkIngreso` FOREIGN KEY (`IdIngreso`) REFERENCES `ingreso` (`idIngreso`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `venta`
--

LOCK TABLES `venta` WRITE;
/*!40000 ALTER TABLE `venta` DISABLE KEYS */;
INSERT INTO `venta` VALUES
(1,'2024-11-01',2,1,101),
(2,'2024-11-01',2,2,101),
(3,'2024-11-01',3,3,101),
(4,'2024-11-02',4,1,102),
(5,'2024-11-02',5,1,102),
(6,'2024-11-02',2,1,102),
(7,'2024-11-03',6,8,103),
(8,'2024-11-03',6,4,103),
(9,'2024-11-03',7,1,103);
/*!40000 ALTER TABLE `venta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'joyeriachabelitadb'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-10-17 13:01:37