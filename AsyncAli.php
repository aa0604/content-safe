<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2018/6/13
 * Time: 10:44
 */

namespace xing\contentSafe;

include_once 'sdk/aliyun-php-sdk-core/Config.php';
use Green\Request\V20170112 as Green;
use xing\contentSafe\core\ImageBaseAli;

class AsyncAli extends ImageBaseAli
{

    public static function create($url, $taskId)
    {

        $client = static::getClient();
        $request = static::getAsyncRequest();

        $task1 = [
            'dataId' =>  $taskId,
            'url' => 'http://xxx.jpg',
            'time' => round(microtime(true)*1000)
        ];
        $request->setContent(
            json_encode(
                [
                    "tasks" => [$task1],
                    "scenes" => ["porn"]
                ]
            ));

        try {
            $response = $client->getAcsResponse($request);
            print_r($response);
            if(200 == $response->code){
                $taskResults = $response->data;
                foreach ($taskResults as $taskResult) {
                    if(200 == $taskResult->code){
                        $taskId = $taskResult->taskId;
                        print_r($taskId);
                        // 将taskId 保存下来，间隔一段时间来轮询结果, 参照ImageAsyncScanResultsRequest
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
    }
}