<?php

namespace TELstatic\Thresh\Controllers;

use TELstatic\Thresh\Requests\ThreshRequest;

/**
 * 文档管理.
 *
 * @desc 文档管理
 *
 * @author TELstatic
 */
class ThreshController
{
    /**
     * 测试列表.
     *
     * @desc 测试列表
     *
     * @author TELstatic
     */
    public function index(Request $request)
    {
        if ('view' === $request->get('type', 'view')) {
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
        $name = 'Thresh 测试文档';

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

        app('thresh')->load($blackControllers, $whiteNamespaceList);

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
            case 'permission':
                return app('thresh')->getPermissions($name, $middleware, $headers);
                break;
        }
    }

    /**
     * 测试创建.
     *
     * @desc 测试创建
     *
     * @author TELstatic
     */
    public function create()
    {
    }

    /**
     * 测试编辑.
     *
     * @desc 测试编辑.
     *
     * @param $id
     *
     * @author TELstatic
     */
    public function edit($id)
    {
    }

    /**
     * 测试显示.
     *
     * @desc 测试显示
     *
     * @param $id
     *
     * @author TELstatic
     */
    public function show($id)
    {
    }

    /**
     * 测试保存.
     *
     * @desc 测试保存
     *
     * @params title string yes null 标题
     * @params name string yes null 名称
     * @params sort integer yes 0 排序
     * @params is_show integer yes 1 是否显示
     * @params thumb string yes null 封面
     * @params enabled_at string yes null 生效时间
     * @params disabled_at string yes null 失效时间
     * @params variants[0][title] string yes null 多规格1标题
     * @params variants[0][thumb] string yes null 多规格1封面
     * @params variants[0][price] string yes null 多规格1价格
     * @params variants[0][stock] string yes null 多规格1库存
     * @params variants[1][title] string yes null 多规格2标题
     * @params variants[1][thumb] string yes null 多规格2封面
     * @params variants[1][price] string yes null 多规格2价格
     * @params variants[1][stock] string yes null 多规格2库存
     *
     * @author TELstatic
     */
    public function store(ThreshRequest $request)
    {

    }

    /**
     * 测试更新.
     *
     * @desc 测试更新
     *
     * @param $id
     *
     * @params title string yes null 标题
     * @params name string yes null 名称
     * @params sort integer yes 0 排序
     * @params is_show integer yes 1 是否显示
     * @params thumb string yes null 封面
     * @params enabled_at string yes null 生效时间
     * @params disabled_at string yes null 失效时间
     * @params variants[0][title] string yes null 多规格1标题
     * @params variants[0][thumb] string yes null 多规格1封面
     * @params variants[0][price] string yes null 多规格1价格
     * @params variants[0][stock] string yes null 多规格1库存
     * @params variants[1][title] string yes null 多规格2标题
     * @params variants[1][thumb] string yes null 多规格2封面
     * @params variants[1][price] string yes null 多规格2价格
     * @params variants[1][stock] string yes null 多规格2库存
     *
     * @author TELstatic
     */
    public function update($id, ThreshRequest $request)
    {
        return $request->all();
    }

    /**
     * 测试删除.
     *
     * @desc 测试删除
     *
     * @param $id
     *
     * @author TELstatic
     */
    public function destroy($id)
    {

    }

}