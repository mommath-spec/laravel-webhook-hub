<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'type',
        'secret',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function calls(): HasMany
    {
        return $this->hasMany(WebhookCall::class, 'endpoint_id');
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(WebhookDestination::class, 'endpoint_id');
    }
}
