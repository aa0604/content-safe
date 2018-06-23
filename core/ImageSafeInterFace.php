<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2018/6/13
 * Time: 13:20
 */

namespace xing\contentSafe\core;


interface ImageSafeInterFace
{

    public function getImageInfo(array $urls, $taskIds = '');
    public function getTextInfo(array $contents, $taskIds = '');
}