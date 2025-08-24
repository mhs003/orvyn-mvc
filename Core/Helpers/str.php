<?php

if (!function_exists('leading_slash')) {
    function leading_slash($str)
    {
        if ($str[0] !== DIRECTORY_SEPARATOR) {
            $str = DIRECTORY_SEPARATOR . $str;
        }

        return $str;
    }
}
