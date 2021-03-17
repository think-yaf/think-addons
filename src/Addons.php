<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace think\addons;

use Closure;
use think\App;
use think\exception\HttpException;
use think\Request;
use think\Response;

/**
 * 多插件模式支持
 */
class Addons
{

    /** @var App */
    protected $app;

    /**
     * 插件名称
     * @var string
     */
    protected $name;

    /**
     * 插件名称
     * @var string
     */
    protected $appName;

    /**
     * 插件路径
     * @var string
     */
    protected $path;

    public function __construct(App $app)
    {
        $this->app  = $app;
        $this->name = $this->app->http->getName();
        $this->path = $this->app->http->getPath();
    }

    /**
     * 多插件解析
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        if ($this->app->config->get('addons.addon_on')) {
            $this->parseAddons();
        }
        return $next($request);
    }

    /**
     * 获取路由目录
     * @access protected
     * @return string
     */
    protected function getRoutePath(): string
    {
        return $this->app->getAppPath() . 'route' . DIRECTORY_SEPARATOR;
    }

    /**
     * 解析多插件
     * @return bool
     */
    protected function parseAddons(): bool
    {

        $path = $addons = $this->app->request->pathinfo();
        if (strpos($path, '.')) {
            $addons = strstr($path, ".", true);
        }
        if (strpos($addons, '/')) {
            $addons = strstr($path, "/", true);
        }
        if ($this->setAddons($addons)) {
            $path = $this->app->request->pathinfo();
            $path =  strpos($path, '/') ? ltrim(strstr($path, '/'), '/') : 'index';
            $this->app->request->setPathinfo($path);
        }
        return true;
    }


    /**
     * 设置插件
     * @param string $appName
     */
    protected function setAddons(string $appName): bool
    {
        $addons_namespace = $this->app->config->get('app.app_namespace') ?: 'app\\' . 'addons\\'  . $appName;
        $addonsPath = $this->app->getRootPath() . $addons_namespace . DIRECTORY_SEPARATOR;
        $namespace =  $addons_namespace . ($this->name ? '\\' . $this->name : '');
        $appPath = $this->app->getRootPath() . $namespace . DIRECTORY_SEPARATOR;
        if (is_dir($appPath)) {
            $config_file = $addonsPath . 'config' . $this->app->getConfigExt();
            if (is_file($config_file)) {
                $this->app->config->load($config_file, 'addons');
            }
            if ($this->app->config->get('addons.addon_on')) {
                // 设置路径
                $this->app->setAppPath($appPath);
                // 设置插件命名空间
                $this->app->setNamespace($namespace);
                //设置运行目录
                $this->app->setRuntimePath($this->app->getRuntimePath() . $appName . DIRECTORY_SEPARATOR);
                // 设置路由
                $this->app->http->setRoutePath($this->getRoutePath());
                //加载插件文件
                $this->loadAddons($appPath);
                // 返回插件状态
                return true;
            }
            if ($this->app->config->get('addons.show_error_msg')) {
                $message = $this->app->config->get('addons.error_message', 'app not exists:' . $appName);
                throw new HttpException(404, $message);
            }
        }
        return false;
    }

    /**
     * 加载插件文件
     * @param string $appName 插件名
     * @return void
     */
    protected function loadAddons(string $appPath): void
    {
        if (is_file($appPath . 'common.php')) {
            include_once $appPath . 'common.php';
        }

        $files = [];

        $files = array_merge($files, glob($appPath . 'config' . DIRECTORY_SEPARATOR . '*' . $this->app->getConfigExt()));

        foreach ($files as $file) {
            $this->app->config->load($file, pathinfo($file, PATHINFO_FILENAME));
        }

        if (is_file($appPath . 'event.php')) {
            $this->app->loadEvent(include $appPath . 'event.php');
        }

        if (is_file($appPath . 'middleware.php')) {
            $this->app->middleware->import(include $appPath . 'middleware.php', 'app');
        }

        if (is_file($appPath . 'provider.php')) {
            $this->app->bind(include $appPath . 'provider.php');
        }

        // 加载插件默认语言包
        $this->app->loadLangPack($this->app->lang->defaultLangSet());
    }
}
