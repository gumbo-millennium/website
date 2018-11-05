-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: database
-- Generation Time: Jul 18, 2018 at 02:31 PM
-- Server version: 10.3.8-MariaDB-1:10.3.8+maria~jessie
-- PHP Version: 7.2.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wordpress`
--
USE wordpress;

-- --------------------------------------------------------

--
-- Dropping data for table `wp_postmeta`
--

DELETE FROM `wp_postmeta` WHERE `post_id` IN (
    SELECT
        `ID`
    FROM
        `wp_posts`
    WHERE
        `post_type` = "nav_menu_item"
    AND
        `post_title` IN ("Documenten", "Activiteiten")
);

--
-- Dropping data for table `wp_term_relationships`
--

DELETE FROM `wp_term_relationships` WHERE `object_id` IN (
    SELECT
        `ID`
    FROM
        `wp_posts`
    WHERE
        `post_type` = "nav_menu_item"
    AND
        `post_title` IN ("Documenten", "Activiteiten")
);

--
-- Dropping data for table `wp_posts`
--

DELETE FROM
    `wp_posts`
WHERE
    `post_type` = "nav_menu_item"
AND
    `post_title` IN ("Documenten", "Activiteiten");

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
