<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Document extends Model
{
    protected $fillable = [ 
        'uuid', 'user_id', 'file_path', 'document_hash', 'filename', 'type', 'size', 
        'signature', 'signed_at', 'public_key_id',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($document) {
            if (!$document->uuid) {
                $document->uuid = Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function watermark()
    {
        return $this->hasOne(Watermark::class);
    }

    public function getPayloadForWatermark(): array
    {
        return [
            'signature'     => $this->signature,
            'timestamp'     => $this->signed_at->toIso8601String(),
            'publicKeyId'   => $this->public_key_id,
            'metadata_hash' => $this->hash,
        ];
    }
}
