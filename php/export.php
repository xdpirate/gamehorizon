<?php
// Export game database
if(isset($_GET['export']) && isset($_GET['format'])) {
    $table = strtolower(trim($_GET['export']));
    $format = strtolower(trim($_GET['format']));

    if($table == "unreleased") {
        $table = "gamesUnreleased";
    } elseif($table == "tba") { 
        $table = "gamesTBA";
    } elseif($table == "released") { 
        $table = "gamesReleased";
    } elseif($table == "collected") { 
        $table = "gamesCollected";
    } elseif($table == "all") {
        $table = "all";
    } else {
        die("Malformed request");
    }

    $outputString = "";
    $orderStr = "";
    if($_GET['order'] == "az") {
        $orderStr = "ORDER BY GameName ASC";
    }

    if($format == "csv") {
        if($table == "all") {
            $resultsUnreleased = mysqli_query($link, "SELECT * FROM gamesUnreleased $orderStr");
            $resultsTBA = mysqli_query($link, "SELECT * FROM gamesTBA $orderStr");
            $resultsReleased = mysqli_query($link, "SELECT * FROM gamesReleased $orderStr");
            $resultsCollected = mysqli_query($link, "SELECT * FROM gamesCollected $orderStr");

            $outputString .= "Table,ID,GameName,ReleaseDate,Platforms\n";

            for($i = 0; $i < mysqli_num_rows($resultsUnreleased); $i++) {
                $gameID = mysqli_result($resultsUnreleased,$i,"ID");
                $gameTitle = mysqli_result($resultsUnreleased,$i,"GameName");
                $releaseDate = mysqli_result($resultsUnreleased,$i,"ReleaseDate");
                $platforms = mysqli_result($resultsUnreleased,$i,"Platforms");
                
                $outputString .= "gamesUnreleased,$gameID,$gameTitle,$releaseDate,$platforms\n";
            }
            
            for($i = 0; $i < mysqli_num_rows($resultsTBA); $i++) {
                $gameID = mysqli_result($resultsTBA,$i,"ID");
                $gameTitle = mysqli_result($resultsTBA,$i,"GameName");
                $platforms = mysqli_result($resultsTBA,$i,"Platforms");
                
                $outputString .= "gamesTBA,$gameID,$gameTitle,,$platforms\n";
            }

            for($i = 0; $i < mysqli_num_rows($resultsReleased); $i++) {
                $gameID = mysqli_result($resultsReleased,$i,"ID");
                $gameTitle = mysqli_result($resultsReleased,$i,"GameName");
                $platforms = mysqli_result($resultsReleased,$i,"Platforms");
                
                $outputString .= "gamesReleased,$gameID,$gameTitle,,$platforms\n";
            }

            for($i = 0; $i < mysqli_num_rows($resultsCollected); $i++) {
                $gameID = mysqli_result($resultsCollected,$i,"ID");
                $gameTitle = mysqli_result($resultsCollected,$i,"GameName");
                $platforms = mysqli_result($resultsCollected,$i,"Platforms");
                
                $outputString .= "gamesCollected,$gameID,$gameTitle,,$platforms\n";
            }

            header('Content-Disposition: attachment; filename="GameHorizon-All-'.date('YmdHis').'.csv"');
            header('Content-Type: text/csv');
            header('Content-Length: ' . strlen($outputString));
            header('Connection: close');

            echo $outputString;
        } else {
            $results = mysqli_query($link, "SELECT * FROM $table $orderStr");
            
            if($table == "gamesUnreleased") {
                $outputString .= "Table,ID,GameName,ReleaseDate,Platforms\n";
            } else {
                $outputString .= "Table,ID,GameName,Platforms\n";
            }
            
            for($i = 0; $i < mysqli_num_rows($results); $i++) {
                $gameID = mysqli_result($results,$i,"ID");
                $gameTitle = mysqli_result($results,$i,"GameName");
                $platforms = mysqli_result($results,$i,"Platforms");

                if($table == "gamesUnreleased") {
                    $releaseDate = mysqli_result($results,$i,"ReleaseDate");
                    $outputString .= "$table,$gameID,$gameTitle,$releaseDate,$platforms\n";
                } else {
                    $outputString .= "$table,$gameID,$gameTitle,$platforms\n";
                } 
            }

            header('Content-Disposition: attachment; filename="GameHorizon-'.$table.'-'.date('YmdHis').'.csv"');
            header('Content-Type: text/csv');
            header('Content-Length: ' . strlen($outputString));
            header('Connection: close');

            echo $outputString;
        }
    } elseif($format == "html") {
        if($table == "all") {
            $resultsUnreleased = mysqli_query($link, "SELECT * FROM gamesUnreleased $orderStr");
            $resultsTBA = mysqli_query($link, "SELECT * FROM gamesTBA $orderStr");
            $resultsReleased = mysqli_query($link, "SELECT * FROM gamesReleased $orderStr");
            $resultsCollected = mysqli_query($link, "SELECT * FROM gamesCollected $orderStr");

            $outputString .= "
                <!DOCTYPE html>
                <html>
                <head>
                    <title>GameHorizon Export</title>
                </head>

                <body>

                <h1>gamesUnreleased</h1>
                <table>
                    <thead>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Release date</th>
                        <th>Platforms</th>
                    </thead>
                    <tbody>                    
            ";

            for($i = 0; $i < mysqli_num_rows($resultsUnreleased); $i++) {
                $gameID = mysqli_result($resultsUnreleased,$i,"ID");
                $gameTitle = mysqli_result($resultsUnreleased,$i,"GameName");
                $releaseDate = mysqli_result($resultsUnreleased,$i,"ReleaseDate");
                $platforms = mysqli_result($resultsUnreleased,$i,"Platforms");
                
                $outputString .= "
                    <tr>
                        <td>$gameID</td><td>$gameTitle</td><td>$releaseDate</td><td>$platforms</td>
                    </tr>
                ";
            }

            $outputString .= "
                </tbody>
            </table>

            <h1>gamesTBA</h1>
                <table>
                    <thead>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Platforms</th>
                    </thead>
                    <tbody>    
            ";
            
            for($i = 0; $i < mysqli_num_rows($resultsTBA); $i++) {
                $gameID = mysqli_result($resultsTBA,$i,"ID");
                $gameTitle = mysqli_result($resultsTBA,$i,"GameName");
                $platforms = mysqli_result($resultsTBA,$i,"Platforms");
                
                $outputString .= "
                <tr>
                    <td>$gameID</td><td>$gameTitle</td><td>$platforms</td>
                </tr>
                ";
            }

            $outputString .= "
                </tbody>
            </table>

            <h1>gamesReleased</h1>
                <table>
                    <thead>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Platforms</th>
                    </thead>
                    <tbody>    
            ";

            for($i = 0; $i < mysqli_num_rows($resultsReleased); $i++) {
                $gameID = mysqli_result($resultsReleased,$i,"ID");
                $gameTitle = mysqli_result($resultsReleased,$i,"GameName");
                $platforms = mysqli_result($resultsReleased,$i,"Platforms");
                
                $outputString .= "
                <tr>
                    <td>$gameID</td><td>$gameTitle</td><td>$platforms</td>
                </tr>
                ";
            }

            $outputString .= "
                </tbody>
            </table>

            <h1>gamesCollected</h1>
                <table>
                    <thead>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Platforms</th>
                    </thead>
                    <tbody>    
            ";

            for($i = 0; $i < mysqli_num_rows($resultsCollected); $i++) {
                $gameID = mysqli_result($resultsCollected,$i,"ID");
                $gameTitle = mysqli_result($resultsCollected,$i,"GameName");
                $platforms = mysqli_result($resultsCollected,$i,"Platforms");
                
                $outputString .= "
                <tr>
                    <td>$gameID</td><td>$gameTitle</td><td>$platforms</td>
                </tr>
                ";
            }

            $outputString .= "
                        </tbody>
                    </table>
                </body>
            </html>
            ";

            header('Content-Disposition: attachment; filename="GameHorizon-All-'.date('YmdHis').'.html"');
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . strlen($outputString));
            header('Connection: close');

            echo $outputString;
        } else {
            $results = mysqli_query($link, "SELECT * FROM $table $orderStr");
            
            $outputString .= "
                <!DOCTYPE html>
                <html>
                <head>
                    <title>GameHorizon Export</title>
                </head>

                <body>

                <h1>$table</h1>
                <table>
                    <thead>
                        <th>ID</th>
                        <th>Name</th>
            ";

            if($table == "gamesUnreleased") {
                $outputString .= "<th>Release date</th>";
            }

            $outputString .= "
                        <th>Platforms</th>
                    </thead>
                    <tbody>                    
            ";

            for($i = 0; $i < mysqli_num_rows($results); $i++) {
                $gameID = mysqli_result($results,$i,"ID");
                $gameTitle = mysqli_result($results,$i,"GameName");
                $platforms = mysqli_result($results,$i,"Platforms");

                if($table == "gamesUnreleased") {
                    $releaseDate = mysqli_result($results,$i,"ReleaseDate");
                    $outputString .= "<tr><td>$gameID</td><td>$gameTitle</td><td>$releaseDate</td><td>$platforms</td></tr>";
                } else {
                    $outputString .= "<tr><td>$gameID</td><td>$gameTitle</td><td>$platforms</td></tr>";
                } 
            }

            $outputString .= "
                        </tbody>
                    </table>
                </body>
            </html>
            ";

            header('Content-Disposition: attachment; filename="GameHorizon-'.$table.'-'.date('YmdHis').'.html"');
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . strlen($outputString));
            header('Connection: close');

            echo $outputString;
        }
    } elseif($format == "text") {
        if($table == "all") {
            $resultsUnreleased = mysqli_query($link, "SELECT * FROM gamesUnreleased $orderStr");
            $resultsTBA = mysqli_query($link, "SELECT * FROM gamesTBA $orderStr");
            $resultsReleased = mysqli_query($link, "SELECT * FROM gamesReleased $orderStr");
            $resultsCollected = mysqli_query($link, "SELECT * FROM gamesCollected $orderStr");

            $outputString .= "Unreleased:\n===========\n";

            for($i = 0; $i < mysqli_num_rows($resultsUnreleased); $i++) {
                $gameTitle = mysqli_result($resultsUnreleased,$i,"GameName");
                $platforms = mysqli_result($resultsUnreleased,$i,"Platforms");

                if($platforms == "") {
                    $outputString .= "$gameTitle\n";
                } else {
                    $platforms = str_replace("|", ", ", $platforms);
                    $outputString .= "$gameTitle ($platforms)\n";
                }
            }

            $outputString .= "\nAnnounced:\n==========\n";
            
            for($i = 0; $i < mysqli_num_rows($resultsTBA); $i++) {
                $gameTitle = mysqli_result($resultsTBA,$i,"GameName");
                $platforms = mysqli_result($resultsTBA,$i,"Platforms");
               
                if($platforms == "") {
                    $outputString .= "$gameTitle\n";
                } else {
                    $platforms = str_replace("|", ", ", $platforms);
                    $outputString .= "$gameTitle ($platforms)\n";
                }
            }

            $outputString .= "\nReleased:\n=========\n";

            for($i = 0; $i < mysqli_num_rows($resultsReleased); $i++) {
                $gameTitle = mysqli_result($resultsReleased,$i,"GameName");
                $platforms = mysqli_result($resultsReleased,$i,"Platforms");
                
                if($platforms == "") {
                    $outputString .= "$gameTitle\n";
                } else {
                    $platforms = str_replace("|", ", ", $platforms);
                    $outputString .= "$gameTitle ($platforms)\n";
                }
            }

            $outputString .= "\nCollected:\n==========\n";

            for($i = 0; $i < mysqli_num_rows($resultsCollected); $i++) {
                $gameTitle = mysqli_result($resultsCollected,$i,"GameName");
                $platforms = mysqli_result($resultsCollected,$i,"Platforms");
                
                if($platforms == "") {
                    $outputString .= "$gameTitle\n";
                } else {
                    $platforms = str_replace("|", ", ", $platforms);
                    $outputString .= "$gameTitle ($platforms)\n";
                }
            }

            header('Content-Disposition: attachment; filename="GameHorizon-All-'.date('YmdHis').'.txt"');
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . strlen($outputString));
            header('Connection: close');

            echo $outputString;
        } else {
            $results = mysqli_query($link, "SELECT * FROM $table $orderStr");

            $tableName = "";
            
            if($table == "gamesUnreleased") {
                $tableName = "Unreleased";
            } elseif($table == "gamesTBA") { 
                $tableName = "Announced";
            } elseif($table == "gamesReleased") { 
                $tableName = "Released";
            } elseif($table == "gamesCollected") { 
                $tableName = "Collected";
            }

            $outputString .= "$tableName:\n==========\n";

            for($i = 0; $i < mysqli_num_rows($results); $i++) {
                $gameTitle = mysqli_result($results,$i,"GameName");
                $platforms = mysqli_result($results,$i,"Platforms");

                if($platforms == "") {
                    $outputString .= "$gameTitle\n";
                } else {
                    $platforms = str_replace("|", ", ", $platforms);
                    $outputString .= "$gameTitle ($platforms)\n";
                }
            }

            header('Content-Disposition: attachment; filename="GameHorizon-'.$tableName.'-'.date('YmdHis').'.txt"');
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . strlen($outputString));
            header('Connection: close');

            echo $outputString;
        }
    } elseif($format == "json") {
        if($table == "all") {
            $resultsUnreleased = mysqli_query($link, "SELECT * FROM gamesUnreleased $orderStr");
            $resultsTBA = mysqli_query($link, "SELECT * FROM gamesTBA $orderStr");
            $resultsReleased = mysqli_query($link, "SELECT * FROM gamesReleased $orderStr");
            $resultsCollected = mysqli_query($link, "SELECT * FROM gamesCollected $orderStr");

            $outputString .= "[\n\t{\n\t\t\"table\": \"gamesUnreleased\",\n\t\t\"data\": [";

            for($i = 0; $i < mysqli_num_rows($resultsUnreleased); $i++) {
                $gameID = mysqli_result($resultsUnreleased,$i,"ID");
                $gameTitle = mysqli_result($resultsUnreleased,$i,"GameName");
                $releaseDate = mysqli_result($resultsUnreleased,$i,"ReleaseDate");
                $platforms = mysqli_result($resultsUnreleased,$i,"Platforms");
                
                $outputString .= "\n\t\t\t{\n\t\t\t\t\"gameID\": \"$gameID\",\n\t\t\t\t\"gameTitle\": \"$gameTitle\",\n\t\t\t\t\"releaseDate\": \"$releaseDate\",\n\t\t\t\t\"platforms\": \"$platforms\"\n\t\t\t},";
            }

            $outputString = substr_replace($outputString ,"", -1) . "\n\t\t]\n\t},\n\t{\n\t\t\"table\": \"gamesTBA\",\n\t\t\"data\": [";
            
            for($i = 0; $i < mysqli_num_rows($resultsTBA); $i++) {
                $gameID = mysqli_result($resultsTBA,$i,"ID");
                $gameTitle = mysqli_result($resultsTBA,$i,"GameName");
                $platforms = mysqli_result($resultsTBA,$i,"Platforms");
                
                $outputString .= "\n\t\t\t{\n\t\t\t\t\"gameID\": \"$gameID\",\n\t\t\t\t\"gameTitle\": \"$gameTitle\",\n\t\t\t\t\"platforms\": \"$platforms\"\n\t\t\t},";
            }

            $outputString = substr_replace($outputString ,"", -1) . "\n\t\t]\n\t},\n\t{\n\t\t\"table\": \"gamesReleased\",\n\t\t\"data\": [";

            for($i = 0; $i < mysqli_num_rows($resultsReleased); $i++) {
                $gameID = mysqli_result($resultsReleased,$i,"ID");
                $gameTitle = mysqli_result($resultsReleased,$i,"GameName");
                $platforms = mysqli_result($resultsReleased,$i,"Platforms");
                
                $outputString .= "\n\t\t\t{\n\t\t\t\t\"gameID\": \"$gameID\",\n\t\t\t\t\"gameTitle\": \"$gameTitle\",\n\t\t\t\t\"platforms\": \"$platforms\"\n\t\t\t},";
            }

            $outputString = substr_replace($outputString ,"", -1) . "\n\t\t]\n\t},\n\t{\n\t\t\"table\": \"gamesCollected\",\n\t\t\"data\": [";

            for($i = 0; $i < mysqli_num_rows($resultsCollected); $i++) {
                $gameID = mysqli_result($resultsCollected,$i,"ID");
                $gameTitle = mysqli_result($resultsCollected,$i,"GameName");
                $platforms = mysqli_result($resultsCollected,$i,"Platforms");
                
                $outputString .= "\n\t\t\t{\n\t\t\t\t\"gameID\": \"$gameID\",\n\t\t\t\t\"gameTitle\": \"$gameTitle\",\n\t\t\t\t\"platforms\": \"$platforms\"\n\t\t\t},";
            }

            $outputString = substr_replace($outputString ,"", -1) . "\n\t\t]\n\t}\n]";

            header('Content-Disposition: attachment; filename="GameHorizon-All-'.date('YmdHis').'.json"');
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . strlen($outputString));
            header('Connection: close');

            echo $outputString;
        } else {
            $results = mysqli_query($link, "SELECT * FROM $table $orderStr");
            
            $outputString .= "{\n\t\"table\": \"$table\",\n\t\"data\": [";
            
            for($i = 0; $i < mysqli_num_rows($results); $i++) {
                $gameID = mysqli_result($results,$i,"ID");
                $gameTitle = mysqli_result($results,$i,"GameName");
                $platforms = mysqli_result($results,$i,"Platforms");
                
                if($table == "gamesUnreleased") {
                    $releaseDate = mysqli_result($results,$i,"ReleaseDate");
                    $outputString .= "\n\t\t{\n\t\t\t\"gameID\": \"$gameID\",\n\t\t\t\"gameTitle\": \"$gameTitle\",\n\t\t\t\"releaseDate\": \"$releaseDate\",\n\t\t\t\"platforms\": \"$platforms\"\n\t\t},";
                } else {
                    $outputString .= "\n\t\t{\n\t\t\t\"gameID\": \"$gameID\",\n\t\t\t\"gameTitle\": \"$gameTitle\",\n\t\t\t\"platforms\": \"$platforms\"\n\t\t},";
                } 
            }

            $outputString = substr_replace($outputString ,"", -1) . "\n\t]\n}\n";

            header('Content-Disposition: attachment; filename="GameHorizon-'.$table.'-'.date('YmdHis').'.json"');
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . strlen($outputString));
            header('Connection: close');

            echo $outputString;
        }
    } else {
        die("Malformed request.");
    }
}
?>