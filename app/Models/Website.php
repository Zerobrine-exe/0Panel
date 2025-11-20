<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Website extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'domain',
        'root_path',
        'php_version',
        'ssl_enabled',
        'ssl_status',
        'nginx_config_path',
        'enabled',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Website $model) {
            if (! $model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

