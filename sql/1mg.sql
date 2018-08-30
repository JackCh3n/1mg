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
  `compress` tinyint(2) DEFAULT NULL,
  `level` tinyint(2) DEFAULT '0',
  `see` tinyint(2) DEFAULT '1',
  `md5` varchar(32) DEFAULT NULL,
  `name` varchar(15) DEFAULT NULL,
  `size` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
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

SET FOREIGN_KEY_CHECKS = 1;
