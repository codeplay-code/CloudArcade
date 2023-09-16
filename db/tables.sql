DROP TABLE IF EXISTS users; 

CREATE TABLE users 
  ( 
	id       SMALLINT UNSIGNED NOT NULL auto_increment, 
	username VARCHAR(255) NOT NULL,
	password TEXT NOT NULL, 
	role     VARCHAR(255) NOT NULL,
	join_date DATE NULL DEFAULT NULL,
	birth_date DATE NULL DEFAULT NULL,
	gender varchar(15) NULL DEFAULT NULL,
	data text NULL DEFAULT NULL,
	email varchar(40) NULL DEFAULT NULL,
	bio varchar(180) NULL DEFAULT NULL,
	xp varchar(180) NULL DEFAULT 0,
	avatar varchar(180) NULL DEFAULT 0,
	PRIMARY KEY (id) 
  );

DROP TABLE IF EXISTS loginlogs; 

CREATE TABLE loginlogs (
	id SMALLINT UNSIGNED NOT NULL auto_increment,
	IpAddress varbinary(16) NOT NULL,
	TryTime bigint(20) NOT NULL,
	PRIMARY KEY (id) 
);

DROP TABLE IF EXISTS login_history; 

CREATE TABLE login_history (
	id SMALLINT UNSIGNED NOT NULL auto_increment,
	ip varbinary(16) NOT NULL,
	data MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	PRIMARY KEY (id) 
);

DROP TABLE IF EXISTS categories; 

CREATE TABLE categories 
  ( 
	 id          SMALLINT UNSIGNED NOT NULL auto_increment, 
	 name        VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	 slug     VARCHAR(30) NOT NULL,
	 description  MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	 meta_description  MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	 fields  TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	 priority smallint(6) NOT NULL DEFAULT '0',
	 PRIMARY KEY (id) 
  );

DROP TABLE IF EXISTS cat_links; 

CREATE TABLE cat_links 
  ( 
	 id         SMALLINT UNSIGNED NOT NULL auto_increment, 
	 gameid     SMALLINT UNSIGNED NOT NULL,
	 categoryid SMALLINT UNSIGNED NOT NULL,
	 PRIMARY KEY (id) 
  ); 

DROP TABLE IF EXISTS pages; 

CREATE TABLE pages 
  ( 
	 id          SMALLINT UNSIGNED NOT NULL auto_increment, 
	 createddate DATE NOT NULL,
	 title       VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	 slug        VARCHAR(255) NOT NULL,
	 content     MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	 fields  TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	 PRIMARY KEY (id) 
  );

DROP TABLE IF EXISTS posts; 

CREATE TABLE posts 
  ( 
	 id          SMALLINT UNSIGNED NOT NULL auto_increment, 
	 created_date DATE NOT NULL,
	 title       VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	 slug        VARCHAR(255) NOT NULL,
	 thumbnail_url   VARCHAR(255) NOT NULL,
	 content     MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	 fields  TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	 PRIMARY KEY (id) 
  );

DROP TABLE IF EXISTS games; 

CREATE TABLE games 
( 
	 id           SMALLINT UNSIGNED NOT NULL auto_increment, 
	 createddate  DATE NOT NULL,
	 title        VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	 description  MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
	 instructions MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
	 category     TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
	 source       TEXT NOT NULL, 
	 thumb_1      VARCHAR(255) NOT NULL,
	 thumb_2      VARCHAR(255) NOT NULL,
	 thumb_small  VARCHAR(255) NOT NULL,
	 url          TEXT NOT NULL, 
	 width        TEXT NOT NULL, 
	 height       TEXT NOT NULL, 
	 tags         TEXT NOT NULL, 
	 views        INT NOT NULL, 
	 upvote       INT NOT NULL, 
	 downvote     INT NOT NULL,
	 slug     VARCHAR(255) NOT NULL,
	 data MEDIUMTEXT NULL DEFAULT NULL, 
	 fields  TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	 published tinyint(1) NOT NULL DEFAULT '1',
	 PRIMARY KEY (id) 
);

DROP TABLE IF EXISTS votelogs; 

CREATE TABLE votelogs 
( 
	id           SMALLINT UNSIGNED NOT NULL auto_increment, 
	game_id     SMALLINT UNSIGNED NOT NULL,
	ip 		varbinary(16) NOT NULL,
	action          TEXT NOT NULL, 
	PRIMARY KEY (id)
);

DROP TABLE IF EXISTS favorites; 

CREATE TABLE favorites 
( 
	id           SMALLINT UNSIGNED NOT NULL auto_increment, 
	game_id     SMALLINT UNSIGNED NOT NULL,
	user_id     SMALLINT UNSIGNED NOT NULL,
	PRIMARY KEY (id)
);

DROP TABLE IF EXISTS collections; 

CREATE TABLE collections 
  ( 
	 id          SMALLINT UNSIGNED NOT NULL auto_increment, 
	 name        VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	 data  MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	 PRIMARY KEY (id) 
  );

DROP TABLE IF EXISTS comments;

CREATE TABLE comments (
 id int(10) unsigned NOT NULL AUTO_INCREMENT,
 game_id int(10) NOT NULL,
 parent_id int(10) unsigned DEFAULT NULL,
 comment varchar(400) NOT NULL,
 sender_id int(40) NOT NULL,
 sender_username varchar(20) NOT NULL,
 created_date DATETIME NULL DEFAULT NULL,
 approved tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (id)
);

DROP TABLE IF EXISTS scores;

CREATE TABLE scores 
  ( 
	 id          SMALLINT UNSIGNED NOT NULL auto_increment, 
	 created_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	 game_id int(40) NOT NULL,
	 user_id int(40) NOT NULL,
	 score INT(6) UNSIGNED NOT NULL DEFAULT '0',
	 PRIMARY KEY (id) 
  );

CREATE TABLE IF NOT EXISTS statistics (
 id int(11) unsigned NOT NULL AUTO_INCREMENT,
 created_date date DEFAULT NULL,
 page_views varchar(255) DEFAULT NULL,
 unique_visitor varchar(255) DEFAULT NULL,
 data mediumtext DEFAULT NULL,
 PRIMARY KEY (id)
);

DROP TABLE IF EXISTS stats_ip_address; 

CREATE TABLE stats_ip_address (
 id int(11) unsigned NOT NULL AUTO_INCREMENT,
 ip_address varchar(255) DEFAULT NULL,
 created_date date DEFAULT NULL,
 PRIMARY KEY (id)
);

DROP TABLE IF EXISTS sessions; 

CREATE TABLE sessions (
  token varchar(400) NOT NULL,
  data text NOT NULL
);

DROP TABLE IF EXISTS prefs;

CREATE TABLE prefs (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  value text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (id)
);

DROP TABLE IF EXISTS menus;

CREATE TABLE menus (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  label varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  url varchar(512) CHARACTER SET utf8 DEFAULT NULL,
  parent_id int(11) DEFAULT NULL,
  name varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
);

DROP TABLE IF EXISTS trends;

CREATE TABLE trends (
  id int(11) NOT NULL AUTO_INCREMENT,
  game_id int(11) DEFAULT NULL,
  views int(11) NOT NULL,
  created date NOT NULL,
  slug varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (id)
);

DROP TABLE IF EXISTS tags;

CREATE TABLE tags (
  id smallint(11) unsigned NOT NULL AUTO_INCREMENT,
  name varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  usage_count int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
);

DROP TABLE IF EXISTS tag_links;

CREATE TABLE tag_links (
  game_id smallint(11) unsigned NOT NULL,
  tag_id smallint(11) unsigned NOT NULL,
  PRIMARY KEY (game_id,tag_id)
);

DROP TABLE IF EXISTS settings;

CREATE TABLE settings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  type VARCHAR(255) NOT NULL,
  category VARCHAR(255) NOT NULL,
  label MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  tooltip MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  description MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci,
  value MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
);

DROP TABLE IF EXISTS translations;

CREATE TABLE translations
(
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    content_type VARCHAR(50) NOT NULL,  -- e.g., 'game', 'post', 'category'
    content_id   INT UNSIGNED NOT NULL, -- corresponds to the relevant table but not a foreign key
    language     VARCHAR(5) NOT NULL,   -- e.g., 'en', 'fr', etc.
    field        VARCHAR(50) NOT NULL,  -- e.g., 'title', 'description', 'content'
    translation  MEDIUMTEXT NOT NULL,
    PRIMARY KEY (id),
    INDEX idx_translations (content_type, content_id, language, field) -- An index for faster lookups
);