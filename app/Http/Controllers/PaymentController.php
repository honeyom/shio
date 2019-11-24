<?php
/**
 * PaymentController.php
 * @Author:代先华
 * @PROJECT_NAME:shop
 * @PRODUCT_NAME:PhpStorm
 * @Last Modified by:Administrator
 * @Last Modified time:2019-11-24  11:13
 * @MONTH_NAME_FULL:十一月
 */

namespace App\Http\Controllers;
use App\Models\Order;
use App\Exceptions\InvalidRequestException;
class PaymentController
{

    public function payment(Order $order,Request $request){
        //判断订单是否属于当前用户
        $this->Authorize('own',$order);
        //订单已支付或者已关闭
        if($order->paid_at || $order->closed){
            throw  new InvalidRequestException('订单状态不正确');
        }
        //调用支付接口
//        return //
        [
            'out_trade_no'=>$order->no,
            'total_amount'=>$order->total_amount,
            'subject'=>'订单标题'
        ];
    }
}