<?php

namespace mix\web;

use mix\base\BaseObject;

/**
 * Controller类
 * @author 刘健 <coder.liu@qq.com>
 */
class controller extends baseobject
{

    // 默认布局
    protected $layout = 'main';

    // 渲染视图 (包含布局)
    public function render($name, $data = [])
    {
        $view            = new view();
        $data['content'] = $view->render($name, $data);
        return $view->render("layout.{$this->layout}", $data);
    }

    // 渲染视图 (不包含布局)
    public function renderpartial($name, $data = [])
    {
        if (strpos($name, '.') === false) {
            $name = $this->viewprefix() . '.' . $name;
        }
        $view = new view();
        return $view->render($name, $data);
    }

    // 视图前缀
    protected function viewprefix()
    {
        return \mix\base\Route::cameltosnake(str_replace([\Mix::app()->controllerNamespace . '\\', '\\', 'controller'], ['', '.', ''], get_class($this)));
    }

}
