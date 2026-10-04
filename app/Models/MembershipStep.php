<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Database\Factories\MembershipStepFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $icon
 * @property string $title
 * @property string $description
 * @property string $requirement
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['icon', 'title', 'description', 'requirement', 'sort_order'])]
class MembershipStep extends Model
{
    /** @use HasFactory<MembershipStepFactory> */
    use HasFactory, HasSortOrder;
}
