-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 18, 2025 at 10:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 7.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crick-tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `cricket_matches`
--

CREATE TABLE `cricket_matches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `team_a_id` bigint(20) UNSIGNED NOT NULL,
  `team_b_id` bigint(20) UNSIGNED NOT NULL,
  `tournament_id` bigint(20) UNSIGNED DEFAULT NULL,
  `stage` enum('group','playoffs','semi-final','final') NOT NULL,
  `match_date` datetime NOT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `match_type` enum('tournament','regular') NOT NULL DEFAULT 'regular',
  `status` enum('upcoming','live','completed') NOT NULL DEFAULT 'upcoming',
  `winning_team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `result_summary` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cricket_matches`
--

INSERT INTO `cricket_matches` (`id`, `title`, `team_a_id`, `team_b_id`, `tournament_id`, `stage`, `match_date`, `venue`, `match_type`, `status`, `winning_team_id`, `result_summary`, `created_at`, `updated_at`) VALUES
(13, 'Storm Strikers vs Sky Smashers', 10, 6, 2, 'group', '2025-07-23 12:24:00', NULL, 'tournament', 'upcoming', NULL, NULL, '2025-07-15 12:58:07', '2025-07-15 12:58:07'),
(14, 'Storm Strikers vs Mountain Kings', 10, 7, 2, 'group', '2025-07-20 18:10:00', NULL, 'tournament', 'upcoming', NULL, NULL, '2025-07-15 12:58:07', '2025-07-15 12:58:07'),
(15, 'Storm Strikers vs Valley Vipers', 10, 8, 2, 'group', '2025-07-26 18:07:00', NULL, 'tournament', 'upcoming', NULL, NULL, '2025-07-15 12:58:07', '2025-07-15 12:58:07'),
(16, 'Sky Smashers vs Mountain Kings', 6, 7, 2, 'group', '2025-07-22 17:27:00', NULL, 'tournament', 'upcoming', NULL, NULL, '2025-07-15 12:58:07', '2025-07-15 12:58:07'),
(17, 'Sky Smashers vs Valley Vipers', 6, 8, 2, 'group', '2025-07-24 10:12:00', NULL, 'tournament', 'upcoming', NULL, NULL, '2025-07-15 12:58:07', '2025-07-15 12:58:07'),
(18, 'Mountain Kings vs Valley Vipers', 7, 8, 2, 'group', '2025-07-26 09:29:00', NULL, 'tournament', 'upcoming', NULL, NULL, '2025-07-15 12:58:07', '2025-07-15 12:58:07'),
(19, 'Desert Hawks vs Forest Rangers', 4, 9, 2, 'group', '2025-07-25 10:30:00', NULL, 'tournament', 'upcoming', NULL, NULL, '2025-07-15 12:58:07', '2025-07-15 12:58:07'),
(20, 'Desert Hawks vs Ocean Blazers', 4, 5, 2, 'group', '2025-07-22 16:12:00', NULL, 'tournament', 'upcoming', NULL, NULL, '2025-07-15 12:58:07', '2025-07-15 12:58:07'),
(21, 'Desert Hawks vs Thunder Warriors', 4, 3, 2, 'group', '2025-07-25 11:06:00', NULL, 'tournament', 'upcoming', NULL, NULL, '2025-07-15 12:58:07', '2025-07-15 12:58:07'),
(22, 'Forest Rangers vs Ocean Blazers', 9, 5, 2, 'group', '2025-07-24 14:14:00', NULL, 'tournament', 'upcoming', NULL, NULL, '2025-07-15 12:58:07', '2025-07-15 12:58:07'),
(23, 'Forest Rangers vs Thunder Warriors', 9, 3, 2, 'group', '2025-07-27 12:29:00', NULL, 'tournament', 'upcoming', NULL, NULL, '2025-07-15 12:58:07', '2025-07-15 12:58:07'),
(24, 'Ocean Blazers vs Thunder Warriors', 5, 3, 2, 'group', '2025-07-23 09:20:00', NULL, 'tournament', 'upcoming', NULL, NULL, '2025-07-15 12:58:07', '2025-07-15 12:58:07');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fall_of_wickets`
--

CREATE TABLE `fall_of_wickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `match_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `wicket_number` tinyint(3) UNSIGNED NOT NULL,
  `runs` int(10) UNSIGNED NOT NULL,
  `overs` decimal(4,1) UNSIGNED NOT NULL,
  `batter_id` bigint(20) UNSIGNED NOT NULL,
  `bowler_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fielder_id` bigint(20) UNSIGNED DEFAULT NULL,
  `dismissal_type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `match_deliveries`
--

CREATE TABLE `match_deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `match_id` bigint(20) UNSIGNED NOT NULL,
  `over_number` bigint(20) UNSIGNED NOT NULL,
  `ball_in_over` bigint(20) UNSIGNED NOT NULL,
  `bowler_id` bigint(20) UNSIGNED NOT NULL,
  `batsman_id` bigint(20) UNSIGNED NOT NULL,
  `non_striker_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batting_team_id` bigint(20) UNSIGNED NOT NULL,
  `bowling_team_id` bigint(20) UNSIGNED NOT NULL,
  `runs_batsman` tinyint(4) NOT NULL DEFAULT 0,
  `runs_extras` tinyint(4) NOT NULL DEFAULT 0,
  `delivery_type` enum('normal','wide','no-ball','bye','leg-bye') NOT NULL DEFAULT 'normal',
  `is_wicket` tinyint(1) NOT NULL DEFAULT 0,
  `wicket_type` enum('bowled','caught','lbw','run out','stumped','hit wicket','retired hurt','none') NOT NULL DEFAULT 'none',
  `wicket_player_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fielder_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `match_players`
--

CREATE TABLE `match_players` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `match_id` bigint(20) UNSIGNED NOT NULL,
  `player_id` bigint(20) UNSIGNED NOT NULL,
  `runs_scored` int(10) UNSIGNED DEFAULT NULL,
  `balls_faced` int(10) UNSIGNED DEFAULT NULL,
  `wickets_taken` int(10) UNSIGNED DEFAULT NULL,
  `overs_bowled` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `match_score_boards`
--

CREATE TABLE `match_score_boards` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `match_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `innings` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `runs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `wickets` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `overs` double(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_100000_create_password_resets_table', 1),
(2, '2019_08_19_000000_create_failed_jobs_table', 1),
(3, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(4, '2025_07_06_201040_create_permission_tables', 1),
(5, '2025_07_07_000000_create_users_table', 1),
(6, '2025_07_07_211106_create_players_table', 2),
(7, '2025_07_08_203129_create_teams_table', 3),
(8, '2025_07_08_203422_create_player_team_table', 3),
(22, '2025_07_11_200240_create_tournaments_table', 4),
(23, '2025_07_11_201521_create_cricket_matches_table', 4),
(24, '2025_07_11_202554_create_match_score_boards_table', 4),
(25, '2025_07_11_202720_create_match_players_table', 4),
(26, '2025_07_11_223300_create_match_deliveries_table', 4),
(27, '2025_07_11_223403_create_tournament_groups_table', 4),
(28, '2025_07_11_223445_create_tournament_group_teams_table', 4),
(29, '2025_07_11_223609_create_fall_of_wickets_table', 4),
(30, '2025_07_11_223637_create_partnerships_table', 4),
(31, '2025_07_12_202217_create_add_columns_to_tournaments_table', 5),
(32, '2025_07_12_202634_create_tournament_team_stats_table', 5),
(33, '2025_07_12_203603_create_player_stats_table', 6),
(34, '2025_07_15_080234_add_group_count_coulmn_to_tournaments', 7),
(35, '2025_07_15_094953_add_tournament_id_coulmn_to_tournament_group_teams', 7),
(36, '2025_07_15_114342_add_stage_coulmn_to_cricket_matches', 7),
(37, '2025_07_18_194102_create_tournament_player_stats_table', 8);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 7),
(3, 'App\\Models\\User', 8),
(3, 'App\\Models\\User', 9),
(3, 'App\\Models\\User', 10),
(3, 'App\\Models\\User', 11),
(3, 'App\\Models\\User', 12),
(3, 'App\\Models\\User', 13),
(3, 'App\\Models\\User', 14),
(3, 'App\\Models\\User', 15),
(3, 'App\\Models\\User', 16),
(3, 'App\\Models\\User', 17),
(3, 'App\\Models\\User', 18),
(3, 'App\\Models\\User', 19),
(3, 'App\\Models\\User', 20),
(3, 'App\\Models\\User', 21),
(3, 'App\\Models\\User', 22),
(3, 'App\\Models\\User', 23),
(3, 'App\\Models\\User', 24),
(3, 'App\\Models\\User', 25),
(3, 'App\\Models\\User', 26),
(3, 'App\\Models\\User', 27),
(3, 'App\\Models\\User', 28),
(3, 'App\\Models\\User', 29),
(3, 'App\\Models\\User', 30),
(3, 'App\\Models\\User', 31),
(3, 'App\\Models\\User', 32),
(3, 'App\\Models\\User', 33),
(3, 'App\\Models\\User', 34),
(3, 'App\\Models\\User', 35),
(3, 'App\\Models\\User', 36),
(3, 'App\\Models\\User', 37),
(3, 'App\\Models\\User', 38),
(3, 'App\\Models\\User', 39),
(3, 'App\\Models\\User', 40),
(3, 'App\\Models\\User', 41),
(3, 'App\\Models\\User', 42),
(3, 'App\\Models\\User', 43),
(3, 'App\\Models\\User', 44),
(3, 'App\\Models\\User', 45),
(3, 'App\\Models\\User', 46),
(3, 'App\\Models\\User', 47),
(3, 'App\\Models\\User', 48),
(3, 'App\\Models\\User', 49),
(3, 'App\\Models\\User', 50),
(3, 'App\\Models\\User', 51),
(3, 'App\\Models\\User', 52),
(3, 'App\\Models\\User', 53),
(3, 'App\\Models\\User', 54),
(3, 'App\\Models\\User', 55),
(3, 'App\\Models\\User', 56),
(3, 'App\\Models\\User', 57),
(3, 'App\\Models\\User', 58),
(3, 'App\\Models\\User', 59),
(3, 'App\\Models\\User', 60),
(3, 'App\\Models\\User', 61),
(3, 'App\\Models\\User', 62),
(3, 'App\\Models\\User', 63),
(3, 'App\\Models\\User', 64),
(3, 'App\\Models\\User', 65),
(3, 'App\\Models\\User', 66),
(3, 'App\\Models\\User', 67),
(3, 'App\\Models\\User', 68),
(3, 'App\\Models\\User', 69),
(3, 'App\\Models\\User', 70),
(3, 'App\\Models\\User', 71),
(3, 'App\\Models\\User', 72),
(3, 'App\\Models\\User', 73),
(3, 'App\\Models\\User', 74),
(3, 'App\\Models\\User', 75),
(3, 'App\\Models\\User', 76),
(3, 'App\\Models\\User', 77),
(3, 'App\\Models\\User', 78),
(3, 'App\\Models\\User', 79),
(3, 'App\\Models\\User', 80),
(3, 'App\\Models\\User', 81),
(3, 'App\\Models\\User', 82),
(3, 'App\\Models\\User', 83),
(3, 'App\\Models\\User', 84),
(3, 'App\\Models\\User', 85),
(3, 'App\\Models\\User', 86),
(3, 'App\\Models\\User', 87),
(3, 'App\\Models\\User', 88),
(3, 'App\\Models\\User', 89),
(3, 'App\\Models\\User', 90),
(3, 'App\\Models\\User', 91),
(3, 'App\\Models\\User', 92),
(3, 'App\\Models\\User', 93),
(3, 'App\\Models\\User', 94),
(3, 'App\\Models\\User', 95),
(3, 'App\\Models\\User', 96);

-- --------------------------------------------------------

--
-- Table structure for table `partnerships`
--

CREATE TABLE `partnerships` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `match_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `batter_1_id` bigint(20) UNSIGNED NOT NULL,
  `batter_2_id` bigint(20) UNSIGNED DEFAULT NULL,
  `runs` int(10) UNSIGNED NOT NULL,
  `balls` int(10) UNSIGNED NOT NULL,
  `start_over` decimal(4,1) UNSIGNED NOT NULL,
  `end_over` decimal(4,1) UNSIGNED DEFAULT NULL,
  `wicket_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(9, 'users-view', 'web', '2025-07-06 15:46:52', '2025-07-06 15:46:52'),
(10, 'users-create', 'web', '2025-07-06 15:46:52', '2025-07-06 15:46:52'),
(11, 'users-edit', 'web', '2025-07-06 15:46:52', '2025-07-06 15:46:52'),
(12, 'users-delete', 'web', '2025-07-06 15:46:52', '2025-07-06 15:46:52'),
(13, 'roles-view', 'web', '2025-07-06 15:46:52', '2025-07-06 15:46:52'),
(14, 'roles-create', 'web', '2025-07-06 15:46:52', '2025-07-06 15:46:52'),
(15, 'roles-edit', 'web', '2025-07-06 15:46:52', '2025-07-06 15:46:52'),
(16, 'roles-delete', 'web', '2025-07-06 15:46:52', '2025-07-06 15:46:52'),
(17, 'permissions-view', 'web', '2025-07-07 12:57:56', '2025-07-07 12:57:56'),
(18, 'permissions-create', 'web', '2025-07-07 12:57:56', '2025-07-07 12:57:56'),
(19, 'permissions-edit', 'web', '2025-07-07 12:57:56', '2025-07-07 12:57:56'),
(20, 'permissions-delete', 'web', '2025-07-07 12:57:56', '2025-07-07 12:57:56'),
(21, 'players-view', 'web', '2025-07-07 15:10:29', '2025-07-07 15:10:29'),
(22, 'players-create', 'web', '2025-07-07 15:10:30', '2025-07-07 15:10:30'),
(23, 'players-edit', 'web', '2025-07-07 15:10:30', '2025-07-07 15:10:30'),
(24, 'players-delete', 'web', '2025-07-07 15:10:30', '2025-07-07 15:10:30'),
(25, 'teams-view', 'web', '2025-07-08 14:38:52', '2025-07-08 14:38:52'),
(26, 'teams-create', 'web', '2025-07-08 14:38:52', '2025-07-08 14:38:52'),
(27, 'teams-edit', 'web', '2025-07-08 14:38:52', '2025-07-08 14:38:52'),
(28, 'teams-delete', 'web', '2025-07-08 14:38:52', '2025-07-08 14:38:52'),
(29, 'tournaments-view', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(30, 'tournaments-create', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(31, 'tournaments-edit', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(32, 'tournaments-delete', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(33, 'tournament-groups-view', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(34, 'tournament-groups-create', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(35, 'tournament-groups-edit', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(36, 'tournament-groups-delete', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(37, 'cricket-matches-view', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(38, 'cricket-matches-create', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(39, 'cricket-matches-edit', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(40, 'cricket-matches-delete', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(41, 'scoreboard-view', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(42, 'scoreboard-create', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(43, 'scoreboard-edit', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(44, 'scoreboard-delete', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(45, 'match-player-view', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(46, 'match-player-create', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(47, 'match-player-edit', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(48, 'match-player-delete', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(49, 'match-deliveries-view', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(50, 'match-deliveries-create', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(51, 'match-deliveries-edit', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(52, 'match-deliveries-delete', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(53, 'tournamentGroupTeams-view', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(54, 'tournamentGroupTeams-create', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(55, 'tournamentGroupTeams-edit', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(56, 'tournamentGroupTeams-delete', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(57, 'fallOfWickets-view', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(58, 'fallOfWickets-create', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(59, 'fallOfWickets-edit', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(60, 'fallOfWickets-delete', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(61, 'partnerships-view', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(62, 'partnerships-create', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(63, 'partnerships-edit', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(64, 'partnerships-delete', 'web', '2025-07-12 13:16:42', '2025-07-12 13:16:42'),
(65, 'tournaments-assign-teams', 'web', '2025-07-15 12:46:13', '2025-07-15 12:46:13'),
(66, 'tournaments-generate-fixtures', 'web', '2025-07-15 12:46:13', '2025-07-15 12:46:13');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `player_type` enum('registered','guest') NOT NULL DEFAULT 'registered',
  `player_role` enum('batsman','bowler','all-rounder','wicketkeeper') DEFAULT NULL,
  `batting_style` enum('right-handed','left-handed','switch hitter') DEFAULT NULL,
  `bowling_style` enum('fast','medium','spin','none') DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`id`, `user_id`, `player_type`, `player_role`, `batting_style`, `bowling_style`, `deleted_at`, `created_at`, `updated_at`) VALUES
(3, 7, 'registered', 'batsman', 'left-handed', 'fast', NULL, '2025-07-10 14:03:53', '2025-07-10 14:03:53'),
(4, 8, 'registered', 'bowler', 'left-handed', 'medium', NULL, '2025-07-10 14:03:53', '2025-07-10 14:03:53'),
(5, 9, 'registered', 'bowler', 'left-handed', 'medium', NULL, '2025-07-10 14:03:53', '2025-07-10 14:03:53'),
(6, 10, 'registered', 'bowler', 'left-handed', 'fast', NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(7, 11, 'registered', 'bowler', 'right-handed', 'medium', NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(8, 12, 'registered', 'batsman', 'right-handed', 'none', NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(9, 13, 'registered', 'all-rounder', 'left-handed', 'none', NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(10, 14, 'registered', 'bowler', 'right-handed', 'none', NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(11, 15, 'registered', 'batsman', 'right-handed', 'spin', NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(12, 16, 'registered', 'wicketkeeper', 'left-handed', 'spin', NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(13, 17, 'registered', 'batsman', 'left-handed', 'medium', NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(14, 18, 'registered', 'wicketkeeper', 'right-handed', 'none', NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(15, 19, 'registered', 'batsman', 'right-handed', 'fast', NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(16, 20, 'registered', 'batsman', 'right-handed', 'none', NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(17, 21, 'registered', 'wicketkeeper', 'left-handed', 'spin', NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(18, 22, 'registered', 'all-rounder', 'right-handed', 'medium', NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(19, 23, 'registered', 'batsman', 'right-handed', 'fast', NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(20, 24, 'registered', 'wicketkeeper', 'left-handed', 'spin', NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(21, 25, 'registered', 'bowler', 'right-handed', 'medium', NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(22, 26, 'registered', 'all-rounder', 'right-handed', 'medium', NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(23, 27, 'registered', 'bowler', 'left-handed', 'fast', NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(24, 28, 'registered', 'wicketkeeper', 'right-handed', 'none', NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(25, 29, 'registered', 'wicketkeeper', 'left-handed', 'fast', NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(26, 30, 'registered', 'bowler', 'right-handed', 'none', NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(27, 31, 'registered', 'batsman', 'left-handed', 'spin', NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(28, 32, 'registered', 'batsman', 'left-handed', 'spin', NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(29, 33, 'registered', 'all-rounder', 'right-handed', 'spin', NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(30, 34, 'registered', 'wicketkeeper', 'right-handed', 'fast', NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(31, 35, 'registered', 'wicketkeeper', 'left-handed', 'fast', NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(32, 36, 'registered', 'batsman', 'right-handed', 'fast', NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(33, 37, 'registered', 'bowler', 'right-handed', 'none', NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(34, 38, 'registered', 'bowler', 'left-handed', 'none', NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(35, 39, 'registered', 'bowler', 'right-handed', 'spin', NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(36, 40, 'registered', 'bowler', 'right-handed', 'fast', NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(37, 41, 'registered', 'bowler', 'right-handed', 'fast', NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(38, 42, 'registered', 'bowler', 'left-handed', 'none', NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(39, 43, 'registered', 'bowler', 'right-handed', 'none', NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(40, 44, 'registered', 'bowler', 'right-handed', 'medium', NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(41, 45, 'registered', 'bowler', 'right-handed', 'spin', NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(42, 46, 'registered', 'batsman', 'right-handed', 'medium', NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(43, 47, 'registered', 'bowler', 'left-handed', 'medium', NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(44, 48, 'registered', 'bowler', 'right-handed', 'spin', NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(45, 49, 'registered', 'all-rounder', 'right-handed', 'medium', NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(46, 50, 'registered', 'all-rounder', 'left-handed', 'fast', NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(47, 51, 'registered', 'all-rounder', 'right-handed', 'none', NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(48, 52, 'registered', 'batsman', 'right-handed', 'none', NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(49, 53, 'registered', 'wicketkeeper', 'left-handed', 'fast', NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(50, 54, 'registered', 'batsman', 'right-handed', 'medium', NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(51, 55, 'registered', 'wicketkeeper', 'right-handed', 'medium', NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(52, 56, 'registered', 'all-rounder', 'left-handed', 'fast', NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(53, 57, 'registered', 'bowler', 'left-handed', 'medium', NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(54, 58, 'registered', 'batsman', 'right-handed', 'none', NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(55, 59, 'registered', 'all-rounder', 'left-handed', 'medium', NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(56, 60, 'registered', 'bowler', 'left-handed', 'spin', NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(57, 61, 'registered', 'bowler', 'right-handed', 'fast', NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(58, 62, 'registered', 'bowler', 'right-handed', 'fast', NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(59, 63, 'registered', 'all-rounder', 'right-handed', 'spin', NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(60, 64, 'registered', 'all-rounder', 'right-handed', 'spin', NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(61, 65, 'registered', 'bowler', 'left-handed', 'spin', NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(62, 66, 'registered', 'all-rounder', 'left-handed', 'fast', NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(63, 67, 'registered', 'all-rounder', 'left-handed', 'fast', NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(64, 68, 'registered', 'wicketkeeper', 'left-handed', 'none', NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(65, 69, 'registered', 'bowler', 'left-handed', 'medium', NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(66, 70, 'registered', 'all-rounder', 'left-handed', 'fast', NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(67, 71, 'registered', 'bowler', 'left-handed', 'spin', NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(68, 72, 'registered', 'bowler', 'left-handed', 'none', NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(69, 73, 'registered', 'bowler', 'right-handed', 'spin', NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(70, 74, 'registered', 'batsman', 'right-handed', 'medium', NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(71, 75, 'registered', 'bowler', 'right-handed', 'spin', NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(72, 76, 'registered', 'bowler', 'left-handed', 'medium', NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(73, 77, 'registered', 'bowler', 'right-handed', 'medium', NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(74, 78, 'registered', 'wicketkeeper', 'right-handed', 'fast', NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(75, 79, 'registered', 'bowler', 'left-handed', 'none', NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(76, 80, 'registered', 'all-rounder', 'right-handed', 'fast', NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(77, 81, 'registered', 'wicketkeeper', 'right-handed', 'none', NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(78, 82, 'registered', 'all-rounder', 'left-handed', 'medium', NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(79, 83, 'registered', 'batsman', 'right-handed', 'fast', NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(80, 84, 'registered', 'all-rounder', 'right-handed', 'spin', NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(81, 85, 'registered', 'batsman', 'right-handed', 'medium', NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(82, 86, 'registered', 'bowler', 'left-handed', 'none', NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(83, 87, 'registered', 'all-rounder', 'right-handed', 'none', NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(84, 88, 'registered', 'wicketkeeper', 'left-handed', 'fast', NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(85, 89, 'registered', 'wicketkeeper', 'left-handed', 'none', NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(86, 90, 'registered', 'wicketkeeper', 'left-handed', 'fast', NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(87, 91, 'registered', 'wicketkeeper', 'right-handed', 'spin', NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(88, 92, 'registered', 'bowler', 'right-handed', 'medium', NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(89, 93, 'registered', 'batsman', 'right-handed', 'spin', NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(90, 94, 'registered', 'bowler', 'right-handed', 'spin', NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(91, 95, 'registered', 'bowler', 'right-handed', 'medium', NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(92, 96, 'registered', 'wicketkeeper', 'right-handed', 'medium', NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00');

-- --------------------------------------------------------

--
-- Table structure for table `player_statistics`
--

CREATE TABLE `player_statistics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `player_id` bigint(20) UNSIGNED NOT NULL,
  `matches_played` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `innings_batted` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_runs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `balls_faced` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `fifties` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `hundreds` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `sixes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `fours` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `strike_rate` double(8,2) NOT NULL DEFAULT 0.00,
  `average` double(8,2) NOT NULL DEFAULT 0.00,
  `innings_bowled` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `overs_bowled` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `runs_conceded` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `wickets` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `bowling_average` double(8,2) NOT NULL DEFAULT 0.00,
  `economy_rate` double(8,2) NOT NULL DEFAULT 0.00,
  `catches` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `runouts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `stumpings` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `player_team`
--

CREATE TABLE `player_team` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `player_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `player_team`
--

INSERT INTO `player_team` (`id`, `player_id`, `team_id`, `created_at`, `updated_at`) VALUES
(1, 22, 3, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(2, 52, 3, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(3, 78, 3, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(4, 91, 3, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(5, 16, 3, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(6, 39, 3, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(7, 75, 3, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(8, 84, 3, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(9, 51, 3, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(10, 8, 3, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(11, 82, 3, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(12, 29, 4, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(13, 40, 4, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(14, 24, 4, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(15, 33, 4, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(16, 37, 4, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(17, 85, 4, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(18, 64, 4, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(19, 7, 4, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(20, 72, 4, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(21, 20, 4, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(22, 30, 4, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(23, 47, 5, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(24, 34, 5, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(25, 5, 5, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(26, 83, 5, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(27, 86, 5, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(28, 18, 5, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(29, 3, 5, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(30, 31, 5, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(31, 54, 5, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(32, 6, 5, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(33, 65, 5, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(34, 28, 6, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(35, 59, 6, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(36, 71, 6, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(37, 68, 6, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(38, 57, 6, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(39, 4, 6, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(40, 62, 6, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(41, 92, 6, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(42, 80, 6, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(43, 58, 6, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(44, 81, 6, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(45, 60, 7, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(46, 35, 7, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(47, 15, 7, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(48, 43, 7, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(49, 45, 7, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(50, 23, 7, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(51, 32, 7, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(52, 17, 7, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(53, 77, 7, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(54, 61, 7, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(55, 11, 7, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(56, 63, 8, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(57, 21, 8, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(58, 36, 8, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(59, 42, 8, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(60, 53, 8, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(61, 12, 8, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(62, 9, 8, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(63, 76, 8, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(64, 74, 8, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(65, 44, 8, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(66, 67, 8, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(67, 55, 9, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(68, 73, 9, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(69, 19, 9, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(70, 66, 9, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(71, 88, 9, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(72, 41, 9, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(73, 89, 9, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(74, 14, 9, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(75, 27, 9, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(76, 90, 9, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(77, 26, 9, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(78, 49, 10, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(79, 50, 10, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(80, 10, 10, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(81, 48, 10, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(82, 87, 10, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(83, 13, 10, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(84, 79, 10, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(85, 46, 10, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(86, 69, 10, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(87, 70, 10, '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(88, 56, 10, '2025-07-10 14:10:27', '2025-07-10 14:10:27');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2025-07-06 21:44:52', '2025-07-06 21:44:52'),
(2, 'manager', 'web', '2025-07-07 12:40:08', '2025-07-07 12:40:08'),
(3, 'player', 'web', '2025-07-07 12:40:48', '2025-07-07 12:40:48');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1);

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `coach_name` varchar(255) DEFAULT NULL,
  `manager_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `slug`, `logo`, `coach_name`, `manager_name`, `description`, `created_at`, `updated_at`) VALUES
(3, 'Thunder Warriors', 'thunder-warriors', NULL, 'Coach 0', 'Manager 0', 'This is team Thunder Warriors', '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(4, 'Desert Hawks', 'desert-hawks', NULL, 'Coach 1', 'Manager 1', 'This is team Desert Hawks', '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(5, 'Ocean Blazers', 'ocean-blazers', NULL, 'Coach 2', 'Manager 2', 'This is team Ocean Blazers', '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(6, 'Sky Smashers', 'sky-smashers', NULL, 'Coach 3', 'Manager 3', 'This is team Sky Smashers', '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(7, 'Mountain Kings', 'mountain-kings', NULL, 'Coach 4', 'Manager 4', 'This is team Mountain Kings', '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(8, 'Valley Vipers', 'valley-vipers', NULL, 'Coach 5', 'Manager 5', 'This is team Valley Vipers', '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(9, 'Forest Rangers', 'forest-rangers', NULL, 'Coach 6', 'Manager 6', 'This is team Forest Rangers', '2025-07-10 14:10:27', '2025-07-10 14:10:27'),
(10, 'Storm Strikers', 'storm-strikers', NULL, 'Coach 7', 'Manager 7', 'This is team Storm Strikers', '2025-07-10 14:10:27', '2025-07-10 14:10:27');

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'upcoming',
  `format` enum('group','round-robin','knockout') NOT NULL DEFAULT 'round-robin',
  `has_knockout` tinyint(1) NOT NULL DEFAULT 1,
  `trophy_image` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `group_count` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`id`, `name`, `slug`, `location`, `description`, `start_date`, `end_date`, `status`, `format`, `has_knockout`, `trophy_image`, `logo`, `created_at`, `updated_at`, `group_count`) VALUES
(2, 'Friendly Tournament', 'friendly-tournament', 'RU Field', NULL, '2025-07-20', '2025-07-31', 'upcoming', 'group', 1, NULL, NULL, '2025-07-15 12:46:05', '2025-07-15 12:46:05', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tournament_groups`
--

CREATE TABLE `tournament_groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tournament_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tournament_groups`
--

INSERT INTO `tournament_groups` (`id`, `tournament_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 2, 'Group A', '2025-07-15 12:47:30', '2025-07-15 12:47:30'),
(2, 2, 'Group B', '2025-07-15 12:47:30', '2025-07-15 12:47:30');

-- --------------------------------------------------------

--
-- Table structure for table `tournament_group_teams`
--

CREATE TABLE `tournament_group_teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `group_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `tournament_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tournament_group_teams`
--

INSERT INTO `tournament_group_teams` (`id`, `group_id`, `team_id`, `tournament_id`, `created_at`, `updated_at`) VALUES
(1, 1, 10, 2, '2025-07-15 12:47:30', '2025-07-15 12:47:30'),
(2, 2, 4, 2, '2025-07-15 12:47:30', '2025-07-15 12:47:30'),
(3, 1, 6, 2, '2025-07-15 12:47:30', '2025-07-15 12:47:30'),
(4, 2, 9, 2, '2025-07-15 12:47:30', '2025-07-15 12:47:30'),
(5, 1, 7, 2, '2025-07-15 12:47:30', '2025-07-15 12:47:30'),
(6, 2, 5, 2, '2025-07-15 12:47:30', '2025-07-15 12:47:30'),
(7, 1, 8, 2, '2025-07-15 12:47:30', '2025-07-15 12:47:30'),
(8, 2, 3, 2, '2025-07-15 12:47:30', '2025-07-15 12:47:30');

-- --------------------------------------------------------

--
-- Table structure for table `tournament_player_stats`
--

CREATE TABLE `tournament_player_stats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tournament_id` bigint(20) UNSIGNED NOT NULL,
  `player_id` bigint(20) UNSIGNED NOT NULL,
  `matches_played` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `innings_batted` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_runs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `balls_faced` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `fifties` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `hundreds` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `sixes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `fours` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `strike_rate` double(8,2) NOT NULL DEFAULT 0.00,
  `average` double(8,2) NOT NULL DEFAULT 0.00,
  `innings_bowled` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `overs_bowled` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `runs_conceded` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `wickets` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `bowling_average` double(8,2) NOT NULL DEFAULT 0.00,
  `economy_rate` double(8,2) NOT NULL DEFAULT 0.00,
  `catches` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `runouts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `stumpings` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournament_team_stats`
--

CREATE TABLE `tournament_team_stats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tournament_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `matches_played` int(11) NOT NULL DEFAULT 0,
  `wins` int(11) NOT NULL DEFAULT 0,
  `losses` int(11) NOT NULL DEFAULT 0,
  `draws` int(11) NOT NULL DEFAULT 0,
  `points` int(11) NOT NULL DEFAULT 0,
  `nrr` double(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `nickname` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `blood_group` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `visible_pass` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `image` varchar(255) DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `national_id` varchar(255) DEFAULT NULL,
  `religion` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `nickname`, `username`, `phone`, `blood_group`, `email`, `email_verified_at`, `password`, `visible_pass`, `status`, `image`, `signature`, `national_id`, `religion`, `gender`, `date_of_birth`, `address`, `role_id`, `deleted_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(2, 'Md. Fahim Tayebee', 'FTayebee', 'trTayebee', '01765649100', 'AB+', 'fahim.tayebee@gmail.com', NULL, '$2y$10$JeD/chj0MlQQ.WZeXsdt/OySFjbp5o9F.SX1s8.INltcu1Bpjd1tm', 'password', 'active', 'user_1751921240.jpeg', NULL, '6458629992', 'islam', 'male', '2000-10-01', 'H-36, Begum Rokeya Road, Hatem khan', 1, NULL, NULL, '2025-07-07 14:47:21', '2025-07-07 14:47:21'),
(7, 'Player 1', 'P1', 'player1', '01700000001', 'A+', 'player1@example.com', NULL, '$2y$10$JeD/chj0MlQQ.WZeXsdt/OySFjbp5o9F.SX1s8.INltcu1Bpjd1tm', 'password', 'active', NULL, NULL, '12345678901', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:53', '2025-07-10 14:03:53'),
(8, 'Player 2', 'P2', 'player2', '01700000002', 'A+', 'player2@example.com', NULL, '$2y$10$8T9XJJTUZt76TnDc0ANkyue6Ct986rM0cklYHVNG.kUyPMr/CJkjy', 'password', 'active', NULL, NULL, '12345678902', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:53', '2025-07-10 14:03:53'),
(9, 'Player 3', 'P3', 'player3', '01700000003', 'A+', 'player3@example.com', NULL, '$2y$10$ARhSCayJqNx77irR8VUF.eDqIzJZ08kxQCdzo.MlwpBoK3IyPJf/u', 'password', 'active', NULL, NULL, '12345678903', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:53', '2025-07-10 14:03:53'),
(10, 'Player 4', 'P4', 'player4', '01700000004', 'A+', 'player4@example.com', NULL, '$2y$10$tOZ3aKzd04rua1DcKJwGNeZouPRb/BPCOY4qYZIzaMQK0LFx3/iD6', 'password', 'active', NULL, NULL, '12345678904', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(11, 'Player 5', 'P5', 'player5', '01700000005', 'A+', 'player5@example.com', NULL, '$2y$10$q9SFi3DzfSquyV5m4JfoEuoUkYs/qI/lE5VVsscAhOtLQ1LZHMHKe', 'password', 'active', NULL, NULL, '12345678905', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(12, 'Player 6', 'P6', 'player6', '01700000006', 'A+', 'player6@example.com', NULL, '$2y$10$gSyl7FcYHXREYsRsuutfCOT8bS0Qhcsx..HhtgOu8oOqaoPi24YUq', 'password', 'active', NULL, NULL, '12345678906', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(13, 'Player 7', 'P7', 'player7', '01700000007', 'A+', 'player7@example.com', NULL, '$2y$10$MsGwzZxAbcog/mfZm.1g6e46AHqvVDFbYnkkbmoET5TXWE.ydckHq', 'password', 'active', NULL, NULL, '12345678907', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(14, 'Player 8', 'P8', 'player8', '01700000008', 'A+', 'player8@example.com', NULL, '$2y$10$VCWng1TsltHwYGB06el5j.5toIky/1XvfuX9W0nlb6yGq91Lu7utC', 'password', 'active', NULL, NULL, '12345678908', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(15, 'Player 9', 'P9', 'player9', '01700000009', 'A+', 'player9@example.com', NULL, '$2y$10$Xaoc5eCcAfnmN8Zr29l9..uXrCyhGLuBurkB/nMugwCD2O6HjizLq', 'password', 'active', NULL, NULL, '12345678909', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(16, 'Player 10', 'P10', 'player10', '017000000010', 'A+', 'player10@example.com', NULL, '$2y$10$dkAe4cPgSPIaykv0izOXP.qdSPwTU1LsJHE9olUyXBosObZdIM17e', 'password', 'active', NULL, NULL, '123456789010', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(17, 'Player 11', 'P11', 'player11', '017000000011', 'A+', 'player11@example.com', NULL, '$2y$10$tYplcWSlqEAVBTxVsZ7cNeccBOzX03bHlBSbUkbBejGMay3S9vCNa', 'password', 'active', NULL, NULL, '123456789011', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(18, 'Player 12', 'P12', 'player12', '017000000012', 'A+', 'player12@example.com', NULL, '$2y$10$dNTGCd24JTuQ9QfNcc.F/.Ck153V33x.SKf9Q4Vf8q3shbrQu/Jnm', 'password', 'active', NULL, NULL, '123456789012', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(19, 'Player 13', 'P13', 'player13', '017000000013', 'A+', 'player13@example.com', NULL, '$2y$10$iskxt0MnnEwbuhzuufSx9.QIeb4dZe6V2fCbEchbnLjGz16snjJ.C', 'password', 'active', NULL, NULL, '123456789013', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(20, 'Player 14', 'P14', 'player14', '017000000014', 'A+', 'player14@example.com', NULL, '$2y$10$4mQsPI0yIeSlA6FrYDJ8x.xBJfwCzR1cX.upfmb..dmENcZ4Yb4la', 'password', 'active', NULL, NULL, '123456789014', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(21, 'Player 15', 'P15', 'player15', '017000000015', 'A+', 'player15@example.com', NULL, '$2y$10$zTs.lWxk.vKsI0Jz5uY6ie5FrX1NlUUeJonAGwqPhS1.bI2X36sPO', 'password', 'active', NULL, NULL, '123456789015', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:54', '2025-07-10 14:03:54'),
(22, 'Player 16', 'P16', 'player16', '017000000016', 'A+', 'player16@example.com', NULL, '$2y$10$dyxHbrLJR.5uy7cfWROIEOhwvsL1S7oHCVuzEVeWFuBaiwIrj9Wy6', 'password', 'active', NULL, NULL, '123456789016', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(23, 'Player 17', 'P17', 'player17', '017000000017', 'A+', 'player17@example.com', NULL, '$2y$10$5r/wujso40lPGWAf9QLhlesF6e8P.aP.pajLRklk1oG9CLqlll78C', 'password', 'active', NULL, NULL, '123456789017', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(24, 'Player 18', 'P18', 'player18', '017000000018', 'A+', 'player18@example.com', NULL, '$2y$10$AYyjEhcobydxQIBELuEAPO9OCA6iGGjs8B93SjzzwcLKuRXbZ1y52', 'password', 'active', NULL, NULL, '123456789018', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(25, 'Player 19', 'P19', 'player19', '017000000019', 'A+', 'player19@example.com', NULL, '$2y$10$TIKXaBZLKy/TSHcWY.9pgu10smMAjKxG1anwYnXDDSGdpGVQSkyKC', 'password', 'active', NULL, NULL, '123456789019', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(26, 'Player 20', 'P20', 'player20', '017000000020', 'A+', 'player20@example.com', NULL, '$2y$10$kNJAlVfodOW90PS0/OTB2ecn2ya.YsePxM1DSiXHBMT2/HzRFRGCy', 'password', 'active', NULL, NULL, '123456789020', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(27, 'Player 21', 'P21', 'player21', '017000000021', 'A+', 'player21@example.com', NULL, '$2y$10$Xh3DDwcq2vLKWB.gKO4X9eia3bhpkciR..O5hJ05.xbAEoWfUF.Xi', 'password', 'active', NULL, NULL, '123456789021', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(28, 'Player 22', 'P22', 'player22', '017000000022', 'A+', 'player22@example.com', NULL, '$2y$10$NmIpPKondEDnoOEmbe4A5OR7VZK6Yw314I0Z42tRxErLRIEjtUMoy', 'password', 'active', NULL, NULL, '123456789022', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(29, 'Player 23', 'P23', 'player23', '017000000023', 'A+', 'player23@example.com', NULL, '$2y$10$FchZ9nh6fZAIewzcsmd0aecWG3lyWlMwZFCPV.O0Polf2dTc2yV4W', 'password', 'active', NULL, NULL, '123456789023', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(30, 'Player 24', 'P24', 'player24', '017000000024', 'A+', 'player24@example.com', NULL, '$2y$10$VzblTGy5vG4xiR9DyBkoj.HLOSQWLOrGUilYdfD5ceb3p65RDK95C', 'password', 'active', NULL, NULL, '123456789024', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(31, 'Player 25', 'P25', 'player25', '017000000025', 'A+', 'player25@example.com', NULL, '$2y$10$FMlLFKGHi2nE49qYK629m.9u4H/ONFtwXvbpppkNQJG.V6SGuVQHe', 'password', 'active', NULL, NULL, '123456789025', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(32, 'Player 26', 'P26', 'player26', '017000000026', 'A+', 'player26@example.com', NULL, '$2y$10$bqh5vFSIincvTzOePundkefeMsD/xAr2KIFy5nB6.eSGNp7yHawlG', 'password', 'active', NULL, NULL, '123456789026', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:55', '2025-07-10 14:03:55'),
(33, 'Player 27', 'P27', 'player27', '017000000027', 'A+', 'player27@example.com', NULL, '$2y$10$uvFXW55hDpHxs9mywEifu.sbYiHRJBwL8O1OwW.vdnSsd8BXD5OCS', 'password', 'active', NULL, NULL, '123456789027', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(34, 'Player 28', 'P28', 'player28', '017000000028', 'A+', 'player28@example.com', NULL, '$2y$10$Ys1U8jlLTDsAAzsoP.n1B.bmBjkCkNPOsyJVXqGLj8ow1g7gDsNMe', 'password', 'active', NULL, NULL, '123456789028', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(35, 'Player 29', 'P29', 'player29', '017000000029', 'A+', 'player29@example.com', NULL, '$2y$10$4cpcwuaIzYuG2OPhfVaJ3eYZtbUN2wMVeFR39Y9247qD6fEyIMZaa', 'password', 'active', NULL, NULL, '123456789029', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(36, 'Player 30', 'P30', 'player30', '017000000030', 'A+', 'player30@example.com', NULL, '$2y$10$Yl.WjrHk2lnzp2k/OurbeOvY7aHPQlgVaSdZcXRBPBW4uAM0R.p.2', 'password', 'active', NULL, NULL, '123456789030', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(37, 'Player 31', 'P31', 'player31', '017000000031', 'A+', 'player31@example.com', NULL, '$2y$10$uS90GxuNU5rSCVI7filUL.Nh09BhnrKp5.YSZb2S5CuhPV2LsQmBi', 'password', 'active', NULL, NULL, '123456789031', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(38, 'Player 32', 'P32', 'player32', '017000000032', 'A+', 'player32@example.com', NULL, '$2y$10$Jvu/JaK4hvXmef/M1Q0Ow.fu.ZF6XpBTSkjczKsKdmNn8/HCZzU6S', 'password', 'active', NULL, NULL, '123456789032', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(39, 'Player 33', 'P33', 'player33', '017000000033', 'A+', 'player33@example.com', NULL, '$2y$10$Yv2d8Dkb5I73IlTfCAHRAOLU6WDEoJEBjyvChJ6xsggMLtGWd4Thi', 'password', 'active', NULL, NULL, '123456789033', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(40, 'Player 34', 'P34', 'player34', '017000000034', 'A+', 'player34@example.com', NULL, '$2y$10$wV640GJQPYZPT5bm.vrMHuOX/Bt.7nscK3SaC2qxRZzUx35raN8WS', 'password', 'active', NULL, NULL, '123456789034', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(41, 'Player 35', 'P35', 'player35', '017000000035', 'A+', 'player35@example.com', NULL, '$2y$10$NmUatVvIPyBf/6RAtBvnGugkX9Inddjez2BOcW5FHJf8T8.a.Unpe', 'password', 'active', NULL, NULL, '123456789035', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(42, 'Player 36', 'P36', 'player36', '017000000036', 'A+', 'player36@example.com', NULL, '$2y$10$mrrYOT2pLwcZMYkfHGQH0OwCvX3lbhSMoKI45JBcJPdOZYmidEGBO', 'password', 'active', NULL, NULL, '123456789036', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(43, 'Player 37', 'P37', 'player37', '017000000037', 'A+', 'player37@example.com', NULL, '$2y$10$pRnaPzQyA782BlI6e.tW6OjibHCopzmeuepgpyxXESRXBoNkPqRqS', 'password', 'active', NULL, NULL, '123456789037', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(44, 'Player 38', 'P38', 'player38', '017000000038', 'A+', 'player38@example.com', NULL, '$2y$10$E/Fk07sTWIuO2hx6fZoTMuJAzQ9ghJ8lIAB9mFdQzB/HCoGNHcRua', 'password', 'active', NULL, NULL, '123456789038', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(45, 'Player 39', 'P39', 'player39', '017000000039', 'A+', 'player39@example.com', NULL, '$2y$10$6ezUg3r/g7RE35R0vSY0vOW96IllzA5vSyAc288dhgRPKVnECj9oa', 'password', 'active', NULL, NULL, '123456789039', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(46, 'Player 40', 'P40', 'player40', '017000000040', 'A+', 'player40@example.com', NULL, '$2y$10$52GqtqMHP7IfOnlUWbZGmenhu0shF.i6jAWhwNF4oMTmSEj4/HtOC', 'password', 'active', NULL, NULL, '123456789040', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:56', '2025-07-10 14:03:56'),
(47, 'Player 41', 'P41', 'player41', '017000000041', 'A+', 'player41@example.com', NULL, '$2y$10$NrcrOT72uj.4J1U2ozyxle86SDlFbJp1puqH7lG6cqMHp77ifFB0G', 'password', 'active', NULL, NULL, '123456789041', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(48, 'Player 42', 'P42', 'player42', '017000000042', 'A+', 'player42@example.com', NULL, '$2y$10$rXTzcExHhZ9s11E.4KdiP.Sb8kmUF1nNI2AR7Ik9AqgWXeUsXM3Uu', 'password', 'active', NULL, NULL, '123456789042', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(49, 'Player 43', 'P43', 'player43', '017000000043', 'A+', 'player43@example.com', NULL, '$2y$10$BYfqTvSR87Nf2E1A4Q9plOTxzUVwbtpdHxniQbRtIgLpYewI2m2KW', 'password', 'active', NULL, NULL, '123456789043', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(50, 'Player 44', 'P44', 'player44', '017000000044', 'A+', 'player44@example.com', NULL, '$2y$10$R6MnFotM1DTei0fWD9qKYejAj0385pjgWhZBP9bzrv6OPJ.qu1B3W', 'password', 'active', NULL, NULL, '123456789044', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(51, 'Player 45', 'P45', 'player45', '017000000045', 'A+', 'player45@example.com', NULL, '$2y$10$M7nLTOM4fNQgrRasNJnr1.eEf0Yn1ZGxSLaDyE9XYHLHTCqsfwZH6', 'password', 'active', NULL, NULL, '123456789045', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(52, 'Player 46', 'P46', 'player46', '017000000046', 'A+', 'player46@example.com', NULL, '$2y$10$J/PBDcaHDQ.1V2oLZTStq.b46qkAWUNaCB4tR9n9IW4xeJ0tsLc2q', 'password', 'active', NULL, NULL, '123456789046', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(53, 'Player 47', 'P47', 'player47', '017000000047', 'A+', 'player47@example.com', NULL, '$2y$10$vZp9JBdJRSGFM6lqy5hgAeUZf7DjjFGvAkoGdV8SSmLAsWYj1F2V.', 'password', 'active', NULL, NULL, '123456789047', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(54, 'Player 48', 'P48', 'player48', '017000000048', 'A+', 'player48@example.com', NULL, '$2y$10$jFyXvzbha46Q0PSjVUC0EO3HxZcZvHUljxo.9hGL9CNX4m5W/X94O', 'password', 'active', NULL, NULL, '123456789048', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(55, 'Player 49', 'P49', 'player49', '017000000049', 'A+', 'player49@example.com', NULL, '$2y$10$y6h/kbKQfqfiOVngP2PcLexkz1Gg7G8pJcGrKMK476u8TOjAAPUkG', 'password', 'active', NULL, NULL, '123456789049', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(56, 'Player 50', 'P50', 'player50', '017000000050', 'A+', 'player50@example.com', NULL, '$2y$10$mTa8FNMt3QtwXMwRKib3JuYzkp4i4xRnF9/Fk3L.q4Hw34FQr6tle', 'password', 'active', NULL, NULL, '123456789050', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(57, 'Player 51', 'P51', 'player51', '017000000051', 'A+', 'player51@example.com', NULL, '$2y$10$ZLR70nGxiNNLIoqfHkrvvOQ5WCisZ6E8N7E/taEb6WtRqx88Xyk8C', 'password', 'active', NULL, NULL, '123456789051', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(58, 'Player 52', 'P52', 'player52', '017000000052', 'A+', 'player52@example.com', NULL, '$2y$10$mCqAkM5gziTfboZeT9BnHOCHNjaONqx/ImlqQ/nh/VkQxrffdfF.6', 'password', 'active', NULL, NULL, '123456789052', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(59, 'Player 53', 'P53', 'player53', '017000000053', 'A+', 'player53@example.com', NULL, '$2y$10$AZa2MwRzuY7LQe/Q9fvfm.Od2C37FHOw6RV0oLPmfQ6aFxhZ3ZgEu', 'password', 'active', NULL, NULL, '123456789053', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:57', '2025-07-10 14:03:57'),
(60, 'Player 54', 'P54', 'player54', '017000000054', 'A+', 'player54@example.com', NULL, '$2y$10$Glk/TkfIgy8hZMk3l414heC2.kSTvOUyJYoZ6y1nhzAkpiiyUOuiO', 'password', 'active', NULL, NULL, '123456789054', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(61, 'Player 55', 'P55', 'player55', '017000000055', 'A+', 'player55@example.com', NULL, '$2y$10$qDoyfMbMSXbHjis4tdqbfuV09OWOaV76Qgdwp97gS9LqZojBSR06W', 'password', 'active', NULL, NULL, '123456789055', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(62, 'Player 56', 'P56', 'player56', '017000000056', 'A+', 'player56@example.com', NULL, '$2y$10$q0naXJtkPFVVh6M4hV6i5O9M/mQcrv5g98btwjrfLxK6IuqXZ.pXW', 'password', 'active', NULL, NULL, '123456789056', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(63, 'Player 57', 'P57', 'player57', '017000000057', 'A+', 'player57@example.com', NULL, '$2y$10$MfDxxzpTsBhFYh0tSCB1MuX5Zttg401wWIZ8LVCoHZFnKrPunyCli', 'password', 'active', NULL, NULL, '123456789057', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(64, 'Player 58', 'P58', 'player58', '017000000058', 'A+', 'player58@example.com', NULL, '$2y$10$hoKJanV0TV.yORur812ekuE33o7auskpL7AIxTvEj39.mVHH1sYJW', 'password', 'active', NULL, NULL, '123456789058', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(65, 'Player 59', 'P59', 'player59', '017000000059', 'A+', 'player59@example.com', NULL, '$2y$10$eqLKXecHLuS.J9e6Zpn8SuUW8qCzXJ4XQuRtD7hJs6yqmAnXkwhpK', 'password', 'active', NULL, NULL, '123456789059', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(66, 'Player 60', 'P60', 'player60', '017000000060', 'A+', 'player60@example.com', NULL, '$2y$10$XhJ.MN7MccoaEsBzj3h8E.8NCmjQNFqNA5hzf/uEXjVGgJEuyC7Ou', 'password', 'active', NULL, NULL, '123456789060', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(67, 'Player 61', 'P61', 'player61', '017000000061', 'A+', 'player61@example.com', NULL, '$2y$10$07eQNgLlrD9T6H41o7FIDO04mogM2ChAigYWBbGVSKUZt7FVaHE3e', 'password', 'active', NULL, NULL, '123456789061', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(68, 'Player 62', 'P62', 'player62', '017000000062', 'A+', 'player62@example.com', NULL, '$2y$10$5ggCIiUZBugMTdv6lwlXM.kg88BLb7zvi0v0g.dqA5JfFLD4Lregq', 'password', 'active', NULL, NULL, '123456789062', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(69, 'Player 63', 'P63', 'player63', '017000000063', 'A+', 'player63@example.com', NULL, '$2y$10$lgoqskf7oJv7/I8YfYJse.GCBKeE71Sjn8ehMIx5b8wZHYQRsU65O', 'password', 'active', NULL, NULL, '123456789063', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(70, 'Player 64', 'P64', 'player64', '017000000064', 'A+', 'player64@example.com', NULL, '$2y$10$UteWiexWFMd/BqLeGiV4VOA4hu2gqB4uKHE6APviCG1MeiXx0Zebq', 'password', 'active', NULL, NULL, '123456789064', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(71, 'Player 65', 'P65', 'player65', '017000000065', 'A+', 'player65@example.com', NULL, '$2y$10$XqUJxyt5cxd1S1tLD5viWefbSo8fpDrk2KCPxomXAVzcwclWsAaou', 'password', 'active', NULL, NULL, '123456789065', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(72, 'Player 66', 'P66', 'player66', '017000000066', 'A+', 'player66@example.com', NULL, '$2y$10$tBovsoJ14p60R8Hm17dkO.cxlDyRw91F/78qEdpHNiMraLUhElBki', 'password', 'active', NULL, NULL, '123456789066', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:58', '2025-07-10 14:03:58'),
(73, 'Player 67', 'P67', 'player67', '017000000067', 'A+', 'player67@example.com', NULL, '$2y$10$7keC6u8J24lM.SnvRJn.KuPy1NhqUMHhqyXbqhKmf2YQ8ICIczvBS', 'password', 'active', NULL, NULL, '123456789067', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(74, 'Player 68', 'P68', 'player68', '017000000068', 'A+', 'player68@example.com', NULL, '$2y$10$GFnH0EY6R.kp4Xbr11NlUucv/XCCKsFFPB6OT0haGPcqzU8ppmUb2', 'password', 'active', NULL, NULL, '123456789068', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(75, 'Player 69', 'P69', 'player69', '017000000069', 'A+', 'player69@example.com', NULL, '$2y$10$TKWS6r3Ue0X7ik.kOhGmkOtmusd1i0RiKR8uzRtBtzi9GLWeyecaC', 'password', 'active', NULL, NULL, '123456789069', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(76, 'Player 70', 'P70', 'player70', '017000000070', 'A+', 'player70@example.com', NULL, '$2y$10$0qWajlVH4e5qMLJJtmvwVuo9zTTDoGWNLg/6UoX.lP4DY2c1z.nMa', 'password', 'active', NULL, NULL, '123456789070', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(77, 'Player 71', 'P71', 'player71', '017000000071', 'A+', 'player71@example.com', NULL, '$2y$10$vBbYdihR/Udkra0oT1Ew5.etSB8IK2qArOCwEHC8IR9o3AcmyMRvS', 'password', 'active', NULL, NULL, '123456789071', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(78, 'Player 72', 'P72', 'player72', '017000000072', 'A+', 'player72@example.com', NULL, '$2y$10$Zjihx8kTnhyL.YGpwdADGehe1ETtMIYk28va9ESoENExIEFFwcwh.', 'password', 'active', NULL, NULL, '123456789072', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(79, 'Player 73', 'P73', 'player73', '017000000073', 'A+', 'player73@example.com', NULL, '$2y$10$lCHuNkvMrZ1sa1KUB6vWs.xbRzhB5UaS8RJKl5wJGoTK6wVFSocr2', 'password', 'active', NULL, NULL, '123456789073', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(80, 'Player 74', 'P74', 'player74', '017000000074', 'A+', 'player74@example.com', NULL, '$2y$10$z6ooAonWEI0494An/Ghcsu/Z3EmkxidO0ynjq3h6vwC9XmufBKOzG', 'password', 'active', NULL, NULL, '123456789074', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(81, 'Player 75', 'P75', 'player75', '017000000075', 'A+', 'player75@example.com', NULL, '$2y$10$Ioa10Vju1ohzGFsbUY5D/.AHJqVBTDh3qS777dz.xtMzaAMhEGxMy', 'password', 'active', NULL, NULL, '123456789075', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(82, 'Player 76', 'P76', 'player76', '017000000076', 'A+', 'player76@example.com', NULL, '$2y$10$KEOAOvUv7pG2xszHx9YNueq4dHgihYiFr1.eQtoMgn6xz8rWSlZMi', 'password', 'active', NULL, NULL, '123456789076', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(83, 'Player 77', 'P77', 'player77', '017000000077', 'A+', 'player77@example.com', NULL, '$2y$10$h5rNd47wnqlkMgW/u5jk6euqwjv5cubfkxssVT91CZwwWrWPT01ru', 'password', 'active', NULL, NULL, '123456789077', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(84, 'Player 78', 'P78', 'player78', '017000000078', 'A+', 'player78@example.com', NULL, '$2y$10$5godV32ns9oE482oZcDN8uBhr/c2JlMfRcBtfUC7x4CanW1RsVkb2', 'password', 'active', NULL, NULL, '123456789078', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(85, 'Player 79', 'P79', 'player79', '017000000079', 'A+', 'player79@example.com', NULL, '$2y$10$rg1LrN6lx4DYrzPBY5NLc.FJcRhgSl6rhvtKlwZrBQ7I5t/kr2w5.', 'password', 'active', NULL, NULL, '123456789079', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:03:59', '2025-07-10 14:03:59'),
(86, 'Player 80', 'P80', 'player80', '017000000080', 'A+', 'player80@example.com', NULL, '$2y$10$hYzwAtRpbx2K4EDl/znFxOT3RBD.9CYkKY7gbVAiJ9RAkfvJ5G9PW', 'password', 'active', NULL, NULL, '123456789080', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(87, 'Player 81', 'P81', 'player81', '017000000081', 'A+', 'player81@example.com', NULL, '$2y$10$C9c9AvJceHBBPrriwP3RweNsIGhQAG04wvSYWQ0ihPSdBA4RVhmgO', 'password', 'active', NULL, NULL, '123456789081', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(88, 'Player 82', 'P82', 'player82', '017000000082', 'A+', 'player82@example.com', NULL, '$2y$10$UKoPrPQHNNIfMTQtiS9sue7qlib9ffNrIkuWH37jf8uqWIxHesPZC', 'password', 'active', NULL, NULL, '123456789082', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(89, 'Player 83', 'P83', 'player83', '017000000083', 'A+', 'player83@example.com', NULL, '$2y$10$Hr2GH9udVl/RSOwNIKw9qOOu851iybuFI2Fg0hBDNueNSyTGw.GNi', 'password', 'active', NULL, NULL, '123456789083', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(90, 'Player 84', 'P84', 'player84', '017000000084', 'A+', 'player84@example.com', NULL, '$2y$10$KOPmCmMphmxOKI87Nl.GA.v46i7lREVmsdBPZR2qjmaHLyxOm3koK', 'password', 'active', NULL, NULL, '123456789084', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(91, 'Player 85', 'P85', 'player85', '017000000085', 'A+', 'player85@example.com', NULL, '$2y$10$6sqKE0dqq0OpSpg.c5l9WOfCLwkKEMAGw5d6M8w8Zag/x.peGTUF2', 'password', 'active', NULL, NULL, '123456789085', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(92, 'Player 86', 'P86', 'player86', '017000000086', 'A+', 'player86@example.com', NULL, '$2y$10$s/0QnC.p88IsK1HrBQgB6u5K6cl4m0gRHm4RTCLYcB/ljZYI.7wzi', 'password', 'active', NULL, NULL, '123456789086', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(93, 'Player 87', 'P87', 'player87', '017000000087', 'A+', 'player87@example.com', NULL, '$2y$10$tbsiV.XOvnr0MNS1NO.FfuaAtg/kqfOZpIvNFFOmUsLzi0TT8i9f.', 'password', 'active', NULL, NULL, '123456789087', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(94, 'Player 88', 'P88', 'player88', '017000000088', 'A+', 'player88@example.com', NULL, '$2y$10$xrq7KTolEfQqzugqOjkN1./ZusEdAOfA.HceT7B8rpqHeHGy18sOy', 'password', 'active', NULL, NULL, '123456789088', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(95, 'Player 89', 'P89', 'player89', '017000000089', 'A+', 'player89@example.com', NULL, '$2y$10$/ZUvRl54y6xvX7v.f8kpt.jIajkCaudaWziJqvXfPUethn3rD29qu', 'password', 'active', NULL, NULL, '123456789089', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00'),
(96, 'Player 90', 'P90', 'player90', '017000000090', 'A+', 'player90@example.com', NULL, '$2y$10$.efw03C9ijmDQq4LVVQf6uBxbrQsakmsN8AS8e9kslOxRNpMWKuG2', 'password', 'active', NULL, NULL, '123456789090', NULL, NULL, NULL, NULL, 3, NULL, NULL, '2025-07-10 14:04:00', '2025-07-10 14:04:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cricket_matches`
--
ALTER TABLE `cricket_matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cricket_matches_team_a_id_foreign` (`team_a_id`),
  ADD KEY `cricket_matches_team_b_id_foreign` (`team_b_id`),
  ADD KEY `cricket_matches_tournament_id_foreign` (`tournament_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `fall_of_wickets`
--
ALTER TABLE `fall_of_wickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fall_of_wickets_match_id_foreign` (`match_id`),
  ADD KEY `fall_of_wickets_team_id_foreign` (`team_id`);

--
-- Indexes for table `match_deliveries`
--
ALTER TABLE `match_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_deliveries_match_id_foreign` (`match_id`),
  ADD KEY `match_deliveries_batsman_id_foreign` (`batsman_id`),
  ADD KEY `match_deliveries_bowler_id_foreign` (`bowler_id`),
  ADD KEY `match_deliveries_non_striker_id_foreign` (`non_striker_id`),
  ADD KEY `match_deliveries_wicket_player_id_foreign` (`wicket_player_id`),
  ADD KEY `match_deliveries_fielder_id_foreign` (`fielder_id`);

--
-- Indexes for table `match_players`
--
ALTER TABLE `match_players`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_players_match_id_foreign` (`match_id`),
  ADD KEY `match_players_player_id_foreign` (`player_id`);

--
-- Indexes for table `match_score_boards`
--
ALTER TABLE `match_score_boards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_score_boards_match_id_foreign` (`match_id`),
  ADD KEY `match_score_boards_team_id_foreign` (`team_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `partnerships`
--
ALTER TABLE `partnerships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partnerships_match_id_foreign` (`match_id`),
  ADD KEY `partnerships_team_id_foreign` (`team_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `players_user_id_unique` (`user_id`);

--
-- Indexes for table `player_statistics`
--
ALTER TABLE `player_statistics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `player_statistics_player_id_unique` (`player_id`);

--
-- Indexes for table `player_team`
--
ALTER TABLE `player_team`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player_team_player_id_foreign` (`player_id`),
  ADD KEY `player_team_team_id_foreign` (`team_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tournaments_slug_unique` (`slug`);

--
-- Indexes for table `tournament_groups`
--
ALTER TABLE `tournament_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_groups_tournament_id_foreign` (`tournament_id`);

--
-- Indexes for table `tournament_group_teams`
--
ALTER TABLE `tournament_group_teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_group_teams_group_id_foreign` (`group_id`),
  ADD KEY `tournament_group_teams_team_id_foreign` (`team_id`),
  ADD KEY `tournament_group_teams_tournament_id_foreign` (`tournament_id`);

--
-- Indexes for table `tournament_player_stats`
--
ALTER TABLE `tournament_player_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tournament_player_stats_tournament_id_player_id_unique` (`tournament_id`,`player_id`),
  ADD KEY `tournament_player_stats_player_id_foreign` (`player_id`);

--
-- Indexes for table `tournament_team_stats`
--
ALTER TABLE `tournament_team_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_team_stats_tournament_id_foreign` (`tournament_id`),
  ADD KEY `tournament_team_stats_team_id_foreign` (`team_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_id_foreign` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cricket_matches`
--
ALTER TABLE `cricket_matches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fall_of_wickets`
--
ALTER TABLE `fall_of_wickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `match_deliveries`
--
ALTER TABLE `match_deliveries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `match_players`
--
ALTER TABLE `match_players`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `match_score_boards`
--
ALTER TABLE `match_score_boards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `partnerships`
--
ALTER TABLE `partnerships`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `player_statistics`
--
ALTER TABLE `player_statistics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_team`
--
ALTER TABLE `player_team`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tournament_groups`
--
ALTER TABLE `tournament_groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tournament_group_teams`
--
ALTER TABLE `tournament_group_teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tournament_player_stats`
--
ALTER TABLE `tournament_player_stats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tournament_team_stats`
--
ALTER TABLE `tournament_team_stats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cricket_matches`
--
ALTER TABLE `cricket_matches`
  ADD CONSTRAINT `cricket_matches_team_a_id_foreign` FOREIGN KEY (`team_a_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cricket_matches_team_b_id_foreign` FOREIGN KEY (`team_b_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cricket_matches_tournament_id_foreign` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fall_of_wickets`
--
ALTER TABLE `fall_of_wickets`
  ADD CONSTRAINT `fall_of_wickets_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fall_of_wickets_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `match_deliveries`
--
ALTER TABLE `match_deliveries`
  ADD CONSTRAINT `match_deliveries_batsman_id_foreign` FOREIGN KEY (`batsman_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `match_deliveries_bowler_id_foreign` FOREIGN KEY (`bowler_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `match_deliveries_fielder_id_foreign` FOREIGN KEY (`fielder_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `match_deliveries_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `match_deliveries_non_striker_id_foreign` FOREIGN KEY (`non_striker_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `match_deliveries_wicket_player_id_foreign` FOREIGN KEY (`wicket_player_id`) REFERENCES `players` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `match_players`
--
ALTER TABLE `match_players`
  ADD CONSTRAINT `match_players_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `match_players_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `match_score_boards`
--
ALTER TABLE `match_score_boards`
  ADD CONSTRAINT `match_score_boards_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `match_score_boards_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `partnerships`
--
ALTER TABLE `partnerships`
  ADD CONSTRAINT `partnerships_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partnerships_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `player_statistics`
--
ALTER TABLE `player_statistics`
  ADD CONSTRAINT `player_statistics_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `player_team`
--
ALTER TABLE `player_team`
  ADD CONSTRAINT `player_team_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `player_team_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournament_groups`
--
ALTER TABLE `tournament_groups`
  ADD CONSTRAINT `tournament_groups_tournament_id_foreign` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournament_group_teams`
--
ALTER TABLE `tournament_group_teams`
  ADD CONSTRAINT `tournament_group_teams_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `tournament_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_group_teams_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_group_teams_tournament_id_foreign` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournament_player_stats`
--
ALTER TABLE `tournament_player_stats`
  ADD CONSTRAINT `tournament_player_stats_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_player_stats_tournament_id_foreign` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournament_team_stats`
--
ALTER TABLE `tournament_team_stats`
  ADD CONSTRAINT `tournament_team_stats_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_team_stats_tournament_id_foreign` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
