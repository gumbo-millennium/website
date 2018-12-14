-- MySQL dump 10.16  Distrib 10.2.19-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: 127.13.37.1    Database: wordpress
-- ------------------------------------------------------
-- Server version	10.3.11-MariaDB-1:10.3.11+maria~bionic

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Database: `wordpress`
--
USE wordpress;

-- --------------------------------------------------------

--
-- Table structure for table `wp_commentmeta`
--

DROP TABLE IF EXISTS `wp_commentmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_commentmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_commentmeta`
--

LOCK TABLES `wp_commentmeta` WRITE;
/*!40000 ALTER TABLE `wp_commentmeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_commentmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_comments`
--

DROP TABLE IF EXISTS `wp_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_comments` (
  `comment_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_post_ID` bigint(20) unsigned NOT NULL DEFAULT 0,
  `comment_author` tinytext COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment_author_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment_karma` int(11) NOT NULL DEFAULT 0,
  `comment_approved` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`comment_ID`),
  KEY `comment_post_ID` (`comment_post_ID`),
  KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  KEY `comment_date_gmt` (`comment_date_gmt`),
  KEY `comment_parent` (`comment_parent`),
  KEY `comment_author_email` (`comment_author_email`(10))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_comments`
--

LOCK TABLES `wp_comments` WRITE;
/*!40000 ALTER TABLE `wp_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_links`
--

DROP TABLE IF EXISTS `wp_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_links` (
  `link_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `link_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `link_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `link_target` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `link_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `link_visible` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `link_owner` bigint(20) unsigned NOT NULL DEFAULT 1,
  `link_rating` int(11) NOT NULL DEFAULT 0,
  `link_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_rel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `link_notes` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_rss` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`link_id`),
  KEY `link_visible` (`link_visible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_links`
--

LOCK TABLES `wp_links` WRITE;
/*!40000 ALTER TABLE `wp_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_options`
--

DROP TABLE IF EXISTS `wp_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_options` (
  `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `option_value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `autoload` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`)
) ENGINE=InnoDB AUTO_INCREMENT=200 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_options`
--

LOCK TABLES `wp_options` WRITE;
/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;
INSERT INTO `wp_options` VALUES (1,'siteurl','http://127.13.37.1:8080','yes'),(2,'home','http://127.13.37.1','yes'),(3,'blogname','Gumbo Millennium','yes'),(4,'blogdescription','Dubbel L, dubbel N, dubbel debuggen','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','wordpress@example.com','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail','yes'),(15,'mailserver_login','','yes'),(16,'mailserver_pass','','yes'),(17,'mailserver_port','1025','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','0','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','d-m-Y','yes'),(24,'time_format','H:i','yes'),(25,'links_updated_date_format','d-m-y h:i','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%postname%/','yes'),(29,'rewrite_rules','a:111:{s:11:\"^wp-json/?$\";s:22:\"index.php?rest_route=/\";s:14:\"^wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:21:\"^index.php/wp-json/?$\";s:22:\"index.php?rest_route=/\";s:24:\"^index.php/wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:15:\"activiteiten/?$\";s:34:\"index.php?post_type=gumbo-activity\";s:45:\"activiteiten/feed/(feed|rdf|rss|rss2|atom)/?$\";s:51:\"index.php?post_type=gumbo-activity&feed=$matches[1]\";s:40:\"activiteiten/(feed|rdf|rss|rss2|atom)/?$\";s:51:\"index.php?post_type=gumbo-activity&feed=$matches[1]\";s:32:\"activiteiten/page/([0-9]{1,})/?$\";s:52:\"index.php?post_type=gumbo-activity&paged=$matches[1]\";s:47:\"category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:42:\"category/(.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:23:\"category/(.+?)/embed/?$\";s:46:\"index.php?category_name=$matches[1]&embed=true\";s:35:\"category/(.+?)/page/?([0-9]{1,})/?$\";s:53:\"index.php?category_name=$matches[1]&paged=$matches[2]\";s:17:\"category/(.+?)/?$\";s:35:\"index.php?category_name=$matches[1]\";s:44:\"tag/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:39:\"tag/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:20:\"tag/([^/]+)/embed/?$\";s:36:\"index.php?tag=$matches[1]&embed=true\";s:32:\"tag/([^/]+)/page/?([0-9]{1,})/?$\";s:43:\"index.php?tag=$matches[1]&paged=$matches[2]\";s:14:\"tag/([^/]+)/?$\";s:25:\"index.php?tag=$matches[1]\";s:45:\"type/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:40:\"type/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:21:\"type/([^/]+)/embed/?$\";s:44:\"index.php?post_format=$matches[1]&embed=true\";s:33:\"type/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?post_format=$matches[1]&paged=$matches[2]\";s:15:\"type/([^/]+)/?$\";s:33:\"index.php?post_format=$matches[1]\";s:40:\"activiteiten/[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:50:\"activiteiten/[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:70:\"activiteiten/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:65:\"activiteiten/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:65:\"activiteiten/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:46:\"activiteiten/[^/]+/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:29:\"activiteiten/([^/]+)/embed/?$\";s:47:\"index.php?gumbo-activity=$matches[1]&embed=true\";s:33:\"activiteiten/([^/]+)/trackback/?$\";s:41:\"index.php?gumbo-activity=$matches[1]&tb=1\";s:53:\"activiteiten/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:53:\"index.php?gumbo-activity=$matches[1]&feed=$matches[2]\";s:48:\"activiteiten/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:53:\"index.php?gumbo-activity=$matches[1]&feed=$matches[2]\";s:41:\"activiteiten/([^/]+)/page/?([0-9]{1,})/?$\";s:54:\"index.php?gumbo-activity=$matches[1]&paged=$matches[2]\";s:48:\"activiteiten/([^/]+)/comment-page-([0-9]{1,})/?$\";s:54:\"index.php?gumbo-activity=$matches[1]&cpage=$matches[2]\";s:37:\"activiteiten/([^/]+)(?:/([0-9]+))?/?$\";s:53:\"index.php?gumbo-activity=$matches[1]&page=$matches[2]\";s:29:\"activiteiten/[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:39:\"activiteiten/[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:59:\"activiteiten/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:54:\"activiteiten/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:54:\"activiteiten/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:35:\"activiteiten/[^/]+/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:12:\"robots\\.txt$\";s:18:\"index.php?robots=1\";s:48:\".*wp-(atom|rdf|rss|rss2|feed|commentsrss2)\\.php$\";s:18:\"index.php?feed=old\";s:20:\".*wp-app\\.php(/.*)?$\";s:19:\"index.php?error=403\";s:18:\".*wp-register.php$\";s:23:\"index.php?register=true\";s:32:\"feed/(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:27:\"(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:8:\"embed/?$\";s:21:\"index.php?&embed=true\";s:20:\"page/?([0-9]{1,})/?$\";s:28:\"index.php?&paged=$matches[1]\";s:27:\"comment-page-([0-9]{1,})/?$\";s:38:\"index.php?&page_id=8&cpage=$matches[1]\";s:41:\"comments/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:36:\"comments/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:17:\"comments/embed/?$\";s:21:\"index.php?&embed=true\";s:44:\"search/(.+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:39:\"search/(.+)/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:20:\"search/(.+)/embed/?$\";s:34:\"index.php?s=$matches[1]&embed=true\";s:32:\"search/(.+)/page/?([0-9]{1,})/?$\";s:41:\"index.php?s=$matches[1]&paged=$matches[2]\";s:14:\"search/(.+)/?$\";s:23:\"index.php?s=$matches[1]\";s:47:\"author/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:42:\"author/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:23:\"author/([^/]+)/embed/?$\";s:44:\"index.php?author_name=$matches[1]&embed=true\";s:35:\"author/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?author_name=$matches[1]&paged=$matches[2]\";s:17:\"author/([^/]+)/?$\";s:33:\"index.php?author_name=$matches[1]\";s:69:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:64:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:45:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/embed/?$\";s:74:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&embed=true\";s:57:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:81:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]\";s:39:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$\";s:63:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]\";s:56:\"([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:51:\"([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:32:\"([0-9]{4})/([0-9]{1,2})/embed/?$\";s:58:\"index.php?year=$matches[1]&monthnum=$matches[2]&embed=true\";s:44:\"([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:65:\"index.php?year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]\";s:26:\"([0-9]{4})/([0-9]{1,2})/?$\";s:47:\"index.php?year=$matches[1]&monthnum=$matches[2]\";s:43:\"([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:38:\"([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:19:\"([0-9]{4})/embed/?$\";s:37:\"index.php?year=$matches[1]&embed=true\";s:31:\"([0-9]{4})/page/?([0-9]{1,})/?$\";s:44:\"index.php?year=$matches[1]&paged=$matches[2]\";s:13:\"([0-9]{4})/?$\";s:26:\"index.php?year=$matches[1]\";s:27:\".?.+?/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:37:\".?.+?/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:57:\".?.+?/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:33:\".?.+?/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:16:\"(.?.+?)/embed/?$\";s:41:\"index.php?pagename=$matches[1]&embed=true\";s:20:\"(.?.+?)/trackback/?$\";s:35:\"index.php?pagename=$matches[1]&tb=1\";s:40:\"(.?.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:35:\"(.?.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:28:\"(.?.+?)/page/?([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&paged=$matches[2]\";s:35:\"(.?.+?)/comment-page-([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&cpage=$matches[2]\";s:24:\"(.?.+?)(?:/([0-9]+))?/?$\";s:47:\"index.php?pagename=$matches[1]&page=$matches[2]\";s:27:\"[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:37:\"[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:57:\"[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\"[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\"[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:33:\"[^/]+/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:16:\"([^/]+)/embed/?$\";s:37:\"index.php?name=$matches[1]&embed=true\";s:20:\"([^/]+)/trackback/?$\";s:31:\"index.php?name=$matches[1]&tb=1\";s:40:\"([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?name=$matches[1]&feed=$matches[2]\";s:35:\"([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?name=$matches[1]&feed=$matches[2]\";s:28:\"([^/]+)/page/?([0-9]{1,})/?$\";s:44:\"index.php?name=$matches[1]&paged=$matches[2]\";s:35:\"([^/]+)/comment-page-([0-9]{1,})/?$\";s:44:\"index.php?name=$matches[1]&cpage=$matches[2]\";s:24:\"([^/]+)(?:/([0-9]+))?/?$\";s:43:\"index.php?name=$matches[1]&page=$matches[2]\";s:16:\"[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:26:\"[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:46:\"[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:41:\"[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:41:\"[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:22:\"[^/]+/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";}','yes'),(30,'hack_file','0','yes'),(31,'blog_charset','UTF-8','yes'),(32,'moderation_keys','','no'),(33,'active_plugins','a:0:{}','yes'),(34,'category_base','','yes'),(35,'ping_sites','','yes'),(36,'comment_max_links','2','yes'),(37,'gmt_offset','','yes'),(38,'default_email_category','1','yes'),(39,'recently_edited','','no'),(40,'template','gumbo-millennium','yes'),(41,'stylesheet','gumbo-millennium','yes'),(42,'comment_whitelist','1','yes'),(43,'blacklist_keys','','no'),(44,'comment_registration','0','yes'),(45,'html_type','text/html','yes'),(46,'use_trackback','0','yes'),(47,'default_role','subscriber','yes'),(48,'db_version','43764','yes'),(49,'uploads_use_yearmonth_folders','1','yes'),(50,'upload_path','','yes'),(51,'blog_public','0','yes'),(52,'default_link_category','2','yes'),(53,'show_on_front','page','yes'),(54,'tag_base','','yes'),(55,'show_avatars','1','yes'),(56,'avatar_rating','G','yes'),(57,'upload_url_path','','yes'),(58,'thumbnail_size_w','150','yes'),(59,'thumbnail_size_h','150','yes'),(60,'thumbnail_crop','1','yes'),(61,'medium_size_w','300','yes'),(62,'medium_size_h','300','yes'),(63,'avatar_default','mystery','yes'),(64,'large_size_w','1024','yes'),(65,'large_size_h','1024','yes'),(66,'image_default_link_type','none','yes'),(67,'image_default_size','','yes'),(68,'image_default_align','','yes'),(69,'close_comments_for_old_posts','0','yes'),(70,'close_comments_days_old','14','yes'),(71,'thread_comments','1','yes'),(72,'thread_comments_depth','5','yes'),(73,'page_comments','0','yes'),(74,'comments_per_page','50','yes'),(75,'default_comments_page','newest','yes'),(76,'comment_order','asc','yes'),(77,'sticky_posts','a:0:{}','yes'),(78,'widget_categories','a:2:{i:2;a:4:{s:5:\"title\";s:0:\"\";s:5:\"count\";i:0;s:12:\"hierarchical\";i:0;s:8:\"dropdown\";i:0;}s:12:\"_multiwidget\";i:1;}','yes'),(79,'widget_text','a:0:{}','yes'),(80,'widget_rss','a:0:{}','yes'),(81,'uninstall_plugins','a:0:{}','no'),(82,'timezone_string','Europe/Amsterdam','yes'),(83,'page_for_posts','0','yes'),(84,'page_on_front','33','yes'),(85,'default_post_format','0','yes'),(86,'link_manager_enabled','0','yes'),(87,'finished_splitting_shared_terms','1','yes'),(88,'site_icon','0','yes'),(89,'medium_large_size_w','768','yes'),(90,'medium_large_size_h','0','yes'),(91,'wp_page_for_privacy_policy','3','yes'),(92,'initial_db_version','38590','yes'),(93,'wp_user_roles','a:5:{s:13:\"administrator\";a:2:{s:4:\"name\";s:13:\"Administrator\";s:12:\"capabilities\";a:61:{s:13:\"switch_themes\";b:1;s:11:\"edit_themes\";b:1;s:16:\"activate_plugins\";b:1;s:12:\"edit_plugins\";b:1;s:10:\"edit_users\";b:1;s:10:\"edit_files\";b:1;s:14:\"manage_options\";b:1;s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:6:\"import\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:8:\"level_10\";b:1;s:7:\"level_9\";b:1;s:7:\"level_8\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:12:\"delete_users\";b:1;s:12:\"create_users\";b:1;s:17:\"unfiltered_upload\";b:1;s:14:\"edit_dashboard\";b:1;s:14:\"update_plugins\";b:1;s:14:\"delete_plugins\";b:1;s:15:\"install_plugins\";b:1;s:13:\"update_themes\";b:1;s:14:\"install_themes\";b:1;s:11:\"update_core\";b:1;s:10:\"list_users\";b:1;s:12:\"remove_users\";b:1;s:13:\"promote_users\";b:1;s:18:\"edit_theme_options\";b:1;s:13:\"delete_themes\";b:1;s:6:\"export\";b:1;}}s:6:\"editor\";a:2:{s:4:\"name\";s:6:\"Editor\";s:12:\"capabilities\";a:34:{s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;}}s:6:\"author\";a:2:{s:4:\"name\";s:6:\"Author\";s:12:\"capabilities\";a:10:{s:12:\"upload_files\";b:1;s:10:\"edit_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;s:22:\"delete_published_posts\";b:1;}}s:11:\"contributor\";a:2:{s:4:\"name\";s:11:\"Contributor\";s:12:\"capabilities\";a:5:{s:10:\"edit_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;}}s:10:\"subscriber\";a:2:{s:4:\"name\";s:10:\"Subscriber\";s:12:\"capabilities\";a:2:{s:4:\"read\";b:1;s:7:\"level_0\";b:1;}}}','yes'),(94,'fresh_site','0','yes'),(95,'WPLANG','','yes'),(96,'widget_search','a:2:{i:2;a:1:{s:5:\"title\";s:0:\"\";}s:12:\"_multiwidget\";i:1;}','yes'),(97,'widget_recent-posts','a:2:{i:2;a:2:{s:5:\"title\";s:0:\"\";s:6:\"number\";i:5;}s:12:\"_multiwidget\";i:1;}','yes'),(98,'widget_recent-comments','a:2:{i:2;a:2:{s:5:\"title\";s:0:\"\";s:6:\"number\";i:5;}s:12:\"_multiwidget\";i:1;}','yes'),(99,'widget_archives','a:2:{i:2;a:3:{s:5:\"title\";s:0:\"\";s:5:\"count\";i:0;s:8:\"dropdown\";i:0;}s:12:\"_multiwidget\";i:1;}','yes'),(100,'widget_meta','a:2:{i:2;a:1:{s:5:\"title\";s:0:\"\";}s:12:\"_multiwidget\";i:1;}','yes'),(101,'sidebars_widgets','a:5:{s:19:\"wp_inactive_widgets\";a:0:{}s:12:\"sidebar-blog\";a:6:{i:0;s:8:\"search-2\";i:1;s:14:\"recent-posts-2\";i:2;s:17:\"recent-comments-2\";i:3;s:10:\"archives-2\";i:4;s:12:\"categories-2\";i:5;s:6:\"meta-2\";}s:18:\"sidebar-activities\";a:0:{}s:13:\"sidebar-files\";a:0:{}s:13:\"array_version\";i:3;}','yes'),(102,'widget_pages','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(103,'widget_calendar','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(104,'widget_media_audio','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(105,'widget_media_image','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(106,'widget_media_gallery','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(107,'widget_media_video','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(108,'widget_tag_cloud','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(109,'widget_nav_menu','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(110,'widget_custom_html','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),(111,'cron','a:5:{i:1530037126;a:4:{s:34:\"wp_privacy_delete_old_export_files\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"hourly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:3600;}}s:16:\"wp_version_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:17:\"wp_update_plugins\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:16:\"wp_update_themes\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1530037207;a:2:{s:19:\"wp_scheduled_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:25:\"delete_expired_transients\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1531599063;a:1:{s:30:\"wp_scheduled_auto_draft_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1531599188;a:1:{s:8:\"do_pings\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:2:{s:8:\"schedule\";b:0;s:4:\"args\";a:0:{}}}}s:7:\"version\";i:2;}','yes'),(112,'theme_mods_twentyseventeen','a:3:{s:18:\"custom_css_post_id\";i:-1;s:18:\"nav_menu_locations\";a:0:{}s:16:\"sidebars_widgets\";a:2:{s:4:\"time\";i:1531600218;s:4:\"data\";a:4:{s:19:\"wp_inactive_widgets\";a:0:{}s:9:\"sidebar-1\";a:6:{i:0;s:8:\"search-2\";i:1;s:14:\"recent-posts-2\";i:2;s:17:\"recent-comments-2\";i:3;s:10:\"archives-2\";i:4;s:12:\"categories-2\";i:5;s:6:\"meta-2\";}s:9:\"sidebar-2\";a:0:{}s:9:\"sidebar-3\";a:0:{}}}}','yes'),(114,'_transient_doing_cron','1544795017.0276639461517333984375','yes'),(137,'new_admin_email','kutbestuur@gumbo-millennium.nl','yes'),(141,'_site_transient_update_plugins','O:8:\"stdClass\":4:{s:12:\"last_checked\";i:1544795043;s:8:\"response\";a:0:{}s:12:\"translations\";a:0:{}s:9:\"no_update\";a:2:{s:19:\"akismet/akismet.php\";O:8:\"stdClass\":9:{s:2:\"id\";s:21:\"w.org/plugins/akismet\";s:4:\"slug\";s:7:\"akismet\";s:6:\"plugin\";s:19:\"akismet/akismet.php\";s:11:\"new_version\";s:3:\"4.1\";s:3:\"url\";s:38:\"https://wordpress.org/plugins/akismet/\";s:7:\"package\";s:54:\"https://downloads.wordpress.org/plugin/akismet.4.1.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:59:\"https://ps.w.org/akismet/assets/icon-256x256.png?rev=969272\";s:2:\"1x\";s:59:\"https://ps.w.org/akismet/assets/icon-128x128.png?rev=969272\";}s:7:\"banners\";a:1:{s:2:\"1x\";s:61:\"https://ps.w.org/akismet/assets/banner-772x250.jpg?rev=479904\";}s:11:\"banners_rtl\";a:0:{}}s:9:\"hello.php\";O:8:\"stdClass\":9:{s:2:\"id\";s:25:\"w.org/plugins/hello-dolly\";s:4:\"slug\";s:11:\"hello-dolly\";s:6:\"plugin\";s:9:\"hello.php\";s:11:\"new_version\";s:3:\"1.6\";s:3:\"url\";s:42:\"https://wordpress.org/plugins/hello-dolly/\";s:7:\"package\";s:58:\"https://downloads.wordpress.org/plugin/hello-dolly.1.6.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:63:\"https://ps.w.org/hello-dolly/assets/icon-256x256.jpg?rev=969907\";s:2:\"1x\";s:63:\"https://ps.w.org/hello-dolly/assets/icon-128x128.jpg?rev=969907\";}s:7:\"banners\";a:1:{s:2:\"1x\";s:65:\"https://ps.w.org/hello-dolly/assets/banner-772x250.png?rev=478342\";}s:11:\"banners_rtl\";a:0:{}}}}','no'),(142,'_site_transient_update_themes','O:8:\"stdClass\":4:{s:12:\"last_checked\";i:1544795045;s:7:\"checked\";a:4:{s:16:\"gumbo-millennium\";s:3:\"1.0\";s:14:\"twentynineteen\";s:3:\"1.0\";s:15:\"twentyseventeen\";s:3:\"1.8\";s:13:\"twentysixteen\";s:3:\"1.6\";}s:8:\"response\";a:0:{}s:12:\"translations\";a:0:{}}','no'),(180,'current_theme','Gumbo Millennium','yes'),(181,'theme_mods_gumbo-millennium','a:2:{i:0;b:0;s:18:\"nav_menu_locations\";a:2:{s:6:\"header\";i:2;s:6:\"footer\";i:3;}}','yes'),(182,'theme_switched','','yes'),(185,'recently_activated','a:0:{}','yes'),(186,'nav_menu_options','a:2:{i:0;b:0;s:8:\"auto_add\";a:0:{}}','yes'),(187,'adminhash','a:2:{s:4:\"hash\";s:32:\"52fca0ddc1020e9b076422dc5f4de469\";s:8:\"newemail\";s:30:\"kutbestuur@gumbo-millennium.nl\";}','yes'),(195,'_site_transient_timeout_theme_roots','1544796844','no'),(196,'_site_transient_theme_roots','a:4:{s:16:\"gumbo-millennium\";s:7:\"/themes\";s:14:\"twentynineteen\";s:7:\"/themes\";s:15:\"twentyseventeen\";s:7:\"/themes\";s:13:\"twentysixteen\";s:7:\"/themes\";}','no'),(197,'_site_transient_update_core','O:8:\"stdClass\":4:{s:7:\"updates\";a:2:{i:0;O:8:\"stdClass\":10:{s:8:\"response\";s:7:\"upgrade\";s:8:\"download\";s:59:\"https://downloads.wordpress.org/release/wordpress-5.0.1.zip\";s:6:\"locale\";s:5:\"en_US\";s:8:\"packages\";O:8:\"stdClass\":5:{s:4:\"full\";s:59:\"https://downloads.wordpress.org/release/wordpress-5.0.1.zip\";s:10:\"no_content\";s:70:\"https://downloads.wordpress.org/release/wordpress-5.0.1-no-content.zip\";s:11:\"new_bundled\";s:71:\"https://downloads.wordpress.org/release/wordpress-5.0.1-new-bundled.zip\";s:7:\"partial\";s:69:\"https://downloads.wordpress.org/release/wordpress-5.0.1-partial-0.zip\";s:8:\"rollback\";b:0;}s:7:\"current\";s:5:\"5.0.1\";s:7:\"version\";s:5:\"5.0.1\";s:11:\"php_version\";s:5:\"5.2.4\";s:13:\"mysql_version\";s:3:\"5.0\";s:11:\"new_bundled\";s:3:\"5.0\";s:15:\"partial_version\";s:3:\"5.0\";}i:1;O:8:\"stdClass\":11:{s:8:\"response\";s:10:\"autoupdate\";s:8:\"download\";s:59:\"https://downloads.wordpress.org/release/wordpress-5.0.1.zip\";s:6:\"locale\";s:5:\"en_US\";s:8:\"packages\";O:8:\"stdClass\":5:{s:4:\"full\";s:59:\"https://downloads.wordpress.org/release/wordpress-5.0.1.zip\";s:10:\"no_content\";s:70:\"https://downloads.wordpress.org/release/wordpress-5.0.1-no-content.zip\";s:11:\"new_bundled\";s:71:\"https://downloads.wordpress.org/release/wordpress-5.0.1-new-bundled.zip\";s:7:\"partial\";s:69:\"https://downloads.wordpress.org/release/wordpress-5.0.1-partial-0.zip\";s:8:\"rollback\";s:70:\"https://downloads.wordpress.org/release/wordpress-5.0.1-rollback-0.zip\";}s:7:\"current\";s:5:\"5.0.1\";s:7:\"version\";s:5:\"5.0.1\";s:11:\"php_version\";s:5:\"5.2.4\";s:13:\"mysql_version\";s:3:\"5.0\";s:11:\"new_bundled\";s:3:\"5.0\";s:15:\"partial_version\";s:3:\"5.0\";s:9:\"new_files\";s:0:\"\";}}s:12:\"last_checked\";i:1544795046;s:15:\"version_checked\";s:3:\"5.0\";s:12:\"translations\";a:0:{}}','no'),(198,'show_comments_cookies_opt_in','0','yes'),(199,'db_upgraded','1','yes');
/*!40000 ALTER TABLE `wp_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_postmeta`
--

DROP TABLE IF EXISTS `wp_postmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_postmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `post_id` (`post_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_postmeta`
--

LOCK TABLES `wp_postmeta` WRITE;
/*!40000 ALTER TABLE `wp_postmeta` DISABLE KEYS */;
INSERT INTO `wp_postmeta` VALUES (19,20,'_menu_item_type','custom'),(20,20,'_menu_item_menu_item_parent','0'),(21,20,'_menu_item_object_id','20'),(22,20,'_menu_item_object','custom'),(23,20,'_menu_item_target',''),(24,20,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),(25,20,'_menu_item_xfn',''),(26,20,'_menu_item_url','/'),(28,21,'_menu_item_type','custom'),(29,21,'_menu_item_menu_item_parent','0'),(30,21,'_menu_item_object_id','21'),(31,21,'_menu_item_object','custom'),(32,21,'_menu_item_target',''),(33,21,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),(34,21,'_menu_item_xfn',''),(35,21,'_menu_item_url','/about'),(37,22,'_menu_item_type','custom'),(38,22,'_menu_item_menu_item_parent','21'),(39,22,'_menu_item_object_id','22'),(40,22,'_menu_item_object','custom'),(41,22,'_menu_item_target',''),(42,22,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),(43,22,'_menu_item_xfn',''),(44,22,'_menu_item_url','/about/history'),(46,23,'_menu_item_type','custom'),(47,23,'_menu_item_menu_item_parent','21'),(48,23,'_menu_item_object_id','23'),(49,23,'_menu_item_object','custom'),(50,23,'_menu_item_target',''),(51,23,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),(52,23,'_menu_item_xfn',''),(53,23,'_menu_item_url','/about/board'),(55,24,'_menu_item_type','custom'),(56,24,'_menu_item_menu_item_parent','21'),(57,24,'_menu_item_object_id','24'),(58,24,'_menu_item_object','custom'),(59,24,'_menu_item_target',''),(60,24,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),(61,24,'_menu_item_xfn',''),(62,24,'_menu_item_url','/about/committees'),(64,25,'_menu_item_type','custom'),(65,25,'_menu_item_menu_item_parent','21'),(66,25,'_menu_item_object_id','25'),(67,25,'_menu_item_object','custom'),(68,25,'_menu_item_target',''),(69,25,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),(70,25,'_menu_item_xfn',''),(71,25,'_menu_item_url','/about/project-groups'),(73,26,'_menu_item_type','custom'),(74,26,'_menu_item_menu_item_parent','0'),(75,26,'_menu_item_object_id','26'),(76,26,'_menu_item_object','custom'),(77,26,'_menu_item_target',''),(78,26,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),(79,26,'_menu_item_xfn',''),(80,26,'_menu_item_url','/news'),(82,27,'_menu_item_type','custom'),(83,27,'_menu_item_menu_item_parent','0'),(84,27,'_menu_item_object_id','27'),(85,27,'_menu_item_object','custom'),(86,27,'_menu_item_target',''),(87,27,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),(88,27,'_menu_item_xfn',''),(89,27,'_menu_item_url','/activities'),(91,28,'_menu_item_type','custom'),(92,28,'_menu_item_menu_item_parent','0'),(93,28,'_menu_item_object_id','28'),(94,28,'_menu_item_object','custom'),(95,28,'_menu_item_target',''),(96,28,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),(97,28,'_menu_item_xfn',''),(98,28,'_menu_item_url','/files'),(100,29,'_menu_item_type','custom'),(101,29,'_menu_item_menu_item_parent','0'),(102,29,'_menu_item_object_id','29'),(103,29,'_menu_item_object','custom'),(104,29,'_menu_item_target',''),(105,29,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),(106,29,'_menu_item_xfn',''),(107,29,'_menu_item_url','/'),(109,30,'_menu_item_type','custom'),(110,30,'_menu_item_menu_item_parent','0'),(111,30,'_menu_item_object_id','30'),(112,30,'_menu_item_object','custom'),(113,30,'_menu_item_target',''),(114,30,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),(115,30,'_menu_item_xfn',''),(116,30,'_menu_item_url','/about'),(118,31,'_menu_item_type','custom'),(119,31,'_menu_item_menu_item_parent','0'),(120,31,'_menu_item_object_id','31'),(121,31,'_menu_item_object','custom'),(122,31,'_menu_item_target',''),(123,31,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),(124,31,'_menu_item_xfn',''),(125,31,'_menu_item_url','/about/history'),(127,32,'_menu_item_type','custom'),(128,32,'_menu_item_menu_item_parent','0'),(129,32,'_menu_item_object_id','32'),(130,32,'_menu_item_object','custom'),(131,32,'_menu_item_target',''),(132,32,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),(133,32,'_menu_item_xfn',''),(134,32,'_menu_item_url','/activities'),(136,33,'_edit_last','1'),(137,33,'_edit_lock','1531600334:1'),(138,35,'_edit_last','1'),(139,35,'_edit_lock','1531600351:1'),(140,37,'_edit_last','1'),(141,37,'_edit_lock','1531600369:1'),(142,39,'_edit_last','1'),(143,39,'_edit_lock','1531600389:1'),(144,39,'_encloseme','1'),(145,41,'_edit_last','1'),(146,41,'_edit_lock','1531601940:1'),(147,41,'_encloseme','1');
/*!40000 ALTER TABLE `wp_postmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_posts`
--

DROP TABLE IF EXISTS `wp_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_posts` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL DEFAULT 0,
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_excerpt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `post_password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `post_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `to_ping` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `pinged` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `guid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT 0,
  `post_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_posts`
--

LOCK TABLES `wp_posts` WRITE;
/*!40000 ALTER TABLE `wp_posts` DISABLE KEYS */;
INSERT INTO `wp_posts` VALUES (20,1,'2018-07-14 22:33:46','2018-07-14 20:33:46','','Homepage','','publish','closed','closed','','homepage','','','2018-07-14 22:33:46','2018-07-14 20:33:46','',0,'http://gumbo.localhost/?p=20',1,'nav_menu_item','',0),(21,1,'2018-07-14 22:33:46','2018-07-14 20:33:46','','Over','','publish','closed','closed','','over','','','2018-07-14 22:33:46','2018-07-14 20:33:46','',0,'http://gumbo.localhost/?p=21',2,'nav_menu_item','',0),(22,1,'2018-07-14 22:33:46','2018-07-14 20:33:46','','Geschiedenis','','publish','closed','closed','','geschiedenis','','','2018-07-14 22:33:46','2018-07-14 20:33:46','',0,'http://gumbo.localhost/?p=22',3,'nav_menu_item','',0),(23,1,'2018-07-14 22:33:46','2018-07-14 20:33:46','','Bestuur','','publish','closed','closed','','bestuur','','','2018-07-14 22:33:46','2018-07-14 20:33:46','',0,'http://gumbo.localhost/?p=23',4,'nav_menu_item','',0),(24,1,'2018-07-14 22:33:46','2018-07-14 20:33:46','','Commissies','','publish','closed','closed','','commissies','','','2018-07-14 22:33:46','2018-07-14 20:33:46','',0,'http://gumbo.localhost/?p=24',5,'nav_menu_item','',0),(25,1,'2018-07-14 22:33:46','2018-07-14 20:33:46','','Projectgroepen','','publish','closed','closed','','projectgroepen','','','2018-07-14 22:33:46','2018-07-14 20:33:46','',0,'http://gumbo.localhost/?p=25',6,'nav_menu_item','',0),(29,1,'2018-07-14 22:34:28','2018-07-14 20:34:28','','Homepage','','publish','closed','closed','','homepage-2','','','2018-07-14 22:34:28','2018-07-14 20:34:28','',0,'http://gumbo.localhost/?p=29',1,'nav_menu_item','',0),(30,1,'2018-07-14 22:34:28','2018-07-14 20:34:28','','Over','','publish','closed','closed','','over-2','','','2018-07-14 22:34:28','2018-07-14 20:34:28','',0,'http://gumbo.localhost/?p=30',2,'nav_menu_item','',0),(31,1,'2018-07-14 22:34:28','2018-07-14 20:34:28','','Geschiedenis','','publish','closed','closed','','geschiedenis-2','','','2018-07-14 22:34:28','2018-07-14 20:34:28','',0,'http://gumbo.localhost/?p=31',3,'nav_menu_item','',0),(32,1,'2018-07-14 22:34:28','2018-07-14 20:34:28','','Activiteiten','','publish','closed','closed','','activiteiten-2','','','2018-07-14 22:34:28','2018-07-14 20:34:28','',0,'http://gumbo.localhost/?p=32',4,'nav_menu_item','',0),(33,1,'2018-07-14 22:34:37','2018-07-14 20:34:37','<!-- wp:gumbo/central-intro -->\n<div class=\"wp-block-gumbo-central-intro central-intro\"><div class=\"central-intro__container\"><h3 class=\"central-intro__title\">Maak kennis met Gumbo Millennium </h3><hr class=\"central-intro__divider\"/><p class=\"central-intro__content\">\n            Sinds 1991 staat Gumbo Millennium voor gezelligheid en om een fantastische\n            studententijd te beleven. Met onze eigen plek op Windesheim om te relaxen, soosavonden in Studentencafé Het\n            Vliegende Paard en veel variërende activiteiten, is er voor ieder wat wils binnen Gumbo.\n        </p></div></div>\n<!-- /wp:gumbo/central-intro -->\n\n<!-- wp:gumbo/unique-selling-points -->\n<div class=\"unique-selling-points\" class=\"wp-block-gumbo-unique-selling-points\"><div class=\"container\"><header class=\"unique-selling-points__header\"><h3 class=\"unique-selling-points__header-title\">Waarom Gumbo Millennium?</h3><p class=\"unique-selling-points__header-text\">Een vereniging naast je studie is goed voor je, maar waarom zou je dan voor Gumbo Millennium kiezen? We geven je graag een paar goede argumenten! </p></header><section class=\"unique-selling-points__features\"><div class=\"row\"><!-- wp:gumbo/unique-selling-point {\"id\":55,\"src\":\"https://cms.gumbo.nu/wp-content/uploads/2018/09/diesel-e1538144642252.jpg\"} -->\n<div class=\"col-md-6 unique-selling-points__feature\" class=\"wp-block-gumbo-unique-selling-point\"><img src=\"https://cms.gumbo.nu/wp-content/uploads/2018/09/diesel-e1538144642252.jpg\" class=\"unique-selling-points__feature-icon\"/><section class=\"unique-selling-points__feature-inner\"><h4 class=\"unique-selling-points__feature-title\">Geen ontgroening</h4><p class=\"unique-selling-points__feature-desc\">\"Feuten\" is leuk als serie, maar om zelf mee te maken wat minder.</p></section></div>\n<!-- /wp:gumbo/unique-selling-point -->\n\n<!-- wp:gumbo/unique-selling-point {\"id\":63,\"src\":\"https://cms.gumbo.nu/wp-content/uploads/2018/09/gallery.png\"} -->\n<div class=\"col-md-6 unique-selling-points__feature\" class=\"wp-block-gumbo-unique-selling-point\"><img src=\"https://cms.gumbo.nu/wp-content/uploads/2018/09/gallery.png\" class=\"unique-selling-points__feature-icon\"/><section class=\"unique-selling-points__feature-inner\"><h4 class=\"unique-selling-points__feature-title\">Gratis koffie en thee</h4><p class=\"unique-selling-points__feature-desc\">Om je studie toch een soort van te overleven.</p></section></div>\n<!-- /wp:gumbo/unique-selling-point -->\n\n<!-- wp:gumbo/unique-selling-point {\"id\":62,\"src\":\"https://cms.gumbo.nu/wp-content/uploads/2018/09/cta.png\"} -->\n<div class=\"col-md-6 unique-selling-points__feature\" class=\"wp-block-gumbo-unique-selling-point\"><img src=\"https://cms.gumbo.nu/wp-content/uploads/2018/09/cta.png\" class=\"unique-selling-points__feature-icon\"/><section class=\"unique-selling-points__feature-inner\"><h4 class=\"unique-selling-points__feature-title\">Ontspanruimte op Windesheim</h4><p class=\"unique-selling-points__feature-desc\">Tussen de colleges door even uitblazen, zodat je weer verder kunt knallen!</p></section></div>\n<!-- /wp:gumbo/unique-selling-point -->\n\n<!-- wp:gumbo/unique-selling-point {\"id\":61,\"src\":\"https://cms.gumbo.nu/wp-content/uploads/2018/09/ad.png\"} -->\n<div class=\"col-md-6 unique-selling-points__feature\" class=\"wp-block-gumbo-unique-selling-point\"><img src=\"https://cms.gumbo.nu/wp-content/uploads/2018/09/ad.png\" class=\"unique-selling-points__feature-icon\"/><section class=\"unique-selling-points__feature-inner\"><h4 class=\"unique-selling-points__feature-title\">Leuke themafeesten</h4><p class=\"unique-selling-points__feature-desc\">Iedere paar weken een leuk ander feest, het hele jaar door!</p></section></div>\n<!-- /wp:gumbo/unique-selling-point --></div></section></div></div>\n<!-- /wp:gumbo/unique-selling-points -->\n\n<!-- wp:gumbo/sponsor /-->\n\n<!-- wp:gumbo/testimonials {\"id\":59,\"photo\":\"https://cms.gumbo.nu/wp-content/uploads/2018/09/MV5BNTQ5OTMyMzQ2MF5BMl5BanBnXkFtZTcwMTk5MzAxMw@@._V1_SY1000_CR0013891000_AL_.jpg\"} -->\n<div class=\"wp-block-gumbo-testimonials testimonials\"><div class=\"container\"><div class=\"testimonials__quote\">And the Lord spake, saying, \"First shalt thou take out the Holy Pin. Then shalt thou count to three, no more, no less. Three shall be the number thou shalt count, and the number of the counting shall be three. Four shalt thou not count, neither count thou two, excepting that thou then proceed to three. Five is right out. Once the number three, being the third number, be reached, then lobbest thou thy Holy Hand Grenade of Antioch towards thy foe, who, being naughty in My sight, shall snuff it.</div><div class=\"testimonials__meta\"><img src=\"https://cms.gumbo.nu/wp-content/uploads/2018/09/MV5BNTQ5OTMyMzQ2MF5BMl5BanBnXkFtZTcwMTk5MzAxMw@@._V1_SY1000_CR0013891000_AL_.jpg\" class=\"testimonials__photo\"/><div class=\"testimonials__author\"><span class=\"testimonials__author-name\">Michael Palin</span><span class=\"testimonials__author-company\">\"Cleric\" in Monthy Python and the Holy Grail</span></div></div></div></div>\n<!-- /wp:gumbo/testimonials -->','Homepage','','publish','closed','closed','','homepage','','','2018-07-14 22:34:37','2018-07-14 20:34:37','<!-- wp:gumbo/central-intro -->\n<div class=\"wp-block-gumbo-central-intro central-intro\"><div class=\"central-intro__container\"><h3 class=\"central-intro__title\">Maak kennis met Gumbo Millennium </h3><hr class=\"central-intro__divider\"/><p class=\"central-intro__content\">\n            Sinds 1991 staat Gumbo Millennium voor gezelligheid en om een fantastische\n            studententijd te beleven. Met onze eigen plek op Windesheim om te relaxen, soosavonden in Studentencafé Het\n            Vliegende Paard en veel variërende activiteiten, is er voor ieder wat wils binnen Gumbo.\n        </p></div></div>\n<!-- /wp:gumbo/central-intro -->\n\n<!-- wp:gumbo/unique-selling-points -->\n<div class=\"unique-selling-points\" class=\"wp-block-gumbo-unique-selling-points\"><div class=\"container\"><header class=\"unique-selling-points__header\"><h3 class=\"unique-selling-points__header-title\">Waarom Gumbo Millennium?</h3><p class=\"unique-selling-points__header-text\">Een vereniging naast je studie is goed voor je, maar waarom zou je dan voor Gumbo Millennium kiezen? We geven je graag een paar goede argumenten! </p></header><section class=\"unique-selling-points__features\"><div class=\"row\"><!-- wp:gumbo/unique-selling-point {\"id\":55,\"src\":\"https://cms.gumbo.nu/wp-content/uploads/2018/09/diesel-e1538144642252.jpg\"} -->\n<div class=\"col-md-6 unique-selling-points__feature\" class=\"wp-block-gumbo-unique-selling-point\"><img src=\"https://cms.gumbo.nu/wp-content/uploads/2018/09/diesel-e1538144642252.jpg\" class=\"unique-selling-points__feature-icon\"/><section class=\"unique-selling-points__feature-inner\"><h4 class=\"unique-selling-points__feature-title\">Geen ontgroening</h4><p class=\"unique-selling-points__feature-desc\">\"Feuten\" is leuk als serie, maar om zelf mee te maken wat minder.</p></section></div>\n<!-- /wp:gumbo/unique-selling-point -->\n\n<!-- wp:gumbo/unique-selling-point {\"id\":63,\"src\":\"https://cms.gumbo.nu/wp-content/uploads/2018/09/gallery.png\"} -->\n<div class=\"col-md-6 unique-selling-points__feature\" class=\"wp-block-gumbo-unique-selling-point\"><img src=\"https://cms.gumbo.nu/wp-content/uploads/2018/09/gallery.png\" class=\"unique-selling-points__feature-icon\"/><section class=\"unique-selling-points__feature-inner\"><h4 class=\"unique-selling-points__feature-title\">Gratis koffie en thee</h4><p class=\"unique-selling-points__feature-desc\">Om je studie toch een soort van te overleven.</p></section></div>\n<!-- /wp:gumbo/unique-selling-point -->\n\n<!-- wp:gumbo/unique-selling-point {\"id\":62,\"src\":\"https://cms.gumbo.nu/wp-content/uploads/2018/09/cta.png\"} -->\n<div class=\"col-md-6 unique-selling-points__feature\" class=\"wp-block-gumbo-unique-selling-point\"><img src=\"https://cms.gumbo.nu/wp-content/uploads/2018/09/cta.png\" class=\"unique-selling-points__feature-icon\"/><section class=\"unique-selling-points__feature-inner\"><h4 class=\"unique-selling-points__feature-title\">Ontspanruimte op Windesheim</h4><p class=\"unique-selling-points__feature-desc\">Tussen de colleges door even uitblazen, zodat je weer verder kunt knallen!</p></section></div>\n<!-- /wp:gumbo/unique-selling-point -->\n\n<!-- wp:gumbo/unique-selling-point {\"id\":61,\"src\":\"https://cms.gumbo.nu/wp-content/uploads/2018/09/ad.png\"} -->\n<div class=\"col-md-6 unique-selling-points__feature\" class=\"wp-block-gumbo-unique-selling-point\"><img src=\"https://cms.gumbo.nu/wp-content/uploads/2018/09/ad.png\" class=\"unique-selling-points__feature-icon\"/><section class=\"unique-selling-points__feature-inner\"><h4 class=\"unique-selling-points__feature-title\">Leuke themafeesten</h4><p class=\"unique-selling-points__feature-desc\">Iedere paar weken een leuk ander feest, het hele jaar door!</p></section></div>\n<!-- /wp:gumbo/unique-selling-point --></div></section></div></div>\n<!-- /wp:gumbo/unique-selling-points -->\n\n<!-- wp:gumbo/sponsor /-->\n\n<!-- wp:gumbo/testimonials {\"id\":59,\"photo\":\"https://cms.gumbo.nu/wp-content/uploads/2018/09/MV5BNTQ5OTMyMzQ2MF5BMl5BanBnXkFtZTcwMTk5MzAxMw@@._V1_SY1000_CR0013891000_AL_.jpg\"} -->\n<div class=\"wp-block-gumbo-testimonials testimonials\"><div class=\"container\"><div class=\"testimonials__quote\">And the Lord spake, saying, \"First shalt thou take out the Holy Pin. Then shalt thou count to three, no more, no less. Three shall be the number thou shalt count, and the number of the counting shall be three. Four shalt thou not count, neither count thou two, excepting that thou then proceed to three. Five is right out. Once the number three, being the third number, be reached, then lobbest thou thy Holy Hand Grenade of Antioch towards thy foe, who, being naughty in My sight, shall snuff it.</div><div class=\"testimonials__meta\"><img src=\"https://cms.gumbo.nu/wp-content/uploads/2018/09/MV5BNTQ5OTMyMzQ2MF5BMl5BanBnXkFtZTcwMTk5MzAxMw@@._V1_SY1000_CR0013891000_AL_.jpg\" class=\"testimonials__photo\"/><div class=\"testimonials__author\"><span class=\"testimonials__author-name\">Michael Palin</span><span class=\"testimonials__author-company\">\"Cleric\" in Monthy Python and the Holy Grail</span></div></div></div></div>\n<!-- /wp:gumbo/testimonials -->',0,'http://gumbo.localhost/?page_id=33',0,'page','',0),(35,1,'2018-07-14 22:34:54','2018-07-14 20:34:54','Wij stelen met uw privé','Privacy Policy','','publish','closed','closed','','privacy-policy','','','2018-07-14 22:34:54','2018-07-14 20:34:54','',0,'http://gumbo.localhost/?page_id=35',0,'page','',0),(37,1,'2018-07-14 22:35:10','2018-07-14 20:35:10','Wij zijn Gumbo.','Statuten','','publish','closed','closed','','bylaws','','','2018-07-14 22:35:10','2018-07-14 20:35:10','',0,'http://gumbo.localhost/?page_id=37',0,'page','',0),(39,1,'2018-07-14 22:35:31','2018-07-14 20:35:31','Het was een goed weekend, ondaks de ijstijd.','Landhuisweekend 2041','','publish','open','open','','landhuisweekend-2041','','','2018-07-14 22:35:31','2018-07-14 20:35:31','',0,'http://gumbo.localhost/?p=39',0,'post','',0),(41,1,'2018-07-14 22:35:59','2018-07-14 20:35:59','Loop betaald een jaar studievertraging op!','Bestuurders gezocht','','publish','open','open','','bestuurders-gezocht','','','2018-07-14 22:35:59','2018-07-14 20:35:59','',0,'http://gumbo.localhost/?p=41',0,'post','',0),(43,1,'2018-07-14 23:28:06','0000-00-00 00:00:00','','Auto Draft','','auto-draft','open','open','','','','','2018-07-14 23:28:06','0000-00-00 00:00:00','',0,'http://gumbo.localhost/?p=43',0,'post','',0),(44,1,'2018-07-18 16:26:42','2018-07-18 14:26:42',' ','','','publish','closed','closed','','44','','','2018-07-18 16:26:42','2018-07-18 14:26:42','',0,'http://127.13.37.1/?p=44',1,'nav_menu_item','',0),(45,1,'2018-07-18 16:26:42','2018-07-18 14:26:42',' ','','','publish','closed','closed','','45','','','2018-07-18 16:26:42','2018-07-18 14:26:42','',0,'http://127.13.37.1/?p=45',2,'nav_menu_item','',0);
/*!40000 ALTER TABLE `wp_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_term_relationships`
--

DROP TABLE IF EXISTS `wp_term_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_term_relationships` (
  `object_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `term_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_term_relationships`
--

LOCK TABLES `wp_term_relationships` WRITE;
/*!40000 ALTER TABLE `wp_term_relationships` DISABLE KEYS */;
INSERT INTO `wp_term_relationships` VALUES (1,1,0),(16,1,0),(18,1,0),(20,2,0),(21,2,0),(22,2,0),(23,2,0),(24,2,0),(25,2,0),(29,3,0),(30,3,0),(31,3,0),(32,3,0),(39,1,0),(41,1,0);
/*!40000 ALTER TABLE `wp_term_relationships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_term_taxonomy`
--

DROP TABLE IF EXISTS `wp_term_taxonomy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_term_taxonomy` (
  `term_taxonomy_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `taxonomy` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `count` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_term_taxonomy`
--

LOCK TABLES `wp_term_taxonomy` WRITE;
/*!40000 ALTER TABLE `wp_term_taxonomy` DISABLE KEYS */;
INSERT INTO `wp_term_taxonomy` VALUES (1,1,'category','',0,2),(2,2,'nav_menu','',0,9),(3,3,'nav_menu','',0,1),(4,4,'nav_menu','',0,2);
/*!40000 ALTER TABLE `wp_term_taxonomy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_termmeta`
--

DROP TABLE IF EXISTS `wp_termmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_termmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `term_id` (`term_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_termmeta`
--

LOCK TABLES `wp_termmeta` WRITE;
/*!40000 ALTER TABLE `wp_termmeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `wp_termmeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_terms`
--

DROP TABLE IF EXISTS `wp_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `term_group` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_terms`
--

LOCK TABLES `wp_terms` WRITE;
/*!40000 ALTER TABLE `wp_terms` DISABLE KEYS */;
INSERT INTO `wp_terms` VALUES (1,'Uncategorised','uncategorised',0),(2,'Hoofdmenu','hoofdmenu',0),(3,'Footer menu','footer-menu',0),(4,'Legal','legal',0);
/*!40000 ALTER TABLE `wp_terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_usermeta`
--

DROP TABLE IF EXISTS `wp_usermeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_usermeta` (
  `umeta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`umeta_id`),
  KEY `user_id` (`user_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_usermeta`
--

LOCK TABLES `wp_usermeta` WRITE;
/*!40000 ALTER TABLE `wp_usermeta` DISABLE KEYS */;
INSERT INTO `wp_usermeta` VALUES (1,1,'nickname','Gumbo Millennium'),(2,1,'first_name',''),(3,1,'last_name',''),(4,1,'description','Uberführer voor de website'),(5,1,'rich_editing','true'),(8,1,'admin_color','fresh'),(11,1,'locale','en_GB'),(12,1,'wp_capabilities','a:1:{s:13:\"administrator\";b:1;}'),(13,1,'wp_user_level','10'),(32,1,'session_tokens','a:1:{s:64:\"126c829a20848bdee1ec4dc5aadf2b7078c7241558d6bec3a3306de9cfd85dd2\";a:4:{s:10:\"expiration\";i:1531774883;s:2:\"ip\";s:10:\"172.18.0.3\";s:2:\"ua\";s:68:\"Mozilla/5.0 (X11; Linux x86_64; rv:62.0) Gecko/20100101 Firefox/62.0\";s:5:\"login\";i:1531602083;}}'),(33,1,'wp_dashboard_quick_press_last_post_id','43'),(34,1,'community-events-location','a:1:{s:2:\"ip\";s:10:\"172.17.0.0\";}'),(35,2,'wp_capabilities','a:1:{s:13:\"administrator\";b:1;}'),(36,2,'wp_user_level','8');
/*!40000 ALTER TABLE `wp_usermeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wp_users`
--

DROP TABLE IF EXISTS `wp_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_users` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_pass` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_nicename` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_url` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_status` int(11) NOT NULL DEFAULT 0,
  `display_name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_users`
--

LOCK TABLES `wp_users` WRITE;
/*!40000 ALTER TABLE `wp_users` DISABLE KEYS */;
INSERT INTO `wp_users` VALUES (1,'gumbo','$P$Bafdp.zm7Us8oBckvxoVbMZ49tPzAd0','Gumbo Millennium','wordpress@example.com','','2018-01-01 00:00:00','',0,'gumbo'),(2,'gumbo@docker.local','$2y$10$rVFlv7kXIZ7x2FFEWU1ao.oN40mrMTEnmRX9etVFcoDezLXo.ekEW','gumbo-millennium','gumbo@docker.local','','2018-12-14 14:41:59','',0,'Gumbo Millennium');
/*!40000 ALTER TABLE `wp_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-12-14 14:46:49
