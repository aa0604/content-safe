<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2018/6/13
 * Time: 11:02
 */

namespace xing\contentSafe\core;

use xing\contentSafe\sdk\aliyunGreen\Green\Request\V20170825 as Green;
use  xing\contentSafe\sdk\aliyunCore\DefaultAcsClient;
use  xing\contentSafe\sdk\aliyunCore\Profile\DefaultProfile;
use xing\contentSafe\SyncAli;

class BaseAli implements BaseInterface
{
    public $config;

    public static $instance;

    public $response;

    // 检查结果 serious=不通过，建议立即采取行动 serious  check = 需要人工审核  ok = 正常
    const CHECK_SERIOUS = 'serious';
    const CHECK_REVIEW = 'review';
    const CHECK_OK = 'ok';

    // 结果
    public $results;

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
     * 配置
     * @param array $config
     * @return $this
     */
    public static function config(array $config)
    {
        $class = new self;
        $class->config = $config;
        return $class;
    }

    /**
     * 返回统一的消息和代码
     * @return array
     */
    public function getResponse()
    {
        return [
            'message' => $this->response->msg ?? '',
            'code' => $this->response->code ?? '',
        ];
    }

    /**
     * 返回场景识别结果，如色情图片、反动（英文）
     * @return array
     */
    public function getHandleResults() : array
    {
        return $this->results;
    }

    /**
     * 获取实例
     * @param array $conifg
     * @return ImageBaseAli
     */
    public static function getInstance($conifg = [])
    {
        if (!empty(static::$instance)) return static::$instance;
        $self = new self;
        $self->config = $conifg;
        static::$instance = & $self;
        return $self;
    }

    public function getClient()
    {
        $config = $this->config;

        $region = $config['region'] ?? 'cn-shanghai';
        $iClientProfile = DefaultProfile::getProfile($region, $config["accessKeyId"], $config["accessKeySecret"]);
        DefaultProfile::addEndpoint($region, $region, "Green", "green.cn-shanghai.aliyuncs.com");
        return new DefaultAcsClient($iClientProfile);
    }

    public function getAsyncRequest()
    {

        $request = new Green\ImageAsyncScanResultsRequest();
        $request->setMethod("POST");
        $request->setAcceptFormat("JSON");
        return $request;
    }

    public function getSyncRequest()
    {

        $request = new Green\ImageSyncScanRequest();
        $request->setMethod("POST");
        $request->setAcceptFormat("JSON");
        return $request;
    }

    public function getTextRequest()
    {

        $request = new Green\TextScanRequest();
        $request->setMethod("POST");
        $request->setAcceptFormat("JSON");
        return $request;
    }
}