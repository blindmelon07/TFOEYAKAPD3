import type { ReactNode } from 'react';
import { Icon } from '@/components/icon';
import {
    fundAllocationColorClasses,
    fundAllocationColorLabels,
} from '@/lib/content';
import { cn } from '@/lib/utils';
import * as fundAllocationRoutes from '@/routes/admin/fund-allocations';
import * as membershipStepRoutes from '@/routes/admin/membership-steps';
import * as missionRoutes from '@/routes/admin/missions';
import * as pillarRoutes from '@/routes/admin/pillars';
import * as statRoutes from '@/routes/admin/stats';
import type { FundAllocationColor } from '@/types';

export type ContentItem = { id: number; [key: string]: unknown };

export type FieldDefinition = {
    name: string;
    label: string;
    type: 'text' | 'textarea' | 'number' | 'select' | 'icon' | 'image';
    help?: string;
    required?: boolean;
    options?: { value: string; label: string }[];
    /** For image fields: the item attribute holding the current image URL. */
    previewAttribute?: string;
};

export type ColumnDefinition = {
    label: string;
    className?: string;
    render: (item: ContentItem) => ReactNode;
};

type ResourceRoutes = {
    index: { url: () => string };
    create: { url: () => string };
    edit: { url: (id: number) => string };
    store: { form: () => { action: string; method: 'post' } };
    update: { form: (id: number) => { action: string; method: 'post' } };
    destroy: { url: (id: number) => string };
};

export type ResourceDefinition = {
    title: string;
    singular: string;
    description: string;
    fields: FieldDefinition[];
    columns: ColumnDefinition[];
    routes: ResourceRoutes;
};

/**
 * Convert a loosely-typed attribute value to display text.
 */
export function toDisplayText(value: unknown): string {
    if (typeof value === 'string') {
        return value;
    }

    if (typeof value === 'number' || typeof value === 'boolean') {
        return `${value}`;
    }

    return '';
}

const text = (item: ContentItem, key: string) => toDisplayText(item[key]);

const sortOrderField: FieldDefinition = {
    name: 'sort_order',
    label: 'Position',
    type: 'number',
    required: true,
    help: 'Lower numbers appear first. Leave gaps (10, 20, 30…) so items can be slotted in between later.',
};

const iconColumn: ColumnDefinition = {
    label: '',
    className: 'w-14',
    render: (item) => (
        <div className="flex h-10 w-10 items-center justify-center rounded bg-surface-container text-primary">
            <Icon name={text(item, 'icon')} className="text-[22px]" />
        </div>
    ),
};

export const adminResources: Record<string, ResourceDefinition> = {
    stats: {
        title: 'Hero Stats',
        singular: 'stat',
        description:
            'The figures in the navy ribbon at the bottom of the hero.',
        routes: statRoutes,
        fields: [
            {
                name: 'value',
                label: 'Value',
                type: 'text',
                required: true,
                help: 'Shown large in gold, e.g. "500+" or "YKP-042".',
            },
            { name: 'label', label: 'Label', type: 'text', required: true },
            sortOrderField,
        ],
        columns: [
            {
                label: 'Value',
                render: (item) => (
                    <span className="font-serif text-title font-bold text-primary">
                        {text(item, 'value')}
                    </span>
                ),
            },
            { label: 'Label', render: (item) => text(item, 'label') },
        ],
    },
    pillars: {
        title: 'Four Pillars',
        singular: 'pillar',
        description: 'The tenet cards under “Foundation of the Order”.',
        routes: pillarRoutes,
        fields: [
            { name: 'icon', label: 'Icon', type: 'icon', required: true },
            {
                name: 'tag',
                label: 'Tag line',
                type: 'text',
                required: true,
                help: 'Small gold label above the title, e.g. "Pillar I • Kapatiran".',
            },
            { name: 'title', label: 'Title', type: 'text', required: true },
            {
                name: 'description',
                label: 'Description',
                type: 'textarea',
                required: true,
            },
            sortOrderField,
        ],
        columns: [
            iconColumn,
            {
                label: 'Pillar',
                render: (item) => (
                    <div className="flex flex-col">
                        <span className="text-label-sm tracking-wider text-secondary uppercase">
                            {text(item, 'tag')}
                        </span>
                        <span className="text-title text-primary">
                            {text(item, 'title')}
                        </span>
                    </div>
                ),
            },
        ],
    },
    missions: {
        title: 'Missions',
        singular: 'mission',
        description: 'The photo cards under “YAKAP Socio-Civic Missions”.',
        routes: missionRoutes,
        fields: [
            {
                name: 'image',
                label: 'Photo',
                type: 'image',
                previewAttribute: 'image_url',
                help: 'JPG, PNG or WebP up to 5 MB. Landscape photos work best.',
            },
            {
                name: 'image_alt',
                label: 'Photo description',
                type: 'text',
                help: 'Describes the photo for screen readers.',
            },
            {
                name: 'category',
                label: 'Category badge',
                type: 'text',
                required: true,
                help: 'Shown on the photo, e.g. "Health & Wellness".',
            },
            {
                name: 'program',
                label: 'Program name',
                type: 'text',
                required: true,
            },
            { name: 'title', label: 'Title', type: 'text', required: true },
            {
                name: 'description',
                label: 'Description',
                type: 'textarea',
                required: true,
            },
            {
                name: 'metric_label',
                label: 'Metric label',
                type: 'text',
                required: true,
                help: 'e.g. "Beneficiaries".',
            },
            {
                name: 'metric_value',
                label: 'Metric value',
                type: 'text',
                required: true,
                help: 'e.g. "1,240 Citizens".',
            },
            sortOrderField,
        ],
        columns: [
            {
                label: '',
                className: 'w-24',
                render: (item) =>
                    item.image_url ? (
                        <img
                            src={text(item, 'image_url')}
                            alt=""
                            className="h-14 w-20 rounded object-cover"
                        />
                    ) : (
                        <div className="flex h-14 w-20 items-center justify-center rounded bg-surface-container">
                            <Icon
                                name="image"
                                className="text-[22px] text-outline"
                            />
                        </div>
                    ),
            },
            {
                label: 'Mission',
                render: (item) => (
                    <div className="flex flex-col">
                        <span className="text-label-sm text-secondary">
                            {text(item, 'category')}
                        </span>
                        <span className="text-title text-primary">
                            {text(item, 'title')}
                        </span>
                    </div>
                ),
            },
            {
                label: 'Metric',
                className: 'hidden md:table-cell',
                render: (item) =>
                    `${text(item, 'metric_value')} · ${text(item, 'metric_label')}`,
            },
        ],
    },
    'fund-allocations': {
        title: 'Fund Allocation',
        singular: 'allocation',
        description:
            'The segments of the “Annual Civic Fund Allocation” bar. Percentages should add up to 100.',
        routes: fundAllocationRoutes,
        fields: [
            { name: 'label', label: 'Label', type: 'text', required: true },
            {
                name: 'percentage',
                label: 'Percentage',
                type: 'number',
                required: true,
                help: 'A whole number from 0 to 100.',
            },
            {
                name: 'color',
                label: 'Bar colour',
                type: 'select',
                required: true,
                options: Object.entries(fundAllocationColorLabels).map(
                    ([value, label]) => ({ value, label }),
                ),
            },
            sortOrderField,
        ],
        columns: [
            {
                label: '',
                className: 'w-10',
                render: (item) => (
                    <span
                        className={cn(
                            'inline-block h-5 w-5 rounded-full',
                            fundAllocationColorClasses[
                                item.color as FundAllocationColor
                            ],
                        )}
                    />
                ),
            },
            {
                label: 'Label',
                render: (item) => (
                    <span className="text-title text-primary">
                        {text(item, 'label')}
                    </span>
                ),
            },
            {
                label: 'Share',
                render: (item) => `${text(item, 'percentage')}%`,
            },
        ],
    },
    'membership-steps': {
        title: 'Membership Steps',
        singular: 'step',
        description:
            'The numbered cards under “How to Soar With the Eagles”. Numbers follow the order below.',
        routes: membershipStepRoutes,
        fields: [
            { name: 'icon', label: 'Icon', type: 'icon', required: true },
            { name: 'title', label: 'Title', type: 'text', required: true },
            {
                name: 'description',
                label: 'Description',
                type: 'textarea',
                required: true,
            },
            {
                name: 'requirement',
                label: 'Requirement',
                type: 'text',
                required: true,
                help: 'Shown after "Requirement:" at the bottom of the card.',
            },
            sortOrderField,
        ],
        columns: [
            iconColumn,
            {
                label: 'Step',
                render: (item) => (
                    <div className="flex flex-col">
                        <span className="text-title text-primary">
                            {text(item, 'title')}
                        </span>
                        <span className="text-body-sm text-on-surface-variant">
                            Requirement: {text(item, 'requirement')}
                        </span>
                    </div>
                ),
            },
        ],
    },
};
