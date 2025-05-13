-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Maj 13, 2025 at 11:21 AM
-- Wersja serwera: 10.4.28-MariaDB
-- Wersja PHP: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wypozyczalnia`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `login` varchar(100) NOT NULL,
  `haslo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `cars`
--

CREATE TABLE `cars` (
  `car_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `license_plate` varchar(20) NOT NULL,
  `mileage` int(11) NOT NULL,
  `fuel_type` enum('benzyna','diesel','hybryda','elektryk') NOT NULL,
  `transmission` enum('manualna','automat') NOT NULL,
  `seats` int(11) NOT NULL,
  `doors` int(11) NOT NULL,
  `daily_price` decimal(10,2) NOT NULL,
  `available` tinyint(1) DEFAULT 1,
  `image_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`car_id`, `category_id`, `brand`, `model`, `year`, `color`, `license_plate`, `mileage`, `fuel_type`, `transmission`, `seats`, `doors`, `daily_price`, `available`, `image_url`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Toyota', 'Yaris', 2022, 'biały', 'WW12345', 15000, 'hybryda', 'automat', 5, 5, 150.00, 1, 'https://example.com/toyota-yaris.jpg', 'Ekonomiczny samochód miejski z napędem hybrydowym', '2025-05-12 23:18:30', '2025-05-12 23:18:30'),
(2, 2, 'Volkswagen', 'Golf', 2021, 'czerwony', 'WW67890', 25000, 'benzyna', 'manualna', 5, 5, 180.00, 1, 'https://example.com/vw-golf.jpg', 'Klasyczny kompaktowy samochód rodzinny', '2025-05-12 23:18:30', '2025-05-12 23:18:30'),
(3, 3, 'BMW', 'Seria 5', 2023, 'czarny', 'WB12345', 5000, 'diesel', 'automat', 5, 4, 350.00, 1, 'https://example.com/bmw-5.jpg', 'Luksusowy sedan biznesowy', '2025-05-12 23:18:30', '2025-05-12 23:18:30'),
(4, 4, 'Land Rover', 'Defender', 2022, 'zielony', 'WB67890', 12000, 'diesel', 'automat', 7, 5, 450.00, 1, 'https://example.com/defender.jpg', 'Legendarny SUV terenowy', '2025-05-12 23:18:30', '2025-05-12 23:18:30'),
(5, 5, 'Porsche', '911', 2023, 'żółty', 'WB98765', 3000, 'benzyna', 'automat', 2, 2, 800.00, 1, 'https://example.com/porsche-911.jpg', 'Ikona sportowych samochodów', '2025-05-12 23:18:30', '2025-05-12 23:18:30'),
(6, 6, 'Tesla', 'Model 3', 2023, 'biały', 'WW54321', 8000, 'elektryk', 'automat', 5, 4, 400.00, 1, 'https://example.com/tesla-model3.jpg', 'Nowoczesny elektryczny sedan', '2025-05-12 23:18:30', '2025-05-12 23:18:30');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `car_categories`
--

CREATE TABLE `car_categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `daily_rate` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_categories`
--

INSERT INTO `car_categories` (`category_id`, `name`, `description`, `daily_rate`) VALUES
(1, 'Ekonomiczne', 'Samochody o niskim spalaniu, idealne do miasta', 50.00),
(2, 'Standard', 'Komfortowe samochody do codziennego użytku', 80.00),
(3, 'Premium', 'Luksusowe samochody wysokiej klasy', 120.00),
(4, 'SUV', 'Pojazdy terenowe i rodzinne', 100.00),
(5, 'Sportowe', 'Samochody o wysokich osiągach', 150.00),
(6, 'Elektryczne', 'Ekologiczne pojazdy elektryczne', 90.00);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `daily_cost` decimal(10,2) NOT NULL,
  `available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`equipment_id`, `name`, `description`, `daily_cost`, `available`) VALUES
(1, 'Nawigacja GPS', 'System nawigacji satelitarnej', 15.00, 1),
(2, 'Dziecięcy fotelik', 'Fotelik samochodowy dla dzieci 9-36 kg', 10.00, 1),
(3, 'Ładowarka do EV', 'Przenośna ładowarka do samochodów elektrycznych', 20.00, 1),
(4, 'Pakiet zimowy', 'Łańcuchy śniegowe i skrobaczka', 12.00, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('karta','przelew','gotówka','blik') NOT NULL,
  `payment_date` datetime NOT NULL,
  `status` enum('oczekujący','zakończony','anulowany','zwrócony') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `rental_id`, `amount`, `payment_method`, `payment_date`, `status`, `transaction_id`, `notes`) VALUES
(1, 1, 1400.00, 'karta', '2023-05-28 15:30:00', 'zakończony', 'PAY123456789', NULL),
(2, 2, 2000.00, 'przelew', '2023-06-01 10:15:00', 'zakończony', 'PAY987654321', NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `rentals`
--

CREATE TABLE `rentals` (
  `rental_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `actual_return_date` datetime DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `insurance_type` enum('basic','premium','full') NOT NULL,
  `status` enum('zarezerwowany','w trakcie','zakończony','anulowany') NOT NULL DEFAULT 'zarezerwowany',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`rental_id`, `user_id`, `car_id`, `start_date`, `end_date`, `actual_return_date`, `total_price`, `insurance_type`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 3, '2023-06-01 10:00:00', '2023-06-05 18:00:00', NULL, 1400.00, 'premium', 'zakończony', NULL, '2025-05-12 23:18:30', '2025-05-12 23:18:30'),
(2, 2, 5, '2023-06-10 09:00:00', '2023-06-15 17:00:00', NULL, 4000.00, 'full', 'zarezerwowany', NULL, '2025-05-12 23:18:30', '2025-05-12 23:18:30');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `rental_equipment`
--

CREATE TABLE `rental_equipment` (
  `rental_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rental_equipment`
--

INSERT INTO `rental_equipment` (`rental_id`, `equipment_id`, `quantity`) VALUES
(1, 1, 1),
(1, 2, 1),
(2, 3, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `samoloty`
--

CREATE TABLE `samoloty` (
  `id` int(100) NOT NULL,
  `nazwa` varchar(100) NOT NULL,
  `typ` varchar(100) NOT NULL,
  `miejscowosc` varchar(100) NOT NULL,
  `lotnisko` varchar(100) NOT NULL,
  `kraj` int(100) NOT NULL,
  `cena` int(100) NOT NULL,
  `waluta` varchar(100) NOT NULL,
  `opis` varchar(100) NOT NULL,
  `zdjecie` int(100) NOT NULL,
  `dostepny` tinyint(1) NOT NULL,
  `data_dodania` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `address`, `driver_license_number`, `created_at`, `updated_at`) VALUES
(1, 'Jan', 'Kowalski', 'jan.kowalski@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+48123456789', 'ul. Kwiatowa 10, Warszawa', 'DW12345678', '2025-05-12 23:18:30', '2025-05-12 23:18:30'),
(2, 'Anna', 'Nowak', 'anna.nowak@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+48987654321', 'ul. Słoneczna 5, Kraków', 'DW87654321', '2025-05-12 23:18:30', '2025-05-12 23:18:30');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`car_id`),
  ADD UNIQUE KEY `license_plate` (`license_plate`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_brand_model` (`brand`,`model`),
  ADD KEY `idx_available` (`available`);

--
-- Indeksy dla tabeli `car_categories`
--
ALTER TABLE `car_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `idx_name` (`name`);

--
-- Indeksy dla tabeli `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`),
  ADD KEY `idx_name` (`name`);

--
-- Indeksy dla tabeli `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `rental_id` (`rental_id`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indeksy dla tabeli `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`rental_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`),
  ADD KEY `idx_dates` (`start_date`,`end_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indeksy dla tabeli `rental_equipment`
--
ALTER TABLE `rental_equipment`
  ADD PRIMARY KEY (`rental_id`,`equipment_id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indeksy dla tabeli `samoloty`
--
ALTER TABLE `samoloty`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `car_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `car_categories`
--
ALTER TABLE `car_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `samoloty`
--
ALTER TABLE `samoloty`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cars`
--
ALTER TABLE `cars`
  ADD CONSTRAINT `cars_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `car_categories` (`category_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`);

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`);

--
-- Constraints for table `rental_equipment`
--
ALTER TABLE `rental_equipment`
  ADD CONSTRAINT `rental_equipment_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`),
  ADD CONSTRAINT `rental_equipment_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
