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
) ENGINE=MyISAM AUTO_INCREMENT=1501 DEFAULT CHARSET=utf8;

CREATE INDEX students_datum_idx ON Students (datum_narodenia);
CREATE INDEX students_priezvisko_idx ON Students (priezvisko);
CREATE INDEX students_meno_idx ON Students (meno);