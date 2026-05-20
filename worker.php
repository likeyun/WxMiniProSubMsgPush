<?php
set_time_limit(0);

require_once './config.php';

$taskId = intval($argv[1] ?? 0);
$workerId = intval($argv[2] ?? 0);

if (!$taskId || !$workerId) {
    exit("task_id or worker_id empty\n");
}

$queueKey = "wepush:task:$taskId:queue";

while (true) {

    $status = $redis->get("wepush:task:$taskId:status");

    if ($status === 'paused') {
        sleep(1);
        continue;
    }

    if ($status === 'stopped') {
        exit;
    }

    if ($status !== 'running') {
        sleep(1);
        continue;
    }

    $redis->setex("wepush:task:$taskId:worker:$workerId:heartbeat", 10, time());

    $qps = intval($redis->get("wepush:task:$taskId:qps") ?: 20);
    limitQps($redis, $taskId, $qps);

    $raw = $redis->rPop($queueKey);

    if (!$raw) {
        finishTaskIfDone($db, $redis, $taskId);
        exit;
    }

    $item = json_decode($raw, true);
    if (!$item || empty($item['openid'])) {
        continue;
    }

    $openid = $item['openid'];
    $retry = intval($item['retry'] ?? 0);

    try {
        $res = sendSubscribeMessage($db, $taskId, $openid);
    } catch (Throwable $e) {
        $res = [
            'errcode' => -1,
            'errmsg' => $e->getMessage()
        ];
    }

    if (intval($res['errcode'] ?? -1) === 0) {
        $redis->incr("wepush:task:$taskId:success");
        $redis->incr("wepush:task:$taskId:done");

        saveLog($db, $taskId, $openid, 1, 0, 'success', $retry);
    } else {
        $errcode = intval($res['errcode'] ?? -1);
        $errmsg = $res['errmsg'] ?? 'unknown error';

        if ($retry < 2 && in_array($errcode, [-1, 40001, 42001, 45009, 45011])) {
            $item['retry'] = $retry + 1;
            $redis->lPush($queueKey, json_encode($item, JSON_UNESCAPED_UNICODE));
            usleep(500000);
        } else {
            $redis->incr("wepush:task:$taskId:fail");
            $redis->incr("wepush:task:$taskId:done");

            saveLog($db, $taskId, $openid, 2, $errcode, $errmsg, $retry);
        }
    }

    finishTaskIfDone($db, $redis, $taskId);
}

function limitQps($redis, $taskId, $qps)
{
    if ($qps <= 0) {
        return;
    }

    while (true) {
        $key = "wepush:task:$taskId:qps:" . time();
        $count = $redis->incr($key);

        if ($count == 1) {
            $redis->expire($key, 2);
        }

        if ($count <= $qps) {
            break;
        }

        usleep(100000);
    }
}

function sendSubscribeMessage($db, $taskId, $openid)
{
    $stmt = $db->prepare("
        SELECT 
            task.id,
            tpl.template_id,
            tpl.page,
            tpl.data_json,
            tpl.miniprogram_state,
            tpl.lang
        FROM wepush_task task
        LEFT JOIN wepush_template tpl ON task.template_db_id = tpl.id
        WHERE task.id = ?
        LIMIT 1
    ");
    $stmt->execute([$taskId]);
    $row = $stmt->fetch();

    if (!$row) {
        return [
            'errcode' => -1,
            'errmsg' => '任务不存在'
        ];
    }

    $accessToken = getAccessToken();

    $url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$accessToken}";

    $body = [
        'touser' => $openid,
        'template_id' => $row['template_id'],
        'page' => $row['page'],
        'data' => json_decode($row['data_json'], true),
        'miniprogram_state' => $row['miniprogram_state'] ?: 'formal',
        'lang' => $row['lang'] ?: 'zh_CN'
    ];

    return httpPostJson($url, $body);
}

function saveLog($db, $taskId, $openid, $status, $errcode, $errmsg, $retry)
{
    $stmt = $db->prepare("
        INSERT INTO wepush_log
        (task_id, openid, status, errcode, errmsg, retry)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $taskId,
        $openid,
        $status,
        $errcode,
        mb_substr($errmsg, 0, 250),
        $retry
    ]);
}

function finishTaskIfDone($db, $redis, $taskId)
{
    $total = intval($redis->get("wepush:task:$taskId:total"));
    $done = intval($redis->get("wepush:task:$taskId:done"));
    $left = intval($redis->lLen("wepush:task:$taskId:queue"));

    if ($total > 0 && $done >= $total && $left <= 0) {
        $success = intval($redis->get("wepush:task:$taskId:success"));
        $fail = intval($redis->get("wepush:task:$taskId:fail"));

        $redis->set("wepush:task:$taskId:status", "finished");

        $stmt = $db->prepare("
            UPDATE wepush_task 
            SET status=3, success=?, fail=?, done=?, finish_time=NOW()
            WHERE id=?
        ");
        $stmt->execute([$success, $fail, $done, $taskId]);
    }
}