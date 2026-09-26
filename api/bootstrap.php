<?php
require __DIR__ . '/common.php';

try {
    $user = current_user($pdo);

    $productSql = '
        SELECT
            d.drink_id AS id,
            d.drink_name AS name,
            d.category,
            d.price,
            d.image,
            d.description AS `desc`,
            d.active,
            d.stock_quantity AS stock,
            COALESCE((
                SELECT SUM(od.quantity)
                FROM order_details od
                INNER JOIN orders o ON o.order_id = od.order_id
                WHERE od.drink_id = d.drink_id
                  AND o.status = "Đã thanh toán"
            ), 0) AS sold
        FROM drinks d
    ';

    if (!($user && $user['role'] === 'admin')) {
        $productSql .= ' WHERE d.active = 1';
    }

    $productSql .= ' ORDER BY d.drink_id';

    $products = $pdo->query($productSql)->fetchAll();

    foreach ($products as &$p) {
        $p['id'] = (int)$p['id'];
        $p['price'] = (float)$p['price'];
        $p['active'] = (bool)$p['active'];
        $p['stock'] = (int)$p['stock'];
        $p['sold'] = (int)$p['sold'];
        $p['totalQuantity'] = $p['stock'];
        $p['remaining'] = max(0, $p['stock'] - $p['sold']);
    }

    $orders = [];
    $users = [];
    $reviews = [];

    if ($user) {
        $raw = $pdo->query('
            SELECT
                o.order_id AS id,
                o.customer_name AS customer,
                o.total_amount AS total,
                o.payment_method AS paymentMethod,
                o.status,
                o.created_at AS date,
                o.paid_at AS paidAt,
                COALESCE(u.username, "") AS staffUsername,
                COALESCE(u.full_name, "Tài khoản đã xóa") AS staffName
            FROM orders o
            LEFT JOIN users u ON u.user_id = o.staff_id
            ORDER BY o.created_at DESC
        ')->fetchAll();

        foreach ($raw as $o) {
            $o['total'] = (float)$o['total'];
            $o['items'] = load_order_items($pdo, $o['id']);
            $orders[] = $o;
        }
    }

    if ($user && $user['role'] === 'admin') {
        $users = $pdo->query('
            SELECT
                user_id AS id,
                username,
                full_name AS fullName,
                email,
                phone,
                role,
                active
            FROM users
            ORDER BY user_id
        ')->fetchAll();

        foreach ($users as &$u) {
            $u['id'] = (int)$u['id'];
            $u['active'] = (bool)$u['active'];
        }

        $reviews = $pdo->query('
            SELECT
                r.review_id AS id,
                r.order_id AS orderId,
                r.customer_name AS customer,
                r.rating,
                r.content,
                COALESCE(u.full_name, "Tài khoản đã xóa") AS enteredBy,
                r.created_at AS date
            FROM reviews r
            LEFT JOIN users u ON u.user_id = r.entered_by
            ORDER BY r.created_at DESC
        ')->fetchAll();

        foreach ($reviews as &$r) {
            $r['rating'] = (int)$r['rating'];
        }
    }

    respond([
        'ok' => true,
        'currentUser' => $user,
        'csrfToken' => csrf_token(),
        'products' => $products,
        'orders' => $orders,
        'users' => $users,
        'reviews' => $reviews
    ]);

} catch (Throwable $e) {
    error_log($e->__toString());

    respond([
        'ok' => false,
        'message' => 'Không thể tải dữ liệu hệ thống.'
    ], 500);
}