-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Czas generowania: 26 Maj 2025, 14:56
-- Wersja serwera: 10.4.22-MariaDB
-- Wersja PHP: 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `skydriverentals`
--

-- --------------------------------------------------------

--
-- Zastąpiona struktura widoku `available_cars`
-- (Zobacz poniżej rzeczywisty widok)
--
CREATE TABLE `available_cars` (
`vehicle_id` int(11)
,`make` varchar(50)
,`model` varchar(50)
,`year` int(11)
,`registration_number` varchar(20)
,`capacity` int(11)
,`fuel_type` varchar(30)
,`engine_power` varchar(30)
,`daily_rate` decimal(10,2)
,`image_path` varchar(255)
,`description` text
,`city` varchar(50)
,`address` text
,`is_airport` tinyint(1)
);

-- --------------------------------------------------------

--
-- Zastąpiona struktura widoku `available_planes`
-- (Zobacz poniżej rzeczywisty widok)
--
CREATE TABLE `available_planes` (
`vehicle_id` int(11)
,`make` varchar(50)
,`model` varchar(50)
,`year` int(11)
,`registration_number` varchar(20)
,`capacity` int(11)
,`fuel_type` varchar(30)
,`engine_power` varchar(30)
,`max_speed` varchar(30)
,`range` varchar(30)
,`hourly_rate` decimal(10,2)
,`image_path` varchar(255)
,`description` text
,`city` varchar(50)
,`address` text
,`is_airport` tinyint(1)
);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `daily_cost` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `equipment`
--

INSERT INTO `equipment` (`equipment_id`, `name`, `description`, `daily_cost`) VALUES
(1, 'Nawigacja GPS', 'System nawigacji satelitarnej', '30.00'),
(2, 'Dziecięcy fotelik', 'Fotelik samochodowy dla dzieci', '20.00'),
(3, 'Wifi w samochodzie', 'Internet mobilny w pojeździe', '50.00'),
(4, 'Dodatkowe ubezpieczenie', 'Pełne ubezpieczenie bez udziału własnego', '100.00'),
(5, 'Instruktor lotniczy', 'Dodatkowy pilot-instruktor', '300.00');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `locations`
--

CREATE TABLE `locations` (
  `location_id` int(11) NOT NULL,
  `city` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_airport` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `locations`
--

INSERT INTO `locations` (`location_id`, `city`, `address`, `phone`, `email`, `is_airport`) VALUES
(1, 'Warszawa', 'ul. Lotnicza 15', '+48 22 123 4567', 'warszawa@skydrive.pl', 0),
(2, 'Kraków', 'ul. Samochodowa 8', '+48 12 345 6789', 'krakow@skydrive.pl', 0),
(3, 'Gdańsk', 'Port Lotniczy Gdańsk', '+48 58 987 6543', 'gdansk@skydrive.pl', 1),
(4, 'Wrocław', 'ul. Powietrzna 22', '+48 71 234 5678', 'wroclaw@skydrive.pl', 0),
(5, 'Poznań', 'Port Lotniczy Poznań', '+48 61 876 5432', 'poznan@skydrive.pl', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `payment_method` enum('credit_card','bank_transfer','cash') NOT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_details` text DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `reservationequipment`
--

CREATE TABLE `reservationequipment` (
  `reservation_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `reservationequipment`
--

INSERT INTO `reservationequipment` (`reservation_id`, `equipment_id`, `quantity`) VALUES
(7, 1, 1),
(7, 2, 1),
(7, 4, 1),
(7, 5, 1),
(8, 1, 1),
(9, 1, 1),
(9, 3, 1),
(10, 1, 1),
(10, 2, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `pickup_location_id` int(11) NOT NULL,
  `return_location_id` int(11) NOT NULL,
  `pickup_date` datetime NOT NULL,
  `return_date` datetime NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `user_id`, `vehicle_id`, `pickup_location_id`, `return_location_id`, `pickup_date`, `return_date`, `total_cost`, `status`, `created_at`, `notes`) VALUES
(1, 3, 2, 3, 4, '2025-05-19 12:00:00', '2025-05-21 12:00:00', '1000.00', 'cancelled', '2025-05-19 22:49:44', NULL),
(6, 3, 5, 1, 2, '2025-06-19 12:00:00', '2025-06-20 12:00:00', '120000.00', 'cancelled', '2025-05-19 23:45:30', NULL),
(7, 4, 6, 1, 1, '2025-05-21 12:00:00', '2025-05-21 12:00:00', '0.00', 'confirmed', '2025-05-20 01:46:52', NULL),
(8, 4, 3, 3, 2, '2025-05-27 12:00:00', '2025-05-27 18:00:00', '0.00', 'confirmed', '2025-05-25 00:52:12', NULL),
(9, 4, 3, 1, 1, '2025-05-28 12:00:00', '2025-05-30 12:00:00', '1360.00', 'pending', '2025-05-26 13:56:04', NULL),
(10, 4, 4, 1, 1, '2025-05-27 12:00:00', '2025-05-28 12:00:00', '49200.00', 'confirmed', '2025-05-26 14:22:53', NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `review_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `driver_license_number` varchar(50) DEFAULT NULL,
  `pilot_license_number` varchar(50) DEFAULT NULL,
  `registration_date` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `address`, `driver_license_number`, `pilot_license_number`, `registration_date`, `last_login`, `is_admin`) VALUES
(2, 'Jan', 'Bananowicz', 'loczkek2@gmail.com', '$2y$10$TWXpv/sZJG5GQ5L686wuZO9YsSZhzFBeuJvDtpM5ZLSY1WrWPAlwu', '727766999', 'Lotniskowa', '42424', 'ULC1231/25', '2025-05-19 21:16:02', NULL, 0),
(3, 'Jan', 'Bednarek', 'benarek@gmail.com', '$2y$10$1Jh.yFqrqI/JVEiMjCj5b.sDPMg06.SjelgPgCZzgfCHI0dtnqrn6', '123456789', 'Boisko Piłkarskie 112', 'NIEMAM', 'ULC564', '2025-05-19 21:25:28', NULL, 0),
(4, 'Pantera', 'Pantera', 'pantera@gmail.com', '$2y$10$Id93ZdhHf1kRmXbWoXv68ekMfX9tadgYwAq4uh6zhbuYV9I/Z.qL2', '72725252', 'panterkowa12 Warszawa', '121241', '', '2025-05-19 22:50:34', NULL, 0),
(5, 'Admin', 'SkyDrive', 'admin@skydrive.pl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, '2025-05-19 23:16:45', NULL, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `vehicleequipment`
--

CREATE TABLE `vehicleequipment` (
  `vehicle_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `vehicles`
--

CREATE TABLE `vehicles` (
  `vehicle_id` int(11) NOT NULL,
  `type` enum('car','plane') NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `registration_number` varchar(20) DEFAULT NULL,
  `capacity` int(11) NOT NULL,
  `fuel_type` varchar(30) DEFAULT NULL,
  `engine_power` varchar(30) DEFAULT NULL,
  `max_speed` varchar(30) DEFAULT NULL,
  `range` varchar(30) DEFAULT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `available` tinyint(1) DEFAULT 1,
  `location_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `type`, `make`, `model`, `year`, `registration_number`, `capacity`, `fuel_type`, `engine_power`, `max_speed`, `range`, `daily_rate`, `hourly_rate`, `available`, `location_id`, `image_path`, `description`) VALUES
(1, 'car', 'Audi', 'A6', 2022, 'WA12345', 5, 'Benzyna', '250 KM', NULL, NULL, '400.00', NULL, 1, 1, 'https://img.chceauto.pl/audi/a6/audi-a6-kombi-4472-49497_head.webp', 'Luksusowy sedan z pełnym wyposażeniem'),
(2, 'car', 'BMW', 'X5', 2021, 'WA67890', 5, 'Diesel', '300 KM', NULL, NULL, '500.00', NULL, 1, 1, 'https://bmw-uzywane.com.pl/assets/photo/upload/cars/30706/vehicle_70f26-scale-1200-0.jpg', 'SUV premium z napędem 4x4'),
(3, 'car', 'Mercedes-Benz', 'S-Class', 2023, 'KR54321', 4, 'Hybryda', '367 KM', NULL, NULL, '600.00', NULL, 1, 2, 'https://www.motortrend.com/uploads/2023/01/2023-Mercedes-Benz-S580-4Matic-13.jpg?w=768&width=768&q=75&format=webp', 'Flagowy model Mercedesa z najnowszymi technologiami'),
(4, 'plane', 'Cessna', '172', 2018, 'SP-ABC', 4, 'Avgas', '160 KM', '230 km/h', '1200 km', '0.00', '2000.00', 1, 3, 'https://www.flyouts.com/images/thumbnails/product_image-3285-1306x735.jpg', 'Klasyczny samolot szkolno-turystyczny'),
(5, 'plane', 'Pilatus', 'PC-12', 2020, 'SP-DEF', 9, 'Jet A-1', '1200 KM', '500 km/h', '3300 km', '0.00', '5000.00', 1, 5, 'https://aviationconsumer.com/wp-content/uploads/2019/09/p1a37g676bfgh1imo3skpe1dpn6.jpg', 'Jednosilnikowy samolot turbośmigłowy biznesowy'),
(6, 'plane', 'Beechcraft', 'King Air 350', 2019, 'SP-GHI', 11, 'Jet A-1', '2x1050 KM', '560 km/h', '3000 km', '0.00', '8000.00', 1, 5, 'https://images.aircharterservice.com/global/aircraft-guide/private-charter/beechcraft-king-air-300-350-1.jpg', 'Dwusilnikowy samolot biznesowy'),
(7, 'plane', 'Cessna', 'C152', 1977, 'SP-EPS', 2, 'AvGas', '110', '110kts', '2000', '0.00', '790.00', 1, 5, 'https://www.aircraft24.pl/images/aircraftpics/74/pic_142474_1_xxl.jpg', 'Samolot turystyczno-szkoolnt');

-- --------------------------------------------------------

--
-- Struktura widoku `available_cars`
--
DROP TABLE IF EXISTS `available_cars`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `available_cars`  AS SELECT `v`.`vehicle_id` AS `vehicle_id`, `v`.`make` AS `make`, `v`.`model` AS `model`, `v`.`year` AS `year`, `v`.`registration_number` AS `registration_number`, `v`.`capacity` AS `capacity`, `v`.`fuel_type` AS `fuel_type`, `v`.`engine_power` AS `engine_power`, `v`.`daily_rate` AS `daily_rate`, `v`.`image_path` AS `image_path`, `v`.`description` AS `description`, `l`.`city` AS `city`, `l`.`address` AS `address`, `l`.`is_airport` AS `is_airport` FROM (`vehicles` `v` join `locations` `l` on(`v`.`location_id` = `l`.`location_id`)) WHERE `v`.`type` = 'car' AND `v`.`available` = 1 ;

-- --------------------------------------------------------

--
-- Struktura widoku `available_planes`
--
DROP TABLE IF EXISTS `available_planes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `available_planes`  AS SELECT `v`.`vehicle_id` AS `vehicle_id`, `v`.`make` AS `make`, `v`.`model` AS `model`, `v`.`year` AS `year`, `v`.`registration_number` AS `registration_number`, `v`.`capacity` AS `capacity`, `v`.`fuel_type` AS `fuel_type`, `v`.`engine_power` AS `engine_power`, `v`.`max_speed` AS `max_speed`, `v`.`range` AS `range`, `v`.`hourly_rate` AS `hourly_rate`, `v`.`image_path` AS `image_path`, `v`.`description` AS `description`, `l`.`city` AS `city`, `l`.`address` AS `address`, `l`.`is_airport` AS `is_airport` FROM (`vehicles` `v` join `locations` `l` on(`v`.`location_id` = `l`.`location_id`)) WHERE `v`.`type` = 'plane' AND `v`.`available` = 1 ;

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`);

--
-- Indeksy dla tabeli `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indeksy dla tabeli `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Indeksy dla tabeli `reservationequipment`
--
ALTER TABLE `reservationequipment`
  ADD PRIMARY KEY (`reservation_id`,`equipment_id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indeksy dla tabeli `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `pickup_location_id` (`pickup_location_id`),
  ADD KEY `return_location_id` (`return_location_id`);

--
-- Indeksy dla tabeli `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeksy dla tabeli `vehicleequipment`
--
ALTER TABLE `vehicleequipment`
  ADD PRIMARY KEY (`vehicle_id`,`equipment_id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indeksy dla tabeli `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`),
  ADD KEY `location_id` (`location_id`);

--
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT dla tabeli `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT dla tabeli `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT dla tabeli `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT dla tabeli `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`);

--
-- Ograniczenia dla tabeli `reservationequipment`
--
ALTER TABLE `reservationequipment`
  ADD CONSTRAINT `reservationequipment_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`),
  ADD CONSTRAINT `reservationequipment_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`);

--
-- Ograniczenia dla tabeli `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`),
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`pickup_location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `reservations_ibfk_4` FOREIGN KEY (`return_location_id`) REFERENCES `locations` (`location_id`);

--
-- Ograniczenia dla tabeli `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`);

--
-- Ograniczenia dla tabeli `vehicleequipment`
--
ALTER TABLE `vehicleequipment`
  ADD CONSTRAINT `vehicleequipment_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`),
  ADD CONSTRAINT `vehicleequipment_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`);

--
-- Ograniczenia dla tabeli `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
