<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Database\Factories\ChapterStatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $value
 * @property string $label
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['value', 'label', 'sort_order'])]
class ChapterStat extends Model
{
    /** @use HasFactory<ChapterStatFactory> */
    use HasFactory, HasSortOrder;
}
