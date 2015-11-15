<?php

/*

    SaguaroQL - because it's a cool name.

    Centralized MySQL-handling class.

*/

class SaguaroQL {
    public $connection;
    private $last; //This is updated after an advanced query (anything other than 'query') to cache the result. Calls without arguments will return this.

    /*function __construct() {
        $this->init(); //Init automatically.
    }*/

    function init() {
        $this->connect(SQLHOST, SQLUSER, SQLPASS);
        $this->selectDatabase(SQLDB);
    }
}

?>