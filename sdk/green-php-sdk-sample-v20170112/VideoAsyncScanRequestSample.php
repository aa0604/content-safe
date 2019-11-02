<?php
/**
 * Created by PhpStorm.
 * User: hyliu
 * Date: 2017/4/21
 * Time: 10:04
 */

include_once 'aliyuncs/aliyun-php-sdk-core/Config.php';
use Green\Request\V20170112 as Green;

date_default_timezone_set("PRC");

$ak = parse_ini_file("aliyun.ak.ini");
//请替换成你自己的accessKeyId、accessKeySecret
$iClientProfile = DefaultProfile::getProfile("cn-shanghai", $ak["accessKeyId"], $ak["accessKeySecret"]); // TODO
DefaultProfile::addEndpoint("cn-shanghai", "cn-shanghai", "Green", "green.cn-shanghai.aliyuncs.com");
$client = new DefaultAcsClient($iClientProfile);

$request = new Green\VideoAsyncScanRequest();
$request->setMethod("POST");
$request->setAcceptFormat("JSON");

$frame1 = array(
    "offset" => 0,
    "url" => "http://xxx1.jpg"
);

$frame2 = array(
    "offset" => 5,
    "url" => "http://xxx2.jpg"
);


$frame3 = array(
    "offset" => 10,
    "url" => "http://xxx3.jpg"
);

$task1 = array(
    "dataId" => uniqid(),
    "interval" => 5,
    "length" => 3600,
    "url" => "http://***.swf",
    "frames" => array($frame1, $frame2, $frame3)
);
$request->setContent(json_encode(array("tasks" => array($task1),
    "scenes" => array("porn"))));

try {
    $response = $client->getAcsResponse($request);
    print_r($response);
} catch (Exception $e) {
    print_r($e);
}
