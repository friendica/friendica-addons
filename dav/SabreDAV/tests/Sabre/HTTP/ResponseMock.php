<?php

class Sabre_HTTP_ResponseMock extends Sabre_HTTP_Response
{
    public $headers = array();
    public $status = '';
    public $body = '';

    public function setHeader($name, $value, $overwrite = true)
    {
        $this->headers[$name] = $value;
    }

    public function sendStatus($code)
    {
        $this->status = $this->getStatusMessage($code);
    }

    public function sendBody($body)
    {
        $this->body = $body;
    }
}
