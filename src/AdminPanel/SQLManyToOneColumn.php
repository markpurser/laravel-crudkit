<?php

namespace Markpurser\LaravelCrudKit\AdminPanel;

use DB;

class SQLManyToOneColumn extends SQLManyToColumn
{
    public function __construct($foreignKey, $relationTableName, $relationLabel, $relationPrimaryKey, $relationLabelColumn, $options, $callbackRelationList)
    {
        parent::__construct($foreignKey, $relationTableName, $relationLabel, $relationPrimaryKey, $relationLabelColumn, 'manytoone', $options, $callbackRelationList);
    }
}

