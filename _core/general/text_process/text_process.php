<?php

/*

    TextProcessor

    Not intended to be used alone, but instead to be loaded as a parent class which extends the desired processor:
        - BBCode
        - Markdown

    However, it can still be used alone by pushing valid array indexes to $processors then calling format().

*/

class TextProcessor {
    public $processors = [];

    private function process($input) {
        $out = str_replace("&#44;", ",", $input);

        foreach ($this->processors as $regex => $resub) {
            $out = preg_replace($regex, $resub, $out);
        }

        return str_replace( ",", "&#44;", $out);
    }

    function format($input) {
        return $this->process($input);
    }
}

?>