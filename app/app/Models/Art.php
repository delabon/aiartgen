<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ArtFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Art extends Model
{
    /** @use HasFactory<ArtFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return BelongsTo<User, Art>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
