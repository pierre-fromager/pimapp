-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 17, 2018 at 04:04 PM
-- Server version: 10.1.26-MariaDB-0+deb9u1
-- PHP Version: 7.0.30-0+deb9u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pimapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` bigint(20) NOT NULL,
  `fid` bigint(20) NOT NULL,
  `datec` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `dateexp` varchar(50) NOT NULL,
  `name` varchar(50) CHARACTER SET latin1 NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 NOT NULL,
  `login` varchar(50) CHARACTER SET latin1 NOT NULL,
  `password` varchar(30) CHARACTER SET latin1 NOT NULL,
  `token` varchar(32) NOT NULL,
  `photo` varchar(200) CHARACTER SET latin1 NOT NULL,
  `age` varchar(3) CHARACTER SET latin1 NOT NULL,
  `sexe` varchar(10) CHARACTER SET latin1 NOT NULL,
  `adresse` varchar(400) CHARACTER SET latin1 NOT NULL,
  `cp` varchar(5) CHARACTER SET latin1 NOT NULL,
  `ville` varchar(40) CHARACTER SET latin1 NOT NULL,
  `profil` varchar(20) CHARACTER SET latin1 NOT NULL,
  `reference` tinyint(4) NOT NULL DEFAULT '0',
  `gsm` varchar(30) CHARACTER SET latin1 NOT NULL,
  `site` varchar(400) CHARACTER SET latin1 NOT NULL,
  `status` varchar(15) CHARACTER SET latin1 NOT NULL,
  `sn` varchar(20) NOT NULL,
  `ip` varchar(16) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `fid`, `datec`, `dateexp`, `name`, `email`, `login`, `password`, `token`, `photo`, `age`, `sexe`, `adresse`, `cp`, `ville`, `profil`, `reference`, `gsm`, `site`, `status`, `sn`, `ip`) VALUES
(822, 892, '2018-08-19 16:01:18', '2015-01-22 11:24:55', 'Admin', 'admin@pimapp.local', 'admin@pimapp.local', 'admin', '', 'http://pier-infor.fr/Site/Media/animx.gif', '50', 'M', 'street@', 'posco', 'city', 'admin', 1, '01234560', 'http://pier-infor.fr', 'valid', '', '192.168.1.254');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `fid` (`fid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=900;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
