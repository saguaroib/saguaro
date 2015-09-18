<?php
    //Potentially rewrite as a class to accept and apply languages easily.
    //However, we should never need to explicitly need config.php if used alone.

    //Require base translation.
    require(dirname(__FILE__) . "/en-us.php");
    //Get defined language and include it, overwriting en-us as needed.
    //include("/../config.php");
    if (defined(LANGUAGE) && LANGUAGE !== "en-us") {
        include(LANGUAGE . ".php");
    }
    
    //Define constants.
    foreach (get_defined_vars() as $KEY=>$VALUE) {
        if (substr($KEY,0,2) === "S_" && !defined($KEY)) {
            define($KEY,$VALUE);
        }
    }
?>
