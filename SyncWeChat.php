<?php


namespace xing\contentSafe;


use EasyWeChat\Factory;
use xing\contentSafe\core\BaseAli;
use xing\contentSafe\core\ImageSafeInterFace;

/**
 * Class SyncWeChat
 * @property $device
 * @property string $access_token
 * @package xing\contentSafe
 */
class SyncWeChat extends BaseAli implements ImageSafeInterFace
{

    public $access_token;

    /**
     * @return \EasyWeChat\BasicService\ContentSecurity\Client
     */
    public function getService()
    {
        $this->device = Factory::miniProgram($this->config);
        return $this->device->content_security;
    }


    public function getTextInfo(array $contents, $taskIds = '')
    {
        $service = $this->getService();
        $result = [];
        foreach ($contents as $content) {
            $result[0][] = $this->checkResult($service->checkText($content));
        }
        return $result;

    }


    public function getImageInfo(array $urls, $taskIds = '')
    {
        
    }

    private function checkResult($result)
    {
        return $result['errcode'] == 0 ? static::CHECK_OK : static::CHECK_SERIOUS;
    }
}