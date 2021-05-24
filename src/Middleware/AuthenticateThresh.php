<?php

namespace App\Http\Middleware;

use Closure;

class AuthenticateThresh
{
    // 路径黑名单
    public $blackList = [];

    // 获取当前授权用户
    protected function guard()
    {
        return auth()->guard();
    }

    // Guest 用户跳转地址
    protected function redirectTo()
    {
        return '/';
    }

    /**
     * 校验用户权限.
     */
    public function handle($request, Closure $next)
    {
        if ($this->guard()->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest($this->redirectTo());
            }
        }

        if ($request->get('format') === 'json') {
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        }

        // 超级用户排除
        if ($this->guard()->id() == 1) {
            return $next($request);
        }

        // 黑名单排除
        if (in_array($request->path(), $this->blackList)) {
            return $next($request);
        }

        $path = $this->formatPath($request->method().'-'.$request->path());

        if (!$this->guard()->user()->can($path)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 403);
            } else {
                abort(403);
            }
        }

        return $next($request);
    }

    /**
     * 格式化路径.
     *
     * @desc admin/order/{21041111452212445} => admin/order/{no}
     * @desc admin/product/edit/10 => admin/product/edit/{id}
     *
     * @param $path
     *
     * @return string|string[]|null
     * @author TELstatic
     */
    public function formatPath($path)
    {
        //替换 数字 为 {id}
        return preg_replace('/(\d+)/', '{id}', $path);
    }
}
