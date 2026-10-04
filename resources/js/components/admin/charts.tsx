import { useState } from 'react';
import { Icon } from '@/components/icon';
import type { ReportChart, ReportValueType } from '@/types';

/**
 * Single-series bar colour: brand-adjacent indigo, validated against the
 * white card surface (lightness band, chroma floor, >= 3:1 contrast).
 */
const BAR_COLOR = '#2b4fb3';

/**
 * Reserved status colours. Never shown alone: always with an icon and label.
 */
const TONES = {
    good: { color: '#0ca30c', icon: 'check_circle' },
    warning: { color: '#fab219', icon: 'hourglass_bottom' },
    critical: { color: '#d03b3b', icon: 'error' },
} as const;

const pesos = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    maximumFractionDigits: 0,
});

export function formatReportValue(
    value: string | number | null,
    type: ReportValueType,
    options: { compact?: boolean } = {},
): string {
    if (value === null || value === '') {
        return '—';
    }

    if (typeof value === 'string' && Number.isNaN(Number(value))) {
        if (type === 'date') {
            return new Date(`${value}T00:00:00`).toLocaleDateString('en-PH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        }

        return value;
    }

    const number = Number(value);

    switch (type) {
        case 'money':
            return options.compact
                ? pesos.format(number)
                : new Intl.NumberFormat('en-PH', {
                      style: 'currency',
                      currency: 'PHP',
                  }).format(number);
        case 'percent':
            return `${number.toFixed(1)}%`;
        case 'number':
            return number.toLocaleString('en-PH');
        default:
            return String(value);
    }
}

/**
 * Round the axis maximum up to a clean value (1, 2, 2.5, 5 × 10^n).
 */
function niceMaximum(value: number): number {
    if (value <= 0) {
        return 1;
    }

    const magnitude = 10 ** Math.floor(Math.log10(value));

    for (const step of [1, 2, 2.5, 5, 10]) {
        if (value <= step * magnitude) {
            return step * magnitude;
        }
    }

    return 10 * magnitude;
}

/**
 * One series of columns (e.g. money received per month) with a hover
 * tooltip per column, hairline gridlines and rounded axis ticks.
 */
function ColumnChart({ chart }: { chart: ReportChart }) {
    const [activeIndex, setActiveIndex] = useState<number | null>(null);
    const maximum = niceMaximum(
        Math.max(...chart.points.map((point) => point.value), 0),
    );
    const ticks = [0, 0.25, 0.5, 0.75, 1].map((share) => share * maximum);
    const labelEvery = Math.ceil(chart.points.length / 12);
    const active = activeIndex === null ? null : chart.points[activeIndex];

    return (
        <figure className="flex flex-col gap-3">
            <figcaption className="flex items-baseline justify-between gap-3">
                <span className="text-label-md text-primary">
                    {chart.title}
                </span>
                <span className="text-body-sm text-on-surface-variant tabular-nums">
                    {active
                        ? `${active.label}: ${formatReportValue(active.value, chart.valueType)}`
                        : 'Hover a column for details'}
                </span>
            </figcaption>
            <div className="flex gap-2">
                <div className="flex h-48 flex-col-reverse justify-between pb-6 text-right text-[0.65rem] text-on-surface-variant tabular-nums">
                    {ticks.map((tick) => (
                        <span key={tick} className="leading-none">
                            {formatReportValue(tick, chart.valueType, {
                                compact: true,
                            })}
                        </span>
                    ))}
                </div>
                <div className="relative flex-1">
                    <div className="pointer-events-none absolute inset-x-0 top-0 bottom-6 flex flex-col-reverse justify-between">
                        {ticks.map((tick) => (
                            <span
                                key={tick}
                                className="h-px w-full bg-surface-container-high"
                            />
                        ))}
                    </div>
                    <div
                        className="relative flex h-48 items-stretch gap-[2px]"
                        onMouseLeave={() => setActiveIndex(null)}
                    >
                        {chart.points.map((point, index) => (
                            <button
                                key={point.label}
                                type="button"
                                aria-label={`${point.label}: ${formatReportValue(point.value, chart.valueType)}`}
                                onMouseEnter={() => setActiveIndex(index)}
                                onFocus={() => setActiveIndex(index)}
                                onBlur={() => setActiveIndex(null)}
                                className="group flex min-w-0 flex-1 flex-col items-center justify-end outline-none"
                            >
                                <span className="flex w-full flex-1 items-end justify-center pb-0">
                                    <span
                                        className="w-full max-w-6 rounded-t transition-opacity group-hover:opacity-80 group-focus-visible:ring-2 group-focus-visible:ring-secondary-fixed-dim"
                                        style={{
                                            height: `${(point.value / maximum) * 100}%`,
                                            minHeight: point.value > 0 ? 2 : 0,
                                            backgroundColor: BAR_COLOR,
                                        }}
                                    />
                                </span>
                                <span className="h-6 w-full truncate pt-1 text-center text-[0.65rem] text-on-surface-variant">
                                    {index % labelEvery === 0
                                        ? point.label
                                        : ''}
                                </span>
                            </button>
                        ))}
                    </div>
                </div>
            </div>
        </figure>
    );
}

/**
 * Shares of a whole (paid / partial / unpaid) as one stacked bar with a
 * legend that always names each part, so colour never carries it alone.
 */
function ProportionChart({ chart }: { chart: ReportChart }) {
    const total = chart.points.reduce((sum, point) => sum + point.value, 0);

    return (
        <figure className="flex flex-col gap-3">
            <figcaption className="text-label-md text-primary">
                {chart.title}
            </figcaption>
            <div className="flex h-6 w-full gap-[2px] overflow-hidden rounded bg-surface-container">
                {chart.points
                    .filter((point) => point.value > 0)
                    .map((point) => (
                        <span
                            key={point.label}
                            title={`${point.label}: ${point.value} (${((point.value / total) * 100).toFixed(0)}%)`}
                            className="h-full first:rounded-l last:rounded-r"
                            style={{
                                width: `${(point.value / total) * 100}%`,
                                backgroundColor: point.tone
                                    ? TONES[point.tone].color
                                    : BAR_COLOR,
                            }}
                        />
                    ))}
            </div>
            <ul className="flex flex-wrap gap-x-5 gap-y-1 text-body-sm">
                {chart.points.map((point) => (
                    <li
                        key={point.label}
                        className="flex items-center gap-1.5 text-on-surface"
                    >
                        {point.tone && (
                            <Icon
                                name={TONES[point.tone].icon}
                                className="text-[16px]"
                                style={{ color: TONES[point.tone].color }}
                            />
                        )}
                        <span className="font-semibold">{point.label}</span>
                        <span className="text-on-surface-variant tabular-nums">
                            {point.value} ·{' '}
                            {total > 0
                                ? `${((point.value / total) * 100).toFixed(0)}%`
                                : '0%'}
                        </span>
                    </li>
                ))}
            </ul>
        </figure>
    );
}

export function ReportChartView({ chart }: { chart: ReportChart }) {
    return chart.kind === 'proportion' ? (
        <ProportionChart chart={chart} />
    ) : (
        <ColumnChart chart={chart} />
    );
}

export const toneIcons = Object.fromEntries(
    Object.entries(TONES).map(([tone, { icon, color }]) => [
        tone,
        { icon, color },
    ]),
) as Record<keyof typeof TONES, { icon: string; color: string }>;
