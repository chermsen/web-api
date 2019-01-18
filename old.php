<?php

/**
 * Class MyraApi
 *
 * @package Myracloud\wordpress
 */
class MyraApi
{
    /**
     * Language settings
     */
    protected $lang = 'en';

    /**
     * The Myra API server
     */
    protected $site = 'api.myracloud.com';

    /**
     * Your API key
     */
    protected $apiKey = '';

    /**
     * Your API secret
     */
    protected $secret = '';

    /**
     * Supported targets
     */
    protected $supportedTargets = [
        'domains',
        'redirects',
        'cacheSettings',
        'dnsRecords',
        'statistic',
        'maintenance',
        'ipfilter',
        'cacheClear',
        'subdomainSetting',
        'certificates',
        'permissions',
        'statistic/query',
    ];

    /**
     * MyraApi constructor.
     *
     * @param string $apiKey
     * @param string $secret
     * @param null $lang
     * @param null $site
     */
    public function __construct($apiKey, $secret, $lang = null, $site = null)
    {
        $this->apiKey = $apiKey;
        $this->secret = $secret;

        if ($lang != null) {
            $this->lang = $lang;
        }
        if ($site != null) {
            $this->site = $site;
        }
    }

    /**
     * Log a message.
     *
     * @var $status  string
     * @var $message string
     * @var $ret     \stdClass|string
     */
    static function logMessage($message, $status = '', $ret = '')
    {
        if ($status != '') {
            $message = '[' . strtoupper($status) . ']: ' . $message;
        }

        if ($status == 'success' || $status == '') {
            $length = strlen($message) + 2;

            echo str_repeat('=', $length), "\n";
            echo ' ', $message, "\n";
            echo str_repeat('=', $length), "\n";
        } else {
            if ($ret != '') {
                echo $message, "\n";

                foreach ($ret->violationList as $data) {
                    echo 'Message: ', $data->message, "\n";
                    echo 'Path:    ', $data->propertyPath, "\n";
                }
            } else {
                echo $message, "\n";
            }
        }
    }

    /**
     * Call a myracloud API routine
     *
     * @param String $target The API call target [redirects | cacheSettings]
     * @param String $method [create | update | list| delete]
     * @param String $domain string The Domain we are working on. For setting general settings please use the domain id
     * @param        $body
     * @param array $options
     *
     * @return array []  A JSON object with results
     * @throws \Exception
     */
    public
    function call(
        $target,
        $method,
        $domain,
        $body,
        $options = []
    ) {
        if (!in_array($target, $this->supportedTargets)) {
            throw new \Exception('[ERROR]: Unsupported $target given: ' . $target);
        }

        switch ($method) {
            case 'create':
                $method = 'PUT';
                break;

            case 'query':
            case 'update':
                $method = 'POST';
                break;

            case 'list':
                $method = 'GET';
                $body = null;
                break;

            case 'delete':
                $method = 'DELETE';
                break;

            default:
                throw new \Exception('[ERROR]: Unknown $method: ' . $method);
                break;
        }

        $date = date('c');
        #$date = '2019-01-17T13:01:44+00:00';
        $uri = '/' . $this->lang . '/rapi/' . $target . '/' . $domain;
        $uri = rtrim($uri, '/');

        if ($body != null) {
            $body = json_encode($body);
        }

        $content_type = 'application/json';
        $signing_string = md5($body) . '#' . $method . '#' . $uri . '#' . $content_type . '#' . $date;
        $date_key = hash_hmac('sha256', $date, 'MYRA' . $this->secret);

        $signing_key = hash_hmac('sha256', 'myra-api-request', $date_key);
        $signature = base64_encode(hash_hmac('sha512', $signing_string, $signing_key, true));

        var_dump($signing_string);
        $genOptions = [];
        $genOptions[] = 'Content-Type: application/json';
        $genOptions[] = 'Content-Length: ' . strlen($body);
        $genOptions[] = 'Host: ' . $this->site;
        $genOptions[] = 'Date: ' . $date;
        $genOptions[] = 'Authorization: MYRA ' . $this->apiKey . ':' . $signature;

        $options = array_merge($genOptions, $options);

        var_dump($uri);
        var_dump($method);
        var_dump($body);
        var_dump($options);


        $ret = $this->curlExec('https://' . $this->site . $uri, $method, $body, $options);

        return $ret;
    }

    /**
     * cURL wrapper for executing an API call
     *
     * @param String $url The URL to be called
     * @param String $method HTTP method [GET | DELETE | POST | PUT]. Default is GET
     * @param String $body The reuqest body in JSON format
     * @param array $options
     *
     * @return array Response from API server
     * @throws \Exception
     */
    protected
    function curlExec(
        $url,
        $method,
        $body,
        array $options = []
    ) {
        $ch = curl_init();

        curl_setopt_array(
            $ch,
            [
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HEADER => false,
                CURLOPT_HTTPHEADER => $options,
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_URL => $url,
                CURLOPT_VERBOSE => false,
            ]
        );

        if ($method == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        if ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $content = curl_exec($ch);

        if ($content === false) {
            throw new \Exception('[ERROR]: Executing cURL. ' . curl_error($ch));
        }

        $info = curl_getinfo($ch);

        curl_close($ch);

        return [
            'content' => $content,
            'info' => $info,
            'statusCode' => $info['http_code'],
        ];
    }
}

require_once 'config.php';

$x = new MyraApi(
    $config['apiKey'],
    $config['secret'],
    'en',
    'beta.myracloud.com'
);

var_dump($x->call('domains', 'list', '', null));