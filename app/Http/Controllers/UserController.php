<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\User;
use Illuminate\Filesystem\Cache;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    //
    public function store(UserRequest $request){
        $verifyData=Cache::get($request->verify_key);
        if(!$verifyData){
            return $this->response->error('验证码已失效',422);
        }
        if(!hash_equals($verifyData['code'],$request->verification_code)){
            return $this->response->errorUnauthorized('验证码错误');
        }
        $user=User::create([
            'name'=>$request>name,
            'phone'=>$verifyData['phone'],
            'password'=>bcrypt($request->password),
        ]);
        Cache::forget($request->verify_key);
        return $this->response->created();

    }
}
