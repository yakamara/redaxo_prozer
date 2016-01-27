<div class="page-header">
	<div class="pull-right" ng-dropdown-multiselect="" options="allWidgets" search-filter="customFilter" selected-model="dashboard.widgets" events="selectEvents" extra-settings="selectSettings" translation-texts="selectTranslation"></div>
	<h1 class="hl1">Dashboard <a title="Dashboard Settings" ng-click="gridSetting()"><i ng-style="gridsterOptions.draggable.enabled == true ? {color: '#06a9d6'} : {color: '#4d595f'}" style="top:2px;" class="glyphicon glyphicon-cog"></i></a></h1>
</div>
<div gridster="gridsterOptions">
	<ul>
		<li gridster-item="wigetsMap" ng-repeat="widget in widgets">
			<div class="box" ng-controller="CustomWidgetCtrl">
				<div class="box-header">
					<h3>{{widget.name}}</h3>
					<div class="box-header-btns pull-right">
						<a ng-if="widget.settingView.controller" title="settings" ng-click="openSettings(widget)"><i class="glyphicon glyphicon-cog"></i></a>
						<a title="Remove widget" ng-click="remove(widget)"><i class="glyphicon glyphicon-trash"></i></a>
					</div>
				</div>
				<div class="box-content">
					<div ng-include="widget.view.url"></div>
				</div>
			</div>
		</li>
	</ul>
</div>