-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2026-05-20 15:01:00
-- 服务器版本： 5.7.43-log
-- PHP 版本： 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `wxpush_liketube_`
--

-- --------------------------------------------------------

--
-- 表的结构 `wepush_log`
--

CREATE TABLE `wepush_log` (
  `id` bigint(20) NOT NULL,
  `task_id` int(11) NOT NULL,
  `openid` varchar(100) NOT NULL,
  `status` tinyint(4) DEFAULT '0' COMMENT '1成功 2失败',
  `errcode` int(11) DEFAULT '0',
  `errmsg` varchar(255) DEFAULT '',
  `retry` int(11) DEFAULT '0',
  `send_time` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wepush_task`
--

CREATE TABLE `wepush_task` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `template_db_id` int(11) NOT NULL,
  `total` int(11) DEFAULT '0',
  `success` int(11) DEFAULT '0',
  `fail` int(11) DEFAULT '0',
  `done` int(11) DEFAULT '0',
  `qps` int(11) DEFAULT '20',
  `workers` int(11) DEFAULT '5',
  `status` tinyint(4) DEFAULT '0' COMMENT '0待开始 1发送中 2暂停 3完成 4停止',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `start_time` datetime DEFAULT NULL,
  `finish_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wepush_template`
--

CREATE TABLE `wepush_template` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `template_id` varchar(150) NOT NULL,
  `template_type` tinyint(4) DEFAULT '1' COMMENT '1一次性 2长期',
  `page` varchar(255) DEFAULT '',
  `miniprogram_state` varchar(30) DEFAULT 'formal',
  `lang` varchar(20) DEFAULT 'zh_CN',
  `data_json` text NOT NULL,
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转储表的索引
--

--
-- 表的索引 `wepush_log`
--
ALTER TABLE `wepush_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `openid` (`openid`);

--
-- 表的索引 `wepush_task`
--
ALTER TABLE `wepush_task`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `wepush_template`
--
ALTER TABLE `wepush_template`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `wepush_log`
--
ALTER TABLE `wepush_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wepush_task`
--
ALTER TABLE `wepush_task`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wepush_template`
--
ALTER TABLE `wepush_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
