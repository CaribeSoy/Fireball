﻿DROP TABLE IF EXISTS cms1_feed;
CREATE TABLE cms1_feed(
feedID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
title VARCHAR(255) NOT NULL,
feedUrl VARCHAR(255) NOT NULL,
lastCheck INT(20));