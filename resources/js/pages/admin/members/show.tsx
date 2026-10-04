import { Form, Link, router } from '@inertiajs/react';
import {
    DuesBadge,
    type DuesStatus,
    MemberAvatar,
    pesos,
    PositionBadge,
    StatusBadge,
} from '@/components/admin/badges';
import { SubmitButton, TextField } from '@/components/admin/form-fields';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { destroy as destroyPayment } from '@/routes/admin/dues';
import { destroy, edit, index } from '@/routes/admin/members';
import { store as storePayment } from '@/routes/admin/members/dues';

type MemberProfile = {
    id: number;
    full_name: string;
    photo_url: string | null;
    member_number: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
    birthday: string | null;
    inducted_at: string | null;
    club: string;
    position: string;
    position_label: string;
    status: string;
    status_label: string;
    login_email: string | null;
};

type Payment = {
    id: number;
    year: number;
    amount: string;
    paid_at: string;
    reference: string | null;
    notes: string | null;
    recorded_by: string | null;
};

type DuesYear = {
    year: number;
    rate: string | null;
    paid: string;
    dues_status: DuesStatus;
    dues_balance: string;
};

const longDate = (value: string | null) =>
    value
        ? new Date(`${value}T00:00:00`).toLocaleDateString('en-PH', {
              year: 'numeric',
              month: 'long',
              day: 'numeric',
          })
        : '—';

function Detail({ label, value }: { label: string; value: string | null }) {
    return (
        <div className="flex flex-col gap-0.5">
            <dt className="text-label-sm tracking-wider text-on-surface-variant uppercase">
                {label}
            </dt>
            <dd className="text-body-md break-words text-on-surface">
                {value || '—'}
            </dd>
        </div>
    );
}

export default function MemberShow({
    member,
    payments,
    duesSummary,
    currentYearBalance,
    currentYear,
    can,
}: {
    member: MemberProfile;
    payments: Payment[];
    duesSummary: DuesYear[];
    currentYearBalance: string | null;
    currentYear: number;
    can: { update: boolean; manageDues: boolean; delete: boolean };
}) {
    const thisYear = duesSummary.find((entry) => entry.year === currentYear);
    const suggestedAmount =
        currentYearBalance && Number(currentYearBalance) > 0
            ? currentYearBalance
            : undefined;
    const today = new Date().toISOString().slice(0, 10);

    const deleteMember = () => {
        if (
            !window.confirm(
                `Remove ${member.full_name}? Their dues history and login will be deleted too.`,
            )
        ) {
            return;
        }

        router.delete(destroy.url(member.id));
    };

    const deletePayment = (payment: Payment) => {
        if (
            !window.confirm(
                `Remove this ${payment.year} payment of ${pesos.format(Number(payment.amount))}?`,
            )
        ) {
            return;
        }

        router.delete(destroyPayment.url(payment.id), { preserveScroll: true });
    };

    return (
        <AdminLayout
            title={member.full_name}
            description={`${member.position_label} · ${member.club}`}
            actions={
                <div className="flex shrink-0 items-center gap-2">
                    {can.delete && (
                        <button
                            type="button"
                            onClick={deleteMember}
                            aria-label="Remove member"
                            className="flex h-10 w-10 items-center justify-center rounded text-red-700 hover:bg-red-50"
                        >
                            <Icon
                                name="person_remove"
                                className="text-[22px]"
                            />
                        </button>
                    )}
                    {can.update && (
                        <Link
                            href={edit.url(member.id)}
                            className="inline-flex flex-1 items-center justify-center gap-2 rounded bg-primary-container px-3 py-2.5 text-label-md text-on-primary transition-colors hover:bg-[#1e3a8a] sm:flex-none sm:px-4"
                        >
                            <Icon name="edit" className="text-[20px]" />
                            <span>Edit</span>
                        </Link>
                    )}
                </div>
            }
        >
            <div className="flex flex-col gap-space-lg">
                <section className="overflow-hidden rounded-lg border border-t-[3px] border-[#d8dee4] border-t-secondary-fixed-dim bg-surface-container-lowest shadow-[0_2px_4px_rgba(15,35,71,0.04)]">
                    <div className="flex flex-col items-center gap-space-md p-space-lg text-center sm:flex-row sm:text-left">
                        <MemberAvatar
                            name={member.full_name}
                            photoUrl={member.photo_url}
                            size="lg"
                        />
                        <div className="flex flex-1 flex-col items-center gap-2 sm:items-start">
                            <h2 className="font-serif text-headline-sm text-primary">
                                {member.full_name}
                            </h2>
                            <div className="flex flex-wrap justify-center gap-2 sm:justify-start">
                                <PositionBadge
                                    position={member.position}
                                    label={member.position_label}
                                />
                                <StatusBadge
                                    status={member.status}
                                    label={member.status_label}
                                />
                                <DuesBadge
                                    status={thisYear?.dues_status ?? 'unpaid'}
                                    balance={thisYear?.dues_balance}
                                    year={currentYear}
                                />
                            </div>
                            <span className="font-mono text-label-md text-on-surface-variant">
                                {member.member_number ?? 'No member number'}
                            </span>
                        </div>
                    </div>
                    <dl className="grid grid-cols-1 gap-space-md border-t border-surface-container p-space-lg sm:grid-cols-2 lg:grid-cols-3">
                        <Detail label="Club" value={member.club} />
                        <Detail
                            label="Date inducted"
                            value={longDate(member.inducted_at)}
                        />
                        <Detail
                            label="Birthday"
                            value={longDate(member.birthday)}
                        />
                        <Detail label="Email" value={member.email} />
                        <Detail label="Mobile" value={member.phone} />
                        <Detail
                            label="Login"
                            value={
                                member.login_email
                                    ? `Can sign in as ${member.login_email}`
                                    : 'No login'
                            }
                        />
                        <div className="sm:col-span-2 lg:col-span-3">
                            <Detail label="Address" value={member.address} />
                        </div>
                    </dl>
                </section>

                <section className="rounded-lg border border-[#d8dee4] bg-surface-container-lowest shadow-[0_2px_4px_rgba(15,35,71,0.04)]">
                    <header className="flex items-center gap-3 border-b border-surface-container px-space-lg py-space-md">
                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-surface-container text-primary">
                            <Icon name="payments" className="text-[22px]" />
                        </div>
                        <div>
                            <h2 className="font-serif text-title font-bold text-primary">
                                Dues & payments
                            </h2>
                            <p className="text-body-sm text-on-surface-variant">
                                Each year is checked against the club's dues
                                amount. Years without an amount count as paid
                                once any payment is recorded.
                            </p>
                        </div>
                    </header>

                    {duesSummary.length > 0 && (
                        <div className="overflow-x-auto border-b border-surface-container">
                            <table className="w-full text-left text-body-sm">
                                <thead className="text-label-sm tracking-wider text-on-surface-variant uppercase">
                                    <tr>
                                        <th className="px-3 pt-3 pb-2 sm:px-4">
                                            Year
                                        </th>
                                        <th className="px-3 pt-3 pb-2 sm:px-4">
                                            Due
                                        </th>
                                        <th className="px-3 pt-3 pb-2 sm:px-4">
                                            Paid
                                        </th>
                                        <th className="px-3 pt-3 pb-2 sm:px-4">
                                            Balance
                                        </th>
                                        <th className="px-3 pt-3 pb-2 sm:px-4">
                                            <span className="sr-only">
                                                Status
                                            </span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-surface-container">
                                    {duesSummary.map((entry) => (
                                        <tr key={entry.year}>
                                            <td className="px-3 py-2.5 font-serif font-bold text-primary sm:px-4">
                                                {entry.year}
                                            </td>
                                            <td className="px-3 py-2.5 sm:px-4">
                                                {entry.rate ? (
                                                    pesos.format(
                                                        Number(entry.rate),
                                                    )
                                                ) : (
                                                    <span className="text-outline">
                                                        Not set
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-3 py-2.5 sm:px-4">
                                                {pesos.format(
                                                    Number(entry.paid),
                                                )}
                                            </td>
                                            <td className="px-3 py-2.5 font-semibold sm:px-4">
                                                {Number(entry.dues_balance) >
                                                0 ? (
                                                    <span className="text-red-700">
                                                        {pesos.format(
                                                            Number(
                                                                entry.dues_balance,
                                                            ),
                                                        )}
                                                    </span>
                                                ) : (
                                                    <span className="text-on-surface-variant">
                                                        —
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-3 py-2.5 text-right sm:px-4">
                                                <DuesBadge
                                                    status={entry.dues_status}
                                                />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {can.manageDues && (
                        <Form
                            {...storePayment.form(member.id)}
                            resetOnSuccess
                            options={{ preserveScroll: true }}
                            className="grid grid-cols-1 items-start gap-space-md border-b border-surface-container bg-surface-container-low p-space-lg sm:grid-cols-2 lg:grid-cols-4"
                        >
                            {({ errors, processing }) => (
                                <>
                                    <TextField
                                        name="year"
                                        label="Dues year"
                                        type="number"
                                        required
                                        defaultValue={currentYear}
                                        error={errors.year}
                                    />
                                    <TextField
                                        name="amount"
                                        label="Amount (₱)"
                                        type="number"
                                        step="0.01"
                                        required
                                        defaultValue={suggestedAmount}
                                        help={
                                            suggestedAmount
                                                ? `${currentYear} balance`
                                                : undefined
                                        }
                                        error={errors.amount}
                                    />
                                    <TextField
                                        name="paid_at"
                                        label="Date paid"
                                        type="date"
                                        required
                                        defaultValue={today}
                                        error={errors.paid_at}
                                    />
                                    <TextField
                                        name="reference"
                                        label="OR / reference no."
                                        error={errors.reference}
                                    />
                                    <TextField
                                        name="notes"
                                        label="Notes"
                                        error={errors.notes}
                                        className="sm:col-span-2 lg:col-span-3"
                                    />
                                    <div className="flex h-full items-end">
                                        <SubmitButton processing={processing}>
                                            Record payment
                                        </SubmitButton>
                                    </div>
                                </>
                            )}
                        </Form>
                    )}

                    {payments.length === 0 ? (
                        <p className="px-space-lg py-space-xl text-center text-body-md text-on-surface-variant">
                            No dues payments recorded yet.
                        </p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-body-sm">
                                <thead className="border-b border-[#d8dee4] text-label-sm tracking-wider text-on-surface-variant uppercase">
                                    <tr>
                                        <th className="px-3 py-3 sm:px-4">
                                            Year
                                        </th>
                                        <th className="px-3 py-3 sm:px-4">
                                            Amount
                                        </th>
                                        <th className="px-3 py-3 sm:px-4">
                                            Paid on
                                        </th>
                                        <th className="hidden px-3 py-3 sm:px-4 md:table-cell">
                                            Reference
                                        </th>
                                        <th className="hidden px-3 py-3 sm:px-4 lg:table-cell">
                                            Recorded by
                                        </th>
                                        {can.manageDues && (
                                            <th className="px-3 py-3 sm:px-4">
                                                <span className="sr-only">
                                                    Actions
                                                </span>
                                            </th>
                                        )}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-[#d8dee4]">
                                    {payments.map((payment) => (
                                        <tr key={payment.id}>
                                            <td className="px-3 py-3 font-serif font-bold text-primary sm:px-4">
                                                {payment.year}
                                            </td>
                                            <td className="px-3 py-3 sm:px-4">
                                                {pesos.format(
                                                    Number(payment.amount),
                                                )}
                                            </td>
                                            <td className="px-3 py-3 sm:px-4">
                                                {longDate(payment.paid_at)}
                                            </td>
                                            <td className="hidden px-3 py-3 sm:px-4 md:table-cell">
                                                {payment.reference ?? '—'}
                                                {payment.notes && (
                                                    <span className="block text-on-surface-variant">
                                                        {payment.notes}
                                                    </span>
                                                )}
                                            </td>
                                            <td className="hidden px-3 py-3 text-on-surface-variant sm:px-4 lg:table-cell">
                                                {payment.recorded_by ?? '—'}
                                            </td>
                                            {can.manageDues && (
                                                <td className="px-3 py-3 text-right sm:px-4">
                                                    <button
                                                        type="button"
                                                        aria-label="Remove payment"
                                                        onClick={() =>
                                                            deletePayment(
                                                                payment,
                                                            )
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
                        </div>
                    )}
                </section>

                {can.update && (
                    <Link
                        href={index.url()}
                        className="self-start text-label-md text-primary hover:underline"
                    >
                        ← Back to members
                    </Link>
                )}
            </div>
        </AdminLayout>
    );
}
