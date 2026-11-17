-- F1SITE.NL database schema (structure only).
-- Create an admin user with: php scripts/create-admin.php <username>

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `teams` (
  `team_id` int(11) NOT NULL AUTO_INCREMENT,
  `team_name` varchar(100) NOT NULL,
  `team_color` varchar(7) NOT NULL,
  `full_team_name` varchar(255) DEFAULT NULL,
  `base_location` varchar(100) DEFAULT NULL,
  `team_principal` varchar(100) DEFAULT NULL,
  `technical_director` varchar(100) DEFAULT NULL,
  `championships_won` int(11) DEFAULT 0,
  `first_entry_year` int(11) DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `current_engine_supplier` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `chassis` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`team_id`),
  UNIQUE KEY `team_name` (`team_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `drivers` (
  `driver_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `nationality` varchar(50) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `driver_number` int(11) DEFAULT NULL,
  `team_name` varchar(100) DEFAULT NULL,
  `championships_won` int(11) DEFAULT 0,
  `career_points` decimal(10,2) DEFAULT 0.00,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `flag_url` varchar(255) DEFAULT NULL,
  `driver_color` varchar(50) DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `place_of_birth` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`driver_id`),
  UNIQUE KEY `driver_number` (`driver_number`),
  KEY `fk_team` (`team_id`),
  CONSTRAINT `fk_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `circuits` (
  `circuit_key` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `grandprix` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `map_url` varchar(255) DEFAULT NULL,
  `first_gp_year` int(11) DEFAULT NULL,
  `lap_count` int(11) DEFAULT NULL,
  `circuit_length_km` decimal(10,3) DEFAULT NULL,
  `race_distance_km` decimal(10,3) DEFAULT NULL,
  `lap_record` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `highlights` text DEFAULT NULL,
  `calendar_order` int(11) DEFAULT NULL,
  `race_year` int(11) NOT NULL,
  `race_datetime` datetime DEFAULT NULL,
  `fp1_datetime` datetime DEFAULT NULL,
  `fp2_datetime` datetime DEFAULT NULL,
  `fp3_datetime` datetime DEFAULT NULL,
  `quali_datetime` datetime DEFAULT NULL,
  `sprint_datetime` datetime DEFAULT NULL,
  `sprint_quali_datetime` datetime DEFAULT NULL,
  `country_flag_url` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`circuit_key`,`race_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `f1_nieuws` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(255) NOT NULL,
  `artikel_url` varchar(512) NOT NULL,
  `publicatie_datum` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `afbeelding_url` varchar(512) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `artikel_url` (`artikel_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_subject` varchar(255) DEFAULT NULL,
  `contact_message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
