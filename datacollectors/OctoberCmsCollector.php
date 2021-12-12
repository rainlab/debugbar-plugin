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
    /** @var array  */
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
        $result = [
            'controller' => get_class($this->controller),
            'url' => $this->url,
        ];

        foreach ($this->page->toArray() as $key => $value) {
            $result[$key] = is_scalar($value) ? $value : $this->formatVar($value);
        }

        return $result;
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
            "cms" => [
                "icon" => "share",
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => "cms",
                "default" => "{}"
            ]
        ];
    }
}
