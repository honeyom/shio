<?php

namespace App\Http\Controllers;
use App\Exceptions\InternalException;
use App\Http\Requests\OrderRequest;
use App\Jobs\CloseOrder;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Models\Order;
use Carbon\Carbon;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    //
    public function store(OrderRequest $request){
        $user=$request->user();
        $order=DB::transaction(function () use($user,$request){
            $address=UserAddress::find($request->input('address_id'));
            //更新此地址的最后使用时间
            $address->update(['last_used_at'=>Carbon::now()]);
            //创建一个订单
            $order=new Order([
                'address'=>['address'=>$address->full_address,'zip'=>$address->zip,'contact_name'=>$address->contact_name,'contact_phone'=>$address->contact_phone,],
                'remark'=>$request->input('remark'),
                'total_amount'=>0,
            ]);
            //订单关联到当前用户

            $order->
            $order->user()->associate($user);
            $order->save();
            $totalAmount=0;
            $items=$request->input('items');
            //遍历用户提交的sku
            foreach ($items as $data){
                $sku=ProductSku::find($data['sku_id']);
                //创建一个orderItem并直接与当前订单关联
                $item=$order->items()->make(['amount'=>$data['amount'],'price'=>$sku->price]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount+=$sku->price * $data['amount'];
                if($sku->decreateStock($data['amount'])<=0){
                    throw  new InternalException('商品库存不足');
                }
            }


            //更新订单总额
            $order->update(['total_amount'=>$totalAmount]);
            //讲下单的商品从购物车中移除
            $skuIds=collect($items)->pluck('sku_id');
            $user->cartItems()->where('product_sku_id',$skuIds)->delete();

            return $order;

        });
        //订单创建之后,防止一直下订单,不支付,暂用库存,在一定时间内执行定时任务,关闭订单,把库存还回去
            $this->dispatch(new CloseOrder($order,config('app.order_ttl')));
        return $order;
    }

}
