<?php

namespace App\Services\User;

use App\Models\User\User;
use App\Services\BaseServices;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;
use App\Notifications\VerificationCode;

class UserServices extends BaseServices
{

    /**
     * 根据用户名获取用户
     * @param string $username
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

    /**
     * @param $ids
     * @return Builder[]|Collection|\Illuminate\Support\Collection
     * 获取用户
     */
    public function getUsers($ids)
    {
        if (empty($ids)) {
            return collect([]);
        }
        return User::query()->whereIn('id', $ids)->get();
    }

    /**
     * @param $mobile
     * @param $send_count
     * @return bool
     * 检查验证码每天发送的次数
     */
    public function checkMobileSendCaptchaCount($mobile, $send_count)
    {
        $countKey = 'register_captcha_count_' . $mobile;

        if (Cache::has($countKey)) {
            $count = Cache::increment('register_captcha_count_' . $mobile, 1);
            if ($count > $send_count) {
                return false;
            }
        } else {
            Cache::put($countKey, 1, Carbon::tomorrow()->diffInSeconds(now()));
        }

        return true;
    }

    /**
     * @param string $mobile
     * @return int
     * @throws \Exception
     * 设置短信验证码
     */
    public function setCaptcha(string $mobile)
    {
        $code = random_int(100000, 999999);
        Cache::put('register_captcha_' . $mobile, $code, 600);
        return $code;
    }

    /**
     * @param string $mobile
     * @param $code
     * @return bool
     * 发送验证码
     */
    public function sendCaptchaMsg(string $mobile, $code)
    {
//        if (app()->env == 'testing') {
//            Log::info('手机号码：'.$mobile.'不用发送短信哦');
//            return true;
//        }
        Notification::route(
            EasySmsChannel::class,
            new PhoneNumber($mobile, 86)
        )->notify(new VerificationCode($code, 'SMS_117526525'));
        return true;
    }

}

