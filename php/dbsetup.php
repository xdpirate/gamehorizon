<?php
// Setup DB if it's the first run
mysqli_query($link, "CREATE DATABASE IF NOT EXISTS gamehorizon");
mysqli_select_db($link, "gamehorizon");
mysqli_query($link, "
    CREATE TABLE IF NOT EXISTS `gamesUnreleased` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `GameName` varchar(255) NOT NULL,
        `ReleaseDate` varchar(255) NOT NULL,
        `Platforms` varchar(255) NOT NULL,
        PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;");

mysqli_query($link, "
    CREATE TABLE IF NOT EXISTS `gamesReleased` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `GameName` varchar(255) NOT NULL,
        `Platforms` varchar(255) NOT NULL,
        PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;");

mysqli_query($link, "CREATE TABLE IF NOT EXISTS `gamesTBA` LIKE `gamesReleased`;");
mysqli_query($link, "CREATE TABLE IF NOT EXISTS `gamesCollected` LIKE `gamesReleased`;");
?>
