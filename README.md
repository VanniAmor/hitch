# VanniHitch
Vanni辉的大学毕业项目，课题【基于百度地图的拼车网设计与开发】

## 项目安装

- php7.1及以上
- Mysql5.7及以上

*注： PHP需要开启CURL拓展

    # 安装项目依赖
    Composer install

    # 开启项目任务队列监听
    php artisan queue:listen


## 项目介绍

上下班拼车，预约出行，绿色环保，节能减排

- 司机端地址：https://github.com/VanniAmor/vanni_hitch_driver
- 乘客端地址：https://github.com/VanniAmor/vanni_hitch_driver

`项目仅做学习和交流，请勿用作商业用途和销售获利`

## 如何配置

- [后端配置说明][后端配置说明]
- [前端配置说明][前端配置说明]

## 已实现的功能
*注： 这里的用户没有指明，就是指司机和乘客

- 用户的登录注册
- 用户的身份认证，包括身份证信息，驾驶证信息，行驶证信息
- 用户设置出行路线和时间
- 司机开启接单模式
- 系统判断路线是否顺路
- 系统派单
- 司机接单
- 乘客发布订单
- 乘客确定上车
- 用户取消订单

## 效果图

![首页](https://note.youdao.com/yws/api/personal/file/04EE18E467454C7086AD1775F25524DC?method=download&shareKey=2e02ab4f920df31ae97236181954d20a "首页")
![个人中心](https://note.youdao.com/yws/api/personal/file/E61D3C66C52D4850B1439B0F3BF772A0?method=download&shareKey=ed41b0a0e75620116795f5201483f515 "个人中心")
![用户信息](https://note.youdao.com/yws/api/personal/file/BD51839D361D41DEBA085C3BF480BFA4?method=download&shareKey=522c5488e7b6ec759827803ce71363fd "用户信息")
![路线设置](https://note.youdao.com/yws/api/personal/file/D571230A69E24DC5A9261E153CB06C33?method=download&shareKey=69baf04a1eac155ab9958a0594eeab1e "路线设置")
![路线计算](https://note.youdao.com/yws/api/personal/file/66B21870F92844A9B15F82FDF6C33F4E?method=download&shareKey=b3e65c68db214a5d12987181bbe69833 "路线计算")
![出行信息](https://note.youdao.com/yws/api/personal/file/04812D9CFA994D4889FB9B3E7552B7A8?method=download&shareKey=e2bf8078839e5e8a29d58ab478bc9827 "出行信息")

[后端配置说明]: https://github.com/VanniAmor/hitch/wiki/%E5%90%8E%E7%AB%AF%E9%A1%B9%E7%9B%AE%E7%9A%84%E9%85%8D%E7%BD%AE "后端配置说明"
[前端配置说明]: https://github.com/VanniAmor/vanni_hitch_driver/wiki "前端配置说明"


## 有问题

可以通过以下方式联系我，或者到issue提问
- email： 1226066980@qq.com
- wx：L1226066980

