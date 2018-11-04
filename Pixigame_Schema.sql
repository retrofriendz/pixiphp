-- MySQL dump 10.13  Distrib 5.7.24, for Linux (x86_64)
--
-- Host: localhost    Database: pixigamedb
-- ------------------------------------------------------
-- Server version	5.7.24-0ubuntu0.16.04.1

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
-- Table structure for table `AppSettings`
--

DROP TABLE IF EXISTS `AppSettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AppSettings` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `JSON` longtext NOT NULL COMMENT 'JSON array of the settings',
  `Created` int(10) unsigned NOT NULL COMMENT 'When it was created',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='History of the web application settings and its current (largest ID)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Chat`
--

DROP TABLE IF EXISTS `Chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Chat` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Originator` int(10) unsigned NOT NULL COMMENT 'reference to user ID',
  `Destination` int(10) unsigned NOT NULL,
  `Content` tinytext NOT NULL,
  `Created` int(10) unsigned NOT NULL COMMENT 'Timestamp',
  `Type` int(10) unsigned NOT NULL COMMENT '0 = User\n1 = Game/Lobby',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `Friend`
--

DROP TABLE IF EXISTS `Friend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Friend` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `r_UserA` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User Initiator Reference',
  `r_UserB` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User Target Reference',
  `Created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Server Timestamp',
  `BlockedByA` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0=No\n1=Yes',
  `BlockedByB` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0=No\n1=Yes',
  `Status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Requested=0\nAccepted=1',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `Game`
--

DROP TABLE IF EXISTS `Game`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Game` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `LastState` longtext NOT NULL COMMENT 'JSON state of the current game',
  `r_Users` mediumtext NOT NULL COMMENT 'Contains a comma-delimited list of user IDs associated with this game',
  `Initiator` int(10) unsigned NOT NULL DEFAULT '0',
  `Created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Timestamp of the game creation',
  `LastPlayed` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Timestamp of the last turn',
  `TurnCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The turn number',
  `Name` tinytext NOT NULL,
  `Settings` longtext NOT NULL COMMENT 'JSON settings for the game',
  `PlayerLimit` int(11) NOT NULL DEFAULT '2',
  `Status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0 Lobby, 1 In-Progress (Started), 2 Completed',
  `Ready` mediumtext NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='This holds the state for any active games, or games that have already been played.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Message`
--

DROP TABLE IF EXISTS `Message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Message` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `r_User` int(10) unsigned NOT NULL,
  `Content` mediumtext NOT NULL,
  `Code` int(10) unsigned NOT NULL COMMENT 'Error code or message hint',
  `Created` int(10) unsigned NOT NULL COMMENT 'Timestamp',
  `Expiry` int(10) unsigned NOT NULL COMMENT 'Timestamp',
  `Reference` int(10) unsigned NOT NULL,
  `Type` tinytext NOT NULL,
  `JSON` longtext NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `Notification`
--

DROP TABLE IF EXISTS `Notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Notification` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Sent` int(10) unsigned NOT NULL,
  `Expiry` int(10) unsigned NOT NULL COMMENT '0=never expires',
  `Content` text NOT NULL,
  `r_User` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `Session`
--

DROP TABLE IF EXISTS `Session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Session` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiresAt` int(10) unsigned NOT NULL,
  `r_User` int(10) unsigned NOT NULL,
  `session_token` tinytext,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `User`
--

DROP TABLE IF EXISTS `User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `User` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `active` int(10) unsigned NOT NULL,
  `username` text NOT NULL,
  `password` tinytext NOT NULL,
  `email` text NOT NULL,
  `emailVerified` int(10) unsigned NOT NULL,
  `su` int(10) unsigned NOT NULL,
  `admin` int(10) unsigned NOT NULL,
  `twitter` text NOT NULL,
  `forgotKey` tinytext NOT NULL,
  `forgotExpires` int(10) unsigned NOT NULL,
  `nickname` tinytext NOT NULL COMMENT 'In-game appearance',
  `banner` text NOT NULL,
  `steamname` tinytext NOT NULL COMMENT 'Steam ID',
  `acl` text NOT NULL,
  `medals` text NOT NULL,
  `history` text NOT NULL COMMENT 'contains win / loss and other game statistics',
  `activationKey` text NOT NULL,
  `activationExpires` int(10) unsigned NOT NULL,
  `last_ip` varchar(45) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `User`
--

LOCK TABLES `User` WRITE;
/*!40000 ALTER TABLE `User` DISABLE KEYS */;
INSERT INTO `User` VALUES (1,1,'testaccount','$2y$10$x/nIDPBVcspROCatub3Xh.8OGi7ABWZ4CW4GNGlE6gavnvEmCK07K','testaccount@rocketship.com',1,1,1,'@LAGameStudio','MzA3ODIxMmRjZDc5MTdjOGVkZDYwZTNmNjQ3YjY1Nzg=',1540755392,'testaccount','{}',' ',' ',' {}','{}','',0,'');
/*!40000 ALTER TABLE `User` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-11-04 10:52:08
