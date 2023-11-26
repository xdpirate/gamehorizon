<?php
// ===================================================================================
// GameHorizon -  Simple self-hosted tracker for upcoming and collected games
// Copyright ©️ 2023 xdpirate

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <https://www.gnu.org/licenses/>.
// ===================================================================================

// These credentials are for the Docker image. If you want to run GameHorizon
// locally without using Docker, don't change these; add them to credentials.php!
$mysqlHost = "db";
$mysqlUser = "php_docker";
$mysqlPassword = "password123";

error_reporting(E_ERROR); // Silence the next line so it doesn't cry when running in Docker
include("./credentials.php");

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", 1);

$platformList = ["PS5", "PS4", "XSX", "XB1", "Switch", "PC", "Android", "PSVR2", "iOS"];
sort($platformList);

require("./functions.php");

$link = mysqli_connect($mysqlHost, $mysqlUser, $mysqlPassword);
if(!$link) {
    die("Couldn't connect: " . mysqli_error($link));
}

require("./dbsetup.php");
require("./export.php");
require("./delete.php");
require("./edit.php");
require("./create.php");
require("./images.php");

$resGamesReleased = mysqli_query($link, "SELECT * FROM gamesReleased ORDER BY GameName ASC;");
$resGamesUnreleased = mysqli_query($link, "SELECT * FROM gamesUnreleased ORDER BY ReleaseDate ASC;");
$resGamesTBA = mysqli_query($link, "SELECT * FROM gamesTBA ORDER BY GameName ASC;");
$resGamesCollected = mysqli_query($link, "SELECT * FROM gamesCollected ORDER BY GameName ASC;");

mysqli_close($link);
?>
<!DOCTYPE html>

<html>
    <head>
        <title>GameHorizon</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style></style>
        <script>
            function getCookie(cname) {
                let name = cname + "=";
                let decodedCookie = decodeURIComponent(document.cookie);
                let ca = decodedCookie.split(';');
                for(let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        return c.substring(name.length, c.length);
                    }
                }
                return "";
            }

            let themes = {
                "daylight": {
                    background: "white",
                    foreground: "black",
                    platformbg: "#00d4ff",
                    oddrow: "#a5c6f3",
                    evenrow: "#d2e2f9",
                    released: "#0a0",
                    link: "#000"
                },
                "midnight": {
                    background: "black",
                    foreground: "white",
                    platformbg: "#00a",
                    oddrow: "#262a2b",
                    evenrow: "#004daa",
                    released: "#0f0",
                    link: "white"
                },
                "nord": {
                    background: "#2e3440",
                    foreground: "#e5e9f0",
                    platformbg: "#3b4252",
                    oddrow: "#434c5e",
                    evenrow: "#4c566a",
                    released: "#0c0",
                    link: "#e5e9f0"
                }
            };

            let currentTheme = getCookie("ghTheme");
            let hideAddGame = getCookie("hideAddGame");
            let searchEngine = getCookie("searchEngine");
            let searchEngineCustomURL = getCookie("searchEngineCustomURL");

            let searchEngines = {
                "startpage": "https://www.startpage.com/sp/search?query=%s",
                "ddg": "https://duckduckgo.com/?q=%s",
                "google": "https://www.google.com/search?q=%s",
                "bing": "https://www.bing.com/search?q=%s",
                "yandex": "https://yandex.com/search/?text=%s",
                "custom": searchEngineCustomURL
            }

            function searchEngineHandler() {
                if(document.getElementById("searchEngine").value == "custom") {
                    document.getElementById("customSearchEngineBox").style.display = "block";
                } else {
                    document.getElementById("customSearchEngineBox").style.display = "none";
                }
            }

            function saveSettings() {
                if(document.getElementById("hideAddGameCheckbox").checked) {
                    hideAddGame = 1;
                } else {
                    hideAddGame = 0;
                }

                searchEngine = document.getElementById("searchEngine").value;
                searchEngineCustomURL = document.getElementById("customSearchEngineBox").value;
                searchEngines["custom"] = searchEngineCustomURL;

                let d = new Date();
                d.setTime(d.getTime() + (3650*24*60*60*1000));
                let expires = "expires="+ d.toUTCString();
                document.cookie = "ghTheme=" + currentTheme + ";" + expires + ";SameSite=Lax;path=/";
                document.cookie = "hideAddGame=" + hideAddGame + ";" + expires + ";SameSite=Lax;path=/";
                document.cookie = "searchEngine=" + searchEngine + ";" + expires + ";SameSite=Lax;path=/";
                document.cookie = "searchEngineCustomURL=" + encodeURIComponent(searchEngineCustomURL) + ";" + expires + ";SameSite=Lax;path=/";
            }

            function doSearch(gameName) {
                if(searchEngines[searchEngine].includes("%s")) {
                    window.open(searchEngines[searchEngine].replace("%s", gameName), '_blank');
                } else {
                    alert("Malformed custom search engine URL, it must contain %s to substitute search terms.")
                }
            }

            function applyTheme(themeName) {
                currentTheme = themeName;
                document.getElementsByTagName('style')[0].innerHTML = `
                    html,body {
                        font-family: Arial, Helvetica, sans-serif;
                        background-color: ${themes[themeName].background};
                        color: ${themes[themeName].foreground};
                    }

                    #everything {
                        width: 50%;
                        margin: auto;
                        margin-top: 50px;
                    }

                    #modal {
                        position: fixed;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        width: 600px;
                        height: 260px;
                        background-color: ${themes[themeName].background};
                        padding: 20px;
                        border: 1px solid ${themes[themeName].foreground};
                        border-radius: 20px;
                        box-shadow: 10px 10px 5px #888888;
                    }

                    #modalbg {
                        position: fixed;
                        top: 0;
                        left: 0;
                        z-index: 1040;
                        width: 100vw;
                        height: 100vh;
                        background-color: rgba(0, 0, 0, 0.4);
                        backdrop-filter: blur(15px);
                    }

                    #newGameWrapper, #optionsWrapper, #updateWrapper {
                        border: 1px solid ${themes[themeName].foreground};
                        border-radius: 10px;
                        padding: 10px;
                        margin-bottom: 20px;
                    }

                    #updateWrapper {
                        margin-top: 20px;
                    }

                    #newGameWrapper {
                        display: ${hideAddGame == 1 ? "none" : "block"};
                    }

                    h1 {
                        margin: 0;
                    }

                    input {
                        background-color: ${themes[themeName].background};
                        color: ${themes[themeName].foreground};
                    }

                    span.tab {
                        margin-left: 5px;
                        margin-right: 5px;
                        cursor: pointer;
                    }

                    .gameReleased {
                        color: ${themes[themeName].released};
                        font-weight: bold;
                    }

                    span.activetab {
                        font-weight: bold;
                        text-decoration: underline;
                    }

                    h1 > a {
                        text-decoration: none;
                        color: ${themes[themeName].link};
                    }

                    a {
                        color: ${themes[themeName].link};
                    }

                    table {
                        width: 100%;
                    }

                    span.platformLabel {
                        border: 1px solid ${themes[themeName].platformbg};
                        background-color: ${themes[themeName].platformbg};
                        border-radius: 10px;
                        color: ${themes[themeName].foreground};
                        font-weight: bold;
                        margin-left: 2px;
                        margin-right: 2px;
                        padding: 3px;
                    }

                    tr:nth-child(odd) {
                        background: ${themes[themeName].oddrow};
                    }

                    tr:nth-child(even) {
                        background: ${themes[themeName].evenrow};
                    }

                    #newEntryToggleDiv, #optionsToggleDiv {
                        text-decoration: underline;
                        cursor: pointer;
                    }

                    .editButton, .deleteButton, .searchButton {
                        cursor: pointer;
                    }

                    .tableFooter {
                        width: 100%;
                        text-align: right;
                    }

                    #formHeader, #optionsHeader {
                        font-size: 22px;
                        font-weight: bold;
                        margin-right: 10px;
                    }

                    th {
                        text-align: left;
                    }

                    td,th {
                        padding: 5px;
                    }

                    th {
                        background-color: ${themes[themeName].evenrow};
                    }

                    #tbaWrapper, #releasedWrapper, #collectionWrapper {
                        display: none;
                    }

                    #customSearchEngineBox {
                        margin-top: 5px;
                        width: 30em;
                        display: none;
                    }

                    #footer {
                        width: 98%;
                        margin-top: 20px;
                        margin-bottom: 20px;
                        font-size: smaller;
                        text-align: center;
                    }

                    /* Phone styles */
                    @media all and (max-width: 1000px) {
                        #everything {
                            width: 100%;
                            margin: auto;
                        }
                    }
                </style>`;
            }

            if(!themes[currentTheme]) {
                currentTheme = "nord";
            }

            if(isNaN(hideAddGame) || hideAddGame == undefined || hideAddGame == null || hideAddGame.trim() == "") {
                hideAddGame = 0;
            }
            
            if(searchEngine == undefined || searchEngine == null || searchEngine.trim() == "") {
                searchEngine = "startpage";
            }

            applyTheme(currentTheme);
        </script>

        <link href="./favicon.png" rel="icon" type="image/png" />

        <script>
            <?php
                $outputString = 'let platformList = [';
                for($i = 0; $i < sizeof($platformList); $i++) {
                    $outputString .= "'$platformList[$i]',";
                }
                $outputString = substr($outputString, 0, -1) . '];'; // Remove trailing comma
                print($outputString);
            ?>
        </script>
    </head>
    
    <body>
        <?php if(!isset($_GET['update'])) { ?><div id="modalbg" style="display: none;">
            <div id="modal">
                <form id="editForm">
                    <b>Name:</b>
                    <input type="text" id="editModalGameTitle" name="editModalGameTitle" style="width: 98%;" placeholder="Game title"></input><br /><br />

                    <b>Release status:</b><br />
                    <input type="radio" id="editModalUnreleased" name="editModalReleaseStatus" value="unreleased" checked><label for="editModalUnreleased">Release date set:</label> <input type="date" id="editModalReleaseDate" name="editModalReleaseDate">

                    <input type="radio" id="editModalTBA" name="editModalReleaseStatus" value="tba"><label for="editModalTBA">TBA</label>

                    <input type="radio" id="editModalReleased" name="editModalReleaseStatus" value="released"><label for="editModalReleased">Released</label>

                    <input type="radio" id="editModalCollection" name="editModalReleaseStatus" value="collection"><label for="editModalCollection">Collected</label>

                    <br /><br />

                    <b>Platforms:</b><br />

                    <?php
                        for($i = 0; $i < sizeof($platformList); $i++) {
                            print("<input type='checkbox' id='editModalCheckbox$i' name='editModalPlatforms[]' value='$platformList[$i]' /><label for='editModalCheckbox$i'>$platformList[$i]</label>\n");
                        }
                    ?>
                    <br /><input type='checkbox' id='editModalCheckboxOther' name='editModalCheckboxOther' /><label for='editModalCheckboxOther'>Other(s):</label> <input type="text" name="editModalOtherPlatforms" id="editModalOtherPlatforms" /> <small>(if more than one, separate with commas)</small>
                        
                    <br /><br />
                    <input type="submit" value="Save" style="width: 49%; height: 4em;" id="editModalSaveButton" /> 
                    <input type="button" value="Cancel" style="width: 49%; height: 4em;" id="editModalCancelButton" onclick="document.getElementById('modalbg').style.display = 'none';" />

                    <input type="hidden" name="editID" id="editID" value="0" />
                    <input type="hidden" name="editStatus" id="editStatus" value="unreleased" />
                </form>
            </div>
        </div><?php } ?>

        <div id="everything">
            <h1><a href="./"><img src="./favicon.png" width="32" height="32" /> GameHorizon</a></h1>
            <small><?php include('quotes.php') ?></small>

            <?php if(!isset($_GET['update'])) { ?><div style="margin-top: 20px; margin-bottom: 20px;">
                <span id="newEntryToggleDiv" onclick="toggleNewGameDiv();"><span id="formHeader">Add game</span></span>
                <span id="optionsToggleDiv" onclick="toggleOptionsDiv();"><span id="optionsHeader">Options</span></span>
            </div>

            <div id="newGameWrapper">
                <form id="formArea" method="GET" action="./">
                    <b>Name:</b>
                    <input type="text" id="gameTitle" name="gameTitle" style="width: 98%;" placeholder="Game title"></input><br /><br />

                    <b>Release status:</b><br />
                    <input type="radio" id="unreleased" name="releaseStatus" value="unreleased" checked><label for="unreleased">Release date set:</label> <input type="date" id="releaseDate" name="releaseDate">

                    <input type="radio" id="tba" name="releaseStatus" value="tba"><label for="tba">TBA</label>

                    <input type="radio" id="released" name="releaseStatus" value="released"><label for="released">Released</label>

                    <input type="radio" id="collection" name="releaseStatus" value="collection"><label for="collection">Collected</label>

                    <br /><br />

                    <b>Platforms:</b><br />

                    <?php
                        for($i = 0; $i < sizeof($platformList); $i++) {
                            print("<input type='checkbox' id='platformCheckbox$i' name='platforms[]' value='$platformList[$i]' /><label for='platformCheckbox$i'>$platformList[$i]</label>\n");
                        }
                    ?>
                    <br /><input type='checkbox' id='platformCheckboxOther' name='platformCheckboxOther' /><label for='platformCheckboxOther'>Other(s):</label> <input type="text" name="addGameOtherPlatforms" id="addGameOtherPlatforms" /> <small>(if more than one, separate with commas)</small>
                        
                    <br /><br />
                    <input type="submit" value="Save" style="width: 49%; height: 4em;" id="saveButton" /> 
                    <input type="button" value="Clear" style="width: 49%; height: 4em;" id="clearButton" onclick="this.parentNode.reset();" /> 

                    <input type="hidden" name="submitted" value="1" />
                </form>
            </div>
           
            <div id="optionsWrapper" style="display:none;">
                <script>
                    document.write(`
                        <b>Theme:</b><br />
                        <input type="radio" name="themeRadio" onchange="applyTheme('daylight');saveSettings();" id="themeDaylight"${currentTheme == "daylight" ? " checked" : ""}><label for="themeDaylight">Daylight</label><br />
                        <input type="radio" name="themeRadio" onchange="applyTheme('midnight');saveSettings();" id="themeMidnight"${currentTheme == "midnight" ? " checked" : ""}><label for="themeMidnight">Midnight</label><br />
                        <input type="radio" name="themeRadio" onchange="applyTheme('nord');saveSettings();" id="themeNord"${currentTheme == "nord" ? " checked" : ""}><label for="themeNord">Nord</label>
                        
                        <hr>
                            <label for="searchEngine">Search engine:</label>

                            <select name="searchEngine" id="searchEngine" onchange="searchEngineHandler();saveSettings();">
                                <option value="startpage"${searchEngine == "startpage" ? " selected" : ""}>Startpage</option>
                                <option value="ddg"${searchEngine == "ddg" ? " selected" : ""}>DuckDuckGo</option>
                                <option value="google"${searchEngine == "google" ? " selected" : ""}>Google</option>
                                <option value="bing"${searchEngine == "bing" ? " selected" : ""}>Bing</option>
                                <option value="yandex"${searchEngine == "yandex" ? " selected" : ""}>Yandex</option>
                                <option disabled>----------</option>
                                <option value="custom"${searchEngine == "custom" ? " selected" : ""}>Custom</option>
                            </select><br />
                            <input type="text" id="customSearchEngineBox" name="customSearchEngineBox" onkeyup="saveSettings();" placeholder="URL - use %s in place of search terms"></input>
                        <hr>

                        <input type="checkbox" onchange="saveSettings();" id="hideAddGameCheckbox"${hideAddGame == 1 ? " checked" : ""}><label for="hideAddGameCheckbox">Hide the Add Game area by default</label>
                        
                        <hr>

                        <div>
                            <b>Export data:</b>
                            
                            <form>
                                <label for="export">Table to export:</label>

                                <select name="export" id="export">
                                    <option value="all">All</option>
                                    <option value="unreleased">Unreleased</option>
                                    <option value="tba">Announced</option>
                                    <option value="released">Released</option>
                                    <option value="collected">Collected</option>
                                </select>
                                
                                <br />
                                
                                Format: 
                                <input type="radio" name="format" id="formatText" value="text" checked><label for="formatText">Plain text</label> <span title="Plain text exports only include game titles and platforms, no database IDs or release dates." onclick="javascript:alert('Plain text exports only include game titles and platforms, no database IDs or release dates.');">🛈</span>
                                <input type="radio" name="format" id="formatCSV" value="csv"><label for="formatCSV">CSV</label>
                                <input type="radio" name="format" id="formatJSON" value="json"><label for="formatJSON">JSON</label>
                                <input type="radio" name="format" id="formatHTML" value="html"><label for="formatHTML">HTML</label>

                                <br />

                                Ordering: <input type="radio" name="order" id="orderAZ" value="az" checked><label for="orderAZ">Alphabetical</label>
                                <input type="radio" name="order" id="orderDB" value="db"><label for="orderDB">Database</label>
                                
                                <br /><br />

                                <input type="submit" value="Export">
                            </form>
                        </div>
                    `);
                </script>
            </div>

            <div id="savedGamesAreaWrapper">
                <div id="tabs">
                        <span id="unreleasedTab" class="tab activetab" onclick="changeTab('unreleased');">Unreleased</span>
                        <span id="tbaTab" class="tab" onclick="changeTab('tba');">Announced</span>
                        <span id="releasedTab" class="tab" onclick="changeTab('released');">Released</span>
                        <span id="collectionTab" class="tab" onclick="changeTab('collected');">Collected</span>
                </div>

                <div class="tableWrapper" id="unreleasedWrapper">
                    <h2>Unreleased</h2>
                    <table>
                        <thead>
                            <th>Name</th>
                            <th>Release date</th>
                            <th>Platforms</th>
                            <th>Options</th>
                        </thead>
                        <tbody>
                            <?php 
                                $numrows = mysqli_num_rows($resGamesUnreleased); 
                                for($i = 0; $i < $numrows; $i++) {
                                    $gameID = mysqli_result($resGamesUnreleased,$i,"ID");
                                    $gameTitle = mysqli_result($resGamesUnreleased,$i,"GameName");
                                    $bareGameTitle = $gameTitle;
                                    $gameTitle = htmlentities($gameTitle);
                                    $releaseDate = mysqli_result($resGamesUnreleased,$i,"ReleaseDate");
                                    $platforms = explode("|", mysqli_result($resGamesUnreleased,$i,"Platforms"));

                                    $remaining = ceil((strtotime($releaseDate) - time())/60/60/24);
                                    $remainingStr = "";

                                    if($remaining == 2) {
                                        $remainingStr = "Releases the day after tomorrow";
                                    } elseif($remaining == 1) {
                                        $remainingStr = "Releases tomorrow";
                                    } elseif($remaining == 0) {
                                        $remainingStr = "Releases today";
                                    } elseif($remaining == -1) {
                                        $remainingStr = "Released yesterday";
                                    } elseif($remaining == -2) {
                                        $remainingStr = "Released the day before yesterday";
                                    } elseif($remaining > 0) {
                                        $remainingStr = "Releases in $remaining days";
                                    } elseif($remaining < -2) {
                                        $remainingStr = "Released";
                                    }

                                    $class = "";
                                    if($remaining < 1) {
                                        $class = "gameReleased";
                                    }

                                    $outputstring = "<tr id='u$gameID'><td>$gameTitle</td><td><span title='$remainingStr' class='$class'>$releaseDate</span></td><td>";

                                    if(sizeof($platforms) > 0 && $platforms[0] !== "") {
                                        for($j = 0; $j < sizeof($platforms); $j++) {
                                            $outputstring .= "<span class='platformLabel'>$platforms[$j]</span>";
                                        }
                                    }

                                    $searchString = urlencode($bareGameTitle);

                                    $outputstring .= "</td><td><span onclick='editGame(\"unreleased\", $gameID, this);' title='Edit' class='editButton'><img src='$editImage' width='24' height='24'></span><span class='searchButton' onclick='doSearch(\"$searchString\")' title='Search the web for this game'><img src='$searchImage' width='24' height='24' /></span><span onclick='deleteGame($gameID, \"unreleased\");'; title='Delete' class='deleteButton'><img src='$deleteImage' width='24' height='24'></span></td></tr>";

                                    print $outputstring;
                                }

                                print "<tr><td colspan='4' class='tableFooter'>Total: <b>$numrows</b> games</td></tr>";
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="tableWrapper" id="tbaWrapper">
                    <h2>Announced</h2>
                    <table>
                        <thead>
                            <th>Name</th>
                            <th>Platforms</th>
                            <th>Options</th>
                        </thead>
                        <tbody>
                            <?php 
                                $numrows = mysqli_num_rows($resGamesTBA); 
                                for($i = 0; $i < $numrows; $i++) {
                                    $gameID = mysqli_result($resGamesTBA,$i,"ID");
                                    $gameTitle = mysqli_result($resGamesTBA,$i,"GameName");
                                    $bareGameTitle = $gameTitle;
                                    $gameTitle = htmlentities($gameTitle);
                                    $releaseDate = mysqli_result($resGamesTBA,$i,"ReleaseDate");
                                    $platforms = explode("|", mysqli_result($resGamesTBA,$i,"Platforms"));

                                    $outputstring = "<tr id='t$gameID'><td>$gameTitle</td><td>";

                                    if(sizeof($platforms) > 0 && $platforms[0] !== "") {
                                        for($j = 0; $j < sizeof($platforms); $j++) {
                                            $outputstring .= "<span class='platformLabel'>$platforms[$j]</span>";
                                        }
                                    }

                                    $searchString = urlencode($bareGameTitle);

                                    $outputstring .= "</td><td><span onclick='editGame(\"tba\", $gameID, this);' title='Edit' class='editButton'><img src='$editImage' width='24' height='24'></span><span class='searchButton' onclick='doSearch(\"$searchString\")' title='Search the web for this game'><img src='$searchImage' width='24' height='24' /></span><span onclick='deleteGame($gameID, \"tba\");'; title='Delete' class='deleteButton'><img src='$deleteImage' width='24' height='24'></span></td></tr>";

                                    print $outputstring;
                                }

                                print "<tr><td colspan='4' class='tableFooter'>Total: <b>$numrows</b> games</td></tr>";
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="tableWrapper" id="releasedWrapper">
                    <h2>Released</h2>
                    <table>
                        <thead>
                            <th>Name</th>
                            <th>Platforms</th>
                            <th>Options</th>
                        </thead>
                        <tbody>
                            <?php 
                                $numrows = mysqli_num_rows($resGamesReleased); 
                                for($i = 0; $i < $numrows; $i++) {
                                    $gameID = mysqli_result($resGamesReleased,$i,"ID");
                                    $gameTitle = mysqli_result($resGamesReleased,$i,"GameName");
                                    $bareGameTitle = $gameTitle;
                                    $gameTitle = htmlentities($gameTitle);
                                    $platforms = explode("|", mysqli_result($resGamesReleased,$i,"Platforms"));

                                    $outputstring = "<tr id='r$gameID'><td>$gameTitle</td><td>";

                                    if(sizeof($platforms) > 0 && $platforms[0] !== "") {
                                        for($j = 0; $j < sizeof($platforms); $j++) {
                                            $outputstring .= "<span class='platformLabel'>$platforms[$j]</span>";
                                        }
                                    }

                                    $searchString = urlencode($bareGameTitle);

                                    $outputstring .= "</td><td><span onclick='editGame(\"released\", $gameID, this);' title='Edit' class='editButton'><img src='$editImage' width='24' height='24'></span><span class='searchButton' onclick='doSearch(\"$searchString\")' title='Search the web for this game'><img src='$searchImage' width='24' height='24' /></span><span onclick='deleteGame($gameID, \"released\");'; title='Delete' class='deleteButton'><img src='$deleteImage' width='24' height='24'></span></td></tr>";

                                    print $outputstring;
                                }

                                print "<tr><td colspan='4' class='tableFooter'>Total: <b>$numrows</b> games</td></tr>";
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="tableWrapper" id="collectionWrapper">
                    <h2>Collection</h2>
                    <table>
                        <thead>
                            <th>Name</th>
                            <th>Platforms</th>
                            <th>Options</th>
                        </thead>
                        <tbody>
                            <?php 
                                $numrows = mysqli_num_rows($resGamesCollected); 
                                for($i = 0; $i < $numrows; $i++) {
                                    $gameID = mysqli_result($resGamesCollected,$i,"ID");
                                    $gameTitle = mysqli_result($resGamesCollected,$i,"GameName");
                                    $bareGameTitle = $gameTitle;
                                    $gameTitle = htmlentities($gameTitle);
                                    $platforms = explode("|", mysqli_result($resGamesCollected,$i,"Platforms"));

                                    $outputstring = "<tr id='c$gameID'><td>$gameTitle</td><td>";

                                    if(sizeof($platforms) > 0 && $platforms[0] !== "") {
                                        for($j = 0; $j < sizeof($platforms); $j++) {
                                            $outputstring .= "<span class='platformLabel'>$platforms[$j]</span>";
                                        }
                                    }

                                    $searchString = urlencode($bareGameTitle);

                                    $outputstring .= "</td><td><span onclick='editGame(\"collection\", $gameID, this);' title='Edit' class='editButton'><img src='$editImage' width='24' height='24'></span><span class='searchButton' onclick='doSearch(\"$searchString\")' title='Search the web for this game'><img src='$searchImage' width='24' height='24' /></span><span onclick='deleteGame($gameID, \"collection\");'; title='Delete' class='deleteButton'><img src='$deleteImage' width='24' height='24'></span></td></tr>";

                                    print $outputstring;
                                }

                                print "<tr><td colspan='4' class='tableFooter'>Total: <b>$numrows</b> games</td></tr>";
                            ?>
                        </tbody>
                    </table>
                </div>
            </div><?php } else { ?>

            <div id="updateWrapper">
                <?php require("./update.php"); ?>
            </div>
            
            <?php } ?>

            <div id="footer">
                GameHorizon <?php $commitHash = substr(file_get_contents('.git/refs/heads/main'),0,7); print("(ver. <a href='https://github.com/xdpirate/gamehorizon/commit/$commitHash'>$commitHash</a>)"); ?> &copy; 2023 xdpirate. Licensed under the <a href="https://github.com/xdpirate/gamehorizon/blob/main/LICENSE.md" target="_blank">GNU General Public License v3.0</a>. <a href="https://github.com/xdpirate/gamehorizon" target="_blank">Github</a> <?php if($updaterEnabled == true) { ?><a href="./?update" title="Click to update this installation of GameHorizon. Requires git on the server.">Update</a><?php } ?>
            </div>
        </div>

        <?php if(!isset($_GET['update'])) { ?><script>
            document.getElementById("editModalOtherPlatforms").readOnly = true;
            document.getElementById("addGameOtherPlatforms").readOnly = true;

            document.getElementById("unreleased").onchange = function() {
                if(this.checked) {
                    document.getElementById("releaseDate").readOnly = false;
                }
            };

            document.getElementById("tba").onchange = function() {
                if(this.checked) {
                    document.getElementById("releaseDate").readOnly = true;
                }
            };

            document.getElementById("released").onchange = function() {
                if(this.checked) {
                    document.getElementById("releaseDate").readOnly = true;
                }
            };
            
            document.getElementById("collection").onchange = function() {
                if(this.checked) {
                    document.getElementById("releaseDate").readOnly = true;
                }
            };

            document.getElementById("platformCheckboxOther").onchange = function() {
                if(this.checked) {
                    document.getElementById("addGameOtherPlatforms").readOnly = false;
                } else {
                    document.getElementById("addGameOtherPlatforms").readOnly = true;
                }
            };
            
            document.getElementById("editModalUnreleased").onchange = function() {
                if(this.checked) {
                    document.getElementById("editModalReleaseDate").readOnly = false;
                }
            };

            document.getElementById("editModalTBA").onchange = function() {
                if(this.checked) {
                    document.getElementById("editModalReleaseDate").readOnly = true;
                }
            };

            document.getElementById("editModalReleased").onchange = function() {
                if(this.checked) {
                    document.getElementById("editModalReleaseDate").readOnly = true;
                }
            };
            
            document.getElementById("editModalCollection").onchange = function() {
                if(this.checked) {
                    document.getElementById("editModalReleaseDate").readOnly = true;
                }
            };

            document.getElementById("editModalCheckboxOther").onchange = function() {
                if(this.checked) {
                    document.getElementById("editModalOtherPlatforms").readOnly = false;
                } else {
                    document.getElementById("editModalOtherPlatforms").readOnly = true;
                }
            };

            function toggleNewGameDiv() {
                let display = document.getElementById("newGameWrapper").style.display;
                if(display == "none" || display == "") {
                    document.getElementById("optionsWrapper").style.display = "none";
                    document.getElementById("newGameWrapper").style.display = "block";
                } else {
                    document.getElementById("newGameWrapper").style.display = "none";
                }
            }
            
            function toggleOptionsDiv() {
                if(document.getElementById("optionsWrapper").style.display == "none") {
                    document.getElementById("newGameWrapper").style.display = "none";
                    document.getElementById("optionsWrapper").style.display = "block";
                } else {
                    document.getElementById("optionsWrapper").style.display = "none";
                }
            }

            function editGame(table, id, editBtn) {
                let row = editBtn.parentNode.parentNode;
                let gameName = row.firstElementChild.innerText.trim();
                let releaseDate = "";
                
                if(table == "unreleased") {
                    releaseDate = row.firstElementChild.nextElementSibling.innerText.trim();
                }

                document.getElementById("editModalGameTitle").value = gameName;

                if(table == "unreleased") {
                    document.getElementById("editModalUnreleased").checked = true;
                    document.getElementById("editModalTBA").checked = false;
                    document.getElementById("editModalReleased").checked = false;
                    document.getElementById("editModalCollection").checked = false;
                    document.getElementById("editModalReleaseDate").value = releaseDate;
                    document.getElementById("editModalReleaseDate").readOnly = false;
                } else if(table == "tba") {
                    document.getElementById("editModalUnreleased").checked = false;
                    document.getElementById("editModalTBA").checked = true;
                    document.getElementById("editModalReleased").checked = false;
                    document.getElementById("editModalCollection").checked = false;
                    document.getElementById("editModalReleaseDate").value = "0000-00-00";
                    document.getElementById("editModalReleaseDate").readOnly = true;
                } else if(table == "released") {
                    document.getElementById("editModalUnreleased").checked = false;
                    document.getElementById("editModalTBA").checked = false;
                    document.getElementById("editModalReleased").checked = true;
                    document.getElementById("editModalCollection").checked = false;
                    document.getElementById("editModalReleaseDate").value = "0000-00-00";
                    document.getElementById("editModalReleaseDate").readOnly = true;
                } else if(table == "collection") {
                    document.getElementById("editModalUnreleased").checked = false;
                    document.getElementById("editModalTBA").checked = false;
                    document.getElementById("editModalReleased").checked = false;
                    document.getElementById("editModalCollection").checked = true;
                    document.getElementById("editModalReleaseDate").value = "0000-00-00";
                    document.getElementById("editModalReleaseDate").readOnly = true;
                }
                
                let platformNode = releaseDate = row.firstElementChild.nextElementSibling;
                
                if(table == "unreleased") {
                    platformNode = row.firstElementChild.nextElementSibling.nextElementSibling;
                }

                let platformSpans = platformNode.querySelectorAll("span");
                let platforms = [];

                for(let i = 0; i < platformSpans.length; i++) {
                    platforms.push(platformSpans[i].innerText.trim());
                }

                // Determine which defined platforms to pre-check
                let checkboxes = document.getElementsByName("editModalPlatforms[]");
                for(let i = 0; i < checkboxes.length; i++) {
                    if(platforms.includes(checkboxes[i].value)) {
                        checkboxes[i].checked = true;
                    } else {
                        checkboxes[i].checked = false;
                    }
                }

                // Determine if there are any platforms that don't have predefined checkboxes
                document.getElementById("editModalCheckboxOther").checked = false;
                document.getElementById("editModalOtherPlatforms").value = "";
                let othersUsed = false;
                for(let i = 0; i < platforms.length; i++) {
                    if(platformList.includes(platforms[i]) == false) {
                        othersUsed = true;
                        document.getElementById("editModalCheckboxOther").checked = true;
                        document.getElementById("editModalOtherPlatforms").readOnly = false;
                        document.getElementById("editModalOtherPlatforms").value += platforms[i] + ", "
                    }
                }

                // Remove trailing comma and space
                if(othersUsed) {
                    document.getElementById("editModalOtherPlatforms").value = document.getElementById("editModalOtherPlatforms").value.slice(0, -2);
                }

                document.getElementById("editID").value = id;
                document.getElementById("editStatus").value = table;

                document.getElementById("modalbg").style.display = "block";
            }

            function changeTab(tabName) {
                let unreleasedTab = document.getElementById("unreleasedTab");
                let tbaTab = document.getElementById("tbaTab");
                let releasedTab = document.getElementById("releasedTab");
                let collectionTab = document.getElementById("collectionTab");
                let activeTab;

                unreleasedTab.classList.remove("activetab");
                tbaTab.classList.remove("activetab");
                releasedTab.classList.remove("activetab");
                collectionTab.classList.remove("activetab");

                if(tabName == "unreleased") {
                    activeTab = unreleasedTab;
                } else if(tabName == "tba") {
                    activeTab = tbaTab;
                } else if(tabName == "released") {
                    activeTab = releasedTab;
                } else if(tabName == "collected") {
                    activeTab = collectionTab;
                } else if(tabName == "collection") {
                    activeTab = collectionTab;
                    tabName = "collected";
                }
                
                activeTab.classList.add("activetab");

                if(activeTab.id == "unreleasedTab") {
                    document.getElementById("unreleasedWrapper").style.display = "block";
                    document.getElementById("tbaWrapper").style.display = "none";
                    document.getElementById("releasedWrapper").style.display = "none";
                    document.getElementById("collectionWrapper").style.display = "none";
                } else if(activeTab.id == "tbaTab") {
                    document.getElementById("unreleasedWrapper").style.display = "none";
                    document.getElementById("tbaWrapper").style.display = "block";
                    document.getElementById("releasedWrapper").style.display = "none";
                    document.getElementById("collectionWrapper").style.display = "none";
                } else if(activeTab.id == "releasedTab") {
                    document.getElementById("unreleasedWrapper").style.display = "none";
                    document.getElementById("tbaWrapper").style.display = "none";
                    document.getElementById("releasedWrapper").style.display = "block";
                    document.getElementById("collectionWrapper").style.display = "none";
                } else if(activeTab.id == "collectionTab") {
                    document.getElementById("unreleasedWrapper").style.display = "none";
                    document.getElementById("tbaWrapper").style.display = "none";
                    document.getElementById("releasedWrapper").style.display = "none";
                    document.getElementById("collectionWrapper").style.display = "block";
                }

                const url = new URL(window.location);
                url.searchParams.set("t", tabName);
                window.history.replaceState({}, "", url);
            }

            function deleteGame(gameID, table) {
                if(confirm("Are you sure you want to delete this game?\nThis cannot be undone!")) {
                    window.location.replace(`./?delete=${gameID}&from=${table}`);
                }
            }

            let params = new URLSearchParams(window.location.search);
            let switchTable = params.get("t");
            if(switchTable) {
                changeTab(switchTable);
            }

            document.getElementById("customSearchEngineBox").value = searchEngineCustomURL;
            searchEngineHandler();
        </script>
        <?php } ?>
    </body>
</html>
