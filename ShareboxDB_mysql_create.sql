CREATE TABLE `public_uploads` (
	`ID` int NOT NULL AUTO_INCREMENT,
	`IP` varchar(255) NOT NULL,
	`fname` varchar(255) NOT NULL,
	`fsize` varchar(255) NOT NULL,
	`fdate` varchar(255) NOT NULL,
	PRIMARY KEY (`ID`)
);

CREATE TABLE `private_uploads` (
	`ID` int NOT NULL AUTO_INCREMENT,
	`IP` varchar(255) NOT NULL,
	`fname` varchar(255) NOT NULL,
	`fsize` varchar(255) NOT NULL,
	`fdate` varchar(255) NOT NULL,
	`fpwd` varchar(255) NOT NULL,
	PRIMARY KEY (`ID`)
);

CREATE TABLE `users` (
	`ID` int NOT NULL AUTO_INCREMENT,
	`username` varchar(255) NOT NULL,
	`email` varchar(255) NOT NULL,
	`pass` varchar(255) NOT NULL,
	PRIMARY KEY (`ID`)
);

CREATE TABLE `conn_tokens` (
	`ID` int NOT NULL AUTO_INCREMENT,
	`created` timestamp NOT NULL DEFAULT current_timestamp(),
	`user_id` int NOT NULL,
	PRIMARY KEY (`ID`)
);





