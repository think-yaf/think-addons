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
use think\helper\Str;

class View extends BaseView
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
        $this->path = $this->config->get('addons.addon_path', $this->app->getAppPath()) . $this->config->get('view.view_dir_name', 'view') . '\\';
    }

    // 设置模板布局
    protected function layout($name = false)
    {
        if ($name && $name != 'false') {
            $name = str_replace(['/', ':'], '\\', $name);
            if (file_exists($layout_path = $this->getPath() . $name . '.' . $this->app->config->get('view.view_suffix'))) {
                $this->app->config->set(['layout_name' => $layout_path], 'view');
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
        $template = $this->parseTemplate($template);
        return BaseView::fetch($template, $vars);
    }
    public function getPath()
    {
        return $this->path;
    }
    // 模板自动解析
    private function parseTemplate(string $template): string
    {
        $request = $this->app->request;
        $path = $this->path;
        $depr = $this->config->get('view.view_depr');
        if (0 !== strpos($template, '/')) {
            $template   = str_replace(['/', ':'], $depr, $template);
            $controller = $request->controller();
            if (strpos($controller, '.')) {
                $pos        = strrpos($controller, '.');
                $controller = substr($controller, 0, $pos) . '.' . Str::snake(substr($controller, $pos + 1));
            } else {
                $controller = Str::snake($controller);
            }
            if ($controller) {
                if ('' == $template) {
                    // 如果模板文件名为空 按照默认规则定位
                    if (2 == $this->config->get('view.auto_rule')) {
                        $template = $request->action(true);
                    } elseif (3 == $this->config->get('view.auto_rule')) {
                        $template = $request->action();
                    } else {
                        $template = Str::snake($request->action());
                    }
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                } elseif (false === strpos($template, $depr)) {
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                }
            }
        } else {
            $template = str_replace(['/', ':'], $depr, substr($template, 1));
        }
        return $path . ltrim($template, '/') . '.' . ltrim($this->config->get('view.view_suffix'), '.');
    }
}
