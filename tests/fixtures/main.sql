
CREATE DATABASE IF NOT EXISTS `zsql`;

GRANT USAGE ON *.* TO 'zsql'@'localhost';
DROP USER 'zsql'@'localhost';

CREATE USER 'zsql'@'localhost' IDENTIFIED BY 'nopass';
GRANT ALL ON zsql.* TO 'zsql'@'localhost';

DROP TABLE IF EXISTS `zsql`.`migrationsFixtureA`;
CREATE TABLE `zsql`.`migrationsFixtureA` LIKE `zsql`.`migrations`;
INSERT INTO `zsql`.`migrationsFixtureA` VALUES ('1412129062', 'TestA', 'success');
INSERT INTO `zsql`.`migrationsFixtureA` VALUES ('1412129177', 'TestB', 'success');

DROP TABLE IF EXISTS `zsql`.`migrationsFixtureB`;
CREATE TABLE `zsql`.`migrationsFixtureB` LIKE `zsql`.`migrations`;
INSERT INTO `zsql`.`migrationsFixtureB` VALUES ('1412129062', 'TestA', 'success');

DROP TABLE IF EXISTS `zsql`.`migrationsFixtureC`;
CREATE TABLE `zsql`.`migrationsFixtureC` LIKE `zsql`.`migrations`;
INSERT INTO `zsql`.`migrationsFixtureC` VALUES ('1412129062', 'TestA', 'failed');
INSERT INTO `zsql`.`migrationsFixtureC` VALUES ('1412129177', 'TestB', 'failed');

DROP TABLE IF EXISTS `zsql`.`migrationsFixtureD`;
CREATE TABLE `zsql`.`migrationsFixtureD` LIKE `zsql`.`migrations`;
INSERT INTO `zsql`.`migrationsFixtureD` VALUES ('1412129062', 'TestA', 'success');
INSERT INTO `zsql`.`migrationsFixtureD` VALUES ('1412225787', 'TestC', 'success');
INSERT INTO `zsql`.`migrationsFixtureD` VALUES ('1412225918', 'TestD', 'initial');
INSERT INTO `zsql`.`migrationsFixtureD` VALUES ('1412227465', 'TestE', 'failed');
