CREATE TABLE `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `problem` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `solution` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start` datetime COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `end` datetime COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `solvingtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;