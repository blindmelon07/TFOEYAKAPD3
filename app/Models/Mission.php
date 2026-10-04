<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\ResolvesAssetUrls;
use Database\Factories\MissionFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $category
 * @property string $program
 * @property string $title
 * @property string $description
 * @property string $metric_label
 * @property string $metric_value
 * @property string|null $image_path
 * @property string|null $image_alt
 * @property int $sort_order
 * @property-read string|null $image_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['category', 'program', 'title', 'description', 'metric_label', 'metric_value', 'image_path', 'image_alt', 'sort_order'])]
#[Appends(['image_url'])]
class Mission extends Model
{
    /** @use HasFactory<MissionFactory> */
    use HasFactory, HasSortOrder, ResolvesAssetUrls;

    /**
     * Get the browser-usable URL of the mission photo.
     *
     * @return Attribute<string|null, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => static::assetUrl($this->image_path));
    }
}
