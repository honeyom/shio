<?php
/**
 * CloseOrder.php
 * @Author:代先华
 * @PROJECT_NAME:shop
 * @PRODUCT_NAME:PhpStorm
 * @Last Modified by:Administrator
 * @Last Modified time:2019-11-24  10:00
 * @MONTH_NAME_FULL:十一月
 */

namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CloseOrder implements ShouldQueue
{
    use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;

    protected $order;
    public function __construct(Order $order,$delay)
    {
        $this->order=$order;
        $this->delay=$delay;
    }
    public  function handle(){
        //对应的订单是否已经被支付,
        //如果已经支付则不需要关闭订单,直接退出
        if($this->order->paid_at){
            return;
        }
        //通过事务,执行sql
        DB::transaction(function (){
            $this->order->update(['closed'=>true]);
            foreach ($this->order->items() as  $item){
                $item->pruductSku->addStock($item->amount);
            }
            if ($this->order->couponCode) {
                $this->order->couponCode->changeUsed(false);
            }
        });

    }
}
// .env设置为redis,
//然后安装composer require predis/predis
//启动队列处理器php artisan queue:work