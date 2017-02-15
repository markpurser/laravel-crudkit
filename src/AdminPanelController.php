<?php

namespace Markpurser\LaravelCrudKit;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Log;
use Exception;


class AdminPanelController extends Controller
{
    private $pageStore = null;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function setPageStore($pageStore)
    {
        $this->pageStore = $pageStore;
    }

    /**
     * Display admin panel
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $pageName = null;

        if(!$this->pageStore)
        {
            return redirect()->action('\Markpurser\LaravelCrudKit\AdminPanelController@error', ['message' => 'No pages found.']);
        }

        if( $request->has('page') )
        {
            $pageName = $request->input('page');
        }

        $pageDescriptor = $this->pageStore->getPageDescriptor($pageName);

        return view('laravel-crudkit::page-content', [
            'page' => $pageName,
            'creatable' => $pageDescriptor->isCreatable(),
            'primaryKey' => $pageDescriptor->getPrimaryKey(),
            'itemId' => -1,
            'pageLabel' => $pageDescriptor->getLabel(),
            'pageMap' => $this->pageStore->getPageMap()
        ]);
    }

    /**
     * Display record
     *
     * @param  Request  $request
     * @return Response
     */
    public function viewItem(Request $request)
    {
        $page = $request->input('page');
        $itemId = $request->input('item-id');

        $pageDescriptor = $this->pageStore->getPageDescriptor($page);

        return view('laravel-crudkit::view-item', [
            'page' => $page,
            'updatable' => $pageDescriptor->isUpdatable(),
            'deletable' => $pageDescriptor->isDeletable(),
            'primaryKey' => $pageDescriptor->getPrimaryKey(),
            'itemId' => $itemId,
            'pageLabel' => $pageDescriptor->getLabel(),
            'pageMap' => $this->pageStore->getPageMap()
        ]);
    }

    /**
     * Edit record
     *
     * @param  Request  $request
     * @return Response
     */
    public function editItem(Request $request)
    {
        $page = $request->input('page');
        $itemId = $request->input('item-id');

        $pageDescriptor = $this->pageStore->getPageDescriptor($page);

        return view('laravel-crudkit::edit-item', [
            'newItem' => false,
            'page' => $page,
            'primaryKey' => $pageDescriptor->getPrimaryKey(),
            'itemId' => $itemId,
            'pageLabel' => $pageDescriptor->getLabel(),
            'pageMap' => $this->pageStore->getPageMap()
        ]);
    }

    /**
     * Add record
     *
     * @param  Request  $request
     * @return Response
     */
    public function addItem(Request $request)
    {
        $page = $request->input('page');

        $pageDescriptor = $this->pageStore->getPageDescriptor($page);

        return view('laravel-crudkit::edit-item', [
            'newItem' => true,
            'page' => $page,
            'primaryKey' => $pageDescriptor->getPrimaryKey(),
            'itemId' => -1,
            'pageLabel' => $pageDescriptor->getLabel(),
            'pageMap' => $this->pageStore->getPageMap()
        ]);
    }

    /**
     * Delete record
     *
     * @param  Request  $request
     * @return Response
     */
    public function deleteItem(Request $request)
    {
        $pageName = $request->input('page');
        $itemId = $request->input('item-id');

        Log::debug('AdminPanelController::delete page name='.$pageName.' itemId='.$itemId);

        $pageDescriptor = $this->pageStore->getPageDescriptor($pageName);

        $pageDescriptor->delete($itemId);

        return redirect()->action('\Markpurser\LaravelCrudKit\AdminPanelController@index', ['page' => $pageName]);
    }

    /**
     * ajax request/responses
     *
     * @param  Request  $request
     * @return Response
     */
    public function getSchema(Request $request)
    {
        $pageName = $request->input('page');

        $pageDescriptor = $this->pageStore->getPageDescriptor($pageName);

        $response = response()->json($pageDescriptor->getSummarySchema());

        return $response;
    }

    /**
     * ajax request/responses
     *
     * @param  Request  $request
     * @return Response
     */
    public function getRows(Request $request)
    {
        $pageName = $request->input('page');
        $currentPage = $request->input('currentpage');
        $itemsPerPage = $request->input('itemsperpage');
        $searchcolinput = $request->input('searchcolumn');
        $searchtextinput = $request->input('searchtext');

        $pageDescriptor = $this->pageStore->getPageDescriptor($pageName);

        $searchColumn = $pageDescriptor->getColumn($searchcolinput);

        // build search term
        $hasBooleanSearchTerm = false;
        $hasWordSearchTerm = false;
        $searchTerm = '';
        if(!is_null($searchColumn) && !empty($searchtextinput))
        {
            if($searchColumn->type == 'boolean')
            {
                $hasBooleanSearchTerm = true;
                $searchTerm = strncmp($searchtextinput, 'y', 1) === 0 ? true : false;
            }
            else {
                $hasWordSearchTerm = true;
                $searchTerm = '%'.$searchtextinput.'%';
            }
        }

        $whereDeletedFalse = function ($query) {
            return $query->where( 'deleted', false );
        };

        $whereBooleanMatch = function ($query) use ($searchColumn, $searchTerm) {
            return $query->where( $searchColumn->name, $searchTerm );
        };

        $whereWordMatch = function ($query) use ($searchColumn, $searchTerm) {
            return $query->where( $searchColumn->name, 'like', $searchTerm );
        };

        $rows['rowCount'] = DB::table( $pageDescriptor->getViewTableName() )
                        ->when( $pageDescriptor->hasSoftDelete(), $whereDeletedFalse )
                        ->when( $hasBooleanSearchTerm, $whereBooleanMatch )
                        ->when( $hasWordSearchTerm, $whereWordMatch )
                        ->count();

        $rows['rows'] = DB::table( $pageDescriptor->getViewTableName() )
                        ->when( $pageDescriptor->hasSoftDelete(), $whereDeletedFalse )
                        ->when( $hasBooleanSearchTerm, $whereBooleanMatch )
                        ->when( $hasWordSearchTerm, $whereWordMatch )
                        ->offset( ($currentPage-1)*$itemsPerPage )
                        ->limit( $itemsPerPage )
                        ->get();

        $response = response()->json($rows);

        return $response;
    }

    /**
     * ajax request/responses
     *
     * @param  Request  $request
     * @return Response
     */
    public function getRecord(Request $request)
    {
        $pageName = $request->input('page');
        $itemId = $request->input('item-id');
        $editForm = $request->input('edit-form')=='true'?true:false;

        $pageDescriptor = $this->pageStore->getPageDescriptor($pageName);

        if($editForm)
        {
            $columns = $pageDescriptor->getSchemaForEdit($itemId);
        }
        else {
            $columns = $pageDescriptor->getSchema($itemId);
        }


        if( $itemId == -1 )
        {
            // new record
            $columns = $pageDescriptor->setInitialValues($columns);
        }
        else {
            // requesting a row of an existing record
            $row = DB::table($pageDescriptor->getViewTableName())->where($pageDescriptor->getPrimaryKey(), $itemId)->get();

            $row = (array)$row[0];

            foreach ($columns as $key => $col)
            {
                if(array_key_exists($key, $row))
                {
                    $coldata = $row[$key];

                    if( $col->type == 'manytoone' )
                    {
                        $coldata = $pageDescriptor->mapManyToOneLabel($coldata, (object)$col);
                    }

                    $col->data = $coldata;
                }
            }
        }

        $response = response()->json($columns, 200, [], JSON_NUMERIC_CHECK);

        return $response;
    }

    /**
     * ajax request/responses
     *
     * @param  Request  $request
     * @return Response
     */
    public function create(Request $request)
    {
        $pageName = $request->input('page');
        $data = $request->json()->all();

        Log::debug('AdminPanelController::create page name='.$request->input('page'));

        $pageDescriptor = $this->pageStore->getPageDescriptor($pageName);

        $extractData = [];
        foreach($data as $col)
        {
            if( ($col['type'] != 'manytomany') && array_key_exists('data', $col) )
            {
                $coldata = $col['data'];

                if( array_key_exists('nullable', $col['options']) && empty($coldata) )
                {
                    $coldata = null;
                }

                if( $col['type'] == 'manytoone' )
                {
                    $coldata = $pageDescriptor->mapManyToOneKey($coldata, (object)$col);
                }

                $extractData[$col['key']] = $coldata;
            }
        }

        try {
            list($id, $error) = $pageDescriptor->create($extractData);
        }
        catch(Exception $e)
        {
            return response()->json(['error' => ''.$e]);
        }

        if(($id == -1) || (!is_null($error)))
        {
            return response()->json(['error' => $error]);
        }

        // handle many to many relationships
        foreach($data as $col)
        {
            if( ($col['type'] == 'manytomany') && array_key_exists('data', $col) )
            {
                $pageDescriptor->updateManyToMany($id, (object)$col);
            }
        }
    }

    /**
     * ajax request/responses
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {
        $pageName = $request->input('page');
        $itemId = $request->input('item-id');
        $data = $request->json()->all();

        Log::debug('AdminPanelController::update page name='.$request->input('page'));

        $pageDescriptor = $this->pageStore->getPageDescriptor($pageName);

        $extractData = [];
        foreach($data as $col)
        {
            if( ($col['type'] != 'manytomany') && array_key_exists('data', $col) )
            {
                $coldata = $col['data'];

                if( array_key_exists('nullable', $col['options']) && empty($coldata) )
                {
                    $coldata = null;
                }

                if( $col['type'] == 'manytoone' )
                {
                    $coldata = $pageDescriptor->mapManyToOneKey($coldata, (object)$col);
                }

                $extractData[$col['key']] = $coldata;
            }
        }

        $pageDescriptor->update($itemId, $extractData);

        // handle many to many relationships
        foreach($data as $col)
        {
            if( ($col['type'] == 'manytomany') && array_key_exists('data', $col) )
            {
                $pageDescriptor->updateManyToMany($itemId, (object)$col);
            }
        }
    }

    /**
     * Error page
     *
     * @param  Request  $request
     * @return Response
     */
    public function error(Request $request)
    {
        $message = $request->input('message');

        return view('laravel-crudkit::errors.admin-panel-error', [
            'message' => $message,
            'page' => 'none',
            'primaryKey' => -1,
            'itemId' => -1,
            'pageLabel' => 'Error',
            'pageMap' => []
        ]);
    }

}
