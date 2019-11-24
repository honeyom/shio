<?php
/**
 * CouponCodeUnavailableException.php
 * @Author:代先华
 * @PROJECT_NAME:shop
 * @PRODUCT_NAME:PhpStorm
 * @Last Modified by:Administrator
 * @Last Modified time:2019-11-24  11:50
 * @MONTH_NAME_FULL:十一月
 */

namespace App\Exceptions;

use Illuminate\Http\Request;
use Exception;
class CouponCodeUnavailableException extends Exception
{
    public function __construct($message, int $code = 403)
    {
        parent::__construct($message, $code);
    }

    // 当这个异常被触发时，会调用 render 方法来输出给用户
    public function render(Request $request)
    {
        // 如果用户通过 Api 请求，则返回 JSON 格式的错误信息
        if ($request->expectsJson()) {
            return response()->json(['msg' => $this->message], $this->code);
        }
        // 否则返回上一页并带上错误信息
        return redirect()->back()->withErrors(['coupon_code' => $this->message]);
    }
}