<?php

/*------------------------------------------------------------------
[Master Stylesheet]
==========================================================================================================================================
Project                        :   Sypaze Technologies
==========================================================================================================================================
Created Date                    :  17/June/2024
------------------------------------------------------------------------------------------------------------------------------------------
Author & Copyright Ownership   :   Murtaza Bharmal
------------------------------------------------------------------------------------------------------------------------------------------
Author URL                     :   https://murtazabharmal.com
------------------------------------------------------------------------------------------------------------------------------------------
Support		                   :   mb.prodeveloper@gmail.com
------------------------------------------------------------------------------------------------------------------------------------------
*/

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
