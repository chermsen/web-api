<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once 'config.php';

$api = new \Myracloud\WebApi\WebApi(
    $config['apiKey'],
    $config['secret'],
    'en',
    'beta.myracloud.com');

$client = $api->getClient();

/** @var \GuzzleHttp\Psr7\Request $res */
$res = $client->get('https://beta.myracloud.com/en/rapi/domains');
#$res = $client->get('https://crmx.myrasec.de/debug.php');
var_dump(json_decode($res->getBody()->getContents()));
