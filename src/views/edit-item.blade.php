@extends('laravel-crudkit::core-admin-panel')


@section('laravel-crudkit-action-buttons')

@endsection


@section('laravel-crudkit-page-content')

<div ng-controller="EditFormController" >
    <form id="editForm" name="editForm">
    <div>
        <dl class="dl-horizontal">
            <div ng-repeat="col in columns" class="form-group" class="{% col.key %}">
                <label for="{% col.key %}">{% col.label %}</label>

                <span class="error" ng-show="editForm.{%col.key%}.$error.required" style="color:red">*</span>

                &nbsp<span ng-if="col.options.tip" style="color:gray" class="fa fa-question-circle" title="{% col.options.tip %}"></span>

                <span ng-if="col.type == 'manytomany'" ><button class="btn-link btn-secondary btn-sm" ng-click="addAllManyToMany(col)">Add All</button></span>

                <div>

                    <div ng-if="col.type == 'datetime'" style="max-width: 250px">
                        <div class="input-group input-group-sm date" options="{% date_display_options %}" datetimepicker ng-model="col.data" ng-change="registerChange(col.key)">
                            <input type="text" class="form-control" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>

                    <input ng-if="col.type == 'boolean'" type="checkbox" class="checkbox" id="{% col.key %}" ng-model="col.data" ng-change="registerChange(col.key)" ng-true-value="1" ng-false-value="0"/>

                    <input ng-if="(col.type == 'string') || (col.type == 'primaryLink')" type="text" class="form-control" id="{% col.key %}" name="{% col.key %}" ng-model="col.data" ng-change="registerChange(col.key)" ng-required="{% col.options.required %}" ng-maxlength="col.options.max" ng-disabled="col.options.disabled || (col.options.greyed && (itemId > -1))" />
                    <div role="alert">
                        <span class="error" ng-show="editForm.{%col.key%}.$error.maxlength" style="color:red">Too long!</span>
                    </div>

                    <textarea ng-if="(col.type == 'textarea')" type="text" class="form-control" id="{% col.key %}" name="{% col.key %}" ng-model="col.data" ng-change="registerChange(col.key)" ng-required="{% col.options.required %}" ng-maxlength="col.options.max" ng-disabled="col.options.disabled || (col.options.greyed && (itemId > -1))"></textarea>
                    <div role="alert">
                        <span class="error" ng-show="editForm.{%col.key%}.$error.maxlength" style="color:red">Too long!</span>
                    </div>

                    <input ng-if="(col.type == 'number') || (col.type == 'percentage')" type="number" class="form-control" id="{% col.key %}" name="{% col.key %}" ng-model="col.data" ng-change="registerChange(col.key)" ng-required="{% col.options.required %}"/>

                    <input ng-if="(col.type == 'price')" type="number" min="0" step="0.01" class="form-control" id="{% col.key %}" name="{% col.key %}" ng-model="col.data" ng-change="registerChange(col.key)" ng-required="{% col.options.required %}"/>

                    <input ng-if="(col.type == 'email')" type="email" class="form-control" id="{% col.key %}" name="{% col.key %}" ng-model="col.data" ng-change="registerChange(col.key)" ng-required="{% col.options.required %}" ng-disabled="col.options.disabled || (col.options.greyed && (itemId > -1))" />
                    <div role="alert">
                        <span class="error" ng-show="editForm.{%col.key%}.$error.email" style="color:red">Invalid email address!</span>
                    </div>

                    <select ng-if="(col.type == 'enum')" class="list-group" name="{% col.key %}" ng-model="col.data" ng-change="registerChange(col.key)">
                        <option class="list-group-item" ng-repeat="option in col.options.enum">{% option %}</option>
                    </select>

                    <input ng-if="(col.type == 'editabledropdown')" type="text" class="list-group" name="{% col.key %}" ng-model="col.data" ng-change="registerChange(col.key)" ng-required="{% col.options.required %}">
                    <select ng-if="(col.type == 'editabledropdown') && col.options.dropdownlist.length > 0" class="list-group" name="{% col.key %}" ng-model="col.data" ng-change="registerChange(col.key)" ng-required="{% col.options.required %}">
                        <option class="list-group-item" ng-repeat="option in col.options.dropdownlist">{% option %}</option>
                    </select>

                    <ul ng-if="col.type == 'manytomany'">
                        <li ng-repeat="item in col.data">
                            <select class="list-group" ng-model="item.label" ng-change="registerChange(col.key)">
                                <option class="list-group-item" ng-repeat="option in col.relationList">{% option.label %}</option>
                            </select>
                            <button class="btn-link btn-secondary btn-sm" ng-click="removeManyToMany(col, item)">Remove</button>
                        </li>
                        <li><button class="btn-link btn-secondary" ng-click="addManyToMany(col)">Add</button></li>
                    </ul>

                    <select ng-if="col.type == 'manytoone'" class="list-group" name="{% col.key %}" ng-model="col.data" ng-change="registerChange(col.key)" ng-required="{% col.options.required %}">
                        <option class="list-group-item" ng-repeat="option in col.relationList">{% option.label %}</option>
                    </select>

                </div>
            </div>
        </dl>
    </div>

    <a class="btn btn-primary btn-lg" ng-click="saveValues()" style="margin-top:20px" ng-disabled='!dirtyFlag || !editForm.$valid'><i class="fa fa-save"></i> Save</a>

    <a class="btn btn-danger btn-lg" ng-click="cancel()" style="margin-top:20px" ><i class="fa fa-cancel"></i> Cancel</a>

    </form>
</div>

@endsection