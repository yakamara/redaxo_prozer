'use strict';

angular.module('Dashboard')
.run(['$anchorScroll', function($anchorScroll) {
		$anchorScroll.yOffset = 50;   // always scroll by 50 extra pixels
}])
.controller('CalendarWidgetCtrl',  ['$scope', 'WidgetData', '$anchorScroll', '$location',
	function($scope, WidgetData, $anchorScroll, $location) {

		$scope.calendars = [];
		$scope.hours = [];

		var createRange = function(i, e) {
			for(i; i<e; i++) {
				$scope.hours.push(i);
			}
		};
		createRange(0, 24);

		 $scope.createTimeline = function(){
			var time = moment(),
				h = parseInt(time.format('H')),
				m = parseInt(time.format('m'));

			return (h*60)+m;
		};

		$scope.isSameHour = function(h){
			var time = moment();
			if (h == time.format('H')){
				return true;
			}

			return false;
		};

		var gotoAnchor = function(hash) {
			var newHash = hash || 'currentTime';
			if ($location.hash() !== newHash) {
				$location.hash(newHash);
			} else {
				$anchorScroll();
			}
		};


		var TIME_FOMRAMT = 'DD.MM.YYYY';

		var mapName = function(i, day){

			if (i == 1) {
				return i + ' Termin am ' + day.format(TIME_FOMRAMT);
			}
			if (i > 1) {
				return i + ' Termine am ' + day.format(TIME_FOMRAMT);
			}

			return 'keine Termin am ' + day.format(TIME_FOMRAMT);
		};

		//$scope.dragOptions = {
		//	start: function(e) {
		//		console.log("STARTING");
		//	},
		//	drag: function(e) {
		//		console.log("DRAGGING");
		//	},
		//	stop: function(e) {
		//		console.log("STOPPING");
		//	},
		//	container: 'calendargrid'
		//}

		$scope.load = function(calendar) {
			var selector = '#calendar-' + calendar.id;
			var day = moment(calendar.from).format('YYYYMMDD');


			pz_tooltipbox(selector, "/screen/calendars/event/?mode=get_flyout_calendar_event&project_ids=&disable_funcktions=true&day="+day+"&calendar_event_id="+ calendar.id);
		};

		$scope.$watch('widget.day', function(newDay) {

			var day = newDay || moment();
			var data = {
				id: 'CalendarWidget',
				settings: {
					from: day.format()
				}
			};


			WidgetData.select(data).then(function (response) {
				var data = response.data || [];

				$scope.widget.name = mapName(data.length, day);
				$scope.calendars = angular.extend([], data);
				gotoAnchor();
			});

		});
	}
])
.controller('CalendarSettingsCtrl', ['$scope', '$timeout', '$rootScope', '$modalInstance', 'WidgetData', 'widget',
	function($scope, $timeout, $rootScope, $modalInstance, WidgetData, widget) {

		$scope.widget.day = moment();

		$scope.dismiss = function() {
			$modalInstance.dismiss();
		};

		$scope.$on('calendarSelectEvent', function(scope){
			$modalInstance.dismiss();
		});
	}
])
.directive("calendar", function() {
	return {
		restrict: "E",
		templateUrl: '/screen/dashboard/?view=widgets/calendar/calendar',
		scope: {
			selected: "="
		},
		link: function(scope) {
			scope.selected = _removeTime(scope.selected || moment());
			scope.month = scope.selected.clone();

			var start = scope.selected.clone();
			start.date(1);
			_removeTime(start.day(0));

			_buildMonth(scope, start, scope.month);

			scope.select = function(day) {
				scope.selected = day.date;

				scope.$emit('calendarSelectEvent', { data: scope });
			};

			scope.next = function() {
				var next = scope.month.clone();
				_removeTime(next.month(next.month()+1).date(1));
				scope.month.month(scope.month.month()+1);
				_buildMonth(scope, next, scope.month);

				scope.$emit('calendarNextEvent', { data: scope });
			};

			scope.previous = function() {
				var previous = scope.month.clone();
				_removeTime(previous.month(previous.month()-1).date(1));
				scope.month.month(scope.month.month()-1);
				_buildMonth(scope, previous, scope.month);

				scope.$emit('calendarPreviousEvent', { data: scope });
			};
		}
	};

	function _removeTime(date) {
		return date.day(0).hour(0).minute(0).second(0).millisecond(0);
	}

	function _buildMonth(scope, start, month) {
		scope.weeks = [];
		var done = false, date = start.clone(), monthIndex = date.month(), count = 0;
		while (!done) {
			scope.weeks.push({ days: _buildWeek(date.clone(), month) });
			date.add(1, "w");
			done = count++ > 2 && monthIndex !== date.month();
			monthIndex = date.month();
		}
	}

	function _buildWeek(date, month) {
		var days = [];
		for (var i = 0; i < 7; i++) {
			days.push({
				name: date.format("dd").substring(0, 1),
				number: date.date(),
				isCurrentMonth: date.month() === month.month(),
				isToday: date.isSame(new Date(), "day"),
				date: date
			});
			date = date.clone();
			date.add(1, "d");
		}
		return days;
	}
});

/*
.directive('ngDraggable', function($document) {
	return {
		restrict: 'A',
		scope: {
			dragOptions: '=ngDraggable'
		},
		link: function(scope, elem, attr) {
			var startX, startY, x = 0, y = 0,
					start, stop, drag, container;

			var width  = elem[0].offsetWidth,
					height = elem[0].offsetHeight;

			// Obtain drag options
			if (scope.dragOptions) {
				start  = scope.dragOptions.start;
				drag   = scope.dragOptions.drag;
				stop   = scope.dragOptions.stop;
				var id = scope.dragOptions.container;
				if (id) {
					container = document.getElementById(id).getBoundingClientRect();
				}
			}

			// Bind mousedown event
			elem.on('mousedown', function(e) {
				e.preventDefault();
				startX = e.clientX - elem[0].offsetLeft;
				startY = e.clientY - elem[0].offsetTop;
				$document.on('mousemove', mousemove);
				$document.on('mouseup', mouseup);
				if (start) start(e);
			});

			// Handle drag event
			function mousemove(e) {
				y = e.clientY - startY;
				x = e.clientX - startX;
				setPosition();
				if (drag) drag(e);
			}

			// Unbind drag events
			function mouseup(e) {
				$document.unbind('mousemove', mousemove);
				$document.unbind('mouseup', mouseup);
				if (stop) stop(e);
			}

			// Move element, within container if provided
			function setPosition() {
				if (container) {
					if (x < container.left) {
						x = container.left;
					} else if (x > container.right - width) {
						x = container.right - width;
					}
					if (y < container.top) {
						y = container.top;
					} else if (y > container.bottom - height) {
						y = container.bottom - height;
					}
				}

				elem.css({
					top: y + 'px',
					//left:  x + 'px'
				});
			}
		}
	}

});*/