<?php
require __DIR__.'/common.php';
try{$counts=[];foreach(['users','drinks','orders','order_details','reviews'] as $t)$counts[$t]=(int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();respond(['ok'=>true,'database'=>'caffe_mini','connected'=>true,'counts'=>$counts]);}catch(Throwable $e){error_log($e->__toString());respond(['ok'=>false,'connected'=>false,'message'=>'CSDL chưa sẵn sàng.'],500);}
