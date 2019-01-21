<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once 'config.php';

$api = new \Myracloud\WebApi\WebApi(
    $config['apiKey'],
    $config['secret'],
    'beta.myracloud.com'
);

$domain = $api->getDomainEndpoint();

$data = $domain->getList();

foreach ($data['list'] as $item) {
    if ($item['name'] == 'example.org') {
        var_dump($domain->update($item['id'], new DateTime($item['modified']), true));
    }
}



#18174
#2019-01-21T10:33:52+0100
#var_dump($domain->delete($create['targetObject'][0]['id'], new DateTime($create['targetObject'][0]['modified'])));

