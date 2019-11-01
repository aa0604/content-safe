<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2018/6/13
 * Time: 10:44
 */

namespace xing\contentSafe;

use GuzzleHttp\Client;
use xing\contentSafe\core\BaseAli;
use xing\contentSafe\core\ImageSafeInterFace;
use GuzzleHttp\Psr7\Request;

/**
 * 同步检测
 * Class SyncAli
 * @package xing\contentSafe
 */
class SyncAli extends BaseAli implements ImageSafeInterFace
{

    /**
     * 另外设置场景
     * @param array $scenes
     * @return $this
     */
    public function setScenes(array $scenes)
    {
        $this->config['scenes'] = $scenes;
        return $this;
    }

    /**
     * 返回建议
     * @param string $suggestion
     * @return string
     */
    private function getSuggestion(string $suggestion) : string
    {
        return $suggestion == 'pass' ? static::CHECK_OK : ($suggestion == 'block' ? static::CHECK_SERIOUS : $suggestion);
    }

    public function getTextInfo(array $contents, $taskIds = '')
    {
        $client = static::getClient();
        $request = static::getTextRequest();
        if (count($contents) > 100) throw new \Exception('每次任务不能超过100个');
        // 组织任务数据
        $tasks = [];
        foreach ($contents as $k => $content) {
            $tasks[] = [
                'dataId' => $k,
                'content' => $content,
            ];
        }
        // 封装为JSON
        $request->setContent(json_encode([
            "tasks" => $tasks,
            "scenes" => ['antispam']
        ]));
        // 返回鉴别结果
        $result = [];

        $this->response = $response = $client->getAcsResponse($request);

        if (200 != $response->code) throw new \Exception($response->msg, $response->code);


        foreach ($response->data as $key => $taskResult) {
            if(200 != $taskResult->code) continue;

            $sceneResults = $taskResult->results;
            foreach ($sceneResults as $v) {

                if (isset($v->details) && !empty($v->details)) foreach ($v->details as $detail) {
                    $this->results[$key][$detail->label] = $this->getSuggestion($v->suggestion);
                    $result[$key][$detail->label] = $this->getSuggestion($v->suggestion);
                }
            }
        }

        return $result;
    }


    public function getImageInfo(array $urls, $taskIds = '')
    {

        $client = static::getClient();
        $request = static::getSyncRequest();

        if (count($urls) > 100) throw new \Exception('每次任务不能超过100个');
        // 组织任务数据
        foreach ($urls as $k => $url) {
            $tasks[] = [
                'dataId' => $k,
                'url' => $url,
            ];
        }
        // 封装为JSON
        $request->setContent(json_encode([
            "tasks" => $tasks,
            "scenes" => $this->config['scenes']
        ]));


        // 返回鉴别结果
        $resultImg = [];

        $this->response = $response = $client->getAcsResponse($request);

        if (200 != $response->code) throw new \Exception($response->msg, $response->code);

        $taskResults = $response->data;
        foreach ($taskResults as $key => $taskResult) {
            if(200 != $taskResult->code) continue;

            $sceneResults = $taskResult->results;
            foreach ($sceneResults as $sceneResult) {
                $label = $sceneResult->label;
                $rate = $sceneResult->rate;
                //根据scene和suggetion做相关的处理
                //do something

                $suggestion = $this->getSuggestion($sceneResult->suggestion);

                switch ($sceneResult->scene) {
                    case 'sface':
                        // 名人脸
                        if (isset($sceneResult->sfaceData) && isset($sceneResult->sfaceData[0]->faces)) {
                            $this->results[$key]['celebrityFace'] = $sceneResult->sfaceData;
                        } else {
                            $this->results[$key]['celebrityFaceRate'] = 0;
                        }
                        // 人脸
                        $this->results[$key]['isFace'] = $label == 'sface' && isset($sceneResult->sfaceData);
                        break;
                    default:
                        $resultImg[$key][$sceneResult->scene] = $suggestion;

                }
            }
        }

        return $resultImg;
    }

    public function faceDetect($image, $type = '0')
    {
        $post = ['type' => $type];
        $type == 0 ? $post['image_url'] = $image : $post['content'] = $image;
// 参数设置
        $config = $this->config;
        $akId =  $config["accessKeyId"];
        $akSecret = $config["accessKeySecret"];
        $body1 = '{"type":"0","image_url":"https://ss0.bdstatic.com/94oJfD_bAAcT8t7mm9GUKT-xh_/timg?image&quality=100&size=b4000_4000&sec=1553926699&di=3e4484731c8897c57e67b3f632801f9a&src=http://b-ssl.duitang.com/uploads/item/201603/28/20160328121906_ErzAB.jpeg"}';
        $body1 = json_encode($post);
        $url = "https://dtplus-cn-shanghai.data.aliyuncs.com/face/detect";

        $date1 = gmdate("D, d M Y H:i:s \G\M\T");
// 参数构造
        $options = array(
            'http' => array(
                'header' => array(
                    'accept'=> "application/json",
                    'content-type'=> "application/json",
                    'date'=> $date1,
                    'authorization' => ''
                ),
                'method' => "POST", //可以是 GET, POST, DELETE, PUT
                'content' => $body1
//         'content' => json_encode($body1)
            )
        );

        $http = $options['http'];
        $header = $http['header'];
        $urlObj = parse_url($url);
        if(empty($urlObj["query"]))
            $path = $urlObj["path"];
        else
            $path = $urlObj["path"]."?".$urlObj["query"];
        $body = $http['content'];
        if(empty($body))
            $bodymd5 = $body;
        else
            $bodymd5 = base64_encode(md5($body,true));
        $stringToSign = $http['method']."\n".$header['accept']."\n".$bodymd5."\n".$header['content-type']."\n".$header['date']."\n".$path;
        $signature = base64_encode(
            hash_hmac(
                "sha1",
                $stringToSign,
                $akSecret, true));

        $authHeader = "Dataplus "."$akId".":"."$signature";
        $options['http']['header']['authorization'] = $authHeader;

#  构造Rest API Client请求
        $client = new Client();
        $headers = ['Content-type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => $options['http']['header']['authorization'], 'Date' => $date1];

        $request = new Request('POST','https://dtplus-cn-shanghai.data.aliyuncs.com/face/detect',$headers,$body1);

        $response = $client->send($request);
        return json_decode($response->getBody());
    }
}