<?php
if($updaterEnabled == true) {
    if(isset($_GET['update'])) {
        $cmd = "";
        $output = array();
        $exitCode = 0;
    
        print("<b>Updating GameHorizon</b><br /><br />");
    
        exec("which git", $output, $exitCode);
    
        if($exitCode > 0) {
            print("
                <div>
                    Could not find <code>git</code>. Updating GameHorizon requires a UNIX-like server OS with <code>git</code> installed.
                    <br /><br /><a href='.'>Return to GameHorizon</a>
                </div>
            ");
        } else {
            $output = array();
        }
        
        if(isset($_POST['pw']) && isset($_POST['user'])) {
            $user = $_POST['user'];
            $pw = str_replace("'", "\\'", $_POST['pw']); // since we're passing the pw into a single-quote echo
    
            $cmd = "echo '$pw' | su - $user -c \"cd \$(pwd); git pull\" 2>/dev/null";
        } else {
            $cmd = "git pull 2>&1";
        }
    
        exec($cmd, $output, $exitCode);
        
        if($exitCode == 128) {
            $statOutput = array();
            exec("stat -c '%U' .git", $statOutput);
            $user = $statOutput[0];
    
            $whoamiOutput = "";
            exec("whoami", $whoamiOutput);
            $phpUser = $whoamiOutput[0];
    
            print("
                <div>
                    GameHorizon is currently running as user <b><code>$phpUser</code></b>.<br />
                    In order to update, you must provide the password for <b>$user</b>, who owns the <code>.git</code> directory.<br /><br />
                    This is because pulling the repository as a different user than the one who owns it will mess up the permissions of the directory, and is thusly disallowed by git by default.<br /><br />
            
                    <form action='./?update' method='POST'>
                        Password for <b>$user</b>: <input type='password' id='pw' name='pw'>
                        <input type='hidden' id='user' name='user' value='$user'>
                        <input type='submit' value='Submit'><br /><br />
                    </form>
    
                    <input type='button' value='Return to GameHorizon' onclick='window.location.href=\".\";'>
                </div>
            ");
        } elseif($exitCode > 0) {
            if(isset($_POST['pw']) && isset($_POST['user'])) {
                print("<div>Failed with exit code $exitCode. Wrong password? <br /><br /><input type='button' value='Try again' onclick='window.location.href=\"./?update\";'> <input type='button' value='Return to GameHorizon' onclick='window.location.href=\".\";'></div>");
            } else {
                print("<div>Failed with exit code $exitCode. Output:<pre>" . implode("<br />", $output) . "</pre><input type='button' value='Try again' onclick='window.location.href=\"./?update\";'> <input type='button' value='Return to GameHorizon' onclick='window.location.href=\".\";'></div>");
            }
        } elseif($exitCode == 0) {
            $commitHash = substr(file_get_contents('.git/refs/heads/main'),0,7);
            $output = implode($output);
            if($output == "Already up to date.") {
                print("
                    <div>Already up to date (commit <a href='https://github.com/xdpirate/gamehorizon/commit/$commitHash' target='_blank'><b><code>$commitHash</code></b></a>).<br /><br />
                        <input type='button' value='Return to GameHorizon' onclick='window.location.href=\".\";'>
                    </div>
                ");
            } else {
                print("
                    <div>
                        Success! Updated to <a href='https://github.com/xdpirate/gamehorizon/commit/$commitHash' target='_blank'><b><code>$commitHash</code></b></a>.<br /><br />
                        <a href='https://github.com/xdpirate/gamehorizon/commits/main' target='_blank'>See commit history</a><br /><br />
                        <input type='button' value='Return to GameHorizon' onclick='window.location.href=\".\";'>
                    </div>
                ");
            }
        }
    }    
}
?>
