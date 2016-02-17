<?php

/*

    Very basic, and hard to use since most pages are cached and we don't use the Page class enough.

*/

class Profiler {
    private $stats = [
        'php' => [],
        'sql' => []
    ];

    function init() {
        $this->stats['php']['start'] = $this->getMicrotime() * -1;
        $this->stats['sql']['start'] = $this->getSQLcount();
    }

    function total() {
        global $mode;

        $php = ($this->getMicrotime() + $this->stats['php']['start']) / 1000;
        $php = sprintf('%f', $php);
        $sql = ($this->getSQLcount() - $this->stats['sql']['start']);
        return "<small>Generated in $php seconds ($sql queries) ($mode)</small>";
    }

    private function getMicrotime() {
        return microtime(true);
    }

    private function getSQLcount() {
        global $mysql;
        $blah = $mysql->fetch_assoc("SHOW STATUS WHERE variable_name = 'Queries'");
        return ((int) $blah['Value']);
    }
}

?>