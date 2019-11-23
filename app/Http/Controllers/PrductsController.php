<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class PrductsController extends Controller
{
    //
    public function index(Request $request){
        $products=Product::query()->where('on_sale',true)->paginate();

        $builder=Product::query()->where('on_sale',true);
        if($search=$request->input('search','')){
            $like='%'.$search.'%';
            //模糊搜索商品标题,.商品详情,SKU辩题,Sku描述
            $builder->where(function ($query) use ($like){
                $query->where('title','like',$like)
                    ->orWhere('description','like',$like)
                    ->orWhereHas('skus',function ($query) use ($like){
                        $query->where('titile','like',$like)
                            ->orWhere('description','like',$like);
                    });
            });
        }
        //是否有提交order参数,如果有就复制给$order变量
        //order参数用来控制商品的排序规则
        if($order=$request->input('order','')){
//            是否是_asc或者_desc结尾
            if(preg_match('/^(.+)_(asc|desc)$/',$order,$m)){
                if(in_array($m[1],['price','sold_count','rating'])){
                    $builder->orderBy($m[1],$m[2]);
                }
            }
        }
        $products=$builder->paginate(16);
        dd($products);
        //        return view('products.index',['products'=>$products]);
//        保留用户的搜索内容，把用户的搜索和排序参数传到模板文件
//        return view('products.index',[
//            'products'=>$products,
//            'filters'=>[
//                'search'=>$search,
//                'order'=>$order
//            ]
//        ]);
//        解决页面下方的翻页组件进入下一页之后这两个框又复原了，原本在地址栏里的搜索和排序参数也丢失了:$filters变量传给分页组件
//        <div class="float-right">{{ $products->appends($filters)->render() }}</div>
//        每次选择排序方式之后都需要点搜索按钮才能生效，这个体验不是很好，我们可以通过监听下拉框的 change 事件来触发表单自动提交
//        var filters={!!json_encode($filter)!!};
//        $(document).ready(function(){
//            $('search-form select[name=order]').on ('change',function(){
//                $('.search-form').submit();
//            });
//        });
    }
}
