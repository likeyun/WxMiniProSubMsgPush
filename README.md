# WePush MiniProgram Push

基于 PHP + Redis + MySQL 开发的微信小程序订阅消息推送系统。

支持：

- 微信小程序一次性订阅消息
- 微信小程序长期订阅消息
- Redis 高性能消息队列
- Worker 多进程并发
- QPS 限速
- TXT 导入 openid
- 模板管理
- 模板测试发送
- 实时任务监控
- 失败日志
- 企业级后台 UI

---

# ✨ 功能特性

- ✅ 微信小程序订阅消息推送
- ✅ 一次性订阅 / 长期订阅
- ✅ Redis 高性能队列
- ✅ PHP Worker 多进程并发
- ✅ 自定义 QPS
- ✅ 推送任务暂停 / 继续 / 停止
- ✅ 推送实时进度
- ✅ 企业级后台 UI
- ✅ TXT 批量导入 openid
- ✅ 模板可视化编辑
- ✅ 模板测试发送
- ✅ 失败日志记录
- ✅ access_token Redis 缓存
- ✅ 实时数据可视化
- ✅ 登录权限验证

---

# 🖼️ 系统截图

## 控制台

> 建议这里放截图

## 模板管理

> 建议这里放截图

## 任务执行中心

> 建议这里放截图

---

# 🚀 技术架构

```text
PHP 8+
Redis
MySQL
Worker CLI
微信 subscribeMessage.send
```

架构：

```text
Web后台
    ↓
Redis Queue
    ↓
Worker 并发消费
    ↓
微信 subscribeMessage.send
```

---

# 📦 目录结构

```text
wepush/
├─ api.php
├─ config.php
├─ worker.php
├─ login.php
├─ logout.php
├─ index.php
├─ install.sql
└─ README.md
```

---

# ⚙️ 环境要求

```text
PHP >= 7.4
Redis >= 5
MySQL >= 5.7
Linux 推荐
```

PHP 扩展：

```text
redis
pdo
pdo_mysql
curl
json
mbstring
```

---

# 🔧 安装教程

## 1、导入数据库

导入：

```text
install.sql
```

---

## 2、配置数据库和 Redis

编辑：

```php
config.php
```

修改：

```php
$db = new PDO(
    'mysql:host=127.0.0.1;dbname=数据库名;charset=utf8mb4',
    '数据库账号',
    '数据库密码'
);

$redis->connect('127.0.0.1', 6379);
```

---

## 3、配置微信小程序

```php
define('WX_APPID', '你的小程序appid');
define('WX_SECRET', '你的小程序secret');
```

---

## 4、开放 exec 函数

本项目需要使用：

```text
exec
shell_exec
proc_open
popen
```

宝塔：

```text
PHP设置
→ 禁用函数
```

删除：

```text
exec,shell_exec,proc_open,popen
```

然后重启 PHP。

---

# 🔐 默认登录

访问：

```text
/login.php
```

默认账号密码：

```text
admin
123456
```

修改：

```php
login.php
```

里面：

```php
$ADMIN_USER = 'admin';
$ADMIN_PASS = '123456';
```

---

# 📬 创建模板

支持：

- 一次性订阅
- 长期订阅

无需手写 JSON。

后台直接：

```text
字段名
字段值
```

自动生成：

```json
{
  "thing1": {
    "value": "测试内容"
  }
}
```

---

# 🧪 测试发送

后台支持：

```text
指定 openid 测试发送
```

测试 openid 自动本地缓存。

---

# 📂 导入 openid

支持：

```text
TXT 文件导入
```

格式：

```text
openid1
openid2
openid3
```

也支持直接粘贴。

系统自动：

- 去重
- 过滤空行
- 统计数量

---

# ⚡ 并发与 QPS

支持：

- Worker 并发
- QPS 控制

推荐：

```text
QPS：20~50
Worker：5~10
```

---

# 📊 实时任务监控

支持：

- 实时进度
- 成功数
- 失败数
- 剩余数
- 环形进度图
- 动态数据面板

---

# 🧠 Redis 设计

队列：

```text
wepush:task:{task_id}:queue
```

任务状态：

```text
running
paused
stopped
finished
```

统计：

```text
success
fail
done
```

---

# 🔁 失败重试

自动重试：

```text
40001
42001
45009
45011
-1
```

默认：

```text
最多重试 2 次
```

---

# 📑 微信接口

使用：

```text
subscribeMessage.send
```

接口：

```text
https://api.weixin.qq.com/cgi-bin/message/subscribe/send
```

---

# ⚠️ 注意事项

## 1、订阅消息限制

微信限制：

```text
用户必须授权订阅
```

否则：

```text
43101
```

---

## 2、QPS 不建议太高

建议：

```text
20~50 QPS
```

不要无限并发。

---

## 3、长期订阅

长期订阅仅部分行业开放。

---

# 🛠️ 后续计划

- [ ] WebSocket 实时日志
- [ ] CSV 导入
- [ ] 多小程序支持
- [ ] Redis Stream
- [ ] Docker 部署
- [ ] 代理池
- [ ] 可视化图表
- [ ] 多管理员
- [ ] API Token
- [ ] 消息模板市场

---

# ❤️ 开源协议

MIT License

---

# 🌟 Star

如果这个项目对你有帮助，欢迎 Star ⭐

---

# 👨‍💻 作者

```text
WePush MiniProgram Push
基于 PHP + Redis 的微信小程序订阅消息推送系统
```
