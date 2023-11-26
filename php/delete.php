<?php
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
    } else {
        die("Malformed request.");
    }

    mysqli_query($link, "DELETE FROM $table WHERE ID=$ID");
    mysqli_close($link);

    header("Location: ./?t=$_GET[from]");
}
?>
