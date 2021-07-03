<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

class DeleteFile extends Model implements Auditable
{
    use HasFactory,
        \OwenIt\Auditing\Auditable;

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
        'delete_file_at' => 'datetime',
        'deleted_file_at' => 'datetime',
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
            $query->last_updated_editor_id = auth()->user()->id;
        });

        static::updating(function ($query) {
            $query->last_updated_editor_id = auth()->user()->id;
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
        $deleteFileData['delete_file_at'] = $timeDelete ?? Carbon::now()->add(1, 'day');

        if (!is_null($model)) {
            $deleteFileData['modelName'] = get_class($model);
            $keyName = $model->getKeyName;
            $deleteFileData['modelKey'] = $model->$keyName;
        }

        return static::create($deleteFileData)->refresh();
    }
}
