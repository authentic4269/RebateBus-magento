-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 16, 2017 at 07:00 PM
-- Server version: 5.7.18-0ubuntu0.16.04.1
-- PHP Version: 5.6.29-1+deb.sury.org~xenial+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `magento1`
--

-- --------------------------------------------------------

--
-- Table structure for table `sales_flat_quote_item_rebate`
--

DROP TABLE IF EXISTS `sales_flat_quote_item_rebate`;
CREATE TABLE `sales_flat_quote_item_rebate` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `verification` varchar(255) NOT NULL,
  `maxqty` int(11) NOT NULL,
  `program` varchar(64) NOT NULL,
  `amount` int(11) NOT NULL,
  `busid` varchar(32) NOT NULL,
  `cap` float NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sales_flat_quote_item_rebate`
--
ALTER TABLE `sales_flat_quote_item_rebate`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_id` (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sales_flat_quote_item_rebate`
--
ALTER TABLE `sales_flat_quote_item_rebate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
