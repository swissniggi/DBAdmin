-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Erstellungszeit: 14. Sep 2018 um 15:09
-- Server-Version: 5.7.21
-- PHP-Version: 7.1.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `dbadmin`
--
CREATE DATABASE IF NOT EXISTS `dbadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `dbadmin`;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lastimport`
--

DROP TABLE IF EXISTS `lastimport`;
CREATE TABLE IF NOT EXISTS `lastimport` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `dbName` varchar(50) NOT NULL,
  `importDate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
