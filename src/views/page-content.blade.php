@extends('laravel-crudkit::core-admin-panel')

@section('laravel-crudkit-action-buttons')

@if ($creatable)
<a href="{{ url('admin-panel-add-item?page='.$page) }}" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Add New</a>
@endif

@endsection


@section('laravel-crudkit-page-content')

<div ng-controller="SummaryTableController" >
    <div class="input-group col-md-6">
        <div class="col-md-6">
            <select class="form-control" name="searchcolumn" ng-model="searchcolumn" ng-change="pageChanged()">
                <option class="list-group-item" ng-repeat="col in columns">{% col.label %}</option>
            </select>
        </div>
        <div class="input-group col-md-6">
            <input class="form-control" type='text' ng-model="searchtext" ng-keyup="$event.keyCode == 13 ? pageChanged() : null"/>
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" ng-click="pageChanged()">Search</button>
            </span>
        </div>
    </div>
    <div cg-busy="loadingPromise">
        <table class="table">
            <thead>
            <tr>
                <th style="width: 30px">
                    <input type='checkbox' ng-change="selectAll ()" ng-model="allSelectedFlag" class="table-checkbox" />
                </th>
                <th ng-repeat="col in columns">
                    {% col.label %}
                </th>
            </tr>
            </thead>
            <tbody>
                <tr ng-repeat="row in rows" ng-class="{'info': row.selectedFlag}">
                    <td>
                        <input ng-model="row.selectedFlag" type="checkbox" class="table-checkbox" ng-change="updateSelectedCount ()"/>
                    </td>
                    <td ng-repeat="col in columns">
                        <div ng-switch on="col.type">
                            <div ng-switch-when="string">
                                {% row[col.key] %}
                            </div>
                            <div ng-switch-when="textarea">
                                {% row[col.key].substr(0, 50) + '...' %}
                            </div>
                            <div ng-switch-when="email">
                                {% row[col.key] %}
                            </div>
                            <div ng-switch-when="number">
                                {% row[col.key] %}
                            </div>
                            <div ng-switch-when="price">
                                {% row[col.key] | currency:"&pound;" %}
                            </div>
                            <div ng-switch-when="percentage">
                                {% row[col.key] %}%
                            </div>
                            <div ng-switch-when="enum">
                                {% row[col.key] %}
                            </div>
                            <div ng-switch-when="datetime">
                                {% row[col.key] %}
                            </div>
                            <div ng-switch-when="boolean">
                                {% booleanToString(row[col.key]) %}
                            </div>
                            <div ng-switch-when="primaryLink">
                                <a ng-href="{% itemLink(row) %}">{% row[col.key] %}</a>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <pagination total-items="rowCount" ng-model="currentPage" items-per-page=10 max-size=10 rotate="false" boundary-links="false" ng-change="pageChanged()"></pagination>
    </div>
</div>

@endsection