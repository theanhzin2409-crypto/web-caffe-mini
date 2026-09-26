CAFFE MINI - WEBSITE QUẢN LÝ BÁN HÀNG HOÀN CHỈNH
==================================================

Kiến trúc:
- Frontend: HTML5, CSS3, JavaScript, Bootstrap 5
- Backend: PHP 8+
- CSDL: MySQL/MariaDB (XAMPP)
- Xác thực: PHP Session + mật khẩu băm password_hash()
- Bảo vệ thao tác ghi: CSRF token

CÁC CHỨC NĂNG ĐÃ HOÀN THIỆN
- Khách xem, tìm kiếm và lọc menu.
- Đăng ký/đăng nhập tài khoản.
- Phân quyền Admin / Nhân viên ở cả giao diện và API.
- Admin quản lý người dùng: thêm, sửa, khóa/mở, xóa.
- Admin quản lý thực đơn: thêm, sửa, ẩn/xóa món.
- Nhân viên tạo đơn, chỉnh số lượng, lưu đơn chờ thanh toán.
- Mở lại đơn chờ để chỉnh sửa và thanh toán sau.
- Thanh toán tiền mặt hoặc tạo QR chuyển khoản VietQR.
- Ghi nhận đánh giá sau khi đơn đã thanh toán.
- Admin quản lý đơn hàng, đánh giá và báo cáo doanh thu.
- Người dùng cập nhật thông tin cá nhân và mật khẩu.
- Dữ liệu nghiệp vụ được lưu trong CSDL caffe_mini.

CẤU TRÚC CSDL CỐT LÕI
- users
- drinks
- orders
- order_details
- reviews

CÀI ĐẶT TRÊN XAMPP
1. Bật Apache và MySQL trong XAMPP.
2. Chép toàn bộ thư mục này vào:
   C:\xampp\htdocs\caffe_mini
3. Mở phpMyAdmin: http://localhost/phpmyadmin
4. Chọn database caffe_mini (hoặc tạo mới nếu chưa có).
5. Import file:
   database\caffe_mini.sql
   Lưu ý: file SQL này tạo lại 5 bảng, vì vậy sẽ xóa dữ liệu thử nghiệm cũ trong các bảng đó.
6. Kiểm tra kết nối:
   http://localhost/caffe_mini/api/health.php
   Kết quả đúng sẽ có "ok": true và "connected": true.
7. Mở hệ thống:
   http://localhost/caffe_mini/

CẤU HÌNH CSDL
Mặc định XAMPP:
- host: 127.0.0.1
- database: caffe_mini
- user: root
- password: để trống
Nếu máy dùng cấu hình khác, sửa api/db.php.

GHI CHÚ
- Cấu hình tài khoản ngân hàng dùng để tạo VietQR được lưu trên trình duyệt của máy bán hàng; đơn hàng và dữ liệu quản lý vẫn lưu trong MySQL.
- Mã QR dùng dịch vụ ảnh VietQR nên cần Internet để tải hình QR.
- Không mở website bằng cách double-click file HTML. Hãy luôn chạy qua http://localhost/caffe_mini/ để PHP và CSDL hoạt động.
