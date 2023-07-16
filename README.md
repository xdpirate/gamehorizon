# GameHorizon

Simple self-hosted tracker to keep track of upcoming, current and collected games.

## Requirements

* A web server
* A SQL server
* PHP

GameHorizon is developed and tested using Apache2 and MySQL, but other web servers and SQL servers will probably work. Maybe.

## Usability

It works as intended. It doesn't look too hot on phones at the moment, owing to the horizontal table layout of the games. It looks nice on a tablet-style display, though, or the inner screen of foldables. I'm planning to make a more mobile-friendly UI in the future, but focus right now is on the desktop experience.

## Features

* Add a game to one of four tables:
  * **Unreleased** - Games that unreleased, but have a release date confirmed
  * **Announced** - Games that unreleased, but there is no release date confirmed yet
  * **Released** - Games that have already been released
  * **Collected** - Games in your collection
* Freely move games between tables by editing them
* Support for adding platforms to each game
* For unreleased games, show a countdown to when the game releases when you hover over the release date (e.g. *"Releases tomorrow"*, *"Releases in 81 days"*)
* Buttons to edit, delete or web search for any given game
* Names are not unique and can exist in several states and tables at once
* Themes: Light theme, dark theme, [Nord](https://www.nordtheme.com/)
* Export game data to CSV or HTML formats

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

If the filenames in your exported files are stamped with the wrong time, include `date_default_timezone_set("Europe/Oslo");` on a separate line before the closing php tag `?>`, then replace `Europe/Oslo` with the appropriate timezone according to PHP's [List of Supported Timezones](https://www.php.net/manual/en/timezones.php).


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
