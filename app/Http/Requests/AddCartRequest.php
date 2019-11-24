<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sku_id'=>[
            'required',function($attribute,$vlalue,$fail){
                if(!$sku=ProductSku::find($value)){
                    return $fail('商品不存在');
                }
                if(!$sku->product->on_salee){
                    return $fail('商品未上架');
                }
                if(0===$sku->stock){
                    return $fail('已售完');
                }
                if($this->input(amount)>0 && $sku->stock<$this->input($amount)){
                    return $fail('商品库存不足');
                }

            },
            ],
            'amount'=>['required','interger','min:1'],
        ];
    }
    public funcion attribute(){
        return ['amount'=>'商品数量'];
    }
    public funcion messages(){
        return ['sku_id.required'=>'请选择商品'];
    }
}
