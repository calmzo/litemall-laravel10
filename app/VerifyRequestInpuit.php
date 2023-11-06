<?php

namespace App;

use App\Exceptions\BusinessException;
use App\Utils\CodeResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

trait VerifyRequestInpuit
{

    /**
     * @param $key
     * @param  null  $default
     * @return array|mixed|null
     * @throws BusinessException
     */
    public function verifyId($key, $default = null)
    {
        return $this->verifyData($key, $default, 'required|integer|digits_between:1,20');
    }


    /**
     * @param $key
     * @param  null  $default
     * @return array|mixed|null
     * @throws BusinessException
     */
    public function verifyString($key, $default = null)
    {
        return $this->verifyData($key, $default, 'string');
    }


    /**
     * @param $key
     * @param  null  $default
     * @return array|mixed|null
     * @throws BusinessException
     */
    public function verifyBoolean($key, $default = null)
    {
        return $this->verifyData($key, $default, 'boolean');
    }


    /**
     * @param $key
     * @param  null  $default
     * @return array|mixed|null
     * @throws BusinessException
     */
    public function verifyInteger($key, $default = null)
    {
        return $this->verifyData($key, $default, 'integer');
    }

    /**
     * @param $key
     * @param  null  $default
     * @param  array  $enum
     * @return array|mixed|null
     * @throws BusinessException
     */
    public function verifyEnum($key, $default = null, $enum = [])
    {
        return $this->verifyData($key, $default, Rule::in($enum));
    }


    /**
     * @param $key
     * @param $default
     * @param $rule
     * @return array|mixed|null
     * @throws BusinessException
     */
    public function verifyData($key, $default, $rule)
    {
        $value = request()->input($key, $default);
        $validator = Validator::make([$key => $value], [$key => $rule]);
//        if (is_null($default) && is_null($value)) {
//            return $value;
//        }
        if ($validator->fails()) {
            throw new BusinessException(CodeResponse::PARAM_VALUE_ILLEGAL, $validator->errors()->first());
        }
        return $value;
    }
}
