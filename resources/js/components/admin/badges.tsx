import { Icon } from '@/components/icon';
import { cn } from '@/lib/utils';

const statusClasses: Record<string, string> = {
    active: 'bg-emerald-50 text-emerald-800 border-emerald-200',
    inactive:
        'bg-surface-container text-on-surface-variant border-outline-variant',
    suspended: 'bg-amber-50 text-amber-800 border-amber-300',
    deceased: 'bg-slate-100 text-slate-600 border-slate-300',
};

export function StatusBadge({
    status,
    label,
}: {
    status: string;
    label?: string;
}) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded border px-2 py-0.5 text-label-sm capitalize',
                statusClasses[status] ?? statusClasses.inactive,
            )}
        >
            {label ?? status}
        </span>
    );
}

/**
 * Officers get the gold "honor chip"; regular members a quiet neutral tag.
 */
export function PositionBadge({
    position,
    label,
}: {
    position: string;
    label: string;
}) {
    const isOfficer = position !== 'member';

    return (
        <span
            className={cn(
                'inline-flex items-center gap-1 rounded border px-2 py-0.5 text-label-sm whitespace-nowrap',
                isOfficer
                    ? 'border-secondary-fixed-dim bg-[#fffbeb] text-[#b45309]'
                    : 'border-transparent bg-surface-container text-on-surface-variant',
            )}
        >
            {isOfficer && <Icon name="military_tech" className="text-[14px]" />}
            {isOfficer ? label.replace(/^Club /, '') : label}
        </span>
    );
}

export type DuesStatus = 'paid' | 'partial' | 'unpaid';

const duesStyles: Record<DuesStatus, { className: string; icon: string }> = {
    paid: {
        className: 'bg-primary-container text-secondary-fixed',
        icon: 'check_circle',
    },
    partial: {
        className: 'bg-amber-50 text-amber-800',
        icon: 'hourglass_bottom',
    },
    unpaid: { className: 'bg-red-50 text-red-700', icon: 'schedule' },
};

export const pesos = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
});

/**
 * A year's dues standing; partial payments also show what is still owed.
 */
export function DuesBadge({
    status,
    year,
    balance,
}: {
    status: DuesStatus;
    year?: number;
    balance?: string;
}) {
    const style = duesStyles[status];

    return (
        <span
            className={cn(
                'inline-flex items-center gap-1 rounded px-2 py-0.5 text-label-sm whitespace-nowrap',
                style.className,
            )}
        >
            <Icon name={style.icon} className="text-[14px]" />
            {year} {status}
            {status === 'partial' && balance && (
                <span className="font-normal">
                    · {pesos.format(Number(balance))} due
                </span>
            )}
        </span>
    );
}

export function MemberAvatar({
    name,
    photoUrl,
    size = 'md',
}: {
    name: string;
    photoUrl: string | null;
    size?: 'sm' | 'md' | 'lg';
}) {
    const initials = name
        .split(' ')
        .filter(Boolean)
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

    return (
        <div
            className={cn(
                'flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary-container font-serif font-bold text-secondary-fixed',
                {
                    sm: 'h-7 w-7 text-[0.65rem]',
                    md: 'h-10 w-10 text-body-sm',
                    lg: 'h-24 w-24 text-headline-sm ring-2 ring-secondary-fixed-dim',
                }[size],
            )}
        >
            {photoUrl ? (
                <img
                    src={photoUrl}
                    alt=""
                    className="h-full w-full object-cover"
                />
            ) : (
                initials
            )}
        </div>
    );
}
