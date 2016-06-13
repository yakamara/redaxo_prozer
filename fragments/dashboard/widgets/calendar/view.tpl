<div class="calendar view-day" style="position: relative;" ng-controller="CalendarWidgetCtrl">
    <div id="calendargrid" class="grid clearfix calendargrid" style="position: relative; width: 270px;" >

        <dl class="hours">
            <div ng-repeat="hour in hours">
                <dt id="{{isSameHour(hour) ? 'currentTime': ''}}"  class="hour title">{{hour}}:00</dt>
                <dd class="hour box"></dd>
            </div>
        </dl>
        <div class="timeline" style="{{'top:'+ createTimeline() + 'px;'}}">
            <span class="icon"></span>
            <span class="line"></span>
        </div>


        <div class="animate-switch calendar view-day" ng-if="brithday.name != ''" >
            <article ng-repeat="calendar in calendars" /*ng-draggable="dragOptions"*/ class="block image label event dragable labelb5 labelc5" data-event-job="0" ng-style="calendar.style">
                <section id="{{'calendar-' + calendar.id}}" ng-click="load(calendar)" class="data">
                    <div class="grid4col">
                        <div class="column fist">
                            <h1>{{calendar.title}}</h1>
                            <h1>{{calendar.id}}</h1>
                        </div>
                        <div class="column text-left">
                            <div class="grid1col">
                                <div class="column">
                                    <span class="name">{{ calendar.description | limitTo: 20 }}{{calendar.description.length > 20 ? '...' : ''}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="column last">
                            <span ng-if="brithday.birthday_in_days > 0" class="text-success">in {{brithday.birthday_in_days}} Tagen</span>
                            <span ng-if="brithday.birthday_in_days == 0" class="text-danger">hat heute <br>Geburstag</span>
                        </div>
                    </div>
                </section>
            </article>
        </div>
    </div>
</div>



