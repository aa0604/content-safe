# content-safe
# 说明
目前对接了阿里云的内容安全检测，可检查文本，图片，视频的内容是否和谐，是否包含广告。

可配置为YII中使用

### 安装
composer require xing.chen/content-safe dev-master



# 使用示例
```php
<?php
$config =  [
    'accessKeyId' => '',
    'accessKeySecret' => '',
    'region' => 'cn-shanghai',
    // 图片、视频要检查的场景
    'scenes' => ['porn', 'terrorism', 'live' , 'ad'],
    'drive' => 'ali', // 使用哪个厂商，目前有阿里（百度我也开发了一点点，生产环境使用中，还没空迁移过来）
];

// 配置为Yii的组件
'components' => [
    'contentSafe' => [
            'class' => '\xing\contentSafe\SyncAli',
        'config' => $config
]
];

try{
    // 检查图片合法性
    $retulst = \xing\contentSafe\SyncAli::config($config)->getImageInfo(['图片绝对路径 1', '图片绝对路径 2']);
    
    foreach ($results as $data) {
        foreach ($data as $label => $v) {
            if ($v != \xing\contentSafe\SyncAli::CHECK_OK) throw new \Exception('图片非法');
        }
    }
    // 检查文本
    $retulst = \xing\contentSafe\ContentSafeService::getTextInfo(['批量内容', '批量内容 2']);
    
    foreach ($results as $data) {
        foreach ($data as $label => $v) {
            if ($v != \xing\contentSafe\SyncAli::CHECK_OK) throw new \Exception('文本非法');
        }
    }
    
    // 检查视频
    // 注：视频目前仅支持异步检测
    $retulst = \xing\contentSafe\ContentSafeService::addVideoTask(['视频url', '视频url']);
    
    // 先检查全部任务
    foreach ($results as $v) if ($v->code != 200) throw new \Exception('视频检测任务失败');

    foreach ($results as $v) echo "任务Id：{$v->taskId}, dataId={$v->dataId}";
    
} catch (\Exception $e) {
    exit($e->getMessage());
}

```
