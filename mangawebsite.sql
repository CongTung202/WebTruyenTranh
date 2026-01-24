-- MySQL dump 10.13  Distrib 8.0.34, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: mangawebsite
-- ------------------------------------------------------
-- Server version	8.0.35

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles` (
  `ArticleID` int NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Description` text COLLATE utf8mb4_unicode_ci,
  `CoverImage` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Status` tinyint DEFAULT '0',
  `ViewCount` int DEFAULT '0',
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `IsDeleted` tinyint DEFAULT '0',
  `CategoryID` int DEFAULT NULL,
  PRIMARY KEY (`ArticleID`),
  KEY `CategoryID` (`CategoryID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles`
--

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
INSERT INTO `articles` VALUES (1,'one-piece','One Piece','Câu chuyện về hành trình tìm kiếm kho báu của Luffy.','uploads/covers/1767701166_695cfaaed09b3.png',1,15076,'2026-01-06 11:05:29','2026-01-07 17:58:21',0,NULL),(2,'naruto','Naruto','Hành trình trở thành Hokage của Naruto Uzumaki.','uploads/covers/1767701200_695cfad0dddb7.png',2,20042,'2026-01-06 11:05:29','2026-01-08 19:03:57',0,NULL),(3,'one-punch-man','One Punch Man','Thánh phồng tôm đấm phát chết luôn.','uploads/covers/1767701213_695cfadd72324.png',1,8530,'2026-01-06 11:05:29','2026-01-06 23:28:14',1,NULL),(4,'gal','Gal','hello world','uploads/covers/1767716515_695d36a3b3e8d.png',1,26,'2026-01-06 23:21:55','2026-01-08 18:50:02',0,NULL),(5,'alma','Alma','','uploads/covers/1767719436_695d420c591e3.jpg',2,16,'2026-01-07 00:10:36','2026-01-08 19:03:37',0,1),(6,'elder-ring','Elder Ring','','uploads/covers/1767719976_695d442845e71.jpg',1,15,'2026-01-07 00:19:36','2026-01-07 17:24:48',0,NULL),(7,'uma','uma','gfg','uploads/covers/1767720024_695d4458699f8.png',1,7,'2026-01-07 00:20:24','2026-01-08 20:39:37',0,NULL),(8,'vncvn','OMG Test','cxcbc','https://res.cloudinary.com/dhefmthim/image/upload/v1767875310/covers/hi5oxoj75ndu5kaujyrw.jpg',1,2,'2026-01-08 19:28:28','2026-01-09 13:33:36',0,2);
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles_authors`
--

DROP TABLE IF EXISTS `articles_authors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles_authors` (
  `ArticleID` int NOT NULL,
  `AuthorID` int NOT NULL,
  PRIMARY KEY (`ArticleID`,`AuthorID`),
  KEY `AuthorID` (`AuthorID`),
  CONSTRAINT `articles_authors_ibfk_1` FOREIGN KEY (`ArticleID`) REFERENCES `articles` (`ArticleID`) ON DELETE CASCADE,
  CONSTRAINT `articles_authors_ibfk_2` FOREIGN KEY (`AuthorID`) REFERENCES `authors` (`AuthorID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles_authors`
--

LOCK TABLES `articles_authors` WRITE;
/*!40000 ALTER TABLE `articles_authors` DISABLE KEYS */;
INSERT INTO `articles_authors` VALUES (1,1),(8,1),(2,2),(3,3),(7,4);
/*!40000 ALTER TABLE `articles_authors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles_genres`
--

DROP TABLE IF EXISTS `articles_genres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles_genres` (
  `ArticleID` int NOT NULL,
  `GenreID` int NOT NULL,
  PRIMARY KEY (`ArticleID`,`GenreID`),
  KEY `GenreID` (`GenreID`),
  CONSTRAINT `articles_genres_ibfk_1` FOREIGN KEY (`ArticleID`) REFERENCES `articles` (`ArticleID`) ON DELETE CASCADE,
  CONSTRAINT `articles_genres_ibfk_2` FOREIGN KEY (`GenreID`) REFERENCES `genres` (`GenreID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles_genres`
--

LOCK TABLES `articles_genres` WRITE;
/*!40000 ALTER TABLE `articles_genres` DISABLE KEYS */;
INSERT INTO `articles_genres` VALUES (1,1),(2,1),(3,1),(5,1),(8,1),(1,2),(2,2),(6,2),(8,2),(1,3),(3,3),(6,3),(7,3),(8,3),(6,4),(8,4),(6,5),(8,5),(8,7);
/*!40000 ALTER TABLE `articles_genres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `authors`
--

DROP TABLE IF EXISTS `authors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `authors` (
  `AuthorID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Avatar` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Description` text COLLATE utf8mb4_unicode_ci,
  `IsDeleted` tinyint DEFAULT '0',
  PRIMARY KEY (`AuthorID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `authors`
--

LOCK TABLES `authors` WRITE;
/*!40000 ALTER TABLE `authors` DISABLE KEYS */;
INSERT INTO `authors` VALUES (1,'Eiichiro Oda','oda.jpg','Tác giả của bộ truyện huyền thoại One Piece.',0),(2,'Masashi Kishimoto','kishimoto.jpg','Cha đẻ của Naruto.',0),(3,'ONE','one.jpg','Tác giả của One Punch Man.',0),(4,'fgfdh',NULL,NULL,0);
/*!40000 ALTER TABLE `authors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookmarks`
--

DROP TABLE IF EXISTS `bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookmarks` (
  `BookmarkID` int NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  `ArticleID` int NOT NULL,
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`BookmarkID`),
  UNIQUE KEY `UserID` (`UserID`,`ArticleID`),
  KEY `ArticleID` (`ArticleID`),
  CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`ArticleID`) REFERENCES `articles` (`ArticleID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookmarks`
--

LOCK TABLES `bookmarks` WRITE;
/*!40000 ALTER TABLE `bookmarks` DISABLE KEYS */;
INSERT INTO `bookmarks` VALUES (1,2,1,'2026-01-06 11:05:29'),(2,2,3,'2026-01-06 11:05:29'),(3,3,2,'2026-01-06 11:05:29'),(6,1,3,'2026-01-06 22:45:53'),(7,1,2,'2026-01-06 23:28:47'),(13,1,1,'2026-01-07 17:56:20'),(14,5,2,'2026-01-08 18:46:09'),(15,5,4,'2026-01-08 18:47:57'),(19,5,5,'2026-01-08 19:03:43'),(20,1,7,'2026-01-08 20:49:16');
/*!40000 ALTER TABLE `bookmarks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `CategoryID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Description` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CategoryID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Manga','Truyện tranh Nhật Bản','/category/manga'),(2,'Manhwa','Truyện tranh Hàn Quốc','/category/manhwa'),(3,'Manhua','Truyện tranh Trung Quốc','/category/manhua');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chapter_images`
--

DROP TABLE IF EXISTS `chapter_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chapter_images` (
  `ImageID` int NOT NULL AUTO_INCREMENT,
  `ChapterID` int NOT NULL,
  `ImageURL` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `SortOrder` int NOT NULL,
  PRIMARY KEY (`ImageID`),
  KEY `ChapterID` (`ChapterID`),
  CONSTRAINT `chapter_images_ibfk_1` FOREIGN KEY (`ChapterID`) REFERENCES `chapters` (`ChapterID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chapter_images`
--

LOCK TABLES `chapter_images` WRITE;
/*!40000 ALTER TABLE `chapter_images` DISABLE KEYS */;
INSERT INTO `chapter_images` VALUES (6,5,'uploads/chapters/1767707481_695d135987522.png',0),(7,5,'uploads/chapters/1767707481_695d13598878d.png',1),(8,5,'uploads/chapters/1767707481_695d135989410.png',2),(9,5,'uploads/chapters/1767707481_695d13598a296.png',3),(10,5,'uploads/chapters/1767707481_695d13598ada1.png',4),(11,5,'uploads/chapters/1767707481_695d13598b932.png',5),(12,5,'uploads/chapters/1767707860_695d14d4cfcaa.png',6),(13,4,'uploads/chapters/1767708088_695d15b850138.png',0),(14,4,'uploads/chapters/1767708088_695d15b850cad.png',1),(15,4,'uploads/chapters/1767708088_695d15b8518b1.png',2),(16,4,'uploads/chapters/1767708088_695d15b8523e9.png',3),(17,4,'uploads/chapters/1767708088_695d15b852c77.png',4),(18,4,'uploads/chapters/1767708088_695d15b853767.png',5),(19,1,'uploads/chapters/1767708232_695d16483378c.png',0),(20,1,'uploads/chapters/1767708232_695d1648342ad.png',1),(21,1,'uploads/chapters/1767708232_695d164834f6b.png',2),(22,1,'uploads/chapters/1767708232_695d16483639b.png',3),(23,1,'uploads/chapters/1767708232_695d16483748b.png',4),(24,1,'uploads/chapters/1767708232_695d164838342.png',5),(25,2,'uploads/chapters/1767708346_695d16bad7565.png',0),(26,2,'uploads/chapters/1767708346_695d16bad822c.jpg',1),(27,2,'uploads/chapters/1767708346_695d16bad8ce6.jpg',2),(28,2,'uploads/chapters/1767708346_695d16bad966b.jpg',3),(29,2,'uploads/chapters/1767708346_695d16bada214.jfif',4),(30,2,'uploads/chapters/1767708346_695d16badaab8.jfif',5),(31,2,'uploads/chapters/1767708346_695d16badb50f.png',6),(32,2,'uploads/chapters/1767708346_695d16badbd3c.jpg',7),(33,2,'uploads/chapters/1767708346_695d16badc541.jpg',8),(34,2,'uploads/chapters/1767708346_695d16badcd7a.jpg',9),(35,2,'uploads/chapters/1767708346_695d16badd8fa.jpg',10),(36,2,'uploads/chapters/1767708346_695d16bade142.jpg',11),(37,2,'uploads/chapters/1767708346_695d16bade8a7.png',12),(38,2,'uploads/chapters/1767708346_695d16badefd4.jpg',13),(39,2,'uploads/chapters/1767708346_695d16badf73b.png',14),(40,2,'uploads/chapters/1767708346_695d16badfe22.png',15),(41,2,'uploads/chapters/1767708346_695d16bae04ab.png',16),(42,2,'uploads/chapters/1767708346_695d16bae0bc4.png',17),(43,2,'uploads/chapters/1767708346_695d16bae139d.png',18),(44,2,'uploads/chapters/1767708346_695d16bae1ac6.png',19),(45,3,'uploads/chapters/1767708528_695d1770c6bb2.png',0),(46,3,'uploads/chapters/1767708528_695d1770c7888.webp',1),(47,3,'uploads/chapters/1767708528_695d1770c862d.jpg',2),(48,3,'uploads/chapters/1767708528_695d1770c90d2.jpg',3),(49,3,'uploads/chapters/1767708528_695d1770c9c58.jpg',4),(50,3,'uploads/chapters/1767708528_695d1770ca542.jpg',5),(51,3,'uploads/chapters/1767708528_695d1770cb166.jfif',6),(52,3,'uploads/chapters/1767708528_695d1770cb931.jfif',7),(53,3,'uploads/chapters/1767708528_695d1770cc5e2.jfif',8),(54,3,'uploads/chapters/1767708528_695d1770ccea8.jfif',9),(55,3,'uploads/chapters/1767708528_695d1770cd78a.jfif',10),(56,3,'uploads/chapters/1767708528_695d1770cdfa0.jfif',11),(57,3,'uploads/chapters/1767708528_695d1770ce7f0.jfif',12),(58,3,'uploads/chapters/1767708528_695d1770cf0b8.jfif',13),(59,3,'uploads/chapters/1767708528_695d1770cf927.jfif',14),(60,3,'uploads/chapters/1767708528_695d1770d0264.jfif',15),(61,3,'uploads/chapters/1767708528_695d1770d0a7c.jpg',16),(62,3,'uploads/chapters/1767708528_695d1770d12e7.png',17),(63,3,'uploads/chapters/1767708528_695d1770d1a3e.png',18),(64,3,'uploads/chapters/1767708528_695d1770d2358.webp',19),(65,6,'uploads/chapters/1767716541_695d36bde95e7.png',0),(66,6,'uploads/chapters/1767716541_695d36bdea239.png',1),(67,6,'uploads/chapters/1767716541_695d36bdeac8c.png',2),(68,6,'uploads/chapters/1767716541_695d36bdeb4a3.png',3),(69,6,'uploads/chapters/1767716541_695d36bdebd89.png',4),(70,6,'uploads/chapters/1767716541_695d36bdec65f.png',5),(77,7,'https://res.cloudinary.com/dhefmthim/image/upload/v1767879492/chapters/o808n6jhxqjr4rni1svo.png',0),(78,7,'https://res.cloudinary.com/dhefmthim/image/upload/v1767879498/chapters/h3nurb1taglvaoj3uqdw.jpg',1),(79,7,'https://res.cloudinary.com/dhefmthim/image/upload/v1767879504/chapters/aoqvt3mb7mfylvrme6r8.jpg',2),(80,7,'https://res.cloudinary.com/dhefmthim/image/upload/v1767879519/chapters/cphblt11diqe8jryvm1i.jpg',3),(81,7,'https://res.cloudinary.com/dhefmthim/image/upload/v1767879573/chapters/uzpv9xyrmfprbrslaxfg.png',4),(82,8,'https://res.cloudinary.com/dhefmthim/image/upload/v1767940604/chapters/sdprsnm6q2z59qafuu0g.png',0),(83,8,'https://res.cloudinary.com/dhefmthim/image/upload/v1767940607/chapters/dcdit1lrjjlh4bw15ac5.png',1),(84,8,'https://res.cloudinary.com/dhefmthim/image/upload/v1767940610/chapters/wypmddscaqarkqmx85ac.png',2),(85,8,'https://res.cloudinary.com/dhefmthim/image/upload/v1767940612/chapters/svlabhkokmpc1r0emxiy.png',3),(86,8,'https://res.cloudinary.com/dhefmthim/image/upload/v1767940615/chapters/bkx7rx6umnrfo5kf1n2r.png',4),(87,8,'https://res.cloudinary.com/dhefmthim/image/upload/v1767940618/chapters/ex9solobuwxpfpprykvg.png',5),(88,8,'https://res.cloudinary.com/dhefmthim/image/upload/v1767940621/chapters/hhlx07hvxvhmn6vnrx4d.png',6);
/*!40000 ALTER TABLE `chapter_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chapters`
--

DROP TABLE IF EXISTS `chapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chapters` (
  `ChapterID` int NOT NULL AUTO_INCREMENT,
  `ArticleID` int NOT NULL,
  `Title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Index` float NOT NULL,
  `ViewCount` int DEFAULT '0',
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `IsDeleted` tinyint DEFAULT '0',
  PRIMARY KEY (`ChapterID`),
  KEY `ArticleID` (`ArticleID`),
  CONSTRAINT `chapters_ibfk_1` FOREIGN KEY (`ArticleID`) REFERENCES `articles` (`ArticleID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chapters`
--

LOCK TABLES `chapters` WRITE;
/*!40000 ALTER TABLE `chapters` DISABLE KEYS */;
INSERT INTO `chapters` VALUES (1,1,'Romance Dawn',1,5004,'2026-01-06 11:05:29',0),(2,1,'Luffy và Zoro',2,4801,'2026-01-06 11:05:29',0),(3,2,'Uzumaki Naruto!',1,6007,'2026-01-06 11:05:29',0),(4,3,'Kẻ mạnh nhất',3,3000,'2026-01-06 11:05:29',1),(5,3,'sggd',2,0,'2026-01-06 20:51:21',1),(6,4,'Hell Nah',1,1,'2026-01-06 23:22:21',0),(7,7,'fesg',1,1,'2026-01-07 00:27:01',0),(8,8,'Uma Test',1,1,'2026-01-09 13:36:34',0);
/*!40000 ALTER TABLE `chapters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `CommentID` int NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  `ArticleID` int NOT NULL,
  `ChapterID` int DEFAULT NULL,
  `Content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `IsDeleted` tinyint DEFAULT '0',
  PRIMARY KEY (`CommentID`),
  KEY `UserID` (`UserID`),
  KEY `ArticleID` (`ArticleID`),
  KEY `ChapterID` (`ChapterID`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`ArticleID`) REFERENCES `articles` (`ArticleID`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`ChapterID`) REFERENCES `chapters` (`ChapterID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (1,2,1,NULL,'Truyện hay quá, hóng chap mới!','2026-01-06 11:05:29',0),(2,3,1,1,'Chap mở đầu huyền thoại.','2026-01-06 11:05:29',0),(3,1,2,NULL,'Bộ này kết thúc viên mãn.','2026-01-06 11:05:29',0),(4,4,1,NULL,'dsgdssd','2026-01-06 21:50:50',0),(5,4,1,NULL,'dsgdgsdg','2026-01-06 21:51:02',0),(6,4,1,1,'dthdhdhf','2026-01-06 21:58:12',0),(7,4,1,2,'thiên lý ơi','2026-01-06 21:59:50',0),(8,1,6,NULL,'omg','2026-01-07 11:52:43',0),(9,1,1,NULL,'ua alo','2026-01-07 16:54:22',0),(10,5,4,NULL,'vòng xe lăn bánh','2026-01-08 18:47:51',0),(11,1,7,NULL,'bjk','2026-01-08 20:53:53',0);
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `genres`
--

DROP TABLE IF EXISTS `genres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `genres` (
  `GenreID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `Description` text COLLATE utf8mb4_unicode_ci,
  `IsDeleted` tinyint DEFAULT '0',
  PRIMARY KEY (`GenreID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `genres`
--

LOCK TABLES `genres` WRITE;
/*!40000 ALTER TABLE `genres` DISABLE KEYS */;
INSERT INTO `genres` VALUES (1,'Action','Thể loại hành động, kịch tính.',0),(2,'Adventure','Những cuộc phiêu lưu khám phá thế giới.',0),(3,'Comedy','Hài hước, giải trí.',0),(4,'Drama','Cốt truyện sâu sắc, tâm lý.',0),(5,'Fantasy','Thế giới giả tưởng, phép thuật.',0),(7,'Xe Lăn',NULL,0);
/*!40000 ALTER TABLE `genres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `history` (
  `HistoryID` int NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  `ArticleID` int NOT NULL,
  `ChapterID` int NOT NULL,
  `LastReadAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`HistoryID`),
  UNIQUE KEY `User_Article_Unique` (`UserID`,`ArticleID`),
  KEY `UserID` (`UserID`),
  KEY `ArticleID` (`ArticleID`),
  KEY `ChapterID` (`ChapterID`),
  CONSTRAINT `history_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  CONSTRAINT `history_ibfk_2` FOREIGN KEY (`ArticleID`) REFERENCES `articles` (`ArticleID`) ON DELETE CASCADE,
  CONSTRAINT `history_ibfk_3` FOREIGN KEY (`ChapterID`) REFERENCES `chapters` (`ChapterID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history`
--

LOCK TABLES `history` WRITE;
/*!40000 ALTER TABLE `history` DISABLE KEYS */;
INSERT INTO `history` VALUES (1,2,1,2,'2026-01-06 11:05:29'),(2,3,2,3,'2026-01-06 11:05:29');
/*!40000 ALTER TABLE `history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `UserID` int NOT NULL AUTO_INCREMENT,
  `UserName` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Avatar` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Role` tinyint DEFAULT '0',
  `IsDeleted` tinyint DEFAULT '0',
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `UserName` (`UserName`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@manga.com','123456','https://res.cloudinary.com/dhefmthim/image/upload/v1767876433/avatars/g534xgssgauvyhanbcgs.png',1,0,'2026-01-06 11:05:29'),(2,'nguoidoc01','user1@gmail.com','hashed_pass_456','uploads/avatars/1767718133_695d3cf586583.png',0,0,'2026-01-06 11:05:29'),(3,'manga_fan','fan2024@gmail.com','hashed_pass_789','uploads/avatars/1767718147_695d3d03aaf0b.webp',0,0,'2026-01-06 11:05:29'),(4,'boka','halan123@gmail.com','123456',NULL,0,0,'2026-01-06 21:44:06'),(5,'boka1','dfhfh@gmail.com','123456','uploads/avatars/1767872844_1269715.jpg',1,0,'2026-01-08 18:45:25');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-09 13:46:00
