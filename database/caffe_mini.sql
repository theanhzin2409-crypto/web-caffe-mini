CREATE DATABASE IF NOT EXISTS caffe_mini CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE caffe_mini;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS drinks;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users(
 user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(50) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 full_name VARCHAR(100) NOT NULL,
 email VARCHAR(100) NOT NULL UNIQUE,
 phone VARCHAR(20),
 role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE drinks(
 drink_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 drink_name VARCHAR(100) NOT NULL,
 category VARCHAR(50) NOT NULL,
 price DECIMAL(12,0) NOT NULL,
 image VARCHAR(255),
 description TEXT,
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_drinks_category(category), INDEX idx_drinks_active(active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders(
 order_id VARCHAR(30) PRIMARY KEY,
 customer_name VARCHAR(100),
 staff_id INT UNSIGNED NULL,
 total_amount DECIMAL(12,0) NOT NULL DEFAULT 0,
 payment_method ENUM('Tiền mặt','Chuyển khoản') NULL,
 status ENUM('Chờ thanh toán','Đã thanh toán','Hủy') NOT NULL DEFAULT 'Chờ thanh toán',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 paid_at DATETIME NULL,
 CONSTRAINT fk_orders_staff FOREIGN KEY(staff_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE SET NULL,
 INDEX idx_orders_status(status), INDEX idx_orders_created(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_details(
 detail_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id VARCHAR(30) NOT NULL,
 drink_id INT UNSIGNED NOT NULL,
 quantity INT UNSIGNED NOT NULL,
 unit_price DECIMAL(12,0) NOT NULL,
 sub_total DECIMAL(12,0) NOT NULL,
 CONSTRAINT fk_od_order FOREIGN KEY(order_id) REFERENCES orders(order_id) ON UPDATE CASCADE ON DELETE CASCADE,
 CONSTRAINT fk_od_drink FOREIGN KEY(drink_id) REFERENCES drinks(drink_id) ON UPDATE CASCADE ON DELETE RESTRICT,
 INDEX idx_od_order(order_id), INDEX idx_od_drink(drink_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews(
 review_id VARCHAR(30) PRIMARY KEY,
 order_id VARCHAR(30) NOT NULL UNIQUE,
 customer_name VARCHAR(100),
 rating TINYINT UNSIGNED NOT NULL,
 content TEXT,
 entered_by INT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT chk_rating CHECK(rating BETWEEN 1 AND 5),
 CONSTRAINT fk_reviews_order FOREIGN KEY(order_id) REFERENCES orders(order_id) ON UPDATE CASCADE ON DELETE CASCADE,
 CONSTRAINT fk_reviews_user FOREIGN KEY(entered_by) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mật khẩu đã được băm bằng password_hash() của PHP.
INSERT INTO users(username,password,full_name,email,phone,role,active) VALUES
('admin','$2y$12$AYxWzR1SwNM43HOlRi2uheJEfndaTkBI.iDBWg2VGjse/WzbHXaaG','Quản trị viên','admin@caffemini.vn','0900000000','admin',1),
('nhanvien1','$2y$12$dyAkFOBUBEAJ1mtfay2Anea/bY7aLFYtSnZomwsizlZW9cjjjLQdK','Nguyễn Văn Nhân','nhanvien1@caffemini.vn','0911111111','staff',1);

INSERT INTO drinks(drink_name,category,price,image,description,active) VALUES
('Cà phê đen','Cà phê',25000,'media/Ca_phe_den.png','Cà phê đen đậm vị, pha tại quán.',1),
('Cà phê sữa','Cà phê',30000,'media/Ca_phe_sua.png','Cà phê sữa thơm, vị cân bằng.',1),
('Bạc xỉu','Cà phê',32000,'media/Bac_xiu.png','Bạc xỉu béo nhẹ, dễ uống.',1),
('Trà đào','Trà',35000,'media/Tra_dao.png','Trà đào thanh mát, thơm dịu.',1),
('Trà sữa trân châu','Trà sữa',39000,'media/Tra_sua_tran_chau.png','Trà sữa kèm trân châu đường đen.',1),
('Nước cam','Nước ép',35000,'media/Nuoc_cam.png','Nước cam tươi, dùng lạnh.',1),
('Nước ép dứa','Nước ép',35000,'media/Nuoc_ep_dua.png','Dứa tươi ép nguyên chất, vị chua ngọt dễ uống.',1),
('Nước ép dưa hấu','Nước ép',32000,'media/Nuoc_ep_dua_hau.png','Dưa hấu tươi ép mát lạnh, thanh ngọt tự nhiên.',1),
('Nước ép ổi','Nước ép',35000,'media/Nuoc_ep_oi.png','Nước ép ổi thơm mát, vị ngọt dịu.',1),
('Nước ép chanh leo','Nước ép',35000,'media/Nuoc_ep_chanh_leo.png','Chanh leo tươi pha cân bằng vị chua ngọt.',1),
('Nước ép táo','Nước ép',40000,'media/Nuoc_ep_tao.png','Táo tươi ép nguyên chất, vị thanh nhẹ.',1),
('Bơ già dừa non','Sinh tố',45000,'media/Bo_gia_dua_non.png','Sinh tố bơ già dừa non béo mịn, thơm ngậy và mát lạnh.',1),
('Chanh tươi đá','Nước giải khát',25000,'media/Tra_chanh.png','Chanh tươi pha lạnh, chua ngọt và giải khát.',1),
('Cacao sữa đá','Nước giải khát',35000,'media/Cacao.png','Cacao thơm béo pha cùng sữa và đá lạnh.',1),
('Matcha latte','Trà sữa',40000,'media/Matcha_latte.png','Matcha thơm nhẹ kết hợp sữa béo, dùng nóng hoặc lạnh.',1),
('Khô bò','Đồ ăn kèm',35000,'media/Kho_bo.png','Khô bò cay nhẹ, phù hợp dùng kèm đồ uống.',1),
('Hướng dương','Đồ ăn kèm',20000,'media/Huong_duong.png','Hạt hướng dương rang thơm, dùng ăn nhẹ tại quán.',1),
('Khô gà lá chanh','Đồ ăn kèm',30000,'media/Kho_ga.png','Khô gà lá chanh thơm cay nhẹ, ăn kèm tiện lợi.',1);
