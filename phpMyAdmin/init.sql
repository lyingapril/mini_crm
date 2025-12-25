-- 先创建数据库（确保字符集正确）
CREATE DATABASE IF NOT EXISTS `mini_crm` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `mini_crm`;

-- 1. 用户表（权限控制）
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '加密密码（password_hash生成）',
  `role` enum('admin','user') DEFAULT 'user' COMMENT '角色：管理员/普通用户',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_username` (`username`) -- 新增：用户名唯一，避免重复注册
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户权限表';

-- 2. 客户表（核心数据，含敏感信息加密字段）
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '客户姓名',
  `phone` varbinary(255) NOT NULL COMMENT '加密手机号（VARBINARY存储二进制数据）',
  `email` varbinary(255) NOT NULL COMMENT '加密邮箱（VARBINARY存储二进制数据）',
  `company` varchar(100) DEFAULT '' COMMENT '所属公司', -- 优化：默认空字符串，避免NULL
  `status` enum('active','inactive') DEFAULT 'active' COMMENT '客户状态：活跃/非活跃',
  `created_by` int(11) NOT NULL COMMENT '创建人（关联users表id）',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`) -- 索引：优化按创建人查询
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='客户核心数据表';

-- 插入测试数据（管理员账号：admin，密码：123456）
INSERT INTO `users` (`username`, `password`, `role`) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');