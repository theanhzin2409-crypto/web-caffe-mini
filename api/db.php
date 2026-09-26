<?php
declare(strict_types=1);
const DB_HOST='127.0.0.1';
const DB_NAME='caffe_mini';
const DB_USER='root';
const DB_PASS='';
try{
  $pdo=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
}catch(Throwable $e){
  http_response_code(500);header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok'=>false,'message'=>'Không thể kết nối CSDL caffe_mini. Hãy kiểm tra MySQL trong XAMPP.'],JSON_UNESCAPED_UNICODE);exit;
}
