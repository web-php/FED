
CREATE TABLE `organization` (

        `id` int(32) unsigned NOT NULL AUTO_INCREMENT,
        `org_id` int(32) NOT NULL COMMENT 'id c сервера fedresurs.ru',
        `org_name` varchar(255) NOT NULL COMMENT 'id Полное фирменное наименование',
        `org_brief_name` varchar(255) DEFAULT NULL COMMENT 'Сокращённое фирменное наименование:' ,
        `org_address` varchar(500) DEFAULT NULL COMMENT 'Юридический адрес (по данным ЕГРЮЛ)' ,      
        `postal_address` varchar(500) DEFAULT NULL COMMENT 'Почтовый адрес (по данным компании)' , 
        `inn` varchar(20) DEFAULT NULL COMMENT 'ИНН' , 
        `kpp` varchar(20) DEFAULT NULL COMMENT 'КПП' , 
        `ogrn` varchar(20) DEFAULT NULL COMMENT 'ОГРН' , 
        `init_capital` varchar(100) DEFAULT NULL COMMENT 'Уставный капитал (по данным ЕГРЮЛ)' , 
        `org_cost` varchar(100) DEFAULT NULL COMMENT 'Стоимость чистых активов' , 
        `msg_count` int(32) DEFAULT NULL COMMENT 'Количество сообщений:' , 
        `okopf` int(1) DEFAULT NULL COMMENT 'ОКОПФ: Организационно-правовая форма 1- есь NULL нет , сортировка по org_okopf.id_org' , 
        `region` int(32) DEFAULT NULL COMMENT 'Регион предприятия ключ на region.id ' , 
        `okved` int(32) DEFAULT NULL COMMENT 'Основная отрасль: ключ на okved.id ' , 
        `org_status` int(32) DEFAULT NULL COMMENT 'Cтатус юридического лица : ключ на org_status.id ' , 
        `licvidation_date` datetime DEFAULT NULL  COMMENT 'Дата внесения в ЕГРЮЛ записи о нахождении в стадии ликвидации' , 
        `hash` varchar(32) NOT NULL COMMENT 'хэш всех полей уникальное поле' ,   
        PRIMARY KEY (`id`) ,
        UNIQUE KEY(`hash`),
        KEY `org_id` (`org_id`) , 
        KEY `org_name` (`org_name` , `org_brief_name`) , 
        KEY `request` (`inn` , `kpp` , `ogrn`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ;


CREATE TABLE `okopf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `okopf` varchar(255) DEFAULT NULL COMMENT 'ОКОПФ: Организационно-правовая форма' ,
  PRIMARY KEY (`id`) ,
  UNIQUE KEY(`okopf`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ;

CREATE TABLE `org_okopf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `org_id` int(32) DEFAULT NULL COMMENT 'id организации ключ на organization.org_id' ,
  `okopf_id` int(32) DEFAULT NULL COMMENT 'id okopf ключ на okopf.id' ,
  PRIMARY KEY (`id`) ,
  UNIQUE KEY(`org_id` , `okopf_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ;

CREATE TABLE `region` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `region` varchar(255) DEFAULT NULL COMMENT 'регион' ,
  PRIMARY KEY (`id`) ,
  UNIQUE KEY(`region`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ;

CREATE TABLE `okved` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `okved` varchar(255) DEFAULT NULL COMMENT 'ОКВЕД' ,
  PRIMARY KEY (`id`) ,
  UNIQUE KEY(`okved`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ;

CREATE TABLE `org_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `org_status` varchar(255) DEFAULT NULL COMMENT 'статус организации' ,
  PRIMARY KEY (`id`) ,
  UNIQUE KEY(`org_status`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ;

CREATE TABLE `doc_error` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `org_id` int(32) NOT NULL COMMENT 'id c сервера fedresurs.ru' ,
  `error` int(32) NOT NULL COMMENT 'код ошибки error.id' ,
  `attempt` int(2) NOT NULL COMMENT 'колличество попыток' ,
  `parsed_date` datetime DEFAULT NULL  COMMENT 'Дата парсинга' , 
  `state` int(1) NOT NULL COMMENT '0 или 1 . 0 - действующая ошибка требуется повторная проверка , 1 - точно известно что документа нет' ,   
  PRIMARY KEY (`id`) ,
  UNIQUE KEY(`org_id` , `err_code`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ;

CREATE TABLE `error` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `error` varchar(255) DEFAULT NULL COMMENT 'текстовое обозначение ошибки' ,
  PRIMARY KEY (`id`) ,
  UNIQUE KEY(`error`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ;



