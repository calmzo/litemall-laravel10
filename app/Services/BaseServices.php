<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Utils\CodeResponse;

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


    /**
     * @param  array  $response
     * @param  null  $info
     * @throws BusinessException
     */
    public function throwBusinessException(array $response = CodeResponse::PARAM_ILLEGAL, $info = null)
    {
        if (!is_null($info)) {
            $response[1] = $info;
        }
        throw new BusinessException($response);
    }

}

