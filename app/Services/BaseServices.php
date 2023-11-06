<?php

namespace App\Services;



class BaseServices
{

    protected static $instace;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (static::$instace instanceof static) {
            return static::$instace;
        }
        static::$instace = new static();
        return static::$instace;

    }

    private function __construct()
    {
    }


    private function clone()
    {
    }



}

