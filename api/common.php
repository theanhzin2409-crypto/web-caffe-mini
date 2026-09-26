<?php
declare(strict_types=1);
$secure=!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off';
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
session_start();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
require __DIR__.'/db.php';
function respond(array $data,int $status=200):never{http_response_code($status);echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
function json_input():array{$raw=file_get_contents('php://input');if(!$raw)return[];$d=json_decode($raw,true);return is_array($d)?$d:[];}
function csrf_token():string{if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(24));return(string)$_SESSION['csrf'];}
function check_csrf():void{$m=strtoupper($_SERVER['REQUEST_METHOD']??'GET');if(in_array($m,['GET','HEAD','OPTIONS'],true))return;$sent=$_SERVER['HTTP_X_CSRF_TOKEN']??'';if(!$sent||!hash_equals(csrf_token(),$sent))respond(['ok'=>false,'message'=>'Phiên làm việc không hợp lệ. Hãy tải lại trang.'],419);}
function current_user(PDO $pdo):?array{$uid=$_SESSION['user_id']??null;if(!$uid)return null;$s=$pdo->prepare('SELECT user_id AS id,username,full_name AS fullName,email,phone,role,active FROM users WHERE user_id=? LIMIT 1');$s->execute([(int)$uid]);$u=$s->fetch();if(!$u||!(bool)$u['active']){$_SESSION=[];return null;}$u['id']=(int)$u['id'];$u['active']=(bool)$u['active'];return$u;}
function require_user(PDO $pdo):array{$u=current_user($pdo);if(!$u)respond(['ok'=>false,'message'=>'Bạn cần đăng nhập để thực hiện chức năng này.'],401);return$u;}
function require_role(PDO $pdo,array $roles):array{$u=require_user($pdo);if(!in_array($u['role'],$roles,true))respond(['ok'=>false,'message'=>'Bạn không có quyền thực hiện chức năng này.'],403);return$u;}
function valid_payment(?string $v):?string{if($v===null||$v==='')return null;return in_array($v,['Tiền mặt','Chuyển khoản'],true)?$v:null;}
function valid_status(string $v):bool{return in_array($v,['Chờ thanh toán','Đã thanh toán','Hủy'],true);}
function make_order_id():string{return'DH'.date('YmdHis').random_int(100,999);}
function load_order_items(PDO $pdo,string $id):array{$s=$pdo->prepare('SELECT od.drink_id AS id,d.drink_name AS name,od.unit_price AS price,od.quantity AS qty FROM order_details od JOIN drinks d ON d.drink_id=od.drink_id WHERE od.order_id=? ORDER BY od.detail_id');$s->execute([$id]);$r=$s->fetchAll();foreach($r as &$i){$i['id']=(int)$i['id'];$i['price']=(float)$i['price'];$i['qty']=(int)$i['qty'];}return$r;}
function normalize_order_items(PDO $pdo,array $items):array{if(!$items)respond(['ok'=>false,'message'=>'Đơn hàng chưa có món.'],422);$out=[];$total=0.0;$s=$pdo->prepare('SELECT drink_id,drink_name,price,active FROM drinks WHERE drink_id=? LIMIT 1');foreach($items as $i){$id=(int)($i['id']??0);$qty=(int)($i['qty']??0);if($id<=0||$qty<=0)respond(['ok'=>false,'message'=>'Dữ liệu món trong đơn không hợp lệ.'],422);$s->execute([$id]);$d=$s->fetch();if(!$d)respond(['ok'=>false,'message'=>'Có món không còn tồn tại trong thực đơn.'],422);if(!(bool)$d['active'])respond(['ok'=>false,'message'=>'Món "'.$d['drink_name'].'" đang tạm ngừng bán.'],422);$price=(float)$d['price'];$sub=$price*$qty;$total+=$sub;$out[]=['id'=>$id,'name'=>$d['drink_name'],'qty'=>$qty,'price'=>$price,'subTotal'=>$sub];}return[$out,$total];}
