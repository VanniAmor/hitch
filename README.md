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

### 后端
项目使用Lumen + Dingo进行搭建，使用到的技术有：

- JWTAuth： token令牌验证
- Redis：消息队列、缓存乘客发布的路线
- 第三方技术
 - 百度地图
 - 百度文字识别
 - GoEasy信息推送
 - 七牛云对象存储
 - 短信服务

### 前端
项目分为司机端和乘客端，使用到的相关技术有：

- MUI：轻便的、H5+开发框架，能使用html编写手机App应用，负责项目的事件处理和项目架构
- Vue：负责数据绑定和渲染
- Axios：网络请求的发布和处理
- 第三方技术
  - 百度地图
  - GoEasy信息推送

### 已实现的功能
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

# 效果图

![首页](https://note.youdao.com/yws/api/personal/file/E4886412478D48F9ADCFEA0ED8F88EA7?method=download&shareKey=4115ff8203b6b49b9526e1cb727abb65 "首页")
![个人中心](https://note.youdao.com/yws/api/personal/file/0C3135C50223460F8AFDBCB60E1AA9ED?method=download&shareKey=23b9c6f4392f42778140b0cf2518343b "个人中心")
![用户信息](https://note.youdao.com/yws/api/personal/file/1BE2419CD1D94FAFA809D3C4F2993D68?method=download&shareKey=e83822a9d8f017036d29b51b1d49c19f "用户信息")
![路线设置](https://note.youdao.com/yws/api/personal/file/9BFBD86B835C4E6D82A8407BEB00CB82?method=download&shareKey=91895b2f3d70b66ee5e61ba4b37f3956 "路线设置")
![路线计算](https://note.youdao.com/yws/api/personal/file/E0031BB4820F4887BBE6065E0F2B899C?method=download&shareKey=ee23cde21fbd21876b77feaac36b30db "路线计算")
![出行信息](https://note.youdao.com/yws/api/personal/file/85EFC2BAA121476190C5CD32D36BDA3C?method=download&shareKey=d5cc1e76648819c19c08310c0d781056 "出行信息")


