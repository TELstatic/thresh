<?php

namespace TELstatic\Thresh\Controllers;

/**
 * Swagger 预览.
 * @desc Swagger 预览
 *
 * @author TELstatic
 */
class SwaggerController
{
    /**
     * Swagger 预览.
     *
     * @desc Swagger 预览
     * @author TELstatic
     */
    public function index()
    {
        return view('thresh::swagger');
    }

}
