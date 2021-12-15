<?php

namespace RainLab\Debugbar\DataCollectors;

use Cms\Classes\ComponentBase;
use Cms\Classes\Controller;
use Cms\Classes\Layout;
use Cms\Classes\Page;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class OctoberComponentsCollector extends DataCollector implements Renderable
{
    /** @var Controller  */
    protected $controller;

    /** @var Page  */
    protected $page;

    /** @var Layout  */
    protected $layout;

    public function __construct(Controller $controller, Page $page, Layout $layout)
    {
        $this->controller = $controller;
        $this->page = $page;
        $this->layout = $layout;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        /** @var ComponentBase[]|object $components */
        $components = [];

        foreach ($this->layout->components as $alias => $componentObj) {
            $components[$alias] = $this->formatVar($this->makeComponentDetails($componentObj));
        }

        foreach ($this->page->components as $alias => $componentObj) {
            $components[$alias] = $this->formatVar($this->makeComponentDetails($componentObj));
        }

        return $components;
    }

    /**
     * makeComponentDetails builds a useful array to describe a component
     */
    protected function makeComponentDetails($componentObj): array
    {
        return [
            'class' => get_class($componentObj),
            'props' => $componentObj->getProperties()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'components';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return [
            "components" => [
                "icon" => "puzzle-piece",
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => "components",
                "default" => "{}"
            ]
        ];
    }
}
