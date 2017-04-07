<?php
// 测试用例
$data = [
	'oprateCode' => '1',
	'messageBody' => [
		'orderId' => 'test_00044',
		'result' => 1,
		'oaNo' => 'OA12345',
		'rejectiveReason' => '发送方，不适用队列测试',
		'platformId' => 'N000831400',
	]
];
$rbmq = new RabbitMqModel();
$rbmq->setData($data);
$rbmq->submit();