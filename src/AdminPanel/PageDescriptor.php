<?php

namespace Markpurser\LaravelCrudKit\AdminPanel;

use DB;
use Log;
use Exception;

class PageDescriptor
{
    private $creatable = true;
    private $updatable = true;
    private $deletable = true;
    private $viewTableName = null;
    private $label = null;
    private $pageid = null;
    private $columnLookup = [];
    private $summaryColumns = [];
    private $tableLookup = [];

    private $initialValuesCallback = null;
    private $createCallback = null;
    private $updateCallback = null;
    private $deleteCallback = null;

    public function __construct($label)
    {
        $this->label = $label;
        $this->pageid = preg_replace("/[^A-Za-z0-9]/", "", $this->label);
    }

    public function setInitialValues($columns)
    {
        if($this->initialValuesCallback)
        {
            $columns = call_user_func($this->initialValuesCallback, $columns);
        }
        else {
            $columns = $this->onInitialValues($columns);
        }

        return $columns;
    }

    public function create($columnValues)
    {
        $id = -1;
        $error = null;

        if($this->createCallback)
        {
            list($id, $error) = call_user_func($this->createCallback, $columnValues, $this);
        }
        else {
            list($id, $error) = $this->onCreate($columnValues, $this);
        }

        return [$id, $error];
    }

    public function update($id, $columnValues)
    {
        if($this->updateCallback)
        {
            call_user_func($this->updateCallback, $id, $columnValues);
        }
        else {
            $this->onUpdate($id, $columnValues);
        }
    }

    public function delete($id)
    {
        if($this->deleteCallback)
        {
            call_user_func($this->deleteCallback, $id, $this);
        }
        else {
            $this->onDelete($id, $this);
        }
    }

    public function setInitialValuesCallback(callable $callback)
    {
        $this->initialValuesCallback = $callback;

        return $this;
    }

    public function setCreateCallback(callable $callback)
    {
        $this->createCallback = $callback;

        return $this;
    }

    public function setUpdateCallback(callable $callback)
    {
        $this->updateCallback = $callback;

        return $this;
    }

    public function setDeleteCallback(callable $callback)
    {
        $this->deleteCallback = $callback;

        return $this;
    }

    // override to provide custom behaviour
    public function onInitialValues($columns)
    {
        return $columns;
    }

    // override to provide custom behaviour
    public function onCreate($columnValues, $pageDescriptor)
    {
        if($this->isMultiTable())
        {
            throw new Exception('Cannot create multiple tables. Please provide a custom create callback.');
        }

        list($id, $error) = ($this->getTable())->createRecord($columnValues);

        return [$id, $error];
    }

    // override to provide custom behaviour
    public function onUpdate($id, $columnValues)
    {
        $this->defaultUpdate($id, $columnValues);
    }

    // override to provide custom behaviour
    public function onDelete($id, $pageDescriptor)
    {
        if($this->isMultiTable())
        {
            throw new Exception('Cannot delete multiple tables. Please provide a custom delete callback.');
        }

        ($this->getTable())->deleteRecord($id);
    }

    public function getViewTableName()
    {
        if(is_null($this->viewTableName))
        {
            return $this->getTable()->getTableName();
        }
        else {
            return $this->viewTableName;
        }
    }

    public function setViewTableName($viewTableName)
    {
        $this->viewTableName = $viewTableName;

        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getid()
    {
        return $this->pageid;
    }

    public function getPrimaryKey()
    {
        $table = $this->getTable();
        return $table->getPrimaryKey();
    }

    public function isCreatable()
    {
        return $this->creatable;
    }

    public function disableCreate()
    {
        $this->creatable = false;

        return $this;
    }

    public function isUpdatable()
    {
        return $this->updatable;
    }

    public function disableUpdate()
    {
        $this->updatable = false;

        return $this;
    }

    public function isDeletable()
    {
        return $this->deletable;
    }

    public function disableDelete()
    {
        $this->deletable = false;

        return $this;
    }

    public function addTable($table)
    {
        $tableName = $table->getTableName();

        $this->tableLookup[$tableName] = $table;

        $addColumns = $table->getColumns();

        $duplicates = array_intersect_key($this->columnLookup, $addColumns);

        // don't report timestamps as duplicates but allow one timestamp per page
        $duplicates = array_diff_key($duplicates, ['created_at' => 0, 'updated_at' => 0]);

        if(!empty($duplicates))
        {
            throw new Exception('Duplicate column names: '.implode(', ',array_keys($duplicates)).', found adding table \''.$tableName.'\'');
        }

        $this->columnLookup = array_merge($this->columnLookup, $addColumns);

        return $this;
    }

    public function hasSoftDelete()
    {
        return $this->getTable()->hasSoftDelete();
    }

    private function isMultiTable()
    {
        return count($this->tableLookup) > 1;
    }

    private function getTable()
    {
        if(empty($this->tableLookup))
        {
            throw new Exception('No tables added to page descriptor '.$this->label);
        }

        return reset($this->tableLookup);
    }

    public function setSummaryColumns($summaryColumns)
    {
        $this->summaryColumns = $summaryColumns;

        $columnsNotFound = array_diff($this->summaryColumns, array_keys($this->columnLookup));

        if(!empty($columnsNotFound))
        {
            throw new Exception('Summary columns not found: '.implode(', ',$columnsNotFound));
        }

        $column = $this->columnLookup[$summaryColumns[0]];

        if($column)
        {
            $column->setType('primaryLink');
        }

        return $this;
    }

    public function getSummarySchema()
    {
        $schema = [];

        foreach($this->summaryColumns as $name)
        {
            $column = $this->columnLookup[$name];
            $schema[$column->name] = (object)[
                'key' => $column->name,
                'label' => $column->label,
                'type' => $column->type
            ];
        }

        return $schema;
    }

    public function getColumn($columnLabel)
    {
        // TODO get SQLColumn from summary columns
        $column = null;

        foreach($this->columnLookup as $col)
        {
            if($col->label == $columnLabel)
            {
                $column = $col;
                break;
            }
        }

        return $column;
    }

    public function getSchema($itemId = -1)
    {
        return $this->getSchemaInternal($itemId, ['hide_allforms']);
    }

    public function getSchemaForEdit($itemId = -1)
    {
        return $this->getSchemaInternal($itemId, ['hide_allforms', 'hide_editform']);
    }

    private function getSchemaInternal($itemId = -1, $hiddenColumnOptions)
    {
        $schema = [];

        foreach($this->columnLookup as $column)
        {
            // determine if the column is hidden
            $hidden = false;
            foreach($hiddenColumnOptions as $option)
            {
                if(array_key_exists($option, $column->options) && ($column->options[$option]))
                {
                    $hidden = true;
                }
            }

            if(!$hidden)
            {
                $schema[$column->name] = (object)[
                    'key' => $column->name,
                    'label' => $column->label,
                    'type' => $column->type,
                    'options' => $column->options
                ];

                if($column instanceof SQLManyToManyColumn || $column instanceof SQLManyToOneColumn)
                {
                    $schema[$column->name]->relationList = $column->relationList();
                }

                // this shouldn't be here - it's returning data as part of the schema
                if($column instanceof SQLManyToManyColumn)
                {
                    $schema[$column->name]->data = $column->dataAsLabelList($itemId);
                }
            }
        }

        return $schema;
    }

    public function splitUpdates($columnValues)
    {
        $splitUpdates = [];

        foreach($this->tableLookup as $tableName => $table)
        {
            Log::debug('PageDescriptor::splitUpdates tableName='.$tableName);

            $updateInfo = [];
            foreach($columnValues as $key => $value)
            {
                Log::debug('PageDescriptor::splitUpdates column key='.$key.' value='.$value);

                if($table->hasColumn($key))
                {
                    Log::debug('PageDescriptor::splitUpdates in lookup');

                    $updateInfo[$key] = $value;
                }
            }

            $splitUpdates[$tableName] = $updateInfo;
        }

        return $splitUpdates;
    }

    private function defaultUpdate($id, $columnValues)
    {
        Log::debug('PageDescriptor::defaultUpdate id='.$id);

        // Ensure your PHP timezone is set correctly. Laravel stores created_at and updated_at using the TIMESTAMP datatype
        // which should be set to local time (database converts to UTC internally)
        $columnValues['updated_at'] = date("Y-m-d H:i:s");

        // the updates can be applied to a database view table, see:
        // see http://dev.mysql.com/doc/refman/5.7/en/view-updatability.html
        // though for some reason you cannot apply all the updated columns together or you get
        //'SQLSTATE[HY000]: General error: 1393 Can not modify more than one base table through a join view
        // hence the columns must be grouped according to the tables that make up the view
        $splitUpdates = $this->splitUpdates($columnValues);

        DB::transaction(function() use (&$id, $splitUpdates)
        {
            foreach($splitUpdates as $updateInfo)
            {
                DB::table($this->getViewTableName())
                    ->where($this->getPrimaryKey(), $id)
                    ->update($updateInfo);
            }
        });
    }

    public function updateManyToMany($inId, $inCol)
    {
        Log::debug('PageDescriptor::updateManyToMany column name='.$inCol->key);

        $col = $this->columnLookup[$inCol->key];
        $relationList = $col->relationList();

        $idMap = [];
        foreach($relationList as $item)
        {
            $idMap[$item->label] = $item->id;
        }

        $outIds = [];
        foreach($inCol->data as $selectedItem)
        {
            $outIds[] = $idMap[$selectedItem['label']];
        }

        // remove duplicates
        $outIds = array_unique($outIds);

        DB::transaction(function() use ($col, $inId, $outIds)
        {
            $linkTable = $col->linkTable;

            // delete existing link table entries
            DB::table($linkTable)->where($col->linkInColumn, '=', $inId)->delete();

            // add new entries
            foreach($outIds as $outId)
            {
                Log::debug('PageDescriptor::updateManyToMany inId='.$inId.' outId='.$outId);

                $linkInfo[$col->linkInColumn] = $inId;
                $linkInfo[$col->linkOutColumn] = $outId;

                DB::table($linkTable)->insert($linkInfo);
            }
        });
    }

    public function mapManyToOneKey($label, $inCol)
    {
        Log::debug('PageDescriptor::mapManyToOneKey selected='.$label);

        $col = $this->columnLookup[$inCol->key];
        $relationList = $col->relationList();

        $id = -1;
        foreach($relationList as $item)
        {
            if($item->label == $label)
            {
                $id =  $item->id;

                Log::debug('PageDescriptor::mapManyToOneKey mapped id='.$id);
                break;
            }
        }

        return $id;
    }

    public function mapManyToOneLabel($id, $inCol)
    {
        Log::debug('PageDescriptor::mapManyToOneLabel selected='.$id);

        $col = $this->columnLookup[$inCol->key];
        $relationList = $col->relationList();

        $label = '';
        foreach($relationList as $item)
        {
            if($item->id == $id)
            {
                $label =  $item->label;

                Log::debug('PageDescriptor::mapManyToOneLabel mapped label='.$label);
                break;
            }
        }

        return $label;
    }
}
