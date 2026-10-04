<?php

namespace App\Models;

use App\Models\Concerns\ResolvesAssetUrls;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory, ResolvesAssetUrls;

    public const string CACHE_KEY = 'site-settings';

    /**
     * Setting keys that hold an uploaded image path rather than text.
     *
     * @var list<string>
     */
    public const array IMAGE_KEYS = ['logo', 'hero_background'];

    /**
     * The default value of every editable site setting.
     *
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            'logo' => '/images/sed-removebg-preview.png',
            'hero_background' => '/images/majestic_philippine_eagle_pithecophaga_jefferyi_with_crown_of_feathers_powerful.png',

            'header_title' => 'The Fraternal Order of Eagles',
            'header_subtitle' => 'Philippine Eagles | YAKAP Chapter',
            'member_portal_url' => '#',

            'hero_location' => 'Sorsogon Eagles District III • Sorsogon City, Philippines',
            'hero_motto' => 'Alang-Alang sa Diyos at Sambayanang Pilipino',
            'hero_eyebrow' => 'The Fraternal Order of Eagles — Philippine Eagles',
            'hero_title' => 'YAKAP CHAPTER',
            'hero_tagline' => 'Unity, Service, and Unbroken Brotherhood',
            'hero_description' => 'Pioneering indigenous socio-civic leadership across the archipelago. We bind our strength to uplift the marginalized, defend civic honor, and preserve the legacy of true Filipino brotherhood.',
            'hero_primary_cta' => 'Explore Our Brotherhood',
            'hero_secondary_cta' => "Read Eagle's Creed",

            'pillars_eyebrow' => 'Foundation of the Order',
            'pillars_title' => 'The Four Pillars of the Philippine Eagles',
            'pillars_description' => 'As the first Philippine-born fraternal socio-civic movement, every member of YAKAP Chapter lives, leads, and serves by four sacred tenets.',

            'creed_eyebrow' => 'The Sovereign Pledge',
            'creed_title' => "The Eagle's Creed",
            'creed_description' => 'Recited at every formal assembly, regular agape, and sacred charter ceremony since our founding in 1979.',
            'creed_text' => 'I am an Eagle, the Philippine Eagle. I fly high above the petty jealousies and animosities of mortal men. I serve my God, my country, and my fellowmen with honor, loyalty, and true fraternal love.',
            'creed_source' => 'TFOE-PE Fundamental Charter',
            'creed_code' => 'Honorary Code of 1979',

            'outreach_eyebrow' => 'Action in the Field',
            'outreach_title' => 'YAKAP Socio-Civic Missions',
            'outreach_description' => 'Real impact measured in lives transformed, barangays empowered, and civic responsibility fulfilled.',
            'outreach_badge' => 'Year-to-Date 2024 Civic Summary',

            'fund_eyebrow' => 'Audit & Accountability',
            'fund_title' => 'Annual Civic Fund Allocation',
            'fund_description' => 'All member dues, alumni endowments, and fraternal benefit proceeds go directly toward certified philanthropic initiatives.',

            'membership_eyebrow' => 'Fraternal Aspirants',
            'membership_title' => 'How to Soar With the Eagles',
            'membership_description' => 'Membership in the Philippine Eagles is a sacred lifetime honor granted only to men and women of proven civic integrity, goodwill, and dedication to public service.',

            'secretariat_eyebrow' => 'Fraternal Secretariat',
            'secretariat_title' => 'Seeking Affiliation with YAKAP Chapter?',
            'secretariat_description' => 'Connect with our Chapter Secretariat for upcoming Assembly dates, sponsor introductions, or transfer credentials from sister chapters.',
            'secretariat_cta' => 'Contact Secretariat',
            'charter_guidelines_label' => 'Charter Guidelines',
            'charter_guidelines_url' => '#',

            'footer_name' => 'TFOE-PE YAKAP Chapter',
            'footer_motto' => 'Alang-alang sa Diyos at Bayan',
            'footer_description' => 'Service Through Strong Brotherhood. Committed to philanthropic service, civic stewardship, and lasting fraternal solidarity across the Philippines.',
            'footer_charter' => 'Charter No. YKP-042 | Regional Assembly VII',
            'contact_address' => 'YAKAP Chapter Fraternal Hall',
            'contact_email' => 'secretariat@yakapeagles.ph',
            'contact_phone' => '+63 (02) 8920-EAGLE',
            'footer_accreditation' => 'Accredited Civic Order',
            'footer_copyright' => 'The Fraternal Order of Eagles - Philippine Eagles (TFOE-PE) YAKAP Chapter. All Rights Reserved.',
            'footer_latin_motto' => 'Pro Deo et Patria',
        ];
    }

    /**
     * Get every site setting, falling back to defaults for unsaved keys.
     *
     * @return array<string, string|null>
     */
    public static function values(): array
    {
        /** @var array<string, string|null> $stored */
        $stored = Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => static::query()->pluck('value', 'key')->all(),
        );

        return array_intersect_key([...static::defaults(), ...$stored], static::defaults());
    }

    /**
     * Get every site setting with image keys resolved to public URLs.
     *
     * @return array<string, string|null>
     */
    public static function publicValues(): array
    {
        $values = static::values();

        foreach (self::IMAGE_KEYS as $imageKey) {
            $values[$imageKey] = static::assetUrl($values[$imageKey]);
        }

        return $values;
    }

    /**
     * Persist the given settings and clear the settings cache.
     *
     * @param  array<string, string|null>  $values
     */
    public static function store(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
