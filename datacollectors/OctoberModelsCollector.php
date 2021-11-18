<?php

namespace RainLab\Debugbar\DataCollectors;

use October\Rain\Database\Model;
use Illuminate\Contracts\Events\Dispatcher;
use Barryvdh\Debugbar\DataCollector\ModelsCollector;

class OctoberModelsCollector extends ModelsCollector
{
    public $models = [];
    public $count = 0;

    /**
     * @param Dispatcher $events
     */
    public function __construct(Dispatcher $events)
    {
        Model::extend(function ($model) {
            $model->bindEvent('model.afterFetch', function () use ($model) {
                $class = get_class($model);
                $this->models[$class] = ($this->models[$class] ?? 0) + 1;
                $this->count++;
            });
        });
    }
}
