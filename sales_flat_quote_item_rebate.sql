SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--

-- --------------------------------------------------------

--
-- Table structure for table `sales_flat_quote_item_rebate`
--

CREATE TABLE `sales_flat_quote_item_rebate` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `verification` varchar(255) NOT NULL,
  `maxqty` int(11) NOT NULL,
  `program` varchar(64) NOT NULL,
  `amount` int(11) NOT NULL
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
