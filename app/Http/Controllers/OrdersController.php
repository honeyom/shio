<?php

namespace App\Http\Controllers;
use App\Exceptions\InternalException;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\ApplyRefundRequest;
use App\Http\Requests\HandleRefundRequest;
use App\Http\Requests\OrderRequest;
use App\Jobs\CloseOrder;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Models\Order;
use App\Services\CartService;
use Carbon\Carbon;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CouponCode;
use App\Exceptions\CouponCodeUnavailableException;
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
            $skuIds=collect($items)->pluck('sku_id')->all;
            $user->cartItems()->where('product_sku_id',$skuIds)->delete();

            return $order;

        });
        //订单创建之后,防止一直下订单,不支付,暂用库存,在一定时间内执行定时任务,关闭订单,把库存还回去
            $this->dispatch(new CloseOrder($order,config('app.order_ttl')));
        return $order;
    }
    public function index(Request $request){
        $orders=Order::query()
        ->with(['items.product','items.productSku'])
        ->where('user_id',$request->user()->id)
        ->orderBy('create_at','desc')
        ->paginate();
    return view('orders.index',['orders'=>$orders]);
    }
    public function show(Order $order,Request $request){
            $this->authorize('own',$order);
            return view('orders.show',['order'=>$order->load(['items.productSku','items.product'])]);
//            load与with预加载类似,load是已经查询出来的模型上调用,with是在查询构造器上调用
    }

}

//自动解析功能注入 CartService 类

class OrdersController2 extends Controller{

    public  function store(OrderRequest $request,CartService $cartService,CouponCode $coupon = null){

        $user=$request->user();
        // 如果传入了优惠券，则先检查是否可用
        if ($coupon) {
            // 但此时我们还没有计算出订单总金额，因此先不校验
            $coupon->checkAvailable();
        }
//        $coupon 也放到了 use 中
        $order=DB::transaction(function () use($user,$request,$cartService,$coupon){

            //讲下单的商品从购物车中移除
            $skuIds=collect($request->input('items'))->pluck('sku_id')->all();
            $cartService->remove($skuIds);
        });
    }


    //1.申请退款
    public function applayRefund(Order $order,ApplyRefundRequest $request){
        //校验订单是否属于当前用户
        $this->authorize('owen',$order);
        //判断订单是否已付款
        if(!$order->paid_at){
            throw new InvalidRequestException('订单未支付,不可退款');
        }
        //判断订单状态是否正确
        if($order->refund_status !==Order::REFUND_STATUS_PENDING){
            throw  new InvalidRequestException('订单已经申请过退款,不要重复申请');
        }
        $extra=$order->exists??[];
        $extra['refund_reason']=$request->input('reason');
        $order->update([
            'refund_status'=>Order::REFUND_STATUS_APPLIED,
            'extra'=>$extra,
        ]);
        return $order;

    }
    //2.拒绝退款
    public function handleRefund(Order $order,HandleRefundRequest $request){
        //判断订单状态是否正确
        if($order->refund_status !==Order::REFUND_STATUS_APPLIED ){
            throw new InvalidRequestException('订单状态不正确');
        }
        //是否同意退款
        if($request->input('agree')){
            //todo            //同意退款
            $extra = $order->extra ?: [];
            unset($extra['refund_disagree_reason']);
            $order->update([
                'extra' => $extra,
            ]);
            // 调用退款逻辑
            $this->_refundOrder($order);
        }else{
            //拒绝退款
            //清空退款理由
            $extra=$order->exists?:[];
            $extra['refund_disagree_reason'] = $request->input('reason');
            // 将订单的退款状态改为未退款
            $order->update([
                'refund_status' => Order::REFUND_STATUS_PENDING,
                'extra'         => $extra,
            ]);
        }
        return $order;

    }
    //3.同意退款
    protected function _refundOrder(Order $order)
    {
        // 判断该订单的支付方式
        switch ($order->payment_method) {
            case 'wechat':
                // 微信的先留空
                // todo
                break;
            case 'alipay':
                // 用我们刚刚写的方法来生成一个退款订单号
                $refundNo = Order::getAvailableRefundNo();
                // 调用支付宝支付实例的 refund 方法
                $ret = app('alipay')->refund([
                    'out_trade_no' => $order->no, // 之前的订单流水号
                    'refund_amount' => $order->total_amount, // 退款金额，单位元
                    'out_request_no' => $refundNo, // 退款订单号
                ]);
                // 根据支付宝的文档，如果返回值里有 sub_code 字段说明退款失败
                if ($ret->sub_code) {
                    // 将退款失败的保存存入 extra 字段
                    $extra = $order->extra;
                    $extra['refund_failed_code'] = $ret->sub_code;
                    // 将订单的退款状态标记为退款失败
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_FAILED,
                        'extra' => $extra,
                    ]);
                } else {
                    // 将订单的退款状态标记为退款成功并保存退款订单号
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_SUCCESS,
                    ]);
                }
                break;
            default:
                // 原则上不可能出现，这个只是为了代码健壮性
                throw new InternalException('未知订单支付方式：'.$order->payment_method);
                break;
        }
    }

}
