'use strict';

(function() {
    angular.module('Dashboard', ['gridster', 'ui.bootstrap', 'ngRoute', 'angularjs-dropdown-multiselect'])

        /**
         *
         *
         */
        .controller('RootCtrl', ['$scope', '$http', 'SettingFactory', 'DataFormat',
        function($scope, $http, SettingFactory, DataFormat) {

            SettingFactory.get().then(function successCallback(response) {
                var dashboard = DataFormat.changeResponse(response.data) || {};

                $scope.Mytemplate = '/screen/dashboard/?view=view';

                $scope.widgets = dashboard.active;
                $scope.allWidgets = response.data;
                $scope.wigetsMap =  dashboard.map;


                $scope.selectSettings = {
                    displayProp: 'name',
                    scrollableHeight: '250px',
                    scrollable: true,
                    buttonClasses: 'btn btn-entity-select selected',
                    smartButtonMaxItems: 10,
                    dynamicTitle: true,
                    externalIdProp: ''
                };

                $scope.selectTranslation = {
                    buttonDefaultText: 'Wähle deine Widgets',
                    checkAll: 'Alle wählen',
                    uncheckAll: 'Alle löschen'
                };

                $scope.dashboard =  {
                    widgets: dashboard.active
                };

            }, function errorCallback(response) {
                // called asynchronously if an error occurs
                // or server returns response with an error status.

            });

            $scope.$on('someEvent', function() {
                var event = {};

                for(var i=0;i<$scope.widgets.length;i++){
                    $scope.widgets[i].active = true;
                    event[$scope.widgets[i].id] = angular.copy($scope.widgets[i]);
                    event[$scope.widgets[i].id].active = true;
                    event[$scope.widgets[i].id].data = [];
                }
                SettingFactory.Sync(event);
            });
        }
    ]);

})();