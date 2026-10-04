<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ClubDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A letter, memo or form a club prints on its letterhead.
 *
 * @property int $id
 * @property int $club_id
 * @property int|null $created_by
 * @property string $title
 * @property CarbonImmutable $document_date
 * @property string|null $body
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Club $club
 * @property-read User|null $author
 */
#[Fillable(['title', 'document_date', 'body'])]
class ClubDocument extends Model
{
    /** @use HasFactory<ClubDocumentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_date' => 'date:Y-m-d',
        ];
    }

    /**
     * Get the club the document belongs to.
     *
     * @return BelongsTo<Club, $this>
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the user who created the document.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the file name used when the document is downloaded.
     */
    public function downloadName(): string
    {
        $slug = Str::slug($this->title) ?: 'document';

        return "{$slug}-{$this->document_date->format('Y-m-d')}.docx";
    }
}
