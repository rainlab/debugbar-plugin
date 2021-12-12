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
    /** @var array  */
    protected $params;

    public function __construct(Controller $controller, $action, array $params)
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
        $result = [
            'controller' => get_class($this->controller),
            'action' => $this->action,
            'params' => $this->params,
        ];

        if (class_exists(get_class($this->controller)) && method_exists($this->controller, $this->action)) {
            $reflector = new \ReflectionMethod($this->controller, $this->action);

            $filename = ltrim(str_replace(base_path(), '', $reflector->getFileName()), '/');
            $result['file'] = $filename . ':' . $reflector->getStartLine() . '-' . $reflector->getEndLine();
        }

        return $result;
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
            "backend" => [
                "icon" => "share",
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => "backend",
                "default" => "{}"
            ]
        ];
    }
}
