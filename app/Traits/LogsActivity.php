<?php

namespace App\Traits;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            Activity::log(
                'Создан ' . class_basename($model),
                $model->getActivityDescription(),
                strtolower(class_basename($model)) . '_created',
                auth()->user(),
                $model
            );
        });

        static::updated(function (Model $model) {
            Activity::log(
                'Обновлен ' . class_basename($model),
                $model->getActivityDescription(),
                strtolower(class_basename($model)) . '_updated',
                auth()->user(),
                $model
            );
        });

        static::deleted(function (Model $model) {
            Activity::log(
                'Удален ' . class_basename($model),
                $model->getActivityDescription(),
                strtolower(class_basename($model)) . '_deleted',
                auth()->user(),
                $model
            );
        });
    }

    protected function getActivityDescription(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
} 