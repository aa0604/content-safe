# content-safe
##### 内容安全检测，如对接阿里云的图片检测，人工智能检查识别不和谐的图片，文字，检查是否有人脸等

提示：可配置为YII中使用

### 安装
composer require xing.chen/content-safe dev-master



# 使用示例
```php
<?php

    // 在yii中使用
    $service = Yii::$app->contentSafe;
    // 独立运行
    $service = \xing\contentSafe\SyncAli::config($config);
    
    // 检查图片
    $results = $service
    ->setScenes(['场景1', '场景2']) // 如果yii里配置有，则此行可注释/删除
    ->getImageInfo($imgUrl);
    
    // 检查文字
    $results = static::getInstance()->getTextInfo([$content]);
    foreach ($results as $k => $data) {
        foreach ($data as $label => $v) {
            if ($v == self::CHECK_SERIOUS) '非法';
        }
    }
    // 是否包含人脸
    foreach ($service->getHandleResults() as $data) {
        if (!isset($data['isFace']) || !$data['isFace'])
            throw new \Exception('图片未包含人脸');
    }
    
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
print_r($service->getHandleResults());
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
