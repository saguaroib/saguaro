<?php
/*

    Generates a listing of OPs.

    The class you didn't know you wanted.
    Don't try this at home.

*/

require("post.php");

class Catalog {
    public $data = [];
    
    function format() {
        global $log;
        
        $temp = "";
        
        //Pick out OPs.
        foreach ($log as $entry) {
            if ($entry['resto'] == 0)
                $this->data[$entry['no']] = [
                    "no" => $entry['no'],
                    "replies" => 0,
                    "images" => 0
                ];
        }
        
        //Assign reply stats.
        foreach ($log as $entry) {
            if ($entry['resto'] > 0) {
                $this->data[$entry['resto']]["replies"]++;
                if ($entry["fname"])
                    $this->data[$entry['resto']]["images"]++;
                
            }
        }
        
        array_pop($this->data); //TO-DO: Not this.
        $this->data = array_reverse($this->data); //Eh.
        
        //print nl2br(print_r($this->data, true));
        
        foreach ($this->data as $entry) {
            $temp .= $this->generateOP($log[$entry["no"]],$entry);
        }
        
        $temp = "<div class='catalog_container'>" . $temp . "</div>";
        
        return $temp;
    }
    
    function generateOP($input,$stats) {
        $post = new Post();
        
        return $post->format($input,$stats);
    }
}


?>