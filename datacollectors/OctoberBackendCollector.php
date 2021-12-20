<?php

namespace RainLab\Debugbar\DataCollectors;

use Backend\Classes\Controller;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class OctoberBackendCollector extends DataCollector implements Renderable
{
    /** @var Controller  */
    protected $controller;

    /** @var string */
    protected $action;

    /** @var array */
    protected $params;

    public function __construct(Controller $controller, $action, array $params = [])
    {
        $this->controller = $controller;
        $this->action = $action;
        $this->params = $params;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $ajaxHandler = $this->controller->getAjaxHandler();

        $result = [
            'controller' => get_class($this->controller),
            'action' => $this->action,
            'params' => $this->params,
            'ajaxHandler' => $ajaxHandler,
        ];

        $reflector = $this->getReflector($ajaxHandler);
        if ($reflector instanceof \ReflectionMethod) {
            $result['controller'] = $reflector->getDeclaringClass()->getName();
            $result['action'] = $reflector->getName();
        } else {
            $result['controller'] = $reflector->getName();
        }

        $filename = ltrim(str_replace(base_path(), '', $reflector->getFileName()), '/');
        $result['file'] = $filename . ':' . $reflector->getStartLine() . '-' . $reflector->getEndLine();


        return $result;
    }

    /**
     * @param $handler
     * @return \ReflectionClass|\ReflectionMethod
     * @see Controller::runAjaxHandler()
     */
    protected function getReflector($handler)
    {
        $reflector = new \ReflectionClass($this->controller);

        if ($handler) {
            /*
             * Process Widget handler
             */
            if (strpos($handler, '::')) {
                [$widgetName, $handlerName] = explode('::', $handler);

                if (isset($this->controller->widget->{$widgetName}) && ($widget = $this->controller->widget->{$widgetName}) && method_exists($widget, $handlerName)) {
                    $reflector = new \ReflectionMethod($widget, $handlerName);
                }
            }
            else {
                /*
                 * Process page specific handler (index_onSomething)
                 */
                $pageHandler = $this->action . '_' . $handler;

                if (method_exists($this->controller, $pageHandler)) {
                    $reflector = new \ReflectionMethod($this->controller, $pageHandler);
                }

                /*
                 * Process page global handler (onSomething)
                 */
                if (method_exists($this->controller, $handler)) {
                    $reflector = new \ReflectionMethod($this->controller, $handler);
                }

                foreach ((array) $this->controller->widget as $widget) {
                    if (method_exists($widget, $handler)) {
                        $reflector = new \ReflectionMethod($widget, $handler);
                    }
                }
            }
        }

        return $reflector;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'backend';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return [
            "route" => [
                "icon" => "share",
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => "backend",
                "default" => "{}"
            ]
        ];
    }
}
