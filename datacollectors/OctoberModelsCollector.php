<?php

namespace RainLab\Debugbar\DataCollectors;

use October\Rain\Database\Model;
use DebugBar\DataCollector\ObjectCountCollector;

class OctoberModelsCollector extends ObjectCountCollector
{
    public function __construct()
    {
        parent::__construct('models');

        Model::extend(function ($model) {
            $model->bindEvent('model.afterFetch', function () use ($model) {
                $this->countClass($model);
            });
        });
    }
}
