<?php
include_once("lib/Client.php");
$client = new Client();
$action = '';//新订单

/*
status :1 未付款
	    2 已付款
	    3 退货，退款
	    4 取消
*/
if ($action == 'new'){
	$data = array(
				  'user_id'=>'368064',
				  'order_id'=>'X1234567890',
				  'order_time'=>'1299724672',
				  'pid'=>'P1234567890',
				  'price'=>'25.50',
				  'number'=>'2',
				  'total_price'=>'50.00',
				  'goods_url'=>'http://www.sitename.com/product?id=10',
				  'title'=>'119元疯狂抢购！兰蔻金纯卓颜玫瑰唇膏（金色限量版），独特保湿成分长达8小时维持唇部水润，同lec\'',
				  'desc'=>'最新的LE ROUGE ABSOLU玫瑰柔润唇膏，使双唇变得比以往任何时候都要明艳动人。为了让女性的唇部闪烁充满魅力的光芒，
				  			兰蔻改造了自己最优秀的经典唇膏，赋予它无法抵抗的魔力。
							采用兰蔻实验室研发的独特Pro-Xylane 因子，完美重塑盒丰盈双唇，带出性感丰润的唇妆。
							质地细腻柔和，令上妆舒适。独特保湿成分长达8小时维持唇部水润。同时带来防晒隔离保护。lec\'
							丝质玫瑰娇色，勾勒柔美丰盈轮廓，盈润缎彩尽致盛放！',
				  'merchant_addr'=>'广州市越秀区北京路34号',
				  'status'=>'1'
				  );
}else{//更新 （已付款,退货,退款,取消）
	$data = array(
				  'user_id'=>'368064',
				  'order_id'=>'X1234567890',
				  'order_time'=>'1299724672',
				  'pid'=>'P1234567890',
				  'price'=>'25.50',
				  'number'=>'2',
				  'total_price'=>'50.00',
				  'goods_url'=>'http://www.sitename.com/product?id=10',
				  'title'=>'119元疯狂抢购！兰蔻金纯卓颜玫瑰唇膏（金色限量版），独特保湿成分长达8小时维持唇部水润，同lec\'',
				  'desc'=>'最新的LE ROUGE ABSOLU玫瑰柔润唇膏，使双唇变得比以往任何时候都要明艳动人。为了让女性的唇部闪烁充满魅力的光芒，
				  			兰蔻改造了自己最优秀的经典唇膏，赋予它无法抵抗的魔力。
							采用兰蔻实验室研发的独特Pro-Xylane 因子，完美重塑盒丰盈双唇，带出性感丰润的唇妆。
							质地细腻柔和，令上妆舒适。独特保湿成分长达8小时维持唇部水润。同时带来防晒隔离保护。lec\'
							丝质玫瑰娇色，勾勒柔美丰盈轮廓，盈润缎彩尽致盛放！',
				  'merchant_addr'=>'广州市越秀区北京路34号',
				  'status'=>'2'
				  );
}
$res = $client->send($data);
var_dump(json_decode($res));