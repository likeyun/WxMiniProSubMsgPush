<?php
date_default_timezone_set('Asia/Shanghai');

$db = new PDO(
    'mysql:host=127.0.0.1;dbname=数据库名称;charset=utf8mb4',
    '数据库账号',
    '数据库密码',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

// 小程序配置
define('WX_APPID', '小程序Appid');
define('WX_SECRET', '小程序Appsecret');

function jsonOut($code, $msg, $data = [])
{
    echo json_encode([
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function httpPostJson($url, $data)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 15
    ]);

    $res = curl_exec($ch);

    if ($res === false) {
        return [
            'errcode' => -1,
            'errmsg' => curl_error($ch)
        ];
    }

    curl_close($ch);

    $json = json_decode($res, true);

    return $json ?: [
        'errcode' => -1,
        'errmsg' => $res
    ];
}

function httpGetJson($url)
{
    $res = file_get_contents($url);
    return json_decode($res, true);
}

function getAccessToken()
{
    global $redis;

    $key = 'wepush:wx:access_token';

    $token = $redis->get($key);
    if ($token) {
        return $token;
    }

    $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . WX_APPID . '&secret=' . WX_SECRET;

    $res = httpGetJson($url);

    if (empty($res['access_token'])) {
        throw new Exception('获取 access_token 失败：' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    $redis->setex($key, 7000, $res['access_token']);

    return $res['access_token'];
}