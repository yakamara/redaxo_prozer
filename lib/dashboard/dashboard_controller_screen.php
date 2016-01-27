<?php


use FragSeb\Dashboard\Adapter\DashboardModelAdapter;
use FragSeb\Dashboard\DashboardFactory;
use FragSeb\Dashboard\DashboardMapper;

class pz_dashboard_controller_screen extends pz_dashboard_controller
{
    public $name = 'dashboard';

    public $function = '';

    public $functions = ['dashboard', 'widgetsSettings', 'widgetData'];

    public $function_default = 'dashboard';

    public $navigation = [];

    public function controller($function)
    {
        $this->function = $function;

        if (!in_array($function, $this->functions)) {
            $this->function = $this->function_default;
        }

        $p = [];
        $p['mediaview'] = 'screen';
        $p['controll'] = 'dashboard';
        $p['function'] = $this->function;
        $p['view'] = rex_request('view', 'string', null);

        switch ($this->function) {
            case 'dashboard' :

                return $this->dashboardAction($p);
            case 'widgetsSettings' :

                return $this->widgetsSettingsAction($p);
            case 'widgetData' :

                return $this->widgetDataAction($p);
            default:

                return $this->dashboardAction($p);

        }
    }

    private function dashboardAction($p)
    {
        if($p['view']){
            return $this->widgetView($p['view']); //file_get_contents(__DIR__ . '/../../assets/dashboard/dashboard/'.$p['view']);
        }

        return $this->response($p, '');
    }



    private function widgetsSettingsAction($p)
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);


        if (isset($request)) {
            $model = new DashboardModelAdapter();
            $data = json_decode($postdata);
            $model->setData(json_encode($data->query))->update();
        }

        $factory = new DashboardFactory();
        $dashboard = $factory->create(new DashboardSettings(), new DashboardModelAdapter());

        return DashboardMapper::toJson($dashboard); //$postdata;
    }

    private function widgetDataAction($p)
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata, false);

        $class = $request->query->id. 'Adapter';

        if(class_exists($class)) {
            $adapter = new $class;

            $requestData = (array) $request->query->settings;
            $data = $adapter->get($requestData);
        }

        return json_encode($data);
    }
}
