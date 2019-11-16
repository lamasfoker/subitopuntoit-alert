<?php
declare(strict_types=1);

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

    public function send(): void
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
     * @return Response
     */
    public function setHttpCode(int $httpCode): Response
    {
        $this->httpCode = $httpCode;
        return $this;
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
     * @return Response
     */
    public function setData(array $data): Response
    {
        $this->data = $data;
        return $this;
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
     * @return Response
     */
    public function setMessage(string $message): Response
    {
        $this->message = $message;
        return $this;
    }

}
