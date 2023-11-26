<?php
if(isset($_GET['submitted']) && $_GET['submitted'] == "1") {
    $gameName = mysqli_real_escape_string($link, trim($_GET["gameTitle"]));
    $releaseStatus = $_GET["releaseStatus"];
    $releaseDate = "";

    $platforms = [];
    
    if(isset($_GET['platforms'])) {
        $platforms = $_GET['platforms'];
    }

    if(isset($_GET['platformCheckboxOther']) && $_GET['platformCheckboxOther'] == "on") {
        $otherPlatforms = explode(",", mysqli_real_escape_string($link, $_GET['addGameOtherPlatforms']));

        for($i = 0; $i < sizeof($otherPlatforms); $i++) {
            array_push($platforms, trim($otherPlatforms[$i]));
        }
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

    $gameID = mysqli_insert_id($link);
    mysqli_close($link);

    $fragment = strtolower(substr($releaseStatus, 0, 1)) . strval($gameID);
    header("Location: ./?t=$releaseStatus#$fragment");
}
?>
