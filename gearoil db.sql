/*
SQLyog Community v12.5.0 (64 bit)
MySQL - 5.7.19 : Database - gearoil
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`gearoil` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

USE `gearoil`;

/*Table structure for table `bikes` */

DROP TABLE IF EXISTS `bikes`;

CREATE TABLE `bikes` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) CHARACTER SET utf16 COLLATE utf16_unicode_ci NOT NULL,
  `bike_name` varchar(100) CHARACTER SET utf16 COLLATE utf16_unicode_ci NOT NULL,
  `model_year` int(10) NOT NULL,
  `member_id` int(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `bikes` */

insert  into `bikes`(`id`,`company_name`,`bike_name`,`model_year`,`member_id`) values 
(1,'BAJAJ','pulsur',2015,NULL),
(2,'SUZUKI','gixer GSX',2018,NULL),
(3,'BAJAJ','discover',2016,NULL),
(4,'HONDA','CB hornet',2018,NULL),
(9,'suzuki','gixer',2018,10),
(10,'suzuki','gixer',2018,11),
(11,'suzuki','gixer',2018,12),
(12,'suzuki','pul',2016,13),
(13,'suzuki','pul',2016,14);

/*Table structure for table `members` */

DROP TABLE IF EXISTS `members`;

CREATE TABLE `members` (
  `member_id` int(255) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf16 COLLATE utf16_unicode_ci NOT NULL,
  `userrole` int(255) NOT NULL,
  `email` varchar(255) CHARACTER SET utf16 COLLATE utf16_unicode_ci NOT NULL,
  `user_monthly_expenditure` float(50,2) NOT NULL DEFAULT '0.00',
  `user_yearly_expenditure` float(50,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`member_id`),
  KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `members` */

insert  into `members`(`member_id`,`username`,`userrole`,`email`,`user_monthly_expenditure`,`user_yearly_expenditure`,`created_at`) values 
(1,'testuser',1,'test@test.com',0.00,39250.00,'2018-08-25 18:24:21'),
(2,'zia',1,'zia@zia.com',0.00,0.00,'2018-08-25 18:24:21'),
(3,'rabi',1,'rabi@cse.com',0.00,0.00,'2018-08-25 18:24:21'),
(4,'sahid',1,'sahid@cse.com',0.00,7000.00,'2018-08-25 18:24:21'),
(10,'samada',1,'api@post.com',0.00,0.00,'2018-08-25 18:24:21'),
(12,'rup',1,'api@post.com',0.00,0.00,'2018-08-25 18:28:29'),
(13,'rahat',1,'sun@mon.bd',0.00,121.00,'2018-08-29 20:48:53');

/*Table structure for table `migrations` */

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `migrations` */

/*Table structure for table `service_cost` */

DROP TABLE IF EXISTS `service_cost`;

CREATE TABLE `service_cost` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `cost` float(20,2) NOT NULL,
  `rating` float(10,2) NOT NULL,
  `shop_name` varchar(100) CHARACTER SET utf16 COLLATE utf16_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_name` (`shop_name`),
  CONSTRAINT `fk_shop_name` FOREIGN KEY (`shop_name`) REFERENCES `shops` (`shop_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `service_cost` */

insert  into `service_cost`(`id`,`service_name`,`cost`,`rating`,`shop_name`) values 
(7,'Full Service',500.00,5.00,'labu'),
(8,'Full Service',600.00,5.00,'babul');

/*Table structure for table `shops` */

DROP TABLE IF EXISTS `shops`;

CREATE TABLE `shops` (
  `shop_id` int(255) NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(100) CHARACTER SET utf16 COLLATE utf16_unicode_ci NOT NULL,
  `location` varchar(255) CHARACTER SET utf16 COLLATE utf16_unicode_ci NOT NULL,
  `rating` float(10,2) NOT NULL,
  PRIMARY KEY (`shop_id`,`shop_name`),
  UNIQUE KEY `name` (`shop_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `shops` */

insert  into `shops`(`shop_id`,`shop_name`,`location`,`rating`) values 
(1,'babul','farmgate',4.50),
(2,'labu','changkharpul',4.50),
(3,'Trust','bijoy sharani',5.00),
(4,'Jamuna','asad gate',5.00),
(5,'dulal','kollanpur',5.00),
(6,'Jaber','kollanpur',5.00),
(7,'dulalMia','kollanpur',5.00),
(8,'lkhjkljkljkl','kollanpur',5.00);

/*Table structure for table `temporary_user_services` */

DROP TABLE IF EXISTS `temporary_user_services`;

CREATE TABLE `temporary_user_services` (
  `temporary_user_services_id` int(100) NOT NULL AUTO_INCREMENT,
  `member_id` int(100) DEFAULT NULL,
  `shop_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_amount` int(100) DEFAULT NULL,
  `shop_rating_by_user` int(100) DEFAULT NULL,
  `shop_review_by_user` text COLLATE utf8_unicode_ci,
  `service_date_time` datetime DEFAULT NULL,
  `shop_location` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`temporary_user_services_id`),
  KEY `member_id_tempoarary_user_services` (`member_id`),
  CONSTRAINT `member_id_tempoarary_user_services` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `temporary_user_services` */

insert  into `temporary_user_services`(`temporary_user_services_id`,`member_id`,`shop_name`,`service_name`,`service_amount`,`shop_rating_by_user`,`shop_review_by_user`,`service_date_time`,`shop_location`) values 
(1,12,'gearoil','full',600,4,'good','2018-08-07 16:49:01','tajmohal road'),
(2,12,'jamuna','full',500,3,'not that good\r\n','2018-07-02 16:50:15','badda'),
(7,12,'sahdjkashdj','asjdhaksdh',5060,2,'sasdasd','2018-09-13 18:41:45','asdasdasdasd'),
(8,12,'dulal','Full',500,5,NULL,'2018-09-13 18:41:45','kollanpur'),
(9,12,'Jaber','Full',500,5,NULL,'2018-09-13 18:41:45','kollanpur'),
(10,12,'dulal','Full',500,5,NULL,'2018-09-13 18:41:45','kollanpur'),
(11,1,'dulal','Full',1000,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(12,1,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(13,13,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(14,13,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(15,13,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(16,13,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(17,13,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(18,13,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(19,13,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(20,13,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(21,13,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(22,13,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(23,13,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(24,1,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(25,1,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(26,1,'dulal','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(27,1,'dulal','Full',600,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(28,1,'dulalMia','Full',6000,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(29,4,'dulalMia','Full',6000,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(30,1,'dulalMia','Full',6000,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(31,1,'dulalMia','Full',6000,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(32,1,'dulalMia','Full',6000,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(33,1,'dulalMia','Full',6000,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(34,4,'dulalMia','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(35,4,'lkhjkljkljkl','Full',500,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur'),
(36,1,'dulal','Full',650,5,'CHOLE AR KI','2018-09-13 18:41:45','kollanpur');

/*Table structure for table `user_services` */

DROP TABLE IF EXISTS `user_services`;

CREATE TABLE `user_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(255) NOT NULL,
  `service_id` int(255) NOT NULL,
  `user_rating` float(10,2) NOT NULL,
  `user_comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'n/a',
  `service_date` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_name` (`service_id`),
  KEY `fk_id_user` (`user_id`),
  CONSTRAINT `fk_id_user` FOREIGN KEY (`user_id`) REFERENCES `members` (`member_id`),
  CONSTRAINT `fk_service_id` FOREIGN KEY (`service_id`) REFERENCES `service_cost` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `user_services` */

insert  into `user_services`(`id`,`user_id`,`service_id`,`user_rating`,`user_comment`,`service_date`) values 
(1,1,7,4.00,'n/a','2018-02-09'),
(2,1,8,5.00,'n/a','2018-06-21');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`name`,`email`,`password`,`remember_token`,`created_at`,`updated_at`) values 
(1,'sunzid','sunzid02@yahoo.com','$2y$10$lxT7ZXHMyYKDpFp9TBG2ge5g1YnWrglseywFA0jSpxPTKpVATAzni','rgYtJvvPvzKEbbMQa3C81vkm4fHMHPZ74FAyQ5sYJvqzFoDXxHNUjnW1foLb','2018-08-21 10:56:42','2018-08-21 10:56:42');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
