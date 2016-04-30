/*
SQLyog Community Edition- MySQL GUI v6.14
MySQL - 5.0.27-community-nt : Database - trac
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

create database if not exists `trac`;

USE `trac`;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*Table structure for table `attachment` */

DROP TABLE IF EXISTS `attachment`;

CREATE TABLE `attachment` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` varchar(30) default NULL,
  `filename` varchar(50) NOT NULL,
  `type` varchar(30) default NULL,
  `size` int(11) default NULL,
  `time` datetime default NULL,
  `description` varchar(255) default NULL,
  `author` varchar(30) default NULL,
  `ipnr` varchar(30) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `attachment` */

insert  into `attachment`(`id`,`parent_id`,`filename`,`type`,`size`,`time`,`description`,`author`,`ipnr`) values (1,'TKT_2','favicon.ico','image/x-icon',3638,'2008-02-18 17:06:50','aaaa','test',NULL),(4,'TKT_2','106746Kg.rar','application/octet-stream',33047,'2008-02-18 17:08:30','replace fie test','rocky',NULL);

/*Table structure for table `comments` */

DROP TABLE IF EXISTS `comments`;

CREATE TABLE `comments` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` varchar(30) NOT NULL default '',
  `time` datetime default NULL,
  `author` varchar(30) default NULL,
  `comments` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `comments` */

/*Table structure for table `component` */

DROP TABLE IF EXISTS `component`;

CREATE TABLE `component` (
  `name` varchar(30) NOT NULL,
  `owner` varchar(30) default NULL,
  `description` text,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `component` */

insert  into `component`(`name`,`owner`,`description`) values ('BizDataObj','Rocky','Data layer'),('BizForm','Rocky','Presentation layer\n:)');

/*Table structure for table `enum` */

DROP TABLE IF EXISTS `enum`;

CREATE TABLE `enum` (
  `id` int(11) NOT NULL auto_increment,
  `type` varchar(30) default NULL,
  `name` varchar(50) default NULL,
  `value` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `enum` */

insert  into `enum`(`id`,`type`,`name`,`value`) values (1,'Priority','Minor','1'),(2,'Priority','Critical','2'),(3,'Priority','Major','3'),(4,'Severity','Low','1'),(5,'Severity','Mideum','2'),(6,'Severity','High','3'),(8,'Type','Defect','1'),(9,'Type','Feature','2'),(10,'Type','Enhancement','3'),(11,'Type','Task','4'),(12,'Status','Open','1'),(13,'Status','Accepted','2'),(14,'Status','Reopened','3'),(15,'Status','Resolved','4'),(16,'Status','Closed','5'),(17,'Resolution','Unresolved','1'),(18,'Resolution','Fixed','2'),(19,'Resolution','Won\'t Fix','3'),(20,'Resolution','Duplicated','4'),(21,'Resolution','Incomplete','5'),(22,'Resolution','Cannot Reproduce','6'),(23,'Priority','Blocker','4'),(24,'Priority','Trivial','5');

/*Table structure for table `milestone` */

DROP TABLE IF EXISTS `milestone`;

CREATE TABLE `milestone` (
  `name` varchar(30) NOT NULL,
  `due` datetime default NULL,
  `completed` datetime default NULL,
  `description` text,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `milestone` */

insert  into `milestone`(`name`,`due`,`completed`,`description`) values ('alpha','2008-02-12 00:04:40',NULL,'alpha version of the product'),('beta1','2008-02-27 00:05:31',NULL,NULL);

/*Table structure for table `ob_sysids` */

DROP TABLE IF EXISTS `ob_sysids`;

CREATE TABLE `ob_sysids` (
  `TABLENAME` char(20) NOT NULL default '',
  `PREFIX` char(10) default NULL,
  `IDBODY` int(11) default NULL,
  PRIMARY KEY  (`TABLENAME`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `ob_sysids` */

insert  into `ob_sysids`(`TABLENAME`,`PREFIX`,`IDBODY`) values ('ticket','TKT',22);

/*Table structure for table `ticket` */

DROP TABLE IF EXISTS `ticket`;

CREATE TABLE `ticket` (
  `id` varchar(30) NOT NULL,
  `type` varchar(30) default NULL,
  `time` datetime default NULL,
  `changetime` datetime default NULL,
  `component` varchar(30) default NULL,
  `serverity` varchar(30) default NULL,
  `priority` varchar(30) default NULL,
  `owner` varchar(30) default NULL,
  `reporter` varchar(30) default NULL,
  `cc` varchar(30) default NULL,
  `version` varchar(30) default NULL,
  `milestone` varchar(30) default NULL,
  `status` varchar(30) default NULL,
  `resolution` varchar(255) default NULL,
  `summary` varchar(255) default NULL,
  `description` text,
  `keywords` varchar(100) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `ticket` */

insert  into `ticket`(`id`,`type`,`time`,`changetime`,`component`,`serverity`,`priority`,`owner`,`reporter`,`cc`,`version`,`milestone`,`status`,`resolution`,`summary`,`description`,`keywords`) values ('TKT_18','Defect','2008-02-20 23:46:56','2008-02-21 01:29:00','BizForm','Low','Minor',NULL,NULL,NULL,'1.0','alpha','Resolved','Won\'t Fix','test openbiz trac','Build trac with openbiz \n- learn the trac data model (table)\n- build dataobjs, forms, views\n- write templates and css',NULL),('TKT_2','Defect','2008-02-06 23:12:37','2008-02-13 01:50:58','BizForm','Critial','Medium',NULL,NULL,NULL,'1.0','alpha','Open',NULL,'test openbiz trac','Build trac with openbiz \n- learn the trac data model (table)\n- build dataobjs, forms, views\n- write templates and css',NULL),('TKT_3',NULL,'2008-02-09 00:12:51','2008-02-09 00:12:51','BizForm',NULL,NULL,NULL,NULL,NULL,'1.0','alpha',NULL,NULL,'test','1234567890',NULL),('TKT_4',NULL,'2008-02-09 00:12:58','2008-02-09 00:12:58','BizForm',NULL,NULL,NULL,NULL,NULL,'1.0','alpha',NULL,NULL,'test','1234567890',NULL);

/*Table structure for table `ticket_change` */

DROP TABLE IF EXISTS `ticket_change`;

CREATE TABLE `ticket_change` (
  `id` int(11) NOT NULL auto_increment,
  `ticket` char(30) default NULL,
  `time` datetime default NULL,
  `author` varchar(50) default NULL,
  `field` varchar(30) default NULL,
  `oldvalue` text,
  `newvalue` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `ticket_change` */

insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (1,'TKT_2','2008-02-11 23:13:30','unkown','type','Enhancement','Defect'),(21,'TKT_2','2008-02-13 00:18:30','aaa','milestone','beta1','alpha'),(24,'TKT_2','2008-02-13 01:08:10','rocky','comment',NULL,'my comment 1'),(25,'TKT_2','2008-02-13 01:08:31','sad','status','Open','Fixed'),(26,'TKT_2','2008-02-13 01:12:24','aaa','status','Fixed','Open'),(28,'TKT_2','2008-02-13 01:13:41','aaa','milestone','alpha','beta1'),(29,'TKT_2','2008-02-13 01:17:36','qqq','milestone','beta1','alpha'),(30,'TKT_2','2008-02-13 01:17:36','qqq','description','1234567890\nasdsad','1234567890\r\nasdsad'),(32,'TKT_2','2008-02-13 01:23:16','qqq1','priority','High','Medium'),(33,'TKT_2','2008-02-13 01:23:16','qqq1','milestone','beta1','alpha'),(34,'TKT_2','2008-02-13 01:23:16','qqq1','description','1234567890\nasdsad','1234567890\r\nasdsad'),(36,'TKT_2','2008-02-13 01:31:12','www','milestone','beta1','alpha'),(38,'TKT_2','2008-02-13 01:50:59','rocky','description','1234567890\r\nasdsad','Build trac with openbiz \n- learn the trac data model (table)\n- build dataobjs, forms, views\n- write templates and css'),(39,'TKT_2','2008-02-18 17:26:36','rocky_test','comment',NULL,'The error I\'m referring to in comment:26 is an exception raised in a WikiSyntaxProvider, inside a wiki formatting done while rendering the timeline.\n\nThe corresponding backtrace for that error can be seen in attachment:timeline_rendering_backtrace.log\n\nNow, if I catch the exception before it goes through the template rendering, the memory usage is OK again. The live object count, as measured by doing a len(gc.get_objects()) after a gc.collect(), remains constant at 62642, after successive requests.\n\nIf I raise an exception on purpose after the template rendering, there\'s also no increase in memory usage.\n\nBut if the original exception is not caught, the live object count increase by ~50000 after each call.\n\nI\'ll try to locate the exact point in the backtrace which cause the memory retention.\n'),(40,'TKT_14','2008-02-20 01:33:04',NULL,'Id',NULL,'TKT_14'),(41,'TKT_14','2008-02-20 01:33:04',NULL,'type',NULL,'Defect'),(42,'TKT_14','2008-02-20 01:33:04',NULL,'component',NULL,'BizDataObj'),(43,'TKT_14','2008-02-20 01:33:04',NULL,'serverity',NULL,'Stopper'),(44,'TKT_14','2008-02-20 01:33:04',NULL,'priority',NULL,'High'),(45,'TKT_14','2008-02-20 01:33:04',NULL,'version',NULL,'1.0'),(46,'TKT_14','2008-02-20 01:33:04',NULL,'milestone',NULL,'alpha'),(47,'TKT_14','2008-02-20 01:33:04',NULL,'status',NULL,'Open'),(48,'TKT_14','2008-02-20 01:33:04',NULL,'summary',NULL,'This openbiz trac is used ti track issues'),(49,'TKT_16','2008-02-20 01:56:51','rocky','keywords',NULL,'trac, php, openbiz'),(51,'TKT_18','2008-02-21 01:07:43','rocky','serverity','Critial','Low'),(52,'TKT_18','2008-02-21 01:07:43','rocky','priority','Medium','Minor'),(53,'TKT_18','2008-02-21 01:29:00','rocky','status','Open','Resolved'),(54,'TKT_18','2008-02-21 01:29:00','rocky','resolution',NULL,'Won\'t Fix');

/*Table structure for table `version` */

DROP TABLE IF EXISTS `version`;

CREATE TABLE `version` (
  `name` varchar(30) NOT NULL,
  `time` datetime default NULL,
  `description` text,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `version` */

insert  into `version`(`name`,`time`,`description`) values ('1.0','2008-02-01 00:07:52',NULL),('1.01','2008-02-12 00:07:41',NULL);

/*Table structure for table `wiki` */

DROP TABLE IF EXISTS `wiki`;

CREATE TABLE `wiki` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(30) NOT NULL,
  `version` int(11) default NULL,
  `time` datetime default NULL,
  `author` varchar(30) default NULL,
  `ipnr` varchar(30) default NULL,
  `text` text,
  `comment` varchar(255) default NULL,
  `readonly` enum('Y','N') default 'N',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `wiki` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;


-- Database: `demo`
-- 

create database if not exists `demo`;

USE `demo`;

-- --------------------------------------------------------

-- 
-- Table structure for table `attach`
-- 

CREATE TABLE `attach` (
  `SYSID` varchar(20) NOT NULL default '',
  `PARENT_ID` varchar(20) NOT NULL default '',
  `NAME` varchar(50) NOT NULL default '',
  `SIZE` varchar(20) default NULL,
  `TYPE` varchar(40) default NULL,
  `LOCATION` varchar(50) default NULL,
  `DATA` mediumblob,
  PRIMARY KEY  (`SYSID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `attach`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `attendee`
-- 

CREATE TABLE `attendee` (
  `SYSID` varchar(20) NOT NULL default '',
  `LASTNAME` varchar(30) NOT NULL default '',
  `FIRSTNAME` varchar(30) NOT NULL default '',
  `OTHERNAME` varchar(50) default NULL,
  `PHONE` varchar(20) default NULL,
  `EMAIL` varchar(30) default NULL,
  `ADDRESS` varchar(50) default NULL,
  `DESCRIPTION` varchar(255) default NULL,
  `PHOTO` mediumblob,
  `TYPE` varchar(20) default NULL,
  `UPORG_ID` varchar(20) default NULL,
  `CHILD_FLAG` char(1) default NULL,
  PRIMARY KEY  (`SYSID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `attendee`
-- 

INSERT INTO `attendee` VALUES ('ATD_1', 'Liu', 'Michael', 'Will', '', 'mike@yahoo.com', '', '', '', '', '', '');
INSERT INTO `attendee` VALUES ('ATD_2', 'Houston', 'Alan', 'Houston', '6501111001', 'ahouston@yahoo.com', '', '', '', 'Individual', '', '');
INSERT INTO `attendee` VALUES ('ATD_6', 'Smith', 'Andy', 'obposi_6', '', 'asmith@yahoo.com', '', '', '', 'Individual', '', '');
INSERT INTO `attendee` VALUES ('ATD_4', 'Lee', 'Pete', 'obposi_6', '4081112100', 'plee@yahoo.com', '', '', '', 'Individual', '', '');
INSERT INTO `attendee` VALUES ('ATD_5', 'Lau', 'Frank', '??', '4081112100', 'flau@yahoo.com', '', '', '', 'Individual', '', '');
INSERT INTO `attendee` VALUES ('ATD_7', 'Monk', 'Steve', 'obposi_', '', 'smonk@yahoo.com', '', '', '', 'Individual', '', '');
INSERT INTO `attendee` VALUES ('ATD_8', 'Louis', 'Jeff', 'obposi_4', '', 'jlouis@yahoo.com', '', '', '', 'Individual', '', '');
INSERT INTO `attendee` VALUES ('ATD_22', 'Liu', 'Michael', 'obposi_4', '', 'mliu@yahoo.com', '', '', '', 'Individual', '', '');
INSERT INTO `attendee` VALUES ('ATD_50', 'Liu', 'Michael', 'obposi_4', '', 'mliu@yahoo.com', '', '', '', 'Individual', '', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `audit`
-- 

CREATE TABLE `audit` (
  `SYSID` varchar(20) NOT NULL default '',
  `DATAOBJNAME` varchar(100) NOT NULL default '',
  `OBJECTID` varchar(20) NOT NULL default '',
  `FIELDNAME` varchar(30) NOT NULL default '',
  `OLDVALUE` varchar(100) default NULL,
  `NEWVALUE` varchar(100) default NULL,
  `CHANGETIME` datetime default NULL,
  `CHANGEBY` varchar(20) default NULL,
  PRIMARY KEY  (`SYSID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `audit`
-- 

INSERT INTO `audit` VALUES ('ADT_10', 'demo.BOEvent', 'EVT_5', 'Name', 'NCCAF 2004 Basketball -4', 'NCCAF 2004 Basketball -5', '2006-04-27 01:14:49', '');
INSERT INTO `audit` VALUES ('ADT_11', 'demo.BOEvent', 'EVT_2', 'Name', 'NCCAF 2004 Badminton 10', 'NCCAF 2004 Badminton', '2006-05-07 08:27:09', '');
INSERT INTO `audit` VALUES ('ADT_12', 'demo.BOEvent', 'EVT_5', 'Name', 'NCCAF 2004 Basketball -5', 'NCCAF 2004 Basketball', '2006-05-07 08:27:32', '');
INSERT INTO `audit` VALUES ('ADT_5', 'demo.BOEvent', 'EVT_2', 'Name', 'NCCAF 2004 Badminton 1', 'NCCAF 2004 Badminton 10', '2006-04-27 00:43:27', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `autoincr`
-- 

CREATE TABLE `autoincr` (
  `ID` int(11) NOT NULL auto_increment,
  `NAME` varchar(30) default NULL,
  `DESCRIPTION` varchar(50) default NULL,
  `F_ID` int(11) default '10',
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- 
-- Dumping data for table `autoincr`
-- 

INSERT INTO `autoincr` VALUES (1, 'asd1', 'asd', 0);
INSERT INTO `autoincr` VALUES (2, 'asd2', 'test', 0);
INSERT INTO `autoincr` VALUES (3, 'aa3', 'asd', 0);
INSERT INTO `autoincr` VALUES (4, 'aaa', 'aaa', 0);
INSERT INTO `autoincr` VALUES (5, 'bbb', 'bbb', 0);
INSERT INTO `autoincr` VALUES (6, 'aa3', 'asd', 0);
INSERT INTO `autoincr` VALUES (7, 'asd1', 'asd', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `calattds`
-- 

CREATE TABLE `calattds` (
  `SYSID` varchar(20) NOT NULL default '',
  `LNAME` varchar(20) NOT NULL default '',
  `FNAME` varchar(20) NOT NULL default '',
  `COMPANY` varchar(30) default NULL,
  `JOB_TITLE` varchar(30) default NULL,
  `CONTACT` varchar(50) default NULL,
  PRIMARY KEY  (`SYSID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `calattds`
-- 

INSERT INTO `calattds` VALUES ('ATD_1', 'Jordan', 'Micheal', 'TNT', 'Sport Commentor', 'mjordan@tnt.com');
INSERT INTO `calattds` VALUES ('ATD_2', 'Johnson', 'Magic', '', '', 'mjohnson@coach.com');
INSERT INTO `calattds` VALUES ('ATD_3', 'Bonds', 'Barry', '', '', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `calevts`
-- 

CREATE TABLE `calevts` (
  `SYSID` varchar(20) NOT NULL default '',
  `SUBJECT` varchar(20) default NULL,
  `TYPE` varchar(20) default NULL,
  `LOCATION` varchar(50) default NULL,
  `NOTES` varchar(200) default NULL,
  `STARTTIME` datetime default NULL,
  `ENDTIME` datetime default NULL,
  `REPEATFLAG` char(1) default 'N',
  `REPEATCYCLE` varchar(20) default NULL,
  `REPEATEND` datetime default NULL,
  PRIMARY KEY  (`SYSID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `calevts`
-- 

INSERT INTO `calevts` VALUES ('CAL_1', 'Group Meeting', 'Meeting', 'Room 101', 'Marketing weekly group meeting', '2005-06-01 08:00:00', '2005-06-01 10:30:00', '', '', '1999-11-30 00:00:00');
INSERT INTO `calevts` VALUES ('CAL_2', 'BT Conf call', 'Appointment', 'Hall 201', 'Conference call with BT sales', '2005-06-01 09:00:00', '2005-06-01 12:30:00', '', '', '1999-11-30 00:00:00');
INSERT INTO `calevts` VALUES ('CAL_14', 'Test week event', 'Appointment', 'Room 300', 'Test repeated event', '2005-06-01 14:38:34', '2005-06-01 16:38:34', 'Y', 'Every week', '2005-08-04 16:38:35');
INSERT INTO `calevts` VALUES ('CAL_16', 'Test day repeat', 'Meeting', 'Cafe', '', '2005-06-06 12:15:00', '2005-06-06 13:15:00', 'Y', 'Every day', '2005-06-10 23:44:16');
INSERT INTO `calevts` VALUES ('CAL_17', 'Test month repeat', 'Meeting', 'Conference 300', 'Monthly meeting', '2005-06-07 09:10:28', '2005-06-07 10:10:28', 'Y', 'Every month', '2005-10-01 00:10:28');
INSERT INTO `calevts` VALUES ('CAL_18', 'Test year repeat', 'Birthday', '', 'Mike birthday', '2005-06-10 00:14:34', '2005-06-10 00:15:34', 'Y', 'Every year', '2008-09-25 00:14:34');

-- --------------------------------------------------------

-- 
-- Table structure for table `calevts_attds`
-- 

CREATE TABLE `calevts_attds` (
  `SYSID` int(20) NOT NULL auto_increment,
  `EVT_ID` varchar(20) NOT NULL default '',
  `ATD_ID` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`SYSID`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- 
-- Dumping data for table `calevts_attds`
-- 

INSERT INTO `calevts_attds` VALUES (1, 'CAL_1', 'ATD_1');
INSERT INTO `calevts_attds` VALUES (3, 'CAL_1', 'ATD_3');
INSERT INTO `calevts_attds` VALUES (4, 'CAL_2', 'ATD_1');
INSERT INTO `calevts_attds` VALUES (8, 'CAL_2', 'ATD_2');
INSERT INTO `calevts_attds` VALUES (9, '', 'ATD_1');

-- --------------------------------------------------------

-- 
-- Table structure for table `compkey`
-- 

CREATE TABLE `compkey` (
  `LASTNAME` char(30) NOT NULL default '',
  `FIRSTNAME` char(30) NOT NULL default '',
  `AGE` decimal(10,0) default NULL,
  PRIMARY KEY  (`LASTNAME`,`FIRSTNAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `compkey`
-- 

INSERT INTO `compkey` VALUES ('aaa', 'eee', 30);
INSERT INTO `compkey` VALUES ('aaa', 'hhh', 56);
INSERT INTO `compkey` VALUES ('aaa', 'jjj', 12);

-- --------------------------------------------------------

-- 
-- Table structure for table `events`
-- 

CREATE TABLE `events` (
  `SYSID` varchar(20) NOT NULL default '',
  `NAME` varchar(100) NOT NULL default '',
  `HOST` varchar(50) NOT NULL default '',
  `START` datetime default NULL,
  `END` datetime default NULL,
  `LOCATION` varchar(50) default NULL,
  `DESCRIPTION` varchar(255) default NULL,
  PRIMARY KEY  (`SYSID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `events`
-- 

INSERT INTO `events` VALUES ('EVT_3', 'NCCAF 2004 Tennis', 'NCCAF', '2004-06-20 10:33:51', '2004-06-20 13:33:51', 'San Jose State Univ', '');
INSERT INTO `events` VALUES ('EVT_4', 'NCCAF 2004 Soccer', 'NCCAF', '2006-10-30 23:47:56', '0000-00-00 00:00:00', 'San Jose State Univ', '');
INSERT INTO `events` VALUES ('EVT_5', 'NCCAF 2004 Basketball', 'NCCAF', '2004-11-30 09:00:00', '0000-00-00 00:00:00', 'San Jose State Univ', '');
INSERT INTO `events` VALUES ('EVT_6', 'NCCAF 2004 Table Tennis', 'NCCAF', '2004-06-19 09:00:00', '2004-06-19 21:00:00', 'San Jose State Univ', '');
INSERT INTO `events` VALUES ('EVT_118', 'NCCAF 2004 Badminton', 'NCCAF', '2004-06-20 10:33:51', '2004-06-20 13:33:51', 'San Jose State Univ', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `ob_sysids`
-- 

CREATE TABLE `ob_sysids` (
  `TABLENAME` char(20) NOT NULL default '',
  `PREFIX` char(10) default NULL,
  `IDBODY` int(11) default NULL,
  PRIMARY KEY  (`TABLENAME`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `ob_sysids`
-- 

INSERT INTO `ob_sysids` VALUES ('events', 'EVT', 118);
INSERT INTO `ob_sysids` VALUES ('regist', 'REG', 173);
INSERT INTO `ob_sysids` VALUES ('calevts', 'CAL', 58);
INSERT INTO `ob_sysids` VALUES ('audit', 'ADT', 12);
INSERT INTO `ob_sysids` VALUES ('ob_users', 'USR', 1);
INSERT INTO `ob_sysids` VALUES ('matches', 'MTC', 223);
INSERT INTO `ob_sysids` VALUES ('schedule', 'SCHD', 289);
INSERT INTO `ob_sysids` VALUES ('sponsors', 'SPSR', 6);
INSERT INTO `ob_sysids` VALUES ('calattds', 'CATD', 5);
INSERT INTO `ob_sysids` VALUES ('attendee', 'ATD', 53);
INSERT INTO `ob_sysids` VALUES ('attach', 'ATCH', 77);

-- --------------------------------------------------------

-- 
-- Table structure for table `ob_users`
-- 

CREATE TABLE `ob_users` (
  `SYSID` varchar(20) NOT NULL default '',
  `USERID` varchar(15) NOT NULL default '',
  `PASSWORD` varchar(15) default NULL,
  PRIMARY KEY  (`SYSID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `ob_users`
-- 

INSERT INTO `ob_users` VALUES ('USR_1', 'admin', 'admin');
INSERT INTO `ob_users` VALUES ('USR_2', 'bill', 'bill');

-- --------------------------------------------------------

-- 
-- Table structure for table `regist`
-- 

CREATE TABLE `regist` (
  `SYSID` varchar(20) NOT NULL default '',
  `ATTENDEE_ID` varchar(20) NOT NULL default '',
  `EVENT_ID` varchar(20) NOT NULL default '',
  `FEE` decimal(10,0) default NULL,
  `ONSCHD_FLAG` char(1) default NULL,
  PRIMARY KEY  (`SYSID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `regist`
-- 

INSERT INTO `regist` VALUES ('REG_103', 'ATD_5', 'EVT_5', 15, '');
INSERT INTO `regist` VALUES ('REG_101', 'ATD_1', 'EVT_4', 20, '');
INSERT INTO `regist` VALUES ('REG_130', 'ATD_8', 'EVT_3', 15, '');
INSERT INTO `regist` VALUES ('REG_11', 'ATD_4', 'EVT_5', 25, '');
INSERT INTO `regist` VALUES ('REG_139', 'ATD_5', 'EVT_3', 0, 'Y');
INSERT INTO `regist` VALUES ('REG_148', 'ATD_2', 'EVT_5', 0, '');
INSERT INTO `regist` VALUES ('REG_136', 'ATD_1', 'EVT_3', NULL, '');
INSERT INTO `regist` VALUES ('REG_100', 'ATD_4', 'EVT_4', NULL, '');
INSERT INTO `regist` VALUES ('REG_61', 'ATD_8', 'EVT_4', NULL, '');
INSERT INTO `regist` VALUES ('REG_95', 'ATD_5', 'EVT_4', NULL, '');
INSERT INTO `regist` VALUES ('REG_104', 'ATD_4', 'EVT_3', NULL, '');
INSERT INTO `regist` VALUES ('REG_173', 'ATD_8', 'EVT_118', 15, '');
INSERT INTO `regist` VALUES ('REG_172', 'ATD_2', 'EVT_118', 15, '');
INSERT INTO `regist` VALUES ('REG_170', 'ATD_5', 'EVT_6', 15, '');

-- --------------------------------------------------------

-- 
-- Table structure for table `sponsors`
-- 

CREATE TABLE `sponsors` (
  `SYSID` varchar(20) NOT NULL default '',
  `NAME` varchar(50) NOT NULL default '',
  `CONTACT` varchar(100) default NULL,
  `ADDRESS` varchar(100) default NULL,
  `DONATION` decimal(10,0) default NULL,
  `EXPENSE` decimal(10,0) default NULL,
  `COMMENTS` varchar(200) default NULL,
  PRIMARY KEY  (`SYSID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `sponsors`
-- 

INSERT INTO `sponsors` VALUES ('SPSR_2', 'Tiger Balm', '', '', 30000, 25000, '');
INSERT INTO `sponsors` VALUES ('SPSR_3', 'San Jose Mecury Daily', '', '', 50000, 45000, '');
INSERT INTO `sponsors` VALUES ('SPSR_4', 'Starbucks Coffee', '', '', 15000, 20000, '');
INSERT INTO `sponsors` VALUES ('SPSR_5', 'Midas Auto', '', '', 25000, 20000, '');
INSERT INTO `sponsors` VALUES ('SPSR_6', 'Steves Creeks Ford', '', '', 10000, 12000, '');

