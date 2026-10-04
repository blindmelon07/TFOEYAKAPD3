<?php

namespace Database\Seeders;

use App\Models\ChapterStat;
use App\Models\FundAllocation;
use App\Models\MembershipStep;
use App\Models\Mission;
use App\Models\Pillar;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class LandingPageSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the landing page with the chapter's original content.
     */
    public function run(): void
    {
        Setting::store(Setting::defaults());

        $this->seedList(ChapterStat::class, [
            ['value' => '500+', 'label' => 'Kuya & Ate Members'],
            ['value' => '50+', 'label' => 'Community Outreach Missions'],
            ['value' => '100%', 'label' => 'Civic & Humanitarian Service'],
            ['value' => 'YKP-042', 'label' => 'Officially Chartered Chapter'],
        ]);

        $this->seedList(Pillar::class, [
            [
                'icon' => 'diversity_3',
                'tag' => 'Pillar I • Kapatiran',
                'title' => 'Brotherhood',
                'description' => 'Unconditional camaraderie, mutual respect, and lifetime fraternal fidelity. We stand shoulder-to-shoulder through all trials of nationhood.',
            ],
            [
                'icon' => 'volunteer_activism',
                'tag' => 'Pillar II • Serbisyo',
                'title' => 'Service to Humanity',
                'description' => 'The living essence of “YAKAP”—to embrace, shelter, and empower the underserved with genuine philanthropic devotion.',
            ],
            [
                'icon' => 'flag',
                'tag' => 'Pillar III • Nasyonalismo',
                'title' => 'Patriotism',
                'description' => 'Deep, unyielding allegiance to the Republic of the Philippines, promoting indigenous culture, national peace, and sovereignty.',
            ],
            [
                'icon' => 'balance',
                'tag' => 'Pillar IV • Dangal',
                'title' => 'Integrity & Honor',
                'description' => "Upholding upright conduct, ethical governance in private and public affairs, and the sacred sanctity of one's sworn fraternal pledge.",
            ],
        ]);

        $this->seedList(Mission::class, [
            [
                'category' => 'Health & Wellness',
                'program' => 'Project Yakap Kalinga',
                'title' => 'Barangay Medical & Dental Mission',
                'description' => 'Free clinical consultations, tooth extraction, optical checks, and maintenance medicines for 1,200 indigent families.',
                'metric_label' => 'Beneficiaries',
                'metric_value' => '1,240 Citizens',
                'image_path' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAUiObp7e-YPBLyqw6-YA-fwl_hzHR1nwVlCtG2WpjoViHQBng8YEcJCjiMsqq9Vvgf3X-0x6wnQj0VqOfcX59kfgLr6veMgWgMytrA9ET_twUoEo4k3tcb9qVJ5QOAuTThmyYcvUmQZphEg9axfQ5mEOqFo05eorJbvJtVb5B6-R0lqAG2rn_QNj3NbIm8LFcG8yZ7EnIxHxy1bhhO4ViAnWrLsumNa_0Q8oS6TXWBvFr4XxqjwCAP',
                'image_alt' => 'Volunteer doctors and nurses in Eagles vests conducting a community medical mission',
            ],
            [
                'category' => 'Education',
                'program' => 'Eagle Wings Grant',
                'title' => 'Youth Scholarship Endowment',
                'description' => 'Full semester tuition, stipends, and school supplies provided to promising students from rural public high schools.',
                'metric_label' => 'Active Scholars',
                'metric_value' => '85 Students',
                'image_path' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDL9_4yKdwOiqqLVRtAChny8Qqzo5VVEX8qY07aaEN8b9gyMKtUxIdrBmB1EM8Jt3Ef4o7H0lJH9P895YPoYr9BwzR2fdaVFppEKrfhWLwV_tMfVHN1pxIxmqKieQjgHrIy1JDn_FQebIfC8NoztzNiwk01TjXBQzANevBJ1WY9Ht3kPiUC6fZ9lFn2ywzkpCeGL6m4H0_EC-pgESbD6IfqNxLTZr8ATmShDFE0oiMU6vrvRv3Wvzqs',
                'image_alt' => 'Young scholars receiving educational grant certificates from Eagles members',
            ],
            [
                'category' => 'Rapid Response',
                'program' => 'Alay Agila Calamity Unit',
                'title' => 'Typhoon & Flood Relief Dispatch',
                'description' => 'Immediate deployment of clean water filtration, dry rations, and hygiene sets within 36 hours of natural emergencies.',
                'metric_label' => 'Emergency Aid Packs',
                'metric_value' => '3,500 Families',
                'image_path' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuB7ry4R6GcDxOpDSXHaApKX5L2UbzRdSVx3TiKCnEwTTPIeIpDBcWGDV-oACzAATegrWw-l8-0ogi0A70X85JwZAPK-xOAQ5Nk_wbfflp0R8AdFLub2hqP_wSJSD7mEgWi-W5Rg_17JMVsp7ZsmXPWXLtDlON6wMswjG8EokcYC9pgK_D3goOEAbHmygku5o991ZCiXoVQXFoy8TYg3PbXAUUtZGUggHq9TO8gM1EMdOxs_Dl70aq-b',
                'image_alt' => 'Relief convoy trucks arriving in a storm-affected coastal town',
            ],
            [
                'category' => 'Ecology',
                'program' => 'Bantay Kalikasan',
                'title' => 'Philippine Eagle Habitat Care',
                'description' => "Reforestation of Sierra Madre corridors and educational school caravans advocating for the national bird's survival.",
                'metric_label' => 'Trees Planted',
                'metric_value' => '12,000 Saplings',
                'image_path' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBApiZrV-symWoxPAYusyFPqidhZUoz7HAIsq9xJ7ozEe3uhjFhmlQOY5YKNMB4lB0YkCw3cqSS3A3MPG4lxNBjT6gJqGQxUwMFkmTyrtNIAeyTavdDianDr7lfyL6d0w97r454KDsqFXyRIDTeZeM1vGsghrAMl2zfWmtK5JvSec0us1hTt94vUi3Sk6tg3r55bPZY8qQqjxoS-PZCeNvwcbRIAdqKB18ItVrmAv7J7tC87-aymnOP',
                'image_alt' => 'Volunteers planting endemic hardwood saplings in a rainforest',
            ],
        ]);

        $this->seedList(FundAllocation::class, [
            ['label' => 'Health', 'percentage' => 45, 'color' => 'primary'],
            ['label' => 'Relief', 'percentage' => 25, 'color' => 'primary-container'],
            ['label' => 'Grants', 'percentage' => 20, 'color' => 'secondary'],
            ['label' => 'Nature', 'percentage' => 10, 'color' => 'secondary-container'],
        ]);

        $this->seedList(MembershipStep::class, [
            [
                'icon' => 'group_add',
                'title' => 'Fraternal Sponsorship',
                'description' => 'Every applicant must be formally sponsored by an active Kuya or Ate in good standing within the YAKAP Chapter or the National Assembly.',
                'requirement' => '1 Primary & 1 Co-Sponsor',
            ],
            [
                'icon' => 'fact_check',
                'title' => 'Review & Orientation',
                'description' => 'Thorough background review by the Committee on Membership, followed by attendance in the official Eagle Aspirant Pre-Induction Seminar (PIS).',
                'requirement' => 'Moral & Civic Clearance',
            ],
            [
                'icon' => 'workspace_premium',
                'title' => 'Rite of Passage & Oath',
                'description' => "Fulfillment of fraternal initiation rites and solemn swearing of the Eagle's Oath before the National Assembly and Chapter Council.",
                'requirement' => 'Lifetime Commitment',
            ],
        ]);
    }

    /**
     * Replace a model's records with the given rows, preserving their order.
     *
     * @param  class-string<Model>  $model
     * @param  list<array<string, mixed>>  $rows
     */
    private function seedList(string $model, array $rows): void
    {
        $model::query()->delete();

        foreach ($rows as $index => $row) {
            $model::query()->create([...$row, 'sort_order' => ($index + 1) * 10]);
        }
    }
}
