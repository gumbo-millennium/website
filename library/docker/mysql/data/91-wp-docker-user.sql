-- phpMyAdmin SQL Dump
-- version 4.0.9
-- http://www.phpmyadmin.net
--
-- Machine: 127.20.20.1
-- Genereertijd: 12 okt 2018 om 14:22
-- Serverversie: 5.6.39
-- PHP-versie: 7.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databank: `wordpress`
--

USE wordpress;

-- --------------------------------------------------------

--
-- Gegevens worden uitgevoerd voor tabel `wp_users`
--

INSERT INTO `wp_users` (
    `user_login`,
    `user_pass`,
    `user_nicename`,
    `user_email`,
    `display_name`
) VALUES (
    'docker',
    '$P$BxewYV1kxQSnYw8AlLbb8HlOvlvHhM/',
    'docker',
    'docker@example.com',
    'Docker'
);

-- --------------------------------------------------------

SET @docker_user_id = LAST_INSERT_ID();

-- --------------------------------------------------------

--
-- Gegevens worden uitgevoerd voor tabel `wp_usermeta`
--

INSERT INTO `wp_usermeta` (
    `user_id`,
    `meta_key`,
    `meta_value`
) VALUES
    (@docker_user_id, 'nickname', 'Docker'),
    (@docker_user_id, 'first_name', ''),
    (@docker_user_id, 'last_name', 'Docker'),
    (@docker_user_id, 'description', 'Docker user'),
    (@docker_user_id, 'rich_editing', 'true'),
    (@docker_user_id, 'syntax_highlighting', 'true'),
    (@docker_user_id, 'comment_shortcuts', 'false'),
    (@docker_user_id, 'admin_color', 'blue'),
    (@docker_user_id, 'wp_capabilities', 'a:1:{s:13:\"administrator\";b:1;}'),
    (@docker_user_id, 'wp_user_level', '10');


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
