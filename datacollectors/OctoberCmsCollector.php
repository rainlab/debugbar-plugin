<?php

namespace RainLab\Debugbar\DataCollectors;

use Cms\Classes\Controller;
use Cms\Classes\Page;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class OctoberCmsCollector extends DataCollector implements Renderable
{
    /** @var Controller  */
    protected $controller;

    /** @var string */
    protected $url;

    /** @var Page  */
    protected $page;

    public function __construct(Controller $controller, $url, Page $page)
    {
        $this->controller = $controller;
        $this->url = $url;
        $this->page = $page;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $ajaxHandler = $this->controller->getAjaxHandler();

        $result = [
            'controller' => get_class($this->controller),
            'action' => null,
            'url' => $this->url,
            'ajaxHandler' => $ajaxHandler,
            'file' => $this->page->getFileName(),
        ];

        $reflector = $this->getReflector($ajaxHandler);
        if ($reflector) {
            $filename = ltrim(str_replace(base_path(), '', $reflector->getFileName()), '/');
            $result['file'] = $filename . ':' . $reflector->getStartLine() . '-' . $reflector->getEndLine();
            $result['controller'] = $reflector->getDeclaringClass()->getName();
            $result['action'] = $reflector->getName();
        }

        foreach ($this->page->toArray() as $key => $value) {
            $result[$key] = is_scalar($value) ? $value : $this->formatVar($value);
        }

        return $result;
    }

    /**
     * @param $handler
     * @return \ReflectionMethod|null
     * @see Controller::runAjaxHandler()
     */
    protected function getReflector($handler)
    {
        if (!$handler) {
            return null;
        }

        $reflector = null;

        /*
         * Process Component handler
         */
        if (strpos($handler, '::')) {

            [$componentName, $handlerName] = explode('::', $handler);

            $componentObj = $this->controller->findComponentByName($componentName);

            if ($componentObj && method_exists($componentObj, $handlerName)) {
                $reflector = new \ReflectionMethod($componentObj, $handlerName);
            }
        }
        /*
         * Process code section handler
         */
        else {
            if (method_exists($this->page, $handler)) {
                $reflector = new \ReflectionMethod($this->page, $handler);
            }

            if (!$this->controller->getLayout()->isFallBack() && method_exists($this->controller->getLayout(), $handler)) {
                $reflector = new \ReflectionMethod($this->controller->getLayout(), $handler);
            }

            /*
             * Cycle each component to locate a usable handler
             */
            if (($componentObj = $this->controller->findComponentByHandler($handler)) !== null) {
                $reflector = new \ReflectionMethod($componentObj, $handler);
            }
        }

        return $reflector;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'cms';
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
                "map" => "cms",
                "default" => "{}"
            ]
        ];
    }
}
