<?php

namespace think\addons;

use think\Service as BaseService;

class Service extends BaseService
{
    public function boot()
    {
        $this->app->event->listen('HttpRun', function () {
            //判断多应用
            if ($this->app->config->get('app.multi_app')) {
                $this->app->middleware->unshift(Addons::class, 'app');
            } else {
                $this->app->middleware->unshift(Addons::class);
            }
        });
    }
}
