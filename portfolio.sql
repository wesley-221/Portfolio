-- phpMyAdmin SQL Dump
-- version 4.4.12
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Gegenereerd op: 29 jun 2016 om 14:03
-- Serverversie: 5.6.25
-- PHP-versie: 5.6.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `portfolio`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `accessibility`
--

CREATE TABLE IF NOT EXISTS `accessibility` (
  `pagename` varchar(50) NOT NULL,
  `groupid` int(11) NOT NULL DEFAULT '1',
  `loggedin` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Gegevens worden geëxporteerd voor tabel `accessibility`
--

INSERT INTO `accessibility` (`pagename`, `groupid`, `loggedin`) VALUES
('adminpanel', 2, 1),
('blog', 1, 0),
('changelog', 1, 0),
('document', 1, 1),
('editblog', 2, 1),
('editchangelog', 4, 1),
('edituploadcenter', 5, 1),
('main', 1, 0),
('makeblog', 1, 1),
('managegroups', 3, 1),
('manageusers', 2, 1),
('message', 1, 1),
('news', 1, 0),
('pageaccessibility', 10, 1),
('profiles', 1, 0),
('settings', 1, 1),
('uploadcenter', 1, 1);
-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `blog`
--

CREATE TABLE IF NOT EXISTS `blog` (
  `blogid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `blogname` varchar(50) NOT NULL,
  `blogdescription` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `sticky` int(11) NOT NULL,
  `postdate` date NOT NULL,
  `temp_id` varchar(15) NOT NULL DEFAULT 'UNDEFINED'
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `blogresponse`
--

CREATE TABLE IF NOT EXISTS `blogresponse` (
  `blogresponseid` int(11) NOT NULL,
  `blogid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `reblog` tinyint(1) NOT NULL,
  `bloglike` tinyint(1) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `changelog`
--

CREATE TABLE IF NOT EXISTS `changelog` (
  `changelogid` int(11) NOT NULL,
  `versionname` varchar(25) NOT NULL,
  `userid` int(11) NOT NULL COMMENT 'commiter id',
  `type` varchar(10) NOT NULL COMMENT 'fix/update',
  `text` varchar(200) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=latin1;
-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cookies`
--

CREATE TABLE IF NOT EXISTS `cookies` (
  `cookieid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `cookie` varchar(128) NOT NULL,
  `hostname` varchar(50) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=latin1;
-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `friends`
--

CREATE TABLE IF NOT EXISTS `friends` (
  `userfriend` int(11) NOT NULL COMMENT 'the user that is is friends with another user',
  `otheruser` int(11) NOT NULL COMMENT 'the friend',
  `notification` int(11) NOT NULL,
  `accepted` int(11) NOT NULL,
  `safetycheck` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `groupid` int(11) NOT NULL,
  `groupname` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Gegevens worden geëxporteerd voor tabel `groups`
--

INSERT INTO `groups` (`groupid`, `groupname`) VALUES
(1, 'user'),
(2, 'moderator'),
(3, 'administrator'),
(4, 'developer'),
(10, 'owner'),
(11, 'PortfolioBot');
-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `messageid` int(11) NOT NULL,
  `senderid` int(11) NOT NULL,
  `receiverid` int(11) NOT NULL,
  `subject` varchar(25) NOT NULL,
  `message` text NOT NULL,
  `date` datetime NOT NULL,
  `messageread` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=836 DEFAULT CHARSET=latin1;
-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `uploads`
--

CREATE TABLE IF NOT EXISTS `uploads` (
  `uploadid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `uploadname` varchar(50) NOT NULL,
  `originalname` varchar(50) NOT NULL,
  `passwordprotected` int(1) NOT NULL DEFAULT '0',
  `password` varchar(128) NOT NULL,
  `postdate` date NOT NULL,
  `softdelete` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=329 DEFAULT CHARSET=latin1;
-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userid` int(11) NOT NULL,
  `username` varchar(25) NOT NULL,
  `firstname` varchar(25) NOT NULL,
  `lastname` varchar(25) NOT NULL,
  `email` varchar(50) NOT NULL,
  `interests` varchar(75) NOT NULL,
  `aboutme` text NOT NULL,
  `countrycode` varchar(5) NOT NULL DEFAULT 'NL',
  `theme` varchar(20) NOT NULL DEFAULT 'style-dark.css',
  `visibility` varchar(8) NOT NULL DEFAULT 'public',
  `showemail` varchar(4) NOT NULL DEFAULT 'no',
  `showblogs` varchar(8) NOT NULL DEFAULT 'public',
  `showuploads` varchar(8) DEFAULT 'public',
  `ordermessage` varchar(25) NOT NULL DEFAULT 'messageid' COMMENT 'messageread, date, userid, messageid',
  `joindate` date NOT NULL,
  `lastseen` datetime NOT NULL,
  `pageviews` int(11) NOT NULL DEFAULT '0',
  `groupid` int(11) NOT NULL DEFAULT '1',
  `salt` varchar(50) NOT NULL DEFAULT 'UNDEFINED',
  `password` varchar(128) NOT NULL DEFAULT 'UNDEFINED'
) ENGINE=InnoDB AUTO_INCREMENT=166 DEFAULT CHARSET=latin1;
-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `versions`
--

CREATE TABLE IF NOT EXISTS `versions` (
  `versionid` int(11) NOT NULL,
  `versionname` varchar(25) NOT NULL,
  `creatorid` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Indexen voor tabel `accessibility`
--
ALTER TABLE `accessibility`
  ADD PRIMARY KEY (`pagename`);

--
-- Indexen voor tabel `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`blogid`,`userid`);

--
-- Indexen voor tabel `blogresponse`
--
ALTER TABLE `blogresponse`
  ADD PRIMARY KEY (`blogresponseid`,`blogid`,`userid`);

--
-- Indexen voor tabel `changelog`
--
ALTER TABLE `changelog`
  ADD PRIMARY KEY (`changelogid`);

--
-- Indexen voor tabel `cookies`
--
ALTER TABLE `cookies`
  ADD PRIMARY KEY (`cookieid`,`userid`,`cookie`);

--
-- Indexen voor tabel `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`userfriend`,`otheruser`);

--
-- Indexen voor tabel `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`groupid`);

--
-- Indexen voor tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`messageid`,`senderid`,`receiverid`);

--
-- Indexen voor tabel `uploads`
--
ALTER TABLE `uploads`
  ADD PRIMARY KEY (`uploadid`,`userid`,`uploadname`);

--
-- Indexen voor tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userid`);

--
-- Indexen voor tabel `versions`
--
ALTER TABLE `versions`
  ADD PRIMARY KEY (`versionid`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `blog`
--
ALTER TABLE `blog`
  MODIFY `blogid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT voor een tabel `blogresponse`
--
ALTER TABLE `blogresponse`
  MODIFY `blogresponseid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT voor een tabel `changelog`
--
ALTER TABLE `changelog`
  MODIFY `changelogid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=122;
--
-- AUTO_INCREMENT voor een tabel `cookies`
--
ALTER TABLE `cookies`
  MODIFY `cookieid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=50;
--
-- AUTO_INCREMENT voor een tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `messageid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=836;
--
-- AUTO_INCREMENT voor een tabel `uploads`
--
ALTER TABLE `uploads`
  MODIFY `uploadid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=329;
--
-- AUTO_INCREMENT voor een tabel `users`
--
ALTER TABLE `users`
  MODIFY `userid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=166;
--
-- AUTO_INCREMENT voor een tabel `versions`
--
ALTER TABLE `versions`
  MODIFY `versionid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
