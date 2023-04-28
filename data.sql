-- phpMyAdmin SQL Dump
-- version 4.6.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 18, 2016 at 10:30 AM
-- Server version: 10.1.10-MariaDB
-- PHP Version: 5.5.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `oldversion`
--

-- --------------------------------------------------------

--
-- Table structure for table `cms_ads`
--

CREATE TABLE `cms_ads` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `view` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `layout` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `count_link` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` text NOT NULL,
  `link` text NOT NULL,
  `to` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `color` varchar(10) NOT NULL DEFAULT '',
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `day` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `mesto` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `bold` tinyint(1) NOT NULL DEFAULT '0',
  `italic` tinyint(1) NOT NULL DEFAULT '0',
  `underline` tinyint(1) NOT NULL DEFAULT '0',
  `show` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_album_cat`
--

CREATE TABLE `cms_album_cat` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(40) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `password` varchar(20) NOT NULL DEFAULT '',
  `access` tinyint(4) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_album_comments`
--

CREATE TABLE `cms_album_comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `sub_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `reply` text NOT NULL,
  `attributes` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_album_downloads`
--

CREATE TABLE `cms_album_downloads` (
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `file_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_album_files`
--

CREATE TABLE `cms_album_files` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `album_id` int(10) UNSIGNED NOT NULL,
  `description` text NOT NULL,
  `img_name` varchar(100) NOT NULL DEFAULT '',
  `tmb_name` varchar(100) NOT NULL DEFAULT '',
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `comments` tinyint(1) NOT NULL DEFAULT '1',
  `comm_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `access` tinyint(4) UNSIGNED NOT NULL DEFAULT '0',
  `vote_plus` int(11) NOT NULL DEFAULT '0',
  `vote_minus` int(11) NOT NULL DEFAULT '0',
  `views` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `downloads` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `unread_comments` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_album_views`
--

CREATE TABLE `cms_album_views` (
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `file_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_album_votes`
--

CREATE TABLE `cms_album_votes` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `file_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `vote` tinyint(2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_ban_ip`
--

CREATE TABLE `cms_ban_ip` (
  `id` int(10) UNSIGNED NOT NULL,
  `ip1` bigint(11) NOT NULL DEFAULT '0',
  `ip2` bigint(11) NOT NULL DEFAULT '0',
  `ban_type` tinyint(4) NOT NULL DEFAULT '0',
  `link` varchar(100) NOT NULL,
  `who` varchar(25) NOT NULL,
  `reason` text NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_ban_users`
--

CREATE TABLE `cms_ban_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ban_time` int(11) NOT NULL DEFAULT '0',
  `ban_while` int(11) NOT NULL DEFAULT '0',
  `ban_type` tinyint(4) NOT NULL DEFAULT '1',
  `ban_who` varchar(30) NOT NULL DEFAULT '',
  `ban_ref` int(11) NOT NULL DEFAULT '0',
  `ban_reason` text NOT NULL,
  `ban_raz` varchar(30) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_chat`
--

CREATE TABLE `cms_chat` (
  `id` int(10) NOT NULL,
  `uid` int(10) NOT NULL DEFAULT '0',
  `text` varchar(500) NOT NULL DEFAULT '',
  `time` int(10) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cms_chat`
--

INSERT INTO `cms_chat` (`id`, `uid`, `text`, `time`) VALUES
(1, 1, 'Test thôi mà', 1466691081);

-- --------------------------------------------------------

--
-- Table structure for table `cms_contact`
--

CREATE TABLE `cms_contact` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `from_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `friends` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `ban` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `man` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_forum_files`
--

CREATE TABLE `cms_forum_files` (
  `id` int(10) UNSIGNED NOT NULL,
  `cat` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `subcat` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `topic` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `post` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `filename` text NOT NULL,
  `filetype` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `dlcount` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `del` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_forum_rdm`
--

CREATE TABLE `cms_forum_rdm` (
  `topic_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cms_forum_rdm`
--

INSERT INTO `cms_forum_rdm` (`topic_id`, `user_id`, `time`) VALUES
(3, 1, 1466692088);

-- --------------------------------------------------------

--
-- Table structure for table `cms_forum_vote`
--

CREATE TABLE `cms_forum_vote` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` int(2) NOT NULL DEFAULT '0',
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `topic` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL,
  `count` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_forum_vote_users`
--

CREATE TABLE `cms_forum_vote_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `user` int(11) NOT NULL DEFAULT '0',
  `topic` int(11) NOT NULL,
  `vote` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_library_comments`
--

CREATE TABLE `cms_library_comments` (
  `id` int(11) UNSIGNED NOT NULL,
  `sub_id` int(11) UNSIGNED NOT NULL,
  `time` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `text` text NOT NULL,
  `reply` text,
  `attributes` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_library_rating`
--

CREATE TABLE `cms_library_rating` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `st_id` int(11) NOT NULL,
  `point` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_likes`
--

CREATE TABLE `cms_likes` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `sub_id` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_log`
--

CREATE TABLE `cms_log` (
  `time` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `uid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `text` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_mail`
--

CREATE TABLE `cms_mail` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `from_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `read` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `sys` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `delete` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `file_name` varchar(100) NOT NULL DEFAULT '',
  `count` int(10) NOT NULL DEFAULT '0',
  `size` int(10) NOT NULL DEFAULT '0',
  `them` varchar(100) NOT NULL DEFAULT '',
  `spam` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_sessions`
--

CREATE TABLE `cms_sessions` (
  `session_id` char(32) NOT NULL DEFAULT '',
  `ip` bigint(11) NOT NULL DEFAULT '0',
  `ip_via_proxy` bigint(11) NOT NULL DEFAULT '0',
  `browser` varchar(255) NOT NULL DEFAULT '',
  `lastdate` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `sestime` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `views` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `movings` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `place` varchar(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_settings`
--

CREATE TABLE `cms_settings` (
  `key` tinytext NOT NULL,
  `val` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cms_settings`
--

INSERT INTO `cms_settings` (`key`, `val`) VALUES
('active', '1'),
('admp', 'admin'),
('antiflood', 'a:5:{s:4:"mode";i:1;s:3:"day";i:4;s:5:"night";i:10;s:7:"dayfrom";i:10;s:5:"dayto";i:22;}'),
('clean_time', '1468810376'),
('copyright', 'Diễn đàn giải trí tổng hợp'),
('email', 'hanh94hut@gmail.com'),
('flsz', '4000'),
('gzip', '1'),
('lng', ''),
('lng_list', 'a:1:{i:0;N;}'),
('meta_desc', 'Diễn đàn giải trí tổng hợp dành cho giới trẻ'),
('meta_key', 'giải trí, chat chit, chém gió, kết bạn'),
('mod_forum', '2'),
('mod_lib', '2'),
('mod_lib_comm', '1'),
('mod_reg', '1'),
('news', 'a:8:{s:4:"view";i:1;s:4:"size";i:255;s:8:"quantity";i:2;s:4:"days";i:0;s:6:"breaks";b:1;s:7:"smileys";b:1;s:4:"tags";b:1;s:3:"kom";b:1;}'),
('offer', '0'),
('cat_friends', ''),
('site_access', '2'),
('skindef', 'default'),
('timeshift', '7');

-- --------------------------------------------------------

--
-- Table structure for table `cms_users_data`
--

CREATE TABLE `cms_users_data` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `key` varchar(30) NOT NULL DEFAULT '',
  `val` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_users_guestbook`
--

CREATE TABLE `cms_users_guestbook` (
  `id` int(10) UNSIGNED NOT NULL,
  `sub_id` int(10) UNSIGNED NOT NULL,
  `time` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `text` text NOT NULL,
  `reply` text NOT NULL,
  `attributes` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_users_iphistory`
--

CREATE TABLE `cms_users_iphistory` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `ip` bigint(11) NOT NULL DEFAULT '0',
  `ip_via_proxy` bigint(11) NOT NULL DEFAULT '0',
  `time` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `farm_area`
--

CREATE TABLE `farm_area` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `item_id` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `end_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `water_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `grass` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `pest` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `ns` tinyint(3) UNSIGNED NOT NULL DEFAULT '100'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `farm_item`
--

CREATE TABLE `farm_item` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(64) NOT NULL DEFAULT '',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `price` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `currency` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `min` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `max` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `cost` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `level` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `farm_item`
--

INSERT INTO `farm_item` (`id`, `name`, `type`, `price`, `currency`, `min`, `max`, `cost`, `time`, `level`) VALUES
(1, 'Khế', 0, 0, 0, 0, 100, 10, 28800, 13),
(2, 'Cà chua', 1, 10, 0, 10, 75, 1, 14400, 1),
(3, 'Cà rốt', 1, 10, 0, 10, 108, 1, 21600, 1),
(4, 'Khóm', 1, 10, 0, 10, 165, 1, 36000, 1),
(5, 'Dưa hấu', 1, 10, 0, 10, 138, 1, 28800, 1),
(6, 'Nho', 1, 10, 0, 10, 240, 1, 57600, 1),
(7, 'Hoa hồng', 1, 10, 0, 10, 45, 1, 7200, 1),
(8, 'Lúa', 1, 10, 0, 10, 720, 1, 172800, 1),
(9, 'Xoài', 1, 10, 0, 10, 360, 1, 86400, 1),
(10, 'Thanh long', 1, 10, 0, 10, 189, 1, 43200, 1),
(11, 'Hướng dương', 1, 10, 0, 10, 189, 1, 43200, 1),
(12, 'Hoa tulip', 1, 10, 0, 10, 108, 1, 21600, 1);

-- --------------------------------------------------------

--
-- Table structure for table `farm_warehouse`
--

CREATE TABLE `farm_warehouse` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `item_id` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `count` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `forum`
--

CREATE TABLE `forum` (
  `id` int(10) UNSIGNED NOT NULL,
  `refid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `type` char(1) NOT NULL DEFAULT '',
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `from` varchar(25) NOT NULL DEFAULT '',
  `realid` int(3) NOT NULL DEFAULT '0',
  `ip` bigint(11) NOT NULL DEFAULT '0',
  `ip_via_proxy` bigint(11) NOT NULL DEFAULT '0',
  `soft` varchar(255) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `prefix` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `close` tinyint(1) NOT NULL DEFAULT '0',
  `close_who` varchar(25) NOT NULL DEFAULT '',
  `vip` tinyint(1) NOT NULL DEFAULT '0',
  `edit` varchar(32) NOT NULL DEFAULT '',
  `tedit` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `curators` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `forum`
--

INSERT INTO `forum` (`id`, `refid`, `type`, `time`, `user_id`, `from`, `realid`, `ip`, `ip_via_proxy`, `soft`, `text`, `prefix`, `close`, `close_who`, `vip`, `edit`, `tedit`, `curators`) VALUES
(1, 0, 'f', 0, 0, '', 1, 0, 0, 'Your First Category', 'Demo Category', 0, 0, '', 0, '0', 0, ''),
(2, 1, 'r', 0, 0, '', 1, 0, 0, 'Your First Forum', 'Demo Forum', 0, 0, '', 0, '0', 0, ''),
(3, 2, 't', 1466692088, 1, 'admin', 0, 0, 0, 'a:3:{i:0;s:10:"phonho.net";i:1;s:5:"mrken";i:2;s:7:"johncms";}', 'Your first topic', 0, 0, '', 1, '', 0, ''),
(4, 3, 'm', 1466692088, 1, 'admin', 0, 2130706433, 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.63 Safari/537.36', 'Xin chào, đây là chủ đề thử nghiệm cho code phonho.net phiên bản 2.\n\nBạn có thể thay đổi hoặc chỉnh sửa tùy ý!\n\nTruy cập http://phonho.net để biết thêm chi tiết', 0, 0, '', 0, '', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `library_cats`
--

CREATE TABLE `library_cats` (
  `id` int(10) UNSIGNED NOT NULL,
  `parent` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `dir` tinyint(1) NOT NULL DEFAULT '0',
  `pos` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_add` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `library_tags`
--

CREATE TABLE `library_tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `lib_text_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `tag_name` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `library_texts`
--

CREATE TABLE `library_texts` (
  `id` int(10) UNSIGNED NOT NULL,
  `cat_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `text` mediumtext NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `announce` text,
  `uploader` varchar(100) NOT NULL DEFAULT '',
  `uploader_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `count_views` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `premod` tinyint(1) NOT NULL DEFAULT '0',
  `comments` tinyint(1) NOT NULL DEFAULT '0',
  `count_comments` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(10) UNSIGNED NOT NULL,
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `avt` varchar(25) NOT NULL DEFAULT '',
  `name` text NOT NULL,
  `text` text NOT NULL,
  `kom` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `time`, `avt`, `name`, `text`, `kom`) VALUES
(1, 1466691132, 'admin', 'Cài đặt thành công mã nguồn PhoNho.Net', 'Chúc mừng bạn đã cài đặt thành công mã nguồn JohnCMS mod bởi MrKen tại http://phonho.net!\n\nChúc vui!', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `account` varchar(32) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `fb_id` varchar(32) NOT NULL DEFAULT '',
  `rights` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `failed_login` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `imname` varchar(50) NOT NULL DEFAULT '',
  `sex` varchar(2) NOT NULL DEFAULT '',
  `coin` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `gold` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `xu` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `luong` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `vip_exp` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `komm` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `postforum` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `dayb` int(2) NOT NULL DEFAULT '0',
  `monthb` int(2) NOT NULL DEFAULT '0',
  `yearb` int(4) NOT NULL DEFAULT '0',
  `datereg` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `lastdate` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `mail` varchar(50) NOT NULL DEFAULT '',
  `skype` varchar(50) NOT NULL DEFAULT '',
  `facebook` varchar(50) NOT NULL DEFAULT '',
  `about` text NOT NULL,
  `live` varchar(100) NOT NULL DEFAULT '',
  `mobile` varchar(20) NOT NULL DEFAULT '',
  `status` varchar(100) NOT NULL DEFAULT '',
  `ip` bigint(11) NOT NULL DEFAULT '0',
  `ip_via_proxy` bigint(11) NOT NULL DEFAULT '0',
  `browser` text NOT NULL,
  `preg` tinyint(1) NOT NULL DEFAULT '0',
  `regadm` varchar(25) NOT NULL DEFAULT '',
  `mailvis` tinyint(1) NOT NULL DEFAULT '0',
  `sestime` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `total_on_site` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `lastpost` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `rest_code` varchar(32) NOT NULL DEFAULT '',
  `rest_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `movings` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `place` varchar(30) NOT NULL DEFAULT '',
  `set_user` text NOT NULL,
  `set_mail` text NOT NULL,
  `comm_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `comm_old` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `priv_key` varchar(16) NOT NULL DEFAULT '',
  `day_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `sft_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `sft_level` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `gift` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `received` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `account`, `password`, `fb_id`, `rights`, `failed_login`, `imname`, `sex`, `coin`, `gold`, `xu`, `luong`, `vip_exp`, `komm`, `postforum`, `dayb`, `monthb`, `yearb`, `datereg`, `lastdate`, `mail`, `skype`, `facebook`, `about`, `live`, `mobile`, `status`, `ip`, `ip_via_proxy`, `browser`, `preg`, `regadm`, `mailvis`, `sestime`, `total_on_site`, `lastpost`, `rest_code`, `rest_time`, `movings`, `place`, `set_user`, `set_mail`, `comm_count`, `comm_old`, `priv_key`, `day_time`, `sft_time`, `sft_level`, `gift`, `received`) VALUES
(1, 'admin', '14e1b600b1fd579f47433b88e8d85291', '', 9, 1, 'A đờ min', 'm', 186, 0, 4010, 0, 0, 0, 1, 0, 0, 0, 1466691019, 1468830626, '', '', '', 'A đờ min', '', '', '', 2130706433, 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.63 Safari/537.36', 1, '', 0, 1468830316, 910, 1466692088, '', 0, 2, 'mainpage', '', '', 0, 0, '1468810376@HeV43', 1468810376, 1468830422, 1, 0, 0),
(2, 'BOT', '894c925e9616baf4484f6fccbf9013c', '', 0, 0, 'BOT', 'm', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1466691334, 1468830583, '', '', '', 'Smart BOT', '', '', '', 2130706433, 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.63 Safari/537.36', 1, '', 0, 1466691334, 0, 0, '', 0, 0, '', '', '', 0, 0, '', 0, 0, 1, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cms_ads`
--
ALTER TABLE `cms_ads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cms_album_cat`
--
ALTER TABLE `cms_album_cat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `access` (`access`);

--
-- Indexes for table `cms_album_comments`
--
ALTER TABLE `cms_album_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sub_id` (`sub_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cms_album_downloads`
--
ALTER TABLE `cms_album_downloads`
  ADD PRIMARY KEY (`user_id`,`file_id`);

--
-- Indexes for table `cms_album_files`
--
ALTER TABLE `cms_album_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `album_id` (`album_id`),
  ADD KEY `access` (`access`);

--
-- Indexes for table `cms_album_views`
--
ALTER TABLE `cms_album_views`
  ADD PRIMARY KEY (`user_id`,`file_id`);

--
-- Indexes for table `cms_album_votes`
--
ALTER TABLE `cms_album_votes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `file_id` (`file_id`);

--
-- Indexes for table `cms_ban_ip`
--
ALTER TABLE `cms_ban_ip`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip1` (`ip1`),
  ADD UNIQUE KEY `ip2` (`ip2`);

--
-- Indexes for table `cms_ban_users`
--
ALTER TABLE `cms_ban_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ban_time` (`ban_time`);

--
-- Indexes for table `cms_chat`
--
ALTER TABLE `cms_chat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cms_contact`
--
ALTER TABLE `cms_contact`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_user` (`user_id`,`from_id`),
  ADD KEY `time` (`time`),
  ADD KEY `ban` (`ban`);

--
-- Indexes for table `cms_forum_files`
--
ALTER TABLE `cms_forum_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cat` (`cat`),
  ADD KEY `subcat` (`subcat`),
  ADD KEY `topic` (`topic`),
  ADD KEY `post` (`post`);

--
-- Indexes for table `cms_forum_rdm`
--
ALTER TABLE `cms_forum_rdm`
  ADD PRIMARY KEY (`topic_id`,`user_id`),
  ADD KEY `time` (`time`);

--
-- Indexes for table `cms_forum_vote`
--
ALTER TABLE `cms_forum_vote`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`),
  ADD KEY `topic` (`topic`);

--
-- Indexes for table `cms_forum_vote_users`
--
ALTER TABLE `cms_forum_vote_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic` (`topic`);

--
-- Indexes for table `cms_library_comments`
--
ALTER TABLE `cms_library_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sub_id` (`sub_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cms_library_rating`
--
ALTER TABLE `cms_library_rating`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`st_id`);

--
-- Indexes for table `cms_likes`
--
ALTER TABLE `cms_likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cms_mail`
--
ALTER TABLE `cms_mail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `from_id` (`from_id`),
  ADD KEY `time` (`time`),
  ADD KEY `read` (`read`),
  ADD KEY `sys` (`sys`),
  ADD KEY `delete` (`delete`);

--
-- Indexes for table `cms_sessions`
--
ALTER TABLE `cms_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `lastdate` (`lastdate`),
  ADD KEY `place` (`place`(10));

--
-- Indexes for table `cms_settings`
--
ALTER TABLE `cms_settings`
  ADD PRIMARY KEY (`key`(30));

--
-- Indexes for table `cms_users_data`
--
ALTER TABLE `cms_users_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `key` (`key`);

--
-- Indexes for table `cms_users_guestbook`
--
ALTER TABLE `cms_users_guestbook`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sub_id` (`sub_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cms_users_iphistory`
--
ALTER TABLE `cms_users_iphistory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `user_ip` (`ip`);

--
-- Indexes for table `farm_area`
--
ALTER TABLE `farm_area`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `farm_item`
--
ALTER TABLE `farm_item`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `farm_warehouse`
--
ALTER TABLE `farm_warehouse`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forum`
--
ALTER TABLE `forum`
  ADD PRIMARY KEY (`id`),
  ADD KEY `refid` (`refid`),
  ADD KEY `type` (`type`),
  ADD KEY `time` (`time`),
  ADD KEY `close` (`close`),
  ADD KEY `user_id` (`user_id`);
ALTER TABLE `forum` ADD FULLTEXT KEY `text` (`text`);

--
-- Indexes for table `library_cats`
--
ALTER TABLE `library_cats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `library_tags`
--
ALTER TABLE `library_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lib_text_id` (`lib_text_id`),
  ADD KEY `tag_name` (`tag_name`);

--
-- Indexes for table `library_texts`
--
ALTER TABLE `library_texts`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `library_texts` ADD FULLTEXT KEY `text` (`text`,`name`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name_lat` (`account`),
  ADD KEY `lastdate` (`lastdate`),
  ADD KEY `place` (`place`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cms_ads`
--
ALTER TABLE `cms_ads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_album_cat`
--
ALTER TABLE `cms_album_cat`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_album_comments`
--
ALTER TABLE `cms_album_comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_album_files`
--
ALTER TABLE `cms_album_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_album_votes`
--
ALTER TABLE `cms_album_votes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_ban_ip`
--
ALTER TABLE `cms_ban_ip`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_ban_users`
--
ALTER TABLE `cms_ban_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_chat`
--
ALTER TABLE `cms_chat`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `cms_contact`
--
ALTER TABLE `cms_contact`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_forum_files`
--
ALTER TABLE `cms_forum_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_forum_vote`
--
ALTER TABLE `cms_forum_vote`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_forum_vote_users`
--
ALTER TABLE `cms_forum_vote_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_library_comments`
--
ALTER TABLE `cms_library_comments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_library_rating`
--
ALTER TABLE `cms_library_rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_likes`
--
ALTER TABLE `cms_likes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_mail`
--
ALTER TABLE `cms_mail`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_users_data`
--
ALTER TABLE `cms_users_data`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_users_guestbook`
--
ALTER TABLE `cms_users_guestbook`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cms_users_iphistory`
--
ALTER TABLE `cms_users_iphistory`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `farm_area`
--
ALTER TABLE `farm_area`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `farm_item`
--
ALTER TABLE `farm_item`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `farm_warehouse`
--
ALTER TABLE `farm_warehouse`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `forum`
--
ALTER TABLE `forum`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `library_cats`
--
ALTER TABLE `library_cats`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `library_tags`
--
ALTER TABLE `library_tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `library_texts`
--
ALTER TABLE `library_texts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
