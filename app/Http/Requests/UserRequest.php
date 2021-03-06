<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'name'=>'required|between:3,25|regx:/^[A-Za-z0-9\-\_]+$/|unique:users,name',
            'password'=>'required|string|min:6',
            'verify_key'=>'required|string',
            'vetify_code'=>'required|string',
        ];
    }
    public function attributes()
    {
        return [
          'verify_key'=>'短信验证码 key',
          'verify_code'=>'短信验证码',
        ]; // TODO: Change the autogenerated stu
    }
}
