CREATE TABLE `domains` (
`id`  bigint NOT NULL AUTO_INCREMENT ,
`zone`  varchar(10) NOT NULL COMMENT 'доменная зона' ,
`domain`  varchar(1000) NOT NULL COMMENT 'Доменное имя как есть ' ,
`reg_name`  varchar(1000) NOT NULL COMMENT 'Имя регистратора' ,
`registered`  date NULL DEFAULT NULL COMMENT 'Когда зарегестрирован' ,
`registered_to`  date NULL DEFAULT NULL COMMENT 'Дата окончания регистрации' ,
`expiration_date`  date NULL DEFAULT NULL COMMENT 'дата после которой домен может быть перерегестрирован' ,
`type`  varchar(2) NULL DEFAULT "0" COMMENT 'состояние домена' ,
`hash`  varchar(32) NOT NULL ,
PRIMARY KEY (`id`),
UNIQUE INDEX `i_hash` (`hash`) ,
INDEX `i_general` (`zone`, `domain`, `reg_name`, `type`) ,
INDEX `i_date` (`registered`, `registered_to`, `expiration_date`) 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8
COMMENT='Таблица доменных имен '
;

