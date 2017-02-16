<?php

namespace Markpurser\LaravelCrudKit\Classes;

use Exception;

class PageStore
{
    private $pageDescriptors = [];

    public function __construct($pageDescriptors)
    {
        foreach($pageDescriptors as $pageDescriptor)
        {
            $this->add($pageDescriptor);
        }
    }

    public function add($pageDescriptor)
    {
        $this->pageDescriptors[$pageDescriptor->getid()] = $pageDescriptor;

        return $this;
    }

    public function getPageDescriptor($pageName = null)
    {
        if(empty($this->pageDescriptors))
        {
            throw new Exception('No pages added.');
        }

        if(!$pageName)
        {
            return reset($this->pageDescriptors);
        }
        else {
            return $this->pageDescriptors[$pageName];
        }
    }

    public function getPageMap()
    {
        $pageMap = [];

        foreach($this->pageDescriptors as $item)
        {
            $pageMap[] = (object)[
                'id' => $item->getid(),
                'label' => $item->getLabel()
            ];
        }

        return $pageMap;
    }

}

