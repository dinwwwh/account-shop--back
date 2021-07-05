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

    /**
     * The attributes & relationships that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'audits', #Contain history changes of this model
    ];

    /**
     * Modify before store data changes in audit
     * Should add attributes in $hidden property above
     *
     * @var array
     * */
    protected $attributeModifiers = [];

    protected $fillable = [
        'path',
        'model_name',
        'model_key',
        'delete_file_at',
        'deleted_file_at',
        'errors',
        'successes',
        'creator_id',
        'latest_updater_id',
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
        'latest_updater_id' => 'integer',
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
            $query->creator_id = optional(auth()->user())->id;
            $query->latest_updater_id = optional(auth()->user())->id;
        });

        static::updating(function ($query) {
            $query->latest_updater_id = optional(auth()->user())->id;
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
