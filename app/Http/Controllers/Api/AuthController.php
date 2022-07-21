<?php

namespace App\Http\Controllers\Api;

use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;
use Hash;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        if (auth()->attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = auth()->user();

            if ($user->user_status_code == 3) {
                $message = "Tài khoản đã bị vô hiệu quá";
                return $this->handleError(404, [], $message);
            }

            if (empty($user->email_verified_at)) {
                $data['user'] = $user;
                $data['token'] = "";
                auth()->logout();
                $message = "Thành công";
                return $this->handleResponse(200, $data, $message);
            }

            // updata status
            $user->user_status_code = 1;
            $user->save();

            $data['user'] = $user;
            $data['token'] =  $user->createToken('api-auth')->plainTextToken;

            $message = "Thành công";
            return $this->handleResponse(200, $data, $message);
        } else {
            $message = "Tài khoản hoặc mật khẩu không chính xác";
            return $this->handleError(401, [], $message);
        }
    }

    public function register(Request $request)
    {
        // check years old (> 14)
        $day = date('Y-m-d', strtotime('-14 years'));

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password',
            'name' => 'required|string|min:2|max:30',
            'birthday' => 'required|date|before:' . $day,
            'gender' => 'required|string',
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'birthday' => $request->birthday,
            'gender' => $request->gender,
            'role_id' => 2,
            'user_status_code' => 0
        ]);

        $otp = Otp::create([
            'user_id' => $user->id,
            'code' => $this->randomOtp(),
            'expired_in' => strtotime('+10 minutes'),
            'type' => 1
        ]);
        // send mail register
        try {
            Mail::to($user->email)->send(new OtpMail($otp));
        } catch (\Exception $err) {
            $otp->delete();
            $user->delete();
            $message = "Không thể gửi mã OTP tới email đăng ký";
            return $this->handleError(500, [], $message);
        }

        $message = "Tạo tài khoản thành công";
        return $this->handleResponse(201, $user, $message);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|same:new_password'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $user = User::where('id', '=', auth()->id())
            ->firstOrFail();

        if (Hash::check($request->current_password, $user->password)) {
            $user->password = Hash::make($request->new_password);
            $user->save();

            $message = "Đổi mật khẩu thành công";
            return $this->handleResponse(204, [], $message);
        }

        $message = "Mật khẩu cung cấp không chính xác";
        return $this->handleError(422, [], $message);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->user_status_code = 0;
        $user->save();
        $user->currentAccessToken()->delete();

        $message = "Đăng xuất thành công";
        return $this->handleResponse(204, [], $message);
    }

    public function resentOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'type' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $user = User::where('id', '=', $request->user_id)
            ->firstOrFail();

        Otp::where([
            ['user_id', '=', $request->user_id],
            ['type', '=', $request->type]
        ])->delete();

        $otp = Otp::create([
            'user_id' => $request->user_id,
            'code' => $this->randomOtp(),
            'expired_in' => strtotime('+10 minutes'),
            'type' => $request->type
        ]);
        // send mail
        try {
            Mail::to($user->email)->send(new OtpMail($otp));
        } catch (Throwable $e) {
            // Get error here
        }
        $message = "Gửi mã xác thực otp thành công";
        return $this->handleResponse(204, [], $message);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $user = User::where('email', 'like', $request->email)
            ->firstOrFail();

        if ($user) {
            $otp = Otp::create([
                'user_id' => $user->id,
                'code' => $this->randomOtp(),
                'expired_in' => strtotime('+10 minutes'),
                'type' => 2
            ]);
            // send mail forgot password
            try {
                Mail::to($user->email)->send(new OtpMail($otp));
            } catch (Throwable $e) {
                // Get error here
            }
            $message = "Thành công";
            return $this->handleResponse(200, $user, $message);
        }
        $message = "Địa chỉ email cung cấp không tồn tại";
        return $this->handleError(404, [], $message);
    }

    public function forgotPasswordVerifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|min:6|max:6',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $otp = Otp::where([
            ['code', 'like', $request->code],
            ['user_id', '=', $request->user_id],
            ['type', '=', 2],
            ['expired_in', '>=', now()]
        ])->firstOrFail();

        if ($otp) {
            $otp->delete();
            $message = "Xác thực otp thành công";
            return $this->handleResponse(204, [], $message);
        }
    }

    public function forgotPasswordComplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        // updata status and login user
        $user = User::where('id', '=', $request->user_id)
            ->firstOrFail();

        if (empty($user->email_verified_at)) {
            $user->email_verified_at = now();
        }
        $user->password = Hash::make($request->password);
        $user->user_status_code = 1;
        $user->save();
        auth()->login($user);

        $data['user'] = $user;
        $data['token'] =  $user->createToken('api-auth')->plainTextToken;

        $message = "Thành công";
        return $this->handleResponse(200, $data, $message);
    }

    public function registerVerifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|min:6|max:6',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $otp = Otp::where([
            ['code', 'like', $request->code],
            ['user_id', '=', $request->user_id],
            ['type', '=', 1],
            ['expired_in', '>=', now()]
        ])->firstOrFail();

        if ($otp) {
            // success
            $otp->delete();
            // updata status and login user
            $user = User::where('id', '=', $request->user_id)
                ->firstOrFail();
            $user->email_verified_at = now();
            $user->user_status_code = 1;
            $user->save();
            auth()->login($user);

            $data['user'] = $user;
            $data['token'] =  $user->createToken('api-auth')->plainTextToken;

            $message = "Thành công";
            return $this->handleResponse(200, $data, $message);
        }
    }

    public function randomOtp()
    {
        $otp = "";
        for ($i = 0; $i < 6; $i++) {
            $otp = $otp . mt_rand(0, 9);
        }
        return $otp;
    }
}
