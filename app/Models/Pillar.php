<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Database\Factories\PillarFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $icon
 * @property string $tag
 * @property string $title
 * @property string $description
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['icon', 'tag', 'title', 'description', 'sort_order'])]
class Pillar extends Model
{
    /** @use HasFactory<PillarFactory> */
    use HasFactory, HasSortOrder;
}
