<?php
require __DIR__.'/common.php';

require_role($pdo,['admin']);
check_csrf();

$m = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$d = json_input();

try {

    if ($m === 'POST' || $m === 'PUT') {

        $name   = trim((string)($d['name'] ?? ''));
        $cat    = trim((string)($d['category'] ?? ''));
        $price  = (float)($d['price'] ?? -1);
        $stock  = (int)($d['stock'] ?? 100);
        $image  = trim((string)($d['image'] ?? ''));
        $desc   = trim((string)($d['desc'] ?? ''));
        $active = !empty($d['active']) ? 1 : 0;

        if ($name === '' || $cat === '' || $price < 0 || $stock < 0) {
            respond([
                'ok' => false,
                'message' => 'Thông tin món chưa hợp lệ.'
            ], 422);
        }

        if ($m === 'POST') {

            $s = $pdo->prepare(
                'INSERT INTO drinks
                (drink_name, category, price, image, description, active, stock_quantity)
                VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            $s->execute([
                $name,
                $cat,
                $price,
                $image,
                $desc,
                $active,
                $stock
            ]);

            respond([
                'ok' => true,
                'id' => (int)$pdo->lastInsertId(),
                'message' => 'Đã thêm món.'
            ]);
        }

        $id = (int)($d['id'] ?? 0);

        if ($id <= 0) {
            respond([
                'ok' => false,
                'message' => 'Thiếu mã món.'
            ], 422);
        }

        $s = $pdo->prepare(
            'UPDATE drinks
             SET drink_name=?,
                 category=?,
                 price=?,
                 image=?,
                 description=?,
                 active=?,
                 stock_quantity=?
             WHERE drink_id=?'
        );

        $s->execute([
            $name,
            $cat,
            $price,
            $image,
            $desc,
            $active,
            $stock,
            $id
        ]);

        respond([
            'ok' => true,
            'message' => 'Đã cập nhật món.'
        ]);
    }

    if ($m === 'DELETE') {

        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            respond([
                'ok' => false,
                'message' => 'Thiếu mã món.'
            ], 422);
        }

        $q = $pdo->prepare(
            'SELECT COUNT(*) FROM order_details WHERE drink_id=?'
        );

        $q->execute([$id]);

        if ((int)$q->fetchColumn() > 0) {

            $pdo->prepare(
                'UPDATE drinks SET active=0 WHERE drink_id=?'
            )->execute([$id]);

            respond([
                'ok' => true,
                'message' => 'Món đã phát sinh giao dịch nên được chuyển sang Tạm ẩn để giữ lịch sử hóa đơn.'
            ]);
        }

        $pdo->prepare(
            'DELETE FROM drinks WHERE drink_id=?'
        )->execute([$id]);

        respond([
            'ok' => true,
            'message' => 'Đã xóa món.'
        ]);
    }

    respond([
        'ok' => false,
        'message' => 'Phương thức không được hỗ trợ.'
    ], 405);

} catch (Throwable $e) {

    error_log($e->__toString());

    respond([
        'ok' => false,
        'message' => 'Không thể cập nhật thực đơn.'
    ], 500);
}