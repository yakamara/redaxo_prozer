<div>
    <div class="modal-header">
        <button type="button" class="close" ng-click="dismiss()" aria-hidden="true">&times;</button>
        <h2>{{brithday.firstname}} {{brithday.name}}</h2>
    </div>
    <div class="modal-body">
        <div class="grid1col">
            <div class="column">
                <ul>
                    <li ng-repeat="field in brithday.fields | orderBy:'field.type'">
                        <a href="/screen/emails/create/?to={{field.vars.value}}">{{field.vars.value}}</a> <span class="info">{{field.vars.label}}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="modal-footer">
    </div>
</div>
