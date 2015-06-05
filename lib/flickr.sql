SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `flickr_scraper` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci ;
USE `flickr_scraper` ;

-- -----------------------------------------------------
-- Table `flickr_scraper`.`photos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flickr_scraper`.`photos` ;

CREATE  TABLE IF NOT EXISTS `flickr_scraper`.`photos` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `farm_id` INT(4) NOT NULL ,
  `server_id` INT(8) NOT NULL ,
  `photo_id` CHAR(15) NOT NULL ,
  `secret` VARCHAR(20) NOT NULL ,
  `date_taken` DATETIME NOT NULL ,
  `large` VARCHAR(255) NOT NULL ,
  `original` VARCHAR(255) NOT NULL ,
  `deleted` DATETIME NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `PHOTO_UQ` (`farm_id` ASC, `server_id` ASC, `photo_id` ASC, `secret` ASC) ,
  INDEX `DATE_TAKEN` (`date_taken` ASC, `photo_id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flickr_scraper`.`profiles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flickr_scraper`.`profiles` ;

CREATE  TABLE IF NOT EXISTS `flickr_scraper`.`profiles` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `snid` VARCHAR(255) NOT NULL ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `snid_UNIQUE` (`snid` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flickr_scraper`.`scrape_results`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flickr_scraper`.`scrape_results` ;

CREATE  TABLE IF NOT EXISTS `flickr_scraper`.`scrape_results` (
  `profile_id` INT NOT NULL ,
  `photo_id` INT NOT NULL ,
  `retrieved` DATETIME NOT NULL ,
  `viewed` DATETIME NULL ,
  PRIMARY KEY (`profile_id`, `photo_id`) ,
  INDEX `scrape_photos_fk` (`photo_id` ASC) ,
  INDEX `scrape_profiles_fk` (`profile_id` ASC) ,
  CONSTRAINT `scrape_photos_fk`
    FOREIGN KEY (`photo_id` )
    REFERENCES `flickr_scraper`.`photos` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `scrape_profiles_fk`
    FOREIGN KEY (`profile_id` )
    REFERENCES `flickr_scraper`.`profiles` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flickr_scraper`.`search_queries`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flickr_scraper`.`search_queries` ;

CREATE  TABLE IF NOT EXISTS `flickr_scraper`.`search_queries` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `search` TEXT NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flickr_scraper`.`search_results`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flickr_scraper`.`search_results` ;

CREATE  TABLE IF NOT EXISTS `flickr_scraper`.`search_results` (
  `query_id` INT NOT NULL ,
  `photo_id` INT NOT NULL ,
  `retrieved` DATETIME NOT NULL ,
  `viewed` DATETIME NULL ,
  PRIMARY KEY (`query_id`, `photo_id`) ,
  INDEX `search_queries_fk` (`query_id` ASC) ,
  INDEX `search_photos_fk` (`photo_id` ASC) ,
  CONSTRAINT `search_queries_fk`
    FOREIGN KEY (`query_id` )
    REFERENCES `flickr_scraper`.`search_queries` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `search_photos_fk`
    FOREIGN KEY (`photo_id` )
    REFERENCES `flickr_scraper`.`photos` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flickr_scraper`.`filters`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flickr_scraper`.`filters` ;

CREATE  TABLE IF NOT EXISTS `flickr_scraper`.`filters` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `field` VARCHAR(45) NOT NULL ,
  `operator` ENUM('==', '!=', '>', '<', '>=', '<=') NOT NULL DEFAULT '==' ,
  `value` TEXT NOT NULL ,
  `action` ENUM('save', 'drop') NOT NULL ,
  `priority` INT(3) NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`id`) ,
  INDEX `PRIORITY_INDEX` (`priority` ASC, `field` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flickr_scraper`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flickr_scraper`.`users` ;

CREATE  TABLE IF NOT EXISTS `flickr_scraper`.`users` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `username` VARCHAR(255) NOT NULL ,
  `passhash` CHAR(64) NOT NULL ,
  `secret` CHAR(20) NOT NULL ,
  `api_key` CHAR(40) NOT NULL ,
  `log_in` TINYINT(1) NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) ,
  UNIQUE INDEX `api_key_UNIQUE` (`api_key` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flickr_scraper`.`logs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flickr_scraper`.`logs` ;

CREATE  TABLE IF NOT EXISTS `flickr_scraper`.`logs` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `user_id` INT NOT NULL ,
  `timestamp` DATETIME NOT NULL ,
  `log_level` ENUM('error', 'warning', 'info') NOT NULL ,
  `message` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `sys_logs_users_fk` (`user_id` ASC) ,
  CONSTRAINT `sys_logs_users_fk`
    FOREIGN KEY (`user_id` )
    REFERENCES `flickr_scraper`.`users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- System user
-- Cannot log in, only used for logging
-- -----------------------------------------------------

START TRANSACTION;
USE `flickr_scraper`;
INSERT INTO `flickr_scraper`.`users` (`id`, `username`, `passhash`, `secret`, `api_key`, `log_in`) VALUES (1, 'system', '374cd9d139c966f016f006517b2f4cc4d0ff7ed9a4ed579ec31ebcd869b43e9d', 'hYt86FdsC9LkmN06TgsL', 'd249176d49d18582456cac9a2c08ef91e153554e', 0);

COMMIT;
