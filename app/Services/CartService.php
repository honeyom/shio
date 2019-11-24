<?php
/**
 * CartService.php
 * @Author:代先华
 * @PROJECT_NAME:shop
 * @PRODUCT_NAME:PhpStorm
 * @Last Modified by:Administrator
 * @Last Modified time:2019-11-24  10:29
 * @MONTH_NAME_FULL:十一月
 * 封装业务逻辑
 */

namespace App\Services;
use App\Models\CartItem;
use Auth;

class CartService
{
    public function get(){
        return Auth::user()->CarItems()->with(['productSku.product'])->get();
    }
    public  function add($skuId,$amount){
        $user=Auth::user();
        // 从数据库中查询该商品是否已经在购物车中
        if($item=$user->cartItems()->where(['product_sku_id'],$skuId)->first){
            // 如果存在则直接叠加商品数量
            $item->update([
                'amount'=>$item->amount+$amount,
            ]);
        }else{
            //否则新增一条新的额购物车记录
            $item=new CartItem(['amount'=>$amount]);
            $item->user()->associate($user);
            $item->productSkus()->associate($skuId);
            $item->save();
        }
        return $item;

    }
    public function remove($skuIds){
        //可以单个id,也可以传数组
        if(!is_array($skuIds)){
            $skuIds=[$skuIds];
        }
        Auth::user()->CartItems()->whereIn('product_sku_id',$skuIds)->delete();

    }

}