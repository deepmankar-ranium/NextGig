<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * One row in the append-only status history. Rows can be created, never changed or deleted
 * (they disappear only with their application).
 */
class ApplicationStatusChange extends Model
{
    protected $table = 'application_status_history';

    public const UPDATED_AT = null;

    protected $fillable = ['application_id', 'from_status', 'to_status', 'changed_by'];

    protected $casts = ['created_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Application status history is append-only.'));
        static::deleting(fn () => throw new LogicException('Application status history is append-only.'));
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
