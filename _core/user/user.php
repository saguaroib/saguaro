<?php
/*

    Handles user instance data.

    $user = new User;
    $user->init();
    $user->info(); //Returns entire $instance.
    $user->info('permissions'); //Returns specified key from $instance.

*/

class User {
    private $instance = [ //Default settings.
        'canPost' => true,
        'isBanned' => true, //Unset by checkBan(), if not banned.
        'permissions' => [
            'none' => true,
            'janitor' => false,
            'janitor_board' => false,
            'moderator' => false,
            'manager' => false,
            'admin' => false
        ],
        'special' => [
            'reportflood' => false,
            'floodbypass' => false
        ]
    ];
    private $row;

    function init($user, $pass) {
        $this->checkBan(); //Just get this out of the way. Admins can be banned and still post. WIP.

        if ($user && $pass)
            $this->pullRow($user, $pass);
            $this->checkPermissions(); //Only check (therefore modify) permissions if we have a validated user.
    }

    function info($topkey = null) {
        if ($topkey)
           return $this->instance[$topkey];
        else
            return $this->instance;
    }

    private function pullRow($user, $pass) {
        global $mysql; //Use the SaguaroQL class.
        $this->row = $mysql->fetch_assoc("SELECT allowed,denied FROM " . SQLMODSLOG . " WHERE user='$user' and password='$pass'");
    }

    private function checkBan() {
        global $mysql; //Use the SaguaroQL class.

        $ip = $_SERVER['REMOTE_ADDR'];
        $yes = $mysql->num_rows("SELECT ip,active FROM " . SQLBANLOG . " WHERE ip='" . $ip . "' AND active <> '0' LIMIT 1");

        $this->instance['isBanned'] = ($yes) ? true : false;

        if ($this->instance['isBanned'] === true)
            $this->instance['canPost'] = false; //Override canPost if banned.
    }

    private function checkPermissions() {
        if (!empty($this->row)) {
            //At this point they're a validated user.
            $this->instance['canPost'] = true;
            $allow = explode(',', $this->row['allowed']);
            $deny = explode(',', $this->row['denied']);

            foreach ($allow as $prop)
                $this->instance['permissions']["$prop"] = true;
            foreach ($deny as $prop)
                $this->instance['permissions']["$prop"] = false;
        }
    }
}

?>