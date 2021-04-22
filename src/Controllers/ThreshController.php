<?php

namespace TELstatic\Thresh\Controllers;

use TELstatic\Thresh\Requests\ThreshRequest;
use Illuminate\Http\Request;

/**
 * 文档管理
 * @desc 文档管理
 * @package TELstatic\Thresh
 * @author TELstatic
 */
class ThreshController
{
    /**
     * 测试列表
     * @desc 测试列表
     * @author TELstatic
     * Date: 2021/4/22/0022
     */
    public function index(Request $request)
    {
        if ($request->get('type', 'view') === 'view') {
            return view('thresh::doc');
        }

        // 命名空间白名单
        $whiteNamespaceList = [
            'TELstatic\Thresh\Controllers',
        ];

        // 控制器黑名单
        $blackControllers = [
            'TELstatic\Thresh\Controllers\SwaggerController',
        ];

        // 导出文件名
        $name = 'Thresh 测试文档 '.date('Y-m-d H:i:s');

        $headers = [
            [
                'key'   => 'Accept',
                'value' => 'application/vnd.thresh.v1+json',
            ],
            [
                'key'   => 'Authorization',
                'value' => 'Bearer {{token}}',
            ],
        ];

        // 指定中间件时 添加 headers 常用在 api 接口添加 Authorization header
        $middleware = 'throttle:60,1';

        app('thresh')->load($blackControllers, $whiteNamespaceList, 2);

        switch ($request->get('type')) {
            default:
            case 'markdown':
                app('thresh')->exportMarkdown($name, $middleware, $headers);
                break;
            case 'postman':
                return app('thresh')->exportPostman($name, $middleware, $headers);
                break;
            case 'swagger':
                return app('thresh')->exportSwagger($name, $middleware, $headers);
                break;
        }
    }

    /**
     * 测试创建
     * @desc 测试创建
     * @author TELstatic
     * Date: 2021/4/22/0022
     */
    public function create()
    {

    }

    /**
     * 测试编辑
     * @desc 测试编辑
     * @param $id
     * @author TELstatic
     * Date: 2021/4/22/0022
     */
    public function edit($id)
    {

    }

    /**
     * 测试显示
     * @desc 测试显示
     * @param $id
     * @author TELstatic
     * Date: 2021/4/22/0022
     */
    public function show($id)
    {

    }

    /**
     * 测试保存
     * @desc 测试保存
     * @param ThreshRequest $request
     * @params title string yes null 标题
     * @params name string yes null 名称
     * @params sort integer yes 0 排序
     * @params is_show integer yes 1 是否显示
     * @params thumb string yes null 封面
     * @params enabled_at string yes null 生效时间
     * @params disabled_at string yes null 失效时间
     * @author TELstatic
     * Date: 2021/4/22/0022
     */
    public function store(ThreshRequest $request)
    {

    }

    /**
     * 测试更新
     * @desc 测试更新
     * @param $id
     * @param ThreshRequest $request
     * @params title string yes null 标题
     * @params name string yes null 名称
     * @params sort integer yes 0 排序
     * @params is_show integer yes 1 是否显示
     * @params thumb string yes null 封面
     * @params enabled_at string yes null 生效时间
     * @params disabled_at string yes null 失效时间
     * @author TELstatic
     * Date: 2021/4/22/0022
     */
    public function update($id, ThreshRequest $request)
    {

    }

    /**
     * 测试删除
     * @desc 测试删除
     * @param $id
     * @author TELstatic
     * Date: 2021/4/22/0022
     */
    public function destroy($id)
    {

    }
}