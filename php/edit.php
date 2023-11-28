<?php
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

    $targetTable = $newTable;

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
    
    if(isset($_GET['editModalPlatforms'])) {
        $platforms = $_GET['editModalPlatforms'];
    }

    if(isset($_GET['editModalCheckboxOther']) && $_GET['editModalCheckboxOther'] == "on") {
        $otherPlatforms = explode(",", mysqli_real_escape_string($link, $_GET['editModalOtherPlatforms']));

        for($i = 0; $i < sizeof($otherPlatforms); $i++) {
            array_push($platforms, trim($otherPlatforms[$i]));
        }
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

        $gameID = mysqli_insert_id($link);
        mysqli_query($link, "DELETE FROM $oldTable WHERE ID=$gameID");
    }

    $fragment = strtolower(substr($newTable, 5, 1)) . strval($gameID);

    header("Location: ./?t=$targetTable#$fragment");
}
?>
