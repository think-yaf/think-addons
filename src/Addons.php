<?php

declare(strict_types=1);

namespace think\addons;

use Closure;
use think\App;
use think\exception\HttpException;
use think\Request;
use think\Response;
use think\addons\Controller;
use think\helper\Str;

/**
 * 多应用模式支持
 */
class Addons
{

    /** @var App */
    protected $app;

    /**
     * 应用路径
     * @var string
     */
    protected $path;

    public function __construct(App $app)
    {
        $this->app  = $app;
        $this->http = $this->app->http;
    }

    /**
     * 多应用解析
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */

    public function handle($request, Closure $next)
    {
        pre( $this->app->middleware->all());
        return $next($request);
    }








    public function handles($request, Closure $next)
    {
        $namespace = $this->parseNamespace($request->controller());
        $controller = $this->parseController($request->controller());
        $this->app->config->set(['app_namespace' => $namespace], 'app');
        $this->app->request->setController($controller);
        $request->setController($controller);
        echo ' 当前控制器：'.$namespace . '\\' . $this->app->config->get('route.controller_layer') . '\\' . $controller;
        return $next($request);
    }

    public function parseController(string $name): string
    {
        $name  = str_replace(['/', '.'], '\\', strpos($name, '.') ? $name : Str::lower($name));
        $array = explode('\\', $name);
        $class = Str::studly(array_pop($array));
        array_shift($array);
        $path  = $array ? implode('\\', $array) . '\\' : '';
        return  $path . $class;
    }

    public function parseNamespace(string $name): string
    {
        $name  = str_replace(['/', '.'], '\\', strpos($name, '.') ? $name : Str::lower($name));
        $array = explode('\\', $name);
        return 'app\\addons\\' . $array[0] . ($this->http->getName() ? '\\' . $this->http->getName() : '');
    }
}
