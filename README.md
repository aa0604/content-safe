# content-safe
##### 内容安全检测，如对接阿里云的图片检测

提示：可配置为YII中使用

### 安装
composer require xing.chen/content-safe dev-master



# 使用示例
```php
<?php
try{
    // 检查图片合法性
    $retulst = ContentSafeService::checkImage(['图片绝对路径 1', '图片绝对路径 2']);
    // 检查图片是否合法，是否包含人脸
    $retulst = ContentSafeService::checkImage(['图片绝对路径 1', '图片绝对路径 2']);
} catch (\Exception $e) {
    exit($e->getMessage());
}

// 输出结果：
print_r($retulst);
/*
Array
(
    [0] => Array
        (
            [porn] => serious  表示违规严重
            [terrorism] => review 表示需要人工审核
            [live] => ok 表示正常图片
            [ad] => ok
            [logo] => ok
        )
)
 */

// 输出处理结果（目前图片只包含人脸，文本识别则包含所有结果）
print_r(ContentSafeService::ContentSafeService()->getHandleResults());
/*
Array
(
    [0] => Array
        (
            [celebrityFaceRate] => 0  // 名人脸的机率
            [isFace] => 1 // 是否含人脸
        )

)
 */


ContentSafeService::checkImage($checkImages);
class ContentSafeService
{

    // 检查结果 serious=不通过，建议立即采取行动 serious  check = 需要人工审核  ok = 正常
    const CHECK_SERIOUS = \xing\contentSafe\SyncAli::CHECK_SERIOUS;
    const CHECK_REVIEW = \xing\contentSafe\SyncAli::CHECK_REVIEW;
    const CHECK_OK = \xing\contentSafe\SyncAli::CHECK_OK;

    public static $result;
    public static $isFace;

    /**
     * 获取实例
     * @return \xing\contentSafe\SyncAli
     */
    public static function getInstance()
    {
        // 两种方式2选1
        return new \xing\contentSafe\SyncAli::config($config);
        return Yii::$app->contentSafe;
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

        foreach ($results as $k => $data) {
            foreach ($data as $label => $v) {
                if ($v == self::CHECK_SERIOUS) '第'. $k .'是非法图片';
            }
        }
        return self::CHECK_OK;
    }

    /**
     * 检查 图片是否合法，是否包含人脸
     * @param array $imgUrl 图片数组
     * @return array string
     * @throws ApiCodeException
     * @throws \Exception
     */
    public static function checkFace(array $imgUrl)
    {

        static::checkImage($imgUrl);
        // 是否包含人脸
        foreach (static::getInstance()->getHandleResults() as $data) {
            if (!isset($data['isFace']) || !$data['isFace'])
                throw new \Exception('图片未包含人脸');
        }
        return self::CHECK_OK;
    }

    /**
     * @param $content
     * @return string
     * @throws \Exception
     */
    public static function checkText($content)
    {

        $results = static::getInstance()->getTextInfo([$content]);

        foreach ($results as $data) {
            foreach ($data as $label => $v) {
                if ($v != self::CHECK_OK) throw new ApiCodeException(ResponseMap::CONTENT_ILLEGAL);
            }
        }
        return self::CHECK_OK;
    }

}
```

###  公共配置

```php
<?php
$config = [
    // TODO 阿里云需开通 AliyunYundunGreenWebFullAccess 的子帐号
    'accessKeyId' => '',
    'accessKeySecret' => '', 
    'region' => 'cn-shanghai',
                // 检查哪些类型
                'scenes' => ['porn', 'terrorism', 'live' , 'ad', 'logo', 'sface']
    ];
```


## 阿里云驱动
```php
<?php 
// 配置
];
\xing\contentSafe\ImageSyncAli::getInstance($config)->create('图片url地址', '唯一的任务id');

];
```
#### 配置到YII中使用
```php
<?php
// 在components中添加：
'contentSafe' => [
            'class' => '\xing\contentSafe\SyncAli',
            'config' => $config // 请自行换为将上面的配置
        ]
```
