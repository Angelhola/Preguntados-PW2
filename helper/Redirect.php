<?php

class Redirect
{
    public static function to($url)
    {
        if (!preg_match('#^(https?:)?//#', $url) && ($url[0] ?? '') !== '/') {
            $url = BASE_URL . $url;
        }

        header("Location: $url");
        exit();
    }

    public static function toIndex()
    {
        self::to('');
    }
}