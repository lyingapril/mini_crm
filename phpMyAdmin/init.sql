-- 1. 用户表（权限控制）
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '加密密码',
  `role` enum('admin','user') DEFAULT 'user' COMMENT '角色：管理员/普通用户',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. 客户表（核心数据，含敏感信息加密字段）
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '客户姓名',
  `phone` varchar(255) NOT NULL COMMENT '加密手机号',
  `email` varchar(255) NOT NULL COMMENT '加密邮箱',
  `company` varchar(100) DEFAULT NULL COMMENT '所属公司',
  `status` enum('active','inactive') DEFAULT 'active' COMMENT '客户状态',
  `created_by` int(11) NOT NULL COMMENT '创建人（关联users表id）',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`) -- 索引优化查询
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 插入测试数据（管理员账号：admin，密码：123456，已加密）
INSERT INTO `users` (`username`, `password`, `role`) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');