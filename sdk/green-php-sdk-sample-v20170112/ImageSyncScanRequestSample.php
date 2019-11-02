<?php
/**
 * User: hyliu
 * Time: 17:46
 * 同步图片检测样例，调用会实时返回检测结果
 */

include_once 'aliyuncs/aliyun-php-sdk-core/Config.php';
use Green\Request\V20170112 as Green;
date_default_timezone_set("PRC");

$ak = parse_ini_file("aliyun.ak.ini");
//请替换成你自己的accessKeyId、accessKeySecret
$iClientProfile = DefaultProfile::getProfile("cn-shanghai", $ak["accessKeyId"], $ak["accessKeySecret"]); // TODO
DefaultProfile::addEndpoint("cn-shanghai", "cn-shanghai", "Green", "green.cn-shanghai.aliyuncs.com");
$client = new DefaultAcsClient($iClientProfile);

$request = new Green\ImageSyncScanRequest();
$request->setMethod("POST");
$request->setAcceptFormat("JSON");

$task1 = array('dataId' =>  uniqid(),
    'url' => 'http://xxx.jpg',
    'time' => round(microtime(true)*1000)
);
$request->setContent(json_encode(array("tasks" => array($task1),
                              "scenes" => array("porn"))));

try {
    $response = $client->getAcsResponse($request);
    print_r($response);
    if(200 == $response->code){
        $taskResults = $response->data;
        foreach ($taskResults as $taskResult) {
            if(200 == $taskResult->code){
                $sceneResults = $taskResult->results;
                foreach ($sceneResults as $sceneResult) {
                    $scene = $sceneResult->scene;
                    $suggestion = $sceneResult->suggestion;
                    //根据scene和suggetion做相关的处理
                    //do something
                    print_r($scene);
                    print_r($suggestion);
                }
            }else{
                print_r("task process fail:" + $response->code);
            }
        }
    }else{
        print_r("detect not success. code:" + $response->code);
    }
} catch (Exception $e) {
    print_r($e);
}



