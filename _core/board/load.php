<?php
/*
    Loads a board's local config.json file into defined variables.
*/
class SaguaroLoader {
    public function loadConfig() {
        $conf = file_get_contents("config.json");
        if ($conf) {
            $conf = json_decode($conf, true);
            foreach ($conf as $key => $value) {
                define($key, $value);
            }
            return true;
        }
        error(S_CONFLOAD);
    }
}