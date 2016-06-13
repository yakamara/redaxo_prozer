<div class="" ng-controller="BirthdayWidgetCtrl">
    <ul  class="animate-switch-container" >
        <li class="animate-switch" ng-if="brithday.name != ''" ng-repeat="brithday in widget.data.birthday | orderBy:'birthday_in_days'"">
            <article class="block image label" ng-click="contractView(brithday)">
                <section class="data">
                    <div class="grid4col">
                        <div class="column fist">
                            <figure>
                                <a href="javascript:void(0)" onclick="pz_loadPage('address_form','/screen/addresses/all/?address_id=3&amp;mode=edit_address')">
                                    <div class="">
                                        <img ng-if="brithday.photo ==''" src="/assets/addons/prozer/css/user.png" width="40" height="40">
                                        <img ng-if="brithday.photo !=''" data-ng-src="{{brithday.photo}}">
                                    </div>
                                </a>
                            </figure>
                        </div>
                        <div class="column text-left">
                            <div class="grid1col">
                                <div class="column">
                                    <span class="name">{{brithday.firstname}} {{brithday.name}}</span>
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
        </li>
        <li class="text-center text-warning animate-switch"  ng-if="widget.data == ''">
            Heute hat niemand Geburtstag!
        </li>
    </ul>
    <div id="addresses_list" class="design2col"><div>
</div>
