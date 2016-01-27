'use strict';

angular.module('Dashboard')

.controller('BirthdayWidgetCtrl', ['$scope', '$modal',
	function($scope, $modal) {
		$scope.contractView = function(brithday){
			$modal.open({
				scope: $scope,
				templateUrl: '/screen/dashboard/?view=widgets/birthday/contactView',
				controller: 'ContactViewWidgetCtrl',
				resolve: {
					brithday: function() {
						return brithday;
					}
				}
			});
		};
	}
])

.controller('ContactViewWidgetCtrl', ['$scope', '$timeout', '$rootScope', '$modalInstance', 'brithday',
	function($scope, $timeout, $rootScope, $modalInstance, brithday) {
		$scope.brithday = brithday;

		$scope.dismiss = function() {
			$modalInstance.dismiss();
		};
	}
])

.controller('BirthdaySettingsCtrl', ['$scope', '$timeout', '$rootScope', '$modalInstance', 'WidgetData', 'widget',
	function($scope, $timeout, $rootScope, $modalInstance, WidgetData, widget) {
		$scope.widget = widget;


		$scope.form = {
			settings: {
				frame: widget.settings.frame
			}
		};

		$scope.dismiss = function() {
			$modalInstance.dismiss();
		};

		$scope.submit = function() {

			var data = {
				id: widget.id,
				settings: {
					frame: $scope.form.settings.frame
				}
			};

			WidgetData.update(data, $scope, widget);

			$modalInstance.close(widget);
		};

		$scope.$watchCollection('widget', function(newCol, oldCol) {
			if(!(newCol == oldCol)) {
				$scope.$emit('someEvent', {
					massage: 'WidgetSettingsCtrl',
					data: $scope.widgets
				});
			}
		});

	}
]);