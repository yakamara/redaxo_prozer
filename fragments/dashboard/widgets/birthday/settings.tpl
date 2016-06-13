<div>
    <form name="_form" class="form-horizontal" ng-submit="submit(_form)">
        <div class="modal-header">
            <button type="button" class="close" ng-click="dismiss()" aria-hidden="true">&times;</button>
            <h3>{{widget.name}} Settings</h3>
        </div>

        <div class="modal-body">
            <div class="form-group" ng-class="{error: _form.name.$error && _form.submitted}">
                <label class="control-label col-lg-3 col-md-3">Anzahl der Tage</label>
                <div class="col-lg-9 col-md-9">
                    <input type="number" min="1" max="365" name="form.settings.frame" type="text" ng-model="form.settings.frame" class="text" required/>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" ng-click="dismiss()" class="bt1" tabindex="-1"><!--i class="glyphicon glyphicon-remove"></i -->Cancel</button>
            <button type="submit" class="bt1"><!-- i class="glyphicon glyphicon-ok"></i -->Save</button>
        </div>
    </form>
</div>
