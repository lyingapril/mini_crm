<?php
/**
 * 数据库操作基类（面向对象封装，复用核心逻辑）
 */
class Database {
    private $host;
    private $username;
    private $password;
    private $dbname;
    private $charset;
    private $conn; // 数据库连接句柄

    // 构造函数：初始化配置并连接数据库
    public function __construct() {
        // 加载数据库配置
        $config = require_once __DIR__ . '/../config/db_config.php';
        $this->host = $config['host'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->dbname = $config['dbname'];
        $this->charset = $config['charset'];

        // 建立数据库连接
        $this->connect();
    }

    // 连接数据库（私有方法，仅内部调用）
    private function connect() {
        $this->conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->dbname
        );

        // 连接错误处理
        if ($this->conn->connect_error) {
            $this->responseError(500, "数据库连接失败：" . $this->conn->connect_error);
        }

        // 设置字符编码
        $this->conn->set_charset($this->charset);
    }

    // 预处理查询（核心方法：防止SQL注入，安全合规）
    public function query($sql, $params = [], $types = '') {
        // 准备SQL语句
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            $this->responseError(500, "SQL预处理失败：" . $this->conn->error);
        }

        // 绑定参数（若有参数）
        if (!empty($params)) {
            // 自动生成参数类型（默认字符串s，可手动指定types参数覆盖）
            if (empty($types)) {
                $types = str_repeat('s', count($params));
            }
            $stmt->bind_param($types, ...$params);
        }

        // 执行SQL
        if (!$stmt->execute()) {
            $this->responseError(500, "SQL执行失败：" . $stmt->error);
        }

        // 获取结果（查询类SQL返回结果集，增删改返回影响行数）
        $result = $stmt->get_result();
        if ($result) {
            // 查询：返回关联数组
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $data;
        } else {
            // 增删改：返回影响行数
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            return $affectedRows;
        }
    }

    // 获取最后插入的ID（新增数据时用）
    public function getLastInsertId() {
        return $this->conn->insert_id;
    }

    // 关闭数据库连接
    public function close() {
        $this->conn->close();
    }

    // 统一错误响应（复用，避免重复代码）
    private function responseError($code, $msg) {
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(["code" => $code, "msg" => $msg]);
        exit;
    }
}