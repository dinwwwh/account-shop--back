<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DeleteFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'model_name',
        'model_key',
        'delete_file_at',
        'deleted_file_at',
        'errors',
        'successes',
        'creator_id',
        'last_updated_editor_id',
    ];

    protected $casts = [
        'path' => 'string',
        'model_name' => 'string',
        'model_key' => 'string',
        'errors' => 'string',
        'successes' => 'array',
        'delete_file_at' => 'timestamp',
        'deleted_file_at' => 'timestamp',
        'creator_id' => 'integer',
        'last_updated_editor_id' => 'integer',
    ];

    /**
     * To set default
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Custom
        static::creating(function ($query) {
            $query->creator_id = auth()->id;
            $query->last_updated_editor_id = auth()->id;
        });

        static::updating(function ($query) {
            $query->last_updated_editor_id = auth()->id;
        });
    }

    /**
     * register a schedule for delete a file
     *
     * @param  mixed $path
     * @param  mixed $timeDelete
     * @param  mixed $model
     * @return void
     */
    public static function register($path, Carbon $timeDelete = null, $model = null)
    {
        // Initial data
        $deleteFileData = [];

        $deleteFileData['path'] = $path;
        $deleteFileData['delete_file_at'] = $timeDelete ?? Carbon::now();

        if (!is_null($model)) {
            $deleteFileData['modelName'] = get_class($model);
            $keyName = $model->getKeyName;
            $deleteFileData['modelKey'] = $model->$keyName;
        }

        return static::create($deleteFileData)->refresh();
    }
}