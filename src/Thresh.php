<?php

namespace TELstatic\Thresh;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Route;

/**
 * 权限服务类
 * Class PermissionService
 * @package App\Services
 */
class Thresh
{
    protected $routes;
    protected $actions = [];

    protected $permissions = [];

    // 控制器黑名单
    protected $blackControllerList = [
        '\Illuminate\Routing\ViewController',
    ];

    // 验证规则置顶
    protected $ruleWhiteList = [
        'id',
    ];

    // 命名空间白名单
    protected $whiteNamespaceList = [];

    // 默认选中权限
    protected $defaultPermissions = [];

    // 禁用权限
    protected $blackPermissions = [];

    // 原始权限
    protected $originPermissions = [];

    public function load(
        $blackControllerList,
        $whiteNamespaceList
    ) {
        $this->blackControllerList = array_merge($this->blackControllerList,
            is_string($blackControllerList) ? [$blackControllerList] : $blackControllerList);
        $this->whiteNamespaceList = array_merge($this->whiteNamespaceList,
            is_string($whiteNamespaceList) ? [$whiteNamespaceList] : $whiteNamespaceList);

        $this->loadRoutes();
        $this->loadActions();

        $this->loadPermissions();
    }

    /**
     * 加载路由
     * Date: 2020/4/17
     */
    protected function loadRoutes()
    {
        $this->routes = Route::getRoutes();
    }

    /**
     * 加载控制器方法
     * Date: 2020/4/17
     */
    protected function loadActions()
    {
        foreach ($this->routes as $route) {
            $action = $route->getAction();

            //去除 闭包路由
            if (isset($action['controller'])) {
                list ($controller) = explode('@', $action['controller']);

                //去除 黑名单
                if (in_array($controller, $this->blackControllerList)) {
                    continue;
                }
            } else {
                continue;
            }

            if (in_array($action['namespace'], $this->whiteNamespaceList)) {
                if (count($route->methods) > 1) {
                    foreach ($route->methods as $method) {
                        if ($method == 'HEAD') {
                            continue;
                        }

                        $action['http_method'] = $method;

                        $action['uri'] = $this->getUri($route->uri);

                        $this->actions[] = $action;
                    }
                } else {
                    $action['http_method'] = current($route->methods);
                    $action['uri'] = $this->getUri($route->uri);

                    $this->actions[] = $action;
                }
            }
        }
    }

    /**
     * 生成权限
     * Date: 2020/4/17
     * @throws \ReflectionException
     */
    protected function loadPermissions()
    {
        //获取 注释
        foreach ($this->actions as $action) {
            list ($controllerName, $methodName) = explode('@', $action['controller']);

            $controller = new \ReflectionClass($controllerName);

            $this->getControllerDoc($controllerName, $controller);

            $this->getMethodDoc($controllerName, $methodName, $action, $controller);
        }
    }

    /**
     * 获取权限
     * Date: 2020/4/17
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * 获取默认权限
     */
    public function getOriginPermissions()
    {
        return $this->originPermissions;
    }

    /**
     * 获取默认权限
     * @desc 获取默认权限
     * @author TELstatic
     * Date: 2020/8/19/0019
     */
    public function getDefaultPermissions()
    {
        return $this->defaultPermissions;
    }

    /**
     * 获取禁用权限
     * @desc 获取禁用权限
     * @param array $blackPermissions
     * @return array
     * @author TELstatic
     * Date: 2021/4/8/0008
     */
    public function getBlackPermissions($blackPermissions = [])
    {
        if (!empty($blackPermissions)) {
            $this->blackPermissions = array_merge($this->blackPermissions, $blackPermissions);
        }

        return $this->blackPermissions;
    }

    /**
     * 导出 Markdown 文档
     * @desc 导出 Markdown 文档
     * @param $name
     * @param null $middleware
     * @param array $headers
     * @author TELstatic
     * Date: 2021/4/21/0021
     */
    public function exportMarkdown($name, $middleware = null, $headers = [])
    {
        $filename = $name.'.md';

        ob_start();

        echo '# '.$name;

        echo PHP_EOL;
        echo PHP_EOL;

        echo '> 导出时间: '.date('Y-m-d H:i:s');

        echo PHP_EOL;
        echo PHP_EOL;

        echo "## 目录";
        echo PHP_EOL;
        echo PHP_EOL;

        foreach ($this->getPermissions() as $permission) {
            echo PHP_EOL;
            echo '* '.'['.$permission['title'].'](#'.$permission['title'].')';
            echo PHP_EOL;
            foreach ($permission['methods'] as $method) {
                echo '  * '.'['.$method['title'].'](#'.$method['title'].')';
                echo PHP_EOL;
            }
        }

        foreach ($this->getPermissions() as $permission) {
            echo PHP_EOL;
            echo '<a id="'.$permission['title'].'"></a>';
            echo PHP_EOL;
            echo '### '.$permission['title'];

            foreach ($permission['methods'] as $method) {
                echo PHP_EOL;
                echo '<a id="'.$method['title'].'"></a>';
                echo PHP_EOL;
                echo '#### '.$method['title'];
                echo PHP_EOL;
                echo '> '.implode('', $method['desc']);
                echo PHP_EOL;

                echo '* 地址 '.'`'.'{url}/'.$method['uri'].'`';
                echo PHP_EOL;

                echo '* 请求方式 '.'`'.$method['method'].'`';
                echo PHP_EOL;

                if ($middleware) {
                    $header = in_array($middleware, $method['middleware']) ? $headers : [];
                } else {
                    $header = [];
                }

                if (!empty($header)) {
                    echo PHP_EOL;
                    echo PHP_EOL;
                    echo '* 请求头';
                    echo PHP_EOL;
                    echo PHP_EOL;

                    echo '|   名称    |  类型  | 必填 | 默认值 |  备注  |';
                    echo PHP_EOL;

                    echo '| :-------: | :----: | :--: | :----: | :----: |';
                    echo PHP_EOL;

                    foreach ($header as $item) {
                        echo "|   {$item['key']}    |  string  | yes | {$item['value']} |    |";
                        echo PHP_EOL;
                    }
                }

                if (!empty($method['rules'])) {
                    echo PHP_EOL;
                    echo PHP_EOL;
                    echo '* 验证规则';
                    echo PHP_EOL;
                    echo PHP_EOL;

                    echo '|   名称    |  规则 | ';
                    echo PHP_EOL;
                    echo '| :-------: |  :----: |';
                    echo PHP_EOL;

                    foreach ($method['rules'] as $rule) {
                        $name = str_replace('*', '\*', $rule['name']);
                        $value = str_replace(['|', '"', '[', ']', ',{}'], [','], json_encode($rule['value']));

                        echo "|   {$name}    | {$value} |";
                        echo PHP_EOL;
                    }
                }

                echo PHP_EOL;
                echo PHP_EOL;
                echo '* 请求参数';
                echo PHP_EOL;
                echo PHP_EOL;

                if (!empty($method['params'])) {
                    echo '|   名称    |  类型  | 必填 | 默认值 |  备注  |';
                    echo PHP_EOL;

                    echo '| :-------: | :----: | :--: | :----: | :----: |';
                    echo PHP_EOL;

                    foreach ($method['params'] as $param) {
                        echo "|   {$param['name']}    |  {$param['type']}  | {$param['require']} | {$param['default']} |  {$param['comment']}  |";
                        echo PHP_EOL;
                    }
                }

                $path = explode('/', $method['uri']);

                $variables = array_filter($path, function ($item) {
                    return strpos($item, '{') === 0;
                });

                if (!empty($variables)) {
                    if (empty($method['params'])) {
                        echo '|   名称    |  类型  | 必填 | 默认值 |  备注  |';
                        echo PHP_EOL;

                        echo '| :-------: | :----: | :--: | :----: | :----: |';
                        echo PHP_EOL;
                    }

                    foreach ($variables as $variable) {
                        $variable = str_replace(['{', '}'], '', $variable);

                        $type = in_array($variable, $this->ruleWhiteList) ? 'int' : 'string';

                        echo "|   {$variable}    |  {$type}  | yes | null |  {$variable}  |";
                        echo PHP_EOL;
                    }
                }

                echo PHP_EOL;
                echo PHP_EOL;
            }

            echo PHP_EOL;
        }

        header('Content-type:application/octet-stream');
        header('Accept-Ranges:bytes');
        header("Content-Disposition:attachment;filename={$filename};");
    }

    /**
     * 导出 Postman 配置
     * @desc 导出 Postman 配置
     * @param string $name 文档名称
     * @param null $middleware
     * @param array $headers
     * @return array
     * @author TELstatic
     * Date: 2021/4/21/0021
     */
    public function exportPostman($name, $middleware = null, $headers = [])
    {
        $data = [
            'info' => [
                '_postman_id' => '',
                'name'        => $name,
                'schema'      => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => [],
        ];

        $i = 0;

        foreach ($this->getPermissions() as $originPermission) {
            $data['item'][$i] = [
                'name' => $originPermission['title'],
            ];

            foreach ($originPermission['methods'] as $method) {
                $method['uri'] = str_replace(['{', '}'], [':', ''], $method['uri']);

                $path = explode('/', $method['uri']);

                $url = [
                    'raw'  => '{{url}}'.'/'.$method['uri'],
                    'host' => '{{url}}',
                    'path' => $path,
                ];

                $variables = array_filter($path, function ($item) {
                    return str_starts_with($item, ':');
                });

                if (!empty($variables)) {
                    foreach ($variables as $variable) {
                        $url['variable'][] = [
                            'key'   => trim($variable, ':'),
                            'value' => '',
                        ];
                    }
                }

                if ('GET' === $method['method']) {
                    foreach ($method['params'] as $param) {
                        $query = [
                            'key'         => $param['name'],
                            'value'       => $param['default'] === 'null' ? '' : $param['default'],
                            'description' => $param['comment'],
                            'type'        => 'text',
                        ];

                        if ($param['type'] === 'file') {
                            $query['src'] = '';
                            $query['type'] = 'file';

                            unset($query['value']);
                        }

                        $url['query'][] = $query;
                    }
                } else {
                    $body = [
                        'mode'     => 'formdata',
                        'formdata' => [],
                    ];

                    foreach ($method['params'] as $param) {
                        $form = [
                            'key'         => $param['name'],
                            'description' => $param['comment'],
                            'type'        => 'text',
                            'value'       => $param['default'] === 'null' ? '' : $param['default'],
                        ];

                        if ($param['type'] === 'file') {
                            $form['src'] = '';
                            $form['type'] = 'file';

                            unset($form['value']);
                        }

                        $body['formdata'][] = $form;
                    }
                }

                if ($middleware) {
                    $header = in_array($middleware, $method['middleware']) ? $headers : [];
                } else {
                    $header = [];
                }

                $request = [
                    'description' => implode('', $method['desc']),
                    'method'      => $method['method'],
                    'header'      => $header,
                    'url'         => $url,
                ];

                if (isset($body)) {
                    $request['body'] = $body;

                    unset($body);
                }

                $data['item'][$i]['item'][] = [
                    'name'     => $method['title'],
                    'request'  => $request,
                    'response' => [],
                ];
            }

            $i++;
        }

        return $data;
    }

    /**
     * 导出 Swagger 配置文件
     * @desc 导出 Swagger 配置文件
     * @param $name
     * @param $middleware
     * @param $headers
     * @author TELstatic
     * Date: 2021/4/22/0022
     */
    public function exportSwagger($name, $middleware, $headers)
    {
        $data = [
            'swagger'  => '2.0',
            'info'     => [
                'title'       => $name,
                'description' => '导出时间: '.date('Y-m-d H:i:s'),
            ],
            'host'     => $_SERVER['HTTP_HOST'],
            'basePath' => '/',
            'schemes'  => $_SERVER['SERVER_PORT'] == 80 ? ['http'] : ['http', 'https'],
        ];

        foreach ($this->getPermissions() as $permission) {
            foreach ($permission['methods'] as $method) {
                $schema = [
                    'summary'     => $method['title'],
                    'description' => implode($method['desc'], ''),
                    'parameters'  => [],
                ];

                if ($middleware) {
                    $header = in_array($middleware, $method['middleware']) ? $headers : [];

                    foreach ($header as $item) {
                        $schema['parameters'][] = [
                            'in'          => 'header',
                            'name'        => $item['key'],
                            'description' => $item['key'],
                            'required'    => true,
                            'value'       => $item['value'],
                        ];
                    }
                }

                if ($method['method'] !== 'GET') {
                    $schema['consumes'] = [
                        'multipart/form-data',
                    ];
                }

                $path = explode('/', $method['uri']);

                $variables = array_filter($path, function ($item) {
                    return strpos($item, '{') === 0;
                });

                foreach ($variables as $variable) {
                    $schema['parameters'][] = [
                        'in'          => 'path',
                        'name'        => $variable,
                        'description' => $variable,
                        'required'    => true,
                    ];
                }

                foreach ($method['params'] as $param) {
                    $parameter = [
                        'in'          => $method['method'] === 'GET' ? 'query' : 'body',
                        'name'        => $param['name'],
                        'description' => $param['comment'],
                        'required'    => $param['require'] == 'yes',
                        'type'        => $param['type'],
                    ];

                    if (isset($param['min'])) {
                        $parameter['minimum'] = $param['min'];
                    }

                    if (isset($param['max'])) {
                        $parameter['maximum'] = $param['max'];
                    }

                    $schema['parameters'][] = $parameter;
                }

                $data['paths'][$method['uri']][strtolower($method['method'])] = $schema;
            }
        }

        return $data;
    }

    /**
     * 获取控制器注释
     * @param $controllerName
     * @param \ReflectionClass $controller
     * Date: 2020/4/17
     */
    protected function getControllerDoc($controllerName, \ReflectionClass $controller)
    {
        if (!isset($this->permissions[$controllerName])) {
            $controllerDoc = $controller->getDocComment();

            $controllerDocs = [
                'name'          => $this->formatName($controllerName),
                'title'         => $this->formatName($controllerName),
                'desc'          => [],
                'check'         => false,
                'indeterminate' => false,
            ];

            if ($controllerDoc) {
                $haystack = $this->formatDoc($controllerDoc);

                if ($haystack) {
                    $controllerDocs['title'] = $this->formatTitle($haystack) ?? $controllerName;
                    $controllerDocs['desc'] = $this->formatDesc($haystack);
                }
            }

            $this->permissions[$controllerName] = $controllerDocs;
        }
    }

    /**
     * 获取键值
     * @desc 获取键值
     * @param $methodName
     * @param $method
     * @return string
     * @author TELstatic
     * Date: 2021/4/14/0014
     */
    protected function getKey($methodName, $method)
    {
        return $method.'-'.$methodName;
    }

    /**
     * 获取路径
     * @desc 获取路径
     * @param $uri
     * @return string|string[]|null
     * @author TELstatic
     * Date: 2021/4/14/0014
     */
    protected function getUri($uri)
    {
        $explodeUri = explode('/', str_replace(['{', '}'], '', $uri));

        // 资源路由 admin/user/{user} => admin/user/{id}
        if (count($explodeUri) != count(array_unique($explodeUri))) {
            return preg_replace('/{(\S+)}/', '{id}', $uri);
        }

        return $uri;
    }

    /**
     * 生成权限
     * @desc 生成权限
     * @param $action
     * @return mixed|string
     * Date: 2021/4/14/0014
     */
    protected function getPermission($action)
    {
        return $action['http_method'].'-'.$action['uri'];
    }

    /**
     * 获取验证规则
     * @desc 获取验证规则
     * @param $parameters
     * @param $method
     * @return array
     *
     * Date: 2021/4/14/0014
     */
    protected function getMethodRules($parameters, $method)
    {
        $rules = [];

        foreach ($parameters as $parameter) {
            if ($parameterClass = $parameter->getClass()) {
                $parameterClassName = $parameterClass->getName();

                // 判断是否为 自定义 Request 类
                if (!$parameterClass->hasMethod('authorize')) {
                    continue;
                }

                $request = new $parameterClassName;

                $request->setMethod($method);

                foreach ($request->rules() as $name => $rule) {
                    array_push($rules, [
                        'name'  => $name,
                        'value' => $rule,
                    ]);
                }
            } else {
                if (in_array($parameter->getName(), $this->ruleWhiteList)) {
                    array_unshift($rules, [
                        'name'  => $parameter->getName(),
                        'value' => 'required|integer',
                    ]);
                } else {
                    array_push($rules, [
                        'name'  => $parameter->getName(),
                        'value' => 'required|max:'.Builder::$defaultStringLength,
                    ]);
                }
            }
        }

        return $rules;
    }

    /**
     * 获取方法注释
     * @param $controllerName
     * @param $methodName
     * @param $action
     * @param \ReflectionClass $controller
     * Date: 2020/4/17
     * @throws \ReflectionException
     */
    protected function getMethodDoc($controllerName, $methodName, $action, \ReflectionClass $controller)
    {
        $permission = $this->getPermission($action);

        $methodDocs = [
            'name'       => $this->formatName($methodName),
            'title'      => $this->formatName($methodName),
            'desc'       => [],
            'method'     => $action['http_method'],
            'uri'        => $action['uri'],
            'permission' => $permission,
            'middleware' => $action['middleware'] ?? [],
            '_expanded'  => true,
            'rules'      => [],
            'params'     => [],
            'returns'    => [],
        ];

        $parameters = $controller->getMethod($methodName)->getParameters();

        $methodDocs['rules'] = $this->getMethodRules($parameters, $action['http_method']);

        $method = $controller->getMethod($methodName);

        $methodDoc = $method->getDocComment();

        if ($methodDoc) {
            $haystack = $this->formatDoc($methodDoc);

            if ($haystack) {
                $methodDocs['title'] = $this->formatTitle($haystack) ?? $methodName;
                $methodDocs['desc'] = $this->formatDesc($haystack);
                $methodDocs['params'] = $this->formatParams($haystack);
                $methodDocs['returns'] = $this->formatReturns($haystack);
                $methodDocs['is_default'] = $this->formatDefault($haystack);
                $methodDocs['is_black'] = $this->formatBlack($haystack);
            }
        }

        // 默认权限
        if (isset($methodDocs['is_default']) && $methodDocs['is_default']) {
            // 非禁用权限
            if (!(isset($methodDocs['is_black']) && $methodDocs['is_black'])) {
                array_push($this->defaultPermissions, $permission);
            }
        }

        // 禁用权限
        if (isset($methodDocs['is_black']) && $methodDocs['is_black']) {
            array_push($this->blackPermissions, $permission);
        }

        array_push($this->originPermissions, $permission);

        $this->permissions[$controllerName]['methods'][$this->getKey($methodName,
            $action['http_method'])] = $methodDocs;
    }

    /**
     * 格式化注释
     * @param $doc
     * Date: 2020/4/17
     * @return bool|mixed
     */
    protected function formatDoc($doc)
    {
        //格式错误
        if (preg_match('#^/\*\*(.*)\*/#s', $doc, $comment) === false) {
            return false;
        }

        //移除 符号 *
        if (preg_match_all('#^\s*\*(.*)#m', trim($comment[1]), $lines) === false) {
            return false;
        } else {
            return $lines[1];
        }
    }

    /**
     * 格式化名称
     * @param $haystack
     * Date: 2020/4/17
     * @return mixed
     */
    protected function formatName($haystack)
    {
        $temp = explode('\\', $haystack);

        return end($temp);
    }

    /**
     * 格式化中文名称
     * @param $haystack
     * Date: 2020/4/17
     * @return string|null
     */
    protected function formatTitle($haystack)
    {
        return count($haystack) > 0 ? trim($haystack[0]) : null;
    }

    /**
     * 格式化描述
     * @param $haystack
     * Date: 2020/4/17
     * @return array
     */
    protected function formatDesc($haystack)
    {
        $reg = '/@desc.*/i';
        $desc = [];

        foreach ($haystack as $line) {
            if (preg_match($reg, trim($line), $tmp) !== false) {
                if (!empty($tmp)) {
                    $desc[] = trim(str_replace('@desc', '', $tmp[0]));
                }
            }
        }

        return $desc;
    }

    /**
     * 格式化参数
     * @param $haystack
     * Date: 2020/4/17
     * @return array
     */
    protected function formatParams($haystack)
    {
        $reg = '/@params.*/i';
        $params = [];

        foreach ($haystack as $k => $line) {
            // dd($haystack);
            if (preg_match($reg, trim($line), $tmp) !== false) {
                if (!empty($tmp)) {
                    $temp = explode(' ', trim(str_replace('@params', '', $tmp[0])));

                    if (count($temp) == 7) {
                        $params[$k]['name'] = $temp[0];
                        $params[$k]['type'] = $temp[1];
                        $params[$k]['require'] = $temp[2];
                        $params[$k]['default'] = $temp[3];
                        $params[$k]['min'] = $temp[4];
                        $params[$k]['max'] = $temp[5];
                        $params[$k]['comment'] = $temp[6];
                    }

                    if (count($temp) == 5) {
                        $params[$k]['name'] = $temp[0];
                        $params[$k]['type'] = $temp[1];
                        $params[$k]['require'] = $temp[2];
                        $params[$k]['default'] = $temp[3];
                        $params[$k]['comment'] = $temp[4];
                    }
                }
            }
        }

        rsort($params);

        return $params;
    }

    /**
     * 格式化返回
     * @param $haystack
     * Date: 2020/4/17
     * @return array
     */
    protected function formatReturns($haystack)
    {
        $reg = '/@return.*/i';
        $returns = [];

        foreach ($haystack as $i => $line) {
            if (preg_match($reg, trim($line), $tmp) !== false) {
                if (!empty($tmp)) {
                    $temp = explode(' ', trim(str_replace('@return', '', $tmp[0])));

                    $returns[$i][] = json_encode($temp);
                }
            }
        }

        sort($returns);

        return $returns;
    }

    /**
     * 格式化默认权限
     * @desc 格式化默认权限
     * @param $haystack
     * @return bool
     * @author TELstatic
     * Date: 2020/8/19/0019
     */
    protected function formatDefault($haystack)
    {
        $reg = '/@default.*/i';

        foreach ($haystack as $line) {
            if (preg_match($reg, trim($line), $tmp) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * 格式化禁用权限
     * @desc 格式化禁用权限
     * @param $haystack
     * @return bool
     * @author TELstatic
     * Date: 2020/8/19/0019
     */
    protected function formatBlack($haystack)
    {
        $reg = '/@black.*/i';

        foreach ($haystack as $line) {
            if (preg_match($reg, trim($line), $tmp) === 1) {
                return true;
            }
        }

        return false;
    }
}
