
var app = angular.module("apApp", ['apConfig', 'ui.bootstrap', 'ae-datetimepicker'], function($interpolateProvider) {
        $interpolateProvider.startSymbol('{%');
        $interpolateProvider.endSymbol('%}');
    });


// in:  dateStr   date string with format "YYYY-mm-dd HH:ii:ss"
// out: Date      moment.js Date object
function strToDate(dateStr)
{
    var y = parseInt(dateStr.substr(0,4));
    var m = parseInt(dateStr.substr(5,2)) - 1;
    var d = parseInt(dateStr.substr(8,2));
    var h = parseInt(dateStr.substr(11,2));
    var i = parseInt(dateStr.substr(14,2));
    var s = parseInt(dateStr.substr(17,2));
    return new Date(y, m, d, h, i, s);
}

// in:  Date      moment.js Date object
// out: dateStr   date string with format "YYYY-mm-dd HH:ii:ss"
function dateToStr(date)
{
    return date.format("YYYY-MM-DD HH:mm:ss");
}

app.controller("SummaryTableController", function ($scope, $http, Page, PrimaryKey) {

    $scope.page = Page;
    $scope.primaryKey = PrimaryKey;
    $scope.pageCount = 1;
    $scope.schema = [];
    $scope.allSelectedFlag = false;
    $scope.selectedCount = 0;
    $scope.rows = [];
    $scope.columns = {};

    // search
    $scope.searchcolumn = "";
    $scope.searchtext = "";

    // pagination variables
    $scope.rowCount = 1;
    $scope.currentPage = 1;

    $scope.itemLink = function (row) {
        return '/admin-panel/view-item?page=' + $scope.page + '&item-id=' + row['id'];
    };

    $scope.booleanToString = function (val) {
        return parseInt(val)?'yes':'no';
    };

    $scope.pageChanged = function () {
        update_data ();
    };

    var update_data = function () {
        $http.get('/admin-panel/api/getrows?page='+$scope.page+"&currentpage="+$scope.currentPage+"&searchcolumn="+$scope.searchcolumn+"&searchtext="+$scope.searchtext).then(function (data) {
            $scope.rows.length = 0;
            $scope.rows = data.data.rows;
            $scope.rowCount = data.data.rowCount;
        },
        function(err){
            window.location.href = "/admin-panel/error?message=" + err.statusText;
        });
    };

    $http.get('/admin-panel/api/getschema?page='+$scope.page).then(function (data) {
        $scope.columns = data.data;
        console.log($scope.columns);
        update_data ();
    },
    function(err){
        window.location.href = "/admin-panel/error?message=" + err.statusText;
    });
});

app.controller("ViewFormController", function ($scope, $http, Page, PrimaryKey, ItemId) {

    $scope.date_display_options = '{format:"DD MMMM YYYY HH:mm"}';

    $scope.page = Page;
    $scope.primaryKey = PrimaryKey;
    $scope.itemId = ItemId;
    $scope.columns = {};
    $scope.dirtyFlag = false;

    $scope.booleanToString = function (val) {
        return parseInt(val)?'yes':'no';
    };

    $http.get('/admin-panel/api/getrecord?page='+$scope.page+'&item-id='+$scope.itemId+'&edit-form=false').then(function (data) {
        $scope.columns = data.data;
    },
    function(err){
        window.location.href = "/admin-panel/error?message=" + err.statusText;
    });

});

app.controller("EditFormController", function ($scope, $http, Page, PrimaryKey, ItemId) {

    $scope.date_display_options = '{format:"DD MMMM YYYY HH:mm"}';

    $scope.page = Page;
    $scope.primaryKey = PrimaryKey;
    $scope.itemId = ItemId;
    $scope.columns = {};
    $scope.dirtyFlag = false;
    $scope.doubleClick = false;

    $scope.registerChange = function (key) {
        // $scope.changedValues[key] = $scope.formItems[key];
        // $scope.extraClasses[key] = "has-change";
        $scope.dirtyFlag = true;
    };

    $scope.saveValues = function () {
        if (!$scope.dirtyFlag || !$scope.editForm.$valid || $scope.doubleClick)
            return;

        $scope.doubleClick = true;

        _.forEach($scope.columns, function(col) {
            if(col.data && (col.type === 'datetime'))
            {
                col.data = dateToStr(col.data);
            }
        });

        if($scope.itemId == -1) {

            $http.post('/admin-panel/api/create?page='+$scope.page, $scope.columns).then(function (data) {
                console.log(data);
                if(data.data.error)
                {
                    window.location.href = "/admin-panel/error?message=" + data.data.error;
                }
                else {
                    window.location.href = "/admin-panel?page=" + $scope.page;
                }
            },
            function(err){
                window.location.href = "/admin-panel/error?message=" + err.statusText;
            });
        }
        else {

            $http.post('/admin-panel/api/update?page='+$scope.page+'&item-id='+$scope.itemId, $scope.columns).then(function (data) {
                window.location.href = "/admin-panel?page=" + $scope.page;
            },
            function(err){
                window.location.href = "/admin-panel/error?message=" + err.statusText;
            });
        }
    }

    $scope.cancel = function () {
        window.location.href = "/admin-panel?page=" + $scope.page;
    }

    $scope.addManyToMany = function (col) {
        if(col.relationList.length > 0)
        {
            $scope.dirtyFlag = true;
            if(typeof col.data === 'undefined')
            {
                col.data = [{label: col.relationList[0].label}];
            }
            else {
                col.data.push({label: col.relationList[0].label});
            }
        }
    };

    $scope.addAllManyToMany = function (col) {
        if(col.relationList.length > 0)
        {
            $scope.dirtyFlag = true;
            col.data = [];

            _.forEach(col.relationList, function(relation) {
                col.data.push({label: relation.label});
            });
        }
    };

    $scope.removeManyToMany = function (col, item) {
        $scope.dirtyFlag = true;
        var index = col.data.indexOf(item);
        col.data.splice(index, 1);
    };

    $http.get('/admin-panel/api/getrecord?page='+$scope.page+'&item-id='+$scope.itemId+'&edit-form=true').then(function (data) {
        $scope.columns = data.data;
        console.log($scope.columns);

        _.forEach($scope.columns, function(col) {
            if(col.data && (col.type === 'datetime'))
            {
                col.data = strToDate(col.data);
            }
        });
    },
    function(err){
        window.location.href = "/admin-panel/error?message=" + err.statusText;
    });
    
});

