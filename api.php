<?php
session_start();

if (empty($_SESSION['wepush_login'])) {
    echo json_encode([
        'code' => 401,
        'msg' => '请先登录'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once './config.php';

$action = $_GET['action'] ?? '';

if ($action === 'test_send') {

    $templateDbId = intval($_POST['template_db_id'] ?? 0);
    $openid = trim($_POST['openid'] ?? '');

    if (!$templateDbId || !$openid) {
        jsonOut(1, '参数不能为空');
    }

    $stmt = $db->prepare("
        SELECT *
        FROM wepush_template
        WHERE id=?
        LIMIT 1
    ");
    $stmt->execute([$templateDbId]);

    $tpl = $stmt->fetch();

    if (!$tpl) {
        jsonOut(1, '模板不存在');
    }

    try {

        $accessToken = getAccessToken();

        $url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$accessToken}";

        $body = [
            'touser' => $openid,
            'template_id' => $tpl['template_id'],
            'page' => $tpl['page'],
            'data' => json_decode($tpl['data_json'], true),
            'miniprogram_state' => $tpl['miniprogram_state'] ?: 'formal',
            'lang' => $tpl['lang'] ?: 'zh_CN'
        ];

        $res = httpPostJson($url, $body);

        jsonOut(
            intval($res['errcode'] ?? -1) === 0 ? 200 : 1,
            $res['errmsg'] ?? 'unknown',
            $res
        );

    } catch (Throwable $e) {

        jsonOut(1, $e->getMessage());
    }
}

if ($action === 'save_template') {
    $title = trim($_POST['title'] ?? '');
    $templateId = trim($_POST['template_id'] ?? '');
    $templateType = intval($_POST['template_type'] ?? 1);
    $page = trim($_POST['page'] ?? '');
    $dataJson = trim($_POST['data_json'] ?? '');

    if (!$title || !$templateId || !$dataJson) {
        jsonOut(1, '参数不能为空');
    }

    json_decode($dataJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonOut(1, 'data_json 不是合法JSON');
    }

    $stmt = $db->prepare("
        INSERT INTO wepush_template 
        (title, template_id, template_type, page, data_json)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$title, $templateId, $templateType, $page, $dataJson]);

    jsonOut(200, '模板保存成功');
}

if ($action === 'template_list') {
    $list = $db->query("SELECT * FROM wepush_template ORDER BY id DESC")->fetchAll();
    jsonOut(200, 'success', $list);
}

if ($action === 'create_task') {
    $title = trim($_POST['title'] ?? '');
    $templateDbId = intval($_POST['template_db_id'] ?? 0);
    $qps = max(1, intval($_POST['qps'] ?? 20));
    $workers = max(1, intval($_POST['workers'] ?? 5));
    $openidText = trim($_POST['openid_text'] ?? '');

    if (!$title || !$templateDbId || !$openidText) {
        jsonOut(1, '参数不能为空');
    }

    $openids = preg_split('/[\r\n,，\s]+/', $openidText);
    $openids = array_values(array_unique(array_filter(array_map('trim', $openids))));

    if (!$openids) {
        jsonOut(1, 'openid不能为空');
    }

    $stmt = $db->prepare("
        INSERT INTO wepush_task 
        (title, template_db_id, total, qps, workers, status)
        VALUES (?, ?, ?, ?, ?, 0)
    ");
    $stmt->execute([$title, $templateDbId, count($openids), $qps, $workers]);

    $taskId = $db->lastInsertId();

    $queueKey = "wepush:task:$taskId:queue";

    foreach ($openids as $openid) {
        $redis->lPush($queueKey, json_encode([
            'task_id' => $taskId,
            'openid' => $openid,
            'retry' => 0
        ], JSON_UNESCAPED_UNICODE));
    }

    $redis->set("wepush:task:$taskId:status", "waiting");
    $redis->set("wepush:task:$taskId:total", count($openids));
    $redis->set("wepush:task:$taskId:success", 0);
    $redis->set("wepush:task:$taskId:fail", 0);
    $redis->set("wepush:task:$taskId:done", 0);
    $redis->set("wepush:task:$taskId:qps", $qps);
    $redis->set("wepush:task:$taskId:workers", $workers);

    jsonOut(200, '任务创建成功', [
        'task_id' => $taskId
    ]);
}

if ($action === 'start') {
    $taskId = intval($_POST['task_id'] ?? 0);

    $stmt = $db->prepare("SELECT * FROM wepush_task WHERE id=? LIMIT 1");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    if (!$task) {
        jsonOut(1, '任务不存在');
    }

    $workers = intval($task['workers']);

    $redis->set("wepush:task:$taskId:status", "running");

    $db->prepare("UPDATE wepush_task SET status=1, start_time=NOW() WHERE id=?")
        ->execute([$taskId]);

    for ($i = 1; $i <= $workers; $i++) {
        $cmd = "nohup php " . __DIR__ . "/worker.php {$taskId} {$i} > /dev/null 2>&1 &";
        exec($cmd);
    }

    jsonOut(200, '任务已启动');
}

if ($action === 'pause') {
    $taskId = intval($_POST['task_id'] ?? 0);
    $redis->set("wepush:task:$taskId:status", "paused");
    $db->prepare("UPDATE wepush_task SET status=2 WHERE id=?")->execute([$taskId]);
    jsonOut(200, '已暂停');
}

if ($action === 'resume') {
    $taskId = intval($_POST['task_id'] ?? 0);
    $redis->set("wepush:task:$taskId:status", "running");
    $db->prepare("UPDATE wepush_task SET status=1 WHERE id=?")->execute([$taskId]);
    jsonOut(200, '已继续');
}

if ($action === 'stop') {
    $taskId = intval($_POST['task_id'] ?? 0);
    $redis->set("wepush:task:$taskId:status", "stopped");
    $db->prepare("UPDATE wepush_task SET status=4 WHERE id=?")->execute([$taskId]);
    jsonOut(200, '已停止');
}

if ($action === 'progress') {
    $taskId = intval($_GET['task_id'] ?? 0);

    $total = intval($redis->get("wepush:task:$taskId:total"));
    $success = intval($redis->get("wepush:task:$taskId:success"));
    $fail = intval($redis->get("wepush:task:$taskId:fail"));
    $done = intval($redis->get("wepush:task:$taskId:done"));
    $status = $redis->get("wepush:task:$taskId:status") ?: 'unknown';
    $left = intval($redis->lLen("wepush:task:$taskId:queue"));

    $percent = $total > 0 ? round($done / $total * 100, 2) : 0;

    jsonOut(200, 'success', [
        'task_id' => $taskId,
        'total' => $total,
        'success' => $success,
        'fail' => $fail,
        'done' => $done,
        'left' => $left,
        'percent' => $percent,
        'status' => $status
    ]);
}

if ($action === 'task_list') {
    $list = $db->query("SELECT * FROM wepush_task ORDER BY id DESC LIMIT 30")->fetchAll();
    jsonOut(200, 'success', $list);
}

if ($action === 'fail_log') {
    $taskId = intval($_GET['task_id'] ?? 0);

    $stmt = $db->prepare("
        SELECT * FROM wepush_log 
        WHERE task_id=? AND status=2 
        ORDER BY id DESC 
        LIMIT 100
    ");
    $stmt->execute([$taskId]);

    jsonOut(200, 'success', $stmt->fetchAll());
}

if ($action === 'template_detail') {
    $id = intval($_GET['id'] ?? 0);

    $stmt = $db->prepare("SELECT * FROM wepush_template WHERE id=? LIMIT 1");
    $stmt->execute([$id]);

    $row = $stmt->fetch();

    if (!$row) {
        jsonOut(1, '模板不存在');
    }

    jsonOut(200, 'success', $row);
}

if ($action === 'update_template') {
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $templateId = trim($_POST['template_id'] ?? '');
    $templateType = intval($_POST['template_type'] ?? 1);
    $page = trim($_POST['page'] ?? '');
    $dataJson = trim($_POST['data_json'] ?? '');

    if (!$id || !$title || !$templateId || !$dataJson) {
        jsonOut(1, '参数不能为空');
    }

    json_decode($dataJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonOut(1, 'data_json格式错误');
    }

    $stmt = $db->prepare("
        UPDATE wepush_template 
        SET title=?, template_id=?, template_type=?, page=?, data_json=?
        WHERE id=?
    ");

    $stmt->execute([
        $title,
        $templateId,
        $templateType,
        $page,
        $dataJson,
        $id
    ]);

    jsonOut(200, '模板更新成功');
}

if ($action === 'delete_task') {
    $taskId = intval($_POST['task_id'] ?? 0);

    if (!$taskId) {
        jsonOut(1, '任务ID不能为空');
    }

    $redis->set("wepush:task:$taskId:status", "stopped");
    $redis->del("wepush:task:$taskId:queue");
    $redis->del("wepush:task:$taskId:total");
    $redis->del("wepush:task:$taskId:success");
    $redis->del("wepush:task:$taskId:fail");
    $redis->del("wepush:task:$taskId:done");
    $redis->del("wepush:task:$taskId:qps");
    $redis->del("wepush:task:$taskId:workers");

    $db->prepare("DELETE FROM wepush_task WHERE id=?")->execute([$taskId]);
    $db->prepare("DELETE FROM wepush_log WHERE task_id=?")->execute([$taskId]);

    jsonOut(200, '任务已删除');
}

jsonOut(404, '未知操作');