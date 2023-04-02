<?php

namespace App\Traits;

trait CreatedUpdatedBy
{
    public static function bootCreatedUpdatedBy()
    {
        // updating created_by and updated_by when model is created
        static::creating(function ($model) {
            if (!$model->isDirty('created_by')) {
                if(auth()->check()){
                    $model->created_by = auth()->user()->id;
                }
            }
            if (!$model->isDirty('updated_by')) {
                if(auth()->check()){
                    $model->updated_by = auth()->user()->id;
                }
            }
        });

        // updating updated_by when model is updated
        static::updating(function ($model) {
            if (!$model->isDirty('updated_by')) {
                if (auth()->check()) {
                    $model->updated_by = auth()->user()->id;
                }
            }
        });
    }


}
