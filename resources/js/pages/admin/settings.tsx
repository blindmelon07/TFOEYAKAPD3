import { Form } from '@inertiajs/react';
import {
    ImageField,
    SubmitButton,
    TextAreaField,
    TextField,
} from '@/components/admin/form-fields';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { update } from '@/routes/admin/settings';
import type { SiteSettings } from '@/types';

type SettingField = {
    key: string;
    label: string;
    type?: 'text' | 'textarea' | 'image' | 'email';
    help?: string;
    wide?: boolean;
};

type SettingGroup = {
    title: string;
    icon: string;
    description: string;
    fields: SettingField[];
};

const eaglesHint =
    'The words "Eagle", "Eagles" and "Eagle\'s" are shown in the gold script font automatically.';

const settingGroups: SettingGroup[] = [
    {
        title: 'Branding & Header',
        icon: 'badge',
        description: 'The logo and the name shown in the top navigation bar.',
        fields: [
            {
                key: 'logo',
                label: 'Chapter logo / seal',
                type: 'image',
                help: 'Used in the header and as the large crest in the hero. A transparent PNG works best.',
                wide: true,
            },
            { key: 'header_title', label: 'Header title', help: eaglesHint },
            { key: 'header_subtitle', label: 'Header subtitle' },
            {
                key: 'member_portal_url',
                label: 'Member Portal link',
                help: 'Full URL (https://…) or # to disable.',
            },
        ],
    },
    {
        title: 'Hero',
        icon: 'web_asset',
        description: 'The large navy banner at the top of the page.',
        fields: [
            {
                key: 'hero_background',
                label: 'Background photo',
                type: 'image',
                help: 'Shown on the right side of the hero and in the strip above the membership section.',
                wide: true,
            },
            { key: 'hero_location', label: 'Location line' },
            { key: 'hero_motto', label: 'Motto pill' },
            {
                key: 'hero_eyebrow',
                label: 'Small heading above the title',
                help: eaglesHint,
                wide: true,
            },
            { key: 'hero_title', label: 'Main title' },
            { key: 'hero_tagline', label: 'Tagline' },
            {
                key: 'hero_description',
                label: 'Introduction',
                type: 'textarea',
                wide: true,
            },
            { key: 'hero_primary_cta', label: 'Gold button label' },
            { key: 'hero_secondary_cta', label: 'Navy button label' },
        ],
    },
    {
        title: 'Four Pillars Section',
        icon: 'account_balance',
        description:
            'The heading above the pillar cards. Edit the cards themselves under “Four Pillars”.',
        fields: [
            { key: 'pillars_eyebrow', label: 'Small heading' },
            { key: 'pillars_title', label: 'Title', help: eaglesHint },
            {
                key: 'pillars_description',
                label: 'Description',
                type: 'textarea',
                wide: true,
            },
        ],
    },
    {
        title: "Eagle's Creed",
        icon: 'menu_book',
        description: 'The navy creed box below the pillars.',
        fields: [
            { key: 'creed_eyebrow', label: 'Small heading' },
            { key: 'creed_title', label: 'Title', help: eaglesHint },
            {
                key: 'creed_description',
                label: 'Description',
                type: 'textarea',
                wide: true,
            },
            {
                key: 'creed_text',
                label: 'Creed text',
                type: 'textarea',
                help: 'Quotation marks are added automatically.',
                wide: true,
            },
            { key: 'creed_source', label: 'Source (left caption)' },
            { key: 'creed_code', label: 'Code (right caption)' },
        ],
    },
    {
        title: 'Missions & Fund Allocation',
        icon: 'volunteer_activism',
        description:
            'Headings for the missions grid and the fund allocation bar.',
        fields: [
            { key: 'outreach_eyebrow', label: 'Missions small heading' },
            { key: 'outreach_title', label: 'Missions title' },
            {
                key: 'outreach_description',
                label: 'Missions description',
                type: 'textarea',
                wide: true,
            },
            {
                key: 'outreach_badge',
                label: 'Summary badge',
                help: 'e.g. "Year-to-Date 2024 Civic Summary".',
            },
            { key: 'fund_eyebrow', label: 'Fund small heading' },
            { key: 'fund_title', label: 'Fund title' },
            {
                key: 'fund_description',
                label: 'Fund description',
                type: 'textarea',
                wide: true,
            },
        ],
    },
    {
        title: 'Membership & Secretariat',
        icon: 'group_add',
        description:
            'The membership heading and the secretariat call-to-action box.',
        fields: [
            { key: 'membership_eyebrow', label: 'Membership small heading' },
            {
                key: 'membership_title',
                label: 'Membership title',
                help: eaglesHint,
            },
            {
                key: 'membership_description',
                label: 'Membership description',
                type: 'textarea',
                wide: true,
            },
            { key: 'secretariat_eyebrow', label: 'Secretariat small heading' },
            { key: 'secretariat_title', label: 'Secretariat title' },
            {
                key: 'secretariat_description',
                label: 'Secretariat description',
                type: 'textarea',
                wide: true,
            },
            {
                key: 'secretariat_cta',
                label: 'Contact button label',
                help: 'Opens an email to the contact email below.',
            },
            {
                key: 'charter_guidelines_label',
                label: 'Guidelines button label',
            },
            {
                key: 'charter_guidelines_url',
                label: 'Guidelines link',
                help: 'Full URL to the document, or # to disable.',
                wide: true,
            },
        ],
    },
    {
        title: 'Footer & Contact',
        icon: 'contact_mail',
        description:
            'Contact details and the footer at the bottom of every page.',
        fields: [
            { key: 'footer_name', label: 'Footer name' },
            { key: 'footer_motto', label: 'Footer motto' },
            {
                key: 'footer_description',
                label: 'Footer description',
                type: 'textarea',
                wide: true,
            },
            { key: 'footer_charter', label: 'Charter line' },
            { key: 'footer_accreditation', label: 'Accreditation badge' },
            { key: 'contact_address', label: 'Address' },
            { key: 'contact_email', label: 'Email', type: 'email' },
            { key: 'contact_phone', label: 'Phone' },
            { key: 'footer_latin_motto', label: 'Latin motto' },
            {
                key: 'footer_copyright',
                label: 'Copyright text',
                help: '"© <current year>" is added in front automatically.',
                wide: true,
            },
        ],
    },
];

export default function Settings({
    settings,
    imageUrls,
}: {
    settings: SiteSettings;
    imageUrls: Record<string, string | null>;
}) {
    return (
        <AdminLayout
            title="Site Settings"
            description="Every heading, paragraph and image on the landing page that isn't part of a list."
        >
            <Form
                {...update.form()}
                resetOnSuccess={['logo', 'hero_background']}
                options={{ preserveScroll: true }}
                className="flex flex-col gap-space-lg"
            >
                {({ errors, processing, hasErrors }) => (
                    <>
                        {settingGroups.map((group) => (
                            <section
                                key={group.title}
                                className="rounded-lg border border-[#d8dee4] bg-surface-container-lowest shadow-[0_2px_4px_rgba(15,35,71,0.04)]"
                            >
                                <header className="flex items-start gap-3 border-b border-surface-container px-space-lg py-space-md">
                                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-surface-container text-primary">
                                        <Icon
                                            name={group.icon}
                                            className="text-[22px]"
                                        />
                                    </div>
                                    <div>
                                        <h2 className="font-serif text-title font-bold text-primary">
                                            {group.title}
                                        </h2>
                                        <p className="text-body-sm text-on-surface-variant">
                                            {group.description}
                                        </p>
                                    </div>
                                </header>
                                <div className="grid grid-cols-1 gap-space-lg p-space-lg md:grid-cols-2">
                                    {group.fields.map((field) => {
                                        const className = field.wide
                                            ? 'md:col-span-2'
                                            : undefined;

                                        if (field.type === 'image') {
                                            return (
                                                <div
                                                    key={field.key}
                                                    className={className}
                                                >
                                                    <ImageField
                                                        name={field.key}
                                                        label={field.label}
                                                        help={field.help}
                                                        currentUrl={
                                                            imageUrls[field.key]
                                                        }
                                                        error={
                                                            errors[field.key]
                                                        }
                                                    />
                                                </div>
                                            );
                                        }

                                        if (field.type === 'textarea') {
                                            return (
                                                <TextAreaField
                                                    key={field.key}
                                                    name={field.key}
                                                    label={field.label}
                                                    help={field.help}
                                                    defaultValue={
                                                        settings[field.key]
                                                    }
                                                    error={errors[field.key]}
                                                    className={className}
                                                />
                                            );
                                        }

                                        return (
                                            <TextField
                                                key={field.key}
                                                name={field.key}
                                                label={field.label}
                                                help={field.help}
                                                type={
                                                    field.type === 'email'
                                                        ? 'email'
                                                        : 'text'
                                                }
                                                defaultValue={
                                                    settings[field.key]
                                                }
                                                error={errors[field.key]}
                                                className={className}
                                            />
                                        );
                                    })}
                                </div>
                            </section>
                        ))}

                        <div className="sticky bottom-4 flex items-center justify-end gap-3 rounded-lg border border-[#d8dee4] bg-surface-container-lowest/95 px-space-lg py-space-md shadow-[0_8px_16px_rgba(15,35,71,0.08)] backdrop-blur-md">
                            {hasErrors && (
                                <p className="mr-auto text-body-sm text-red-700">
                                    Some fields need attention. Scroll up to fix
                                    them.
                                </p>
                            )}
                            <SubmitButton processing={processing}>
                                Save settings
                            </SubmitButton>
                        </div>
                    </>
                )}
            </Form>
        </AdminLayout>
    );
}
