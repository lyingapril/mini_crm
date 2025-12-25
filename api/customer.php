<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../utils/EncryptUtil.php';

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT");
header("Access-Control-Allow-Headers: Content-Type");

$method = $_SERVER["REQUEST_METHOD"];
$params = json_decode(file_get_contents("php://input"), true);

// 实例化工具类
$db = new Database();

// 新增客户（POST）
if ($method === "POST") {
    // 接收并校验参数
    $name = $params["name"] ?? "";
    $phone = $params["phone"] ?? "";
    $email = $params["email"] ?? "";
    $company = $params["company"] ?? "";
    $status = $params["status"] ?? "active";
    $createdBy = (int)($params["createdBy"] ?? 0);

    if (empty($name) || empty($phone) || empty($email) || $createdBy <= 0) {
        echo json_encode(["code" => 400, "msg" => "姓名、手机号、邮箱、创建人ID不能为空"]);
        exit;
    }

    // 加密敏感数据（调用工具类，逻辑统一）
    $encryptedPhone = EncryptUtil::encrypt($phone);
    $encryptedEmail = EncryptUtil::encrypt($email);

    // 插入数据
    $sql = "INSERT INTO customers (name, phone, email, company, status, created_by) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $params = [$name, $encryptedPhone, $encryptedEmail, $company, $status, $createdBy];
    $types = "sssssi";

    $affectedRows = $db->query($sql, $params, $types);
    $newId = $db->getLastInsertId(); // 获取新增ID
    $db->close();

    if ($affectedRows > 0) {
        echo json_encode(["code" => 200, "msg" => "客户新增成功", "data" => ["id" => $newId]]);
    } else {
        echo json_encode(["code" => 500, "msg" => "新增客户失败"]);
    }
}

// 编辑客户（PUT）
if ($method === "PUT") {
    $id = (int)($params["id"] ?? 0);
    $name = $params["name"] ?? "";
    $phone = $params["phone"] ?? "";
    $email = $params["email"] ?? "";
    $company = $params["company"] ?? "";
    $status = $params["status"] ?? "active";

    // 参数校验
    if ($id <= 0 || empty($name) || empty($phone) || empty($email)) {
        echo json_encode(["code" => 400, "msg" => "ID、姓名、手机号、邮箱不能为空"]);
        exit;
    }

    // 加密敏感数据
    $encryptedPhone = EncryptUtil::encrypt($phone);
    $encryptedEmail = EncryptUtil::encrypt($email);

    // 更新数据
    $sql = "UPDATE customers SET name=?, phone=?, email=?, company=?, status=? WHERE id=?";
    $params = [$name, $encryptedPhone, $encryptedEmail, $company, $status, $id];
    $types = "sssssi";

    $affectedRows = $db->query($sql, $params, $types);
    $db->close();

    if ($affectedRows > 0) {
        echo json_encode(["code" => 200, "msg" => "客户编辑成功"]);
    } else {
        echo json_encode(["code" => 500, "msg" => "编辑客户失败（可能未找到客户或数据无变化）"]);
    }
}
?>