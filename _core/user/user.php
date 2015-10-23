<?php
/*

    Handles user instance data.

    $user = new User;
    $user->init();
    $user->info(); //Returns entire $instance.
    $user->info('permissions'); //Returns specified key from $instance.

    This class saves User->instance to $_SESSION[User->session_var] after being first run then retrieves it instead of populating it again when reused (multiple inits).
    To get around this behavior (for instances that need the latest user status) call User->invalidate() before User->init().
    Or if you're lazy, User->autoinit() does the above.

*/

class User {
    public $session_var = 'user'; //The session variable to store $instance in. Can be changed to handle multiple user instances at once.
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

    public function init($user, $pass) {
        if (!$this->load()) {
            //Process as normal.
            $this->checkBan(); //Just get this out of the way. Admins can be banned and still post. WIP.

            if ($user && $pass)
                $this->pullRow($user, $pass);
                $this->checkPermissions(); //Only check (therefore modify) permissions if we have a validated user.

            $this->save();
        } //Else, do nothing since it was retrieved from the session.
    }

    public function autoinit($user, $pass) {
        $this->invalidate();
        $this->init($user, $pass);
    }

    public function info($topkey = null) {
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

    private function load() {
        if (isset($_SESSION[$this->session_var])) {
            $this->instance = $_SESSION[$this->session_var];
            return true;
        } else {
            return false;
        }
    }

    private function save() {
        $_SESSION[$this->session_var] = $this->instance;
    }

    public function invalidate() {
        if (isset($_SESSION[$this->session_var]))
            unset($_SESSION[$this->session_var]);
    }
}

?>