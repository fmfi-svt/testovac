-- phpMyAdmin SQL Dump
-- version 3.3.7deb5build0.10.10.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 25, 2012 at 02:59 AM
-- Server version: 5.1.61
-- PHP Version: 5.3.3-1ubuntu9.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `testovac`
--

-- --------------------------------------------------------

--
-- Table structure for table `Students`
--

CREATE TABLE IF NOT EXISTS `Students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meno` varchar(255) CHARACTER SET utf8 COLLATE utf8_slovak_ci NOT NULL,
  `priezvisko` varchar(255) CHARACTER SET utf8 COLLATE utf8_slovak_ci NOT NULL,
  `datum_narodenia` date NOT NULL,
  `priemer1` double DEFAULT NULL,
  `priemer2` double(4,0) DEFAULT NULL,
  `forma_studia` varchar(10) CHARACTER SET utf8 COLLATE utf8_slovak_ci DEFAULT NULL,
  `pid` int(16) DEFAULT NULL,
  `last_time_edit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `printed` tinyint(1) NOT NULL,
  `exported` tinyint(1) NOT NULL,
  `edited_by` varchar(255) CHARACTER SET utf8 COLLATE utf8_slovak_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;
