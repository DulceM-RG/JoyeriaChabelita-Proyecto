-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: chavelita
-- ------------------------------------------------------
-- Server version	11.5.2-MariaDB

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
-- Table structure for table `categoria`
--

DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categoria` (
  `idCategoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(25) NOT NULL,
  PRIMARY KEY (`idCategoria`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categoria`
--

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
INSERT INTO `categoria` VALUES (1,'Anillo '),(2,'Cadena'),(3,'Pulso'),(4,'Broquel'),(5,'Arete Violador'),(6,'Arete Asa'),(7,'Filigrana'),(8,'Argolla matrimonial'),(9,'Reloj'),(10,'Dije'),(11,'Esclava'),(12,'Medalla Religiosa'),(13,'Gargantilla'),(14,'Tobillera'),(15,'Pulsera');
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cliente`
--

DROP TABLE IF EXISTS `cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cliente` (
  `idCliente` int(11) NOT NULL AUTO_INCREMENT,
  `idTipoCliente` int(11) NOT NULL,
  `nombre` varchar(15) DEFAULT NULL,
  `apellidoPaterno` varchar(15) DEFAULT NULL,
  `apellidoMaterno` varchar(15) DEFAULT NULL,
  `telefono` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`idCliente`),
  UNIQUE KEY `telefono` (`telefono`),
  KEY `idTipoCliente` (`idTipoCliente`),
  CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`idTipoCliente`) REFERENCES `tipocliente` (`idTipoCliente`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente`
--

LOCK TABLES `cliente` WRITE;
/*!40000 ALTER TABLE `cliente` DISABLE KEYS */;
INSERT INTO `cliente` VALUES (1,1,NULL,NULL,NULL,NULL),(2,2,'Maria','Hernandez','Garcia','5551234562'),(3,2,'Juan','Perez','Martinez','9541234563'),(4,2,'Ana','Lopez','Ruiz','2811234564'),(5,2,'Luis','Martinez','Santos','9511234565');
/*!40000 ALTER TABLE `cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credenciales`
--

DROP TABLE IF EXISTS `credenciales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `credenciales` (
  `idControl` varchar(10) NOT NULL,
  `idEmpleado` int(11) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `intentosFallidos` int(11) DEFAULT 0,
  `fechaCreacion` date DEFAULT curdate(),
  `ultimoCambio` date DEFAULT curdate(),
  `activo` enum('Activo','Baja') DEFAULT 'Activo',
  PRIMARY KEY (`idControl`),
  KEY `idEmpleado` (`idEmpleado`),
  CONSTRAINT `credenciales_ibfk_1` FOREIGN KEY (`idEmpleado`) REFERENCES `empleado` (`idEmpleado`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credenciales`
--

LOCK TABLES `credenciales` WRITE;
/*!40000 ALTER TABLE `credenciales` DISABLE KEYS */;
INSERT INTO `credenciales` VALUES ('A25100303',3,'3456',0,'2025-10-03','2025-10-03','Activo'),('A25100505',5,'5678',0,'2025-10-05','2025-10-05','Activo'),('G25100101',1,'1234',0,'2025-10-01','2025-10-01','Activo'),('V25100202',2,'2345',0,'2025-10-02','2025-10-02','Activo'),('V25100404',4,'4567',0,'2025-10-04','2025-10-04','Activo');
/*!40000 ALTER TABLE `credenciales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `direccion`
--

DROP TABLE IF EXISTS `direccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `direccion` (
  `idDireccion` int(11) NOT NULL AUTO_INCREMENT,
  `nombreCalle` varchar(100) NOT NULL,
  `numeroCalle` int(11) NOT NULL,
  `localidad` varchar(100) NOT NULL,
  `codigoPostal` varchar(5) NOT NULL,
  PRIMARY KEY (`idDireccion`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direccion`
--

LOCK TABLES `direccion` WRITE;
/*!40000 ALTER TABLE `direccion` DISABLE KEYS */;
INSERT INTO `direccion` VALUES (1,'Calle de Los Libres',123,'Centro','71233'),(2,'Calle Macedonio Alcalá',456,'Centro Histórico','71406'),(3,'Calle García Vigil',789,'Centro','71300'),(4,'Avenida Universidad',101,'Ex-Hacienda Candiani','71233'),(5,'Calle Manuel Sabino Crespo',202,'Reforma','71233');
/*!40000 ALTER TABLE `direccion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleado`
--

DROP TABLE IF EXISTS `empleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  KEY `idPuesto` (`idPuesto`),
  KEY `idDireccion` (`idDireccion`),
  CONSTRAINT `empleado_ibfk_1` FOREIGN KEY (`idPuesto`) REFERENCES `puestoempleado` (`idPuesto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `empleado_ibfk_2` FOREIGN KEY (`idDireccion`) REFERENCES `direccion` (`idDireccion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleado`
--

LOCK TABLES `empleado` WRITE;
/*!40000 ALTER TABLE `empleado` DISABLE KEYS */;
INSERT INTO `empleado` VALUES (1,'Carlos','Hernández','López',9511234567,1,1),(2,'María','García','Ruiz',9512345678,2,2),(3,'José','Ramírez','Santos',9513456789,3,3),(4,'Ana','Martínez','Pérez',9514567890,2,4),(5,'Luis','Flores','Mendoza',9515678901,3,5);
/*!40000 ALTER TABLE `empleado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingreso`
--

DROP TABLE IF EXISTS `ingreso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ingreso` (
  `idIngreso` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `importeTotal` decimal(10,2) NOT NULL,
  `metodoPago` enum('efectivo','tarjeta') NOT NULL,
  PRIMARY KEY (`idIngreso`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingreso`
--

LOCK TABLES `ingreso` WRITE;
/*!40000 ALTER TABLE `ingreso` DISABLE KEYS */;
INSERT INTO `ingreso` VALUES (1,'2024-11-01',15000.50,'efectivo'),(2,'2024-11-02',25000.00,'efectivo'),(3,'2024-11-03',18000.75,'tarjeta'),(4,'2024-11-04',22000.00,'tarjeta'),(5,'2024-11-05',19500.20,'efectivo');
/*!40000 ALTER TABLE `ingreso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producto`
--

DROP TABLE IF EXISTS `producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `producto` (
  `idProducto` varchar(20) NOT NULL,
  `idCategoria` int(11) DEFAULT NULL,
  `rfcProveedor` varchar(13) DEFAULT NULL,
  `stock` int(11) NOT NULL,
  `kilataje` enum('8K','10K','14K','18k') DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `precioCompra` decimal(8,2) DEFAULT NULL,
  `precioUnitario` decimal(8,2) DEFAULT NULL,
  `gramos` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`idProducto`),
  KEY `idCategoria` (`idCategoria`),
  KEY `rfcProveedor` (`rfcProveedor`),
  CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`idCategoria`) REFERENCES `categoria` (`idCategoria`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`rfcProveedor`) REFERENCES `proveedor` (`rfc`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto`
--

LOCK TABLES `producto` WRITE;
/*!40000 ALTER TABLE `producto` DISABLE KEYS */;
INSERT INTO `producto` VALUES ('P001',1,'JY0001240G186',50,'14K','Solitario de compromiso',4000.00,5250.00,5.25),('P002',2,'JY0001244K228',30,'10K','Tejido cubano diamantado en oro blanco',12000.00,15750.00,15.75),('P003',3,'JY0001246M244',40,'18k','Tejido torsal',9500.00,12600.00,6.30),('P004',4,'JY0001247N259',60,'10K','Dormilonas',750.00,1000.00,1.00),('P005',5,'JY0001248O264',25,'14K','Forma de flor',7000.00,9000.00,4.50);
/*!40000 ALTER TABLE `producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productoventa`
--

DROP TABLE IF EXISTS `productoventa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productoventa` (
  `idProductoVenta` bigint(20) NOT NULL AUTO_INCREMENT,
  `idVenta` int(11) DEFAULT NULL,
  `idProducto` varchar(20) DEFAULT NULL,
  `costo` decimal(8,2) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `importe` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`idProductoVenta`),
  KEY `idVenta` (`idVenta`),
  KEY `idProducto` (`idProducto`),
  CONSTRAINT `productoventa_ibfk_1` FOREIGN KEY (`idVenta`) REFERENCES `venta` (`idVenta`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `productoventa_ibfk_2` FOREIGN KEY (`idProducto`) REFERENCES `producto` (`idProducto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productoventa`
--

LOCK TABLES `productoventa` WRITE;
/*!40000 ALTER TABLE `productoventa` DISABLE KEYS */;
INSERT INTO `productoventa` VALUES (1,1,'P001',5250.00,1,5250.00),(2,2,'P002',15750.00,1,15750.00),(3,3,'P003',12600.00,2,25200.00),(4,4,'P004',1000.00,3,3000.00),(5,5,'P005',9000.00,1,9000.00);
/*!40000 ALTER TABLE `productoventa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedor`
--

DROP TABLE IF EXISTS `proveedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedor` (
  `rfc` varchar(13) NOT NULL,
  `razonSocial` varchar(100) NOT NULL,
  `telefono` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`rfc`),
  UNIQUE KEY `telefono` (`telefono`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedor`
--

LOCK TABLES `proveedor` WRITE;
/*!40000 ALTER TABLE `proveedor` DISABLE KEYS */;
INSERT INTO `proveedor` VALUES ('JY0001240G186','Alhajas del Sol S.A. de C.V.',9511234567),('JY0001244K228','Oro Puro de Oaxaca S.A.',9511234571),('JY0001246M244','El Tesoro del Oro S.A.',9511234573),('JY0001247N259','Joyas del Edén S.A. de C.V.',9511234574),('JY0001248O264','Gemas y Joyas de la Sierra S.A.',9511234575);
/*!40000 ALTER TABLE `proveedor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `puestoempleado`
--

DROP TABLE IF EXISTS `puestoempleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `puestoempleado` (
  `idPuesto` int(11) NOT NULL AUTO_INCREMENT,
  `puesto` varchar(50) NOT NULL,
  `sueldo` decimal(7,2) NOT NULL,
  PRIMARY KEY (`idPuesto`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `puestoempleado`
--

LOCK TABLES `puestoempleado` WRITE;
/*!40000 ALTER TABLE `puestoempleado` DISABLE KEYS */;
INSERT INTO `puestoempleado` VALUES (1,'Gerente',5000.00),(2,'Venta',4000.00),(3,'Almacén',8000.00);
/*!40000 ALTER TABLE `puestoempleado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipocliente`
--

DROP TABLE IF EXISTS `tipocliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipocliente` (
  `idTipoCliente` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(15) NOT NULL,
  PRIMARY KEY (`idTipoCliente`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipocliente`
--

LOCK TABLES `tipocliente` WRITE;
/*!40000 ALTER TABLE `tipocliente` DISABLE KEYS */;
INSERT INTO `tipocliente` VALUES (1,'Publico'),(2,'Mayorista');
/*!40000 ALTER TABLE `tipocliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `venta`
--

DROP TABLE IF EXISTS `venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `venta` (
  `idVenta` int(11) NOT NULL AUTO_INCREMENT,
  `fechaVenta` date DEFAULT NULL,
  `idEmpleado` int(11) DEFAULT NULL,
  `idCliente` int(11) DEFAULT NULL,
  `idIngreso` int(11) DEFAULT NULL,
  PRIMARY KEY (`idVenta`),
  KEY `idEmpleado` (`idEmpleado`),
  KEY `idCliente` (`idCliente`),
  KEY `idIngreso` (`idIngreso`),
  CONSTRAINT `venta_ibfk_1` FOREIGN KEY (`idEmpleado`) REFERENCES `empleado` (`idEmpleado`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `venta_ibfk_2` FOREIGN KEY (`idCliente`) REFERENCES `cliente` (`idCliente`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `venta_ibfk_3` FOREIGN KEY (`idIngreso`) REFERENCES `ingreso` (`idIngreso`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `venta`
--

LOCK TABLES `venta` WRITE;
/*!40000 ALTER TABLE `venta` DISABLE KEYS */;
INSERT INTO `venta` VALUES (1,'2024-11-01',2,2,1),(2,'2024-11-02',4,3,2),(3,'2024-11-03',2,4,3),(4,'2024-11-04',4,5,4),(5,'2024-11-05',2,1,5);
/*!40000 ALTER TABLE `venta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'chavelita'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-21 22:07:24
