<?php

namespace App\Models;

use App\Models\Concerns\ResolvesAssetUrls;
use Database\Factories\ClubFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $name
 * @property string|null $location
 * @property string|null $charter_number
 * @property string|null $logo_path
 * @property string|null $letterhead_path
 * @property-read string|null $logo_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'location', 'charter_number'])]
class Club extends Model
{
    /** @use HasFactory<ClubFactory> */
    use HasFactory, ResolvesAssetUrls;

    /**
     * Get the browser-usable URL of the club's logo.
     *
     * @return Attribute<string|null, never>
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => static::assetUrl($this->logo_path));
    }

    /**
     * Get the letters, memos and forms the club prints on its letterhead.
     *
     * @return HasMany<ClubDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ClubDocument::class);
    }

    /**
     * Determine whether the club has uploaded its own letterhead.
     */
    public function hasOwnLetterhead(): bool
    {
        return $this->letterhead_path !== null && Storage::disk('local')->exists($this->letterhead_path);
    }

    /**
     * Get the path of the .docx letterhead the club's documents are built
     * from: its own upload, or the district's shared letterhead.
     */
    public function letterheadTemplatePath(): string
    {
        return $this->hasOwnLetterhead()
            ? Storage::disk('local')->path((string) $this->letterhead_path)
            : resource_path('templates/letterhead.docx');
    }

    /**
     * Get the club's members, officers included.
     *
     * @return HasMany<Member, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    /**
     * Get the yearly dues amounts the club charges.
     *
     * @return HasMany<DuesRate, $this>
     */
    public function duesRates(): HasMany
    {
        return $this->hasMany(DuesRate::class);
    }

    /**
     * Get the club's dues amount for the given year, if one is set.
     */
    public function duesRateFor(int $year): ?string
    {
        return $this->duesRates()->where('year', $year)->value('amount');
    }
}
