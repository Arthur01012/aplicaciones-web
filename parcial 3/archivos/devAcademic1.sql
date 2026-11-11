/*
SQLyog Ultimate v13.1.1 (64 bit)
MySQL - 11.4.13-MariaDB-cll-lve-log : Database - innegone_bd_devacademic
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`innegone_bd_devacademic` /*!40100 DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci */;

USE `innegone_bd_devacademic`;

/*Table structure for table `assignments` */

DROP TABLE IF EXISTS `assignments`;

CREATE TABLE `assignments` (
  `assignment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `assignment_code` varchar(20) NOT NULL,
  `course_id` int(10) unsigned NOT NULL,
  `title` varchar(160) NOT NULL,
  `due_date` date DEFAULT NULL,
  PRIMARY KEY (`assignment_id`),
  UNIQUE KEY `uq_assignments_code` (`assignment_code`),
  KEY `fk_assignments_course` (`course_id`),
  CONSTRAINT `fk_assignments_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `assignments` */

insert  into `assignments`(`assignment_id`,`assignment_code`,`course_id`,`title`,`due_date`) values 
(1,'HTML-GRID',1,'Práctica 2: Grid','2026-11-15'),
(2,'JS-FINAL',2,'Proyecto Final','2026-11-20'),
(3,'DB-EXAM',3,'Examen Parcial','2026-11-18'),
(4,'WEB-PROTOTYPE',4,'Prototipo de plataforma','2026-11-22');

/*Table structure for table `courses` */

DROP TABLE IF EXISTS `courses`;

CREATE TABLE `courses` (
  `course_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_code` varchar(12) NOT NULL,
  `name` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`course_id`),
  UNIQUE KEY `uq_courses_code` (`course_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `courses` */

insert  into `courses`(`course_id`,`course_code`,`name`,`is_active`) values 
(1,'HTML','HTML5 & CSS3',1),
(2,'JS','JavaScript',1),
(3,'DB','Bases de Datos',1),
(4,'WEB','Desarrollo Web',1);

/*Table structure for table `cursos` */

DROP TABLE IF EXISTS `cursos`;

CREATE TABLE `cursos` (
  `idcurso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(10,2) unsigned NOT NULL DEFAULT 0.00,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idcurso`),
  KEY `idx_cursos_destacado` (`destacado`),
  KEY `idx_cursos_titulo` (`titulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `cursos` */

/*Table structure for table `notices` */

DROP TABLE IF EXISTS `notices`;

CREATE TABLE `notices` (
  `notice_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) NOT NULL,
  `message` varchar(500) NOT NULL,
  `published_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`notice_id`),
  UNIQUE KEY `uq_notices_title_date` (`title`,`published_at`),
  KEY `idx_notices_active_date` (`is_active`,`published_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `notices` */

insert  into `notices`(`notice_id`,`title`,`message`,`published_at`,`is_active`) values 
(1,'Reunión docente','Viernes 15:00 hrs.','2026-10-20 09:00:00',1),
(2,'Cierre de actas','28 de octubre','2026-10-21 09:00:00',1);

/*Table structure for table `students` */

DROP TABLE IF EXISTS `students`;

CREATE TABLE `students` (
  `student_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `student_code` varchar(12) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `uq_students_code` (`student_code`),
  UNIQUE KEY `uq_students_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=260 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `students` */

insert  into `students`(`student_id`,`student_code`,`full_name`,`email`,`is_active`) values 
(1,'A001','Ana Gómez','ana.gomez@educontrol.local',1),
(2,'A002','Luis Hernández','luis.hernandez@educontrol.local',1),
(3,'A003','María López','maria.lopez@educontrol.local',1),
(4,'A004','Jorge Ruiz','jorge.ruiz@educontrol.local',1),
(5,'A005','Alumno 005','alumno005@educontrol.local',1),
(6,'A006','Alumno 006','alumno006@educontrol.local',1),
(7,'A007','Alumno 007','alumno007@educontrol.local',1),
(8,'A008','Alumno 008','alumno008@educontrol.local',1),
(9,'A009','Alumno 009','alumno009@educontrol.local',1),
(10,'A010','Alumno 010','alumno010@educontrol.local',1),
(11,'A011','Alumno 011','alumno011@educontrol.local',1),
(12,'A012','Alumno 012','alumno012@educontrol.local',1),
(13,'A013','Alumno 013','alumno013@educontrol.local',1),
(14,'A014','Alumno 014','alumno014@educontrol.local',1),
(15,'A015','Alumno 015','alumno015@educontrol.local',1),
(16,'A016','Alumno 016','alumno016@educontrol.local',1),
(17,'A017','Alumno 017','alumno017@educontrol.local',1),
(18,'A018','Alumno 018','alumno018@educontrol.local',1),
(19,'A019','Alumno 019','alumno019@educontrol.local',1),
(20,'A020','Alumno 020','alumno020@educontrol.local',1),
(21,'A021','Alumno 021','alumno021@educontrol.local',1),
(22,'A022','Alumno 022','alumno022@educontrol.local',1),
(23,'A023','Alumno 023','alumno023@educontrol.local',1),
(24,'A024','Alumno 024','alumno024@educontrol.local',1),
(25,'A025','Alumno 025','alumno025@educontrol.local',1),
(26,'A026','Alumno 026','alumno026@educontrol.local',1),
(27,'A027','Alumno 027','alumno027@educontrol.local',1),
(28,'A028','Alumno 028','alumno028@educontrol.local',1),
(29,'A029','Alumno 029','alumno029@educontrol.local',1),
(30,'A030','Alumno 030','alumno030@educontrol.local',1),
(31,'A031','Alumno 031','alumno031@educontrol.local',1),
(32,'A032','Alumno 032','alumno032@educontrol.local',1),
(33,'A033','Alumno 033','alumno033@educontrol.local',1),
(34,'A034','Alumno 034','alumno034@educontrol.local',1),
(35,'A035','Alumno 035','alumno035@educontrol.local',1),
(36,'A036','Alumno 036','alumno036@educontrol.local',1),
(37,'A037','Alumno 037','alumno037@educontrol.local',1),
(38,'A038','Alumno 038','alumno038@educontrol.local',1),
(39,'A039','Alumno 039','alumno039@educontrol.local',1),
(40,'A040','Alumno 040','alumno040@educontrol.local',1),
(41,'A041','Alumno 041','alumno041@educontrol.local',1),
(42,'A042','Alumno 042','alumno042@educontrol.local',1),
(43,'A043','Alumno 043','alumno043@educontrol.local',1),
(44,'A044','Alumno 044','alumno044@educontrol.local',1),
(45,'A045','Alumno 045','alumno045@educontrol.local',1),
(46,'A046','Alumno 046','alumno046@educontrol.local',1),
(47,'A047','Alumno 047','alumno047@educontrol.local',1),
(48,'A048','Alumno 048','alumno048@educontrol.local',1),
(49,'A049','Alumno 049','alumno049@educontrol.local',1),
(50,'A050','Alumno 050','alumno050@educontrol.local',1),
(51,'A051','Alumno 051','alumno051@educontrol.local',1),
(52,'A052','Alumno 052','alumno052@educontrol.local',1),
(53,'A053','Alumno 053','alumno053@educontrol.local',1),
(54,'A054','Alumno 054','alumno054@educontrol.local',1),
(55,'A055','Alumno 055','alumno055@educontrol.local',1),
(56,'A056','Alumno 056','alumno056@educontrol.local',1),
(57,'A057','Alumno 057','alumno057@educontrol.local',1),
(58,'A058','Alumno 058','alumno058@educontrol.local',1),
(59,'A059','Alumno 059','alumno059@educontrol.local',1),
(60,'A060','Alumno 060','alumno060@educontrol.local',1),
(61,'A061','Alumno 061','alumno061@educontrol.local',1),
(62,'A062','Alumno 062','alumno062@educontrol.local',1),
(63,'A063','Alumno 063','alumno063@educontrol.local',1),
(64,'A064','Alumno 064','alumno064@educontrol.local',1),
(65,'A065','Alumno 065','alumno065@educontrol.local',1),
(66,'A066','Alumno 066','alumno066@educontrol.local',1),
(67,'A067','Alumno 067','alumno067@educontrol.local',1),
(68,'A068','Alumno 068','alumno068@educontrol.local',1),
(69,'A069','Alumno 069','alumno069@educontrol.local',1),
(70,'A070','Alumno 070','alumno070@educontrol.local',1),
(71,'A071','Alumno 071','alumno071@educontrol.local',1),
(72,'A072','Alumno 072','alumno072@educontrol.local',1),
(73,'A073','Alumno 073','alumno073@educontrol.local',1),
(74,'A074','Alumno 074','alumno074@educontrol.local',1),
(75,'A075','Alumno 075','alumno075@educontrol.local',1),
(76,'A076','Alumno 076','alumno076@educontrol.local',1),
(77,'A077','Alumno 077','alumno077@educontrol.local',1),
(78,'A078','Alumno 078','alumno078@educontrol.local',1),
(79,'A079','Alumno 079','alumno079@educontrol.local',1),
(80,'A080','Alumno 080','alumno080@educontrol.local',1),
(81,'A081','Alumno 081','alumno081@educontrol.local',1),
(82,'A082','Alumno 082','alumno082@educontrol.local',1),
(83,'A083','Alumno 083','alumno083@educontrol.local',1),
(84,'A084','Alumno 084','alumno084@educontrol.local',1),
(85,'A085','Alumno 085','alumno085@educontrol.local',1),
(86,'A086','Alumno 086','alumno086@educontrol.local',1),
(87,'A087','Alumno 087','alumno087@educontrol.local',1),
(88,'A088','Alumno 088','alumno088@educontrol.local',1),
(89,'A089','Alumno 089','alumno089@educontrol.local',1),
(90,'A090','Alumno 090','alumno090@educontrol.local',1),
(91,'A091','Alumno 091','alumno091@educontrol.local',1),
(92,'A092','Alumno 092','alumno092@educontrol.local',1),
(93,'A093','Alumno 093','alumno093@educontrol.local',1),
(94,'A094','Alumno 094','alumno094@educontrol.local',1),
(95,'A095','Alumno 095','alumno095@educontrol.local',1),
(96,'A096','Alumno 096','alumno096@educontrol.local',1),
(97,'A097','Alumno 097','alumno097@educontrol.local',1),
(98,'A098','Alumno 098','alumno098@educontrol.local',1),
(99,'A099','Alumno 099','alumno099@educontrol.local',1),
(100,'A100','Alumno 100','alumno100@educontrol.local',1),
(101,'A101','Alumno 101','alumno101@educontrol.local',1),
(102,'A102','Alumno 102','alumno102@educontrol.local',1),
(103,'A103','Alumno 103','alumno103@educontrol.local',1),
(104,'A104','Alumno 104','alumno104@educontrol.local',1),
(105,'A105','Alumno 105','alumno105@educontrol.local',1),
(106,'A106','Alumno 106','alumno106@educontrol.local',1),
(107,'A107','Alumno 107','alumno107@educontrol.local',1),
(108,'A108','Alumno 108','alumno108@educontrol.local',1),
(109,'A109','Alumno 109','alumno109@educontrol.local',1),
(110,'A110','Alumno 110','alumno110@educontrol.local',1),
(111,'A111','Alumno 111','alumno111@educontrol.local',1),
(112,'A112','Alumno 112','alumno112@educontrol.local',1),
(113,'A113','Alumno 113','alumno113@educontrol.local',1),
(114,'A114','Alumno 114','alumno114@educontrol.local',1),
(115,'A115','Alumno 115','alumno115@educontrol.local',1),
(116,'A116','Alumno 116','alumno116@educontrol.local',1),
(117,'A117','Alumno 117','alumno117@educontrol.local',1),
(118,'A118','Alumno 118','alumno118@educontrol.local',1),
(119,'A119','Alumno 119','alumno119@educontrol.local',1),
(120,'A120','Alumno 120','alumno120@educontrol.local',1),
(121,'A121','Alumno 121','alumno121@educontrol.local',1),
(122,'A122','Alumno 122','alumno122@educontrol.local',1),
(123,'A123','Alumno 123','alumno123@educontrol.local',1),
(124,'A124','Alumno 124','alumno124@educontrol.local',1),
(125,'A125','Alumno 125','alumno125@educontrol.local',1),
(126,'A126','Alumno 126','alumno126@educontrol.local',1),
(127,'A127','Alumno 127','alumno127@educontrol.local',1),
(128,'A128','Alumno 128','alumno128@educontrol.local',1),
(129,'A129','Alumno 129','alumno129@educontrol.local',1),
(130,'A130','Alumno 130','alumno130@educontrol.local',1),
(131,'A131','Alumno 131','alumno131@educontrol.local',1),
(132,'A132','Alumno 132','alumno132@educontrol.local',1),
(133,'A133','Alumno 133','alumno133@educontrol.local',1),
(134,'A134','Alumno 134','alumno134@educontrol.local',1),
(135,'A135','Alumno 135','alumno135@educontrol.local',1),
(136,'A136','Alumno 136','alumno136@educontrol.local',1),
(137,'A137','Alumno 137','alumno137@educontrol.local',1),
(138,'A138','Alumno 138','alumno138@educontrol.local',1),
(139,'A139','Alumno 139','alumno139@educontrol.local',1),
(140,'A140','Alumno 140','alumno140@educontrol.local',1),
(141,'A141','Alumno 141','alumno141@educontrol.local',1),
(142,'A142','Alumno 142','alumno142@educontrol.local',1);

/*Table structure for table `submissions` */

DROP TABLE IF EXISTS `submissions`;

CREATE TABLE `submissions` (
  `submission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int(10) unsigned NOT NULL,
  `assignment_id` int(10) unsigned NOT NULL,
  `status` enum('entregado','pendiente') NOT NULL DEFAULT 'pendiente',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`submission_id`),
  UNIQUE KEY `uq_submissions_student_assignment` (`student_id`,`assignment_id`),
  KEY `idx_submissions_status` (`status`),
  KEY `idx_submissions_updated` (`updated_at`),
  KEY `fk_submissions_assignment` (`assignment_id`),
  CONSTRAINT `fk_submissions_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`assignment_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_submissions_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `submissions` */

insert  into `submissions`(`submission_id`,`student_id`,`assignment_id`,`status`,`updated_at`) values 
(1,1,1,'entregado','2026-11-06 12:45:00'),
(2,2,2,'pendiente','2026-11-05 10:30:00'),
(3,3,1,'entregado','2026-11-04 15:10:00'),
(4,4,3,'entregado','2026-11-03 09:20:00'),
(5,5,1,'pendiente','2026-10-27 09:00:00'),
(6,6,2,'pendiente','2026-10-26 09:00:00'),
(7,7,3,'pendiente','2026-10-25 09:00:00'),
(8,8,4,'pendiente','2026-10-24 09:00:00'),
(9,9,1,'pendiente','2026-10-23 09:00:00'),
(10,10,2,'pendiente','2026-10-22 09:00:00'),
(11,11,3,'pendiente','2026-10-21 09:00:00'),
(12,12,4,'pendiente','2026-10-20 09:00:00'),
(13,13,1,'pendiente','2026-10-19 09:00:00'),
(14,14,2,'pendiente','2026-10-18 09:00:00'),
(15,15,3,'pendiente','2026-10-17 09:00:00'),
(16,16,4,'pendiente','2026-10-16 09:00:00'),
(17,17,1,'pendiente','2026-10-15 09:00:00'),
(18,18,2,'pendiente','2026-10-14 09:00:00'),
(19,19,3,'pendiente','2026-10-13 09:00:00'),
(20,20,4,'pendiente','2026-10-12 09:00:00'),
(21,21,1,'pendiente','2026-10-11 09:00:00');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'docente',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `users` */

insert  into `users`(`user_id`,`username`,`password_hash`,`full_name`,`role`,`is_active`) values 
(1,'carlos.mendoza','$2y$12$2wx1K0GO1bp7S8ORKwbohOktn8VGqZxEj2uZMZF3gLb7uEyIx51iu','Prof. Carlos Mendoza','docente',1);

/*Table structure for table `usuarios` */

DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `psw` varchar(255) NOT NULL,
  `rol` varchar(20) NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuarios_email` (`email`),
  KEY `idx_usuarios_rol_activo` (`rol`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `usuarios` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
