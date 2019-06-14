
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
  `rebate_way` tinyint(1) DEFAULT '1' COMMENT '0盈亏  1:流水',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='普通用户代理表';

-- -----------------------------
-- Records of `new_relationship`
-- -----------------------------
INSERT INTO `new_relationship` VALUES ('1', '42', '118', '119,120,121', '122', '1');
INSERT INTO `new_relationship` VALUES ('2', '118', '119,120,121', '122', '', '1');
INSERT INTO `new_relationship` VALUES ('3', '120', '122', '', '', '1');
INSERT INTO `new_relationship` VALUES ('4', '178', '218,42', '118', '119,120,121', '1');
