--page
DROP TABLE IF EXISTS cms1_page;
CREATE TABLE cms1_page (
pageID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
parentID INT(10) DEFAULT 0,
userID INT(10),
title VARCHAR(255) NOT NULL,
description MEDIUMTEXT,
metaDescription MEDIUMTEXT,
metaKeywords VARCHAR(255),
invisible TINYINT(1) DEFAULT 0,
robots ENUM('index,follow', 'index,nofollow', 'noindex,follow', 'noindex,nofollow') NOT NULL DEFAULT 'index,follow',
showOrder INT(10) DEFAULT 0
);

--content
DROP TABLE IF EXISTS cms1_content;
CREATE TABLE cms1_content(
contentID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
pageID INT(10),
title VARCHAR(255) NOT NULL,
showOrder INT(10) DEFAULT 0,
cssID VARCHAR(255),
cssClasses VARCHAR(255)
);

--section
DROP TABLE IF EXISTS cms1_content_section;
CREATE TABLE cms1_content_section(
sectionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
contentID INT(10),
sectionType INT(10) DEFAULT 1,
sectionData MEDIUMTEXT,
showOrder INT(10) DEFAULT 0,
cssID VARCHAR (255),
cssClasses VARCHAR(255)
));

--foreign keys
ALTER TABLE cms1_page ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE cms1_content ADD FOREIGN KEY (pageID) REFERENCES cms1_page (pageID) ON DELETE SET NULL;
ALTER TABLE cms1_content_section ADD FOREIGN KEY (contentID) REFERENCES cms1_content (contentID) ON DELETE SET NULL;