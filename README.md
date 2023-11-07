# litemall

一个小商场系统。

litemall = PHP Laravel框架后端 + Vue管理员前端 + 微信小程序用户前端 + Vue用户移动端


## 项目代码

* [码云](https://gitee.com/linlinjava/litemall)
* [GitHub](https://github.com/calmzo/litemall-laravel10.git)

## 项目架构
![](./doc/pics/readme/project-structure.png)

## 技术栈

> 1. Laravel
> 2. Vue
> 3. 微信小程序

![](doc/pics/readme/technology-stack.png)

## 功能

### 小商城功能

* 首页
* 专题列表、专题详情
* 分类列表、分类详情
* 品牌列表、品牌详情
* 新品首发、人气推荐
* 优惠券列表、优惠券选择
* 团购
* 搜索
* 商品详情、商品评价、商品分享
* 购物车
* 下单
* 订单列表、订单详情、订单售后
* 地址、收藏、足迹、意见反馈
* 客服

### 管理平台功能

* 会员管理
* 商城管理
* 商品管理
* 推广管理
* 系统管理
* 配置管理
* 统计报表


## 开发环境部署/安装

1. 配置最小开发环境：
    * [MySQL](https://dev.mysql.com/downloads/mysql/)
    * [Laravel10.x](https://github.com/laravel/laravel)
    * [Php8.1.0及以上](https://www.php.net/downloads)
    * [Composer2.2.0及以上](https://composer.p2hp.com/)
    * [Nodejs](https://nodejs.org/en/download/)
    * [微信开发者工具](https://developers.weixin.qq.com/miniprogram/dev/devtools/download.html)

### 基础安装

1、克隆源代码

克隆 `litemall` 代码到本地

```
https://github.com/calmzo/litemall-laravel10.git
```

2、安装扩展包依赖

```
composer install
```

3、生成 `Laravel`  框架的配置文件

```
cp .env.example .env
```

根据情况修改成自己的配置，比如邮件发送配置，数据库配置，微信支付配置，支付宝支付配置等等

```
APP_URL=http://larabbs.test
...
DB_HOST=localhost
DB_DATABASE=larabbs
DB_USERNAME=homestead
DB_PASSWORD=secret

DOMAIN=.larabbs.test
```

如果某些端口被占用，需要修改配置，改成其他的端口

4、导入数据到数据库中

`sql` 文件在 `sql` 目录中，使用数据库工具导入数据即可，比如 `navicat` 等



## 前端快速启动
1. 启动管理后台前端

   打开命令行，输入以下命令
    ```bash
    cd litemall/litemall-admin
    npm install --registry=https://registry.npm.taobao.org
    npm run dev
    ```
   此时，浏览器打开，输入网址`http://localhost:9527`, 此时进入管理后台登录页面。

2. 启动小商城前端

   这里存在两套小商场前端litemall-wx和renard-wx，开发者可以分别导入和测试：

    1. 微信开发工具导入litemall-wx项目;
    2. 项目配置，启用“不校验合法域名、web-view（业务域名）、TLS 版本以及 HTTPS 证书”
    3. 点击“编译”，即可在微信开发工具预览效果；
    4. 也可以点击“预览”，然后手机扫描登录（但是手机需开启调试功能）。

   注意：
   > 这里只是最简启动方式，而小商场的微信登录、微信支付等功能需开发者设置才能运行，
   > 更详细方案请参考[文档](https://linlinjava.gitbook.io/litemall/project)。

6. 启动轻商城前端

   打开命令行，输入以下命令
    ```bash
    cd litemall/litemall-vue
    npm install --registry=https://registry.npm.taobao.org
    npm run dev
    ```
   此时，浏览器（建议采用chrome 手机模式）打开，输入网址`http://localhost:6255`, 此时进入轻商场。

   注意：
   > 现在功能很不稳定，处在开发阶段。


## 致谢

本项目基于或参考以下项目：

1. [nideshop-mini-program](https://github.com/tumobi/nideshop-mini-program)

   项目介绍：基于Node.js+MySQL开发的开源微信小程序商城（微信小程序）

   项目参考：

    1. litemall项目数据库基于nideshop-mini-program项目数据库；
    2. litemall项目的litemall-wx模块基于nideshop-mini-program开发。

2. [vue-element-admin](https://github.com/PanJiaChen/vue-element-admin)

   项目介绍： 一个基于Vue和Element的后台集成方案

   项目参考：litemall项目的litemall-admin模块的前端框架基于vue-element-admin项目修改扩展。

3. [mall-admin-web](https://github.com/macrozheng/mall-admin-web)

   项目介绍：mall-admin-web是一个电商后台管理系统的前端项目，基于Vue+Element实现。

   项目参考：litemall项目的litemall-admin模块的一些页面布局样式参考了mall-admin-web项目。

4. [biu](https://github.com/CaiBaoHong/biu)

   项目介绍：管理后台项目开发脚手架，基于vue-element-admin和springboot搭建，前后端分离方式开发和部署。

   项目参考：litemall项目的权限管理功能参考了biu项目。

5. [vant--mobile-mall](https://github.com/qianzhaoy/vant--mobile-mall)

   项目介绍：基于有赞 vant 组件库的移动商城。

   项目参考：litemall项目的litemall-vue模块基于vant--mobile-mall项目开发。

## 推荐

1. [Flutter_Mall](https://github.com/youxinLu/mall)

   项目介绍：Flutter_Mall是一款Flutter开源在线商城应用程序。

2. [Taro_Mall](https://github.com/jiechud/taro-mall)

   项目介绍：Taro_Mall是一款多端开源在线商城应用程序，后台是基于litemall基础上进行开发，前端采用Taro框架编写。

## 问题

1. 新建路由相关

2. jwt相关

3. 设置laravel 接口异常错误返回json数据 
   【文件Exceptions\Handler.php重写render方法】
    
4. 表名前缀问题
    database.php中设置前缀
   
5. 兼容前端token认证
    ```php 
   public function handle($request, Closure $next, ...$guards)
    {
        if (!is_null($request->headers->get("X-Litemall-Token"))) {
            $request->headers->set('Authorization','Bearer '.$request->headers->get("X-Litemall-Token"));
        }
        return $next($request);
    } 
   ```

