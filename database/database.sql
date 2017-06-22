-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 22, 2017 at 11:04 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ortify`
--
CREATE DATABASE IF NOT EXISTS `ortify` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `ortify`;

-- --------------------------------------------------------

--
-- Table structure for table `account_states`
--

DROP TABLE IF EXISTS `account_states`;
CREATE TABLE IF NOT EXISTS `account_states` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_id` int(9) NOT NULL,
  `type` varchar(255) NOT NULL,
  `time` int(90) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `avatars`
--

DROP TABLE IF EXISTS `avatars`;
CREATE TABLE IF NOT EXISTS `avatars` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `id_of_user` int(9) NOT NULL,
  `avatar_path` varchar(255) NOT NULL,
  `date` varchar(255) NOT NULL,
  `positions` varchar(255) NOT NULL DEFAULT '0,0',
  `rotate_degree` varchar(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `backgrounds`
--

DROP TABLE IF EXISTS `backgrounds`;
CREATE TABLE IF NOT EXISTS `backgrounds` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `id_of_user` int(9) NOT NULL,
  `background_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_of` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `blocked_users`
--

DROP TABLE IF EXISTS `blocked_users`;
CREATE TABLE IF NOT EXISTS `blocked_users` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_ids` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(99) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

DROP TABLE IF EXISTS `chats`;
CREATE TABLE IF NOT EXISTS `chats` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `start_date` varchar(20) NOT NULL,
  `chatter_ids` varchar(255) NOT NULL,
  `latest_activity` int(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `comment_replies`
--

DROP TABLE IF EXISTS `comment_replies`;
CREATE TABLE IF NOT EXISTS `comment_replies` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `comment_id` int(99) NOT NULL,
  `user_id` int(99) NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `time` int(99) NOT NULL,
  `upvotes` int(99) NOT NULL,
  `downvotes` int(99) NOT NULL,
  `is_reply_to` int(9) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `comment_upvotes_and_downvotes`
--

DROP TABLE IF EXISTS `comment_upvotes_and_downvotes`;
CREATE TABLE IF NOT EXISTS `comment_upvotes_and_downvotes` (
  `id` int(99) NOT NULL AUTO_INCREMENT,
  `comment_id` int(99) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `contact_of` int(9) NOT NULL,
  `contact` int(9) NOT NULL,
  `date_added` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `post_id` int(9) NOT NULL,
  `user_id` int(9) NOT NULL,
  `time` int(99) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

--
-- Table structure for table `following_tags`
--

DROP TABLE IF EXISTS `following_tags`;
CREATE TABLE IF NOT EXISTS `following_tags` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `id_of_user` int(9) NOT NULL,
  `tag` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=36 ;

-- --------------------------------------------------------

--
-- Table structure for table `hidden_chats`
--

DROP TABLE IF EXISTS `hidden_chats`;
CREATE TABLE IF NOT EXISTS `hidden_chats` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `chat_id` int(9) NOT NULL,
  `user_id` int(9) NOT NULL,
  `date_of` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `logins_and_logouts`
--

DROP TABLE IF EXISTS `logins_and_logouts`;
CREATE TABLE IF NOT EXISTS `logins_and_logouts` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_id` int(9) NOT NULL,
  `date` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=401 ;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(99) NOT NULL AUTO_INCREMENT,
  `chat_id` int(9) NOT NULL,
  `message_from` int(9) NOT NULL,
  `message` text NOT NULL,
  `date_of` varchar(255) CHARACTER SET latin1 NOT NULL,
  `read_yet` tinyint(1) NOT NULL DEFAULT '0',
  `message_type` varchar(255) NOT NULL DEFAULT 'text-message',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(99) NOT NULL AUTO_INCREMENT,
  `notification_from` int(99) NOT NULL,
  `notification_to` int(99) NOT NULL,
  `time` int(99) NOT NULL,
  `type` int(99) NOT NULL,
  `extra` int(99) NOT NULL,
  `extra2` int(99) NOT NULL,
  `extra3` int(99) NOT NULL,
  `read_yet` bigint(99) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=205 ;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(9) NOT NULL,
  `time` int(99) NOT NULL,
  `posted_by` int(9) NOT NULL,
  `file_types` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `post_views` int(99) NOT NULL,
  `post_section` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=93 ;

-- --------------------------------------------------------

--
-- Table structure for table `post_comments`
--

DROP TABLE IF EXISTS `post_comments`;
CREATE TABLE IF NOT EXISTS `post_comments` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `post_id` int(99) NOT NULL,
  `user_id` int(99) NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `time` int(99) NOT NULL,
  `upvotes` int(99) NOT NULL,
  `downvotes` int(99) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=40 ;

-- --------------------------------------------------------

--
-- Table structure for table `post_reports`
--

DROP TABLE IF EXISTS `post_reports`;
CREATE TABLE IF NOT EXISTS `post_reports` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `post_id` int(9) NOT NULL,
  `user_id` int(9) NOT NULL,
  `time` int(99) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `post_votes`
--

DROP TABLE IF EXISTS `post_votes`;
CREATE TABLE IF NOT EXISTS `post_votes` (
  `id` int(99) NOT NULL AUTO_INCREMENT,
  `post_id` int(99) NOT NULL,
  `user_id` int(99) NOT NULL,
  `option_index` int(9) NOT NULL,
  `time` int(99) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=381 ;

-- --------------------------------------------------------

--
-- Table structure for table `reply_upvotes_and_downvotes`
--

DROP TABLE IF EXISTS `reply_upvotes_and_downvotes`;
CREATE TABLE IF NOT EXISTS `reply_upvotes_and_downvotes` (
  `id` int(99) NOT NULL AUTO_INCREMENT,
  `comment_id` int(99) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sent_files`
--

DROP TABLE IF EXISTS `sent_files`;
CREATE TABLE IF NOT EXISTS `sent_files` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `id_of_user` int(9) NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_of` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(36) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar_picture` varchar(255) NOT NULL,
  `background_path` varchar(255) NOT NULL DEFAULT '',
  `gender` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `birthdate` varchar(255) NOT NULL,
  `sign_up_date` varchar(255) NOT NULL,
  `activated` varchar(255) NOT NULL,
  `first_failed_login` int(99) NOT NULL,
  `failed_login_count` int(4) NOT NULL,
  `password_reset_code` int(99) NOT NULL,
  `last_forgot_password_email_sent` int(99) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
