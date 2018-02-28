<?php

namespace mix\websocket;

use mix\base\BaseObject;

/**
 * Controller类
 * @author 刘健 <coder.liu@qq.com>
 */
class Controller extends BaseObject
{

    // 服务
    protected $_server;

    // 文件描述符
    protected $_fd;

    // 获取服务
    public function getServer()
    {
        return $this->_server;
    }

    // 获取文件描述符
    public function getFd()
    {
        return $this->_fd;
    }

}
