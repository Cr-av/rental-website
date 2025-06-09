-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Cze 09, 2025 at 10:47 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

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
-- Struktura tabeli dla tabeli `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `tytul` varchar(255) NOT NULL,
  `typ` enum('ksiazka','film') NOT NULL,
  `link` varchar(255) NOT NULL,
  `dostepnosc` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `tytul`, `typ`, `link`, `dostepnosc`) VALUES
(1, 'W pustyni i w puszczy', 'ksiazka', 'media/w-pustyni-i-w-puszczy.pdf', 1),
(2, 'Lalka', 'ksiazka', 'media/lalka-tom-pierwszy.pdf', 1),
(3, 'Pan Tadeusz', 'ksiazka', 'media/pan-tadeusz.pdf', 1),
(4, 'Incepcja', 'film', 'media/Incepcja.mp4', 1),
(5, 'Matrix', 'film', 'media/MATRIX.mp4', 1),
(8, '1984', 'ksiazka', 'media/1984.pdf', 1),
(9, 'Chłopi', 'ksiazka', 'media/chlopi.pdf', 1),
(10, 'Forrest Gump', 'film', 'media/Forrest_Gump.mp4', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `imie` varchar(50) NOT NULL,
  `nazwisko` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `haslo_hash` varchar(255) NOT NULL,
  `rola` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `imie`, `nazwisko`, `email`, `haslo_hash`, `rola`) VALUES
(1, 'Admin', 'System', 'admin@localhost', '$2y$10$6sOvvK7gxM7Y1GVfxwZgfeLZgP84sOYz4bRcET6uWYNJ/q.GMgA3i', 'admin'),
(2, 'Pawel', 'Zalewski', '123@o2.pl', '$2y$10$ExISfbRDwqn9HFB.SBxZ..a51uxU1.b4mlMqbP3i3rLe1Oifc/jUq', 'user'),
(3, 'Przemek', 'Zal', 'xsuperx122@onet.pl', '$2y$10$MzWRJKJJY7VBxplessiv2OlCyblyrdJkXCth142Kx4aUVqx18GhY2', 'admin');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `wypozyczenia`
--

CREATE TABLE `wypozyczenia` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `data_wypozyczenia` datetime NOT NULL DEFAULT current_timestamp(),
  `data_zwrotu` datetime DEFAULT NULL,
  `status` enum('wypozyczone','zwrocone') NOT NULL DEFAULT 'wypozyczone',
  `oplata` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wypozyczenia`
--

INSERT INTO `wypozyczenia` (`id`, `user_id`, `media_id`, `data_wypozyczenia`, `data_zwrotu`, `status`, `oplata`) VALUES
(47, 3, 1, '2025-06-09 16:59:35', '2025-06-09 16:59:50', 'zwrocone', 2.00),
(48, 3, 1, '2025-06-09 17:00:36', '2025-06-09 17:00:40', 'zwrocone', 28.00),
(49, 3, 1, '2025-06-09 17:01:26', '2025-06-09 17:01:34', 'zwrocone', 14.00),
(50, 3, 10, '2025-06-09 17:02:27', '2025-06-09 17:02:33', 'zwrocone', 2.00),
(52, 3, 1, '2025-06-09 17:16:06', '2025-06-09 17:16:16', 'zwrocone', 28.00),
(53, 3, 1, '2025-06-09 17:27:40', '2025-06-09 17:31:57', 'zwrocone', 2.00);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeksy dla tabeli `wypozyczenia`
--
ALTER TABLE `wypozyczenia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `media_id` (`media_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wypozyczenia`
--
ALTER TABLE `wypozyczenia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `wypozyczenia`
--
ALTER TABLE `wypozyczenia`
  ADD CONSTRAINT `wypozyczenia_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wypozyczenia_ibfk_2` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
