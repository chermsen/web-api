<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Middleware;

use Psr\Http\Message\RequestInterface;

/**
 * Class Signature
 * @package Myracloud\WebApi\Authentication
 */
class Signature
{
    /**
     * @var null|string
     */
    private $secret;
    /**
     * @var null|string
     */
    private $apiKey;
    /**
     * @var false|string
     */
    private $date;
    /**
     * @var string
     */
    private $contentType = 'application/json';


    /**
     * @param null|string $secret
     * @param null $apiKey
     */
    public function __construct($secret = null, $apiKey = null)
    {
        $this->secret = $secret;
        $this->apiKey = $apiKey;
        $this->date = date('c');
    }

    public function signRequest(RequestInterface $request)
    {
        $request = $request->withHeader('Content-Type', $this->contentType);
        $request = $request->withHeader('Date', $this->date);

        $signingString = $this->getStringToSign($request);

        $request = $request->withHeader('Authorization', $this->getSignature($signingString));
        return $request;
    }

    /**
     * Return unsigned string representation of the signature data
     *
     * @param RequestInterface $request
     * @return string
     */
    public function getStringToSign(RequestInterface $request)
    {
        return
            implode('#', [
                md5($request->getBody()->getContents()),
                $request->getMethod(),
                $request->getUri()->getPath(),
                $this->contentType,
                $this->date
            ]);

    }

    /**
     * Return signature as string
     *
     * @param $signingString
     * @return string
     */
    public function getSignature($signingString)
    {

        $key = hash_hmac('sha256', $this->date, 'MYRA' . $this->secret);
        $key = hash_hmac('sha256', 'myra-api-request', $key);
        $signature = base64_encode(hash_hmac('sha512', $signingString, $key, true));
        return "MYRA " . $this->apiKey . ":" . $signature;
    }
}