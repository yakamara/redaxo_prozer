'use strict';

angular.module('Dashboard')

.controller('DashboardCtrl', ['$scope', '$timeout',
	function($scope) {
		$scope.gridsterOptions = {
			margins: [10, 10],
			columns: 4,
			rowHeight: 'match',
			draggable: {
				enabled: false,
				handle: 'h3',
				stop: function() {
					$scope.$emit('someEvent', {
						massage: 'Grister',
						data: $scope.widgets
					});
				}
			},
			resizable: {
				enabled: false,
				//handles: ['n', 'e', 's', 'w', 'ne', 'se', 'sw', 'nw'],
				stop: function() {
					$scope.$emit('someEvent', {
						massage: 'Grister',
						data: $scope.widgets
					});
				}
			}
		};

		$scope.gridSetting = function() {
			$scope.gridsterOptions.resizable.enabled = !$scope.gridsterOptions.resizable.enabled;
			$scope.gridsterOptions.draggable.enabled = !$scope.gridsterOptions.draggable.enabled;
		};

		$scope.$watchCollection('widgets', function(newCol, oldCol) {
			if(!(newCol === oldCol)) {
				$scope.$emit('someEvent', {
					massage: 'DashboardCtrl',
					data: $scope.widgets
				});
			}
		});
	}
])

.controller('CustomWidgetCtrl', ['$scope', '$modal', '$filter',
	function($scope, $modal) {

		$scope.remove = function(widget) {
            $scope.widgets.splice($scope.widgets.indexOf(widget), 1);
		};

		$scope.openSettings = function(widget) {
			if(widget.settingView.controller) {
				$modal.open({
					scope: $scope,
					templateUrl: widget.settingView.url,
					controller: widget.settingView.controller,
					resolve: {
						widget: function() {
							return widget;
						}
					}
				});
			}
		};

		$scope.$watchCollection('', function(newCol, oldCol, scope) {
			if(!(newCol === oldCol)) {
				$scope.$emit('someEvent', {
					massage: 'CustomWidgetCtrl',
					scope: scope,
					data: $scope.widgets
				});
			}
		});
	}
])

.filter('getById', function() {
    return function(items, item) {
        for(var i=0;i<items.length;i++) {
            if(items[i].id === item.id){
                return items[i]
            }
        }
        return null;
    }
});
