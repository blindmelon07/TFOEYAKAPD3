import { Form, Link } from '@inertiajs/react';
import {
    DuesBadge,
    type DuesStatus,
    MemberAvatar,
    PositionBadge,
    StatusBadge,
} from '@/components/admin/badges';
import { inputClassName } from '@/components/admin/form-fields';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { cn } from '@/lib/utils';
import { create, index, show } from '@/routes/admin/members';
import type { ClubOption, Paginated, SelectOption } from '@/types';

type MemberRow = {
    id: number;
    full_name: string;
    photo_url: string | null;
    member_number: string | null;
    club: string;
    position: string;
    position_label: string;
    status: string;
    dues_status: DuesStatus;
    dues_balance: string;
    has_login: boolean;
};

type Filters = {
    search?: string;
    club?: string;
    status?: string;
    dues?: string;
};

const selectClassName = cn(inputClassName, 'py-2');

export default function MembersIndex({
    members,
    filters,
    clubs,
    clubName,
    statuses,
    currentYear,
    canCreate,
}: {
    members: Paginated<MemberRow>;
    filters: Filters;
    clubs: ClubOption[];
    clubName: string | null;
    statuses: SelectOption[];
    currentYear: number;
    canCreate: boolean;
}) {
    const submitOnChange = (event: React.ChangeEvent<HTMLSelectElement>) =>
        event.currentTarget.form?.requestSubmit();

    return (
        <AdminLayout
            title={clubName ? `${clubName} Members` : 'All Members'}
            description={
                clubName
                    ? 'Members of your club. Officers are listed first.'
                    : 'Every member across District III. Officers are listed first.'
            }
            actions={
                canCreate && (
                    <Link
                        href={create.url()}
                        className="inline-flex flex-1 items-center justify-center gap-2 rounded bg-primary-container px-4 py-2.5 text-label-md text-on-primary transition-colors hover:bg-[#1e3a8a] sm:flex-none"
                    >
                        <Icon name="person_add" className="text-[20px]" />
                        <span>Add member</span>
                    </Link>
                )
            }
        >
            <Form
                {...index.form()}
                options={{ preserveState: true, preserveScroll: true }}
                className="mb-space-lg grid grid-cols-1 gap-space-sm rounded-lg border border-[#d8dee4] bg-surface-container-lowest p-space-md sm:grid-cols-2 lg:grid-cols-[2fr_repeat(3,1fr)_auto]"
            >
                <label className="relative">
                    <span className="sr-only">Search</span>
                    <Icon
                        name="search"
                        className="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-[20px] text-outline"
                    />
                    <input
                        type="search"
                        name="search"
                        defaultValue={filters.search ?? ''}
                        placeholder="Name or member number"
                        className={cn(inputClassName, 'py-2 pl-10')}
                    />
                </label>
                {clubs.length > 0 && (
                    <select
                        name="club"
                        aria-label="Club"
                        defaultValue={filters.club ?? ''}
                        onChange={submitOnChange}
                        className={selectClassName}
                    >
                        <option value="">All clubs</option>
                        {clubs.map((club) => (
                            <option key={club.id} value={club.id}>
                                {club.name}
                            </option>
                        ))}
                    </select>
                )}
                <select
                    name="status"
                    aria-label="Status"
                    defaultValue={filters.status ?? ''}
                    onChange={submitOnChange}
                    className={selectClassName}
                >
                    <option value="">Any status</option>
                    {statuses.map((status) => (
                        <option key={status.value} value={status.value}>
                            {status.label}
                        </option>
                    ))}
                </select>
                <select
                    name="dues"
                    aria-label={`${currentYear} dues`}
                    defaultValue={filters.dues ?? ''}
                    onChange={submitOnChange}
                    className={selectClassName}
                >
                    <option value="">{currentYear} dues: any</option>
                    <option value="paid">{currentYear} dues: paid</option>
                    <option value="partial">{currentYear} dues: partial</option>
                    <option value="unpaid">{currentYear} dues: unpaid</option>
                </select>
                <button
                    type="submit"
                    className="rounded bg-primary-container px-4 py-2 text-label-md text-on-primary hover:bg-[#1e3a8a]"
                >
                    Search
                </button>
            </Form>

            <div className="overflow-x-auto rounded-lg border border-[#d8dee4] bg-surface-container-lowest shadow-[0_2px_4px_rgba(15,35,71,0.04)]">
                {members.data.length === 0 ? (
                    <div className="flex flex-col items-center gap-3 px-6 py-16 text-center">
                        <Icon
                            name="group_off"
                            className="text-[40px] text-outline"
                        />
                        <p className="text-body-md text-on-surface-variant">
                            No members match these filters.
                        </p>
                    </div>
                ) : (
                    <>
                        <ul className="divide-y divide-[#d8dee4] sm:hidden">
                            {members.data.map((member) => (
                                <li key={member.id}>
                                    <Link
                                        href={show.url(member.id)}
                                        className="flex items-start gap-3 px-4 py-3 active:bg-surface-container-low"
                                    >
                                        <MemberAvatar
                                            name={member.full_name}
                                            photoUrl={member.photo_url}
                                        />
                                        <div className="flex min-w-0 flex-1 flex-col gap-1.5">
                                            <div className="flex items-start justify-between gap-2">
                                                <span className="min-w-0">
                                                    <span className="block truncate text-label-md text-primary">
                                                        {member.full_name}
                                                    </span>
                                                    <span className="block truncate font-mono text-label-sm font-normal text-outline">
                                                        {member.member_number ??
                                                            'No member no.'}
                                                        {clubs.length > 0 &&
                                                            ` · ${member.club}`}
                                                    </span>
                                                </span>
                                                <Icon
                                                    name="chevron_right"
                                                    className="shrink-0 text-[20px] text-outline"
                                                />
                                            </div>
                                            <div className="flex flex-wrap gap-1.5">
                                                <PositionBadge
                                                    position={member.position}
                                                    label={
                                                        member.position_label
                                                    }
                                                />
                                                {member.status !== 'active' && (
                                                    <StatusBadge
                                                        status={member.status}
                                                    />
                                                )}
                                                <DuesBadge
                                                    status={member.dues_status}
                                                    balance={
                                                        member.dues_balance
                                                    }
                                                    year={currentYear}
                                                />
                                            </div>
                                        </div>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                        <table className="hidden w-full text-left text-body-sm sm:table">
                            <thead className="border-b border-[#d8dee4] bg-surface-container-low text-label-sm tracking-wider text-on-surface-variant uppercase">
                                <tr>
                                    <th className="px-4 py-3">Member</th>
                                    {clubs.length > 0 && (
                                        <th className="hidden px-4 py-3 md:table-cell">
                                            Club
                                        </th>
                                    )}
                                    <th className="px-4 py-3">Position</th>
                                    <th className="hidden px-4 py-3 sm:table-cell">
                                        Status
                                    </th>
                                    <th className="px-4 py-3">Dues</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#d8dee4]">
                                {members.data.map((member) => (
                                    <tr
                                        key={member.id}
                                        className="transition-colors hover:bg-surface-container-low"
                                    >
                                        <td className="px-4 py-3">
                                            <Link
                                                href={show.url(member.id)}
                                                className="flex items-center gap-3"
                                            >
                                                <MemberAvatar
                                                    name={member.full_name}
                                                    photoUrl={member.photo_url}
                                                />
                                                <span className="flex flex-col">
                                                    <span className="text-label-md text-primary hover:underline">
                                                        {member.full_name}
                                                    </span>
                                                    <span className="font-mono text-label-sm font-normal text-outline">
                                                        {member.member_number ??
                                                            'No member no.'}
                                                        {member.has_login && (
                                                            <Icon
                                                                name="key"
                                                                className="ml-1 align-middle text-[14px] text-secondary"
                                                            />
                                                        )}
                                                    </span>
                                                </span>
                                            </Link>
                                        </td>
                                        {clubs.length > 0 && (
                                            <td className="hidden px-4 py-3 text-on-surface-variant md:table-cell">
                                                {member.club}
                                            </td>
                                        )}
                                        <td className="px-4 py-3">
                                            <PositionBadge
                                                position={member.position}
                                                label={member.position_label}
                                            />
                                        </td>
                                        <td className="hidden px-4 py-3 sm:table-cell">
                                            <StatusBadge
                                                status={member.status}
                                            />
                                        </td>
                                        <td className="px-4 py-3">
                                            <DuesBadge
                                                status={member.dues_status}
                                                balance={member.dues_balance}
                                                year={currentYear}
                                            />
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </>
                )}
            </div>

            {members.last_page > 1 && (
                <nav className="mt-space-md flex items-center justify-between text-body-sm text-on-surface-variant">
                    <span>
                        {members.from}–{members.to} of {members.total}
                    </span>
                    <div className="flex gap-2">
                        {members.prev_page_url && (
                            <Link
                                href={members.prev_page_url}
                                preserveScroll
                                className="rounded border border-outline-variant px-3 py-1.5 text-label-md text-primary hover:bg-surface-container"
                            >
                                Previous
                            </Link>
                        )}
                        {members.next_page_url && (
                            <Link
                                href={members.next_page_url}
                                preserveScroll
                                className="rounded border border-outline-variant px-3 py-1.5 text-label-md text-primary hover:bg-surface-container"
                            >
                                Next
                            </Link>
                        )}
                    </div>
                </nav>
            )}
        </AdminLayout>
    );
}
