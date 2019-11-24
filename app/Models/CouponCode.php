<?php
/**
 * CouponCode.php
 * @Author:代先华
 * @PROJECT_NAME:shop
 * @PRODUCT_NAME:PhpStorm
 * @Last Modified by:Administrator
 * @Last Modified time:2019-11-24  11:46
 * @MONTH_NAME_FULL:十一月
 */

namespace App\Models;
use App\User;
use Illuminate\Support\Str;

class CouponCode
{
    public static function findAvailableCode($length = 16)
    {
        do {
            // 生成一个指定长度的随机字符串，并转成大写
            $code = strtoupper(Str::random($length));
            // 如果生成的码已存在就继续循环
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }
    public function checkAvailable(User $user,$orderAmount = null)
    {
        $used = Order::where('user_id', $user->id)
            ->where('coupon_code_id', $this->id)
            ->where(function($query) {
                $query->where(function($query) {
                    $query->whereNull('paid_at')
                        ->where('closed', false);
                })->orWhere(function($query) {
                    $query->whereNotNull('paid_at')
                        ->where('refund_status', '!=', Order::REFUND_STATUS_SUCCESS);
                });
            })
            ->exists();
        if ($used) {
            throw new CouponCodeUnavailableException('你已经使用过这张优惠券了');
        }
        if (!$this->enabled) {
            throw new CouponCodeUnavailableException('优惠券不存在');
        }

        if ($this->total - $this->used <= 0) {
            throw new CouponCodeUnavailableException('该优惠券已被兑完');
        }

        if ($this->not_before && $this->not_before->gt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券现在还不能使用');
        }

        if ($this->not_after && $this->not_after->lt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券已过期');
        }

        if (!is_null($orderAmount) && $orderAmount < $this->min_amount) {
            throw new CouponCodeUnavailableException('订单金额不满足该优惠券最低金额');
        }
    }
    //计算优惠后金额
    public function getAdjustedPrice($orderAmount)
    {
        // 固定金额
        if ($this->type === self::TYPE_FIXED) {
            // 为了保证系统健壮性，我们需要订单金额最少为 0.01 元
            return max(0.01, $orderAmount - $this->value);
        }

        return number_format($orderAmount * (100 - $this->value) / 100, 2, '.', '');
    }
//优惠券的用量,库存
    public function changeUsed($increase = true)
    {
        // 传入 true 代表新增用量，否则是减少用量
        if ($increase) {
            // 与检查 SKU 库存类似，这里需要检查当前用量是否已经超过总量
            return $this->newQuery()->where('id', $this->id)->where('used', '<', $this->total)->increment('used');
        } else {
            return $this->decrement('used');
        }
    }
}