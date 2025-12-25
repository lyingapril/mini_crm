<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../utils/EncryptUtil.php';

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// 接收参数
$status = $_GET["status"] ?? "";
$page = (int)($_GET["page"] ?? 1);
$pageSize = (int)($_GET["pageSize"] ?? 10);
$createdBy = (int)($_GET["createdBy"] ?? 0);
$offset = ($page - 1) * $pageSize;

// 参数校验
if ($createdBy <= 0) {
    echo json_encode(["code" => 400, "msg" => "创建人ID不能为空"]);
    exit;
}

$db = new Database();

// 构建查询条件（动态拼接，安全可控）
$sql = "SELECT id, name, company, status, created_at FROM customers WHERE created_by = ?";
$params = [$createdBy];
$types = "i";

if (!empty($status)) {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

// 分页+排序（之前加的索引优化保留）
$sql .= " ORDER BY created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $pageSize;
$types .= "ii";

// 查询客户列表
$customerList = $db->query($sql, $params, $types);

// 查询总数（用于分页计算）
$countSql = "SELECT COUNT(*) as total FROM customers WHERE created_by = ?" . (!empty($status) ? " AND status = ?" : "");
$countParams = [$createdBy];
$countTypes = "i";
if (!empty($status)) {
    $countParams[] = $status;
    $countTypes .= "s";
}
$totalList = $db->query($countSql, $countParams, $countTypes);
$total = $totalList[0]["total"];

$db->close();

// 返回结果（无需解密，前端按需解密敏感数据）
echo json_encode([
    "code" => 200,
    "data" => [
        "list" => $customerList,
        "total" => $total,
        "page" => $page,
        "pageSize" => $pageSize,
        "totalPages" => ceil($total / $pageSize)
    ]
]);
?>