<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\ProductSku;
use App\Services\CartService;
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
        $addresses = $request->user()->addresses()->orderBy('last_used_at', 'desc')->get();
        return view('cart.index', ['cartItems' => $cartItems, 'addresses' => $addresses]);
    }
    //移除购物车
    public function remove(ProductSku $sku,Request $request){
    	$request->user()->cartItems()->where('product_sku_id',$sku_id)->delete();
    	return [];
    }


}

//改编版购物车.(优化 )
class CartController2 extends Controller{

    protected $cartServer;
    //自动解析功能注入 CartService 类
    public function __construct(CartService $cartService)
    {
        $this->cartServer=$cartService;

    }

    public function index(Request $request){
        $cartItems=$this->cartServer->get();
        $addresses=$request->user()->address()->orderBy('last_used_at','desc')->get();
        return view('cart.index',['cartItems'=>$cartItems,'addresses'=>$addresses]);
    }
    public function add(AddCartRequest $request){
        $$this->cartServer->add([$request->input('sku_id'),$request->input('amount')]);
        return [];
    }
    public function remove(ProductSku $sku,Request $request){
        $this->cartServer->remove($sku->id);
        return [];
    }
}