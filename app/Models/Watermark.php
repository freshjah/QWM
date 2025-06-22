<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Watermark extends Model
{
    protected $fillable = [
        'document_id', 'entropy_seed', 'timestamp', 'signature', 'signature_algorithm', 'revoked', 'key_id'
    ];

    // Accessor
    public function getEntropySeedAttribute()
    {
        return Crypt::decryptString($this->attributes['entropy_seed_encrypted']);
    }

    // Mutator
    public function setEntropySeedAttribute($value)
    {
        $this->attributes['entropy_seed_encrypted'] = Crypt::encryptString($value);
    }

        public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function isValid(): bool
    {
        return !$this->revoked;
    }

    public function markAsRevoked(): void
    {
        $this->revoked = true;
        $this->save();
    }
}
