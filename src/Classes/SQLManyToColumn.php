<?php

namespace Markpurser\LaravelCrudKit\Classes;

use DB;

class SQLManyToColumn extends SQLColumn
{
    private $relationTableName = null;
    private $relationPrimaryKey = null;
    private $relationLabelColumn = null;
    private $callbackRelationList = null;

    public function __construct($foreignKey, $relationTableName, $relationLabel, $relationPrimaryKey, $relationLabelColumn, $type, $options, $callbackRelationList)
    {
        parent::__construct($foreignKey, $relationLabel, $type, $options);

        $this->relationTableName = $relationTableName;
        $this->relationPrimaryKey = $relationPrimaryKey;
        $this->relationLabelColumn = $relationLabelColumn;
        $this->callbackRelationList = $callbackRelationList;
    }

    public function relationList()
    {
        if($this->callbackRelationList)
        {
            return call_user_func($this->callbackRelationList);
        }
        else {
            $list = DB::table($this->relationTableName)->select($this->relationPrimaryKey, $this->relationLabelColumn)->get();

            $relationList = [];
            foreach($list as $item)
            {
                $itemAsArray = (array)$item;

                $relationList[] = (object)['id' => $itemAsArray[$this->relationPrimaryKey], 'label' => $itemAsArray[$this->relationLabelColumn]];
            }

            return $relationList;
        }
    }
}

