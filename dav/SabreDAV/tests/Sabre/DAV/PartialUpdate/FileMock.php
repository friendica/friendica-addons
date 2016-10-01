<?php

class Sabre_DAV_PartialUpdate_FileMock implements Sabre_DAV_PartialUpdate_IFile
{
    protected $data = '';

    public function put($str)
    {
        if (is_resource($str)) {
            $str = stream_get_contents($str);
        }
        $this->data = $str;
    }

    public function putRange($str, $start)
    {
        if (is_resource($str)) {
            $str = stream_get_contents($str);
        }
        $this->data = substr($this->data, 0, $start).$str.substr($this->data, $start + strlen($str));
    }

    public function get()
    {
        return $this->data;
    }

    public function getContentType()
    {
        return 'text/plain';
    }

    public function getSize()
    {
        return strlen($this->data);
    }

    public function getETag()
    {
        return '"'.$this->data.'"';
    }

    public function delete()
    {
        throw new Sabre_DAV_Exception_MethodNotAllowed();
    }

    public function setName($name)
    {
        throw new Sabre_DAV_Exception_MethodNotAllowed();
    }

    public function getName()
    {
        return 'partial';
    }

    public function getLastModified()
    {
        return null;
    }
}
