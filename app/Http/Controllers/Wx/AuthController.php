<?php

namespace App\Http\Controllers\Wx;

use App\Models\User\User;
use App\Services\User\UserServices;
use App\Utils\CodeResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{Auth, Cache, Hash};

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
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
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

        //验证验证码 todo 放开校验
//        UserServices::getInstance()->checkCaptcha($mobile, $code);

        $avatar = 'https://yanxuan.nosdn.127.net/80841d741d7fa3073e0ae27bf487339f.jpg?imageView&quality=90&thumbnail=64x64';
        //微信登录 todos

        $user = new User();
        $user->username = $username;
        $user->password = Hash::make($password);
        $user->mobile = $mobile;
        $user->avatar = $avatar;
        $user->nickname = $username;
        $user->last_login_time = Carbon::now()->toDateTimeString();
        $user->last_login_ip = $request->getClientIp();
        $user->add_time = Carbon::now()->toDateTimeString();
        $user->update_time = Carbon::now()->toDateTimeString();
        $user->save();

        // 给新用户发送注册优惠券
//        couponAssignService.assignForRegister(user.getId());
        $token = Auth::guard('wx')->login($user);
        //TODO 新用户发券

        $ret = [
            'token' => $token,
            'userInfo' => $user
        ];
        return $this->success($ret);
    }


    public function login(Request $request)
    {
        $username = $this->verifyString('username');
        $password = $this->verifyId('password');
        $user = UserServices::getInstance()->getByUsername($username);

        if (is_null($user)) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT);
        }

        $isPass = Hash::check($password, $user->getAuthPassword());
        if (!$isPass) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT, '账号和密码不正确');
        }

        $user->last_login_time = now()->toDateTimeString();
        $user->last_login_ip = $request->getClientIp();

        if (!$user->save()) {
            return $this->fail(CodeResponse::UPDATED_FAIL);
        }

        $token = Auth::login($user);

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
        $mobile = $this->verifyId('mobile');
        $user = UserServices::getInstance()->getByMobile($mobile);
        if (!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_MOBILE_REGISTERED);
        }

        //todo 防刷
        $lock = Cache::add('register_captcha_lock_' . $mobile, 1, 60);
        if (!$lock) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY);
        }
        $isPass = UserServices::getInstance()->checkMobileSendCaptchaCount($mobile, 10);
        if (!$isPass) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY, '验证码每天发送不能超过10次');
        }
        $code = UserServices::getInstance()->setCaptcha($mobile);
        UserServices::getInstance()->sendCaptchaMsg(mobile: $mobile, code: $code);

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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $password = $request->input('password');
        $mobile = $request->input('mobile');
        $code = $request->input('code');

        if (is_null($password) || is_null($mobile) || is_null($code)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }

        if ($code != Cache::get('register_captcha_' . $mobile)) {
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
        $mobile = $this->verifyString('mobile');
        $code = $this->verifyString('code');
        $password = $this->verifyString('password');

        $isPass = UserServices::getInstance()->checkCaptcha(mobile: $mobile, code: $code);
        if (!$isPass) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_UNMATCH);
        }

        $user = UserServices::getInstance()->getByMobile($mobile);
        if (is_null($user)) {
            return $this->fail(CodeResponse::AUTH_MOBILE_UNREGISTERED);
        }
        $password = Hash::make($password);
        $user->password = $password;
        return $user->save() ? $this->success() : $this->fail(CodeResponse::UPDATED_FAIL);
    }

    /**
     * 账号信息更新
     */
    public function profile(Request $request)
    {
        $nickname = $this->verifyString('nickname', null);
        $avatar = $this->verifyString('avatar', null);
        $gender = $this->verifyString('gender', null);

        /** @var User $user */
        $user = $this->user();
        if (!empty($nickname)) {
            $user->nickname = $nickname;
        }

        if (!empty($avatar)) {
            $user->avatar = $avatar;
        }

        if (!empty($gender)) {
            $user->gender = $gender;
        }

        return $user->save() ? $this->success() : $this->fail(CodeResponse::UPDATED_FAIL);
    }


    public function logout()
    {
        Auth::logout();
        return $this->success();
    }

}
