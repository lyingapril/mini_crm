<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../core/Database.php';

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

$db = new Database();

// 1. 客户状态分布统计
$statusSql = "SELECT status, COUNT(*) as count FROM customers GROUP BY status";
$statusData = $db->query($statusSql);

// 2. 近30天新增客户统计
$thirtyDaysAgo = date("Y-m-d H:i:s", strtotime("-30 days"));
$newCustomerSql = "SELECT DATE(created_at) as date, COUNT(*) as count FROM customers 
                   WHERE created_at >= ? GROUP BY DATE(created_at) ORDER BY date ASC";
$newCustomerData = $db->query($newCustomerSql, [$thirtyDaysAgo]);

$db->close();

// 格式化返回结果（便于前端渲染图表）
$formattedStatusData = array_map(function($item) {
    return [
        "name" => $item["status"] === "active" ? "活跃客户" : "非活跃客户",
        "value" => (int)$item["count"]
    ];
}, $statusData);

$formattedNewCustomerData = [
    "dates" => array_column($newCustomerData, "date"),
    "counts" => array_map(function($item) { return (int)$item["count"]; }, $newCustomerData)
];

echo json_encode([
    "code" => 200,
    "data" => [
        "statusDistribution" => $formattedStatusData,
        "newCustomerIn30Days" => $formattedNewCustomerData
    ]
]);
?>