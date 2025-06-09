-- MySQL dump 10.13  Distrib 8.0.37, for Linux (x86_64)
--
-- Host: localhost    Database: megacomic
-- ------------------------------------------------------
-- Server version	8.0.37

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
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$rfG6fNRck5ZPEcyR0hWaJuJDttRDy247OpHFw7XJXxExx.2gAT4ei');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'Acción'),(2,'Animación'),(3,'Apocalíptico'),(4,'Artes Marciales'),(5,'Aventura'),(6,'Ciberpunk'),(7,'Ciencia Ficción'),(8,'Comedia'),(9,'Crimen'),(10,'Demonios'),(11,'Deporte'),(12,'Drama'),(13,'Ecchi'),(14,'Extranjero'),(15,'Familia'),(16,'Fantasia'),(17,'Género Bender'),(18,'Gore'),(19,'Guerra'),(20,'Harem'),(21,'Historia'),(22,'Horror'),(23,'Magia'),(24,'Mecha'),(25,'Militar'),(26,'Misterio'),(27,'Musica'),(28,'Niños'),(29,'Oeste'),(30,'Parodia'),(31,'Policiaco'),(32,'Psicológico'),(33,'Realidad'),(34,'Realidad Virtual'),(35,'Recuentos de la vida'),(36,'Reencarnación'),(37,'Romance'),(38,'Samurái'),(39,'Sobrenatural'),(40,'Superpoderes'),(41,'Supervivencia'),(42,'Telenovela'),(43,'Thriller'),(44,'Tragedia'),(45,'Traps'),(46,'Vampiros'),(47,'Vida Escolar');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chapters`
--

DROP TABLE IF EXISTS `chapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chapters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `manga_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `chapter_number` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `manga_id` (`manga_id`),
  CONSTRAINT `chapters_ibfk_1` FOREIGN KEY (`manga_id`) REFERENCES `mangas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chapters`
--

LOCK TABLES `chapters` WRITE;
/*!40000 ALTER TABLE `chapters` DISABLE KEYS */;
INSERT INTO `chapters` VALUES (3,2,'piloto',1,'2025-05-25 15:59:32'),(4,2,'Naruto Uzumaki',2,'2025-05-25 16:16:53'),(5,3,'Ichigo',1,'2025-06-04 14:44:53'),(6,5,'Amanecer de un pirata',1,'2025-06-06 08:37:40'),(7,2,'la aldea de la hoja',3,'2025-06-06 15:17:00');
/*!40000 ALTER TABLE `chapters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `manga_id` int NOT NULL,
  `name` varchar(100) DEFAULT 'Anónimo',
  `content` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `manga_id` (`manga_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`manga_id`) REFERENCES `mangas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (1,2,'Prueba','muy bueno tio','2025-06-05 16:12:48'),(2,2,'Prueba2','es malísimo','2025-06-05 16:16:33'),(3,2,'prueba 3','mejora el dibujo bro','2025-06-06 11:41:30'),(4,2,'o','pruebita','2025-06-09 10:42:35'),(5,5,'escarlet','oda god','2025-06-09 16:46:56');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `manga_categoria`
--

DROP TABLE IF EXISTS `manga_categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `manga_categoria` (
  `manga_id` int NOT NULL,
  `categoria_id` int NOT NULL,
  PRIMARY KEY (`manga_id`,`categoria_id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `manga_categoria_ibfk_1` FOREIGN KEY (`manga_id`) REFERENCES `mangas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `manga_categoria_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `manga_categoria`
--

LOCK TABLES `manga_categoria` WRITE;
/*!40000 ALTER TABLE `manga_categoria` DISABLE KEYS */;
INSERT INTO `manga_categoria` VALUES (3,1),(5,1),(2,4),(3,4),(5,4),(2,5),(5,5),(2,7),(3,8),(5,8),(2,16),(3,16),(2,38),(3,38),(3,39),(3,40);
/*!40000 ALTER TABLE `manga_categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mangas`
--

DROP TABLE IF EXISTS `mangas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mangas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `cover_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('publicandose','pausado','terminado') COLLATE utf8mb4_general_ci DEFAULT 'publicandose',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mangas`
--

LOCK TABLES `mangas` WRITE;
/*!40000 ALTER TABLE `mangas` DISABLE KEYS */;
INSERT INTO `mangas` VALUES (2,'Naruto','Hace doce años, el poderoso Zorro Demonio de Nueve Colas atacó Konohagakure, la Aldea Oculta Entre las Hojas. Fue derrotado y sellado dentro del niño Naruto Uzumaki, gracias al sacrificio del Cuarto Hokage, quien dio su vida por la aldea. Ahora, Naruto, conocido como el ninja cabeza hueca número uno, se propone convertirse en el próximo Hokage y demostrar su valía a todos aquellos que alguna vez dudaron de él.','cover_68311794025cd2.39459958.jpg','2025-05-24 00:49:24','terminado'),(3,'Bleach','Kurosaki Ichigo es un joven aparentemente normal en la ciudad de Karakura, hasta que descubre que posee una capacidad extraordinaria: la habilidad de ver espíritus. Su vida, apacible pero desprovista de un rumbo definido, da un giro inesperado al conocer a Kuchiki Rukia, una Shinigami de la Sociedad de Almas que, al enfrentarse a una misión descontrolada, se ve obligada a transferirle parte de su poder a Ichigo, convirtiéndolo en el Shinigami Sustituto. A partir de ese instante, ambos se ven inmersos en la eterna lucha por proteger a Karakura y a quienes aman. Cada encuentro en su camino enciende nuevas aventuras y retos, impulsándolo a superarse continuamente, sin perder de vista lo más valioso: la defensa inquebrantable de su familia y amigos.','cover_6841659f35d5f.jpg','2025-06-04 14:42:45','pausado'),(5,'One Piece','Hace veintidós años, el legendario pirata, Gold Roger fue ejecutado. Sus últimas palabras fueron que su tesoro conocido como \"One Piece\" estaba escondido en algún lugar de la Grand Line. Esto dio inicio a la Era de los Piratas. Ahora, veinte dos años después, Monkey D. Luffy de diecisiete años desea encontrar el One Piece y convertirse en el Rey de los Piratas.','cover_6842a7bd36bda8.86973850.jpg','2025-06-06 08:33:01','publicandose');
/*!40000 ALTER TABLE `mangas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `chapter_id` int NOT NULL,
  `page_number` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chapter_id` (`chapter_id`),
  CONSTRAINT `pages_ibfk_1` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (3,3,1,'page_683340e0a2fd4.jpg'),(5,4,1,'page_6833427574c1d.jpg'),(9,3,3,'page_68334959ba10d.jpg'),(10,3,2,'page_6833497c6b1cc.jpg'),(13,5,1,'page_68405ff025521.jpg'),(14,5,2,'page_684060691a014.jpg'),(17,6,2,'page_6842a8d49e90f.jpg'),(18,6,1,'page_6842a8d49fae1.jpg'),(19,7,1,'page_6843066c271c0.jpg'),(20,7,2,'page_6843066c27ff4.jpg'),(23,6,3,'page_6846b5afdfa13.jpg');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-09 17:19:56
