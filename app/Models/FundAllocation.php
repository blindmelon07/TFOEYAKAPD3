<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Database\Factories\FundAllocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $label
 * @property int $percentage
 * @property string $color
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['label', 'percentage', 'color', 'sort_order'])]
class FundAllocation extends Model
{
    /** @use HasFactory<FundAllocationFactory> */
    use HasFactory, HasSortOrder;

    /**
     * The palette swatches an allocation segment may use.
     *
     * @var list<string>
     */
    public const array COLORS = ['primary', 'primary-container', 'secondary', 'secondary-container'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'percentage' => 'integer',
        ];
    }
}
