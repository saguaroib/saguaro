<?php
/*

    Generate a thread based on the given OP #.

    Example:
        1 is an OP
        2 is a reply to OP 1
        3 is a reply to OP 1
        4 is an OP

    $sample = new Thread();
    $sample->format(1); //Format the first OP.
    $sample->format(2); //Will succeed, but only attempt to format that as an OP then fail (since replies can't have replies).

*/

//require("../../config.php"); //In a real environment this wouldn't be needed as it would be inherited from its parent.
require("post.php");

class Thread {
    function format($op) {
        global $log;

        $temp .= $this->generateOP($log[$op]);

        foreach ($log as $entry) {
            if ($entry["resto"] == $op) {
                $temp .= $this->generateReply($entry);
            }
        }

        return $temp;
    }

    function generateOP($input) {
        $post = new Post();
        $post->data = $input;

        return $post->formatOP();
    }

    function generateReply($input) {
        $post = new Post();
        $post->data = $input;

        return $post->format();
    }
}
?>