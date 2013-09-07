DROP TABLE IF EXISTS wcf1_user_twofa_blacklist;
CREATE TABLE wcf1_user_twofa_blacklist (
	code	CHAR(6) NOT NULL,
	userID	INT(10) NOT NULL,
	time	INT(10) NOT NULL,
	
	KEY (userID, code, time),
	KEY (code),
	KEY (time)
);

ALTER TABLE wcf1_user ADD COLUMN twofaSecret CHAR(16) DEFAULT NULL;

ALTER TABLE wcf1_user_twofa_blacklist ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
