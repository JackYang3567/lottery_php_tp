
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;

-- -----------------------------
-- Records of `new_relationship`
-- -----------------------------
INSERT INTO `new_relationship` VALUES ('2', '38', '100,61', '66', '67');
INSERT INTO `new_relationship` VALUES ('3', '49', '50,57', '51,58', '52,59');
INSERT INTO `new_relationship` VALUES ('4', '50', '51', '52', '62');
INSERT INTO `new_relationship` VALUES ('5', '51', '52', '62', '63');
INSERT INTO `new_relationship` VALUES ('6', '53', '54', '55,60', '56');
INSERT INTO `new_relationship` VALUES ('7', '54', '55,60', '56', '64');
INSERT INTO `new_relationship` VALUES ('8', '55', '56', '64', '');
INSERT INTO `new_relationship` VALUES ('9', '57', '58', '59', '');
INSERT INTO `new_relationship` VALUES ('10', '58', '59', '', '');
INSERT INTO `new_relationship` VALUES ('22', '52', '62', '63', '');
INSERT INTO `new_relationship` VALUES ('23', '62', '63', '', '');
INSERT INTO `new_relationship` VALUES ('24', '63', '', '', '');
INSERT INTO `new_relationship` VALUES ('32', '56', '64', '', '');
INSERT INTO `new_relationship` VALUES ('33', '61', '66', '67', '');
INSERT INTO `new_relationship` VALUES ('34', '66', '67', '', '');
