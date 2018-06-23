<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2018/6/13
 * Time: 10:44
 */

namespace xing\contentSafe;

use xing\contentSafe\core\BaseAli;
use xing\contentSafe\core\ImageSafeInterFace;

/**
 * 同步检测
 * Class SyncAli
 * @package xing\contentSafe
 */
class SyncAli extends BaseAli implements ImageSafeInterFace
{


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

        if (200 != $response->code) return $result;


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

        if (200 != $response->code) return $resultImg;

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
}