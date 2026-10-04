import { Link, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { Icon } from '@/components/icon';
import { InstallAppButton } from '@/components/install-app-button';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import { logout } from '@/routes/admin';
import { edit as accountEdit } from '@/routes/admin/account';
import { show as memberShow } from '@/routes/admin/members';

export function roleLabel(role?: string, position?: string | null): string {
    return role === 'district_admin'
        ? 'District Administrator'
        : (position ?? 'Member');
}

function initialsOf(name: string): string {
    return name
        .split(' ')
        .filter(Boolean)
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

/**
 * The signed-in user's avatar with a menu for their own pages and logging out.
 *
 * `variant="sidebar"` renders a full-width card that opens upwards (desktop
 * sidebar); `variant="compact"` renders just the avatar and opens downwards
 * (mobile top bar).
 */
export function UserMenu({ variant }: { variant: 'sidebar' | 'compact' }) {
    const { auth } = usePage().props;
    const [isOpen, setIsOpen] = useState(false);
    const containerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!isOpen) {
            return;
        }

        const closeOnOutsideClick = (event: MouseEvent) => {
            if (!containerRef.current?.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };
        const closeOnEscape = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setIsOpen(false);
            }
        };

        document.addEventListener('mousedown', closeOnOutsideClick);
        document.addEventListener('keydown', closeOnEscape);

        return () => {
            document.removeEventListener('mousedown', closeOnOutsideClick);
            document.removeEventListener('keydown', closeOnEscape);
        };
    }, [isOpen]);

    if (!auth.user) {
        return null;
    }

    const role = roleLabel(auth.user.role, auth.user.position);
    const avatar = (
        <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-secondary-container font-serif text-body-sm font-bold text-on-secondary-fixed ring-2 ring-secondary-fixed-dim/40">
            {initialsOf(auth.user.name)}
        </span>
    );

    const menuItemClassName =
        'flex w-full items-center gap-3 px-4 py-2.5 text-left text-label-md text-on-surface transition-colors hover:bg-surface-container-low';

    return (
        <div ref={containerRef} className="relative">
            <button
                type="button"
                onClick={() => setIsOpen((open) => !open)}
                aria-expanded={isOpen}
                aria-haspopup="menu"
                aria-label="Account menu"
                className={cn(
                    'flex items-center gap-3 transition-colors',
                    variant === 'sidebar'
                        ? 'w-full rounded-lg bg-white/5 px-3 py-2.5 text-left hover:bg-white/10'
                        : 'rounded-full p-0.5 hover:ring-2 hover:ring-primary-fixed-dim',
                    isOpen && variant === 'sidebar' && 'bg-white/10',
                )}
            >
                {avatar}
                {variant === 'sidebar' && (
                    <>
                        <span className="flex min-w-0 flex-1 flex-col">
                            <span className="truncate text-label-md text-on-primary">
                                {auth.user.name}
                            </span>
                            <span className="truncate text-body-sm text-on-primary-container">
                                {role}
                            </span>
                        </span>
                        <Icon
                            name="unfold_more"
                            className="text-[20px] text-on-primary-container"
                        />
                    </>
                )}
            </button>

            {isOpen && (
                <div
                    role="menu"
                    className={cn(
                        'absolute z-50 w-64 overflow-hidden rounded-lg border border-[#d8dee4] bg-surface-container-lowest py-1 shadow-[0_16px_32px_rgba(15,35,71,0.2)]',
                        variant === 'sidebar'
                            ? 'bottom-full left-0 mb-2'
                            : 'top-full right-0 mt-2',
                    )}
                >
                    <div className="border-b border-surface-container px-4 py-3">
                        <p className="truncate text-label-md text-primary">
                            {auth.user.name}
                        </p>
                        <p className="truncate text-body-sm text-on-surface-variant">
                            {auth.user.email}
                        </p>
                        <span className="mt-1.5 inline-flex rounded border border-secondary-fixed-dim bg-[#fffbeb] px-2 py-0.5 text-label-sm text-[#b45309]">
                            {role}
                        </span>
                    </div>

                    {auth.memberId && (
                        <Link
                            href={memberShow.url(auth.memberId)}
                            role="menuitem"
                            onClick={() => setIsOpen(false)}
                            className={menuItemClassName}
                        >
                            <Icon
                                name="badge"
                                className="text-[20px] text-primary"
                            />
                            My Profile & Dues
                        </Link>
                    )}
                    <Link
                        href={accountEdit.url()}
                        role="menuitem"
                        onClick={() => setIsOpen(false)}
                        className={menuItemClassName}
                    >
                        <Icon
                            name="manage_accounts"
                            className="text-[20px] text-primary"
                        />
                        Login & Password
                    </Link>
                    <InstallAppButton variant="menu-item" />
                    <a
                        href={home.url()}
                        target="_blank"
                        rel="noreferrer"
                        role="menuitem"
                        className={menuItemClassName}
                    >
                        <Icon
                            name="open_in_new"
                            className="text-[20px] text-primary"
                        />
                        View website
                    </a>

                    <div className="mt-1 border-t border-surface-container pt-1">
                        <Link
                            href={logout()}
                            as="button"
                            role="menuitem"
                            className={cn(
                                menuItemClassName,
                                'text-red-700 hover:bg-red-50',
                            )}
                        >
                            <Icon name="logout" className="text-[20px]" />
                            Log out
                        </Link>
                    </div>
                </div>
            )}
        </div>
    );
}
