<?php

namespace SubitoPuntoItAlert\Api;

class Response
{
    /**
     * @var int
     */
    private $httpCode;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $message;

    /**
     * Response constructor.
     * @param int $httpCode
     * @param array $data
     * @param string $message
     */
    public function __construct(int $httpCode = 200, array $data = null, string $message = 'Ok')
    {
        $this->httpCode = $httpCode;
        $this->data = $data;
        $this->message = $message;
    }

    public function send()
    {
        header('Content-Type: application/json');
        echo json_encode(array('code' => $this->httpCode, 'message' => $this->message, 'data' => $this->data));
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /**
     * @param int $httpCode
     */
    public function setHttpCode(int $httpCode): void
    {
        $this->httpCode = $httpCode;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

}
