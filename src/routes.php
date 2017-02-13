<?php


Route::group(['middleware' => 'web', 'namespace' => 'Markpurser\LaravelCrudKit'], function () {

	// Laravel CrudKit Admin Panel Routes
	Route::get('/admin-panel', 'AdminPanelController@index');
	Route::get('/admin-panel-view-item', 'AdminPanelController@viewItem');
	Route::get('/admin-panel-edit-item', 'AdminPanelController@editItem');
	Route::get('/admin-panel-add-item', 'AdminPanelController@addItem');
	Route::get('/admin-panel-delete-item', 'AdminPanelController@deleteItem');

	Route::get('/admin-panel/api/getschema', 'AdminPanelController@getSchema');
	Route::get('/admin-panel/api/getrows', 'AdminPanelController@getRows');
	Route::get('/admin-panel/api/getrecord', 'AdminPanelController@getRecord');
	Route::post('/admin-panel/api/create', 'AdminPanelController@create');
	Route::post('/admin-panel/api/update', 'AdminPanelController@update');

	Route::get('/admin-panel-error', 'AdminPanelController@error');

});