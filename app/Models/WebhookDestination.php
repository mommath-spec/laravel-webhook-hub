<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDestination extends Model
{
    use HasFactory;

    protected $fillable = [
        'endpoint_id',
        'url',
        'secret',
        'enabled',
        'last_status',
        'last_response_code',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'endpoint_id');
    }
}
