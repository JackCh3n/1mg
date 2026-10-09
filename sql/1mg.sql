/*
 Navicat Premium Data Transfer

 Source Server         : loaclhost
 Source Server Type    : MySQL
 Source Server Version : 50723
 Source Host           : localhost:3306
 Source Schema         : tu

 Target Server Type    : MySQL
 Target Server Version : 50723
 File Encoding         : 65001

 Date: 30/08/2018 18:31:36
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for imginfo
-- ----------------------------
DROP TABLE IF EXISTS `imginfo`;
CREATE TABLE `imginfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(30) DEFAULT NULL,
  `ip` varchar(18) DEFAULT NULL,
  `ua` varchar(150) DEFAULT NULL,
  `date` varchar(20) DEFAULT NULL,
  `dir` varchar(25) DEFAULT NULL,
  `compress` tinyint(2) DEFAULT '0',
  `level` tinyint(2) DEFAULT '0',
  `see` tinyint(2) DEFAULT '1',
  `md5` varchar(32) DEFAULT NULL,
  `name` varchar(15) DEFAULT NULL,
  `size` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_md5` (`md5`),
  KEY `idx_date` (`date`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sm
-- ----------------------------
DROP TABLE IF EXISTS `sm`;
CREATE TABLE `sm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(25) DEFAULT NULL,
  `ua` varchar(255) DEFAULT NULL,
  `date` varchar(25) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `delete` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for admin
-- 默认账号: admin  默认密码: admin123456  (登录后请在"网站设置"中修改)
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `last_login` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `admin` (`username`, `password_hash`) VALUES
('admin', '$2y$10$R8LDJ.GfstAQcuwqjBXzjOSa8AA61uIoM1SUoc4sYVMNtFHZxQh6C');

SET FOREIGN_KEY_CHECKS = 1;
