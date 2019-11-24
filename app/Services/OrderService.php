<?php
/**
 * OrderService.php
 * @Author:代先华
 * @PROJECT_NAME:shop
 * @PRODUCT_NAME:PhpStorm
 * @Last Modified by:Administrator
 * @Last Modified time:2019-11-24  10:53
 * @MONTH_NAME_FULL:十一月
 */

namespace App\Services;
use App\Exceptions\InternalException;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Order;
use App\Models\ProductSku;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderService
{

    public function store(User $user,UserAddress $address,$remark,$items,CouponCode $coupon = null){
        if ($coupon) {
            $coupon->checkAvailable($user);
        }
        $order=DB::transaction(function ()use($user,$address,$remark,$items,$coupon){
//            更新地址的最后使用时间
            $address->update(['last_used_at'=>Carbon::now()]);
            //创建一个订单
            $order=new Order([
                'addresses'=>[
                'address'=>$address->full_address,
                'zip'=>$address->zip,
                'contact_name'=>$address->contact_name,
                'contact_phone'=>$address->contact_phone,
            ],
                'remark'=>$remark,
                'total_amount'=>0,
                ]);
            //订单关联到用户
            $order->user()->associate($user);
            $order->save();
            $totalAmount=0;
            //遍历用户已提交的sku
            foreach ($items as $data) {
                $sku=ProductSku::find($data['sku_id']);
                //创建一个OrderItem并直接与当前订单关联
                $item=$order->items()->make([
                    'amount'=>$data['amount'],
                    'price'=>$sku->price,
                ]);
                $item->prooduct()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount+=$sku->price * $data['amount'];
                //优惠券检查
                if ($coupon) {
                    $coupon->checkAvailable($user, $totalAmount);
                }
                if($sku->decreaseStock($data['amount'])<0){
                    throw new InvalidRequestException('库存不足');
                }
                
            }
            $order->update(['total_amount'=>$totalAmount]);
            //讲下单的商品从购物车中移除
            $skuIds=collect($items)->pluck('sku_id')->all();
            app(CartService::class)->remove($skuIds);
            return $order;

        });
        dispatch(new CloseOrder($order,config('app.order_ttl')));
        return $order;

    }
}