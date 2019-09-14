<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2018/6/13
 * Time: 12:13
 */

namespace xing\contentSafe;

use xing\helper\exception\ApiCodeException;
use xing\helper\exception\LanguageException;

class ContentSafeService
{

    // 检查结果 serious=不通过，建议立即采取行动 serious  check = 需要人工审核  ok = 正常
    const CHECK_SERIOUS = \xing\contentSafe\SyncAli::CHECK_SERIOUS;
    const CHECK_REVIEW = \xing\contentSafe\SyncAli::CHECK_REVIEW;
    const CHECK_OK = \xing\contentSafe\SyncAli::CHECK_OK;

    public static $result;
    public static $isFace;

    /**
     * @return \xing\contentSafe\SyncAli
     */
    public static function getInstance($config = '')
    {
        return SyncAli::config($config);
    }

    /**
     * 检查视频
     * @param array $videoUrl
     * @param $model
     * @param $targetId
     * @throws \Exception
     */
    public static function addVideoTask(array $videoUrl, $model, $targetId)
    {

        $results = static::getInstance()->addVideoTask($videoUrl);

        // 先检查全部任务
        foreach ($results as $v) if ($v->code != 200) throw new \Exception('视频检测任务失败');

        return $results;

    }
    /**
     * 检查图片
     * @param array $imgUrl 图片数组
     * @return string 检查结果
     * @throws \Exception
     */
    public static function checkImage(array $imgUrl)
    {
        $results = static::getInstance()->getImageInfo($imgUrl);

        foreach ($results as $data) {
            foreach ($data as $label => $v) {
                if ($v != self::CHECK_OK) throw new ApiCodeException('图片非法');
            }
        }
        return self::CHECK_OK;
    }

    /**
     * 检查 图片是否合法，是否包含人脸
     * @param string $imgUrl 图片数组
     * @return array|string
     * @throws ApiCodeException
     * @throws \Exception
     */
    public static function checkFace(string $imgUrl)
    {

        $result = static::getInstance()->faceDetect($imgUrl);

        if (!isset($result->face_num) || $result->face_num <= 0)
            throw new  ApiCodeException('未包含人脸');
        return static::CHECK_OK;
    }

    /**
     * @param $content
     * @return string
     * @throws \Exception
     */
    public static function checkText(array $content)
    {

        if (empty($content)) throw new \Exception('内容为空');
        $results = static::getInstance()->getTextInfo(array_filter($content));

        foreach ($results as $data) {
            foreach ($data as $label => $v) {
                if ($v != self::CHECK_OK) throw new ApiCodeException('内容非法');
            }
        }
        return self::CHECK_OK;
    }

}