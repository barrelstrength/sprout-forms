<?php
namespace Craft;

class SproutForms_HtmlDisplay
{
    private $htmlFields = array();
    
    public function __set($key, $val)
    {
        $this->htmlFields[$key] = $val;
    }
    
    public function __get($key)
    {
        if (isset($this->htmlFields[$key]))
        {
            return $this->htmlFields[$key];
        }
    }
    
    public function __call($key, $args)
    {
        if (isset($this->htmlFields[$key]))
        {
            echo $this->htmlFields[$key];
        }
    }
}