<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", 1);

require("./credentials.php");

function mysqli_result($res,$row=0,$col=0){ 
    $numrows = mysqli_num_rows($res); 
    if($numrows && $row <= ($numrows-1) && $row >=0){
        mysqli_data_seek($res,$row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
        if(isset($resrow[$col])){
            return $resrow[$col];
        }
    }
    return false;
}

$link = mysqli_connect($mysqlHost, $mysqlUser, $mysqlPassword, "gamehorizon");
if(!$link) {
    die("Couldn't connect: " . mysqli_error($link));
}

if(isset($_GET['delete']) && isset($_GET['from'])) {
    $ID = mysqli_real_escape_string($link, $_GET['delete']);

    if($_GET['from'] == "unreleased") {
        $table = "gamesUnreleased";
    } elseif($_GET['from'] == "tba") {
        $table = "gamesTBA";
    } elseif($_GET['from'] == "released") {
        $table = "gamesReleased";
    } elseif($_GET['from'] == "collection") {
        $table = "gamesCollected";
    }

    mysqli_query($link, "DELETE FROM $table WHERE ID=$ID");

    mysqli_close($link);

    header("Location: ./");
}

if(isset($_GET['editID']) && isset($_GET['editStatus'])) {
    $gameID = mysqli_real_escape_string($link, $_GET['editID']);
    $gameName = mysqli_real_escape_string($link, $_GET['editModalGameTitle']);
    
    $oldTable = $_GET['editStatus'];
    $newTable = $_GET['editModalReleaseStatus'];
    $releaseDate = "";

    if($oldTable == "unreleased") {
        $oldTable = "gamesUnreleased";
    } elseif($oldTable == "tba") {
        $oldTable = "gamesTBA";
    } elseif($oldTable == "released") {
        $oldTable = "gamesReleased";
    } elseif($oldTable == "collection") {
        $oldTable = "gamesCollected";
    } else {
        die("Malformed request.");
    }

    if($newTable == "unreleased") {
        $newTable = "gamesUnreleased";
        $releaseDate = mysqli_real_escape_string($link, $_GET['editModalReleaseDate']);
    } elseif($newTable == "tba") {
        $newTable = "gamesTBA";
    } elseif($newTable == "released") {
        $newTable = "gamesReleased";
    } elseif($newTable == "collection") {
        $newTable = "gamesCollected";
    } else {
        die("Malformed request.");
    }

    $platforms = [];

    if(isset($_GET["editModalPS5"]) && $_GET["editModalPS5"] == "on") {
        array_push($platforms, "PS5");
    }
    if(isset($_GET["editModalPS4"]) && $_GET["editModalPS4"] == "on") {
        array_push($platforms, "PS4");
    }
    if(isset($_GET["editModalPSVR2"]) && $_GET["editModalPSVR2"] == "on") {
        array_push($platforms, "PSVR2");
    }
    if(isset($_GET["editModalPC"]) && $_GET["editModalPC"] == "on") {
        array_push($platforms, "PC");
    }
    if(isset($_GET["editModalSwitch"]) && $_GET["editModalSwitch"] == "on") {
        array_push($platforms, "Switch");
    }
    if(isset($_GET["editModalAndroid"]) && $_GET["editModalAndroid"] == "on") {
        array_push($platforms, "Android");
    }

    $platforms = mysqli_real_escape_string($link, implode("|", $platforms));

    if($oldTable == $newTable) {
        // Same tables, update record
        if($oldTable == "gamesUnreleased") {
            mysqli_query($link, "UPDATE gamesUnreleased SET GameName = '$gameName', ReleaseDate = '$releaseDate', Platforms = '$platforms' WHERE ID=$gameID");
        } else {
            mysqli_query($link, "UPDATE $oldTable SET GameName = '$gameName', Platforms = '$platforms' WHERE ID=$gameID");
        }
    } else {
        // Different tables, migrate data and remove old record
        if($newTable == "gamesUnreleased") {
            mysqli_query($link, "INSERT INTO gamesUnreleased (GameName,ReleaseDate,Platforms) VALUES ('$gameName','$releaseDate','$platforms')");
        } else {
            mysqli_query($link, "INSERT INTO $newTable (GameName,Platforms) VALUES ('$gameName','$platforms')");
        }

        mysqli_query($link, "DELETE FROM $oldTable WHERE ID=$gameID");
    }
}

if(isset($_GET['submitted']) && $_GET['submitted'] == "1") {
    $gameName = mysqli_real_escape_string($link, trim($_GET["gameTitle"]));
    $releaseStatus = $_GET["releaseStatus"];
    $releaseDate = "";
    $platforms = [];

    if(isset($_GET["ps5"]) && $_GET["ps5"] == "on") {
        array_push($platforms, "PS5");
    }
    if(isset($_GET["ps4"]) && $_GET["ps4"] == "on") {
        array_push($platforms, "PS4");
    }
    if(isset($_GET["psvr2"]) && $_GET["psvr2"] == "on") {
        array_push($platforms, "PSVR2");
    }
    if(isset($_GET["pc"]) && $_GET["pc"] == "on") {
        array_push($platforms, "PC");
    }
    if(isset($_GET["switch"]) && $_GET["switch"] == "on") {
        array_push($platforms, "Switch");
    }
    if(isset($_GET["android"]) && $_GET["android"] == "on") {
        array_push($platforms, "Android");
    }

    $platforms = mysqli_real_escape_string($link, implode("|", $platforms));

    if($releaseStatus == "unreleased") {
        $releaseDate = mysqli_real_escape_string($link, $_GET["releaseDate"]);
        mysqli_query($link, "INSERT INTO gamesUnreleased (GameName,ReleaseDate,Platforms) VALUES ('$gameName','$releaseDate','$platforms')");
    } elseif($releaseStatus == "tba") {
        mysqli_query($link, "INSERT INTO gamesTBA (GameName,Platforms) VALUES ('$gameName','$platforms')");
    } elseif($releaseStatus == "released") {
        mysqli_query($link, "INSERT INTO gamesReleased (GameName,Platforms) VALUES ('$gameName','$platforms')");
    } elseif($releaseStatus == "collection") {
        mysqli_query($link, "INSERT INTO gamesCollected (GameName,Platforms) VALUES ('$gameName','$platforms')");
    }

    mysqli_close($link);

    header("Location: ./");
}

$resGamesReleased = mysqli_query($link, "SELECT * FROM gamesReleased ORDER BY GameName ASC;");
$resGamesUnreleased = mysqli_query($link, "SELECT * FROM gamesUnreleased ORDER BY ReleaseDate ASC;");
$resGamesTBA = mysqli_query($link, "SELECT * FROM gamesTBA ORDER BY GameName ASC;");
$resGamesCollected = mysqli_query($link, "SELECT * FROM gamesCollected ORDER BY GameName ASC;");

mysqli_close($link);

$editImage = "data:image/png;base64,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";

$deleteImage = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAABbmlDQ1BpY2MAACiRdZE5SwNRFIW/LBLRiIIWIgopolhEEAWx1FjYBAkxglGbyWQTsgwzCRJsBRsLwUK0cSv8B9oKtgqCoAgidvZujYTxPkeISPKGN/fjvHcud86AO5LXC5Z3CgrFshmbDQcWE0sB3wteBuhiFL+mW8Z0NBqh6fq8w6Xq7Yjq1fxew9WeSls6uFqFJ3TDLAvLNETWyobiLeEePaelhA+FQ6YMKHyl9KTDz4qzDr8rNuOxGXCrnoHsH07+YT1nFoSHhYOFfEX/nUd9iT9dXJiX2ie7H4sYs4QJkKTCKnnKjEgtSmaNfaM/vjlK4tHlbVDFFEeWnHhDolaka1pqRvS0PHmqKvf/eVqZ8TGnuz8MLU+2/TYIvh2obdv215Ft147B8wgXxbq/JDlNfoi+XdeCB9C5AWeXdS25C+eb0PtgaKb2I3lkuzMZeD2FjgR030DbspPV7zkn9xBfl190DXv7MCT3O1e+ASAkaBhQp6ckAAAACXBIWXMAAC4jAAAuIwF4pT92AAADPElEQVRYCe1WvW4TQRDe+3ViGyEItpxX4CVsJJCQaGgoqBE1b0OHREdBQ4OEBBLOQxAJHiAYBwnRmMRnfMw3c3M/e947H02arGPv7uzMfN98O2fHmOtxxQp4XfA/fPo8J/9pS8zJw/v3Zi0++XGYr/ZYbJLNdPnrt0nTdKe353lmfOtmG8FKbCcCf5OEwftxdPj0yeOLcqY3b9/Fq3VyCZ8uo3YF7z/O55SgUxUdAE8ePZjNyv41BU6/fps6FC7H/e+6VlhNAc388tVr66Jli09eWSxTsWp45pP5kvXF82c7sWoK5BlocXa2MJ6XmuPjSZ5ebJ6ZTMbsqsCL7z9oL3YluVws2Wc8GfG868NJYHhjaJJkTU1XVIHuT9BksFkKrJMNjGZbQllTPEg1Dd912O/3zcFhn4+ZBH/QlmatuhKL8xxMNRBw7FzDqcB2m5o4jotKGZiLr+USAAJjPAsONiZXC2ODUwFIjHccR2YwHJiUvmTsAShIzoqUwGHnK2JwRFmkYMpGA4EsNcVGEZEYDKhCJeFxSrkKTS4zA2vFmDVEEa3ZfQWsgFSHpGEYGvQFSKDVdvVBmqNlpDLC5ca08E2DAoCgFxMhSJr9wDe9gx4FFWF6DnAplsDxBw7Mg6w829CydyqQJ6bgbUoVZ0R8zzcRNSfAQEqT86UAKDNhyWoxE2x2j6IU61wBkVHXKT0ZWHtEIo57Wc0FapoVy+CUT3ylACt9vnUS2G7p5lANKgcwvVExCqJPbq6InhD8BAMQ4DBiLUN8lLxa7dlJgAMBRYgAFnBAg5jYAeZTc0JqgDMHxPCLTOyHqaBlE9irB6QK6QPKnZPSxEEQlMAFQmLIFwENw6mAys0pSk3IiSmhAEAJECNV/EL+/KwFHLwaFMDTCwB5F1eg4KKI2smRr0L9Me8zGghk4ERCQSQ5iAmJih3GrFkpEru9RjMBVqBaMbKCCH6sKtXiKYF/B3DkchJAdfgN0DvWxzAIEFIFxxdVEAYmQMbSiPCEtAynx5/VyozHd2rVjsZHeeXlb8ij0e0alNouLyr/QFf8nAR+np+fbpLkLsvMFetV0P1SxdyipBLO20YQhl/afK7Pr0yBf8VNbF6y8h0lAAAAAElFTkSuQmCC";

$searchImage = "data:image/png;base64,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";
?>

<!DOCTYPE html>

<html>
    <head>
        <title>GameHorizon</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            html,body {
                font-family: Arial, Helvetica, sans-serif;
            }

            #everything {
                width: 50%;
                margin: auto;
            }

            #modal {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 600px;
                height: 150px;
                background-color: white;
                padding: 20px;
                border: 1px solid black;
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

            #newGameWrapper {
                border: 1px solid black;
                border-radius: 10px;
                padding: 10px;
                margin-bottom: 20px;
            }

            h1 {
                margin: 0;
            }

            span.tab {
                margin-left: 5px;
                margin-right: 5px;
                cursor: pointer;
            }

            span.activetab {
                font-weight: bold;
                text-decoration: underline;
            }

            h1 > a {
                text-decoration: none;
                color: #000;
            }

            table {
                width: 100%;
            }

            span.platformLabel {
                border: 1px solid blue;
                background-color: #000099;
                border-radius: 10px;
                color: #FFF;
                font-weight: bold;
                margin-left: 2px;
                margin-right: 2px;
                padding: 3px;
            }

            tr:nth-child(even) {
                background-color: #ccc;
            }

            #newEntryToggleDiv {
                text-decoration: underline;
                cursor: pointer;
            }

            .editButton {
                cursor: pointer;
            }

            .tableFooter {
                width: 100%;
                text-align: right;
            }

            th {
                text-align: left;
            }

            td,th {
                padding: 5px;
            }

            tr:nth-child(even) {
                background: #d2e2f9;
            }

            tr:nth-child(odd) {
                background: #a5c6f3;
            }

            th {
                background-color: #d2e2f9;
            }

            #tbaWrapper, #releasedWrapper, #collectionWrapper {
                display: none;
            }

            /* Phone styles */
            @media all and (max-width: 1000px) {
                #everything {
                    width: 100%;
                    margin: auto;
                }
            }
        </style>

        <link href="./favicon.png" rel="icon" type="image/png" />

        <script>let toastTimeout;</script>
    </head>
    
    <body>
        <div id="modalbg" id="editModal" style="display: none;">
            <div id="modal">
                <b>Editing <i><span id="editModalGameName"></span></i></b>
                <form id="editForm">
                    <input type="text" id="editModalGameTitle" name="editModalGameTitle" style="width: 98%;" placeholder="Game title"></input><br />

                    <input type="radio" id="editModalUnreleased" name="editModalReleaseStatus" value="unreleased" checked><label for="editModalUnreleased">Release date:</label> <input type="date" id="editModalReleaseDate" name="editModalReleaseDate">

                    <input type="radio" id="editModalTBA" name="editModalReleaseStatus" value="tba"><label for="editModalTBA">TBA</label>

                    <input type="radio" id="editModalReleased" name="editModalReleaseStatus" value="released"><label for="editModalReleased">Released</label>

                    <input type="radio" id="editModalCollection" name="editModalReleaseStatus" value="collection"><label for="editModalCollection">Collected</label>

                    <br />
                    Platform(s): 
                        <input type="checkbox" id="editModalPS5" name="editModalPS5" /><label for="editModalPS5">PS5</label>
                        <input type="checkbox" id="editModalPS4" name="editModalPS4" /><label for="editModalPS4">PS4</label>
                        <input type="checkbox" id="editModalPSVR2" name="editModalPSVR2" /><label for="editModalPSVR2">PSVR2</label>
                        <input type="checkbox" id="editModalPC" name="editModalPC" /><label for="editModalPC">PC</label>
                        <input type="checkbox" id="editModalSwitch" name="editModalSwitch" /><label for="editModalSwitch">Switch</label>
                        <input type="checkbox" id="editModalAndroid" name="editModalAndroid" /><label for="editModalAndroid">Android</label>
                        
                    <br /><br />
                    <input type="submit" value="Save" style="width: 49%; height: 4em;" id="editModalSaveButton" /> 
                    <input type="button" value="Cancel" style="width: 49%; height: 4em;" id="editModalCancelButton" onclick="document.getElementById('modalbg').style.display = 'none';" />

                    <input type="hidden" name="editID" id="editID" value="0" />
                    <input type="hidden" name="editStatus" id="editStatus" value="unreleased" />
                </form>
            </div>
        </div>

        <div id="everything">
            <h1><a href="./"><img src="./favicon.png" width="32" height="32" /> GameHorizon</a></h1>
            <small><i>"This game sucks"</i> - AVGN</small>

            <div id="newEntryToggleDiv" onclick="toggleNewGameDiv();"><h3 id="formHeader">Add game</h3></div>
            <div id="newGameWrapper">
                <form id="formArea" method="GET" action="./">
                    <input type="text" id="gameTitle" name="gameTitle" style="width: 98%;" placeholder="Game title"></input><br />

                    <input type="radio" id="unreleased" name="releaseStatus" value="unreleased" checked><label for="unreleased">Release date:</label> <input type="date" id="releaseDate" name="releaseDate">
                    
                    <input type="radio" id="tba" name="releaseStatus" value="tba"><label for="tba">TBA</label>

                    <input type="radio" id="released" name="releaseStatus" value="released"><label for="released">Released</label>
                    
                    <input type="radio" id="collection" name="releaseStatus" value="collection"><label for="collection">Collected</label>

                    <br />
                    Platform(s): 
                        <input type="checkbox" id="ps5" name="ps5" /><label for="ps5">PS5</label>
                        <input type="checkbox" id="ps4" name="ps4" /><label for="ps4">PS4</label>
                        <input type="checkbox" id="psvr2" name="psvr2" /><label for="psvr2">PSVR2</label>
                        <input type="checkbox" id="pc" name="pc" /><label for="pc">PC</label>
                        <input type="checkbox" id="switch" name="switch" /><label for="switch">Switch</label>
                        <input type="checkbox" id="android" name="android" /><label for="android">Android</label>
                        
                    <br /><br />
                    <input type="submit" value="Save" style="width: 49%; height: 4em;" id="saveButton" /> 
                    <input type="button" value="Clear" style="width: 49%; height: 4em;" id="clearButton" onclick="this.parentNode.reset();" /> 

                    <input type="hidden" name="submitted" value="1" />
                </form>
            </div>
           
            <div id="savedGamesAreaWrapper">
                <div id="tabs">
                        <span id="unreleasedTab" class="tab activetab" onclick="changeTab(this, 'unreleased');">Unreleased</span>
                        <span id="tbaTab" class="tab" onclick="changeTab(this, 'tba');">Announced</span>
                        <span id="releasedTab" class="tab" onclick="changeTab(this, 'released');">Released</span>
                        <span id="collectionTab" class="tab" onclick="changeTab(this, 'collection');">Collected</span>
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
                                    $gameTitle = htmlentities(mysqli_result($resGamesUnreleased,$i,"GameName"));
                                    $releaseDate = mysqli_result($resGamesUnreleased,$i,"ReleaseDate");
                                    $platforms = explode("|", mysqli_result($resGamesUnreleased,$i,"Platforms"));

                                    $remaining = ceil((strtotime($releaseDate) - time())/60/60/24);

                                    if($remaining == 2) {
                                        $remaining = "Releases the day after tomorrow";
                                    } elseif($remaining == 1) {
                                        $remaining = "Releases tomorrow";
                                    } elseif($remaining == 0) {
                                        $remaining = "Releases today";
                                    } elseif($remaining == -1) {
                                        $remaining = "Released yesterday";
                                    } elseif($remaining == -2) {
                                        $remaining = "Released the day before yesterday";
                                    } elseif($remaining > 0) {
                                        $remaining = "Releases in $remaining days";
                                    } elseif($remaining < -2) {
                                        $remaining = "Released more than 2 days ago";
                                    }

                                    $outputstring = "<tr><td>$gameTitle</td><td><span title='$remaining'>$releaseDate</span></td><td>";

                                    if(sizeof($platforms) > 0 && $platforms[0] !== "") {
                                        for($j = 0; $j < sizeof($platforms); $j++) {
                                            $outputstring .= "<span class='platformLabel'>$platforms[$j]</span>";
                                        }
                                    }

                                    $searchString = "https://www.startpage.com/sp/search?query=" . urlencode($gameTitle);

                                    $outputstring .= "</td><td><span onclick='editGame(\"unreleased\", $gameID, this);' title='Edit' class='editButton'><img src='$editImage' width='24' height='24'></span><a href='$searchString' title='Search Startpage for this game' target='_blank'><img src='$searchImage' width='24' height='24' /></a><a href='./?delete=$gameID&from=unreleased' title='Delete' class='deleteButton'><img src='$deleteImage' width='24' height='24'></span></td></tr>";

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
                                    $gameTitle = htmlentities(mysqli_result($resGamesTBA,$i,"GameName"));
                                    $releaseDate = mysqli_result($resGamesTBA,$i,"ReleaseDate");
                                    $platforms = explode("|", mysqli_result($resGamesTBA,$i,"Platforms"));

                                    $outputstring = "<tr><td>$gameTitle</td><td>";

                                    if(sizeof($platforms) > 0 && $platforms[0] !== "") {
                                        for($j = 0; $j < sizeof($platforms); $j++) {
                                            $outputstring .= "<span class='platformLabel'>$platforms[$j]</span>";
                                        }
                                    }

                                    $searchString = "https://www.startpage.com/sp/search?query=" . urlencode($gameTitle);

                                    $outputstring .= "</td><td><span onclick='editGame(\"tba\", $gameID, this);' title='Edit' class='editButton'><img src='$editImage' width='24' height='24'></span><a href='$searchString' title='Search Startpage for this game' target='_blank'><img src='$searchImage' width='24' height='24' /></a><a href='./?delete=$gameID&from=tba' title='Delete' class='deleteButton'><img src='$deleteImage' width='24' height='24'></span></td></tr>";

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
                                    $gameTitle = htmlentities(mysqli_result($resGamesReleased,$i,"GameName"));
                                    $platforms = explode("|", mysqli_result($resGamesReleased,$i,"Platforms"));

                                    $outputstring = "<tr><td>$gameTitle</td><td>";

                                    if(sizeof($platforms) > 0 && $platforms[0] !== "") {
                                        for($j = 0; $j < sizeof($platforms); $j++) {
                                            $outputstring .= "<span class='platformLabel'>$platforms[$j]</span>";
                                        }
                                    }

                                    $searchString = "https://www.startpage.com/sp/search?query=" . urlencode($gameTitle);

                                    $outputstring .= "</td><td><span onclick='editGame(\"released\", $gameID, this);' title='Edit' class='editButton'><img src='$editImage' width='24' height='24'></span><a href='$searchString' title='Search Startpage for this game' target='_blank'><img src='$searchImage' width='24' height='24' /></a><a href='./?delete=$gameID&from=released' title='Delete' class='deleteButton'><img src='$deleteImage' width='24' height='24'></span></td></tr>";

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
                                    $gameTitle = htmlentities(mysqli_result($resGamesCollected,$i,"GameName"));
                                    $platforms = explode("|", mysqli_result($resGamesCollected,$i,"Platforms"));

                                    $outputstring = "<tr><td>$gameTitle</td><td>";

                                    if(sizeof($platforms) > 0 && $platforms[0] !== "") {
                                        for($j = 0; $j < sizeof($platforms); $j++) {
                                            $outputstring .= "<span class='platformLabel'>$platforms[$j]</span>";
                                        }
                                    }

                                    $searchString = "https://www.startpage.com/sp/search?query=" . urlencode($gameTitle);

                                    $outputstring .= "</td><td><span onclick='editGame(\"collection\", $gameID, this);' title='Edit' class='editButton'><img src='$editImage' width='24' height='24'></span><a href='$searchString' title='Search Startpage for this game' target='_blank'><img src='$searchImage' width='24' height='24' /></a><a href='./?delete=$gameID&from=collection' title='Delete' class='deleteButton'><img src='$deleteImage' width='24' height='24'></span></td></tr>";

                                    print $outputstring;
                                }

                                print "<tr><td colspan='4' class='tableFooter'>Total: <b>$numrows</b> games</td></tr>";
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="toastNotification"></div>
        
        <script>
            //document.getElementById("newGameWrapper").style.display = "none";

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

            function toggleNewGameDiv() {
                if(document.getElementById("newGameWrapper").style.display == "none") {
                    document.getElementById("newGameWrapper").style.display = "block";
                } else {
                    document.getElementById("newGameWrapper").style.display = "none";
                }
            }

            function editGame(table, id, editBtn) {
                let row = editBtn.parentNode.parentNode;
                let gameName = row.firstElementChild.innerText.trim();
                let releaseDate = "";
                
                if(table == "unreleased") {
                    releaseDate = row.firstElementChild.nextElementSibling.innerText.trim();
                }

                document.getElementById("editModalGameName").innerText = gameName;
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

                if(platforms.includes("PS5")) {
                    document.getElementById("editModalPS5").checked = true;
                } else {
                    document.getElementById("editModalPS5").checked = false;
                }

                if(platforms.includes("PS4")) {
                    document.getElementById("editModalPS4").checked = true;
                } else {
                    document.getElementById("editModalPS4").checked = false;
                }

                if(platforms.includes("PSVR2")) {
                    document.getElementById("editModalPSVR2").checked = true;
                } else {
                    document.getElementById("editModalPSVR2").checked = false;
                }

                if(platforms.includes("PC")) {
                    document.getElementById("editModalPC").checked = true;
                } else {
                    document.getElementById("editModalPC").checked = false;
                }

                if(platforms.includes("Switch")) {
                    document.getElementById("editModalSwitch").checked = true;
                } else {
                    document.getElementById("editModalSwitch").checked = false;
                }

                if(platforms.includes("Android")) {
                    document.getElementById("editModalAndroid").checked = true;
                } else {
                    document.getElementById("editModalAndroid").checked = false;
                }

                document.getElementById("editID").value = id;
                document.getElementById("editStatus").value = table;

                document.getElementById("modalbg").style.display = "block";
            }

            function changeTab(tabElement, tabName) {
                document.getElementById("unreleasedTab").classList.remove("activetab");
                document.getElementById("tbaTab").classList.remove("activetab");
                document.getElementById("releasedTab").classList.remove("activetab");
                document.getElementById("collectionTab").classList.remove("activetab");
                
                tabElement.classList.add("activetab");

                if(tabElement.id == "unreleasedTab") {
                    document.getElementById("unreleasedWrapper").style.display = "block";
                    document.getElementById("tbaWrapper").style.display = "none";
                    document.getElementById("releasedWrapper").style.display = "none";
                    document.getElementById("collectionWrapper").style.display = "none";
                } else if(tabElement.id == "tbaTab") {
                    document.getElementById("unreleasedWrapper").style.display = "none";
                    document.getElementById("tbaWrapper").style.display = "block";
                    document.getElementById("releasedWrapper").style.display = "none";
                    document.getElementById("collectionWrapper").style.display = "none";
                } else if(tabElement.id == "releasedTab") {
                    document.getElementById("unreleasedWrapper").style.display = "none";
                    document.getElementById("tbaWrapper").style.display = "none";
                    document.getElementById("releasedWrapper").style.display = "block";
                    document.getElementById("collectionWrapper").style.display = "none";
                } else if(tabElement.id == "collectionTab") {
                    document.getElementById("unreleasedWrapper").style.display = "none";
                    document.getElementById("tbaWrapper").style.display = "none";
                    document.getElementById("releasedWrapper").style.display = "none";
                    document.getElementById("collectionWrapper").style.display = "block";
                }
            }
        </script>
    </body>
</html>
