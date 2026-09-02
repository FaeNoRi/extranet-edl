<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PasswordResetToken extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'token', 'expiration', 'used'];

    protected function casts(): array
    {
        return [
            'expiration' => 'datetime',
            'used' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Émet un nouveau jeton pour l'utilisateur et invalide les précédents.
     */
    public static function issueFor(User $user, int $ttlHours = 48): self
    {
        static::where('user_id', $user->id)->whereNull('used')->delete();

        return static::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'expiration' => Carbon::now()->addHours($ttlHours),
        ]);
    }

    public function isValid(): bool
    {
        return ! $this->used && $this->expiration->isFuture();
    }

    public function markUsed(): void
    {
        $this->forceFill(['used' => true])->save();
    }
}
