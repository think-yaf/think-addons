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

namespace think\addons;

//use  think\View as BaseView; // extends BaseView
use think\facade\View as BaseView;
use think\App;

class View
{
    protected $app;
    protected $request;
    protected $config;
    // 数据初始化
    protected $data = [
        'code' => 0,
        'msg' => '成功',
        'data' => []
    ];

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $app->request;
        $this->config = $app->config;
        $this->data['title'] = $this->app->config->get('app.app_name', '企业管理系统');
    }

    // 设置模板布局
    protected function layout($name = false)
    {
        if ($name && $name != 'false') {
            $layout_path = $this->app->config->get('view.view_path') . $name . '.html';
            if (file_exists($layout_path)) {
                return  $this->app->config->set(['layout_name' => $name], 'view');
            }
            return false;
        }
        $this->app->config->set(['layout_on' => false], 'view');
        return $this;
    }
    // 设置数据赋值
    public function assign($name, $value = null)
    {
        if (is_array($name)) {
            $this->data = array_merge($this->data, $name);
        } else {
            $this->data[$name] = $value;
        }
        return $this;
    }
    //显示模板
    public function fetch($template = '', array $vars = [])
    {
        $this->data['code'] = ($template === false || strpos((isset($this->data['msg']) ? $this->data['msg'] : ''), '成功') === false) ? 1 : 0;
        // 判断是否为接口请求
        if ($this->request->isAjax() || strtolower($this->request->ext()) == 'json' || is_bool($template)) {
            return json($this->data);
        }
        //查找插件模板
        if (!BaseView::engine('php')->exists($template)) {
            //尝试使用默认的目模板录
            $view_path = $this->config->get('view.view_path_app');
            if ($this->config->get('addons.addon_name')) {
                $view_path = $view_path . $this->config->get('addons.addon_name') . '\\';
            }
            $this->config->set(['view_path' => $view_path], 'view');
        }
        return BaseView::fetch($template, $vars);
    }
}
