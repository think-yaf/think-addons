<?php
// +----------------------------------------------------------------------
// | ThinkYaf [ think-addons 始于 thinkphp6 高级用法 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2021-now http://thinkyaf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: thinkyaf <thinkyaf@qq.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace think\addons\facade;

use think\addons\Facade;

class View extends Facade
{
    protected static function getFacadeClass()
    {
        return 'think\\addons\\view';
    }
}
