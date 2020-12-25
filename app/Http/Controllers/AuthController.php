<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller {
    public function __construct() {

    }
    public $token = true;

    // 註冊
    // req: name, email, password, c_password
    // res: success, token
    public function register(Request $request) {

        $validator = Validator::make($request->all(),
            [
                'name'       => 'required',
                'email'      => 'required|email',
                'password'   => 'required',
                'c_password' => 'required|same:password',
            ]);

        if ($validator->fails()) {

            // return response()->json(['error' => $validator->errors()], 403);
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], Response::HTTP_NOT_ACCEPTABLE);

        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->role_type = $request->RoleType;
        $user->role_no = $request->RoleNo;
        $user->save();

        if ($this->token) {
            return $this->login($request);
        }

        return response()->json([
            'success' => true,
            'data'    => $user,
        ], Response::HTTP_OK);
    }

    // 燈入
    // req: email, password
    // res: success, token
    public function login(Request $request) {
        $input = $request->only('email', 'password');
        $jwt_token = null;

        if (!$jwt_token = JWTAuth::attempt($input)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        $user = JWTAuth::user();

        $user->haskey = DB::table('webauthn_keys')->where('user_id', $user->id)->first() != null;

        return response()->json([
            'success' => true,
            'token'   => $jwt_token,
            'user'    => $user,
        ]);
    }

    // 登出 +token
    // req:
    // res: success, message
    public function logout(Request $request) {
        Validator::make($request->all(),
            [
                'token' => 'required',
            ]);

        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully',
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, the user cannot be logged out',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // 獲取使用者資料 +token
    // req:
    // res: user{id, name, ...}
    public function getUser(Request $request) {
        // return $request->getHttpHost();
        Validator::make($request->all(),
            [
                'token' => 'required',
            ]);

        $user = JWTAuth::authenticate($request->token);

        $user->haskey = DB::table('webauthn_keys')->where('user_id', $user->id)->first() != null;
        if ($user->haskey) {
            $user->haskey_id = DB::table('webauthn_keys')->where('user_id', $user->id)->first()->id;
        }

        return response()->json(['user' => $user]);
    }

    // 測試權限
    public function roleTest(Request $request) {
        Validator::make($request->all(),
            [
                'token' => 'required',
            ]);

        $user = JWTAuth::authenticate($request->token);

        return response()->json(['MemberId' => $user->id, 'Account' => $user->name, 'RuleType' => $user->role_type, 'RuleNo' => $user->role_no, 'IsSuccess' => 'true']);
    }
}
