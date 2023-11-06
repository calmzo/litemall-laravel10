<?php

namespace App\Http\Controllers\Wx;

class UserController extends WxController
{
    public $except = [''];

    public function index()
    {
        return $this->success([]);
    }

}
