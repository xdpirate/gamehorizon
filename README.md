# GameHorizon

Simple self-hosted tracker for upcoming and current games you wanna play.

## Requirements

* A web server like Apache2
* PHP
* MySQL

## Security
There is currently zero security measures implemented. You can use `.htaccess` based authentication or make sure the application isn't exposed outside your local network.

## Database credentials

You need to create `credentials.php` in the same directory as `index.php`, and populate it with the following:

```
<?php
$mysqlHost = "your-sql-hostname";
$mysqlUser = "your-sql-username";
$mysqlPassword = "your-sql-password";
?>
```

Replace the values of the variables to fit your database configuration.

# Database format
You need one database named `gamehorizon`.

```
CREATE DATABASE gamehorizon; USE gamehorizon;
```

You need to create three tables: `gamesUnreleased`, `gamesTBA`, and `gamesReleased`.

```
CREATE TABLE `gamesReleased` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `GameName` varchar(255) NOT NULL,
  `Platforms` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `gamesTBA` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `GameName` varchar(255) NOT NULL,
  `Platforms` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `gamesUnreleased` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `GameName` varchar(255) NOT NULL,
  `ReleaseDate` varchar(255) NOT NULL,
  `Platforms` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
```