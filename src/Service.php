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
namespace think\addons;

use think\Service as BaseService;
use think\app\MultiApp;

class Service extends BaseService
{
    public function boot()
    {
        $this->app->event->listen('HttpRun', function () {
            //判断多应用
            $type = class_exists(MultiApp::class) ? 'app' : 'global';
            $this->app->middleware->add(Addons::class, $type);
        });
    }
}
