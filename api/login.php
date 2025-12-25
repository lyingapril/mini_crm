<?php
// 引入核心类和配置（注意路径：根据你的目录结构调整）
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../core/Database.php';

// 设置响应头（跨域+JSON格式）
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// 接收前端参数
$params = json_decode(file_get_contents("php://input"), true);
$username = $params["username"] ?? "";
$password = $params["password"] ?? "";

// 参数校验
if (empty($username) || empty($password)) {
    echo json_encode(["code" => 400, "msg" => "用户名和密码不能为空"]);
    exit;
}

// 实例化数据库类（OOP核心：调用封装好的方法）
$db = new Database();

// 查询用户（预处理查询，防止SQL注入）
$sql = "SELECT id, username, password, role FROM users WHERE username = ?";
$userList = $db->query($sql, [$username]);
$db->close(); // 关闭连接

// 验证用户和密码
if (empty($userList)) {
    echo json_encode(["code" => 401, "msg" => "账号不存在"]);
    exit;
}
$user = $userList[0];
if (!password_verify($password, $user["password"])) {
    echo json_encode(["code" => 401, "msg" => "密码错误"]);
    exit;
}

// 返回结果（隐藏密码）
unset($user["password"]);
echo json_encode([
    "code" => 200,
    "msg" => "登录成功",
    "data" => $user
]);
?>