<?php

/*

    End-to-end configuration, sanity testing, and installation file for Saguaro.

*/

$lockout = "._lockout";

if (file_exists($lockout)) {
    exit(); //If the lockout file exists, terminate the script.
} else {
    $neotest = new NeoTest;
    $neotest->init();
}

class NeoTest {
    private $mysql;
    private $data = [];
    public $success = "<span class='success'>SUCCESS</span>";
    public $fail = "<span class='fail'>FAIL</span>";

    function init() {
        session_start();
        if (!isset($_SESSION['step'])) { $_SESSION['step'] = 'welcome'; $_SESSION['data'] = []; }
        $step_order = ['welcome', 'checkRequirements', 'locateCore', 'checkSQL', 'setupSQL', 'setupAdmin', 'setupDirs', 'setupBoard', 'finalize', 'write'];
        $target = $_SESSION['step'];

        $step = ($_POST['step']) ? $_POST['step'] : $_GET['step'];

        switch ($step) {
            case "next":
                $target = $step_order[min(array_search($target, $step_order) + 1, count($step_order))];
                break;
            case "prev":
                $target = $step_order[max(array_search($target, $step_order) - 1, 0)];
                break;
            case "revert": $target = "revert"; break;
            case "lock": $target = "createlockfile"; break;
            case "refresh": break;
            case "restart":
            default:
                unset($_SESSION);
                break;
        }
        $_SESSION['step'] = $target;

        $out = "";

        switch ($target) {
            case 'checkRequirements': $out = $this->checkRequirements(); break;
            case 'locateCore': $out = $this->locateCore(); break;
            case 'checkSQL': $out = $this->checkSQL(); break;
            case 'setupSQL': $out = $this->setupSQL(); break;
            case 'setupAdmin': $out = $this->setupAdmin(); break;
            case 'setupDirs': $out = $this->setupDirs(); break;
            case 'setupBoard': $out = $this->setupBoard(); break;
            case 'finalize': $out = $this->finalize(); break;
            case 'revert': $out = $this->revert(); break;
            case 'createlockfile': $out = $this->createlockfile(); break;
            case 'write': $out = $this->write(); break;
            case 'welcome':
            default:
                $out = $this->welcome(); break;
        }

        echo $out;
    }

    function initSQL($server, $user, $pass) {
        $server = (!$server && $_SESSION['data']['sql']['server']) ? $_SESSION['data']['sql']['server'] : $server;
        $user = (!$user && $_SESSION['data']['sql']['user']) ? $_SESSION['data']['sql']['user'] : $user;
        $pass = (!$pass && $_SESSION['data']['sql']['pass']) ? $_SESSION['data']['sql']['pass'] : $pass;

        $this->mysql = new mysqli($server, $user, $pass);
    }

    private function generatePage($body,$hideNext = false) {
        $css = "<style type='text/css'>body { background-color:#EEF2FF; text-align:center; font-family:sans-serif; } .box { background-color:rgba(0,0,0,.1); padding:10px; border-radius:10px; margin: 2% auto; width:60%; display: block; } table td input { width: 100%; }</style>";
        $header = "<div class='box' style='text-align:left'><a href='?step=restart'>&lt; Restart</a><div style='float:right'><a href='?step=prev'>&lt; Previous</a>" . (($hideNext == true) ? "" : " | <a href='?step=next'>Next &gt;</a>") . "</div></div>";
        $html = "<html><head><title>Saguaro Configuration, Testing, and Installation Utility</title>$css</head><body>$header$body</body></html>";

        return $html;
    }

    function generateResults($tests) {
        $out = "<table style='width:100%;text-align:center;'><tr><th>Test</th><th>Result</th><th>Additional information</th></tr>";
        foreach ($tests as $key => $results) {
            $color = ($results['valid']) ? "green" : "red";
            $msg = ($results['valid']) ? "PASS" : "FAIL";
            $debug = "<span style='color:$color;font-weight:bold;'>" . $results['current'] . "</span>" . (($results['valid']) ? " >= " : " < ") . $results['min'] . " (required)";

            $out .= "<tr><td><strong>$key</strong></td><td><span style='color:$color;font-weight:bold;'>$msg</span></td><td>$debug</td></tr>";
        }
        $out .= "</table>";

        return $out;
    }

    function checkRequirements() {
        $min_php = '4.2.0';
        $min_gd = '2.0.0';

        //Check PHP version.
        $version_tests["PHP version"] = [
            "current" => phpversion(),
            "valid" => version_compare(phpversion(), $min_php, '>='),
            "min" => $min_php
        ];

        //Check GD version.
        $gd_ver = (function_exists('gd_info')) ? preg_match("/(?:\d\.?)+/", gd_info()["GD Version"], $match) : 0;
        if (function_exists('gd_info')) $gd_ver = $match[0];
        $version_tests["GD version"] = [
            "current" => (function_exists("gd_info")) ? $gd_ver : 0,
            "valid" => (function_exists("gd_info")) ? version_compare($gd_ver, $min_gd, '>=') : 0,
            "min" => $min_gd
        ];

        //Output results of tests.
        $out = "<div class='box'>Below are the initial tests of server compatability.<br>If any tests fail, you may still proceed but success is not guaranteed.<br>Consider meeting these conditions. You may need to contact your server provider.<br><br><a href='?step=refresh'>RE-RUN TESTS</a><br><br>";

        $out .= $this->generateResults($version_tests) . "</div>";

        return $this->generatePage($out);
    }

    function welcome() {
        $text = "<div id='content' class='box'>Welcome to the Saguaro Configuration, Testing, and Installation Utility.<br><br>This will walk you through the steps needed to get Saguaro up and running with little to no issues.<br>Above are the main navigation options, \"Restart\" to return here and \"Next\" to proceed to the next step.<br><br><hr><a href='?step=lock'>Create the lockfile</a> to seal this off. <strong>First time installs do click this!</strong><br>Backups cannot be reverted here, they must be done manually.</div>";
        return $this->generatePage($text);
    }

    function locateCore() {
        //Attempt to detect the _core directory.
        $CORE_DIR = ($_GET['core']) ? $_GET['core'] : (($_SESSION['data']['core_dir']) ? $_SESSION['data']['core_dir'] : '_core/');
        //$CORE_DIR = rtrim($CORE_DIR, '/') . '/'; //https://stackoverflow.com/a/9339669
        $okay = true;
        $text = "";
        if (!file_exists($CORE_DIR) || !is_dir($CORE_DIR)) {
            //Default and expected directory does not exist, so ask for it.
            $text = "The specified core directory ($CORE_DIR) does not exist or could not be found.<br><br>This may happen if you're performing a multi-board setup.<br>Specify the location below relative to this file and imgboard.php.";
        } else {
            $okay = false;
            $text = "The specified core directory ($CORE_DIR) was found successfully.<br><br>If you would like to change it, usually for multi-board setups, do so below.<br>If this folder is not the core directory, you may experience problems during setup.";
            $_SESSION['data']['core_dir'] = $CORE_DIR;
        }

        $text .= "<br>Please ensure the path ends with a foward slash, /.<br><br><form action='' method='get'><input type='hidden' name='step' value='refresh'><input type='text' name='core' value='$CORE_DIR'><input type='submit' value='Re-check'></form>";

        return $this->generatePage("<div class='box'>$text</div>",$okay);
    }

    function checkSQL() {
        //$path = $_SESSION['data']['core_dir'] . "/mysql/mysql.php";
        $text = "";
        $hideNext = true;
        $min_mysql = '4.0.0';

        //if (0 == 1 && !file_exists($path)) { $text = "Unable to find the SQL class in the core directory given. ($path)<br>Ensure it exists or the core directory is set properly.<br><br><a href='?step=refresh'>Re-check</a>";} else {
        if ($_POST['server'] && $_POST['user'] && $_POST['pass']) {
            //Do this purely via MySQLi, like original test, for now until the classes are updated.
            //require_once($path); $mysql = new SaguaroMySQL; $mysql->connect($_GET['server'],$_GET['user'],$_GET['pass']);
            $this->initSQL($_POST['server'], $_POST['user'], $_POST['pass']);

            if (mysqli_connect_errno()) {
                $text .= "Failed to connect to the SQL server, ensure the information is correct.<br><strong>Error code:</strong> " . mysqli_connect_errno() . "<br><br>";
                mysqli_close($this->mysql);
            } else {
                $_SESSION['data']['sql'] = [
                    'server' => $_POST['server'],
                    'user' => $_POST['user'],
                    'pass' => $_POST['pass']
                ];

                //Check version.
                $mver = mysqli_get_server_info($this->mysql);
                $tests["MySQL version"] = [
                    "current" => $mver,
                    "valid" => version_compare($mver, $min_mysql, '>='),
                    "min" => $min_mysql
                ];
                $hideNext = !$tests['MySQL version']['valid'];
                $text .= "Connection successful. Below is the test on the server's version.<br>Passing this test is required. Contact your server provider for help.<br><br>" . $this->generateResults($tests);
            }
            $text .= "<hr>";
        }

        $text .= "Configure the connection to the SQL server. (uses MySQLi)<br><small>Data entered is sent as plaintext over the current connection, which may not be encrypted.<br>If already configured, this can be skipped but then database/table/admin configuring is too.</small><br><br>";
        $text .= "<form action='' method='post'>
                <input type='hidden' name='step' value='refresh'><table style='display:inline;'>
                <tr><td>Server</td><td><input type='text' name='server' value='".(isset($_POST['server']) ? $_POST['server'] : "127.0.0.1")."'></td></tr>
                <tr><td>Username</td><td><input type='text' name='user' value='".(isset($_POST['user']) ? $_POST['user'] : "")."'></td></tr>
                <tr><td>Password</td><td><input type='password' name='pass' value='".(isset($_POST['pass']) ? $_POST['pass'] : "")."'></td></tr>
                <tr><td></td><td><input type='submit' value='Test connection'></td></tr>
                </table></form>";

        return $this->generatePage("<div class='box'>$text</div>",false);
    }

    function setupSQL() {
        $hideNext = true;
        $config = true;

        if ($_POST['database'] && $_POST['prefix']) {
            $this->initSQL(); $mysqli = $this->mysql;

            if (mysqli_connect_errno()) {
                unset($_SESSION['data']['sql']);
                $text .= "Failed to connect to the SQL server, return to the previous step and ensure it's correct.<br><strong>Error code:</strong> " . mysqli_connect_errno() . "<br><br>";
                mysqli_close($mysqli);
            } else {
                $db = $_POST['database'];
                $prefix = $_POST['prefix'];
                $_SESSION['data']['sql']['database'] = $db;
                $_SESSION['data']['sql']['prefix'] = $prefix;
                $config = false;
                $text .= "These queries were executed as <strong>" .$_SESSION['data']['sql']['user'] . "</strong>.<br>";

                $has_db = mysqli_select_db($mysqli, $db);
                if (!$has_db) { //Attempt to create database.
                    $text .= "<strong>$db</strong> database does not exist, creating... ";
                    $status = mysqli_query($mysqli, "CREATE DATABASE $db");
                    $text .= ($status) ? $success : $fail;

                    if (!$status) {
                        $text .= "Unable to create <strong>$db</strong> database (error: " . mysqli_errno($mysqli) . "), cannot initialize.";
                        $config = true;
                    } else {
                        $has_db = true;
                    }
                } else {
                    $text .= "<strong>$db</strong> database already exists.<br>";
                }

                if ($has_db) {
                    //Attempt to create tables.
                    $tables = [
                        $prefix . "log" => "primary key(no), no int not null auto_increment, now text, name text, email text, sub text, com text, host text, pwd text, media text, time int, sticky int, permasage int, locked int, last int, modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, resto int, board text",
                        $prefix . "banlog" => "board VARCHAR(20), global INT(1), name VARCHAR(200), host VARCHAR(50), com VARCHAR(3000), reason VARCHAR(1000), length INT(25), admin VARCHAR(100), placed INT(25) NOT NULL, PRIMARY KEY (board, placed)",
                        $prefix . "modslog" => "user VARCHAR(25), password VARCHAR(250), public_salt VARCHAR(256), allowed VARCHAR(250), denied VARCHAR(250), PRIMARY KEY (user), UNIQUE KEY (user)",
                        $prefix . "dellog" => "admin VARCHAR(250), postno VARCHAR(20) PRIMARY KEY, action VARCHAR(25), board VARCHAR(250), name VARCHAR(50), sub VARCHAR(50), com VARCHAR(" . S_POSTLENGTH . ")", //Why does S_POSTLENGTH start with S_?
                        $prefix . "bannotes" => "board VARCHAR(25), host VARCHAR(250), type VARCHAR(50), com VARCHAR(3100), reason VARCHAR(2000), admin VARCHAR(250), PRIMARY KEY (host, com), UNIQUE KEY (com)",
                        $prefix . "media" => "primary key(no), no int not null auto_increment, parent int, resto int, filename text, localname text, localthumbname text, filesize int, extension text, width int, height int, thumb_width int, thumb_height int, hash text, board text",
                        "reports" => "no VARCHAR(25), board  VARCHAR(250), type VARCHAR(250), ip VARCHAR(250), reported TIMESTAMP, PRIMARY KEY(no, ip)",
                        "loginattempts" => "userattempt VARCHAR(25) PRIMARY KEY, passattempt VARCHAR(250), board VARCHAR(250), ip VARCHAR(250), attemptno VARCHAR(50)",
                        "rebuildqueue" => "board char(4) NOT NULL, no int(11) NOT NULL, ownedby int(11) NOT NULL default '0', ts timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, PRIMARY KEY (board,no,ownedby)"
                    ];

                    foreach ($tables as $table => $query) {
                        $sql = "SHOW TABLES LIKE '$table'";
                        $q = mysqli_query($mysqli, $sql);
                        $exists = (mysqli_num_rows($q) > 0) ? true : false;

                        if ($exists) {
                            $text .= "<strong>$table</strong> table already exists. (not updated)<br>";
                        } else {
                            $text .= "<strong>$table</strong> table does not exist, creating... ";
                            $status = mysqli_query($mysqli, "CREATE TABLE $table ($query)");
                            $text .= (($status) ? $this->success : $this->fail . "(error " . mysqli_errno($mysqli) . ")") . "<br>";
                        }

                        mysqli_free_result($q);
                    }

                    $hideNext = false;
                }
            }
            $text .= "<hr>";
        }

        if ($config) {
            $text .= "Configure the database and table prefix for this board.<br><small>It's recommended the tables prefix is unique for multi-board installs in the same database.</small><br><br>";
            if ($_SESSION['data']['sql']) {
                $text .= "<form action='' method='post'>
                        <input type='hidden' name='step' value='refresh'><table style='display:inline;'>
                        <tr><td>Database</td><td><input type='text' name='database' value='".(isset($_POST['database']) ? $_POST['database'] : "saguaro")."'></td></tr>
                        <tr><td>Tables Prefix</td><td><input type='text' name='prefix' value='".(isset($_POST['prefix']) ? $_POST['prefix'] : "")."'></td></tr>
                        <tr><td></td><td><input type='submit' value='Initialize database'></td></tr>
                        </table></form>";
            } else {
                $hideNext = false;
                $text .= 'The SQL connection was not configured, so this step is skipped.';
            }
        }

        return $this->generatePage("<div class='box'>$text</div>",$hideNext);
    }

    function setupAdmin() {
        $this->initSQL(); $mysqli = $this->mysql;
        mysqli_select_db($mysqli, $_SESSION['data']['sql']['database']);
        $path = $_SESSION['data']['core_dir'] . "/crypt/legacy.php";
        $hideNext = false;
        $config = true;

        if (!file_exists($path)) {
            $text = "Unable to find the Legacy Crypt class in the core directory given. ($path)<br>Ensure it exists or the core directory is set properly.<br><br>This is <strong>required</strong> for encrypting the administrator password in the database.<br><br><a href='?step=refresh'>Re-check</a>";
        } else {
            if ($_POST['username'] && $_POST['pass'] && $_POST['pass2']) {
                $config = false;
                if ($_POST['pass'] !== $_POST['pass2']) {
                    $text .= "Passwords do not match.";
                    $config = true;
                } else {
                    require_once($path);
                    $crypt = new SaguaroCryptLegacy;
                    $account = ['name' => $_POST['username'], 'pass' => $_POST['pass'], 'priv' => 'janitor_board,moderator,admin,manager', 'deny' => 'none'];
                    $password = $crypt->generate_hash($account['pass']); //Generate password hash and public salt with SaguaroCrypt.

                    $text .= "Account <strong>" . $account['name'] . "</strong> created... (<span class='info' title='Privileges'>" . $account['priv'] . "</span> / <span class='info' title='Denied'>" . $account['deny'] . "</span>) ";

                    $status = mysqli_query($mysqli, "INSERT INTO " . ($_SESSION['data']['sql']['prefix'] . "modslog") . " (user, password, public_salt, allowed, denied) VALUES ('{$account['name']}', '{$password['hash']}', '{$password['public_salt']}', '{$account['priv']}', '{$account['deny']}')");
                    $unfail = (mysqli_errno($mysqli) == 1062) ? "<span class='fail'>ALREADY EXISTS</span><br>" : "Fail";
                    $text .= ($status) ? "Okay" : "(" . mysqli_errno($mysqli) . ") " . $unfail;

                    if (!$status) {
                        $config = true;
                    } else {
                        $hideNext = false;
                    }
                }

                $text .= "<hr>";
            }

            if ($config) {
                $text .= "Create a new administrator account.<br>If one already exists, this step can be skipped.<br><small>Data entered is sent as plaintext over the current connection, which may not be encrypted.</small><br><br>";
                if ($_SESSION['data']['sql']) {
                    $text .= "<form action='' method='post'>
                            <input type='hidden' name='step' value='refresh'><table style='display:inline;'>
                            <tr><td>Username</td><td><input type='text' name='username' value='".(isset($_POST['username']) ? $_POST['username'] : "")."'></td></tr>
                            <tr><td>Password</td><td><input type='password' name='pass' value='".(isset($_POST['pass']) ? $_POST['pass'] : "")."'></td></tr>
                            <tr><td>Confirm</td><td><input type='password' name='pass2' value='".(isset($_POST['pass2']) ? $_POST['pass2'] : "")."'></td></tr>
                            <tr><td></td><td><input type='submit' value='Initialize database'></td></tr>
                            </table></form>";
                } else {
                    $hideNext = false;
                    $text .= 'The SQL connection was not configured, so this step is skipped.';
                }
            }
        }

        return $this->generatePage("<div class='box'>$text</div>",$hideNext);
    }

    function setupDirs() {
        $hideNext = false;
        $config = true;

        if ($_POST['thread'] && $_POST['source'] && $_POST['thumb']) {
            $folders = [$_POST['thread'], $_POST['source'] ,$_POST['thumb']];

            foreach ($folders as $dir) {
                $fdir = "<strong>$dir</strong>";
                $bad = 0;

                if (!is_dir($dir)) {
                    $text .= "$fdir does not exist, creating... ";
                    $status = mkdir($dir);
                    $text .= ($status) ? $this->success : $this->fail;
                } else {
                    $text .= "$fdir already exists.<br>";
                }

                $perms = substr(sprintf('%o', fileperms($dir)), -4);

                if ($perms !== "0777") {
                    $text .= "Changing $fdir permissions from $perms to 0777... ";
                    $status = chmod($dir, 0777);
                    $text .= ($status) ? $this->success : $this->fail;
                } else {
                    $text .= "$fdir has the right permissions (0777).<br>";
                }

                clearstatcache();
            }

            $text .= "<hr>";
        }

        if ($config) {
            $text .= "Configure the directories where threads, uploads, and thumbnails are stored.<br>If already initialized, this step can be skipped.<br><small>Ensure the folder names end with a forward slash, /.<br>If initialization fails, the board folder might need its permissions set to 0777.</small><br><br>";
            $text .= "<form action='' method='post'>
                    <input type='hidden' name='step' value='refresh'><table style='display:inline;'>
                    <tr><td>Threads</td><td><input type='text' name='thread' value='".(isset($_POST['thread']) ? $_POST['thread'] : "res/")."'></td></tr>
                    <tr><td>Uploads</td><td><input type='text' name='source' value='".(isset($_POST['source']) ? $_POST['source'] : "src/")."'></td></tr>
                    <tr><td>Thumbs</td><td><input type='text' name='thumb' value='".(isset($_POST['thumb']) ? $_POST['thumb'] : "thumb/")."'></td></tr>
                    <tr><td></td><td><input type='submit' value='Initialize folders'></td></tr>
                    </table></form>";
        }

        return $this->generatePage("<div class='box'>$text</div>",$hideNext);
    }

    function setupBoard() {
        $hideNext = true;
        $config = true;
        $text;

        if ($_POST['name'] && $_POST['subtitle'] && $_POST['home'] && $_POST['desc']) {
            $_SESSION['data']['board'] = [
                'name' => $_POST['name'],
                'subtitle' => $_POST['subtitle'],
                'home' => $_POST['home'],
            ];

            $hideNext = false;

            $text .= "If you are satisfied with these settings, proceed.<hr>";
        }

        if ($config) {
            $text .= "Configure the board itself.<br><small>If this is not your first time, this step can be skipped otherwise it is required.</small><br><br>";
            $text .= "<form action='' method='post'>
                    <input type='hidden' name='step' value='refresh'><table style='width:100%'>
                    <col width='20%'><col width='80%'>
                    <tr><td>Board Name</td><td><input type='text' name='name' value='".(isset($_POST['name']) ? $_POST['name'] : "My First Saguaro")."'></td></tr>
                    <tr><td>Board Subtitle</td><td><input type='text' name='subtitle' value='".(isset($_POST['subtitle']) ? $_POST['subtitle'] : "Skills honed in the mountains.")."'></td></tr>
                    <tr><td>Board Description</td><td><input type='text' name='desc' value='".(isset($_POST['desc']) ? $_POST['desc'] : "It's a show about nothing.")."'></td></tr>
                    <tr><td>Homepage URL</td><td><input type='text' name='home' value='".(isset($_POST['home']) ? $_POST['home'] : "127.0.0.1")."'></td></tr>
                    <tr><td></td><td><small>Example: http://<span style='color:#25ab25'>mysite.com</span>/myboard</small></td></tr>

                    <tr><td></td><td><input type='submit' value='Set'></td></tr>
                    </table></form>";
        }

        return $this->generatePage("<div class='box'>$text</div>",false);
    }

    private function finalize() {
        $data = $_SESSION['data'];
        $text = "All the following information will be written to your config.php file, please review it before continuing.<br><small>A backup of the config will be created before writing, with an option to automatically revert.</small><br><br>";

        var_dump($_SESSION);
        $text .= "<table style='width:100%'>
        <col width='20%'><col width='80%'>
        <tr><td>Core Directory</td><td>".$data['core_dir']."</td></tr>";
        if ($data['sql']) {
            $text .= "<tr><td>SQL Server</td><td>".$data['sql']['server']."</td></tr>
            <tr><td>SQL Username</td><td>".$data['sql']['user']."</td></tr>
            <tr><td>SQL Password</td><td><span style='font-size:x-small'>(Withheld)</span></td></tr>
            <tr><td>SQL Database</td><td>".$data['sql']['database']."</td></tr>
            <tr><td>SQL Tables Prefix</td><td>".$data['sql']['prefix']." <span style='font-size:x-small'>(appropriate table full names will also be written)</span></td></tr>";
        }
        if ($data['board']) {
            $text .= "<tr><td>Board Name</td><td>".$data['board']['name']."</td></tr>
            <tr><td>Board Subtitle</td><td>".$data['board']['subtitle']."</td></tr>
            <tr><td>Board Description</td><td>".$data['board']['desc']."</td></tr>
            <tr><td>Homepage URL</td><td>".$data['board']['home']."</td></tr>";
        }
        $text .= "</table>";

        return $this->generatePage("<div class='box'>$text</div>",false);
    }

    private function write() {
        $config = 'config.php';

        $backup = 'config_backup'.time().'.php'; //config_backupTIMESTAMP.php
        copy($config, $backup); //Backup config.php
        chmod($backup, 0000); //Disable all permissions for the backup

        //Start writing.
        $data = $_SESSION['data'];
        $pairs = [ //Generate pairs to definitions.
            'CORE_DIR'      => $data['core_dir'],
            'TITLE'         => $data['board']['name'],
            'S_HEADSUB'     => $data['board']['subtitle'],
            'S_DESCR'       => $data['board']['desc'],
            'SITE_ROOT'     => $data['board']['home'],
        ];
        
        if ($data['sql']) {
            array_push($pairs,
                ['SQLUSER'     => $data['sql']['user'],
                 'SQLPASS'     => $data['sql']['pass'],
                 'SQLHOST'     => $data['sql']['server'],
                 'SQLDB'       => $data['sql']['database'],
                 'PREFIX'      => $data['sql']['prefix'],
                 'SQLLOG'      => $data['sql']['prefix'] . "log",
                 'SQLBANLOG'   => $data['sql']['prefix'] . "banlog",
                 'SQLMODSLOG'  => $data['sql']['prefix'] . "modslog",
                 'SQLDELLOG'   => $data['sql']['prefix'] . "dellog",
                 'SQLBANNOTES' => $data['sql']['prefix'] . "bannotes",
                 'SQLMEDIA'    => $data['sql']['prefix'] . "media"]
            );
        }
        
        var_dump($pairs);

        $file_contents = file_get_contents($config); //Read in config
        foreach ($pairs as $define => $new) { //Write out the pairs
            //This should eventually be sanitized more than it is now.
            if (!is_null($new)) {
                $file_contents= preg_replace("/define\('".$define."', .*?\)/", "define('".$define."', '".addslashes($new)."')", $file_contents);
            }
        }

        //file_put_contents($config,$file_contents); //Write out.
        $text .= "Complete.<br>Backup saved to <strong>$backup</strong><br><br><hr><br>
        <strong>Step 1:</strong> Ensure <a href='config.php' target='_blank'>config.php</a> was written properly and does not show in the browser.<br>
        <small>If anything appears in your browser, or it's not a blank screen, there is a problem and security could be compromised.</small><br><br>
        <strong>Step 2:</strong> Check <a href='imgboard.php' target='_blank'>imgboard.php</a> for the reflected changes, if any. Might need to force a regen.<br>
        <small>If this is your first time through, the board will initiliaze when accessed. This is normal.<br>If you do not arrive at the board index your set Homepage URL (currently: ".$data['board']['home'].") is wrong.</small><br><br>
        <strong>Step 3:</strong> If all is good, complete the setup by creating the lockfile below.<br><small>If something is wrong revert to the backup created.</small><br><br><hr><br><a href='?step=revert&to=$backup'>Revert to backup</a> | <a href='?step=lock'>Finish and create lockfile</a>";
        return $this->generatePage("<div class='box'>$text</div>",true);
    }
    
    private function revert() {
        $from = $_GET['to'];
        if (file_exists($from)) {
            rename($from, "config.php"); //Revert to backup.
            chmod("config.php", 0777); //Turn on permissions.
            $text = "The config has been reverted to <strong>$from</strong>. The backup has been deleted.<br><br>If you're done here and haven't already, <a href='?step=lock'>create the lockfile</a>.";
        } else {
            $text = "The specified backup file to revert to does not exist.<br><br>If you're done here and haven't already, <a href='?step=lock'>create the lockfile</a>.";
        }

        return $this->generatePage("<div class='box'>$text</div>",true);
    }
    
    private function createlockfile() {
        global $lockout;
        touch($lockout);
        $text = "The lockfile (".$lockout.") has been created in the board's directory and this file will not operate again until it is deleted!";
        return $this->generatePage("<div class='box'>$text</div>",true);
    }
}

?>