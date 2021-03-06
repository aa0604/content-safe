<?php

namespace xing\contentSafe\sdk\aliyunGreen\Green\Request\V20180509;

/**
 * @deprecated Please use https://github.com/aliyun/openapi-sdk-php
 *
 * Request of VideoSyncScan
 *
 * @method string getClientInfo()
 */
class VideoSyncScanRequest extends \xing\contentSafe\sdk\aliyunCore\RoaAcsRequest
{

    /**
     * @var string
     */
    protected $uriPattern = '/green/video/syncscan';

    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct(
            'Green',
            '2018-05-09',
            'VideoSyncScan',
            'green'
        );
    }

    /**
     * @param string $clientInfo
     *
     * @return $this
     */
    public function setClientInfo($clientInfo)
    {
        $this->requestParameters['ClientInfo'] = $clientInfo;
        $this->queryParameters['ClientInfo'] = $clientInfo;

        return $this;
    }
}
