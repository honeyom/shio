<?php
/**
 * OrderPolicy.php
 * @Author:代先华
 * @PROJECT_NAME:shop
 * @PRODUCT_NAME:PhpStorm
 * @Last Modified by:Administrator
 * @Last Modified time:2019-11-24  10:23
 * @MONTH_NAME_FULL:十一月
 * 只允许订单的创建者可以看到对应的订单信息
 */

namespace App\Policies;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;
    public function  own(User $user,Order $order){
    return $order->user_id ==$user->id;
}

/***
 * 2.AuthServiceProvider 中注册这个策略：
 * 3.OrdersController@show() 中校验权限：    $this->authorize('own',$order);
 */

}