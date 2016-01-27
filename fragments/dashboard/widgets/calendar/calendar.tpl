<div class="header">
    <i class="fa fa-angle-left glyphicon glyphicon-arrow-left" ng-click="previous()"></i>
    <span>{{month.format("MMMM, YYYY")}}</span>
    <i class="fa fa-angle-right glyphicon glyphicon-arrow-right" ng-click="next()"></i>
</div>
<div class="week names">
    <span class="day">So.</span>
    <span class="day">Mo.</span>
    <span class="day">Di.</span>
    <span class="day">Mi.</span>
    <span class="day">Do.</span>
    <span class="day">Fr.</span>
    <span class="day">Sa.</span>
</div>
<div class="week" ng-repeat="week in weeks">
    <span class="day" ng-class="{ today: day.isToday, 'different-month': !day.isCurrentMonth, selected: day.date.isSame(selected) }" ng-click="select(day)" ng-repeat="day in week.days">{{day.number}}</span>
</div>