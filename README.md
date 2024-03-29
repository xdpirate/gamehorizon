# GameHorizon

Simple self-hosted tracker to keep track of upcoming, current and collected games.

![2023-11-26_20-22](https://github.com/xdpirate/gamehorizon/assets/1757462/c786ce02-d625-4dc8-810f-0370e09f7e5d)

## Features

* Add a game to one of four tables:
  * **Unreleased** - Games that are unreleased, but have a release date confirmed
  * **Announced** - Games that are unreleased, but there is no release date confirmed yet
  * **Released** - Games that have already been released
  * **Collected** - Games in your collection
* Freely move games between tables by editing them
* Support for adding platforms to each game
* For unreleased games, show a countdown to when the game releases when you hover over the release date (e.g. *"Releases tomorrow"*, *"Releases in 81 days"*)
* Buttons to edit, delete or web search for any given game
  * Several built-in search engines to choose from
  * Support for defining a custom search engine
* Themes: 
  * [Nord](https://www.nordtheme.com/)
  * Light theme
  * Dark theme
* Quick filter for each table, search titles or platforms
* Export game data to plain text, CSV, JSON or HTML formats
* Names are not unique and can exist in several states and tables at once
* Built-in update mechanism

## Requirements

You can run GameHorizon on your own AMP stack, or via Docker.

### AMP stack

* A web server
* A SQL server
* PHP

GameHorizon is developed and tested using Apache2 and MySQL, but other web servers and SQL servers will probably work. Maybe. 

### Docker

* Docker and Docker Compose

## Installation/Usage

### Running on preinstalled AMP stack

1. Clone this repository, or [grab the latest release](https://github.com/xdpirate/gamehorizon/releases/latest)
2. Put the gamehorizon directory in your web server document root (typically `/var/www/html`).
3. Create `credentials.php` within the same directory as `index.php`, and populate it with the following:

```
<?php
$mysqlHost = "your-sql-hostname";
$mysqlUser = "your-sql-username";
$mysqlPassword = "your-sql-password";
?>
```

4. Replace the values of the variables to fit your database configuration. GameHorizon will set up the database structure by itself.
5. To update GameHorizon, use the [included updater](#updater), or run `git pull` in the repository directory (requires `git` to be installed).

### Running with Docker

1. Clone this repository, or [grab the latest release](https://github.com/xdpirate/gamehorizon/releases/latest)
2. `cd` to the directory with the repository.
3. Build and run the image with `docker-compose up -d`
4. Wait 10-20 seconds after the first run to let the database start up.
5. Visit `http://localhost:1337/` in your browser to use the application.
6. To stop, run `docker-compose stop` in the repository directory.
7. To update GameHorizon, run `git pull` in the repository directory (requires `git` to be installed).

## Usability

It works as intended. It doesn't look too hot on phones at the moment, owing to the horizontal table layout of the games. It looks nice on a tablet-style display, though, or the inner screen of foldables. I'm planning to make a more mobile-friendly UI in the future, but focus right now is on the desktop experience.

## Clean-up/Uninstallation

### AMP stack

* Delete the `gamehorizon` directory from the web server document root.
* Delete the `gamehorizon` database from the MySQL server -`DROP DATABASE gamehorizon`

### Docker

Run `docker-compose down` from inside the repository directory, then delete it. Note that running this command destroys your stored GameHorizon data; don't do it unless you wish for that to happen.

## Security

There are currently zero security measures implemented. For external access, you can use `.htaccess` based authentication or a reverse proxy with authentication. Alternatively, you can make sure the application isn't exposed outside your local network.

If you are running GameHorizon in Docker and also exposing it outside your own network, you need to change the MySQL username and password in `docker-compose.yml` and `index.php` to something unique! If you don't, your database will be vulnerable, as the default credentials are included in plain text in this repository.

## Updater

You can update GameHorizon through the UI by clicking "Update" on the bottom right of the page. This is disabled by default, as it requires you to be running GameHorizon outside of Docker and inside a trusted environment. It requires `git` to be installed on a UNIX-like server. GameHorizon passes your password to the OS in plain text, and although it isn't logged, the mechanism in which it operates can be abused by someone with access to the updater. In order to enable the updater, you must add this to your `credentials.php`:

    $updaterEnabled = true;

This can be toggled on and off at will, and will apply on the next page load. You can enable it, update, then disable it again if you'd like, but I definitely don't recommend keeping it on if you're running in an untrusted environment where other people may have access to GameHorizon.

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
