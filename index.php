<?php
session_start();

if (empty($_SESSION['wepush_login'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>WxPush 小程序订阅消息推送系统</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{box-sizing:border-box}
body{margin:0;background:#f4f6fb;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","Microsoft YaHei",Arial,sans-serif;color:#172033}
.app{display:flex;min-height:100vh}
.sidebar{width:248px;background:#101828;color:#fff;padding:24px 16px;position:fixed;left:0;top:0;bottom:0}
.logo{font-size:21px;font-weight:900;margin-bottom:30px}
.logo span{display:block;font-size:12px;color:#98a2b3;margin-top:6px;font-weight:400}
.nav button{width:100%;border:0;background:transparent;color:#cbd5e1;text-align:left;padding:13px 14px;border-radius:12px;margin-bottom:8px;cursor:pointer;font-size:15px}
.nav button.active,.nav button:hover{background:#1677ff;color:#fff}
.main{margin-left:248px;width:calc(100% - 248px)}
.topbar{height:70px;background:rgba(255,255,255,.88);backdrop-filter:blur(14px);border-bottom:1px solid #e8edf5;display:flex;align-items:center;justify-content:space-between;padding:0 28px;position:sticky;top:0;z-index:5}
.topbar h1{font-size:21px;margin:0}
.tips{font-size:13px;color:#667085}
.content{padding:24px}
.grid{display:grid;grid-template-columns:430px 1fr;gap:20px}
.card{background:#fff;border:1px solid #e8edf5;border-radius:20px;padding:22px;box-shadow:0 12px 30px rgba(16,24,40,.06);margin-bottom:20px}
.card h2{font-size:18px;margin:0 0 16px}
.card-desc{font-size:13px;color:#667085;margin-top:-8px;margin-bottom:16px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
input,select,textarea{width:100%;border:1px solid #d9e0ea;border-radius:13px;padding:12px 13px;font-size:14px;outline:none;background:#fff}
input:focus,select:focus,textarea:focus{border-color:#1677ff;box-shadow:0 0 0 3px rgba(22,119,255,.12)}
textarea{min-height:180px;resize:vertical;line-height:1.6}
.btns{display:flex;gap:10px;flex-wrap:wrap}
button{border:0;border-radius:12px;padding:11px 18px;font-size:14px;cursor:pointer;background:#1677ff;color:#fff}
button:hover{opacity:.9}
.btn-green{background:#12b76a}
.btn-orange{background:#f79009}
.btn-red{background:#f04438}
.btn-gray{background:#667085}
.btn-dark{background:#101828}
.btn-light{background:#eef2f7;color:#344054}
.table-wrap{overflow:auto;border:1px solid #e8edf5;border-radius:14px}
table{width:100%;border-collapse:collapse;font-size:14px;background:#fff}
th,td{padding:13px;border-bottom:1px solid #eef2f7;text-align:center;white-space:nowrap}
th{background:#f8fafc;color:#475467;font-weight:700}
tr:hover td{background:#f9fbff}
.page{display:none}
.page.active{display:block}
.notice{padding:12px 14px;background:#f0f7ff;border:1px solid #cfe6ff;color:#175cd3;border-radius:12px;margin-bottom:16px;font-size:14px}
.status{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:700}
.status:before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor}
.st-0{background:#f2f4f7;color:#667085}
.st-1{background:#e8f2ff;color:#1677ff}
.st-2{background:#fff4e5;color:#f79009}
.st-3{background:#e9f9f0;color:#12b76a}
.st-4{background:#fff1f0;color:#f04438}
.field-list{display:flex;flex-direction:column;gap:10px;margin-bottom:14px}
.field-item{display:grid;grid-template-columns:120px 1fr 42px;gap:10px}
.preview-json{background:#101828;color:#d0d5dd;border-radius:14px;padding:14px;font-size:13px;white-space:pre-wrap;word-break:break-all;min-height:100px}
.upload-box{border:1px dashed #9db7df;background:linear-gradient(180deg,#f8fbff,#f3f7ff);border-radius:16px;padding:18px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;gap:14px}
.upload-info{font-size:14px;color:#475467;line-height:1.7}
.upload-info b{color:#101828}
.upload-btn{display:inline-flex;align-items:center;justify-content:center;background:#1677ff;color:#fff;border-radius:12px;padding:11px 18px;cursor:pointer;font-size:14px;white-space:nowrap;box-shadow:0 8px 18px rgba(22,119,255,.22)}
.upload-btn:hover{opacity:.9}
.upload-btn input{display:none}
.openid-count{display:inline-flex;align-items:center;gap:6px;background:#e9f9f0;color:#12b76a;border-radius:999px;padding:6px 11px;font-size:13px;font-weight:700;margin-bottom:10px}
.hero{background:linear-gradient(135deg,#101828,#123b77 58%,#1677ff);color:#fff;border-radius:24px;padding:28px;position:relative;overflow:hidden}
.hero:after{content:"";position:absolute;right:-70px;top:-70px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.12);animation:pulse 2.4s infinite}
@keyframes pulse{0%{transform:scale(.9);opacity:.5}50%{transform:scale(1.15);opacity:1}100%{transform:scale(.9);opacity:.5}}
.big-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:18px}
.big-stat{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);border-radius:18px;padding:18px}
.big-stat .label{font-size:13px;color:#d9e6ff}
.big-stat .num{font-size:36px;font-weight:900;margin-top:8px;letter-spacing:-1px}
.ring-wrap{display:flex;align-items:center;gap:26px;margin-top:22px}
.ring{width:150px;height:150px;border-radius:50%;background:conic-gradient(#12b76a 0deg,#233b61 0deg);display:flex;align-items:center;justify-content:center;box-shadow:0 0 40px rgba(18,183,106,.25)}
.ring-inner{width:112px;height:112px;border-radius:50%;background:#101828;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:900}
.wave{height:18px;background:linear-gradient(90deg,#12b76a,#1677ff,#12b76a);background-size:200% 100%;border-radius:999px;animation:wave 1.4s linear infinite;overflow:hidden;margin-top:14px}
@keyframes wave{0%{background-position:0 0}100%{background-position:200% 0}}
.mini-text{font-size:14px;color:#d9e6ff;line-height:1.8}
@media(max-width:1000px){
  .sidebar{position:relative;width:100%;height:auto}
  .app{display:block}
  .main{margin-left:0;width:100%}
  .grid,.form-row,.big-stats{grid-template-columns:1fr}
  .content{padding:14px}
  .upload-box{align-items:flex-start;flex-direction:column}
}
</style>
</head>
<body>

<div class="app">
  <aside class="sidebar">
    <div class="logo">
      WxPush Admin
      <span>小程序订阅消息推送系统</span>
    </div>
    <div class="nav">
      <button class="active" onclick="showPage('dashboard',this)">控制台</button>
      <button onclick="showPage('template',this)">模板管理</button>
      <button onclick="showPage('createTask',this)">创建任务</button>
      <button onclick="showPage('taskList',this)">任务列表</button>
      <button onclick="showPage('execute',this)">任务执行</button>
    </div>
  </aside>

  <main class="main">
    <div class="topbar">
      <h1 id="pageTitle">控制台</h1>
      <div class="tips">
        Redis 队列 / Worker 并发 / QPS 控制　
        <a href="logout.php" style="color:#f04438;text-decoration:none;font-weight:700">退出登录</a>
      </div>
    </div>

    <div class="content">

      <section id="dashboard" class="page active">
        <div class="hero">
          <h2 style="margin:0;font-size:22px">实时推送监控</h2>
          <div class="ring-wrap">
            <div class="ring" id="dash_ring">
              <div class="ring-inner" id="dash_percent">0%</div>
            </div>
            <div class="mini-text" id="progress_text">暂无任务执行</div>
          </div>
          <div class="wave"></div>
          <div class="big-stats">
            <div class="big-stat"><div class="label">任务ID</div><div class="num" id="dash_task_id">-</div></div>
            <div class="big-stat"><div class="label">总数</div><div class="num" id="dash_total">0</div></div>
            <div class="big-stat"><div class="label">成功</div><div class="num" id="dash_success">0</div></div>
            <div class="big-stat"><div class="label">失败</div><div class="num" id="dash_fail">0</div></div>
          </div>
        </div>

        <div class="card" style="margin-top:20px">
          <h2>快捷控制</h2>
          <div class="form-row">
            <input id="task_id" placeholder="输入任务ID">
            <input id="current_status" placeholder="当前状态" readonly>
          </div>
          <div class="btns">
            <button class="btn-green" onclick="startTask()">开始</button>
            <button class="btn-orange" onclick="pauseTask()">暂停</button>
            <button onclick="resumeTask()">继续</button>
            <button class="btn-red" onclick="stopTask()">停止</button>
            <button class="btn-gray" onclick="loadProgress()">刷新</button>
          </div>
        </div>
      </section>

      <section id="template" class="page">
        <div class="grid">
          <div class="card">
            <h2 id="template_form_title">创建模板</h2>
            <div class="card-desc">不用手写 JSON，填写字段名和值后自动生成。</div>

            <input type="hidden" id="tpl_edit_id">

            <div class="form-row">
              <input id="tpl_title" placeholder="模板名称">
              <input id="tpl_id" placeholder="微信模板ID">
            </div>

            <div class="form-row">
              <select id="tpl_type">
                <option value="1">一次性订阅</option>
                <option value="2">长期订阅</option>
              </select>
              <input id="tpl_page" placeholder="跳转页面 pages/index/index">
            </div>

            <div class="notice">字段名示例：thing1、time2、character_string3、number4、phrase5</div>

            <div class="field-list" id="tpl_fields"></div>

            <div class="btns" style="margin-bottom:14px">
              <button class="btn-light" onclick="addTplField()">添加字段</button>
              <button class="btn-dark" onclick="buildJsonPreview()">生成预览</button>
            </div>

            <div class="preview-json" id="json_preview">{}</div>

            <div class="form-row" style="margin-top:14px">
              <input id="test_openid" placeholder="测试 openid">
              <select id="test_template_db_id"></select>
            </div>

            <div class="btns">
              <button onclick="saveOrUpdateTemplate()">保存模板</button>
              <button class="btn-green" onclick="testSend()">测试发送</button>
              <button class="btn-gray" onclick="resetTemplateForm()">新建模板</button>
            </div>
          </div>

          <div class="card">
            <h2>模板列表</h2>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>名称</th>
                    <th>类型</th>
                    <th>模板ID</th>
                    <th>操作</th>
                  </tr>
                </thead>
                <tbody id="template_list"></tbody>
              </table>
            </div>
          </div>
        </div>
      </section>

      <section id="createTask" class="page">
        <div class="card">
          <h2>创建群发任务</h2>
          <div class="notice">可上传 txt，也可直接在文本框粘贴/编辑 openid，一行一个。</div>

          <div class="form-row">
            <input id="task_title" placeholder="任务名称">
            <select id="template_db_id"></select>
          </div>

          <div class="form-row">
            <input id="qps" value="20" placeholder="总QPS">
            <input id="workers" value="5" placeholder="Worker数量">
          </div>

          <div class="upload-box">
            <div class="upload-info">
              <b>导入 openid TXT</b><br>
              支持 .txt 文件，一行一个 openid，导入后会自动填充到下方文本框
            </div>
            <label class="upload-btn">
              选择 TXT 文件
              <input type="file" id="openid_file" accept=".txt,text/plain" onchange="readOpenidTxt(this)">
            </label>
          </div>

          <div class="openid-count" id="openid_count">当前 openid：0 个</div>

          <textarea id="openid_text" placeholder="也可以直接粘贴 openid，一行一个" oninput="updateOpenidCount()"></textarea>

          <div class="btns" style="margin-top:14px">
            <button onclick="createTask()">创建任务</button>
            <button class="btn-gray" onclick="clearOpenids()">清空 openid</button>
            <button class="btn-light" onclick="formatOpenids()">去重整理</button>
          </div>
        </div>
      </section>

      <section id="taskList" class="page">
        <div class="card">
          <h2>任务列表</h2>
          <div class="btns" style="margin-bottom:14px">
            <button class="btn-gray" onclick="loadTasks()">刷新任务列表</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>任务</th>
                  <th>总数</th>
                  <th>成功</th>
                  <th>失败</th>
                  <th>QPS</th>
                  <th>Worker</th>
                  <th>状态</th>
                  <th>创建时间</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody id="task_list"></tbody>
            </table>
          </div>
        </div>
      </section>

      <section id="execute" class="page">
        <div class="hero">
          <h2 style="margin:0;font-size:22px">任务执行中心</h2>

          <div class="form-row" style="margin-top:18px">
            <input id="execute_task_id" placeholder="任务ID">
            <input id="execute_status" placeholder="状态" readonly>
          </div>

          <div class="btns">
            <button class="btn-green" onclick="syncExecuteId();startTask()">开始</button>
            <button class="btn-orange" onclick="syncExecuteId();pauseTask()">暂停</button>
            <button onclick="syncExecuteId();resumeTask()">继续</button>
            <button class="btn-red" onclick="syncExecuteId();stopTask()">停止</button>
            <button class="btn-gray" onclick="syncExecuteId();loadProgress()">刷新</button>
          </div>

          <div class="ring-wrap">
            <div class="ring" id="exec_ring">
              <div class="ring-inner" id="exec_percent">0%</div>
            </div>
            <div class="mini-text" id="execute_progress_text">暂无任务</div>
          </div>

          <div class="wave"></div>

          <div class="big-stats">
            <div class="big-stat"><div class="label">已处理</div><div class="num" id="exec_done">0</div></div>
            <div class="big-stat"><div class="label">剩余</div><div class="num" id="exec_left">0</div></div>
            <div class="big-stat"><div class="label">成功</div><div class="num" id="exec_success">0</div></div>
            <div class="big-stat"><div class="label">失败</div><div class="num" id="exec_fail">0</div></div>
          </div>
        </div>

        <div class="card" style="margin-top:20px">
          <h2>失败日志</h2>
          <div class="btns" style="margin-bottom:14px">
            <button class="btn-gray" onclick="loadFailLog()">查看失败日志</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>openid</th>
                  <th>错误码</th>
                  <th>错误信息</th>
                  <th>重试次数</th>
                  <th>时间</th>
                </tr>
              </thead>
              <tbody id="fail_log"></tbody>
            </table>
          </div>
        </div>
      </section>

    </div>
  </main>
</div>

<script>
function post(url, data) {
  return fetch(url, { method: 'POST', body: data }).then(res => res.json());
}
function get(url) {
  return fetch(url).then(res => res.json());
}
function toast(msg) {
  alert(msg);
}

function showPage(id, btn) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.getElementById(id).classList.add('active');

  document.querySelectorAll('.nav button').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  const titles = {
    dashboard: '控制台',
    template: '模板管理',
    createTask: '创建任务',
    taskList: '任务列表',
    execute: '任务执行'
  };

  document.getElementById('pageTitle').innerText = titles[id] || '控制台';

  if (id === 'taskList') loadTasks();
  if (id === 'template') loadTemplates();
}

function readOpenidTxt(input) {
  const file = input.files[0];

  if (!file) return;

  if (!file.name.toLowerCase().endsWith('.txt')) {
    toast('只能上传 txt 文件');
    input.value = '';
    return;
  }

  const reader = new FileReader();

  reader.onload = function(e) {
    const text = e.target.result || '';
    const oldText = document.getElementById('openid_text').value || '';

    const list = parseOpenids(oldText + '\n' + text);
    document.getElementById('openid_text').value = list.join('\n');

    updateOpenidCount();

    toast('导入成功：' + list.length + ' 个 openid');
  };

  reader.readAsText(file, 'utf-8');
}

function parseOpenids(text) {
  return [...new Set(
    String(text || '')
      .split(/[\r\n,，\s]+/)
      .map(v => v.trim())
      .filter(Boolean)
  )];
}

function updateOpenidCount() {
  const list = parseOpenids(document.getElementById('openid_text').value);
  document.getElementById('openid_count').innerText = '当前 openid：' + list.length + ' 个';
}

function formatOpenids() {
  const list = parseOpenids(document.getElementById('openid_text').value);
  document.getElementById('openid_text').value = list.join('\n');
  updateOpenidCount();
  toast('已去重整理：' + list.length + ' 个 openid');
}

function clearOpenids() {
  document.getElementById('openid_text').value = '';
  document.getElementById('openid_file').value = '';
  updateOpenidCount();
}

function addTplField(key = '', value = '') {
  const box = document.getElementById('tpl_fields');
  const div = document.createElement('div');
  div.className = 'field-item';
  div.innerHTML = `
    <input class="tpl-key" placeholder="字段名 thing1" value="${escapeHtml(key)}">
    <input class="tpl-value" placeholder="字段值" value="${escapeHtml(value)}">
    <button class="btn-red" onclick="this.parentNode.remove();buildJsonPreview()">×</button>
  `;
  box.appendChild(div);
}

function buildJsonPreview() {
  const keys = document.querySelectorAll('.tpl-key');
  const values = document.querySelectorAll('.tpl-value');

  const obj = {};

  keys.forEach((k, i) => {
    const key = k.value.trim();
    const value = values[i].value.trim();

    if (key) {
      obj[key] = { value };
    }
  });

  document.getElementById('json_preview').innerText = JSON.stringify(obj, null, 2);
  return obj;
}

function resetTemplateForm() {
  document.getElementById('template_form_title').innerText = '创建模板';
  document.getElementById('tpl_edit_id').value = '';
  document.getElementById('tpl_title').value = '';
  document.getElementById('tpl_id').value = '';
  document.getElementById('tpl_type').value = '1';
  document.getElementById('tpl_page').value = '';
  document.getElementById('tpl_fields').innerHTML = '';
  document.getElementById('json_preview').innerText = '{}';
  addTplField();
}

function saveOrUpdateTemplate() {
  const obj = buildJsonPreview();
  const id = document.getElementById('tpl_edit_id').value;

  const fd = new FormData();
  fd.append('id', id);
  fd.append('title', document.getElementById('tpl_title').value);
  fd.append('template_id', document.getElementById('tpl_id').value);
  fd.append('template_type', document.getElementById('tpl_type').value);
  fd.append('page', document.getElementById('tpl_page').value);
  fd.append('data_json', JSON.stringify(obj));

  const url = id ? './api.php?action=update_template' : './api.php?action=save_template';

  post(url, fd).then(res => {
    toast(res.msg);
    loadTemplates();
  });
}

function editTemplate(id) {
  get('./api.php?action=template_detail&id=' + id).then(res => {
    if (res.code !== 200) {
      toast(res.msg);
      return;
    }

    const d = res.data;

    document.getElementById('template_form_title').innerText = '编辑模板 #' + d.id;
    document.getElementById('tpl_edit_id').value = d.id;
    document.getElementById('tpl_title').value = d.title;
    document.getElementById('tpl_id').value = d.template_id;
    document.getElementById('tpl_type').value = d.template_type;
    document.getElementById('tpl_page').value = d.page || '';
    document.getElementById('tpl_fields').innerHTML = '';

    let json = {};
    try {
      json = JSON.parse(d.data_json || '{}');
    } catch(e) {}

    Object.keys(json).forEach(key => {
      addTplField(key, json[key].value || '');
    });

    if (!Object.keys(json).length) {
      addTplField();
    }

    buildJsonPreview();
  });
}

function loadTemplates() {
  get('./api.php?action=template_list').then(res => {
    const selects = [
      document.getElementById('template_db_id'),
      document.getElementById('test_template_db_id')
    ];

    selects.forEach(select => select.innerHTML = '');

    const tbody = document.getElementById('template_list');
    tbody.innerHTML = '';

    res.data.forEach(item => {
      selects.forEach(select => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.innerText = item.title + ' - ' + item.template_id;
        select.appendChild(opt);
      });

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${item.id}</td>
        <td>${escapeHtml(item.title)}</td>
        <td>${item.template_type == 2 ? '长期订阅' : '一次性订阅'}</td>
        <td>${escapeHtml(item.template_id)}</td>
        <td>
          <button onclick="editTemplate(${item.id})">编辑</button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  });
}

function testSend() {
  const openid = document.getElementById('test_openid').value.trim();
  const templateDbId = document.getElementById('test_template_db_id').value;

  if (!openid) {
    toast('请输入测试openid');
    return;
  }

  localStorage.setItem('wepush_test_openid', openid);

  const fd = new FormData();
  fd.append('openid', openid);
  fd.append('template_db_id', templateDbId);

  post('./api.php?action=test_send', fd).then(res => {
    console.log(res);
    toast(res.code === 200 ? '测试发送成功' : (res.msg || '发送失败'));
  });
}

function createTask() {
  formatOpenids();

  const fd = new FormData();
  fd.append('title', document.getElementById('task_title').value);
  fd.append('template_db_id', document.getElementById('template_db_id').value);
  fd.append('qps', document.getElementById('qps').value);
  fd.append('workers', document.getElementById('workers').value);
  fd.append('openid_text', document.getElementById('openid_text').value);

  post('./api.php?action=create_task', fd).then(res => {
    toast(res.msg);
    if (res.code === 200) {
      document.getElementById('task_id').value = res.data.task_id;
      document.getElementById('execute_task_id').value = res.data.task_id;
    }
    loadTasks();
  });
}

function startTask() {
  const fd = new FormData();
  fd.append('task_id', document.getElementById('task_id').value);

  post('./api.php?action=start', fd).then(res => {
    toast(res.msg);
    loadProgress();
    loadTasks();
  });
}

function pauseTask() {
  const fd = new FormData();
  fd.append('task_id', document.getElementById('task_id').value);

  post('./api.php?action=pause', fd).then(res => {
    toast(res.msg);
    loadProgress();
    loadTasks();
  });
}

function resumeTask() {
  const fd = new FormData();
  fd.append('task_id', document.getElementById('task_id').value);

  post('./api.php?action=resume', fd).then(res => {
    toast(res.msg);
    loadProgress();
    loadTasks();
  });
}

function stopTask() {
  const fd = new FormData();
  fd.append('task_id', document.getElementById('task_id').value);

  post('./api.php?action=stop', fd).then(res => {
    toast(res.msg);
    loadProgress();
    loadTasks();
  });
}

function deleteTask(id) {
  if (!confirm('确定删除任务 #' + id + ' 吗？删除后日志也会删除。')) return;

  const fd = new FormData();
  fd.append('task_id', id);

  post('./api.php?action=delete_task', fd).then(res => {
    toast(res.msg);
    loadTasks();
  });
}

function loadProgress() {
  const taskId = document.getElementById('task_id').value;
  if (!taskId) return;

  get('./api.php?action=progress&task_id=' + taskId).then(res => {
    if (res.code !== 200) return;

    const d = res.data;
    const text = `进度：${d.percent}% | 总数：${d.total} | 已处理：${d.done} | 成功：${d.success} | 失败：${d.fail} | 剩余：${d.left} | 状态：${d.status}`;

    document.getElementById('current_status').value = d.status;
    document.getElementById('execute_status').value = d.status;

    document.getElementById('progress_text').innerText = text;
    document.getElementById('execute_progress_text').innerText = text;

    document.getElementById('dash_task_id').innerText = d.task_id;
    document.getElementById('dash_total').innerText = d.total;
    document.getElementById('dash_success').innerText = d.success;
    document.getElementById('dash_fail').innerText = d.fail;
    document.getElementById('dash_percent').innerText = d.percent + '%';

    document.getElementById('exec_percent').innerText = d.percent + '%';
    document.getElementById('exec_done').innerText = d.done;
    document.getElementById('exec_left').innerText = d.left;
    document.getElementById('exec_success').innerText = d.success;
    document.getElementById('exec_fail').innerText = d.fail;

    updateRing('dash_ring', d.percent);
    updateRing('exec_ring', d.percent);
  });
}

function updateRing(id, percent) {
  const deg = Math.min(100, Number(percent || 0)) * 3.6;
  document.getElementById(id).style.background = `conic-gradient(#12b76a 0deg, #12b76a ${deg}deg, rgba(255,255,255,.18) ${deg}deg, rgba(255,255,255,.18) 360deg)`;
}

function loadTasks() {
  get('./api.php?action=task_list').then(res => {
    const tbody = document.getElementById('task_list');
    tbody.innerHTML = '';

    const map = {
      0: ['待开始','st-0'],
      1: ['发送中','st-1'],
      2: ['暂停','st-2'],
      3: ['完成','st-3'],
      4: ['停止','st-4']
    };

    res.data.forEach(item => {
      const st = map[item.status] || [item.status, 'st-0'];

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${item.id}</td>
        <td>${escapeHtml(item.title)}</td>
        <td>${item.total}</td>
        <td>${item.success}</td>
        <td>${item.fail}</td>
        <td>${item.qps}</td>
        <td>${item.workers}</td>
        <td><span class="status ${st[1]}">${st[0]}</span></td>
        <td>${item.create_time || ''}</td>
        <td>
          <button class="btn-red" onclick="deleteTask(${item.id})">删除</button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  });
}

function syncExecuteId() {
  const id = document.getElementById('execute_task_id').value;
  document.getElementById('task_id').value = id;
}

function loadFailLog() {
  syncExecuteId();

  const taskId = document.getElementById('task_id').value;
  if (!taskId) return toast('请输入任务ID');

  get('./api.php?action=fail_log&task_id=' + taskId).then(res => {
    const tbody = document.getElementById('fail_log');
    tbody.innerHTML = '';

    res.data.forEach(item => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${escapeHtml(item.openid)}</td>
        <td>${item.errcode}</td>
        <td>${escapeHtml(item.errmsg)}</td>
        <td>${item.retry}</td>
        <td>${item.send_time}</td>
      `;
      tbody.appendChild(tr);
    });
  });
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, s => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
  }[s]));
}

document.addEventListener('input', e => {
  if (e.target.classList.contains('tpl-key') || e.target.classList.contains('tpl-value')) {
    buildJsonPreview();
  }

  if (e.target.id === 'test_openid') {
    localStorage.setItem('wepush_test_openid', e.target.value.trim());
  }
});

setInterval(() => {
  const taskId = document.getElementById('task_id').value;
  if (taskId) loadProgress();
}, 1000);

document.getElementById('test_openid').value = localStorage.getItem('wepush_test_openid') || '';

addTplField();
loadTemplates();
loadTasks();
updateOpenidCount();
</script>

</body>
</html>