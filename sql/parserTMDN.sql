
CREATE TABLE `tmdn_base` (

        `id` int(32) unsigned NOT NULL AUTO_INCREMENT,
        `tm` TEXT COMMENT 'TradeMArk торговая марка',
        `anm2` TEXT COMMENT 'Имя . сереализованный массив',
        `tm2` TEXT COMMENT 'TradeMArk2 , 2 обозначение',
        `MarkVerbalElementText` TEXT COMMENT 'в ру зоне 540 поле',
        `sc` TEXT COMMENT '(Filed)Текущий статус товарного знака',
        `ST13` varchar(250) COMMENT 'внутренний номер заявки',
        `ty` TEXT COMMENT 'Вид товарного знака (в ру зоне UNDEFINED)',
        `oc` TEXT COMMENT 'страна',
        `nc` TEXT COMMENT 'мкту по ницкой классификации (сереализованный массив)',
        `vc` TEXT COMMENT 'венской классификации (сереализованный массив)',
        `an` TEXT COMMENT 'Номер заявки',
        `MarkImageURI` TEXT COMMENT 'урл логотипа',
        `ad` TEXT COMMENT 'какоето число , неизвестный параметр',
        `anm` TEXT COMMENT 'Имя . сереализованный массив',
        `OperationCode` TEXT COMMENT '',
        `ApplicantName` TEXT COMMENT 'сереализованный массив',
        `timestamp` TEXT COMMENT 'временная отметка',
        # русские поля
        ipr TEXT COMMENT 'Вид товарного знака (ру поле)',
        RegistrationNumber TEXT COMMENT 'номер регистрации (ру поле)',
        ExpiryDate TEXT COMMENT 'Дата окончания регистрации (ру поле)',
        RegistrationDate TEXT COMMENT 'дата регистрации знака (ру поле)',
        PRIMARY KEY (`id`) ,
        UNIQUE KEY(`ST13`),
        KEY `oc` (`oc`(10)) , 
        KEY `an` (`an`(10)) , 
        KEY `ipr` (`ipr`(10)) , 
        KEY `sc` (`sc`(10)) , 
        KEY `ty` (`ty`(10)) 

) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 


