<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class InternalException extends Exception
{
    //
    protected $msgForUser;
    public function __construct($message = "", $code = 0,$msgForUser="系统内部错误", Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->msgForUser=$msgForUser
    }
    public function rendor(Request $request){
        if($request->expectsJson()){
            return response()->json(['msg'=>$this->msgForUser],$this->code);
        }
        return view('pages.error',['msg'=>$this->msgForUser]);
    }
}
