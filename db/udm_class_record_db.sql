-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: udm_class_record_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `udm_class_record_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `udm_class_record_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `udm_class_record_db`;

--
-- Table structure for table `backup_history`
--

DROP TABLE IF EXISTS `backup_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `action_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `action_type` enum('export','import') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `status` enum('success','failed') NOT NULL,
  `message` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `action_timestamp` (`action_timestamp`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_history`
--

LOCK TABLES `backup_history` WRITE;
/*!40000 ALTER TABLE `backup_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calculated_period_grades`
--

DROP TABLE IF EXISTS `calculated_period_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calculated_period_grades` (
  `period_grade_id` int(11) NOT NULL AUTO_INCREMENT,
  `enrollment_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `period` enum('Preliminary','Mid-Term','Pre-Final') DEFAULT NULL,
  `period_class_standing_grade` decimal(5,2) DEFAULT NULL,
  `period_examination_grade` decimal(5,2) DEFAULT NULL,
  `total_period_grade` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`period_grade_id`),
  UNIQUE KEY `enrollment_id` (`enrollment_id`,`class_id`,`period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calculated_period_grades`
--

LOCK TABLES `calculated_period_grades` WRITE;
/*!40000 ALTER TABLE `calculated_period_grades` DISABLE KEYS */;
/*!40000 ALTER TABLE `calculated_period_grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `grading_system_type` enum('numerical','final_only_numerical') DEFAULT 'numerical',
  PRIMARY KEY (`class_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `subject_id` (`subject_id`),
  KEY `section_id` (`section_id`),
  CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`),
  CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`),
  CONSTRAINT `classes_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (4,2,3,4,'final_only_numerical'),(5,2,3,3,'numerical'),(6,3,4,6,'final_only_numerical'),(10,1,1,4,'final_only_numerical'),(11,1,3,3,'numerical'),(12,1,3,5,'final_only_numerical'),(13,3,5,6,'numerical'),(15,3,4,7,'');
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`enrollment_id`),
  UNIQUE KEY `student_id` (`student_id`,`class_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=769 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enrollments`
--

LOCK TABLES `enrollments` WRITE;
/*!40000 ALTER TABLE `enrollments` DISABLE KEYS */;
INSERT INTO `enrollments` VALUES (165,1,4),(276,1,6),(498,1,10),(166,2,4),(277,2,6),(499,2,10),(167,3,4),(278,3,6),(500,3,10),(168,4,4),(279,4,6),(501,4,10),(169,5,4),(280,5,6),(502,5,10),(170,6,4),(281,6,6),(503,6,10),(171,7,4),(282,7,6),(504,7,10),(172,8,4),(283,8,6),(505,8,10),(173,9,4),(284,9,6),(506,9,10),(174,10,4),(285,10,6),(507,10,10),(175,11,4),(286,11,6),(508,11,10),(176,12,4),(287,12,6),(509,12,10),(177,13,4),(288,13,6),(510,13,10),(178,14,4),(289,14,6),(511,14,10),(179,15,4),(290,15,6),(512,15,10),(180,16,4),(291,16,6),(513,16,10),(181,17,4),(292,17,6),(514,17,10),(182,18,4),(293,18,6),(515,18,10),(183,19,4),(294,19,6),(516,19,10),(184,20,4),(295,20,6),(517,20,10),(185,21,4),(296,21,6),(518,21,10),(186,22,4),(297,22,6),(519,22,10),(187,23,4),(298,23,6),(520,23,10),(188,24,4),(299,24,6),(521,24,10),(189,25,4),(300,25,6),(522,25,10),(190,26,4),(301,26,6),(523,26,10),(191,27,4),(302,27,6),(524,27,10),(192,28,4),(303,28,6),(525,28,10),(193,29,4),(304,29,6),(526,29,10),(194,30,4),(305,30,6),(527,30,10),(195,31,4),(306,31,6),(528,31,10),(196,32,4),(307,32,6),(529,32,10),(197,33,4),(308,33,6),(530,33,10),(198,34,4),(309,34,6),(531,34,10),(199,35,4),(310,35,6),(532,35,10),(200,36,4),(311,36,6),(533,36,10),(201,37,4),(312,37,6),(534,37,10),(202,38,4),(313,38,6),(535,38,10),(203,39,4),(314,39,6),(536,39,10),(662,39,13),(204,40,4),(315,40,6),(537,40,10),(205,41,4),(316,41,6),(538,41,10),(206,42,4),(317,42,6),(539,42,10),(207,43,4),(318,43,6),(540,43,10),(208,44,4),(319,44,6),(541,44,10),(209,45,4),(320,45,6),(542,45,10),(210,46,4),(321,46,6),(543,46,10),(211,47,4),(322,47,6),(544,47,10),(212,48,4),(323,48,6),(545,48,10),(213,49,4),(324,49,6),(546,49,10),(214,50,4),(325,50,6),(547,50,10),(215,51,4),(326,51,6),(548,51,10),(216,52,4),(327,52,6),(549,52,10),(217,53,4),(328,53,6),(550,53,10),(218,54,4),(329,54,6),(551,54,10),(609,55,12),(716,55,15),(610,56,12),(717,56,15),(611,57,12),(718,57,15),(612,58,12),(719,58,15),(613,59,12),(720,59,15),(614,60,12),(721,60,15),(615,61,12),(722,61,15),(616,62,12),(723,62,15),(617,63,12),(724,63,15),(618,64,12),(725,64,15),(619,65,12),(726,65,15),(620,66,12),(727,66,15),(621,67,12),(728,67,15),(622,68,12),(729,68,15),(623,69,12),(730,69,15),(624,70,12),(731,70,15),(625,71,12),(732,71,15),(626,72,12),(733,72,15),(627,73,12),(734,73,15),(628,74,12),(735,74,15),(629,75,12),(736,75,15),(630,76,12),(737,76,15),(631,77,12),(738,77,15),(632,78,12),(739,78,15),(633,79,12),(740,79,15),(634,80,12),(741,80,15),(635,81,12),(742,81,15),(636,82,12),(743,82,15),(637,83,12),(744,83,15),(638,84,12),(745,84,15),(639,85,12),(746,85,15),(640,86,12),(747,86,15),(641,87,12),(748,87,15),(642,88,12),(749,88,15),(643,89,12),(750,89,15),(644,90,12),(751,90,15),(645,91,12),(752,91,15),(646,92,12),(753,92,15),(647,93,12),(754,93,15),(648,94,12),(755,94,15),(649,95,12),(756,95,15),(650,96,12),(757,96,15),(651,97,12),(758,97,15),(652,98,12),(759,98,15),(653,99,12),(760,99,15),(654,100,12),(761,100,15),(655,101,12),(762,101,15),(656,102,12),(763,102,15),(657,103,12),(764,103,15),(658,104,12),(765,104,15),(659,105,12),(766,105,15),(660,106,12),(767,106,15),(661,107,12),(768,107,15),(219,108,5),(552,108,11),(220,109,5),(553,109,11),(221,110,5),(554,110,11),(222,111,5),(555,111,11),(223,112,5),(556,112,11),(224,113,5),(557,113,11),(225,114,5),(558,114,11),(226,115,5),(559,115,11),(227,116,5),(560,116,11),(228,117,5),(561,117,11),(229,118,5),(562,118,11),(230,119,5),(563,119,11),(231,120,5),(564,120,11),(232,121,5),(565,121,11),(233,122,5),(566,122,11),(234,123,5),(567,123,11),(235,124,5),(568,124,11),(236,125,5),(569,125,11),(237,126,5),(570,126,11),(238,127,5),(571,127,11),(239,128,5),(572,128,11),(240,129,5),(573,129,11),(241,130,5),(574,130,11),(242,131,5),(575,131,11),(243,132,5),(576,132,11),(244,133,5),(577,133,11),(245,134,5),(578,134,11),(246,135,5),(579,135,11),(247,136,5),(580,136,11),(248,137,5),(581,137,11),(249,138,5),(582,138,11),(250,139,5),(583,139,11),(251,140,5),(584,140,11),(252,141,5),(585,141,11),(253,142,5),(586,142,11),(254,143,5),(587,143,11),(255,144,5),(588,144,11),(256,145,5),(589,145,11),(257,146,5),(590,146,11),(258,147,5),(591,147,11),(259,148,5),(592,148,11),(260,149,5),(593,149,11),(261,150,5),(594,150,11),(262,151,5),(595,151,11),(263,152,5),(596,152,11),(264,153,5),(597,153,11),(265,154,5),(598,154,11),(266,155,5),(599,155,11),(267,156,5),(600,156,11),(268,157,5),(601,157,11),(269,158,5),(602,158,11),(270,159,5),(603,159,11),(271,160,5),(604,160,11),(272,161,5),(605,161,11),(273,162,5),(606,162,11),(274,163,5),(607,163,11),(275,164,5),(608,164,11);
/*!40000 ALTER TABLE `enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `final_grades`
--

DROP TABLE IF EXISTS `final_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `final_grades` (
  `final_grade_id` int(11) NOT NULL AUTO_INCREMENT,
  `enrollment_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `overall_final_grade` decimal(5,2) DEFAULT NULL,
  `remarks` varchar(20) DEFAULT NULL,
  `final_change_timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`final_grade_id`),
  UNIQUE KEY `enrollment_id` (`enrollment_id`,`class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=717 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `final_grades`
--

LOCK TABLES `final_grades` WRITE;
/*!40000 ALTER TABLE `final_grades` DISABLE KEYS */;
INSERT INTO `final_grades` VALUES (715,276,6,80.00,'Satisfactory','2025-05-30 01:13:10');
/*!40000 ALTER TABLE `final_grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grade_components`
--

DROP TABLE IF EXISTS `grade_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grade_components` (
  `component_id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) DEFAULT NULL,
  `component_name` varchar(100) DEFAULT NULL,
  `period` enum('Preliminary','Mid-Term','Pre-Final') DEFAULT NULL,
  `type` enum('Class Standing','Examination','Attendance','Quiz','Exam','Assignment','Project','Recitation','Participation','Other') DEFAULT NULL,
  `max_score` decimal(5,2) DEFAULT NULL,
  `is_attendance_based` tinyint(1) DEFAULT 0,
  `is_locked` tinyint(1) DEFAULT 0,
  `weight` decimal(5,2) DEFAULT 0.00,
  PRIMARY KEY (`component_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `grade_components_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grade_components`
--

LOCK TABLES `grade_components` WRITE;
/*!40000 ALTER TABLE `grade_components` DISABLE KEYS */;
INSERT INTO `grade_components` VALUES (15,4,'Attendance - Preliminary','Preliminary','',0.00,1,0,0.00),(16,4,'Attendance - Mid-Term','Mid-Term','',0.00,1,0,0.00),(37,10,'Attendance - Preliminary','Preliminary','',0.00,1,0,0.00),(38,10,'Attendance - Mid-Term','Mid-Term','',0.00,1,1,0.00),(39,10,'Pre-Final Grade','Pre-Final','',100.00,0,1,0.00),(40,12,'Attendance - Preliminary','Preliminary','',0.00,1,0,0.00),(41,12,'Attendance - Mid-Term','Mid-Term','',0.00,1,1,0.00),(42,12,'Pre-Final Grade','Pre-Final','',100.00,0,1,0.00),(44,6,'Prelim','Preliminary','',0.00,1,0,0.00),(45,6,'Midterm','Mid-Term','',0.00,1,0,0.00),(46,6,'Final','Pre-Final','Class Standing',100.00,0,0,100.00),(51,13,'Quiz 1','Preliminary','Quiz',100.00,0,0,50.00),(52,13,'Quiz 2','Mid-Term','Quiz',100.00,0,0,50.00),(53,13,'Quiz 3','Pre-Final','Quiz',100.00,0,0,50.00),(57,15,'Attendance - Preliminary','Preliminary','',0.00,1,0,0.00),(58,15,'Attendance - Mid-Term','Mid-Term','',0.00,1,1,0.00),(59,15,'Pre-Final Grade','Pre-Final','',100.00,0,1,0.00),(60,11,'Quiz 2','Preliminary','Quiz',100.00,0,0,60.00);
/*!40000 ALTER TABLE `grade_components` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grade_history`
--

DROP TABLE IF EXISTS `grade_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grade_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `grade_type` varchar(100) NOT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) NOT NULL,
  `change_timestamp` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`history_id`),
  KEY `class_id` (`class_id`),
  KEY `enrollment_id` (`enrollment_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `grade_history_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `grade_history_ibfk_2` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`) ON DELETE CASCADE,
  CONSTRAINT `grade_history_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3373 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grade_history`
--

LOCK TABLES `grade_history` WRITE;
/*!40000 ALTER TABLE `grade_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `grade_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grade_weights`
--

DROP TABLE IF EXISTS `grade_weights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grade_weights` (
  `weight_id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `component_name` varchar(100) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  PRIMARY KEY (`weight_id`),
  UNIQUE KEY `class_id` (`class_id`,`component_name`),
  CONSTRAINT `grade_weights_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grade_weights`
--

LOCK TABLES `grade_weights` WRITE;
/*!40000 ALTER TABLE `grade_weights` DISABLE KEYS */;
/*!40000 ALTER TABLE `grade_weights` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grades`
--

DROP TABLE IF EXISTS `grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grades` (
  `grade_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `assignment_name` varchar(255) DEFAULT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `a_na_grade` enum('A','NA') DEFAULT NULL,
  `grade_percentage` decimal(5,2) DEFAULT NULL,
  `midterm_grade` decimal(5,2) DEFAULT NULL,
  `final_grade` decimal(5,2) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `date_recorded` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`grade_id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grades`
--

LOCK TABLES `grades` WRITE;
/*!40000 ALTER TABLE `grades` DISABLE KEYS */;
/*!40000 ALTER TABLE `grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `note_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `note_content` text NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`note_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes`
--

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL AUTO_INCREMENT,
  `section_name` varchar(50) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`section_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sections`
--

LOCK TABLES `sections` WRITE;
/*!40000 ALTER TABLE `sections` DISABLE KEYS */;
INSERT INTO `sections` VALUES (3,'BSIT-11','',''),(4,'COE-41','',''),(5,'COE-42','',''),(6,'COE-41','2024-2025','2nd Semester'),(7,'COE-42','2024-2025','2nd Semester');
/*!40000 ALTER TABLE `sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_grades`
--

DROP TABLE IF EXISTS `student_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_grades` (
  `grade_id` int(11) NOT NULL AUTO_INCREMENT,
  `enrollment_id` int(11) DEFAULT NULL,
  `component_id` int(11) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `attendance_status_prelim` enum('A','NA') DEFAULT NULL,
  `attendance_status_midterm` enum('A','NA') DEFAULT NULL,
  `attendance_status` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`grade_id`),
  UNIQUE KEY `enrollment_id` (`enrollment_id`,`component_id`),
  KEY `component_id` (`component_id`),
  CONSTRAINT `student_grades_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`),
  CONSTRAINT `student_grades_ibfk_2` FOREIGN KEY (`component_id`) REFERENCES `grade_components` (`component_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3231 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_grades`
--

LOCK TABLES `student_grades` WRITE;
/*!40000 ALTER TABLE `student_grades` DISABLE KEYS */;
INSERT INTO `student_grades` VALUES (3057,662,51,75.00,NULL,NULL,NULL),(3058,662,52,80.00,NULL,NULL,NULL),(3059,662,53,90.00,NULL,NULL,NULL),(3066,276,44,NULL,'A',NULL,NULL),(3067,276,45,NULL,'A','A',NULL),(3068,277,44,NULL,'A',NULL,NULL),(3069,277,45,NULL,'A','A',NULL),(3070,278,44,NULL,'A',NULL,NULL),(3071,278,45,NULL,'A','A',NULL),(3072,279,44,NULL,'A',NULL,NULL),(3073,279,45,NULL,'A','A',NULL),(3074,280,44,NULL,'A',NULL,NULL),(3075,280,45,NULL,'A','A',NULL),(3076,281,44,NULL,'A',NULL,NULL),(3077,281,45,NULL,'A','A',NULL),(3078,282,44,NULL,'A',NULL,NULL),(3079,282,45,NULL,'A','A',NULL),(3080,283,44,NULL,'A',NULL,NULL),(3081,283,45,NULL,'A','A',NULL),(3082,284,44,NULL,'A',NULL,NULL),(3083,284,45,NULL,'A','A',NULL),(3084,285,44,NULL,'A',NULL,NULL),(3085,285,45,NULL,'A','A',NULL),(3086,286,44,NULL,'A',NULL,NULL),(3087,286,45,NULL,'A','A',NULL),(3088,287,44,NULL,'A',NULL,NULL),(3089,287,45,NULL,'A','A',NULL),(3090,288,44,NULL,'A',NULL,NULL),(3091,288,45,NULL,'A','A',NULL),(3092,289,44,NULL,'A',NULL,NULL),(3093,289,45,NULL,'A','A',NULL),(3094,290,44,NULL,'A',NULL,NULL),(3095,290,45,NULL,'A','A',NULL),(3096,291,44,NULL,'A',NULL,NULL),(3097,291,45,NULL,'A','A',NULL),(3098,292,44,NULL,'A',NULL,NULL),(3099,292,45,NULL,'A','A',NULL),(3100,293,44,NULL,'A',NULL,NULL),(3101,293,45,NULL,'A','A',NULL),(3102,294,44,NULL,'A',NULL,NULL),(3103,294,45,NULL,'A','A',NULL),(3104,295,44,NULL,'A',NULL,NULL),(3105,295,45,NULL,'A','A',NULL),(3106,296,44,NULL,'A',NULL,NULL),(3107,296,45,NULL,'A','A',NULL),(3108,297,44,NULL,'A',NULL,NULL),(3109,297,45,NULL,'A','A',NULL),(3110,298,44,NULL,'A',NULL,NULL),(3111,298,45,NULL,'A','A',NULL),(3112,299,44,NULL,'A',NULL,NULL),(3113,299,45,NULL,'A','A',NULL),(3114,300,44,NULL,'A',NULL,NULL),(3115,300,45,NULL,'A','A',NULL),(3116,301,44,NULL,'A',NULL,NULL),(3117,301,45,NULL,'A','A',NULL),(3118,302,44,NULL,'A',NULL,NULL),(3119,302,45,NULL,'A','A',NULL),(3120,303,44,NULL,'A',NULL,NULL),(3121,303,45,NULL,'A','A',NULL),(3122,304,44,NULL,'A',NULL,NULL),(3123,304,45,NULL,'A','A',NULL),(3124,305,44,NULL,'A',NULL,NULL),(3125,305,45,NULL,'A','A',NULL),(3126,306,44,NULL,'A',NULL,NULL),(3127,306,45,NULL,'A','A',NULL),(3128,307,44,NULL,'A',NULL,NULL),(3129,307,45,NULL,'A','A',NULL),(3130,308,44,NULL,'A',NULL,NULL),(3131,308,45,NULL,'A','A',NULL),(3132,309,44,NULL,'A',NULL,NULL),(3133,309,45,NULL,'A','A',NULL),(3134,310,44,NULL,'A',NULL,NULL),(3135,310,45,NULL,'A','A',NULL),(3136,311,44,NULL,'A',NULL,NULL),(3137,311,45,NULL,'A','A',NULL),(3138,312,44,NULL,'A',NULL,NULL),(3139,312,45,NULL,'A','A',NULL),(3140,313,44,NULL,'A',NULL,NULL),(3141,313,45,NULL,'A','A',NULL),(3142,314,44,NULL,'A',NULL,NULL),(3143,314,45,NULL,'A','A',NULL),(3144,315,44,NULL,'A',NULL,NULL),(3145,315,45,NULL,'A','A',NULL),(3146,316,44,NULL,'A',NULL,NULL),(3147,316,45,NULL,'A','A',NULL),(3148,317,44,NULL,'A',NULL,NULL),(3149,317,45,NULL,'A','A',NULL),(3150,318,44,NULL,'A',NULL,NULL),(3151,318,45,NULL,'A','A',NULL),(3152,319,44,NULL,'A',NULL,NULL),(3153,319,45,NULL,'A','A',NULL),(3154,320,44,NULL,'A',NULL,NULL),(3155,320,45,NULL,'A','A',NULL),(3156,321,44,NULL,'A',NULL,NULL),(3157,321,45,NULL,'A','A',NULL),(3158,322,44,NULL,'A',NULL,NULL),(3159,322,45,NULL,'A','A',NULL),(3160,323,44,NULL,'A',NULL,NULL),(3161,323,45,NULL,'A','A',NULL),(3162,324,44,NULL,'A',NULL,NULL),(3163,324,45,NULL,'A','A',NULL),(3164,325,44,NULL,'A',NULL,NULL),(3165,325,45,NULL,'A','A',NULL),(3166,326,44,NULL,'A',NULL,NULL),(3167,326,45,NULL,'A','A',NULL),(3168,327,44,NULL,'A',NULL,NULL),(3169,327,45,NULL,'A','A',NULL),(3170,328,44,NULL,'A',NULL,NULL),(3171,328,45,NULL,'A','A',NULL),(3172,329,44,NULL,'A',NULL,NULL),(3173,329,45,NULL,'A','A',NULL),(3174,552,60,50.00,NULL,NULL,NULL),(3175,553,60,50.00,NULL,NULL,NULL),(3176,554,60,50.00,NULL,NULL,NULL),(3177,555,60,50.00,NULL,NULL,NULL),(3178,556,60,NULL,NULL,NULL,NULL),(3179,557,60,NULL,NULL,NULL,NULL),(3180,558,60,NULL,NULL,NULL,NULL),(3181,559,60,NULL,NULL,NULL,NULL),(3182,560,60,NULL,NULL,NULL,NULL),(3183,561,60,NULL,NULL,NULL,NULL),(3184,562,60,NULL,NULL,NULL,NULL),(3185,563,60,NULL,NULL,NULL,NULL),(3186,564,60,NULL,NULL,NULL,NULL),(3187,565,60,NULL,NULL,NULL,NULL),(3188,566,60,NULL,NULL,NULL,NULL),(3189,567,60,NULL,NULL,NULL,NULL),(3190,568,60,NULL,NULL,NULL,NULL),(3191,569,60,NULL,NULL,NULL,NULL),(3192,570,60,NULL,NULL,NULL,NULL),(3193,571,60,NULL,NULL,NULL,NULL),(3194,572,60,NULL,NULL,NULL,NULL),(3195,573,60,NULL,NULL,NULL,NULL),(3196,574,60,NULL,NULL,NULL,NULL),(3197,575,60,NULL,NULL,NULL,NULL),(3198,576,60,NULL,NULL,NULL,NULL),(3199,577,60,NULL,NULL,NULL,NULL),(3200,578,60,NULL,NULL,NULL,NULL),(3201,579,60,NULL,NULL,NULL,NULL),(3202,580,60,NULL,NULL,NULL,NULL),(3203,581,60,NULL,NULL,NULL,NULL),(3204,582,60,NULL,NULL,NULL,NULL),(3205,583,60,NULL,NULL,NULL,NULL),(3206,584,60,NULL,NULL,NULL,NULL),(3207,585,60,NULL,NULL,NULL,NULL),(3208,586,60,NULL,NULL,NULL,NULL),(3209,587,60,NULL,NULL,NULL,NULL),(3210,588,60,NULL,NULL,NULL,NULL),(3211,589,60,NULL,NULL,NULL,NULL),(3212,590,60,NULL,NULL,NULL,NULL),(3213,591,60,NULL,NULL,NULL,NULL),(3214,592,60,NULL,NULL,NULL,NULL),(3215,593,60,NULL,NULL,NULL,NULL),(3216,594,60,NULL,NULL,NULL,NULL),(3217,595,60,NULL,NULL,NULL,NULL),(3218,596,60,NULL,NULL,NULL,NULL),(3219,597,60,NULL,NULL,NULL,NULL),(3220,598,60,NULL,NULL,NULL,NULL),(3221,599,60,NULL,NULL,NULL,NULL),(3222,600,60,NULL,NULL,NULL,NULL),(3223,601,60,NULL,NULL,NULL,NULL),(3224,602,60,NULL,NULL,NULL,NULL),(3225,603,60,NULL,NULL,NULL,NULL),(3226,604,60,NULL,NULL,NULL,NULL),(3227,605,60,NULL,NULL,NULL,NULL),(3228,606,60,NULL,NULL,NULL,NULL),(3229,607,60,NULL,NULL,NULL,NULL),(3230,608,60,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `student_grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_number` varchar(20) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `student_number` (`student_number`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,'21-14-001','ABESAMIS','KHEN',NULL),(2,'21-14-003','ALAMA','XANDER',NULL),(3,'21-14-097','ALDAVE','ALI LUIS',NULL),(4,'21-14-100','AQUINO','KIM MAYNARD',NULL),(5,'21-14-094','ARCEO','ELIZABETH',NULL),(6,'21-14-080','ARGUILLES','JOSHUA',NULL),(7,'21-14-102','ASUNCION','ISAIAH',NULL),(8,'21-14-018','AUSTRIA','FRIENDRICH ALLAIN',NULL),(9,'21-14-008','BARON','VINCENT JUDE',NULL),(10,'21-14-009','BICALDO','EDUARD JOHN',NULL),(11,'21-14-010','BUENAFE','ARL CHRISTOPHER',NULL),(12,'21-14-011','CAJIGAL','PATRICE MARICO',NULL),(13,'21-14-104','CORMINAL','CHRISTINE JOY',NULL),(14,'21-14-106','DE','DIOS	DAVE JOSE',NULL),(15,'21-14-014','DELA','CRUZ	MARVIN ANGELO',NULL),(16,'21-14-088','DELA','CRUZ	TRECIA MAE',NULL),(17,'21-14-015','DELOS','SANTOS	RISA MAY',NULL),(18,'21-14-020','DOMDOM','AMANDA LOUISE',NULL),(19,'21-14-022','DRIO','RACHEL ADELINE',NULL),(20,'21-14-110','ESTOQUE','MECAELA',NULL),(21,'21-14-111','FADOL','MC NICOLE',NULL),(22,'21-14-112','GALLENERA','JOCELYN',NULL),(23,'21-14-026','GERARDO','AMY ROSE',NULL),(24,'21-14-027','GERMAN','SOFIA',NULL),(25,'21-14-114','GONZALES','EMERSON',NULL),(26,'21-14-089','GUASCH','KYLA',NULL),(27,'21-14-082','HERNANDEZ','IZAK HALEY',NULL),(28,'20-14-2041','ILEDAN','MARIA ELENA',NULL),(29,'21-14-116','ISLAO','JAYBERT',NULL),(30,'21-14-118','LAGUADOR','ROBERT VINCENT',NULL),(31,'21-14-119','LICAYAN','JOHN PHILIP',NULL),(32,'21-14-120','LINO','CARLO JV',NULL),(33,'21-14-091','MABULAC','MARCELINA',NULL),(34,'20-14-8680','MACASILLI','JASMIN MHAICEE',NULL),(35,'21-14-036','MACAYA','NHEIL CHRISTIAN JIREH',NULL),(36,'21-14-139','MENDOZA','MA. JULIA MANUEL',NULL),(37,'21-14-044','ORALLO','CHANEL VINCENT',NULL),(38,'21-14-123','PACLIAN','CHERISH MONIQUE',NULL),(39,'21-14-046','PALLASIGUE','ERIK JOSEF',NULL),(40,'21-14-125','PANGILINAN','JANSEN IVAN',NULL),(41,'21-14-048','PEÑAFLOR','RALPH ACE',NULL),(42,'21-14-050','PERA','JOHN PHILIP',NULL),(43,'21-14-051','PEREZ','JAN MARC',NULL),(44,'21-14-127','PRADO','EDIZON',NULL),(45,'21-14-128','QUIAMBAO','REIMHAR B.',NULL),(46,'21-14-130','RAMOS','KRISTIE MARNI',NULL),(47,'21-14-052','REVILLAS','HARLENE',NULL),(48,'21-14-053','ROGERO','CHRISTIAN ANDREW',NULL),(49,'21-14-133','SALVADOR','AARON ANGELO',NULL),(50,'21-14-057','SAN','JOSE	LYNRYNE',NULL),(51,'21-14-134','SANTIAGO','JR.	LARRY',NULL),(52,'21-14-058','SELES','DIVINE TRIXY',NULL),(53,'21-14-060','TAJO','KARL CEDRIC',NULL),(54,'21-14-072','VARGAS','IVAN CHRISTOPHER',NULL),(55,'21-14-002','AGABON','FRANCIS KHLYE',NULL),(56,'21-14-099','ANDRES','MARK FRODO',NULL),(57,'21-14-005','ANTONIO','ERICK JAMES',NULL),(58,'21-14-201','ARCEO','ALDRIN',NULL),(59,'21-14-101','ASUNCION','KEVIN VICKMAR',NULL),(60,'21-14-006','BABISTA','LLOYD CLARENCE',NULL),(61,'21-14-068','BOLECHE','JOHN PATRICK',NULL),(62,'21-14-103','CALAOAGAN','JOSHUA',NULL),(63,'21-14-081','CAÑETE','DHARHELL',NULL),(64,'21-14-087','CUMAYAS','JAMES ANGELO',NULL),(65,'21-14-069','DANTE','NEIL MICHAEL',NULL),(66,'21-14-108','DEL','ROSARIO	LUCY MAE',NULL),(67,'21-14-039','DELA','CRUZ	MARTIN LEI',NULL),(68,'21-14-030','DEPOSITAR','JOHN DWAIN SHARDEEP',NULL),(69,'21-14-016','DIAZ','JR	RICKY',NULL),(70,'21-14-019','DINEROS','JEREMY LEE',NULL),(71,'21-14-021','DOROIN','CYRUS',NULL),(72,'21-14-023','EVANGELIO','ALECZANDRA NICOLE',NULL),(73,'21-14-067','GABIOSA','RASHEED',NULL),(74,'21-14-096','GALIT','GERALD',NULL),(75,'21-14-073','GARCIA','DANMAR',NULL),(76,'21-14-025','GARCIA','FRANCHESKA',NULL),(77,'21-14-074','GERONIMO','KEITH ALLEN',NULL),(78,'21-14-115','GUTIERREZ','AIRA MAE',NULL),(79,'21-14-142','HERNANDO','RHAYANA EHRIKA',NULL),(80,'21-14-031','IBAÑEZ','JAMES BARON',NULL),(81,'21-14-032','JAVIER','JOHN ABRAHM',NULL),(82,'21-14-033','LABUNGRAY','KRIZZEL',NULL),(83,'21-14-084','LANUZO','JOHN LUIS',NULL),(84,'21-14-034','LO','ADRIAN',NULL),(85,'21-14-070','LOPEZ','II	RENIE MAR',NULL),(86,'21-14-077','MACARAIG','MARK JOSEPH',NULL),(87,'21-14-040','MATIMTIM','JOHN MICHAEL',NULL),(88,'20-14-4607','MISTADES','MARK ANDREI',NULL),(89,'21-14-122','ONOFRE','REDXYRELL',NULL),(90,'21-14-045','PALACIO','R-JAY',NULL),(91,'21-14-092','PALARCA','GILLAINE',NULL),(92,'21-14-079','PANGANIBAN','NEIL ALLEN',NULL),(93,'21-14-047','PANZUELO','LUCKY EMMANUEL',NULL),(94,'21-14-083','PASAGUE','JOHN ADNEL',NULL),(95,'21-14-049','PENOLIO','MICAH',NULL),(96,'21-14-078','PINEDA','JANN JHUDIELLE',NULL),(97,'21-14-140','QUIMPO','PATRICIA',NULL),(98,'21-14-071','QUINTO','KLAYROLL IVAN',NULL),(99,'21-14-131','REATAZAN','CHRISTOPHER',NULL),(100,'21-14-054','ROMULO','KHAIL MICO',NULL),(101,'21-14-076','SAYAT','MARK BRYAN',NULL),(102,'21-14-135','SISON','JOHN ALBERT',NULL),(103,'21-14-062','TAN','AARON VINCE',NULL),(104,'20-14-3742','TAZARTE','REYCHAEL',NULL),(105,'21-14-063','TENORIO','VANESSA',NULL),(106,'21-14-066','UMALI','CAMILLA NATHALIA',NULL),(107,'21-14-095','VALDEZ','LUIS ANTONIO',NULL),(108,'24-22-011','ABUNGAN','RHEIN LASH',NULL),(109,'24-22-039','AMAGO','REYZAH MAY',NULL),(110,'24-22-287','ASPILLAGA','JHEYRONE',NULL),(111,'24-22-267','ATIBAGOS','DEO',NULL),(112,'24-22-282','BALAIS','RACE EION',NULL),(113,'24-22-036','BODOSO','MARIELLE',NULL),(114,'24-22-009','BORROMEO','ASIA LOUISE',NULL),(115,'24-22-025','CALMA','JYLIANA IYA',NULL),(116,'24-22-017','CANTALEJO','JERSEY REI',NULL),(117,'24-22-014','CAPIZ','JOHN CEDRICK',NULL),(118,'24-22-038','CARDENAS','JR.	ZOILO',NULL),(119,'24-22-007','CASINGINAN','JENNY-ROSE',NULL),(120,'24-22-251','CASTRO','KENNETH',NULL),(121,'24-22-252','COMENDADOR','PRESCIOUS JADE',NULL),(122,'24-22-040','COTIAMCO','JUDETHAVINCE',NULL),(123,'24-22-001','CRUZ','CHYRUS',NULL),(124,'24-22-301','DE','CASTRO	JOHN CARLO',NULL),(125,'24-22-020','DEGOMA','JHUSTIN',NULL),(126,'24-22-331','DELACRUZ','VANESSA',NULL),(127,'24-22-004','DONGALLO','FRANCIS',NULL),(128,'24-22-028','DULDULAO','MARK JESSE',NULL),(129,'24-22-272','ESTRADA','NHICKA ERRA',NULL),(130,'24-22-313','FAJARDO','ALESSANDRA',NULL),(131,'24-22-013','FARAON','CHRISTIAN JHAY',NULL),(132,'24-22-029','FERNANDEZ','KING',NULL),(133,'24-22-027','GECOSO','RAFFAELL JOHN',NULL),(134,'24-22-032','INDOLOS','DUSTIN KWERR',NULL),(135,'23-22-136','JASMIN','JENESIS CLAUDETTE',NULL),(136,'24-22-026','LABONG','KHAIZER CHARLES',NULL),(137,'24-22-319','LAPUZ','GERARD',NULL),(138,'24-22-021','LOZADA','JIMBOY',NULL),(139,'24-22-024','LUISTRO','KEVENLY',NULL),(140,'24-22-033','LUNA','CLARENCE JERALD',NULL),(141,'24-22-292','MADIS','JENIVIEVE',NULL),(142,'24-22-023','MANGALUS','JR.	MICHAEL',NULL),(143,'24-22-008','MARCHAN','RUSHA BELLE',NULL),(144,'24-22-010','MIGUEL','LJ JOURIE',NULL),(145,'24-22-337','OBLIGADO','MARK SIMON',NULL),(146,'24-22-035','OLIVEROS','JENMAR',NULL),(147,'24-22-022','PANER','RAPHAEL',NULL),(148,'24-22-031','PANTI','CHRISTIAN PATRICK',NULL),(149,'24-22-034','PLAGATA','AMARU CHRIS JUNIEL',NULL),(150,'24-22-006','PULO','ANDRE',NULL),(151,'24-22-030','PUNO','ASHLEY FAYE',NULL),(152,'24-22-325','ROLLO','CHRISTIAN DALE',NULL),(153,'24-22-018','SACLOLO','SOFIA ANN',NULL),(154,'24-22-002','SANTILLAN','JOHN MHARWIN',NULL),(155,'24-22-003','SAYCON','BJ',NULL),(156,'24-22-015','SENARLO','JUSTIN LLOYD',NULL),(157,'24-22-005','SUNGA','EZEKIEL',NULL),(158,'24-22-277','TAMPUS','JOHN MARC',NULL),(159,'24-22-016','TEJANO','VENZ LORENZE',NULL),(160,'24-22-345','TORRES','CLYDE DAYNELL',NULL),(161,'24-22-344','TREBITA','CLARENCE',NULL),(162,'24-22-037','VALDECANTOS','LANTIS',NULL),(163,'24-22-307','VILLABER','RODMAR',NULL),(164,'24-22-012','VILLARAMA','JASFER',NULL);
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(20) DEFAULT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`subject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,NULL,'Design Project 2'),(3,'','Introduction to Computing'),(4,'CPE422','Design Project 2'),(5,'CPE112','Computer Engineering As Discipline');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teachers`
--

DROP TABLE IF EXISTS `teachers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`teacher_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teachers`
--

LOCK TABLES `teachers` WRITE;
/*!40000 ALTER TABLE `teachers` DISABLE KEYS */;
INSERT INTO `teachers` VALUES (1,'epallasigue','$2y$10$YynFoTQY1D5ZB1.BRBJlm.YatIJMDorllAgjoHQFDyD5ZGYl0A1ZK','Erik Josef Pallasigue','ejaypallasigue@gmail.com'),(2,'marvin.edu','$2y$10$POBuTWXl2Mr24mfgZdbNIOa7prm1gQkCMZsQXh0iDAIY2pRoLK3QW','Marvin Angelo A. Dela Cruz','marvinangelo@gmail.com'),(3,'faculty','$2y$10$/ILWESAtYwUaWeMRXoE9Hu0WPFZElvzMxzU8td5Jai0iu/3o2pTTy','Faculty Tester','facultytesting@gmail.com');
/*!40000 ALTER TABLE `teachers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$U.1jkDGgCn97e7WYNrkVWO9JI2g89ucLij3aguT.O9q/KtZeazQhq','Admin User','2025-05-29 00:24:48');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'udm_class_record_db'
--

--
-- Dumping routines for database 'udm_class_record_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-30 13:20:06
