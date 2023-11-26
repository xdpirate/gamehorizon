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

require("./php/functions.php");

$link = mysqli_connect($mysqlHost, $mysqlUser, $mysqlPassword);
if(!$link) {
    die("Couldn't connect: " . mysqli_error($link));
}

require("./php/dbsetup.php");
require("./php/export.php");
require("./php/delete.php");
require("./php/edit.php");
require("./php/create.php");
require("./php/images.php");

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
        <style></style> <!-- Defined in ./js/startscripts.js -->
        <script src="./js/startscripts.js"></script>
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
            <small><?php include("./php/quotes.php") ?></small>

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
                <script src="./js/optionsbox.js"></script>
            </div>

            <div id="savedGamesAreaWrapper">
                <div id="tabs">
                        <span id="unreleasedTab" class="tab activetab" onclick="changeTab('unreleased');">Unreleased</span>
                        <span id="tbaTab" class="tab" onclick="changeTab('tba');">Announced</span>
                        <span id="releasedTab" class="tab" onclick="changeTab('released');">Released</span>
                        <span id="collectionTab" class="tab" onclick="changeTab('collected');">Collected</span>
                </div>

                <div class="tableWrapper" id="unreleasedWrapper">
                    <h2>Unreleased
                        <div class="searchbox">
                            <input type="search" placeholder="Filter Unreleased..." oninput="filterTable('unreleased', this.value);" autocomplete="off"> <span class="pointer clearSearch" title="Clear filter" onclick="this.previousElementSibling.value = ''; filterTable('unreleased', '');">❌</span>
                        </div>
                    </h2>

                    <table>
                        <thead>
                            <th>Name</th>
                            <th>Release date</th>
                            <th>Platforms</th>
                            <th>Options</th>
                        </thead>
                        <tbody id="unreleasedTbody">
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
                    <h2>Announced
                        <div class="searchbox">
                            <input type="search" placeholder="Filter Announced..." oninput="filterTable('tba', this.value);" autocomplete="off"> <span class="pointer clearSearch" title="Clear filter" onclick="this.previousElementSibling.value = ''; filterTable('tba', '');">❌</span>
                        </div>
                    </h2>

                    <table>
                        <thead>
                            <th>Name</th>
                            <th>Platforms</th>
                            <th>Options</th>
                        </thead>
                        <tbody id="tbaTbody">
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
                    <h2>Released
                        <div class="searchbox">
                            <input type="search" placeholder="Filter Released..." oninput="filterTable('released', this.value);" autocomplete="off"> <span class="pointer clearSearch" title="Clear filter" onclick="this.previousElementSibling.value = ''; filterTable('released', '');">❌</span>
                        </div>
                    </h2>
                    
                    <table>
                        <thead>
                            <th>Name</th>
                            <th>Platforms</th>
                            <th>Options</th>
                        </thead>
                        <tbody id="releasedTbody">
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
                    <h2>Collection
                        <div class="searchbox">
                            <input type="search" placeholder="Filter Collection..." oninput="filterTable('collection', this.value);" autocomplete="off"> <span class="pointer clearSearch" title="Clear filter" onclick="this.previousElementSibling.value = ''; filterTable('collection', '');">❌</span>
                        </div>
                    </h2>
                    
                    <table>
                        <thead>
                            <th>Name</th>
                            <th>Platforms</th>
                            <th>Options</th>
                        </thead>
                        <tbody id="collectionTbody">
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
                <?php require("./php/update.php"); ?>
            </div>
            
            <?php } ?>

            <div id="footer">
                GameHorizon <?php $commitHash = substr(file_get_contents('.git/refs/heads/main'),0,7); print("(ver. <a href='https://github.com/xdpirate/gamehorizon/commit/$commitHash'>$commitHash</a>)"); ?> &copy; 2023 xdpirate. Licensed under the <a href="https://github.com/xdpirate/gamehorizon/blob/main/LICENSE.md" target="_blank">GNU General Public License v3.0</a>. <a href="https://github.com/xdpirate/gamehorizon" target="_blank">Github</a> <?php if($updaterEnabled == true) { ?><a href="./?update" title="Click to update this installation of GameHorizon. Requires git on the server.">Update</a><?php } ?>
            </div>
        </div>

        <?php if(!isset($_GET['update'])) { ?><script src="./js/endscripts.js"></script><?php } ?>
    </body>
</html>
