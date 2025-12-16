create DATABASE IF NOT EXISTS `curdapp`;

USE `curdapp`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mobile` varchar(30) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT 'Male',
  `hobbies` text DEFAULT NULL,
  `language` varchar(100) DEFAULT NULL,
  `skill` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
);