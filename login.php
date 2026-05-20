<?php
session_start();

$ADMIN_USER = 'admin';
$ADMIN_PASS = '123456';

if (!empty($_SESSION['wepush_login'])) {
    header('Location: index.php');
    exit;
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === $ADMIN_USER && $password === $ADMIN_PASS) {
        $_SESSION['wepush_login'] = true;
        $_SESSION['wepush_user'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $msg = '账号或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>WxPush 登录</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;background:linear-gradient(135deg,#101828,#1677ff);display:flex;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Microsoft YaHei",Arial;color:#101828}
.login-box{width:380px;background:#fff;border-radius:22px;padding:34px;box-shadow:0 24px 70px rgba(0,0,0,.25)}
h1{margin:0 0 8px;font-size:26px}
.desc{color:#667085;font-size:14px;margin-bottom:24px}
input{width:100%;height:46px;border:1px solid #d9e0ea;border-radius:12px;padding:0 14px;margin-bottom:14px;font-size:15px;outline:none}
input:focus{border-color:#1677ff;box-shadow:0 0 0 3px rgba(22,119,255,.12)}
button{width:100%;height:46px;border:0;border-radius:12px;background:#1677ff;color:#fff;font-size:15px;cursor:pointer}
.msg{background:#fff1f0;color:#f04438;padding:10px 12px;border-radius:10px;margin-bottom:14px;font-size:14px}
</style>
</head>
<body>

<div class="login-box">
  <h1>WxPush Admin</h1>
  <div class="desc">微信小程序订阅消息推送系统</div>

  <?php if ($msg): ?>
    <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>

  <form method="post">
    <input type="text" name="username" placeholder="账号" autocomplete="off">
    <input type="password" name="password" placeholder="密码">
    <button type="submit">登录系统</button>
  </form>
</div>

</body>
</html>