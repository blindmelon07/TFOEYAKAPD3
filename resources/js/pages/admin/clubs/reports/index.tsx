import { Link, usePage } from '@inertiajs/react';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { reports as reportsHub } from '@/routes/admin';
import { show } from '@/routes/admin/clubs/reports';
import type { ReportCard } from '@/types';

export default function ClubReports({
    club,
    reports,
}: {
    club: { id: number; name: string };
    reports: ReportCard[];
}) {
    const { auth } = usePage().props;

    return (
        <AdminLayout
            title={`${club.name} Reports`}
            description="View on screen, print, or download as Word or Excel."
        >
            <div className="flex flex-col gap-space-lg">
                <ul className="grid grid-cols-1 gap-gutter md:grid-cols-2">
                    {reports.map((report) => (
                        <li key={report.key}>
                            <Link
                                href={show.url({
                                    club: club.id,
                                    report: report.key,
                                })}
                                className="flex h-full items-start gap-4 rounded-lg border border-l-[3px] border-[#d8dee4] border-l-secondary-fixed-dim bg-surface-container-lowest p-space-lg shadow-[0_2px_4px_rgba(15,35,71,0.04)] transition-shadow hover:shadow-[0_8px_16px_rgba(15,35,71,0.08)]"
                            >
                                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded bg-primary-container text-secondary-fixed">
                                    <Icon
                                        name={report.icon}
                                        className="text-[26px]"
                                    />
                                </div>
                                <div className="flex min-w-0 flex-1 flex-col gap-1">
                                    <span className="font-serif text-title font-bold text-primary">
                                        {report.name}
                                    </span>
                                    <span className="text-body-sm text-on-surface-variant">
                                        {report.description}
                                    </span>
                                    <span className="mt-1 flex flex-wrap gap-1.5 text-label-sm text-on-surface-variant">
                                        {[
                                            'Screen',
                                            'Print',
                                            'Word',
                                            'Excel',
                                        ].map((format) => (
                                            <span
                                                key={format}
                                                className="rounded bg-surface-container px-1.5 py-0.5"
                                            >
                                                {format}
                                            </span>
                                        ))}
                                    </span>
                                </div>
                                <Icon
                                    name="chevron_right"
                                    className="shrink-0 self-center text-[24px] text-outline"
                                />
                            </Link>
                        </li>
                    ))}
                </ul>

                {auth.can.manageClubs && (
                    <Link
                        href={reportsHub.url()}
                        className="self-start text-label-md text-primary hover:underline"
                    >
                        ← Back to all clubs
                    </Link>
                )}
            </div>
        </AdminLayout>
    );
}
