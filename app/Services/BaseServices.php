<?php


namespace App\Services;

use App\Exceptions\BusinessException;
use App\Utils\CodeResponse;

class BaseServices
{
    protected static $instance = [];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        if ((static::$instance[static::class] ?? []) instanceof static) {
            return static::$instance[static::class];
        }
        static::$instance[static::class] = new static();
        return static::$instance[static::class];
    }


    /**
     * @param array $response
     * @param null $info
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
