<?php

namespace Markpurser\LaravelCrudKit\AdminPanel;

use DB;
use Log;
use Exception;

class TableDescriptor
{
    private $tableName = null;
    private $label = null;
    private $primaryKey = 'id';
    private $columns = [];
    private $hasTimestamps = false;
    private $hasSoftDelete = false;

    public function __construct($tableName, $label = null)
    {
        if(is_null($label)) $label = $tableName;

        $this->tableName = $tableName;
        $this->label = $label;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function addColumn($name, $label, $type, $options = [])
    {
        $this->columns[$name] = new SQLColumn($name, $label, $type, $options);

        return $this;
    }

    public function addManyToMany($relationTable, $relationLabelColumn, $linkTable, $linkInColumn, $linkOutColumn, $options = [], callable $callbackRelationList = null)
    {
        $relationTableName = $relationTable->getTableName();
        $relationLabel = $relationTable->getLabel();
        $relationPrimaryKey = $relationTable->getPrimaryKey();

        $this->columns[$relationTableName] = new SQLManyToManyColumn($relationTableName, $relationLabel, $relationPrimaryKey, $relationLabelColumn, $linkTable, $linkInColumn, $linkOutColumn, $options, $callbackRelationList);

        return $this;
    }

    public function addManyToOne($relationTable, $foreignKey, $relationLabelColumn, $options = [], callable $callbackRelationList = null)
    {
        $relationTableName = $relationTable->getTableName();
        $relationLabel = $relationTable->getLabel();
        $relationPrimaryKey = $relationTable->getPrimaryKey();

        $this->columns[$foreignKey] = new SQLManyToOneColumn($foreignKey, $relationTableName, $relationLabel, $relationPrimaryKey, $relationLabelColumn, $options, $callbackRelationList);

        return $this;
    }

    public function addTimestamps()
    {
        $this->columns['created_at'] = new SQLColumn('created_at', 'Created At', 'datetime', ['hide_editform' => true]);
        $this->columns['updated_at'] = new SQLColumn('updated_at', 'Updated At', 'datetime', ['hide_editform' => true]);

        $this->hasTimestamps = true;

        return $this;
    }

    public function addSoftDelete()
    {
        $this->columns['deleted'] = new SQLColumn('deleted', 'Deleted', 'boolean', ['hide_allforms' => true]);

        $this->hasSoftDelete = true;

        return $this;
    }

    public function hasSoftDelete()
    {
        return $this->hasSoftDelete;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function hasColumn($columnName)
    {
        return array_key_exists($columnName, $this->columns);
    }

    public function createRecord($columnValues)
    {
        $id = -1;

        try {
            DB::transaction(function() use (&$id, $columnValues)
            {
                if($this->hasTimestamps)
                {
                    // Ensure your PHP timezone is set correctly. Laravel stores created_at and updated_at using the TIMESTAMP datatype
                    // which should be set to local time (database converts to UTC internally)
                    $now = date("Y-m-d H:i:s");

                    $columnValues['created_at'] = $now;
                    $columnValues['updated_at'] = $now;
                }

                if($this->hasSoftDelete)
                {
                    $columnValues['deleted'] = false;
                }

                $id = DB::table($this->tableName)->insertGetId($columnValues);
            });
        }
        catch(Exception $e)
        {
            return [-1, ''.$e];
        }

        return [$id, null];
    }

    public function deleteRecord($id)
    {
        if($this->hasSoftDelete)
        {
            $updateInfo['deleted'] = true;

            DB::table($this->tableName)
                ->where($this->getPrimaryKey(), $id)
                ->update($updateInfo);
        }
        else {
            DB::transaction(function() use ($id)
            {
                DB::table($this->tableName)->where($this->getPrimaryKey(), '=', $id)->delete();
            });
        }
    }


}
