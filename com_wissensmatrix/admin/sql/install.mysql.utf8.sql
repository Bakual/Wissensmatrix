DROP TABLE IF EXISTS `#__wissensmatrix_erfahrung`;
DROP TABLE IF EXISTS `#__wissensmatrix_mitarbeiter`;
DROP TABLE IF EXISTS `#__wissensmatrix_weiterbildung`;
DROP TABLE IF EXISTS `#__wissensmatrix_weiterbildunggruppe`;
DROP TABLE IF EXISTS `#__wissensmatrix_fachwissen`;
DROP TABLE IF EXISTS `#__wissensmatrix_fachwissengruppe`;
DROP TABLE IF EXISTS `#__wissensmatrix_mit_fwi`;
DROP TABLE IF EXISTS `#__wissensmatrix_mit_wbi`;

CREATE TABLE `#__wissensmatrix_erfahrung` (
  `id`               INT(10)      NOT NULL AUTO_INCREMENT,
  `value`            INT(5)       NOT NULL,
  `title`            VARCHAR(50)  NOT NULL DEFAULT '',
  `alias`            VARCHAR(255) NOT NULL,
  `state`            TINYINT(3)   NOT NULL DEFAULT '0',
  `ordering`         INT(11)      NOT NULL DEFAULT '0',
  `hits`             INT(10)      NOT NULL DEFAULT '0',
  `created`          DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by`       INT(10)      NOT NULL DEFAULT '0',
  `modified`         DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`      INT(10)      NOT NULL DEFAULT '0',
  `catid`            INT(10)      NOT NULL DEFAULT '0',
  `checked_out`      INT(11)      NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language`         CHAR(7)      NOT NULL DEFAULT '*',
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  AUTO_INCREMENT = 0
  DEFAULT CHARSET = utf8;

CREATE TABLE `#__wissensmatrix_mitarbeiter` (
  `id`               INT(10)      NOT NULL AUTO_INCREMENT,
  `uid`              VARCHAR(7)   NOT NULL,
  `name`             VARCHAR(100) NOT NULL DEFAULT '',
  `vorname`          VARCHAR(100) NOT NULL DEFAULT '',
  `geb`              DATE                  DEFAULT NULL,
  `eintritt`         DATE                  DEFAULT NULL,
  `template_id`      INT(10)      NOT NULL,
  `alias`            VARCHAR(255) NOT NULL,
  `state`            TINYINT(3)   NOT NULL DEFAULT '0',
  `ordering`         INT(11)      NOT NULL DEFAULT '0',
  `hits`             INT(10)      NOT NULL DEFAULT '0',
  `created`          DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by`       INT(10)      NOT NULL DEFAULT '0',
  `modified`         DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`      INT(10)      NOT NULL DEFAULT '0',
  `catid`            INT(10)      NOT NULL DEFAULT '0',
  `checked_out`      INT(11)      NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language`         CHAR(7)      NOT NULL DEFAULT '*',
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  AUTO_INCREMENT = 0
  DEFAULT CHARSET = utf8;

CREATE TABLE `#__wissensmatrix_weiterbildung` (
  `id`               INT(10)      NOT NULL AUTO_INCREMENT,
  `sbb_nr`           VARCHAR(11)  NOT NULL,
  `title_de`         VARCHAR(100) NOT NULL,
  `title_fr`         VARCHAR(100) NOT NULL,
  `title_it`         VARCHAR(100) NOT NULL,
  `refresh`          INT(2)       NOT NULL DEFAULT '0',
  `wbig_id`          INT(10)               DEFAULT NULL,
  `alias`            VARCHAR(255) NOT NULL,
  `state`            TINYINT(3)   NOT NULL DEFAULT '0',
  `ordering`         INT(11)      NOT NULL DEFAULT '0',
  `hits`             INT(10)      NOT NULL DEFAULT '0',
  `created`          DATETIME     NULL     DEFAULT '0000-00-00 00:00:00',
  `created_by`       INT(10)      NOT NULL DEFAULT '0',
  `modified`         DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`      INT(10)      NOT NULL DEFAULT '0',
  `catid`            INT(10)      NOT NULL DEFAULT '0',
  `checked_out`      INT(11)      NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language`         CHAR(7)      NOT NULL DEFAULT '*',
  `relevant`         TINYINT(1)   NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  AUTO_INCREMENT = 0
  DEFAULT CHARSET = utf8;

CREATE TABLE `#__wissensmatrix_weiterbildunggruppe` (
  `id`               INT(10)      NOT NULL AUTO_INCREMENT,
  `title_de`         VARCHAR(100) NOT NULL,
  `title_fr`         VARCHAR(100) NOT NULL,
  `title_it`         VARCHAR(100) NOT NULL,
  `alias`            VARCHAR(255) NOT NULL,
  `state`            TINYINT(3)   NOT NULL DEFAULT '0',
  `ordering`         INT(11)      NOT NULL DEFAULT '0',
  `hits`             INT(10)      NOT NULL DEFAULT '0',
  `created`          DATETIME     NULL     DEFAULT '0000-00-00 00:00:00',
  `created_by`       INT(10)      NOT NULL DEFAULT '0',
  `modified`         DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`      INT(10)      NOT NULL DEFAULT '0',
  `catid`            INT(10)      NOT NULL DEFAULT '0',
  `checked_out`      INT(11)      NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language`         CHAR(7)      NOT NULL DEFAULT '*',
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  AUTO_INCREMENT = 0
  DEFAULT CHARSET = utf8;

CREATE TABLE `#__wissensmatrix_fachwissen` (
  `id`               INT(10)      NOT NULL AUTO_INCREMENT,
  `title_de`         VARCHAR(300) NOT NULL,
  `title_fr`         VARCHAR(300) NOT NULL,
  `title_it`         VARCHAR(300) NOT NULL,
  `fwig_id`          INT(10)      NOT NULL DEFAULT '0',
  `alias`            VARCHAR(255) NOT NULL,
  `state`            TINYINT(3)   NOT NULL DEFAULT '0',
  `ordering`         INT(11)      NOT NULL DEFAULT '0',
  `hits`             INT(10)      NOT NULL DEFAULT '0',
  `created`          DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by`       INT(10)      NOT NULL DEFAULT '0',
  `modified`         DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`      INT(10)      NOT NULL DEFAULT '0',
  `catid`            INT(10)      NOT NULL DEFAULT '0',
  `checked_out`      INT(11)      NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language`         CHAR(7)      NOT NULL DEFAULT '*',
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  AUTO_INCREMENT = 0
  DEFAULT CHARSET = utf8;

CREATE TABLE `#__wissensmatrix_fachwissengruppe` (
  `id`               INT(10)      NOT NULL AUTO_INCREMENT,
  `title_de`         VARCHAR(300) NOT NULL,
  `title_fr`         VARCHAR(300) NOT NULL,
  `title_it`         VARCHAR(300) NOT NULL,
  `alias`            VARCHAR(255) NOT NULL,
  `state`            TINYINT(3)   NOT NULL DEFAULT '0',
  `ordering`         INT(11)      NOT NULL DEFAULT '0',
  `hits`             INT(10)      NOT NULL DEFAULT '0',
  `created`          DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by`       INT(10)      NOT NULL DEFAULT '0',
  `modified`         DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`      INT(10)      NOT NULL DEFAULT '0',
  `catid`            INT(10)      NOT NULL DEFAULT '0',
  `checked_out`      INT(11)      NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language`         CHAR(7)      NOT NULL DEFAULT '*',
  `bool`             TINYINT(1)   NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  AUTO_INCREMENT = 0
  DEFAULT CHARSET = utf8;

CREATE TABLE `#__wissensmatrix_mit_fwi` (
  `id`     INT(10) NOT NULL AUTO_INCREMENT,
  `mit_id` INT(10) NOT NULL,
  `fwi_id` INT(10) NOT NULL DEFAULT '0',
  `ist`    INT(10) NOT NULL DEFAULT '0',
  `soll`   INT(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  AUTO_INCREMENT = 0
  DEFAULT CHARSET = utf8;

CREATE TABLE `#__wissensmatrix_mit_wbi` (
  `id`        INT(10) NOT NULL AUTO_INCREMENT,
  `mit_id`    INT(10) NOT NULL,
  `wbi_id`    INT(10) NOT NULL,
  `date`      DATE             DEFAULT NULL,
  `status_id` INT(2)  NOT NULL,
  `bemerkung` TEXT    NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  AUTO_INCREMENT = 0
  DEFAULT CHARSET = utf8;
