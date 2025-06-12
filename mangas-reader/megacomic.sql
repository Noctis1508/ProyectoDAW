-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-06-2025 a las 18:30:32
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `megacomic`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$rfG6fNRck5ZPEcyR0hWaJuJDttRDy247OpHFw7XJXxExx.2gAT4ei');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'Acción'),
(2, 'Animación'),
(3, 'Apocalíptico'),
(4, 'Artes Marciales'),
(5, 'Aventura'),
(6, 'Ciberpunk'),
(7, 'Ciencia Ficción'),
(8, 'Comedia'),
(9, 'Crimen'),
(10, 'Demonios'),
(11, 'Deporte'),
(12, 'Drama'),
(13, 'Ecchi'),
(14, 'Extranjero'),
(15, 'Familia'),
(16, 'Fantasia'),
(17, 'Género Bender'),
(18, 'Gore'),
(19, 'Guerra'),
(20, 'Harem'),
(21, 'Historia'),
(22, 'Horror'),
(23, 'Magia'),
(24, 'Mecha'),
(25, 'Militar'),
(26, 'Misterio'),
(27, 'Musica'),
(28, 'Niños'),
(29, 'Oeste'),
(30, 'Parodia'),
(31, 'Policiaco'),
(32, 'Psicológico'),
(33, 'Realidad'),
(34, 'Realidad Virtual'),
(35, 'Recuentos de la vida'),
(36, 'Reencarnación'),
(37, 'Romance'),
(38, 'Samurái'),
(39, 'Sobrenatural'),
(40, 'Superpoderes'),
(41, 'Supervivencia'),
(42, 'Telenovela'),
(43, 'Thriller'),
(44, 'Tragedia'),
(45, 'Traps'),
(46, 'Vampiros'),
(47, 'Vida Escolar');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chapters`
--

CREATE TABLE `chapters` (
  `id` int(11) NOT NULL,
  `manga_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `chapter_number` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `chapters`
--

INSERT INTO `chapters` (`id`, `manga_id`, `title`, `chapter_number`, `created_at`) VALUES
(3, 2, 'piloto', 1, '2025-05-25 15:59:32'),
(4, 2, 'Naruto Uzumaki', 2, '2025-05-25 16:16:53'),
(5, 3, 'Ichigo', 1, '2025-06-04 14:44:53'),
(6, 5, 'Amanecer de un pirata', 1, '2025-06-06 08:37:40'),
(7, 2, 'la aldea de la hoja', 3, '2025-06-06 15:17:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `manga_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT 'Anónimo',
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comments`
--

INSERT INTO `comments` (`id`, `manga_id`, `name`, `content`, `created_at`) VALUES
(1, 2, 'Prueba', 'muy bueno tio', '2025-06-05 16:12:48'),
(2, 2, 'Prueba2', 'es malísimo', '2025-06-05 16:16:33'),
(3, 2, 'prueba 3', 'mejora el dibujo bro', '2025-06-06 11:41:30'),
(4, 2, 'o', 'pruebita', '2025-06-09 10:42:35'),
(5, 5, 'escarlet', 'oda god', '2025-06-09 16:46:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mangas`
--

CREATE TABLE `mangas` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('publicandose','pausado','terminado') DEFAULT 'publicandose'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mangas`
--

INSERT INTO `mangas` (`id`, `title`, `description`, `cover_image`, `created_at`, `status`) VALUES
(2, 'Naruto', 'Hace doce años, el poderoso Zorro Demonio de Nueve Colas atacó Konohagakure, la Aldea Oculta Entre las Hojas. Fue derrotado y sellado dentro del niño Naruto Uzumaki, gracias al sacrificio del Cuarto Hokage, quien dio su vida por la aldea. Ahora, Naruto, conocido como el ninja cabeza hueca número uno, se propone convertirse en el próximo Hokage y demostrar su valía a todos aquellos que alguna vez dudaron de él.', 'cover_68311794025cd2.39459958.jpg', '2025-05-24 00:49:24', 'terminado'),
(3, 'Bleach', 'Kurosaki Ichigo es un joven aparentemente normal en la ciudad de Karakura, hasta que descubre que posee una capacidad extraordinaria: la habilidad de ver espíritus. Su vida, apacible pero desprovista de un rumbo definido, da un giro inesperado al conocer a Kuchiki Rukia, una Shinigami de la Sociedad de Almas que, al enfrentarse a una misión descontrolada, se ve obligada a transferirle parte de su poder a Ichigo, convirtiéndolo en el Shinigami Sustituto. A partir de ese instante, ambos se ven inmersos en la eterna lucha por proteger a Karakura y a quienes aman. Cada encuentro en su camino enciende nuevas aventuras y retos, impulsándolo a superarse continuamente, sin perder de vista lo más valioso: la defensa inquebrantable de su familia y amigos.', 'cover_6841659f35d5f.jpg', '2025-06-04 14:42:45', 'pausado'),
(5, 'One Piece', 'Hace veintidós años, el legendario pirata, Gold Roger fue ejecutado. Sus últimas palabras fueron que su tesoro conocido como \"One Piece\" estaba escondido en algún lugar de la Grand Line. Esto dio inicio a la Era de los Piratas. Ahora, veinte dos años después, Monkey D. Luffy de diecisiete años desea encontrar el One Piece y convertirse en el Rey de los Piratas.', 'cover_6842a7bd36bda8.86973850.jpg', '2025-06-06 08:33:01', 'publicandose');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `manga_categoria`
--

CREATE TABLE `manga_categoria` (
  `manga_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `manga_categoria`
--

INSERT INTO `manga_categoria` (`manga_id`, `categoria_id`) VALUES
(2, 4),
(2, 5),
(2, 7),
(2, 16),
(2, 38),
(3, 1),
(3, 4),
(3, 8),
(3, 16),
(3, 38),
(3, 39),
(3, 40),
(5, 1),
(5, 4),
(5, 5),
(5, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `chapter_id` int(11) NOT NULL,
  `page_number` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pages`
--

INSERT INTO `pages` (`id`, `chapter_id`, `page_number`, `image_path`) VALUES
(3, 3, 1, 'page_683340e0a2fd4.jpg'),
(5, 4, 1, 'page_684abc094d22c.png'),
(9, 3, 3, 'page_68334959ba10d.jpg'),
(10, 3, 2, 'page_6833497c6b1cc.jpg'),
(13, 5, 1, 'page_68405ff025521.jpg'),
(14, 5, 2, 'page_684060691a014.jpg'),
(17, 6, 2, 'page_6842a8d49e90f.jpg'),
(18, 6, 1, 'page_6842a8d49fae1.jpg'),
(19, 7, 1, 'page_684abc178a1e0.png'),
(20, 7, 2, 'page_684abc1f8612c.png'),
(23, 6, 3, 'page_684abb6f6e9b5.png');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `chapters`
--
ALTER TABLE `chapters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manga_id` (`manga_id`);

--
-- Indices de la tabla `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manga_id` (`manga_id`);

--
-- Indices de la tabla `mangas`
--
ALTER TABLE `mangas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `manga_categoria`
--
ALTER TABLE `manga_categoria`
  ADD PRIMARY KEY (`manga_id`,`categoria_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chapter_id` (`chapter_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `chapters`
--
ALTER TABLE `chapters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `mangas`
--
ALTER TABLE `mangas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `chapters`
--
ALTER TABLE `chapters`
  ADD CONSTRAINT `chapters_ibfk_1` FOREIGN KEY (`manga_id`) REFERENCES `mangas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`manga_id`) REFERENCES `mangas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `manga_categoria`
--
ALTER TABLE `manga_categoria`
  ADD CONSTRAINT `manga_categoria_ibfk_1` FOREIGN KEY (`manga_id`) REFERENCES `mangas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `manga_categoria_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_ibfk_1` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
