<?php
/**
 * ApplyRefundRequest.php
 * @Author:代先华
 * @PROJECT_NAME:shop
 * @PRODUCT_NAME:PhpStorm
 * @Last Modified by:Administrator
 * @Last Modified time:2019-11-24  11:21
 * @MONTH_NAME_FULL:十一月
 * 申请退款
 */

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class ApplyRefundRequest extends FormRequest
{
    public function rules(){
        return [
            'reason'=>'required',
        ];
    }
    public function attribute(){
        return ['reason'=>'原因',];
    }


}