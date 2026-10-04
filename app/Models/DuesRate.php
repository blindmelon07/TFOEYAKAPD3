<?php

namespace App\Models;

use Database\Factories\DuesRateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The amount a club charges each member in dues for one year.
 *
 * @property int $id
 * @property int $club_id
 * @property int $year
 * @property string $amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Club $club
 */
#[Fillable(['year', 'amount'])]
class DuesRate extends Model
{
    /** @use HasFactory<DuesRateFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the club the rate belongs to.
     *
     * @return BelongsTo<Club, $this>
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
