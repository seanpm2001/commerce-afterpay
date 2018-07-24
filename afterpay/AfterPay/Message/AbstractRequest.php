<?php

namespace Omnipay\AfterPay\Message;

use Guzzle\Http\Message\Response as GuzzleResponse;
use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;

abstract class AbstractRequest extends BaseAbstractRequest
{
    protected $liveEndpoint = 'https://api.secure-afterpay.com.au/v1';
    protected $testEndpoint = 'https://api-sandbox.secure-afterpay.com.au/v1';

    /**
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    /**
     * @param mixed $value
     * @return $this
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    /**
     * @return mixed
     */
    public function getMerchantSecret()
    {
        return $this->getParameter('merchantSecret');
    }

    /**
     * @param mixed $value
     * @return $this
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setMerchantSecret($value)
    {
        return $this->setParameter('merchantSecret', $value);
    }

    /**
     * @param mixed $data
     * @return \Omnipay\AfterPay\Message\Response
     * @throws \Guzzle\Http\Exception\RequestException
     */
    public function sendData($data)
    {
        $endpoint = $this->getEndpoint();
        $httpMethod = $this->getHttpMethod();

        $httpRequest = $this->httpClient->createRequest($httpMethod, $endpoint);
        $httpRequest->getCurlOptions()->set(CURLOPT_SSLVERSION, 6); // CURL_SSLVERSION_TLSv1_2
        $httpRequest->addHeader('Authorization', $this->buildAuthorizationHeader());
        $httpRequest->addHeader('Content-type', 'application/json');
        $httpRequest->addHeader('Accept', 'application/json');
        $httpRequest->setBody(json_encode($data));

        $httpResponse = $httpRequest->send();

        $this->response = $this->createResponse(
            $this->parseResponseData($httpResponse)
        );

        return $this->response;
    }

    /**
     * @return string
     */
    public function getHttpMethod()
    {
        return 'POST';
    }

    /**
     * @return string
     */
    protected function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }

    /**
     * @param \Guzzle\Http\Message\Response $httpResponse
     * @return array
     */
    protected function parseResponseData(GuzzleResponse $httpResponse)
    {
        return $httpResponse->json();
    }

    /**
     * @param mixed $data
     * @return \Omnipay\AfterPay\Message\Response
     */
    protected function createResponse($data)
    {
        return new Response($this, $data);
    }

    /**
     * @return string
     */
    protected function buildAuthorizationHeader()
    {
        $merchantId = $this->getMerchantId();
        $merchantSecret = $this->getMerchantSecret();

        return 'Basic ' . base64_encode($merchantId . ':' . $merchantSecret);
    }
}
