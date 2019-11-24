<?php
/**
 * HandleRefundRequest.php
 * @Author:代先华
 * @PROJECT_NAME:shop
 * @PRODUCT_NAME:PhpStorm
 * @Last Modified by:Administrator
 * @Last Modified time:2019-11-24  11:32
 * @MONTH_NAME_FULL:十一月
 * 拒绝退款
 */

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class HandleRefundRequest extends FormRequest
{

    public function rules(){
        return [
            'agree'  => ['required', 'boolean'],
            'reason' => ['required_if:agree,false'], // 拒绝退款时需要输入拒绝理由
        ];
    }
}