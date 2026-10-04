import { Form, Link, router, usePage } from '@inertiajs/react';
import { pesos } from '@/components/admin/badges';
import { SubmitButton, TextField } from '@/components/admin/form-fields';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { index as clubsIndex } from '@/routes/admin/clubs';
import { destroy, store } from '@/routes/admin/clubs/dues-rates';
import { index as membersIndex } from '@/routes/admin/members';

type Rate = {
    id: number;
    year: number;
    amount: string;
    counts: { paid: number; partial: number; unpaid: number };
};

export default function DuesRates({
    club,
    rates,
    currentYear,
    canManage,
}: {
    club: { id: number; name: string };
    rates: Rate[];
    currentYear: number;
    canManage: boolean;
}) {
    const { auth } = usePage().props;
    const hasCurrentYear = rates.some((rate) => rate.year === currentYear);

    const deleteRate = (rate: Rate) => {
        if (
            !window.confirm(
                `Remove the ${rate.year} dues amount? Any payment will then count as paid for ${rate.year}.`,
            )
        ) {
            return;
        }

        router.delete(destroy.url({ club: club.id, duesRate: rate.id }), {
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout
            title={`${club.name} Dues`}
            description="How much each member owes per year. A year is paid once a member's payments reach this amount."
        >
            <div className="flex flex-col gap-space-lg">
                {canManage && (
                    <Form
                        {...store.form(club.id)}
                        resetOnSuccess
                        options={{ preserveScroll: true }}
                        className="grid grid-cols-1 items-start gap-space-md rounded-lg border border-[#d8dee4] bg-surface-container-lowest p-space-lg shadow-[0_2px_4px_rgba(15,35,71,0.04)] sm:grid-cols-[1fr_1fr_auto]"
                    >
                        {({ errors, processing }) => (
                            <>
                                <TextField
                                    name="year"
                                    label="Year"
                                    type="number"
                                    required
                                    defaultValue={
                                        hasCurrentYear
                                            ? currentYear + 1
                                            : currentYear
                                    }
                                    error={errors.year}
                                />
                                <TextField
                                    name="amount"
                                    label="Dues per member (₱)"
                                    type="number"
                                    step="0.01"
                                    required
                                    error={errors.amount}
                                    help="Saving a year that already has an amount replaces it."
                                />
                                <div className="flex h-full items-start pt-7">
                                    <SubmitButton processing={processing}>
                                        Save amount
                                    </SubmitButton>
                                </div>
                            </>
                        )}
                    </Form>
                )}

                <div className="overflow-x-auto rounded-lg border border-[#d8dee4] bg-surface-container-lowest shadow-[0_2px_4px_rgba(15,35,71,0.04)]">
                    {rates.length === 0 ? (
                        <div className="flex flex-col items-center gap-3 px-6 py-16 text-center">
                            <Icon
                                name="request_quote"
                                className="text-[40px] text-outline"
                            />
                            <p className="max-w-md text-body-md text-on-surface-variant">
                                No dues amounts set yet. Until one is set for a
                                year, any payment counts as paid for that year.
                            </p>
                        </div>
                    ) : (
                        <>
                            <ul className="divide-y divide-[#d8dee4] sm:hidden">
                                {rates.map((rate) => (
                                    <li
                                        key={rate.id}
                                        className="flex items-center gap-3 px-4 py-3"
                                    >
                                        <div className="flex min-w-0 flex-1 flex-col gap-1.5">
                                            <div className="flex items-baseline gap-2">
                                                <span className="font-serif text-title font-bold text-primary">
                                                    {rate.year}
                                                </span>
                                                <span className="text-label-md">
                                                    {pesos.format(
                                                        Number(rate.amount),
                                                    )}
                                                </span>
                                            </div>
                                            <div className="flex flex-wrap gap-1.5 text-label-sm">
                                                <span className="rounded bg-primary-container px-2 py-0.5 text-secondary-fixed">
                                                    {rate.counts.paid} paid
                                                </span>
                                                <span className="rounded bg-amber-50 px-2 py-0.5 text-amber-800">
                                                    {rate.counts.partial}{' '}
                                                    partial
                                                </span>
                                                <span className="rounded bg-red-50 px-2 py-0.5 text-red-700">
                                                    {rate.counts.unpaid} unpaid
                                                </span>
                                            </div>
                                        </div>
                                        {canManage && (
                                            <button
                                                type="button"
                                                aria-label={`Remove ${rate.year} amount`}
                                                onClick={() => deleteRate(rate)}
                                                className="flex h-10 w-10 shrink-0 items-center justify-center rounded text-red-700 hover:bg-red-50"
                                            >
                                                <Icon
                                                    name="delete"
                                                    className="text-[20px]"
                                                />
                                            </button>
                                        )}
                                    </li>
                                ))}
                            </ul>
                            <table className="hidden w-full text-left text-body-sm sm:table">
                                <thead className="border-b border-[#d8dee4] bg-surface-container-low text-label-sm tracking-wider text-on-surface-variant uppercase">
                                    <tr>
                                        <th className="px-4 py-3">Year</th>
                                        <th className="px-4 py-3">Amount</th>
                                        <th className="px-4 py-3">Paid</th>
                                        <th className="px-4 py-3">Partial</th>
                                        <th className="px-4 py-3">Unpaid</th>
                                        {canManage && (
                                            <th className="px-4 py-3">
                                                <span className="sr-only">
                                                    Actions
                                                </span>
                                            </th>
                                        )}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-[#d8dee4]">
                                    {rates.map((rate) => (
                                        <tr key={rate.id}>
                                            <td className="px-4 py-3 font-serif text-title font-bold text-primary">
                                                {rate.year}
                                            </td>
                                            <td className="px-4 py-3 text-label-md">
                                                {pesos.format(
                                                    Number(rate.amount),
                                                )}
                                            </td>
                                            <td className="px-4 py-3">
                                                {rate.counts.paid}
                                            </td>
                                            <td className="px-4 py-3 text-amber-800">
                                                {rate.counts.partial}
                                            </td>
                                            <td className="px-4 py-3">
                                                {rate.year === currentYear &&
                                                rate.counts.unpaid > 0 ? (
                                                    <Link
                                                        href={membersIndex.url({
                                                            query: {
                                                                dues: 'unpaid',
                                                                ...(auth.can
                                                                    .manageClubs
                                                                    ? {
                                                                          club: club.id,
                                                                      }
                                                                    : {}),
                                                            },
                                                        })}
                                                        className="font-semibold text-red-700 hover:underline"
                                                    >
                                                        {rate.counts.unpaid}
                                                    </Link>
                                                ) : (
                                                    rate.counts.unpaid
                                                )}
                                            </td>
                                            {canManage && (
                                                <td className="px-4 py-3 text-right">
                                                    <button
                                                        type="button"
                                                        aria-label={`Remove ${rate.year} amount`}
                                                        onClick={() =>
                                                            deleteRate(rate)
                                                        }
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded text-red-700 hover:bg-red-50"
                                                    >
                                                        <Icon
                                                            name="delete"
                                                            className="text-[18px]"
                                                        />
                                                    </button>
                                                </td>
                                            )}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </>
                    )}
                </div>

                {auth.can.manageClubs && (
                    <Link
                        href={clubsIndex.url()}
                        className="self-start text-label-md text-primary hover:underline"
                    >
                        ← Back to clubs
                    </Link>
                )}
            </div>
        </AdminLayout>
    );
}
