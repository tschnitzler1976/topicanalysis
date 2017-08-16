-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 16. Aug 2017 um 14:28
-- Server-Version: 10.1.21-MariaDB
-- PHP-Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `topic_analysis_for_github`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lda`
--

CREATE TABLE `lda` (
  `id` int(11) NOT NULL,
  `id_topic_analysis` int(11) NOT NULL,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `number_of_topics_to_output` int(11) NOT NULL,
  `conference_selected` text COLLATE utf8_unicode_ci NOT NULL,
  `year_from_selected` text COLLATE utf8_unicode_ci NOT NULL,
  `year_to_selected` text COLLATE utf8_unicode_ci NOT NULL,
  `dirname` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lda_id_search_results`
--

CREATE TABLE `lda_id_search_results` (
  `id` int(11) NOT NULL,
  `id_lda` int(11) NOT NULL,
  `id_search_result` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `research_questions`
--

CREATE TABLE `research_questions` (
  `id` int(11) NOT NULL,
  `id_topic_analysis` int(11) NOT NULL,
  `name` mediumtext COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `search_results`
--

CREATE TABLE `search_results` (
  `id` int(11) NOT NULL,
  `id_search_strings` int(11) NOT NULL,
  `exclude` bit(1) NOT NULL,
  `authors` mediumtext CHARACTER SET utf8 NOT NULL,
  `title` mediumtext CHARACTER SET utf8 NOT NULL,
  `conference` mediumtext CHARACTER SET utf8 NOT NULL,
  `year` year(4) NOT NULL,
  `first_link_to_abstracttext` mediumtext CHARACTER SET utf8 NOT NULL,
  `abstracttext` longtext CHARACTER SET utf8 NOT NULL,
  `abstracttext_for_lda` longtext CHARACTER SET utf8 NOT NULL,
  `first_link_to_pdffulltext` mediumtext CHARACTER SET utf8 NOT NULL,
  `path_to_pdffulltext` mediumtext CHARACTER SET utf8 NOT NULL,
  `pdffulltext_as_text` longtext CHARACTER SET utf8 NOT NULL,
  `pdffulltext_as_text_extracted` longtext CHARACTER SET utf8 NOT NULL,
  `pdffulltext_for_lda` longtext CHARACTER SET utf8 NOT NULL,
  `exclusion_already_done` bit(1) NOT NULL,
  `preprocessing_abstracttext_already_done` bit(1) NOT NULL,
  `preprocessing_pdffulltext_already_done` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `search_strings`
--

CREATE TABLE `search_strings` (
  `id` int(11) NOT NULL,
  `id_topic_analysis` int(11) NOT NULL,
  `name` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `htmlsource` mediumtext COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `search_strings_for_results`
--

CREATE TABLE `search_strings_for_results` (
  `id` int(11) NOT NULL,
  `id_topic_analysis` int(11) NOT NULL,
  `name` mediumtext COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `topic_analysis`
--

CREATE TABLE `topic_analysis` (
  `id` int(11) NOT NULL,
  `name` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `lda`
--
ALTER TABLE `lda`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `lda_id_search_results`
--
ALTER TABLE `lda_id_search_results`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `research_questions`
--
ALTER TABLE `research_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `search_results`
--
ALTER TABLE `search_results`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `search_strings`
--
ALTER TABLE `search_strings`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `search_strings_for_results`
--
ALTER TABLE `search_strings_for_results`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `topic_analysis`
--
ALTER TABLE `topic_analysis`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `lda`
--
ALTER TABLE `lda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT für Tabelle `lda_id_search_results`
--
ALTER TABLE `lda_id_search_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1111;
--
-- AUTO_INCREMENT für Tabelle `research_questions`
--
ALTER TABLE `research_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;
--
-- AUTO_INCREMENT für Tabelle `search_results`
--
ALTER TABLE `search_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1717;
--
-- AUTO_INCREMENT für Tabelle `search_strings`
--
ALTER TABLE `search_strings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=847;
--
-- AUTO_INCREMENT für Tabelle `search_strings_for_results`
--
ALTER TABLE `search_strings_for_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=392;
--
-- AUTO_INCREMENT für Tabelle `topic_analysis`
--
ALTER TABLE `topic_analysis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
