CREATE TABLE `Students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meno` varchar(255) CHARACTER SET utf8 COLLATE utf8_slovak_ci NOT NULL,
  `priezvisko` varchar(255) CHARACTER SET utf8 COLLATE utf8_slovak_ci NOT NULL,
  `datum_narodenia` date NOT NULL,
  `priemer1` double DEFAULT NULL,
  `priemer2` double DEFAULT NULL,
  `forma_studia` varchar(10) CHARACTER SET utf8 COLLATE utf8_slovak_ci DEFAULT NULL,
  `pid` varchar(20) DEFAULT NULL,
  `time_of_registration` datetime DEFAULT NULL,
  `last_time_edit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `printed` tinyint(1) NOT NULL,
  `exported` tinyint(1) NOT NULL,
  `edited_by` varchar(255) CHARACTER SET utf8 COLLATE utf8_slovak_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE INDEX students_datum_idx ON Students (datum_narodenia);
CREATE INDEX students_priezvisko_idx ON Students (priezvisko);
CREATE INDEX students_meno_idx ON Students (meno);


CREATE TABLE IF NOT EXISTS `Log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(10) COLLATE utf8_slovak_ci NOT NULL,
  `changed_item` varchar(255) COLLATE utf8_slovak_ci DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `student_name` varchar(255) COLLATE utf8_slovak_ci DEFAULT NULL,
  `new_value` text COLLATE utf8_slovak_ci,
  `user` varchar(100) COLLATE utf8_slovak_ci NOT NULL,
  `time_of_edit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci AUTO_INCREMENT=12 ;
