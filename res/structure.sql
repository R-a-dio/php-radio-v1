-- MySQL dump 10.13  Distrib 5.1.58, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: radiosite
-- ------------------------------------------------------
-- Server version	5.1.58-1ubuntu1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bm_bm`
--

DROP TABLE IF EXISTS `bm_bm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bm_bm` (
  `usr` int(10) unsigned NOT NULL COMMENT 'user id',
  `trk` int(10) unsigned NOT NULL COMMENT 'track id',
  `td` int(10) unsigned NOT NULL COMMENT 'will fail in 2038',
  KEY `usr` (`usr`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='rendundant redundancy';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bm_track`
--

DROP TABLE IF EXISTS `bm_track`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bm_track` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'track id',
  `ip` varchar(15) NOT NULL COMMENT 'submitter ip',
  `td` int(10) unsigned NOT NULL COMMENT 'will fail in 2038',
  `n` int(10) unsigned NOT NULL COMMENT 'times added',
  `tag` text NOT NULL COMMENT 'track title',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=171 DEFAULT CHARSET=utf8 COMMENT='bookmark tracks';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bm_user`
--

DROP TABLE IF EXISTS `bm_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bm_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'user id',
  `ip` varchar(15) NOT NULL COMMENT 'ip address',
  `tdc` int(10) unsigned NOT NULL COMMENT 'time created',
  `tdl` int(10) unsigned NOT NULL COMMENT 'time login',
  `user` varchar(20) NOT NULL COMMENT 'username',
  `pass` varchar(32) NOT NULL COMMENT 'password (md5+seed)',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COMMENT='bookmark accounts';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cdl_album`
--

DROP TABLE IF EXISTS `cdl_album`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cdl_album` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ic` int(10) unsigned NOT NULL,
  `v` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cdl_circle`
--

DROP TABLE IF EXISTS `cdl_circle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cdl_circle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `v` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cdl_link`
--

DROP TABLE IF EXISTS `cdl_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cdl_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ia` int(10) unsigned NOT NULL,
  `v` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nid` int(10) unsigned NOT NULL,
  `header` varchar(100) NOT NULL,
  `text` text NOT NULL,
  `mail` varchar(200) NOT NULL,
  `login` varchar(50) DEFAULT '',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `curqueue`
--

DROP TABLE IF EXISTS `curqueue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `curqueue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestr` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `track` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=131064 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `djs`
--

DROP TABLE IF EXISTS `djs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `djs` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `djname` varchar(60) NOT NULL,
  `djtext` text NOT NULL,
  `djimage` text NOT NULL,
  `visible` int(1) unsigned NOT NULL DEFAULT '0',
  `priority` int(12) unsigned NOT NULL DEFAULT '200',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `efave`
--

DROP TABLE IF EXISTS `efave`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `efave` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'db identifier',
  `inick` int(10) unsigned NOT NULL COMMENT 'nick id',
  `isong` int(10) unsigned NOT NULL COMMENT 'song id',
  KEY `isong` (`isong`),
  KEY `inick` (`inick`),
  CONSTRAINT `inick` FOREIGN KEY (`inick`) REFERENCES `enick` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `isong` FOREIGN KEY (`isong`) REFERENCES `esong` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `enick`
--

DROP TABLE IF EXISTS `enick`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enick` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `nick` varchar(30) COLLATE utf8_bin NOT NULL COMMENT 'irc handle',
  `dta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'first seen',
  `dtb` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'last seen',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nick` (`nick`)
) ENGINE=InnoDB AUTO_INCREMENT=342 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='normalized table for irc handles';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eplay`
--

DROP TABLE IF EXISTS `eplay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eplay` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'db identifier',
  `isong` int(10) unsigned NOT NULL COMMENT 'song id',
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'datoklokkeslett',
  PRIMARY KEY (`id`),
  KEY `iplay` (`isong`),
  CONSTRAINT `iplay` FOREIGN KEY (`isong`) REFERENCES `esong` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41406 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='normalized table for track playback events';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `esong`
--

DROP TABLE IF EXISTS `esong`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `esong` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'db identifier',
  `hash` varchar(40) COLLATE utf8_bin NOT NULL COMMENT 'original meta hash',
  `len` int(10) unsigned NOT NULL COMMENT 'seconds',
  `meta` text COLLATE utf8_bin NOT NULL COMMENT 'current meta',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB AUTO_INCREMENT=14784 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='normalized table for known tracks';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lastplayed`
--

DROP TABLE IF EXISTS `lastplayed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lastplayed` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `song` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=77355 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `listenlog`
--

DROP TABLE IF EXISTS `listenlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `listenlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `listeners` int(10) unsigned NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4033 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `header` varchar(50) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `newstext` text NOT NULL,
  `cancomment` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nickrequesttime`
--

DROP TABLE IF EXISTS `nickrequesttime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nickrequesttime` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `host` text NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pageviews`
--

DROP TABLE IF EXISTS `pageviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pageviews` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pagename` varchar(50) NOT NULL,
  `query` text NOT NULL,
  `host` varchar(180) NOT NULL DEFAULT '0.0.0.0',
  `login` varchar(60) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pending`
--

DROP TABLE IF EXISTS `pending`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pending` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `artist` varchar(200) NOT NULL,
  `track` varchar(200) NOT NULL,
  `album` varchar(200) NOT NULL,
  `path` text NOT NULL,
  `comment` text NOT NULL,
  `origname` text NOT NULL,
  `submitter` varchar(15) NOT NULL,
  `submitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dupe_flag` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3600 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `queue`
--

DROP TABLE IF EXISTS `queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue` (
  `trackid` int(14) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` text CHARACTER SET utf8 COLLATE utf8_bin,
  `type` int(3) DEFAULT '0',
  `meta` text,
  `length` float DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18039 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `radvars`
--

DROP TABLE IF EXISTS `radvars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radvars` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `requests`
--

DROP TABLE IF EXISTS `requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trackid` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `requesttime`
--

DROP TABLE IF EXISTS `requesttime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requesttime` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2283 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `searchlog`
--

DROP TABLE IF EXISTS `searchlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `searchlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `search` text NOT NULL,
  `cumulative_prio` int(11) NOT NULL DEFAULT '0',
  `divided_prio` int(11) NOT NULL DEFAULT '0',
  `res_count` int(11) NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20006 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `streamsongs`
--

DROP TABLE IF EXISTS `streamsongs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streamsongs` (
  `hash` varchar(40) NOT NULL,
  `title` text NOT NULL,
  `playcount` int(10) unsigned NOT NULL DEFAULT '0',
  `length` int(10) unsigned NOT NULL DEFAULT '0',
  `lastplayed` bigint(20) unsigned NOT NULL DEFAULT '0',
  `fave` text NOT NULL,
  PRIMARY KEY (`hash`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `streamstatus`
--

DROP TABLE IF EXISTS `streamstatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streamstatus` (
  `id` int(11) NOT NULL DEFAULT '0',
  `djid` int(10) unsigned NOT NULL DEFAULT '0',
  `np` varchar(200) NOT NULL DEFAULT '',
  `listeners` int(10) unsigned NOT NULL DEFAULT '0',
  `bitrate` int(10) unsigned NOT NULL DEFAULT '0',
  `isafkstream` int(1) NOT NULL DEFAULT '0',
  `isstreamdesk` int(1) NOT NULL DEFAULT '0',
  `start_time` bigint(20) unsigned NOT NULL DEFAULT '0',
  `end_time` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lastset` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `temp_hiroto`
--

DROP TABLE IF EXISTS `temp_hiroto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temp_hiroto` (
  `id` int(14) unsigned NOT NULL AUTO_INCREMENT,
  `artist` varchar(200) NOT NULL,
  `track` varchar(200) NOT NULL,
  `album` varchar(200) NOT NULL,
  `path` text NOT NULL,
  `tags` text NOT NULL,
  `lastplayed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastrequested` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `usable` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `searchindex` (`tags`,`artist`,`track`,`album`),
  FULLTEXT KEY `artist` (`artist`,`track`,`album`,`tags`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tracks`
--

DROP TABLE IF EXISTS `tracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracks` (
  `id` int(14) unsigned NOT NULL AUTO_INCREMENT,
  `artist` varchar(200) NOT NULL,
  `track` varchar(200) NOT NULL,
  `album` varchar(200) NOT NULL,
  `path` text NOT NULL,
  `tags` text NOT NULL,
  `priority` int(10) NOT NULL DEFAULT '0',
  `lastplayed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastrequested` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `usable` int(1) NOT NULL DEFAULT '0',
  `accepter` varchar(200) NOT NULL DEFAULT '',
  `lasteditor` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `searchindex` (`tags`,`artist`,`track`,`album`),
  FULLTEXT KEY `artist` (`artist`,`track`,`album`,`tags`)
) ENGINE=MyISAM AUTO_INCREMENT=2999 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uploadtime`
--

DROP TABLE IF EXISTS `uploadtime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uploadtime` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=725 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL,
  `pass` varchar(40) NOT NULL,
  `djid` int(12) DEFAULT NULL,
  `privileges` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-03-06 17:32:28
