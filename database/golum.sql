-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Aug 07, 2017 at 11:48 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `golum`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_states`
--

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

CREATE TABLE IF NOT EXISTS `avatars` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `id_of_user` int(9) NOT NULL,
  `avatar_path` varchar(255) NOT NULL,
  `date_of` varchar(255) NOT NULL,
  `positions` varchar(255) NOT NULL DEFAULT '0,0',
  `rotate_degree` varchar(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=124 ;

-- --------------------------------------------------------

--
-- Table structure for table `backgrounds`
--

CREATE TABLE IF NOT EXISTS `backgrounds` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `id_of_user` int(9) NOT NULL,
  `background_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_of` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=48 ;

-- --------------------------------------------------------

--
-- Table structure for table `blocked_users`
--

CREATE TABLE IF NOT EXISTS `blocked_users` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_ids` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(99) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

CREATE TABLE IF NOT EXISTS `chats` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `start_date` varchar(20) NOT NULL,
  `chatter_ids` varchar(255) NOT NULL,
  `latest_activity` int(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=37 ;

-- --------------------------------------------------------

--
-- Table structure for table `comment_replies`
--

CREATE TABLE IF NOT EXISTS `comment_replies` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `comment_id` int(99) NOT NULL,
  `user_id` int(99) NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `time` int(99) NOT NULL,
  `upvotes` int(99) NOT NULL,
  `downvotes` int(99) NOT NULL,
  `is_reply_to` int(9) NOT NULL,
  `disabled` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=120 ;

-- --------------------------------------------------------

--
-- Table structure for table `comment_upvotes_and_downvotes`
--

CREATE TABLE IF NOT EXISTS `comment_upvotes_and_downvotes` (
  `id` int(99) NOT NULL AUTO_INCREMENT,
  `comment_id` int(99) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=65 ;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `contact_of` int(9) NOT NULL,
  `contact` int(9) NOT NULL,
  `date_added` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=342 ;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE IF NOT EXISTS `favorites` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `post_id` int(9) NOT NULL,
  `user_id` int(9) NOT NULL,
  `time` int(99) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=178 ;

-- --------------------------------------------------------

--
-- Table structure for table `following_tags`
--

CREATE TABLE IF NOT EXISTS `following_tags` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `id_of_user` int(9) NOT NULL,
  `tag` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=142 ;

-- --------------------------------------------------------

--
-- Table structure for table `hidden_chats`
--

CREATE TABLE IF NOT EXISTS `hidden_chats` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `chat_id` int(9) NOT NULL,
  `user_id` int(9) NOT NULL,
  `date_of` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(99) NOT NULL AUTO_INCREMENT,
  `chat_id` int(9) NOT NULL,
  `message_from` int(9) NOT NULL,
  `message` text NOT NULL,
  `date_of` varchar(255) CHARACTER SET latin1 NOT NULL,
  `read_yet` tinyint(1) NOT NULL DEFAULT '0',
  `message_type` varchar(255) NOT NULL DEFAULT 'text-message',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=511 ;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=974 ;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tags` text COLLATE utf8_unicode_ci NOT NULL,
  `type` int(9) NOT NULL,
  `time` int(99) NOT NULL,
  `posted_by` int(9) NOT NULL,
  `file_types` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `post_views` int(99) NOT NULL,
  `disabled` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'when a post is reported too many times, we set this to true',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=187 ;

-- --------------------------------------------------------

--
-- Table structure for table `post_comments`
--

CREATE TABLE IF NOT EXISTS `post_comments` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `post_id` int(99) NOT NULL,
  `user_id` int(99) NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `time` int(99) NOT NULL,
  `upvotes` int(99) NOT NULL,
  `downvotes` int(99) NOT NULL,
  `disabled` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=460 ;

-- --------------------------------------------------------

--
-- Table structure for table `post_reports`
--

CREATE TABLE IF NOT EXISTS `post_reports` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `post_id` int(9) NOT NULL,
  `user_id` int(9) NOT NULL,
  `time` int(99) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `post_votes`
--

CREATE TABLE IF NOT EXISTS `post_votes` (
  `id` int(99) NOT NULL AUTO_INCREMENT,
  `post_id` int(99) NOT NULL,
  `user_id` int(99) NOT NULL,
  `option_index` int(9) NOT NULL,
  `time` int(99) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=132 ;

-- --------------------------------------------------------

--
-- Table structure for table `reply_upvotes_and_downvotes`
--

CREATE TABLE IF NOT EXISTS `reply_upvotes_and_downvotes` (
  `id` int(99) NOT NULL AUTO_INCREMENT,
  `comment_id` int(99) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Table structure for table `sent_files`
--

CREATE TABLE IF NOT EXISTS `sent_files` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `chat_id` int(9) NOT NULL,
  `id_of_user` int(9) NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_of` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=55 ;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `sess_id` varbinary(128) NOT NULL,
  `sess_data` blob NOT NULL,
  `sess_lifetime` mediumint(9) NOT NULL,
  `sess_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`sess_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `external_id` varchar(64) NOT NULL,
  `external_type` varchar(16) NOT NULL,
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
  `last_seen` int(99) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=96 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
