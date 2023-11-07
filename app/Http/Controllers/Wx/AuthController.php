<?php

namespace App\Http\Controllers\Wx;

use App\Models\User\User;
use App\Notifications\VerifycationCode;
use App\Services\User\UserServices;
use App\Utils\CodeResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;

class AuthController extends WxController
{
    protected $except = ['register', 'login', 'regCaptcha'];

    public function register(Request $request)
    {
        //获取参数
        $username = $request->input('username');
        $password = $request->input('password');
        $mobile = $request->input('mobile');
        $code = $request->input('code');
        // 如果是小程序注册，则必须非空
        // 其他情况，可以为空
        $wxCode = $request->input('wxCode');

        //参数验证
        if (is_null($username) || is_null($password) || is_null($password) || is_null($code)) {
            return $this->fail(CodeResponse::BADARGUMENT);
        }
        $userByUsername = UserServices::getInstance()->getByUsername($username);
        if ($userByUsername) {
            return $this->fail(CodeResponse::AUTH_NAME_REGISTERED, '用户名已注册');

        }
        $userByMobile = UserServices::getInstance()->getByMobile($mobile);
        if ($userByMobile) {
            return $this->fail(CodeResponse::AUTH_NAME_REGISTERED, '手机号已注册');
        }
//
//        if (!RegexUtil.isMobileSimple(mobile)) {
//            return ResponseUtil.fail(AUTH_INVALID_MOBILE, "手机号格式不正确");
//        }


        if ($code != Cache::get('register_captcha_'.$mobile)) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_UNMATCH, '验证码错误');
        }
        //微信登录 todos

        $user = new User();
        $user->username = $username;
        $user->password = Hash::make($password);
        $user->mobile = $mobile;
        $user->avatar = 'https://yanxuan.nosdn.127.net/80841d741d7fa3073e0ae27bf487339f.jpg?imageView&quality=90&thumbnail=64x64';
        $user->nickname = $username;
        $user->last_login_time = now();
        $user->last_login_ip = $request->getClientIp();
        $user->save();

        // 给新用户发送注册优惠券
//        couponAssignService.assignForRegister(user.getId());
        $token = Auth::guard('wx')->login($user);
        $ret = [
            'token' => $token,
            'userInfo' => $user
        ];
        return $this->success($ret);
    }


    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        //参数验证
        if (is_null($username) || is_null($password)) {
            return $this->fail(CodeResponse::BADARGUMENT);
        }
        $user = UserServices::getInstance()->getByUsername($username);
        if (empty($user)) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT);
        }
        //验证密码
        $is_pass = Hash::check($password, $user->password);
        if (!$is_pass) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT, '账号密码不对');
        }
        //更新登录情况
        $user->last_login_time = now();
        $user->last_login_ip = $request->getClientIp();
        if (!$user->save()) {
            return $this->fail(CodeResponse::UPDATED_FAIL);
        }
        $token = Auth::guard('wx')->login($user);
        return $this->success([
            'token' => $token,
            'userInfo' => [
                'nickName' => $username,
                'avatarUrl' => $user->avatar
            ]
        ]);
    }

    public function regCaptcha(Request $request)
    {
        //todo 获取手机号
        $mobile = $request->input('mobile', '13153187435');
        //todo 随机生成6位验证码
        $code = random_int(100000, 999999);
        //todo 防刷
        $lock = Cache::add('register_captcha_lock_'.$mobile, 1, 5);
        if (!$lock) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY);
        }
        $countKey = 'register_captcha_count_'.$mobile;
        if (Cache::has($countKey)) {
            $count = Cache::increment($countKey);
            if ($count > 3) {
                return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY, '验证码当天不能超过10条');
            }
        } else {
            Cache::put($countKey, 0, Carbon::now()->diffInSeconds(now()));
        }
        //todo 保存手机号和验证码关系
        Cache::put('register_captcha_'.$mobile, $code, 600);
        // todo 发送短信
        Notification::route(
            EasySmsChannel::class,
            new PhoneNumber($mobile, 86)
        )->notify(new VerifycationCode($code));

        return $this->success();
    }

    public function info()
    {
        $user = Auth::guard('wx')->user();
        $ret = [
            'nickName' => $user->nickname,
            'avatar' => $user->avatar,
            'gender' => $user->gender,
            'mobile' => $user->mobile,

        ];
        return $this->success($ret);

    }

    /**
     * 账号密码重置
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $password = $request->input('password');
        $mobile = $request->input('mobile');
        $code = $request->input('code');

        if (is_null($password) || is_null($mobile) || is_null($code)) {
            return $this->fail(CodeResponse::BADARGUMENT);
        }

        if ($code != Cache::get('register_captcha_'.$mobile)) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_UNMATCH, '验证码错误');
        }
        $user = UserServices::getInstance()->getByMobile($mobile);
        if (!$user) {
            return $this->fail(CodeResponse::AUTH_NAME_REGISTERED, '手机号未注册');
        }
        $user->password = Hash::make($password);
        $user->save();

        return $this->success();
    }


    /**
     *账号手机号码重置
     */
    public function resetPhone(Request $request)
    {
        $password = $request->input('password');
        $mobile = $request->input('mobile');
        $code = $request->input('code');

        if (is_null($password) || is_null($mobile) || is_null($code)) {
            return $this->fail(CodeResponse::BADARGUMENT);
        }

        if ($code != Cache::get('register_captcha_'.$mobile)) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_UNMATCH, '验证码错误');
        }
        $useByMobile = UserServices::getInstance()->getByMobile($mobile);
        if ($useByMobile) {
            return $this->fail(CodeResponse::AUTH_NAME_REGISTERED, '手机号已注册');
        }
        $user = Auth::user();
        $user->mobile = $mobile;
        $user->save();
        return $this->success();
    }

    /**
     * 账号信息更新
     */
    public function profile(Request $request)
    {
        $id = Auth::user()->id;
        $avatar= $request->input('avatar');
        $gender = $request->input('gender');
        $nickname = $request->input('nickname');

        $user = UserServices::getInstance()->getById($id);
        if (!is_null($avatar)){
            $user->avatar = $avatar;
        }
        if (!is_null($gender)){
            $user->gender = $gender;
        }
        if (!is_null($nickname)){
            $user->nickname = $nickname;
        }
        $user->save();
        return $this->success();
    }


    public function logout()
    {
        Auth::logout();
        return $this->success();
    }

}
