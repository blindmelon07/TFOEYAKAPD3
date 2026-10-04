import { Link } from '@inertiajs/react';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { index as clubsIndex } from '@/routes/admin/clubs';
import { create, index } from '@/routes/admin/clubs/documents';

type ClubForms = {
    id: number;
    name: string;
    location: string | null;
    logo_url: string | null;
    documents_count: number;
    latest_document_date: string | null;
    has_own_letterhead: boolean;
};

const longDate = (value: string) =>
    new Date(`${value.slice(0, 10)}T00:00:00`).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

export default function Forms({ clubs }: { clubs: ClubForms[] }) {
    return (
        <AdminLayout
            title="Forms"
            description="Each club's forms, letters and memos, printed on its letterhead. Pick a club to open its forms."
        >
            {clubs.length === 0 ? (
                <div className="flex flex-col items-center gap-3 rounded-lg border-2 border-dashed border-outline-variant bg-surface-container-lowest px-6 py-16 text-center">
                    <Icon
                        name="description"
                        className="text-[44px] text-outline"
                    />
                    <h2 className="font-serif text-title font-bold text-primary">
                        No clubs yet
                    </h2>
                    <p className="max-w-sm text-body-md text-on-surface-variant">
                        Forms belong to a club. Add a club first.
                    </p>
                    <Link
                        href={clubsIndex.url()}
                        className="mt-2 inline-flex items-center gap-2 rounded bg-primary-container px-4 py-2.5 text-label-md text-on-primary hover:bg-[#1e3a8a]"
                    >
                        Go to Clubs
                    </Link>
                </div>
            ) : (
                <ul className="grid grid-cols-1 gap-gutter md:grid-cols-2">
                    {clubs.map((club) => (
                        <li
                            key={club.id}
                            className="flex flex-col overflow-hidden rounded-lg border border-[#d8dee4] bg-surface-container-lowest shadow-[0_2px_4px_rgba(15,35,71,0.04)] transition-shadow hover:shadow-[0_8px_16px_rgba(15,35,71,0.08)]"
                        >
                            <Link
                                href={index.url(club.id)}
                                className="flex flex-1 items-center gap-4 p-space-lg"
                            >
                                <div className="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary-container">
                                    {club.logo_url ? (
                                        <img
                                            src={club.logo_url}
                                            alt=""
                                            className="h-full w-full object-contain"
                                        />
                                    ) : (
                                        <Icon
                                            name="shield"
                                            className="text-[28px] text-secondary-fixed"
                                        />
                                    )}
                                </div>
                                <div className="flex min-w-0 flex-1 flex-col gap-0.5">
                                    <span className="truncate font-serif text-title font-bold text-primary">
                                        {club.name}
                                    </span>
                                    <span className="text-body-sm text-on-surface-variant">
                                        {club.documents_count === 0
                                            ? 'No forms yet'
                                            : `${club.documents_count} ${club.documents_count === 1 ? 'form' : 'forms'} · latest ${longDate(club.latest_document_date ?? '')}`}
                                    </span>
                                    <span className="text-label-sm text-outline">
                                        {club.has_own_letterhead
                                            ? 'Club letterhead'
                                            : 'District letterhead'}
                                    </span>
                                </div>
                                <Icon
                                    name="chevron_right"
                                    className="shrink-0 text-[24px] text-outline"
                                />
                            </Link>
                            <div className="grid grid-cols-2 gap-2 border-t border-surface-container p-space-md">
                                <Link
                                    href={index.url(club.id)}
                                    className="inline-flex items-center justify-center gap-2 rounded border border-secondary-fixed-dim px-3 py-2 text-label-md text-primary transition-colors hover:bg-[#d4af37]/10"
                                >
                                    <Icon
                                        name="folder_open"
                                        className="text-[18px]"
                                    />
                                    Open forms
                                </Link>
                                <Link
                                    href={create.url(club.id)}
                                    className="inline-flex items-center justify-center gap-2 rounded bg-primary-container px-3 py-2 text-label-md text-on-primary transition-colors hover:bg-[#1e3a8a]"
                                >
                                    <Icon
                                        name="note_add"
                                        className="text-[18px]"
                                    />
                                    New form
                                </Link>
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </AdminLayout>
    );
}
