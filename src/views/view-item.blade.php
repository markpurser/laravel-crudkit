@extends('laravel-crudkit::core-admin-panel')


@section('laravel-crudkit-action-buttons')

@if ($updatable)
<a class="btn btn-primary pull-right" href="{{ url('admin-panel/edit-item?page='.$page.'&item-id='.$itemId) }}" style="margin-left: 10px;"><i class="fa fa-pencil"></i> Edit</a>
@endif

@if ($deletable)
<a class="btn btn-danger pull-right" href="{{ url('admin-panel/delete-item?page='.$page.'&item-id='.$itemId) }}"><i class="fa fa-trash"></i> Delete</a>
@endif

@endsection


@section('laravel-crudkit-page-content')

<div ng-controller="ViewFormController" >
    <div>
        <dl class="dl-horizontal">
            <div ng-repeat="col in columns" class="form-group" class="{% col.key %}">
                <dt>{% col.label %}</dt>

                <dd ng-if="col.type == 'datetime'" style="margin-top: 15px">{% col.data %} </dd>

                <dd ng-if="col.type == 'boolean'" style="margin-top: 15px">{% booleanToString(col.data) %} </dd>

                <dd ng-if="col.type == 'price'" style="margin-top: 15px">{% col.data | currency:"{{ config('crudkit.currency_symbol') }}" %} </dd>

                <dd ng-if="col.type == 'percentage'" style="margin-top: 15px">{% col.data %}% </dd>

                <dd ng-if="(col.type == 'string') || (col.type == 'primaryLink') || (col.type == 'textarea') || (col.type == 'number') || (col.type == 'email') || (col.type == 'enum') || (col.type == 'editabledropdown') || (col.type == 'manytoone')" style="margin-top: 15px">{% col.data %} </dd>

                <dd ng-if="col.type == 'manytomany'">
                    <div ng-if="col.data.length == 0">none</div>
                    <li ng-repeat="item in col.data">{% item.label %}</li>
                </dd>
            </div>
        </dl>
    </div>
</div>


@endsection