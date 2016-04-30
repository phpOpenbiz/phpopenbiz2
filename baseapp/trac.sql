/*
SQLyog Community Edition- MySQL GUI v6.14
MySQL - 5.0.27-community-nt : Database - trac
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

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

insert  into `attachment`(`id`,`parent_id`,`filename`,`type`,`size`,`time`,`description`,`author`,`ipnr`) values (8,'TKT_2','Blue hills.jpg','image/jpeg',28521,'2008-04-19 23:43:12','blue hill image','cat',NULL);

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

insert  into `component`(`name`,`owner`,`description`) values ('BizDataObj','Rocky','Data layer');
insert  into `component`(`name`,`owner`,`description`) values ('BizForm','Rocky','Presentation layer\n:)');

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

insert  into `enum`(`id`,`type`,`name`,`value`) values (1,'Priority','Minor','1');
insert  into `enum`(`id`,`type`,`name`,`value`) values (2,'Priority','Critical','2');
insert  into `enum`(`id`,`type`,`name`,`value`) values (3,'Priority','Major','3');
insert  into `enum`(`id`,`type`,`name`,`value`) values (4,'Severity','Low','1');
insert  into `enum`(`id`,`type`,`name`,`value`) values (5,'Severity','Mideum','2');
insert  into `enum`(`id`,`type`,`name`,`value`) values (6,'Severity','High','3');
insert  into `enum`(`id`,`type`,`name`,`value`) values (8,'Type','Defect','1');
insert  into `enum`(`id`,`type`,`name`,`value`) values (9,'Type','Feature','2');
insert  into `enum`(`id`,`type`,`name`,`value`) values (10,'Type','Enhancement','3');
insert  into `enum`(`id`,`type`,`name`,`value`) values (11,'Type','Task','4');
insert  into `enum`(`id`,`type`,`name`,`value`) values (12,'Status','Open','1');
insert  into `enum`(`id`,`type`,`name`,`value`) values (13,'Status','Accepted','2');
insert  into `enum`(`id`,`type`,`name`,`value`) values (14,'Status','Reopened','3');
insert  into `enum`(`id`,`type`,`name`,`value`) values (15,'Status','Resolved','4');
insert  into `enum`(`id`,`type`,`name`,`value`) values (16,'Status','Closed','5');
insert  into `enum`(`id`,`type`,`name`,`value`) values (17,'Resolution','Unresolved','1');
insert  into `enum`(`id`,`type`,`name`,`value`) values (18,'Resolution','Fixed','2');
insert  into `enum`(`id`,`type`,`name`,`value`) values (19,'Resolution','Won\'t Fix','3');
insert  into `enum`(`id`,`type`,`name`,`value`) values (20,'Resolution','Duplicated','4');
insert  into `enum`(`id`,`type`,`name`,`value`) values (21,'Resolution','Incomplete','5');
insert  into `enum`(`id`,`type`,`name`,`value`) values (22,'Resolution','Cannot Reproduce','6');
insert  into `enum`(`id`,`type`,`name`,`value`) values (23,'Priority','Blocker','4');
insert  into `enum`(`id`,`type`,`name`,`value`) values (24,'Priority','Trivial','5');

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

insert  into `milestone`(`name`,`due`,`completed`,`description`) values ('alpha','2008-02-12 00:04:40',NULL,'alpha version of the product');
insert  into `milestone`(`name`,`due`,`completed`,`description`) values ('beta1','2008-02-27 00:05:31',NULL,NULL);

/*Table structure for table `ob_sysids` */

DROP TABLE IF EXISTS `ob_sysids`;

CREATE TABLE `ob_sysids` (
  `TABLENAME` char(20) NOT NULL default '',
  `PREFIX` char(10) default NULL,
  `IDBODY` int(11) default NULL,
  PRIMARY KEY  (`TABLENAME`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `ob_sysids` */

insert  into `ob_sysids`(`TABLENAME`,`PREFIX`,`IDBODY`) values ('ticket','TKT',24);

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

insert  into `ticket`(`id`,`type`,`time`,`changetime`,`component`,`serverity`,`priority`,`owner`,`reporter`,`cc`,`version`,`milestone`,`status`,`resolution`,`summary`,`description`,`keywords`) values ('TKT_18','Defect','2008-02-20 23:46:56','2008-02-21 01:29:00','BizForm','Low','Minor',NULL,NULL,NULL,'1.0','alpha','Resolved','Won\'t Fix','test openbiz trac','Build trac with openbiz \n- learn the trac data model (table)\n- build dataobjs, forms, views\n- write templates and css',NULL);
insert  into `ticket`(`id`,`type`,`time`,`changetime`,`component`,`serverity`,`priority`,`owner`,`reporter`,`cc`,`version`,`milestone`,`status`,`resolution`,`summary`,`description`,`keywords`) values ('TKT_2','Defect','2008-02-06 23:12:37','2008-02-13 01:50:58','BizForm','Critial','Medium',NULL,NULL,NULL,'1.0','alpha','Open',NULL,'test openbiz trac','Build trac with openbiz \n- learn the trac data model (table)\n- build dataobjs, forms, views\n- write templates and css',NULL);
insert  into `ticket`(`id`,`type`,`time`,`changetime`,`component`,`serverity`,`priority`,`owner`,`reporter`,`cc`,`version`,`milestone`,`status`,`resolution`,`summary`,`description`,`keywords`) values ('TKT_3',NULL,'2008-02-09 00:12:51','2008-02-09 00:12:51','BizForm',NULL,NULL,NULL,NULL,NULL,'1.0','alpha',NULL,NULL,'test','1234567890',NULL);
insert  into `ticket`(`id`,`type`,`time`,`changetime`,`component`,`serverity`,`priority`,`owner`,`reporter`,`cc`,`version`,`milestone`,`status`,`resolution`,`summary`,`description`,`keywords`) values ('TKT_4',NULL,'2008-02-09 00:12:58','2008-02-09 00:12:58','BizForm',NULL,NULL,NULL,NULL,NULL,'1.0','alpha',NULL,NULL,'test','1234567890',NULL);

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

insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (1,'TKT_2','2008-02-11 23:13:30','unkown','type','Enhancement','Defect');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (21,'TKT_2','2008-02-13 00:18:30','aaa','milestone','beta1','alpha');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (24,'TKT_2','2008-02-13 01:08:10','rocky','comment',NULL,'my comment 1');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (25,'TKT_2','2008-02-13 01:08:31','sad','status','Open','Fixed');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (26,'TKT_2','2008-02-13 01:12:24','aaa','status','Fixed','Open');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (28,'TKT_2','2008-02-13 01:13:41','aaa','milestone','alpha','beta1');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (29,'TKT_2','2008-02-13 01:17:36','qqq','milestone','beta1','alpha');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (30,'TKT_2','2008-02-13 01:17:36','qqq','description','1234567890\nasdsad','1234567890\r\nasdsad');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (32,'TKT_2','2008-02-13 01:23:16','qqq1','priority','High','Medium');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (33,'TKT_2','2008-02-13 01:23:16','qqq1','milestone','beta1','alpha');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (34,'TKT_2','2008-02-13 01:23:16','qqq1','description','1234567890\nasdsad','1234567890\r\nasdsad');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (36,'TKT_2','2008-02-13 01:31:12','www','milestone','beta1','alpha');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (38,'TKT_2','2008-02-13 01:50:59','rocky','description','1234567890\r\nasdsad','Build trac with openbiz \n- learn the trac data model (table)\n- build dataobjs, forms, views\n- write templates and css');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (39,'TKT_2','2008-02-18 17:26:36','rocky_test','comment',NULL,'The error I\'m referring to in comment:26 is an exception raised in a WikiSyntaxProvider, inside a wiki formatting done while rendering the timeline.\n\nThe corresponding backtrace for that error can be seen in attachment:timeline_rendering_backtrace.log\n\nNow, if I catch the exception before it goes through the template rendering, the memory usage is OK again. The live object count, as measured by doing a len(gc.get_objects()) after a gc.collect(), remains constant at 62642, after successive requests.\n\nIf I raise an exception on purpose after the template rendering, there\'s also no increase in memory usage.\n\nBut if the original exception is not caught, the live object count increase by ~50000 after each call.\n\nI\'ll try to locate the exact point in the backtrace which cause the memory retention.\n');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (40,'TKT_14','2008-02-20 01:33:04',NULL,'Id',NULL,'TKT_14');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (41,'TKT_14','2008-02-20 01:33:04',NULL,'type',NULL,'Defect');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (42,'TKT_14','2008-02-20 01:33:04',NULL,'component',NULL,'BizDataObj');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (43,'TKT_14','2008-02-20 01:33:04',NULL,'serverity',NULL,'Stopper');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (44,'TKT_14','2008-02-20 01:33:04',NULL,'priority',NULL,'High');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (45,'TKT_14','2008-02-20 01:33:04',NULL,'version',NULL,'1.0');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (46,'TKT_14','2008-02-20 01:33:04',NULL,'milestone',NULL,'alpha');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (47,'TKT_14','2008-02-20 01:33:04',NULL,'status',NULL,'Open');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (48,'TKT_14','2008-02-20 01:33:04',NULL,'summary',NULL,'This openbiz trac is used ti track issues');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (49,'TKT_16','2008-02-20 01:56:51','rocky','keywords',NULL,'trac, php, openbiz');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (51,'TKT_18','2008-02-21 01:07:43','rocky','serverity','Critial','Low');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (52,'TKT_18','2008-02-21 01:07:43','rocky','priority','Medium','Minor');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (53,'TKT_18','2008-02-21 01:29:00','rocky','status','Open','Resolved');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (54,'TKT_18','2008-02-21 01:29:00','rocky','resolution',NULL,'Won\'t Fix');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (55,'TKT_2','2008-03-21 23:13:42','sadsdas','Id',NULL,'TKT_2');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (56,'TKT_2','2008-03-21 23:13:42','sadsdas','type',NULL,'Defect');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (57,'TKT_2','2008-03-21 23:13:42','sadsdas','time',NULL,'2008-02-06 23:12:37');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (58,'TKT_2','2008-03-21 23:13:42','sadsdas','changetime',NULL,'2008-02-13 01:50:58');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (59,'TKT_2','2008-03-21 23:13:42','sadsdas','component',NULL,'BizForm');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (60,'TKT_2','2008-03-21 23:13:42','sadsdas','serverity',NULL,'Critial');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (61,'TKT_2','2008-03-21 23:13:42','sadsdas','priority',NULL,'Medium');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (62,'TKT_2','2008-03-21 23:13:42','sadsdas','version',NULL,'1.0');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (63,'TKT_2','2008-03-21 23:13:42','sadsdas','milestone',NULL,'alpha');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (64,'TKT_2','2008-03-21 23:13:42','sadsdas','status',NULL,'Open');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (65,'TKT_2','2008-03-21 23:13:42','sadsdas','summary',NULL,'test openbiz trac');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (66,'TKT_2','2008-03-21 23:13:42','sadsdas','description',NULL,'Build trac with openbiz \n- learn the trac data model (table)\n- build dataobjs, forms, views\n- write templates and css');
insert  into `ticket_change`(`id`,`ticket`,`time`,`author`,`field`,`oldvalue`,`newvalue`) values (67,'TKT_18','2008-03-21 23:16:46','aaa','comment',NULL,'test openbiz 2.3');

/*Table structure for table `version` */

DROP TABLE IF EXISTS `version`;

CREATE TABLE `version` (
  `name` varchar(30) NOT NULL,
  `time` datetime default NULL,
  `description` text,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `version` */

insert  into `version`(`name`,`time`,`description`) values ('1.0','2008-02-01 00:07:52',NULL);
insert  into `version`(`name`,`time`,`description`) values ('1.01','2008-02-12 00:07:41',NULL);

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
