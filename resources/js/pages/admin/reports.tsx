import { Link } from '@inertiajs/react';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { index as clubsIndex } from '@/routes/admin/clubs';
import { index, show } from '@/routes/admin/clubs/reports';
import type { ReportCard } from '@/types';

type ClubRow = {
    id: number;
    name: string;
    location: string | null;
    logo_url: string | null;
    members_count: number;
};

export default function Reports({
    clubs,
    reports,
}: {
    clubs: ClubRow[];
    reports: ReportCard[];
}) {
    return (
        <AdminLayout
            title="Reports"
            description="Dues, payments and membership reports for each club."
        >
            {clubs.length === 0 ? (
                <div className="flex flex-col items-center gap-3 rounded-lg border-2 border-dashed border-outline-variant bg-surface-container-lowest px-6 py-16 text-center">
                    <Icon
                        name="monitoring"
                        className="text-[44px] text-outline"
                    />
                    <h2 className="font-serif text-title font-bold text-primary">
                        No clubs yet
                    </h2>
                    <p className="max-w-sm text-body-md text-on-surface-variant">
                        Reports are per club. Add a club first.
                    </p>
                    <Link
                        href={clubsIndex.url()}
                        className="mt-2 inline-flex items-center gap-2 rounded bg-primary-container px-4 py-2.5 text-label-md text-on-primary hover:bg-[#1e3a8a]"
                    >
                        Go to Clubs
                    </Link>
                </div>
            ) : (
                <ul className="flex flex-col gap-gutter">
                    {clubs.map((club) => (
                        <li
                            key={club.id}
                            className="overflow-hidden rounded-lg border border-[#d8dee4] bg-surface-container-lowest shadow-[0_2px_4px_rgba(15,35,71,0.04)]"
                        >
                            <Link
                                href={index.url(club.id)}
                                className="flex items-center gap-4 border-b border-surface-container px-space-lg py-space-md hover:bg-surface-container-low"
                            >
                                <div className="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary-container">
                                    {club.logo_url ? (
                                        <img
                                            src={club.logo_url}
                                            alt=""
                                            className="h-full w-full object-contain"
                                        />
                                    ) : (
                                        <Icon
                                            name="shield"
                                            className="text-[26px] text-secondary-fixed"
                                        />
                                    )}
                                </div>
                                <div className="flex min-w-0 flex-1 flex-col">
                                    <span className="truncate font-serif text-title font-bold text-primary">
                                        {club.name}
                                    </span>
                                    <span className="text-body-sm text-on-surface-variant">
                                        {club.members_count}{' '}
                                        {club.members_count === 1
                                            ? 'member'
                                            : 'members'}
                                        {club.location && ` · ${club.location}`}
                                    </span>
                                </div>
                                <Icon
                                    name="chevron_right"
                                    className="text-[24px] text-outline"
                                />
                            </Link>
                            <div className="grid grid-cols-2 gap-2 p-space-md md:grid-cols-4">
                                {reports.map((report) => (
                                    <Link
                                        key={report.key}
                                        href={show.url({
                                            club: club.id,
                                            report: report.key,
                                        })}
                                        className="inline-flex items-center gap-2 rounded border border-secondary-fixed-dim px-3 py-2 text-label-md text-primary transition-colors hover:bg-[#d4af37]/10"
                                    >
                                        <Icon
                                            name={report.icon}
                                            className="text-[18px]"
                                        />
                                        <span className="truncate">
                                            {report.name}
                                        </span>
                                    </Link>
                                ))}
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </AdminLayout>
    );
}
