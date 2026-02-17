<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'endpoint_id',
        'uuid',
        'payload',
        'headers',
        'status',
        'attempts',
        'last_error',
        'relay_results',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'relay_results' => 'array',
    ];

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'endpoint_id');
    }
}
