ALTER TABLE `game_aks` CHANGE `name` `name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `teilnehmer` `teilnehmer` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `flotten` `flotten` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,`eingeladen` `eingeladen` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL
CREATE TABLE `titanspace1_diplo` (


CREATE TABLE `titanspace1_news` (
ALTER TABLE `game_users_valid` CHANGE `sex` `ip` VARCHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
ALTER TABLE `game_users_valid` ADD `planet` VARCHAR( 64 ) NOT NULL
ALTER TABLE `game_users_valid` ADD `planet` VARCHAR( 64 ) NOT NULL