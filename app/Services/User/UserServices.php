<?php

namespace App\Services\User;

use App\Models\User\User;
use App\Services\BaseServices;

class UserServices extends BaseServices
{

    /**
     * 根据用户名获取用户
     * @param  string  $username
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getByUsername(string $username)
    {
        return User::query()->where('username', $username)->first();
    }

    public function getByMobile(string $mobile)
    {
        return User::query()->where('mobile', $mobile)->first();
    }

    public function getById($id)
    {
        return User::query()->find($id);
    }

    public function getUsersByIds($ids)
    {
        if (empty($ids)) {
            return collect([]);
        }
        return User::query()->whereIn('id', $ids)->where('deleted', 0)->get();
    }


}

