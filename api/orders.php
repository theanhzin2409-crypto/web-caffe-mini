<?php
require __DIR__.'/common.php';$user=require_role($pdo,['admin','staff']);check_csrf();$m=strtoupper($_SERVER['REQUEST_METHOD']??'GET');$d=json_input();
try{
 if($m==='POST'){
  [$items,$total]=normalize_order_items($pdo,is_array($d['items']??null)?$d['items']:[]);
  $id=trim((string)($d['id']??''))?:make_order_id();$customer=trim((string)($d['customer']??''))?:'Khách lẻ';$payment=valid_payment($d['paymentMethod']??null);$status=(string)($d['status']??'Chờ thanh toán');
  if(!valid_status($status))respond(['ok'=>false,'message'=>'Trạng thái đơn không hợp lệ.'],422);if($status==='Đã thanh toán'&&!$payment)respond(['ok'=>false,'message'=>'Hãy chọn phương thức thanh toán.'],422);
  $pdo->beginTransaction();$s=$pdo->prepare('INSERT INTO orders(order_id,customer_name,staff_id,total_amount,payment_method,status,created_at,paid_at) VALUES(?,?,?,?,?,?,NOW(),?)');$s->execute([$id,$customer,$user['id'],$total,$payment,$status,$status==='Đã thanh toán'?date('Y-m-d H:i:s'):null]);$dt=$pdo->prepare('INSERT INTO order_details(order_id,drink_id,quantity,unit_price,sub_total) VALUES(?,?,?,?,?)');foreach($items as $i)$dt->execute([$id,$i['id'],$i['qty'],$i['price'],$i['subTotal']]);$pdo->commit();respond(['ok'=>true,'id'=>$id,'total'=>$total,'message'=>$status==='Đã thanh toán'?'Thanh toán thành công.':'Đã lưu đơn chờ thanh toán.']);
 }
 if($m==='PUT'){
  $id=trim((string)($d['id']??''));if($id==='')respond(['ok'=>false,'message'=>'Thiếu mã đơn.'],422);$q=$pdo->prepare('SELECT status FROM orders WHERE order_id=? LIMIT 1');$q->execute([$id]);$row=$q->fetch();if(!$row)respond(['ok'=>false,'message'=>'Không tìm thấy đơn hàng.'],404);
  if(($d['action']??'')==='status'){
   if($user['role']!=='admin')respond(['ok'=>false,'message'=>'Chỉ Admin được thay đổi trạng thái trực tiếp.'],403);$status=(string)($d['status']??'');if(!valid_status($status))respond(['ok'=>false,'message'=>'Trạng thái không hợp lệ.'],422);$s=$pdo->prepare('UPDATE orders SET status=?,paid_at=CASE WHEN ?="Đã thanh toán" THEN COALESCE(paid_at,NOW()) WHEN ?<>"Đã thanh toán" THEN NULL ELSE paid_at END WHERE order_id=?');$s->execute([$status,$status,$status,$id]);respond(['ok'=>true,'message'=>'Đã cập nhật trạng thái đơn.']);
  }
  if($row['status']!=='Chờ thanh toán')respond(['ok'=>false,'message'=>'Chỉ có thể chỉnh sửa đơn đang chờ thanh toán.'],422);
  [$items,$total]=normalize_order_items($pdo,is_array($d['items']??null)?$d['items']:[]);$customer=trim((string)($d['customer']??''))?:'Khách lẻ';$payment=valid_payment($d['paymentMethod']??null);$status=(string)($d['status']??'Chờ thanh toán');if(!in_array($status,['Chờ thanh toán','Đã thanh toán'],true))respond(['ok'=>false,'message'=>'Trạng thái cập nhật không hợp lệ.'],422);if($status==='Đã thanh toán'&&!$payment)respond(['ok'=>false,'message'=>'Hãy chọn phương thức thanh toán.'],422);
  $pdo->beginTransaction();$s=$pdo->prepare('UPDATE orders SET customer_name=?,staff_id=?,total_amount=?,payment_method=?,status=?,paid_at=? WHERE order_id=?');$s->execute([$customer,$user['id'],$total,$payment,$status,$status==='Đã thanh toán'?date('Y-m-d H:i:s'):null,$id]);$pdo->prepare('DELETE FROM order_details WHERE order_id=?')->execute([$id]);$dt=$pdo->prepare('INSERT INTO order_details(order_id,drink_id,quantity,unit_price,sub_total) VALUES(?,?,?,?,?)');foreach($items as $i)$dt->execute([$id,$i['id'],$i['qty'],$i['price'],$i['subTotal']]);$pdo->commit();respond(['ok'=>true,'id'=>$id,'total'=>$total,'message'=>$status==='Đã thanh toán'?'Thanh toán thành công.':'Đã cập nhật đơn chờ.']);
 }
 respond(['ok'=>false,'message'=>'Phương thức không được hỗ trợ.'],405);
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log($e->__toString());$msg=str_contains($e->getMessage(),'Duplicate')?'Mã đơn đã tồn tại, hãy thử lại.':'Không thể xử lý đơn hàng.';respond(['ok'=>false,'message'=>$msg],500);}
