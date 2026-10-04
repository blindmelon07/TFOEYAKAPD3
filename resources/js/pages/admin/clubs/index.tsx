import { Link, router, usePage } from '@inertiajs/react';
import { MemberAvatar } from '@/components/admin/badges';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { create, destroy, edit } from '@/routes/admin/clubs';
import { index as documentsIndex } from '@/routes/admin/clubs/documents';
import { index as duesRatesIndex } from '@/routes/admin/clubs/dues-rates';
import {
    index as membersIndex,
    show as memberShow,
} from '@/routes/admin/members';
import type { SelectOption } from '@/types';

type Officer = {
    id: number;
    full_name: string;
    photo_url: string | null;
    position: string;
    position_label: string;
};

type ClubRow = {
    id: number;
    name: string;
    location: string | null;
    charter_number: string | null;
    logo_url: string | null;
    members_count: number;
    officers: Officer[];
};

function SummaryStat({
    icon,
    value,
    label,
}: {
    icon: string;
    value: string | number;
    label: string;
}) {
    return (
        <div className="flex items-center gap-3 rounded-lg border border-[#d8dee4] bg-surface-container-lowest px-4 py-3 shadow-[0_2px_4px_rgba(15,35,71,0.04)]">
            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-surface-container text-primary">
                <Icon name={icon} className="text-[22px]" />
            </div>
            <div className="flex flex-col">
                <span className="font-serif text-title leading-tight font-bold text-primary">
                    {value}
                </span>
                <span className="text-label-sm tracking-wider text-on-surface-variant uppercase">
                    {label}
                </span>
            </div>
        </div>
    );
}

function ClubLogo({ club }: { club: ClubRow }) {
    return (
        <div className="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary-container ring-4 ring-surface-container-lowest">
            {club.logo_url ? (
                <img
                    src={club.logo_url}
                    alt={`${club.name} logo`}
                    className="h-full w-full object-contain"
                />
            ) : (
                <Icon
                    name="shield"
                    className="text-[30px] text-secondary-fixed"
                />
            )}
        </div>
    );
}

function ClubCard({
    club,
    offices,
    onDelete,
}: {
    club: ClubRow;
    offices: SelectOption[];
    onDelete: (club: ClubRow) => void;
}) {
    const officerByPosition = new Map(
        club.officers.map((officer) => [officer.position, officer]),
    );

    return (
        <article className="flex flex-col overflow-hidden rounded-lg border border-[#d8dee4] bg-surface-container-lowest shadow-[0_2px_4px_rgba(15,35,71,0.04)] transition-shadow hover:shadow-[0_8px_16px_rgba(15,35,71,0.08)]">
            <div className="relative h-16 bg-primary">
                <div className="absolute inset-x-0 bottom-0 h-[3px] bg-secondary-fixed-dim" />
                <div className="absolute top-2 right-2 flex gap-1">
                    <Link
                        href={edit.url(club.id)}
                        aria-label={`Edit ${club.name}`}
                        className="flex h-9 w-9 items-center justify-center rounded text-primary-fixed-dim transition-colors hover:bg-white/10 hover:text-on-primary"
                    >
                        <Icon name="edit" className="text-[20px]" />
                    </Link>
                    <button
                        type="button"
                        aria-label={`Delete ${club.name}`}
                        onClick={() => onDelete(club)}
                        className="flex h-9 w-9 items-center justify-center rounded text-primary-fixed-dim transition-colors hover:bg-red-500/20 hover:text-red-200"
                    >
                        <Icon name="delete" className="text-[20px]" />
                    </button>
                </div>
            </div>

            <div className="-mt-8 flex flex-col gap-3 px-space-lg">
                <ClubLogo club={club} />
                <div className="flex flex-col gap-1.5">
                    <h2 className="font-serif text-title leading-snug font-bold text-primary">
                        {club.name}
                    </h2>
                    <div className="flex flex-wrap gap-1.5 text-label-sm">
                        {club.location && (
                            <span className="inline-flex items-center gap-1 rounded bg-surface-container px-2 py-0.5 text-on-surface-variant">
                                <Icon
                                    name="location_on"
                                    className="text-[14px]"
                                />
                                {club.location}
                            </span>
                        )}
                        {club.charter_number && (
                            <span className="inline-flex items-center gap-1 rounded border border-secondary-fixed-dim bg-[#fffbeb] px-2 py-0.5 text-[#b45309]">
                                <Icon name="verified" className="text-[14px]" />
                                Charter {club.charter_number}
                            </span>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-2 divide-x divide-surface-container rounded-lg bg-surface-container-low py-2 text-center">
                    <div className="flex flex-col">
                        <span className="font-serif text-headline-sm text-primary">
                            {club.members_count}
                        </span>
                        <span className="text-label-sm tracking-wider text-on-surface-variant uppercase">
                            {club.members_count === 1 ? 'Member' : 'Members'}
                        </span>
                    </div>
                    <div className="flex flex-col">
                        <span className="font-serif text-headline-sm text-primary">
                            {club.officers.length}
                            <span className="text-body-md text-outline">
                                /{offices.length}
                            </span>
                        </span>
                        <span className="text-label-sm tracking-wider text-on-surface-variant uppercase">
                            Offices filled
                        </span>
                    </div>
                </div>
            </div>

            <ul className="flex flex-1 flex-col px-space-lg py-space-md">
                {offices.map((office) => {
                    const officer = officerByPosition.get(office.value);

                    return (
                        <li
                            key={office.value}
                            className="flex min-h-11 items-center justify-between gap-3 border-b border-dashed border-surface-container-high py-1.5 last:border-b-0"
                        >
                            <span className="shrink-0 text-label-sm tracking-wider text-on-surface-variant uppercase">
                                {office.label.replace(/^Club /, '')}
                            </span>
                            {officer ? (
                                <Link
                                    href={memberShow.url(officer.id)}
                                    className="flex min-w-0 items-center gap-2 text-label-md text-primary hover:underline"
                                >
                                    <span className="truncate">
                                        {officer.full_name}
                                    </span>
                                    <MemberAvatar
                                        name={officer.full_name}
                                        photoUrl={officer.photo_url}
                                        size="sm"
                                    />
                                </Link>
                            ) : (
                                <span className="text-body-sm text-outline italic">
                                    Vacant
                                </span>
                            )}
                        </li>
                    );
                })}
            </ul>

            <div className="grid grid-cols-3 gap-2 border-t border-surface-container p-space-md">
                <Link
                    href={membersIndex.url({ query: { club: club.id } })}
                    className="inline-flex items-center justify-center gap-1.5 rounded bg-primary-container px-2 py-2 text-label-md text-on-primary transition-colors hover:bg-[#1e3a8a]"
                >
                    <Icon name="groups" className="text-[18px]" />
                    Roster
                </Link>
                <Link
                    href={duesRatesIndex.url(club.id)}
                    className="inline-flex items-center justify-center gap-1.5 rounded border border-secondary-fixed-dim px-2 py-2 text-label-md text-primary transition-colors hover:bg-[#d4af37]/10"
                >
                    <Icon name="request_quote" className="text-[18px]" />
                    Dues
                </Link>
                <Link
                    href={documentsIndex.url(club.id)}
                    className="inline-flex items-center justify-center gap-1.5 rounded border border-secondary-fixed-dim px-2 py-2 text-label-md text-primary transition-colors hover:bg-[#d4af37]/10"
                >
                    <Icon name="description" className="text-[18px]" />
                    Docs
                </Link>
            </div>
        </article>
    );
}

export default function ClubsIndex({
    clubs,
    offices,
}: {
    clubs: ClubRow[];
    offices: SelectOption[];
}) {
    const { errors } = usePage().props;

    const totalMembers = clubs.reduce(
        (total, club) => total + club.members_count,
        0,
    );
    const filledOffices = clubs.reduce(
        (total, club) => total + club.officers.length,
        0,
    );

    const deleteClub = (club: ClubRow) => {
        if (!window.confirm(`Delete ${club.name}? This cannot be undone.`)) {
            return;
        }

        router.delete(destroy.url(club.id), { preserveScroll: true });
    };

    return (
        <AdminLayout
            title="Clubs"
            description="The Eagles clubs under District III and their current officers."
            actions={
                <Link
                    href={create.url()}
                    className="inline-flex flex-1 items-center justify-center gap-2 rounded bg-primary-container px-4 py-2.5 text-label-md text-on-primary transition-colors hover:bg-[#1e3a8a] sm:flex-none"
                >
                    <Icon name="add" className="text-[20px]" />
                    <span>Add club</span>
                </Link>
            }
        >
            {errors.club && (
                <div className="mb-space-lg flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-body-sm text-red-800">
                    <Icon name="error" className="text-[20px]" />
                    {errors.club}
                </div>
            )}

            {clubs.length === 0 ? (
                <div className="flex flex-col items-center gap-3 rounded-lg border-2 border-dashed border-outline-variant bg-surface-container-lowest px-6 py-16 text-center">
                    <Icon
                        name="diversity_3"
                        className="text-[44px] text-outline"
                    />
                    <h2 className="font-serif text-title font-bold text-primary">
                        No clubs yet
                    </h2>
                    <p className="max-w-sm text-body-md text-on-surface-variant">
                        Add your first club, then add its members and appoint
                        its officers.
                    </p>
                    <Link
                        href={create.url()}
                        className="mt-2 inline-flex items-center gap-2 rounded bg-primary-container px-4 py-2.5 text-label-md text-on-primary hover:bg-[#1e3a8a]"
                    >
                        <Icon name="add" className="text-[20px]" />
                        Add the first club
                    </Link>
                </div>
            ) : (
                <div className="flex flex-col gap-space-lg">
                    <div className="grid grid-cols-1 gap-gutter sm:grid-cols-3">
                        <SummaryStat
                            icon="diversity_3"
                            value={clubs.length}
                            label={clubs.length === 1 ? 'Club' : 'Clubs'}
                        />
                        <SummaryStat
                            icon="groups"
                            value={totalMembers}
                            label="Members"
                        />
                        <SummaryStat
                            icon="military_tech"
                            value={`${filledOffices}/${clubs.length * offices.length}`}
                            label="Offices filled"
                        />
                    </div>

                    <div className="grid grid-cols-1 gap-gutter md:grid-cols-2 xl:grid-cols-3">
                        {clubs.map((club) => (
                            <ClubCard
                                key={club.id}
                                club={club}
                                offices={offices}
                                onDelete={deleteClub}
                            />
                        ))}

                        <Link
                            href={create.url()}
                            className="flex min-h-48 flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-outline-variant text-on-surface-variant transition-colors hover:border-secondary-fixed-dim hover:bg-surface-container-lowest hover:text-primary"
                        >
                            <span className="flex h-12 w-12 items-center justify-center rounded-full bg-surface-container">
                                <Icon name="add" className="text-[28px]" />
                            </span>
                            <span className="text-label-md">Add club</span>
                        </Link>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
