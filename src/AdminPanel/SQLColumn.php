<?php

namespace Markpurser\LaravelCrudKit\AdminPanel;

class SQLColumn
{
    public $name = null;
    public $label = null;
    public $type = null;
    public $options = [];

    public function __construct($name, $label, $type, $options)
    {
        $this->name = $name;
        $this->label = $label;
        $this->type = $type;
        $this->options = $options;
    }

    public function addOption($option)
    {
        $this->options = array_merge($this->options, $option);
    }

    public function setType($type)
    {
        $this->type = $type;
    }

}

