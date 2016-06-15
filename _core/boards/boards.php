<?php

class Board {
    public function build() {
        global $mysql;

        $email = (isset($_POST['email']) && filter_var($email, FILTER_VALIDATE_EMAIL)) ? $_POST['email'] : 0;
        
        if (!preg_match('/^[a-z0-9]{1,30}$/', $_POST['uri'])) error('Invalid URI');
        if (strlen($_POST['title']) > 40) error('Invalid title');
        if (strlen($_POST['subtitle']) > 200) error('Invalid subtitle');
        //if (preg_match('/^[a-zA-Z0-9._]{1,30}$/', $_POST['user'])) error('Invalid user');
       /* require_once(CORE_DIR . '/general/captcha.php');
        $captcha = new Captcha;
        if ($captcha->isValid() !== true) error("Captcha invalid.");*/

        $uri = $mysql->escape_string($_POST['uri']);
        $title = $mysql->escape_string($_POST['title']);
        $subtitle = $mysql->escape_string($_POST['subtitle']);
        $user = $mysql->escape_string($_POST['username']);
        $pwd = $mysql->escape_string($_POST['password']);
        $email = $mysql->escape_string($email);
        
        
        if (is_dir($uri)) error('Board already exists!');

        /*foreach ($config['banned_boards'] as $i => $w) {
            if ($w[0] !== '/') {
                if (strpos($uri,$w) !== false)
                    error(_("Cannot create board with banned word $w"));
            } else {
                if (preg_match($w,$uri))
                    error(_("Cannot create board matching banned pattern $w"));
            }
        }*/

        if ($mysql->result("SELECT COUNT(username) FROM " . SQLMODSLOG . " WHERE username='" . $user . "'") > 0){
            error("That user already exists!");
        }

        require_once(CORE_DIR . "/crypt/legacy.php");
        $crypt = new SaguaroCryptLegacy;
        $pwd = $crypt->generate_hash($pwd);
     

        if (!$mysql->query("INSERT INTO " . SQLMODSLOG . " (username, password, salt, type, boards, email) VALUES ('$user', '" . $pwd['hash'] . "', '" .  $pwd['public_salt'] . "', '99', '$uri', '$email')"))
                error("Databse error.");
                    
        if (!$mysql->query('INSERT INTO boards (`uri`, `title`, `subtitle`) VALUES ("' . $uri . '", "' . $title . '", "' . $subtitle . '")'))
            error("Databse error.");

        // Build the board
        $end = $this->create($uri, $title, $subtitle);

        return $end;
    }
    
    public function form() {
        $temp = '<center><div class="container" ><div class="header">Proof of concept!<!--Click <a href="claim.php">here</a> for a list of abandoned boards!---></div><form method="POST" style="margin-top:25px;">
        <table class="modlog" style="width:auto">
        <tbody>
        <tr><th class="postblock">URI</th><td>/<input name="uri" type="text" size="5">/ <span class="unimportant">(Must be all lowercase or numbers and &lt; 30 chars)</span></td></tr>
        <tr><th class="postblock">Subtitle</th><td><input name="subtitle" type="text" maxlength="200" placeholder="Must be &lt; 200 chars"></td></tr>
        <tr><th class="postblock">Title</th><td><input name="title" type="text" maxlength="40" placeholder="Must be &lt; 40 chars"></td></tr>';
        
        if (isset($_COOKIE['saguaro_auser']) && isset($_COOKIE['saguaro_apass'])) { //We'll check this later.
            $temp .= '<tr><th class="postblock">Username</th><td><input type="text" value="' . $_COOKIE['saguaro_auser'] . '" readonly="readonly"></td></tr>';
            $temp .= '<tr><th class="postblock">Password</th><td><!---Im not really putting your password here, im not a moron---><input type="password" value="password1234" readonly="readonly"></td></tr>';
        } else {
            $temp .= '<tr><th class="postblock">Username</th><td><input name="username" type="text"><span class="unimportant">(Only alphanumeric, periods and underscores)</span></td></tr>';
            $temp .= '<tr><th class="postblock">Password</th><td><input name="password" type="text" value="' . substr(bin2hex(openssl_random_pseudo_bytes(16)), 0, 12) . '" readonly> <span class="unimportant">(write this down)</span></td></tr>';
        }
        $temp .= '<tr><th class="postblock">Email</th><td><input name="email" type="text" placeholder="Optional, for board recovery"></td></tr>
        <tr><th class="postblock">Captcha</th><td><img src="' . CORE_DIR_PUBLIC . '/general/captcha.php" /><br><input class="captcha_text" name="num" size="9" style="text-align:center;" maxlength="5" autocomplete="off" type="text"><br></td></tr>
        </tbody></table><ul style="padding:0;text-align:center;list-style:none"><li><input type="submit" value="Create board"></li></ul></form></center></div>';
        
        return $temp;
    }
    
    private function create($dir, $title, $stitle) {
        global $mysql;
        
        mkdir($dir, 0755);
        
        $dirList= [IMG_DIR, THUMB_DIR, API_DIR];
        mkdir($dir . "/" . RES_DIR);
        foreach($dirList as $directory) {
            $directory = str_replace("../", "", $directory); 
            mkdir($directory . $dir, 0755);
        }
		mkdir(API_DIR . RES_DIR, 0755);
		if (!$this->configGen($dir, $title, $stitle)) {
            $this->rollBack($dir);
            error("Error creating board!");
        }
		
		/*$mysql->query("CREATE TABLE IF NOT EXISTS `posts_" . $dir . "` (
			  `no` int(11) NOT NULL AUTO_INCREMENT,
			  `now` text NOT NULL,
			  `name` text NOT NULL,
			  `email` text NOT NULL,
			  `sub` text NOT NULL,
			  `com` text NOT NULL,
			  `host` text NOT NULL,
			  `pwd` text NOT NULL,
			  `ext` text NOT NULL,
			  `w` int(11) DEFAULT NULL,
			  `h` int(11) DEFAULT NULL,
			  `tn_w` int(11) DEFAULT NULL,
			  `tn_h` int(11) DEFAULT NULL,
			  `tim` bigint(11) NOT NULL,
			  `time` int(11) NOT NULL,
			  `md5` text,
			  `fsize` int(11) DEFAULT NULL,
			  `fname` text,
			  `sticky` int(11) DEFAULT NULL,
			  `permasage` int(11) DEFAULT NULL,
			  `locked` int(11) DEFAULT NULL,
			  `last` int(11) NOT NULL,
			  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  `resto` int(11) NOT NULL,
			  `board` text NOT NULL,
			  PRIMARY KEY (`no`)
			)");*/

		return $dir;
		
    }
	
	private function configGen($dir, $title = 0, $stitle = 0) {
        require_once("config_template.php");
        $write = "<?php ";
        
        foreach($confBools as $key => $value)
            $write .= "define(" . $key . ", " . $value . ");"; 
        
        foreach($confStrings as $key => $value)
            $write .= 'define(' . $key . ', "' . $value . '");'; 
        
        $write .= " ?>";

        $path = $dir . "/config.php";
        $file =fopen($path, "x");
        if (!$file || fwrite($file, $write) == false)
            return false;	

        if (copy("proto/imgboard.php", $dir . "/imgboard.php") !== true)
            return false;

        return true;

        /*foreach($_POST as $value) {
            if (!in_array($value, $whitelist))
            error("Error updating config!"); //wait a minute you dummies
        }*/
    }
    
    private function rollBack($dir) { 
        global $mysql;
        //Roll back changes from an erronuous board creation. Ideally, this shouldn't happen to begin with.
        @$mysql->query('DELETE FROM boards WHERE  uri="' . $dir . '"');
        @rmdir($dir);
		@rmdir($dir . "/". RES_DIR);
		@rmdir($dir . "/". IMG_DIR);
		@rmdir($dir . "/". THUMB_DIR);
        //We won't delete users here though.
    }

}

?>