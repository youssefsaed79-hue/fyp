-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: internflow
-- ------------------------------------------------------
-- Server version	8.4.7

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
-- Current Database: `internflow`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `internflow` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `internflow`;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `deadline_date` date DEFAULT NULL,
  `status` enum('draft','published','removed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ann_status` (`status`),
  KEY `idx_ann_published` (`published_at`),
  KEY `fk_ann_creator` (`created_by`),
  CONSTRAINT `fk_ann_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,'Special Semester 2026/2027 Industrial Training Registration Open','All eligible CCI students who have completed 60 credits (Degree) or 45 credits (Diploma) must finalize company placement offers by May 15, 2026. Please ensure your CV is uploaded in your profile before applying.','2026-05-15','published','2026-10-20 23:29:09',1,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(2,'Upcoming Resume Writing & Interview Prep Workshop','A virtual workshop with industry HR directors will be hosted next month. Details will be published soon.',NULL,'draft',NULL,1,'2026-10-20 15:29:09','2026-10-20 15:29:09');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_user_id` int NOT NULL,
  `vacancy_id` int NOT NULL,
  `cover_letter` text COLLATE utf8mb4_unicode_ci,
  `status` enum('submitted','under_review','shortlisted','interview','accepted','rejected','withdrawn') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `interview_details` text COLLATE utf8mb4_unicode_ci,
  `offer_issued` tinyint(1) NOT NULL DEFAULT '0',
  `offer_text` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_app_student` (`student_user_id`),
  KEY `idx_app_vac` (`vacancy_id`),
  KEY `idx_app_status` (`status`),
  CONSTRAINT `fk_app_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_app_vac` FOREIGN KEY (`vacancy_id`) REFERENCES `vacancies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applications`
--

LOCK TABLES `applications` WRITE;
/*!40000 ALTER TABLE `applications` DISABLE KEYS */;
INSERT INTO `applications` VALUES (1,12,1,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','submitted',NULL,0,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(2,13,2,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','under_review',NULL,0,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(3,14,3,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','shortlisted',NULL,0,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(4,15,4,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','interview','Online Technical Interview scheduled on Zoom: Meeting ID 889 012 3456',0,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(5,16,5,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','accepted','Online Technical Interview scheduled on Zoom: Meeting ID 889 012 3456',1,'We are delighted to offer you the internship position starting in Special Semester.','2026-10-20 15:29:08','2026-10-20 15:29:08'),(6,17,1,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','rejected',NULL,0,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(7,18,2,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','submitted',NULL,0,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(8,19,3,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','under_review',NULL,0,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(9,20,4,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','shortlisted',NULL,0,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(10,21,5,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','interview','Online Technical Interview scheduled on Zoom: Meeting ID 889 012 3456',0,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(11,22,1,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','accepted','Online Technical Interview scheduled on Zoom: Meeting ID 889 012 3456',1,'We are delighted to offer you the internship position starting in Special Semester.','2026-10-20 15:29:08','2026-10-20 15:29:08'),(12,23,2,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','rejected',NULL,0,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(13,24,3,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','submitted',NULL,0,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(14,25,4,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','under_review',NULL,0,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(15,26,5,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','shortlisted',NULL,0,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(16,27,1,'Dear Hiring Manager, I am eager to apply for this internship opportunity.','interview','Online Technical Interview scheduled on Zoom: Meeting ID 889 012 3456',0,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08');
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `placement_id` int NOT NULL,
  `work_date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `hours` decimal(4,2) NOT NULL DEFAULT '8.00',
  `work_arrangement` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'onsite',
  `status` enum('present','absent','leave') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present',
  `absence_reason` text COLLATE utf8mb4_unicode_ci,
  `evidence_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification` enum('pending_verification','verified','flagged') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_verification',
  `flag_comment` text COLLATE utf8mb4_unicode_ci,
  `verified_by` int DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_plc_date` (`placement_id`,`work_date`),
  KEY `idx_att_date` (`work_date`),
  KEY `idx_att_ver` (`verification`),
  KEY `fk_att_verifier` (`verified_by`),
  CONSTRAINT `fk_att_plc` FOREIGN KEY (`placement_id`) REFERENCES `placements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_att_verifier` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (1,2,'2026-09-08','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(2,2,'2026-09-09','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(3,2,'2026-09-10','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(4,2,'2026-09-11','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(5,2,'2026-09-14','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(6,2,'2026-09-15','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(7,2,'2026-09-16','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(8,2,'2026-09-17','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(9,2,'2026-09-18','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(10,2,'2026-09-21','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(11,2,'2026-09-22','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(12,2,'2026-09-23','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(13,2,'2026-09-24','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'flagged','Check-in time discrepancy noted. Please submit supervisor sign-off confirmation.',6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(14,2,'2026-09-25','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(15,2,'2026-09-28','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(16,2,'2026-09-29','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(17,2,'2026-09-30','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(18,2,'2026-10-01','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(19,2,'2026-10-02','08:30:00','17:30:00',8.00,'onsite','leave',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(20,2,'2026-10-05','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(21,2,'2026-10-06','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(22,2,'2026-10-07','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(23,2,'2026-10-08','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(24,2,'2026-10-09','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(25,2,'2026-10-12','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(26,2,'2026-10-13','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(27,2,'2026-10-14','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(28,2,'2026-10-15','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(29,2,'2026-10-16','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(30,2,'2026-10-19','08:30:00','17:30:00',8.00,'onsite','present',NULL,NULL,'verified',NULL,6,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08');
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registration_no` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `industry` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `representative_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `representative_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` enum('partner','self_sourced') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'partner',
  `status` enum('pending','approved','rejected','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `registered_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_company_status` (`status`),
  KEY `idx_company_source` (`source`),
  KEY `fk_comp_reg_by` (`registered_by`),
  CONSTRAINT `fk_comp_reg_by` FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,'Petronas Digital Sdn Bhd','199801012345 (456789-A)','Energy & Oil/Gas Tech','Tower 1, PETRONAS Twin Towers, Kuala Lumpur City Centre, 50088 Kuala Lumpur','Siti Aminah binti Mansor','hr@petronas.test','partner','approved',NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(2,'Tenaga Nasional Berhad (TNB IT & Cloud)','199001009876 (200866-W)','Utilities & Cloud Computing','No. 129, Jalan Bangsar, 59200 Kuala Lumpur','Faizal bin Osman','intern@tnb.com.my','partner','approved',NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(3,'CIMB Group Technologies','197401002647 (18417-M)','Fintech & Banking','Menara CIMB, Jalan Stesen Sentral 2, 50470 Kuala Lumpur','Marcus Wong Wei Loon','techintern@cimb.com','partner','approved',NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(4,'Aerodyne Group','201401033456 (1109542-K)','Drone Tech & Artificial Intelligence','Aerodyne Campus, Cyberjaya, 63000 Selangor','Azlan Shah bin Zain','careers@aerodyne.group','partner','approved',NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(5,'CyberSecurity Malaysia','200601007890 (726048-U)','Information Security','Menara Cyber Axis, Jalan Impact, 63000 Cyberjaya','Nadiah binti Yusof','hr@cybersecurity.my','partner','pending',NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(6,'Grab Malaysia Technology Hub','201101019876 (948010-T)','E-Commerce & Mobility','1 Powerhouse, Bandar Utama, 47800 Petaling Jaya','Kevin Tan','talent@grab.com','partner','suspended',NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(7,'NexTech Innovations Sdn Bhd','202001099887 (139988-X)','Cloud Robotics','Technology Park Malaysia, Bukit Jalil, 57000 Kuala Lumpur','Engr. David Tan','david.tan@nextech.test','self_sourced','approved',NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08');
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_users`
--

DROP TABLE IF EXISTS `company_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `company_users` (
  `user_id` int NOT NULL,
  `company_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`company_id`),
  KEY `fk_cu_company` (`company_id`),
  CONSTRAINT `fk_cu_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cu_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_users`
--

LOCK TABLES `company_users` WRITE;
/*!40000 ALTER TABLE `company_users` DISABLE KEYS */;
INSERT INTO `company_users` VALUES (5,1),(6,2),(7,3),(8,4),(9,5),(10,6);
/*!40000 ALTER TABLE `company_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `placement_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `placement_id` (`placement_id`),
  CONSTRAINT `fk_conv_plc` FOREIGN KEY (`placement_id`) REFERENCES `placements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversations`
--

LOCK TABLES `conversations` WRITE;
/*!40000 ALTER TABLE `conversations` DISABLE KEYS */;
INSERT INTO `conversations` VALUES (1,1,'2026-10-20 15:29:08'),(2,2,'2026-10-20 15:29:08');
/*!40000 ALTER TABLE `conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_user_id` int NOT NULL,
  `type` enum('cv','verification_letter','transcript','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_doc_student` (`student_user_id`),
  KEY `idx_doc_type` (`type`),
  CONSTRAINT `fk_doc_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` VALUES (1,11,'cv','CV_Aly_Youssef.pdf','documents/cv_aly_youssef.pdf','application/pdf',848,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(2,12,'cv','CV_SW01084501.pdf','documents/cv_SW01084501.pdf','application/pdf',874,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(3,13,'cv','CV_SW01084502.pdf','documents/cv_SW01084502.pdf','application/pdf',882,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(4,14,'cv','CV_SW01084503.pdf','documents/cv_SW01084503.pdf','application/pdf',871,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(5,15,'cv','CV_CS01084504.pdf','documents/cv_CS01084504.pdf','application/pdf',878,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(6,16,'cv','CV_CS01084505.pdf','documents/cv_CS01084505.pdf','application/pdf',876,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(7,17,'cv','CV_IT01084506.pdf','documents/cv_IT01084506.pdf','application/pdf',888,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(8,18,'cv','CV_IT01084507.pdf','documents/cv_IT01084507.pdf','application/pdf',875,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(9,19,'cv','CV_SW01084508.pdf','documents/cv_SW01084508.pdf','application/pdf',883,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(10,20,'cv','CV_SW01084509.pdf','documents/cv_SW01084509.pdf','application/pdf',877,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(11,21,'cv','CV_CS01084510.pdf','documents/cv_CS01084510.pdf','application/pdf',878,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(12,22,'cv','CV_IT01084511.pdf','documents/cv_IT01084511.pdf','application/pdf',891,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(13,23,'cv','CV_SW01084512.pdf','documents/cv_SW01084512.pdf','application/pdf',873,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(14,24,'cv','CV_SW01084513.pdf','documents/cv_SW01084513.pdf','application/pdf',875,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(15,25,'cv','CV_CS01084514.pdf','documents/cv_CS01084514.pdf','application/pdf',878,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(16,26,'cv','CV_SW01084515.pdf','documents/cv_SW01084515.pdf','application/pdf',882,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(17,27,'cv','CV_IT01084516.pdf','documents/cv_IT01084516.pdf','application/pdf',875,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(18,28,'cv','CV_SW01084517.pdf','documents/cv_SW01084517.pdf','application/pdf',877,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(19,29,'cv','CV_CS01084518.pdf','documents/cv_CS01084518.pdf','application/pdf',871,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(20,30,'cv','CV_SW01084519.pdf','documents/cv_SW01084519.pdf','application/pdf',885,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(21,31,'cv','CV_IT01084520.pdf','documents/cv_IT01084520.pdf','application/pdf',876,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(22,32,'cv','CV_SW01084521.pdf','documents/cv_SW01084521.pdf','application/pdf',884,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(23,33,'cv','CV_CS01084522.pdf','documents/cv_CS01084522.pdf','application/pdf',876,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(24,34,'cv','CV_SW01084523.pdf','documents/cv_SW01084523.pdf','application/pdf',871,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(25,35,'cv','CV_IT01084524.pdf','documents/cv_IT01084524.pdf','application/pdf',887,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(26,36,'cv','CV_SW01084525.pdf','documents/cv_SW01084525.pdf','application/pdf',876,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(27,37,'cv','CV_CS01084526.pdf','documents/cv_CS01084526.pdf','application/pdf',875,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(28,38,'cv','CV_SW01084527.pdf','documents/cv_SW01084527.pdf','application/pdf',870,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(29,39,'cv','CV_IT01084528.pdf','documents/cv_IT01084528.pdf','application/pdf',890,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(30,40,'cv','CV_SW01084529.pdf','documents/cv_SW01084529.pdf','application/pdf',873,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(31,41,'cv','CV_SW01084530.pdf','documents/cv_SW01084530.pdf','application/pdf',885,'2026-10-20 15:29:08','2026-10-20 15:29:08');
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_log`
--

DROP TABLE IF EXISTS `email_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `to_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('sent','logged','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'logged',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_el_to` (`to_email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_log`
--

LOCK TABLES `email_log` WRITE;
/*!40000 ALTER TABLE `email_log` DISABLE KEYS */;
INSERT INTO `email_log` VALUES (1,'adam.daniel@student.uniten.edu.my','Welcome to InternFlow - UNITEN CCI Industrial Training Account','<h3>Dear Adam Daniel bin Zaki,</h3>\n                    <p>Your student account for the UNITEN CCI Industrial Training platform has been created.</p>\n                    <p><strong>Your Login Credentials:</strong><br>\n                    Email: <code>adam.daniel@student.uniten.edu.my</code><br>\n                    Temporary Password: <code>Temp#07c758!</code></p>\n                    <p><a href=\'C:/Users/User/.gemini/antigravity-ide/brain/e9728b1e-2bc6-405a-ac77-448cfbea40de/scratch/login\'>Click here to Sign In and Set Your New Password</a></p>\n                    <p>Upon your first sign-in, you will be required to create your personal password and complete your student profile by uploading your CV.</p>\n                    <hr><p><small>UNITEN College of Computing &amp; Informatics</small></p>','logged','2026-10-20 15:38:44'),(2,'melissa.tan@student.uniten.edu.my','Welcome to InternFlow - UNITEN CCI Industrial Training Account','<h3>Dear Melissa Tan Shu En,</h3>\n                    <p>Your student account for the UNITEN CCI Industrial Training platform has been created.</p>\n                    <p><strong>Your Login Credentials:</strong><br>\n                    Email: <code>melissa.tan@student.uniten.edu.my</code><br>\n                    Temporary Password: <code>Temp#607418!</code></p>\n                    <p><a href=\'C:/Users/User/.gemini/antigravity-ide/brain/e9728b1e-2bc6-405a-ac77-448cfbea40de/scratch/login\'>Click here to Sign In and Set Your New Password</a></p>\n                    <p>Upon your first sign-in, you will be required to create your personal password and complete your student profile by uploading your CV.</p>\n                    <hr><p><small>UNITEN College of Computing &amp; Informatics</small></p>','logged','2026-10-20 15:38:44'),(3,'pravin.mohan@student.uniten.edu.my','Welcome to InternFlow - UNITEN CCI Industrial Training Account','<h3>Dear Pravin a/l Mohan,</h3>\n                    <p>Your student account for the UNITEN CCI Industrial Training platform has been created.</p>\n                    <p><strong>Your Login Credentials:</strong><br>\n                    Email: <code>pravin.mohan@student.uniten.edu.my</code><br>\n                    Temporary Password: <code>Temp#5a6f6e!</code></p>\n                    <p><a href=\'C:/Users/User/.gemini/antigravity-ide/brain/e9728b1e-2bc6-405a-ac77-448cfbea40de/scratch/login\'>Click here to Sign In and Set Your New Password</a></p>\n                    <p>Upon your first sign-in, you will be required to create your personal password and complete your student profile by uploading your CV.</p>\n                    <hr><p><small>UNITEN College of Computing &amp; Informatics</small></p>','logged','2026-10-20 15:38:45'),(4,'sarah.k@student.uniten.edu.my','Welcome to InternFlow - UNITEN CCI Industrial Training Account','<h3>Dear Sarah binti Kamaruzzaman,</h3>\n                    <p>Your student account for the UNITEN CCI Industrial Training platform has been created.</p>\n                    <p><strong>Your Login Credentials:</strong><br>\n                    Email: <code>sarah.k@student.uniten.edu.my</code><br>\n                    Temporary Password: <code>Temp#11dda8!</code></p>\n                    <p><a href=\'C:/Users/User/.gemini/antigravity-ide/brain/e9728b1e-2bc6-405a-ac77-448cfbea40de/scratch/login\'>Click here to Sign In and Set Your New Password</a></p>\n                    <p>Upon your first sign-in, you will be required to create your personal password and complete your student profile by uploading your CV.</p>\n                    <hr><p><small>UNITEN College of Computing &amp; Informatics</small></p>','logged','2026-10-20 15:38:45'),(5,'wong.zhiwei@student.uniten.edu.my','Welcome to InternFlow - UNITEN CCI Industrial Training Account','<h3>Dear Wong Zhi Wei,</h3>\n                    <p>Your student account for the UNITEN CCI Industrial Training platform has been created.</p>\n                    <p><strong>Your Login Credentials:</strong><br>\n                    Email: <code>wong.zhiwei@student.uniten.edu.my</code><br>\n                    Temporary Password: <code>Temp#24378f!</code></p>\n                    <p><a href=\'C:/Users/User/.gemini/antigravity-ide/brain/e9728b1e-2bc6-405a-ac77-448cfbea40de/scratch/login\'>Click here to Sign In and Set Your New Password</a></p>\n                    <p>Upon your first sign-in, you will be required to create your personal password and complete your student profile by uploading your CV.</p>\n                    <hr><p><small>UNITEN College of Computing &amp; Informatics</small></p>','logged','2026-10-20 15:38:45');
/*!40000 ALTER TABLE `email_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evaluations`
--

DROP TABLE IF EXISTS `evaluations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evaluations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `placement_id` int NOT NULL,
  `evaluator_id` int NOT NULL,
  `type` enum('host_monthly','host_final','visiting_lecturer') COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_no` int DEFAULT NULL,
  `scores_json` json NOT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `total_pct` decimal(5,2) NOT NULL,
  `submitted_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_eval_plc` (`placement_id`),
  KEY `idx_eval_type` (`type`),
  KEY `idx_evaluator` (`evaluator_id`),
  CONSTRAINT `fk_eval_plc` FOREIGN KEY (`placement_id`) REFERENCES `placements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_eval_user` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluations`
--

LOCK TABLES `evaluations` WRITE;
/*!40000 ALTER TABLE `evaluations` DISABLE KEYS */;
INSERT INTO `evaluations` VALUES (1,1,5,'host_final',NULL,'{\"teamwork\": 5, \"attendance\": 5, \"punctuality\": 5, \"work_quality\": 5, \"communication\": 4, \"problem_solving\": 4, \"technical_knowledge\": 5, \"training_objectives\": 4, \"professional_behaviour\": 5}','Outstanding technical performance and initiative.',92.00,'2026-10-20 23:29:08','2026-10-20 15:29:08'),(2,1,2,'visiting_lecturer',NULL,'{\"presentation\": 5, \"comprehension\": 4, \"host_feedback\": 5, \"academic_reflection\": 4}','Satisfactory visit conducted. Student demonstrated deep understanding.',88.00,'2026-10-20 23:29:08','2026-10-20 15:29:08');
/*!40000 ALTER TABLE `evaluations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `final_grades`
--

DROP TABLE IF EXISTS `final_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `final_grades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `placement_id` int NOT NULL,
  `host_score` decimal(5,2) DEFAULT NULL,
  `visiting_score` decimal(5,2) DEFAULT NULL,
  `report_score` decimal(5,2) DEFAULT NULL,
  `logbook_score` decimal(5,2) DEFAULT NULL,
  `total` decimal(5,2) DEFAULT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `locked_by` int DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `placement_id` (`placement_id`),
  KEY `idx_fg_locked` (`locked`),
  KEY `fk_fg_locker` (`locked_by`),
  CONSTRAINT `fk_fg_locker` FOREIGN KEY (`locked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fg_plc` FOREIGN KEY (`placement_id`) REFERENCES `placements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `final_grades`
--

LOCK TABLES `final_grades` WRITE;
/*!40000 ALTER TABLE `final_grades` DISABLE KEYS */;
INSERT INTO `final_grades` VALUES (1,1,92.00,88.00,85.00,90.00,89.20,1,1,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08');
/*!40000 ALTER TABLE `final_grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `final_reports`
--

DROP TABLE IF EXISTS `final_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `final_reports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `placement_id` int NOT NULL,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `originality_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `submitted_at` datetime NOT NULL,
  `mark` decimal(5,2) DEFAULT NULL,
  `marked_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `placement_id` (`placement_id`),
  KEY `idx_fr_marker` (`marked_by`),
  CONSTRAINT `fk_fr_marker` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fr_plc` FOREIGN KEY (`placement_id`) REFERENCES `placements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `final_reports`
--

LOCK TABLES `final_reports` WRITE;
/*!40000 ALTER TABLE `final_reports` DISABLE KEYS */;
INSERT INTO `final_reports` VALUES (1,1,'reports/final_report_p1.pdf','Industrial_Training_Final_Report_NurulHuda.pdf',1,'2026-10-20 23:29:08',85.00,2,'2026-10-20 15:29:08','2026-10-20 15:29:08');
/*!40000 ALTER TABLE `final_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guidelines`
--

DROP TABLE IF EXISTS `guidelines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guidelines` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `kind` enum('eligibility','procedure','responsibility','instruction') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_guide_kind` (`kind`),
  KEY `idx_guide_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guidelines`
--

LOCK TABLES `guidelines` WRITE;
/*!40000 ALTER TABLE `guidelines` DISABLE KEYS */;
INSERT INTO `guidelines` VALUES (1,'Eligibility Requirements','<p>Students must fulfill the following minimum academic requirements prior to commencing industrial training:</p>\n        <ul>\n            <li><strong>Bachelor Degree Programmes:</strong> Must have successfully completed at least <strong>60 credit hours</strong>.</li>\n            <li><strong>Diploma Programmes:</strong> Must have successfully completed at least <strong>45 credit hours</strong>.</li>\n            <li><strong>Prerequisite Subjects:</strong> Prerequisite coursework varies by programme track (Software Engineering, Cyber Security, Information Systems). Confirm eligibility with your academic advisor.</li>\n            <li>Good academic standing without active disciplinary sanctions.</li>\n        </ul>','eligibility',1,'published','2026-10-20 15:29:09','2026-10-20 15:29:09'),(2,'Application & Placement Procedures','<p>Standard procedure for securing host company placements:</p>\n        <ol>\n            <li>Prepare your professional CV and cover letter in InternFlow.</li>\n            <li>Generate and download the official Industrial Training Application &amp; Verification Letter.</li>\n            <li>Apply to partner company listings on InternFlow or approach external organisations.</li>\n            <li>When securing an external placement, register the host supervisor contact in InternFlow to issue the System Invitation Token.</li>\n            <li>Submit Form 1 (Offer Letter Form) within 7 days of receiving an acceptance offer.</li>\n        </ol>','procedure',2,'published','2026-10-20 15:29:09','2026-10-20 15:29:09'),(3,'Roles and Responsibilities','<p>Expectations for all parties during the 12-week training period:</p>\n        <ul>\n            <li><strong>Student Intern:</strong> Observe company working hours, adhere to corporate policies, maintain regular daily attendance, and submit weekly logbooks on time.</li>\n            <li><strong>Host Supervisor:</strong> Provide technical guidance, oversee assigned duties, verify weekly attendance in batches, and evaluate workplace performance (40%).</li>\n            <li><strong>University Supervisor:</strong> Provide academic mentorship, review and grade weekly logbooks (10%), conduct mid-term evaluation (30%), and grade the final report (20%).</li>\n            <li><strong>Coordinator:</strong> Oversee cohort placement, resolve compliance inquiries, and lock final endorsed grades.</li>\n        </ul>','responsibility',3,'published','2026-10-20 15:29:09','2026-10-20 15:29:09'),(4,'Important Instructions & Submission Deadlines','<p>Critical operational instructions during the industrial training period:</p>\n        <ul>\n            <li><strong>Daily Attendance:</strong> Must be logged daily. Host supervisors verify records weekly or monthly. Overall attendance must remain <strong>at or above 80%</strong>.</li>\n            <li><strong>Weekly Logbooks:</strong> Must be submitted weekly by Sunday 23:59. Dual review by company and university supervisor.</li>\n            <li><strong>Monthly Reports:</strong> Submitted once per 4-week cycle (Weeks 4, 8, 12) for academic monitoring.</li>\n            <li><strong>Final Report Submission:</strong> Submit digital copy with signed originality declaration within <strong>two weeks</strong> after the conclusion of the 12-week internship.</li>\n            <li><strong>Visiting Lecturer Visit:</strong> Scheduled between Weeks 8 and 12. Coordinate date with your assigned university supervisor.</li>\n        </ul>','instruction',4,'published','2026-10-20 15:29:09','2026-10-20 15:29:09');
/*!40000 ALTER TABLE `guidelines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invitation_tokens`
--

DROP TABLE IF EXISTS `invitation_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invitation_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `placement_id` int NOT NULL,
  `invited_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `idx_it_token` (`token_hash`),
  KEY `idx_it_placement` (`placement_id`),
  CONSTRAINT `fk_it_placement` FOREIGN KEY (`placement_id`) REFERENCES `placements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invitation_tokens`
--

LOCK TABLES `invitation_tokens` WRITE;
/*!40000 ALTER TABLE `invitation_tokens` DISABLE KEYS */;
INSERT INTO `invitation_tokens` VALUES (1,'acf7524fb5782e9be548ee1d77926b2696e309e1049ca847df8df8167133992d',5,'david.tan@nextech.test','2026-10-23 23:29:09',NULL,0,'2026-10-20 15:29:09');
/*!40000 ALTER TABLE `invitation_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journey_stages`
--

DROP TABLE IF EXISTS `journey_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journey_stages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `stage_no` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_js_order` (`stage_no`),
  KEY `idx_js_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journey_stages`
--

LOCK TABLES `journey_stages` WRITE;
/*!40000 ALTER TABLE `journey_stages` DISABLE KEYS */;
INSERT INTO `journey_stages` VALUES (1,1,'Semester 1 – First Briefing','Attend the initial briefing organised by the College of Computing and Informatics (CCI). Review eligibility prerequisites, credit requirements, and prepare your initial CV and career interests.','published','2026-10-20 15:29:09','2026-10-20 15:29:09'),(2,2,'Semester 2 – Second Briefing, Placement Search & Pre-Registration','Attend the second detailed briefing. Search and apply for accredited vacancies or register self-sourced placements. Pre-register and register the industrial training course subject once offer is accepted.','published','2026-10-20 15:29:09','2026-10-20 15:29:09'),(3,3,'Special Semester – Complete 12-Week Industrial Training','Undertake the 12-week industrial training at the approved host organisation. Submit daily attendance, record weekly digital logbooks, submit monthly progress reports, and host visiting lecturer visit.','published','2026-10-20 15:29:09','2026-10-20 15:29:09'),(4,4,'Following Semester 1 – Results & Completion','Submit final industrial training report, review combined evaluation marks (Host 40%, Visiting 30%, Report 20%, Logbook 10%), and receive the final academic grade endorsement.','published','2026-10-20 15:29:09','2026-10-20 15:29:09');
/*!40000 ALTER TABLE `journey_stages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logbook_attachments`
--

DROP TABLE IF EXISTS `logbook_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logbook_attachments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `logbook_id` int NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_la_log` (`logbook_id`),
  CONSTRAINT `fk_la_log` FOREIGN KEY (`logbook_id`) REFERENCES `logbooks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logbook_attachments`
--

LOCK TABLES `logbook_attachments` WRITE;
/*!40000 ALTER TABLE `logbook_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `logbook_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logbook_reviews`
--

DROP TABLE IF EXISTS `logbook_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logbook_reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `logbook_id` int NOT NULL,
  `reviewer_id` int NOT NULL,
  `reviewer_role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` enum('comment','approve','return') COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lr_log` (`logbook_id`),
  KEY `idx_lr_rev` (`reviewer_id`),
  CONSTRAINT `fk_lr_log` FOREIGN KEY (`logbook_id`) REFERENCES `logbooks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lr_rev` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logbook_reviews`
--

LOCK TABLES `logbook_reviews` WRITE;
/*!40000 ALTER TABLE `logbook_reviews` DISABLE KEYS */;
INSERT INTO `logbook_reviews` VALUES (1,1,6,'company','approve','Excellent progress this week. Well documented.','2026-10-20 15:29:08'),(2,1,2,'university_supervisor','approve','Good technical depth and clear reflection.','2026-10-20 15:29:08'),(3,2,6,'company','approve','Excellent progress this week. Well documented.','2026-10-20 15:29:08'),(4,2,2,'university_supervisor','approve','Good technical depth and clear reflection.','2026-10-20 15:29:08'),(5,3,6,'company','approve','Excellent progress this week. Well documented.','2026-10-20 15:29:08'),(6,3,2,'university_supervisor','approve','Good technical depth and clear reflection.','2026-10-20 15:29:08'),(7,4,6,'company','approve','Excellent progress this week. Well documented.','2026-10-20 15:29:08'),(8,4,2,'university_supervisor','approve','Good technical depth and clear reflection.','2026-10-20 15:29:08'),(9,5,6,'company','approve','Excellent progress this week. Well documented.','2026-10-20 15:29:08'),(10,5,2,'university_supervisor','approve','Good technical depth and clear reflection.','2026-10-20 15:29:08');
/*!40000 ALTER TABLE `logbook_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logbook_revisions`
--

DROP TABLE IF EXISTS `logbook_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logbook_revisions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `logbook_id` int NOT NULL,
  `snapshot_json` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lrev_log` (`logbook_id`),
  CONSTRAINT `fk_lrev_log` FOREIGN KEY (`logbook_id`) REFERENCES `logbooks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logbook_revisions`
--

LOCK TABLES `logbook_revisions` WRITE;
/*!40000 ALTER TABLE `logbook_revisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `logbook_revisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logbooks`
--

DROP TABLE IF EXISTS `logbooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logbooks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `placement_id` int NOT NULL,
  `week_no` int NOT NULL,
  `week_start` date NOT NULL,
  `week_end` date NOT NULL,
  `tasks` text COLLATE utf8mb4_unicode_ci,
  `skills` text COLLATE utf8mb4_unicode_ci,
  `challenges` text COLLATE utf8mb4_unicode_ci,
  `solutions` text COLLATE utf8mb4_unicode_ci,
  `reflection` text COLLATE utf8mb4_unicode_ci,
  `hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','submitted','company_reviewed','approved','returned','resubmitted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `grade` decimal(5,2) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_plc_week` (`placement_id`,`week_no`),
  KEY `idx_log_status` (`status`),
  CONSTRAINT `fk_log_plc` FOREIGN KEY (`placement_id`) REFERENCES `placements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logbooks`
--

LOCK TABLES `logbooks` WRITE;
/*!40000 ALTER TABLE `logbooks` DISABLE KEYS */;
INSERT INTO `logbooks` VALUES (1,2,1,'2026-09-08','2026-09-14','Completed microservices modules and API endpoints for Week 1.\nConducted automated unit testing.','PHP 8, Docker, MySQL indexing, Git flow','Resolved query optimization bottlenecks on large dataset.','Added composite indexing on foreign keys and refactored SQL joins.','Improved technical confidence working with enterprise infrastructure.',40.00,'approved',91.50,'2026-09-14 17:00:00','2026-10-20 15:29:08','2026-10-20 15:29:08'),(2,2,2,'2026-09-15','2026-09-21','Completed microservices modules and API endpoints for Week 2.\nConducted automated unit testing.','PHP 8, Docker, MySQL indexing, Git flow','Resolved query optimization bottlenecks on large dataset.','Added composite indexing on foreign keys and refactored SQL joins.','Improved technical confidence working with enterprise infrastructure.',40.00,'approved',93.00,'2026-09-21 17:00:00','2026-10-20 15:29:08','2026-10-20 15:29:08'),(3,2,3,'2026-09-22','2026-09-28','Completed microservices modules and API endpoints for Week 3.\nConducted automated unit testing.','PHP 8, Docker, MySQL indexing, Git flow','Resolved query optimization bottlenecks on large dataset.','Added composite indexing on foreign keys and refactored SQL joins.','Improved technical confidence working with enterprise infrastructure.',40.00,'approved',94.50,'2026-09-28 17:00:00','2026-10-20 15:29:08','2026-10-20 15:29:08'),(4,2,4,'2026-09-29','2026-10-05','Completed microservices modules and API endpoints for Week 4.\nConducted automated unit testing.','PHP 8, Docker, MySQL indexing, Git flow','Resolved query optimization bottlenecks on large dataset.','Added composite indexing on foreign keys and refactored SQL joins.','Improved technical confidence working with enterprise infrastructure.',40.00,'approved',96.00,'2026-10-05 17:00:00','2026-10-20 15:29:08','2026-10-20 15:29:08'),(5,2,5,'2026-10-06','2026-10-12','Completed microservices modules and API endpoints for Week 5.\nConducted automated unit testing.','PHP 8, Docker, MySQL indexing, Git flow','Resolved query optimization bottlenecks on large dataset.','Added composite indexing on foreign keys and refactored SQL joins.','Improved technical confidence working with enterprise infrastructure.',40.00,'approved',97.50,'2026-10-12 17:00:00','2026-10-20 15:29:08','2026-10-20 15:29:08'),(6,2,6,'2026-10-13','2026-10-19','Deployed CI/CD pipeline stage on staging server. Configured automated lint checks.','DevOps, GitLab CI, Shell Scripting','Pipeline failed due to permission rights in runner container.','Adjusted Docker runner non-root user permissions.','Learned how automated deployment safeguards production integrity.',40.00,'company_reviewed',NULL,'2026-10-19 16:30:00','2026-10-20 15:29:08','2026-10-20 15:29:08'),(7,2,7,'2026-10-20','2026-10-26',NULL,NULL,NULL,NULL,NULL,0.00,'draft',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(8,2,8,'2026-10-27','2026-11-02',NULL,NULL,NULL,NULL,NULL,0.00,'draft',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(9,2,9,'2026-11-03','2026-11-09',NULL,NULL,NULL,NULL,NULL,0.00,'draft',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(10,2,10,'2026-11-10','2026-11-16',NULL,NULL,NULL,NULL,NULL,0.00,'draft',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(11,2,11,'2026-11-17','2026-11-23',NULL,NULL,NULL,NULL,NULL,0.00,'draft',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(12,2,12,'2026-11-24','2026-11-30',NULL,NULL,NULL,NULL,NULL,0.00,'draft',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08');
/*!40000 ALTER TABLE `logbooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `conversation_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_msg_conv` (`conversation_id`),
  KEY `fk_msg_sender` (`sender_id`),
  CONSTRAINT `fk_msg_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,1,5,'Welcome to Petronas Digital! Please check the orientation guide.','2026-10-20 15:29:08'),(2,1,12,'Thank you madam. I have reported for duty today.','2026-10-20 15:29:08'),(3,1,2,'Great. I will conduct the university supervision visit around week 9.','2026-10-20 15:29:08'),(4,2,6,'Welcome to the TNB Cloud Engineering team, Aly! Let us know if you need access permissions.','2026-10-20 15:29:08'),(5,2,11,'Thank you Dr. Faizal. I have set up my local dev environment and Docker containers.','2026-10-20 15:29:08'),(6,2,2,'Dr. Ahmed here. Remember to submit your weekly logbooks on time every Friday.','2026-10-20 15:29:08'),(7,2,2,'Meeting link for our Week 8 Mid-Term Supervision Sync: https://meet.google.com/xyz-internflow','2026-10-20 15:29:08');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `monthly_reports`
--

DROP TABLE IF EXISTS `monthly_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monthly_reports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `placement_id` int NOT NULL,
  `period_no` int NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitted_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_plc_period` (`placement_id`,`period_no`),
  KEY `idx_mr_plc` (`placement_id`),
  CONSTRAINT `fk_mr_plc` FOREIGN KEY (`placement_id`) REFERENCES `placements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `monthly_reports`
--

LOCK TABLES `monthly_reports` WRITE;
/*!40000 ALTER TABLE `monthly_reports` DISABLE KEYS */;
INSERT INTO `monthly_reports` VALUES (1,2,1,'Completed Months 1 training objectives covering server migration, Docker containerization, and unit test suites.','reports/month1_aly.pdf','Month1_Summary_Aly.pdf','2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08');
/*!40000 ALTER TABLE `monthly_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`),
  KEY `idx_notif_read` (`read_at`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,11,'placement_approved','Placement Approved','Your placement at Tenaga Nasional Berhad has been approved by the coordinator.','/placements/my','2026-10-20 23:29:09','2026-10-20 15:29:09'),(2,11,'logbook_approved','Logbook Week 5 Approved','Your university supervisor has reviewed and approved your Week 5 logbook (Grade: 97.5%).','/logbooks',NULL,'2026-10-20 15:29:09');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pr_user` (`user_id`),
  KEY `idx_pr_token` (`token_hash`),
  CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `placements`
--

DROP TABLE IF EXISTS `placements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `placements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_user_id` int NOT NULL,
  `company_id` int NOT NULL,
  `vacancy_id` int DEFAULT NULL,
  `application_id` int DEFAULT NULL,
  `source` enum('platform','self_sourced') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'platform',
  `position` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `supervisor_contact_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supervisor_contact_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_supervisor_user_id` int DEFAULT NULL,
  `university_supervisor_id` int DEFAULT NULL,
  `status` enum('placement_pending','university_approved','internship_active','completed','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'placement_pending',
  `approved_by` int DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_plc_student` (`student_user_id`),
  KEY `idx_plc_company` (`company_id`),
  KEY `idx_plc_status` (`status`),
  KEY `idx_plc_uni_sup` (`university_supervisor_id`),
  KEY `idx_plc_comp_sup` (`company_supervisor_user_id`),
  KEY `fk_plc_vac` (`vacancy_id`),
  KEY `fk_plc_app` (`application_id`),
  KEY `fk_plc_approver` (`approved_by`),
  CONSTRAINT `fk_plc_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_plc_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_plc_comp_sup` FOREIGN KEY (`company_supervisor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_plc_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_plc_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_plc_uni_sup` FOREIGN KEY (`university_supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_plc_vac` FOREIGN KEY (`vacancy_id`) REFERENCES `vacancies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `placements`
--

LOCK TABLES `placements` WRITE;
/*!40000 ALTER TABLE `placements` DISABLE KEYS */;
INSERT INTO `placements` VALUES (1,12,1,1,NULL,'platform','Full-Stack Software Engineering Intern','2026-07-14','2026-10-06','Siti Aminah','hr@petronas.test',5,2,'completed',1,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(2,11,2,3,NULL,'platform','Software Engineering Intern','2026-09-08','2026-12-01','Faizal bin Osman','intern@tnb.com.my',6,2,'internship_active',1,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(3,13,3,5,NULL,'platform','Fintech Mobile Engineer Intern','2026-11-01','2027-01-24','Marcus Wong','techintern@cimb.com',NULL,3,'university_approved',1,'2026-10-20 23:29:08','2026-10-20 15:29:08','2026-10-20 15:29:08'),(4,14,4,7,NULL,'platform','AI & Computer Vision Intern','2026-11-01','2027-01-24','Azlan Shah','careers@aerodyne.group',NULL,NULL,'placement_pending',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(5,15,7,NULL,NULL,'self_sourced','Robotics Systems Engineering Intern','2026-11-15','2027-02-07','Engr. David Tan','david.tan@nextech.test',NULL,NULL,'placement_pending',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08');
/*!40000 ALTER TABLE `placements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resource_categories`
--

DROP TABLE IF EXISTS `resource_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resource_categories`
--

LOCK TABLES `resource_categories` WRITE;
/*!40000 ALTER TABLE `resource_categories` DISABLE KEYS */;
INSERT INTO `resource_categories` VALUES (1,'Forms',1,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(2,'Templates',2,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(3,'Guidelines and Rubrics',3,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(4,'Briefing Slides',4,'2026-10-20 15:29:09','2026-10-20 15:29:09');
/*!40000 ALTER TABLE `resource_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources`
--

DROP TABLE IF EXISTS `resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int NOT NULL,
  `version` int NOT NULL DEFAULT '1',
  `status` enum('draft','published','unpublished') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_res_cat` (`category_id`),
  KEY `idx_res_status` (`status`),
  KEY `fk_res_creator` (`created_by`),
  CONSTRAINT `fk_res_cat` FOREIGN KEY (`category_id`) REFERENCES `resource_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_res_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources`
--

LOCK TABLES `resources` WRITE;
/*!40000 ALTER TABLE `resources` DISABLE KEYS */;
INSERT INTO `resources` VALUES (1,4,'1st Briefing Slide Presentation','Official slides for 1st briefing semester.','resources/1st_briefing_slide_presentation.pdf','1st_briefing_slide_presentation.pdf','application/pdf',824,1,'published','2026-10-20 23:29:09',1,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(2,4,'Final Briefing Slide Presentation','Final briefing slides before reporting to duty.','resources/final_briefing_slide_presentation.pdf','final_briefing_slide_presentation.pdf','application/pdf',831,1,'published','2026-10-20 23:29:09',1,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(3,1,'Form 1 (Offer Letter Form)','Official F1 offer letter confirmation form.','resources/form_1_offer_letter_form_.pdf','form_1_offer_letter_form_.pdf','application/pdf',822,1,'published','2026-10-20 23:29:09',1,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(4,1,'Form 2 (Report for Duty Form)','Official F2 report for duty form to be submitted within week 1.','resources/form_2_report_for_duty_form_.pdf','form_2_report_for_duty_form_.pdf','application/pdf',845,1,'published','2026-10-20 23:29:09',1,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(5,1,'Industrial Training Application Letter','Formal university introduction letter to prospective host companies.','resources/industrial_training_application_letter.pdf','industrial_training_application_letter.pdf','application/pdf',857,1,'published','2026-10-20 23:29:09',1,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(6,1,'IT Completion Acknowledgement Form','Acknowledgment form completed at end of 12-week period.','resources/it_completion_acknowledgement_form.pdf','it_completion_acknowledgement_form.pdf','application/pdf',840,1,'published','2026-10-20 23:29:09',1,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(7,2,'Log Book Template','Word template for supplementary logbook printouts.','resources/log_book_template.pdf','log_book_template.pdf','application/pdf',818,1,'published','2026-10-20 23:29:09',1,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(8,2,'Report Template','Standard LaTeX and Word format for final industrial training report.','resources/report_template.pdf','report_template.pdf','application/pdf',834,1,'published','2026-10-20 23:29:09',1,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(9,2,'Report Template User Manual','User guide on report formatting and citation standards.','resources/report_template_user_manual.pdf','report_template_user_manual.pdf','application/pdf',833,1,'published','2026-10-20 23:29:09',1,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(10,3,'Report Guideline','Comprehensive guidelines on technical report requirements.','resources/report_guideline.pdf','report_guideline.pdf','application/pdf',825,1,'published','2026-10-20 23:29:09',1,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(11,2,'Template Visit Presentation Slide','Standard slide format for visiting lecturer assessment.','resources/template_visit_presentation_slide.pdf','template_visit_presentation_slide.pdf','application/pdf',839,1,'published','2026-10-20 23:29:09',1,'2026-10-20 15:29:09','2026-10-20 15:29:09'),(12,3,'CCI Internship Evaluation Rubric','Official marking rubric for host and university supervisors.','resources/cci_internship_evaluation_rubric.pdf','cci_internship_evaluation_rubric.pdf','application/pdf',843,1,'published','2026-10-20 23:29:09',1,'2026-10-20 15:29:09','2026-10-20 15:29:09');
/*!40000 ALTER TABLE `resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('attendance_alert_pct','80'),('max_upload_mb','10'),('session_timeout_min','30'),('token_expiry_hours','72'),('weight_host','40'),('weight_logbook','10'),('weight_report','20'),('weight_visiting','30');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `user_id` int NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `programme` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `intake` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cgpa` decimal(3,2) DEFAULT NULL,
  `skills` text COLLATE utf8mb4_unicode_ci,
  `interests` text COLLATE utf8mb4_unicode_ci,
  `emergency_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '1',
  `profile_complete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `student_id` (`student_id`),
  CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (11,'SW01084546','Bachelor of Computer Science (Software Engineering)','2023/2024-01',3.82,'PHP, MySQL, JavaScript, HTML5, CSS3, Git, Software Architecture','Web Platforms, Distributed Systems, Cloud Architecture','Said Mahmoud (+60123456789 - Father)',NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(12,'SW01084501','Bachelor of Computer Science (Software Engineering)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(13,'SW01084502','Bachelor of Computer Science (Software Engineering)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(14,'SW01084503','Bachelor of Computer Science (Software Engineering)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(15,'CS01084504','Bachelor of Computer Science (Cyber Security)','2023/2024-02',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(16,'CS01084505','Bachelor of Computer Science (Cyber Security)','2023/2024-02',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(17,'IT01084506','Bachelor of Information Technology (Information Systems)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(18,'IT01084507','Bachelor of Information Technology (Information Systems)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(19,'SW01084508','Bachelor of Computer Science (Software Engineering)','2023/2024-02',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(20,'SW01084509','Bachelor of Computer Science (Software Engineering)','2023/2024-02',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(21,'CS01084510','Bachelor of Computer Science (Cyber Security)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(22,'IT01084511','Bachelor of Information Technology (Information Systems)','2023/2024-02',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(23,'SW01084512','Bachelor of Computer Science (Software Engineering)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(24,'SW01084513','Bachelor of Computer Science (Software Engineering)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(25,'CS01084514','Bachelor of Computer Science (Cyber Security)','2023/2024-02',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(26,'SW01084515','Bachelor of Computer Science (Software Engineering)','2023/2024-02',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(27,'IT01084516','Bachelor of Information Technology (Information Systems)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(28,'SW01084517','Bachelor of Computer Science (Software Engineering)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(29,'CS01084518','Bachelor of Computer Science (Cyber Security)','2023/2024-02',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(30,'SW01084519','Bachelor of Computer Science (Software Engineering)','2023/2024-02',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(31,'IT01084520','Bachelor of Information Technology (Information Systems)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(32,'SW01084521','Bachelor of Computer Science (Software Engineering)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(33,'CS01084522','Bachelor of Computer Science (Cyber Security)','2023/2024-02',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(34,'SW01084523','Bachelor of Computer Science (Software Engineering)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(35,'IT01084524','Bachelor of Information Technology (Information Systems)','2023/2024-02',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(36,'SW01084525','Bachelor of Computer Science (Software Engineering)','2023/2024-02',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(37,'CS01084526','Bachelor of Computer Science (Cyber Security)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(38,'SW01084527','Bachelor of Computer Science (Software Engineering)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(39,'IT01084528','Bachelor of Information Technology (Information Systems)','2023/2024-02',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(40,'SW01084529','Bachelor of Computer Science (Software Engineering)','2023/2024-02',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(41,'SW01084530','Bachelor of Computer Science (Software Engineering)','2023/2024-01',3.45,NULL,NULL,NULL,NULL,1,1,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(44,'SW02090001','Bachelor of Computer Science (Software Engineering)','2024/2025-01',NULL,NULL,NULL,NULL,NULL,1,0,'2026-10-20 15:38:44','2026-10-20 15:38:44'),(45,'SW02090002','Bachelor of Computer Science (Software Engineering)','2024/2025-01',NULL,NULL,NULL,NULL,NULL,1,0,'2026-10-20 15:38:44','2026-10-20 15:38:44'),(46,'CS02090003','Bachelor of Computer Science (Cyber Security)','2024/2025-01',NULL,NULL,NULL,NULL,NULL,1,0,'2026-10-20 15:38:45','2026-10-20 15:38:45'),(47,'IT02090004','Bachelor of Information Technology (Information Systems)','2024/2025-01',NULL,NULL,NULL,NULL,NULL,1,0,'2026-10-20 15:38:45','2026-10-20 15:38:45'),(48,'SW02090005','Bachelor of Computer Science (Software Engineering)','2024/2025-01',NULL,NULL,NULL,NULL,NULL,1,0,'2026-10-20 15:38:45','2026-10-20 15:38:45');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role` enum('student','company','university_supervisor','coordinator') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('pending','active','suspended','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `scope_placement_id` int DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_user_role` (`role`),
  KEY `idx_user_status` (`status`),
  KEY `idx_user_scope` (`scope_placement_id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'coordinator','Dr. Norhaslinda Kamaruddin','coordinator@internflow.test','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,'2026-10-20 23:37:27','2026-10-20 15:29:07','2026-10-20 15:37:27'),(2,'university_supervisor','Dr. Ahmed Mubarak Ahmed Al-Haiqi','dr.ahmed@uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(3,'university_supervisor','Dr. Loo Yim Ling','dr.loo@uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(4,'university_supervisor','Ts. Dr. Zulkifli bin Ishak','dr.zulkifli@uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(5,'company','Siti Aminah (Petronas HR)','hr@petronas.test','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(6,'company','Faizal Osman (TNB Supervisor)','intern@tnb.com.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(7,'company','Marcus Wong (CIMB Lead)','techintern@cimb.com','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(8,'company','Azlan Shah (Aerodyne HR)','careers@aerodyne.group','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(9,'company','Nadiah Yusof','hr@cybersecurity.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(10,'company','Kevin Tan','talent@grab.com','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:07','2026-10-20 15:29:07'),(11,'student','Aly Youssef Said Mahmoud','aly.youssef@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(12,'student','Ahmad bin Razak','ahmad.razak@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(13,'student','Nurul Huda binti Ismail','nurul.huda@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(14,'student','Tan Wei Ming','tan.weiming@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(15,'student','Siti Sarah binti Abdullah','siti.sarah@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(16,'student','Kavitha a/p Subramaniam','kavitha.s@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(17,'student','Muhammad Hafiz bin Yusof','hafiz.yusof@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(18,'student','Lee Jia Wei','lee.jiawei@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(19,'student','Farah Nadia binti Zainal','farah.nadia@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(20,'student','Daniel Lim Jun Kai','daniel.lim@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(21,'student','Anis Farhana binti Roslan','anis.farhana@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(22,'student','Amirul Asyraf bin Kamarudin','amirul.asyraf@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(23,'student','Chong Mei Ling','chong.meiling@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(24,'student','Arun a/l Murugan','arun.murugan@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(25,'student','Ain Zulaikha binti Othman','ain.zulaikha@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(26,'student','Mohd Syamil bin Zakaria','syamil.zakaria@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(27,'student','Goh Jun Hao','goh.junhao@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(28,'student','Natasha binti Azmi','natasha.azmi@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(29,'student','Devan a/l Ramasamy','devan.r@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(30,'student','Syed Muhammad bin Syed Ali','syed.muhammad@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(31,'student','Wong Kah Yee','wong.kahyee@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(32,'student','Fatin Nabilah binti Ahmad','fatin.nabilah@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(33,'student','Haris Danial bin Mansor','haris.danial@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(34,'student','Lim Wei Shan','lim.weishan@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(35,'student','Nur Izzati binti Mahadi','izzati.mahadi@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(36,'student','Sharvin a/l Naidu','sharvin.naidu@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(37,'student','Luqman Hakim bin Idris','luqman.idris@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(38,'student','Teoh Xin Yi','teoh.xinyi@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(39,'student','Aisyah Humaira binti Zamri','aisyah.zamri@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(40,'student','Khoo Boon Kiat','khoo.boonkiat@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(41,'student','Siti Nurhaliza binti Bakar','siti.nurhaliza@student.uniten.edu.my','$2y$10$s8w36zYI7RHfkLeDzS6hKuAu7R5vCMKf4YQIhjyVaP1mAlW28G/wW',0,'active',NULL,NULL,'2026-10-20 15:29:08','2026-10-20 15:29:08'),(44,'student','Adam Daniel bin Zaki','adam.daniel@student.uniten.edu.my','$2y$10$le5o1Tq6XJmlqVyACikgsu89sARtz18WmT9wbUGtnYxFzqsvLeXde',1,'active',NULL,NULL,'2026-10-20 15:38:44','2026-10-20 15:38:44'),(45,'student','Melissa Tan Shu En','melissa.tan@student.uniten.edu.my','$2y$10$lWbJJQEqRezIr9UEHBafqOLN0V6bQ6wIE8BbBGwcgVgishbLY3xi.',1,'active',NULL,NULL,'2026-10-20 15:38:44','2026-10-20 15:38:44'),(46,'student','Pravin a/l Mohan','pravin.mohan@student.uniten.edu.my','$2y$10$UFUE2AtDcIepaTAUl4OwJuNUuausLQZcUqaLOQUCSVNEPqnihDMvK',1,'active',NULL,NULL,'2026-10-20 15:38:45','2026-10-20 15:38:45'),(47,'student','Sarah binti Kamaruzzaman','sarah.k@student.uniten.edu.my','$2y$10$KE5bqhCrmsWNgTQmC.p02OH1d/6taeENsB8qy0quCgB1iRXWODWqO',1,'active',NULL,NULL,'2026-10-20 15:38:45','2026-10-20 15:38:45'),(48,'student','Wong Zhi Wei','wong.zhiwei@student.uniten.edu.my','$2y$10$qScesIiZN7OZjEUcORA8HeASfyrX5PB2Wyl3mkSVsLgNSFyD.Q26y',1,'active',NULL,NULL,'2026-10-20 15:38:45','2026-10-20 15:38:45');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vacancies`
--

DROP TABLE IF EXISTS `vacancies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vacancies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duties` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `requirements` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `slots` int NOT NULL DEFAULT '1',
  `allowance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `working_arrangement` enum('onsite','remote','hybrid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'onsite',
  `duration_weeks` int NOT NULL DEFAULT '12',
  `deadline` date NOT NULL,
  `status` enum('draft','published','closed','flagged','deactivated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vac_company` (`company_id`),
  KEY `idx_vac_status` (`status`),
  KEY `idx_vac_deadline` (`deadline`),
  CONSTRAINT `fk_vac_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vacancies`
--

LOCK TABLES `vacancies` WRITE;
/*!40000 ALTER TABLE `vacancies` DISABLE KEYS */;
INSERT INTO `vacancies` VALUES (1,1,'Software Engineer Intern (Full-Stack Web & Cloud)','Assist the Digital Engineering team in building enterprise web portals.\nDevelop REST APIs in PHP/Python and front-end modules.\nParticipate in daily Agile scrums and code reviews.','Proficiency in PHP, JavaScript, SQL. Knowledge of Git version control. CGPA 3.00 and above.',3,1200.00,'Kuala Lumpur','hybrid',12,'2026-11-19','published','2026-10-20 15:29:08','2026-10-20 15:29:08'),(2,1,'Data Analyst Intern (Energy Insights)','Analyse telemetry and process data from offshore operations. Build interactive dashboards.','Python, SQL, Tableau/PowerBI knowledge. Strong numerical reasoning.',2,1200.00,'Kuala Lumpur','onsite',12,'2026-11-09','published','2026-10-20 15:29:08','2026-10-20 15:29:08'),(3,2,'Cloud Infrastructure & DevOps Intern','Support grid automation and server migration to AWS cloud. Maintain Linux virtual machines and monitoring alerts.','Linux shell, basic networking, Docker concepts.',2,1000.00,'Bangsar, Kuala Lumpur','onsite',12,'2026-11-14','published','2026-10-20 15:29:08','2026-10-20 15:29:08'),(4,2,'Cybersecurity Analyst Intern (SOC Monitoring)','Monitor security incident logs, assist vulnerability scanning and participate in threat hunting drills.','Cybersecurity coursework, Wireshark, SIEM tools familiarity.',2,1000.00,'Kuala Lumpur','hybrid',12,'2026-11-04','published','2026-10-20 15:29:08','2026-10-20 15:29:08'),(5,3,'Mobile App Developer Intern (iOS/Android)','Contribute to next-gen retail banking applications. Write clean unit tests and UI components.','Mobile development experience, Flutter/React Native/Swift, RESTful APIs.',4,1500.00,'KL Sentral','hybrid',12,'2026-11-24','published','2026-10-20 15:29:08','2026-10-20 15:29:08'),(6,3,'Fintech QA Automation Intern','Design automated test scripts for payment processing gateways.','Selenium, Cypress or Postman test scripting.',2,1400.00,'KL Sentral','onsite',12,'2026-10-30','published','2026-10-20 15:29:08','2026-10-20 15:29:08'),(7,4,'AI & Computer Vision Intern','Develop object detection pipelines for drone aerial imagery.','PyTorch/TensorFlow, OpenCV, Python.',2,1300.00,'Cyberjaya','remote',12,'2026-11-29','published','2026-10-20 15:29:08','2026-10-20 15:29:08'),(8,4,'Drone IoT Systems Engineering Intern','Telemetry protocols and embedded controller firmware integration.','C/C++, Raspberry Pi/Arduino, IoT protocols (MQTT).',1,1200.00,'Cyberjaya','onsite',12,'2026-11-03','published','2026-10-20 15:29:08','2026-10-20 15:29:08'),(9,1,'Enterprise Architecture Research Intern','Evaluate modern microservices architectures and legacy systems.','Software engineering principles and UML diagrams.',1,1100.00,'Kuala Lumpur','remote',12,'2026-12-19','draft','2026-10-20 15:29:08','2026-10-20 15:29:08'),(10,2,'IT Helpdesk Support Specialist Intern','Provide level 1 technical support for internal staff across headquarters.','Good communication, Windows Active Directory, troubleshooting.',2,900.00,'Kuala Lumpur','onsite',12,'2026-10-15','closed','2026-10-20 15:29:08','2026-10-20 15:29:08'),(11,3,'Unpaid Marketing & Coffee Delivery Intern','Run personal errands and deliver beverages.','Own transport.',1,0.00,'Kuala Lumpur','onsite',12,'2026-10-30','flagged','2026-10-20 15:29:08','2026-10-20 15:29:08'),(12,3,'Cryptocurrency Miner Operations Intern','Oversee high-voltage mining rigs.','Crypto trading experience.',1,500.00,'Kuala Lumpur','onsite',12,'2026-10-25','deactivated','2026-10-20 15:29:08','2026-10-20 15:29:08');
/*!40000 ALTER TABLE `vacancies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'internflow'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-21  0:07:29
