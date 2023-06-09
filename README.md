# GameHorizon

Simple self-hosted tracker for upcoming, current and collected games.

## Requirements

* A web server
* A SQL server
* PHP

GameHorizon is developed and tested using Apache2 and MySQL, but other web servers and SQL servers will probably work. Maybe.

## Usability

It works as intended. It doesn't look too hot on phones, owing to the horizontal table layout of the games. It looks nice on a tablet-style display, though, or the inner screen of foldables. 

## Security

There are currently zero security measures implemented. For external access, you can use `.htaccess` based authentication or a reverse proxy with authentication. Alternatively, you can make sure the application isn't exposed outside your local network.

## Installation
Clone/download this repository and put the gamehorizon folder in your web server document root (typically `/var/www/html`).

### Database credentials

You need to create `credentials.php` in the same directory as `index.php`, and populate it with the following:

```
<?php
$mysqlHost = "your-sql-hostname";
$mysqlUser = "your-sql-username";
$mysqlPassword = "your-sql-password";
?>
```

Replace the values of the variables to fit your database configuration. GameHorizon will setup the database structure by itself.

## Screenshot
![2023-06-30_18-30](https://github.com/xdpirate/gamehorizon/assets/1757462/c9c55c78-fbba-4d66-8dd8-882728f9a4ad)

## License

GameHorizon is free and open source software, licensed under the GNU General Public License v3.0.

    GameHorizon -  Simple self-hosted tracker for upcoming and collected games
    Copyright ©️ 2023 xdpirate

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
