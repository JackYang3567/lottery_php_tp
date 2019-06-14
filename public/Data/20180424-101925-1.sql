
-- -----------------------------
-- Table structure for `new_relationship`
-- -----------------------------
DROP TABLE IF EXISTS `new_relationship`;
CREATE TABLE `new_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `child_one` text COMMENT '第一下级',
  `child_two` text COMMENT '第二下级',
  `child_three` text COMMENT '第三下级',
  `rebate_way` tinyint(1) DEFAULT '0' COMMENT '0盈亏  1:流水',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

-- -----------------------------
-- Records of `new_relationship`
-- -----------------------------
INSERT INTO `new_relationship` VALUES ('2', '38', '100', '', '', '0');
