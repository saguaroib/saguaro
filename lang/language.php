<?php
    //Require base translation.
    require("en-us.php");
    //Get defined language and include it, overwriting en-us as needed.
    include("../config.php");
    if (defined(LANGUAGE) && LANGUAGE !== "en-us") {
        include(LANGUAGE . ".php");
    }
    
    //Define constants.
    foreach (get_defined_vars() as $KEY=>$VALUE) {
        if (substr($KEY,0,2) === "S_") {
            define($KEY,$VALUE);
        }
    }
?>
