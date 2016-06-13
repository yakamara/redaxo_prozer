'use strict';

angular.module('Dashboard')

.factory('SettingFactory', function($http) {
    var service = {};

    var sync = true;

    var syncCount = 0;

    var url = '/screen/dashboard/widgetsSettings';

    service.get = function () {
        return $http.post(url);
    };

    service.Sync = function(data) {

        if(sync === true && syncCount > 0) {
            sync = false;

            $http.post(url, { 'query' : data })
                .then(function successCallback(response) {
                    sync  = true;
                }, function errorCallback() {
                    sync  = true;
                });

        }
        syncCount++;
    };

    return service;
})

.factory('DataFormat', function() {
    /**
     * Das Rendern des DataFormat muss in ein WebWoker ausgelagert werden.
     *
     */
    var service = {};

    service.changeResponse = function (data) {

        var dashboard = {
            id: '1',
            name: 'Home',
            widgets: [],
            active: [],
            map: {
                id: 'widget.id',
                name: 'widget.name',
                active: 'widget.active',
                col: 'widget.widgetposition.col',
                row: 'widget.widgetposition.row',
                sizeY: 'widget.widgetposition.sizey',
                sizeX: 'widget.widgetposition.sizex',
                controller: 'widget.view.controller',
                url: 'widget.view.url'
            }
        };


        angular.forEach(data, function(value) {
            if(value.active === true) {
                this.active.push(value);
            }

            this.widgets.push(value);

        }, dashboard);

        return dashboard;

    };

    return service;
})

.factory('WidgetData', function($http) {
    var service = {};

    var $widget = {};

    var url = '/screen/dashboard/widgetData';

    service.select = function (data) {
        return $http.post(url, { 'query' : data });
    };

    service.update = function (data, $scope, widget) {
        return $http.post(url, { 'query' : data })
            .then(function successCallback(response) {
                angular.extend($scope.form, response);
                angular.extend(widget, $scope.form);
            }, function errorCallback() {

            });
    };

    return service;

});