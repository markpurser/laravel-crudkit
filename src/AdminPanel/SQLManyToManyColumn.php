<?php

namespace Markpurser\LaravelCrudKit\AdminPanel;

use DB;

class SQLManyToManyColumn extends SQLManyToColumn
{
    public $linkTable = null;
    public $linkInColumn = null;
    public $linkOutColumn = null;

    public function __construct($relationTableName, $relationLabel, $relationPrimaryKey, $relationLabelColumn, $linkTable, $linkInColumn, $linkOutColumn, $options, $callbackRelationList)
    {
        parent::__construct($relationTableName, $relationTableName, $relationLabel, $relationPrimaryKey, $relationLabelColumn, 'manytomany', $options, $callbackRelationList);

        $this->linkTable = $linkTable;
        $this->linkInColumn = $linkInColumn;
        $this->linkOutColumn = $linkOutColumn;
    }

    public function dataAsLabelList($linkInId)
    {
        $list = DB::table($this->linkTable)
                    ->select($this->linkOutColumn)
                    ->where($this->linkInColumn, $linkInId)
                    ->get();

        $rList = $this->relationList();

        $labelList = [];
        foreach($list as $item)
        {
            $itemAsArray = (array)$item;
            $rId = $itemAsArray[$this->linkOutColumn];

            $label = '';
            foreach($rList as $rItem)
            {
                if($rItem->id == $rId)
                {
                    $label = $rItem->label;
                }
            }

            if(!empty($label))
            {
                $labelList[] = (object)['label' => $label];
            }
        }

        return $labelList;
    }
}

