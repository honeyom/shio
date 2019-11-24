<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    //
    public function add(AddCartRequest $request){
    	$user=$request->user();
    	$skuId=$request->input('sku_id');
    	$amount=$request->input('amount');
    	//从数据库中查询改商品书否已经在购物车中
    	if($cart=$user->cartItems()->where('product_sku_id',$skuId)->first()){
    		//如果存在,则直接追加商品数量
    		$cart->update(['amount'=>$cart->amount+$amount]);
    	}
    	else{
    		//否则就创建一个新的购物车记录
    		$cart=new CartItem(['amount'=>$amount]);
    		$cart->user()->associate($user);
    		$cart->productSku()->associate($skuId);
    		$cart->save();
    	}
    	return [];
    }
    //查看购物车
    public function index(Request $request){
    	$cartItems=$request->user()->cartItems()->with(['productSku.product'])->get();
    	return view('cart.index',['cartItems'=>$cartItems]);
    }
    //移除购物车
    public function remove(ProductSku $sku,Request $request){
    	$request->user()->cartItems()->where('product_sku_id',$sku_id)->delete();
    	return [];
    }
    
}