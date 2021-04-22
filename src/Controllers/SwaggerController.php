<?php

namespace TELstatic\Thresh\Controllers;

use TELstatic\Thresh\Requests\ThreshRequest;
use Illuminate\Http\Request;

/**
 * Swagger 预览
 * @desc Swagger 预览
 * @package TELstatic\Thresh
 * @author TELstatic
 */
class SwaggerController
{
    /**
     * Swagger 预览
     * @desc Swagger 预览
     * @author TELstatic
     * Date: 2021/4/22/0022
     */
    public function index()
    {
        return view('thresh::swagger');
    }
}