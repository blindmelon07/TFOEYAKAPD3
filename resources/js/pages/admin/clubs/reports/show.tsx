import { Form, Link } from '@inertiajs/react';
import {
    formatReportValue,
    ReportChartView,
    toneIcons,
} from '@/components/admin/charts';
import { inputClassName } from '@/components/admin/form-fields';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { cn } from '@/lib/utils';
import { csv, index, print, show, word } from '@/routes/admin/clubs/reports';
import type {
    ReportCard,
    ReportColumn,
    ReportData,
    ReportFilter,
    ReportSection,
} from '@/types';

const numericTypes = new Set(['money', 'number', 'percent']);

function FilterField({
    filter,
    value,
}: {
    filter: ReportFilter;
    value: string | number | undefined;
}) {
    const submitOnChange = (
        event: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>,
    ) => event.currentTarget.form?.requestSubmit();

    return (
        <label className="flex min-w-36 flex-1 flex-col gap-1 sm:flex-none">
            <span className="text-label-sm tracking-wider text-on-surface-variant uppercase">
                {filter.label}
            </span>
            {filter.type === 'select' ? (
                <select
                    name={filter.name}
                    defaultValue={String(value ?? '')}
                    onChange={submitOnChange}
                    className={cn(inputClassName, 'py-2')}
                >
                    {filter.options?.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>
            ) : (
                <input
                    name={filter.name}
                    type={filter.type === 'year' ? 'number' : 'date'}
                    min={filter.type === 'year' ? 1979 : undefined}
                    defaultValue={String(value ?? '')}
                    onChange={
                        filter.type === 'date' ? submitOnChange : undefined
                    }
                    className={cn(inputClassName, 'py-2 sm:w-40')}
                />
            )}
        </label>
    );
}

function Cell({
    value,
    column,
    isTotal = false,
}: {
    value: string | number | null | undefined;
    column: ReportColumn;
    isTotal?: boolean;
}) {
    return (
        <td
            className={cn(
                'px-3 py-2 align-top sm:px-4',
                numericTypes.has(column.type) &&
                    'text-right whitespace-nowrap tabular-nums',
                isTotal && 'font-bold text-primary',
            )}
        >
            {value === undefined || (isTotal && value === null)
                ? ''
                : formatReportValue(value, column.type)}
        </td>
    );
}

function SectionTable({ section }: { section: ReportSection }) {
    return (
        <section className="overflow-hidden rounded-lg border border-[#d8dee4] bg-surface-container-lowest shadow-[0_2px_4px_rgba(15,35,71,0.04)]">
            <header className="flex items-center justify-between gap-3 border-b border-surface-container px-space-lg py-space-md">
                <h2 className="font-serif text-title font-bold text-primary">
                    {section.title}
                </h2>
                <span className="text-body-sm text-on-surface-variant">
                    {section.rows.length}{' '}
                    {section.rows.length === 1 ? 'row' : 'rows'}
                </span>
            </header>
            {section.rows.length === 0 ? (
                <p className="px-space-lg py-space-xl text-center text-body-md text-on-surface-variant">
                    {section.empty}
                </p>
            ) : (
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-body-sm">
                        <thead className="bg-surface-container-low text-label-sm tracking-wider text-on-surface-variant uppercase">
                            <tr>
                                {section.columns.map((column) => (
                                    <th
                                        key={column.key}
                                        className={cn(
                                            'px-3 py-2.5 whitespace-nowrap sm:px-4',
                                            numericTypes.has(column.type) &&
                                                'text-right',
                                        )}
                                    >
                                        {column.label}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-surface-container">
                            {section.rows.map((row, rowIndex) => (
                                <tr
                                    key={rowIndex}
                                    className="hover:bg-surface-container-low"
                                >
                                    {section.columns.map((column) => (
                                        <Cell
                                            key={column.key}
                                            value={row[column.key]}
                                            column={column}
                                        />
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                        {section.totals && (
                            <tfoot className="border-t-2 border-outline-variant bg-surface-container-low">
                                <tr>
                                    {section.columns.map((column) => (
                                        <Cell
                                            key={column.key}
                                            value={
                                                section.totals?.[column.key] ??
                                                null
                                            }
                                            column={column}
                                            isTotal
                                        />
                                    ))}
                                </tr>
                            </tfoot>
                        )}
                    </table>
                </div>
            )}
        </section>
    );
}

export default function ReportShow({
    club,
    report,
    filters,
    data,
}: {
    club: { id: number; name: string };
    report: ReportCard & { filters: ReportFilter[] };
    filters: Record<string, string | number>;
    data: ReportData;
}) {
    const routeArgs = { club: club.id, report: report.key };
    const query = { query: filters };

    const exportLinkClassName =
        'inline-flex flex-1 items-center justify-center gap-2 rounded border border-secondary-fixed-dim bg-surface-container-lowest px-3 py-2 text-label-md text-primary transition-colors hover:bg-[#d4af37]/10 sm:flex-none';

    return (
        <AdminLayout
            title={data.title}
            description={`${club.name} · ${data.period}`}
        >
            <div className="flex flex-col gap-space-lg">
                <div className="flex flex-col gap-space-md rounded-lg border border-[#d8dee4] bg-surface-container-lowest p-space-md shadow-[0_2px_4px_rgba(15,35,71,0.04)] lg:flex-row lg:items-end lg:justify-between">
                    {report.filters.length > 0 ? (
                        <Form
                            {...show.form(routeArgs)}
                            options={{ preserveScroll: true }}
                            className="flex flex-wrap items-end gap-space-sm"
                        >
                            {report.filters.map((filter) => (
                                <FilterField
                                    key={filter.name}
                                    filter={filter}
                                    value={filters[filter.name]}
                                />
                            ))}
                            <button
                                type="submit"
                                className="rounded bg-primary-container px-4 py-2.5 text-label-md text-on-primary hover:bg-[#1e3a8a]"
                            >
                                Update
                            </button>
                        </Form>
                    ) : (
                        <p className="text-body-sm text-on-surface-variant">
                            {report.description}
                        </p>
                    )}

                    <div className="flex flex-wrap gap-2">
                        <a
                            href={print.url(routeArgs, query)}
                            target="_blank"
                            rel="noreferrer"
                            className={exportLinkClassName}
                        >
                            <Icon name="print" className="text-[18px]" />
                            Print
                        </a>
                        <a
                            href={word.url(routeArgs, query)}
                            className={exportLinkClassName}
                        >
                            <Icon name="description" className="text-[18px]" />
                            Word
                        </a>
                        <a
                            href={csv.url(routeArgs, query)}
                            className={exportLinkClassName}
                        >
                            <Icon name="table_view" className="text-[18px]" />
                            Excel (CSV)
                        </a>
                    </div>
                </div>

                {data.notice && (
                    <div className="flex items-start gap-2 rounded-lg border border-secondary-fixed-dim bg-[#fffbeb] px-4 py-3 text-body-sm text-[#7a4a06]">
                        <Icon name="info" className="text-[20px]" />
                        {data.notice}
                    </div>
                )}

                <div className="grid grid-cols-2 gap-gutter md:grid-cols-3 xl:grid-cols-4">
                    {data.summary.map((item) => (
                        <div
                            key={item.label}
                            className="flex flex-col gap-1 rounded-lg border border-[#d8dee4] bg-surface-container-lowest px-4 py-3 shadow-[0_2px_4px_rgba(15,35,71,0.04)]"
                        >
                            <span className="flex items-center gap-1.5 text-label-sm tracking-wider text-on-surface-variant uppercase">
                                {item.tone && (
                                    <Icon
                                        name={toneIcons[item.tone].icon}
                                        className="text-[16px]"
                                        style={{
                                            color: toneIcons[item.tone].color,
                                        }}
                                    />
                                )}
                                {item.label}
                            </span>
                            <span className="text-headline-sm font-semibold text-primary tabular-nums">
                                {formatReportValue(item.value, item.type)}
                            </span>
                        </div>
                    ))}
                </div>

                {data.charts.length > 0 && (
                    <div className="grid grid-cols-1 gap-gutter">
                        {data.charts.map((chart) => (
                            <div
                                key={chart.title}
                                className="rounded-lg border border-[#d8dee4] bg-surface-container-lowest p-space-lg shadow-[0_2px_4px_rgba(15,35,71,0.04)]"
                            >
                                <ReportChartView chart={chart} />
                            </div>
                        ))}
                    </div>
                )}

                {data.sections.map((section) => (
                    <SectionTable key={section.title} section={section} />
                ))}

                <Link
                    href={index.url(club.id)}
                    className="self-start text-label-md text-primary hover:underline"
                >
                    ← All reports for {club.name}
                </Link>
            </div>
        </AdminLayout>
    );
}
