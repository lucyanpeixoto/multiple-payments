<?php

if (!function_exists('getOnlyNumbers')) {
    function getOnlyNumbers($string) 
    {
        return preg_replace("/[^0-9]/", "", $string); 
    }
}


if (!function_exists('pr2')) {

    function pr2($var) 
    {
        $template = PHP_SAPI !== 'cli' ? '<pre>%s</pre>' : "\n%s\n";
        printf($template, print_r($var, true));        
    }
}