import { Head, Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import { roleLabel, UserMenu } from '@/components/admin/user-menu';
import { Icon } from '@/components/icon';
import { cn } from '@/lib/utils';
import { dashboard, forms, reports } from '@/routes/admin';
import { index as clubsIndex } from '@/routes/admin/clubs';
import { index as documentsIndex } from '@/routes/admin/clubs/documents';
import { index as duesRatesIndex } from '@/routes/admin/clubs/dues-rates';
import { index as reportsIndex } from '@/routes/admin/clubs/reports';
import { index as fundAllocationsIndex } from '@/routes/admin/fund-allocations';
import {
    index as membersIndex,
    show as memberShow,
} from '@/routes/admin/members';
import { index as membershipStepsIndex } from '@/routes/admin/membership-steps';
import { index as missionsIndex } from '@/routes/admin/missions';
import { index as pillarsIndex } from '@/routes/admin/pillars';
import { edit as settingsEdit } from '@/routes/admin/settings';
import { index as statsIndex } from '@/routes/admin/stats';
import type { Auth } from '@/types';

type NavigationItem = { label: string; icon: string; href: string };
type NavigationGroup = { label: string; items: NavigationItem[] };

/**
 * Build the sidebar for what the signed-in user's role is allowed to reach.
 * Personal pages (profile, password) live in the account menu instead.
 */
function buildNavigation(auth: Auth): NavigationGroup[] {
    const groups: NavigationGroup[] = [];
    const membershipItems: NavigationItem[] = [];

    if (auth.can.viewMembers) {
        membershipItems.push({
            label: auth.can.manageClubs ? 'Members' : 'Club Members',
            icon: 'groups',
            href: membersIndex.url(),
        });
    }

    if (auth.can.manageClubs) {
        membershipItems.push({
            label: 'Clubs & Dues',
            icon: 'diversity_3',
            href: clubsIndex.url(),
        });
    } else if (auth.officerClubId) {
        membershipItems.push(
            {
                label: 'Dues Rates',
                icon: 'request_quote',
                href: duesRatesIndex.url(auth.officerClubId),
            },
            {
                label: 'Forms',
                icon: 'description',
                href: documentsIndex.url(auth.officerClubId),
            },
            {
                label: 'Reports',
                icon: 'monitoring',
                href: reportsIndex.url(auth.officerClubId),
            },
        );
    }

    if (auth.can.manageClubs) {
        membershipItems.push(
            {
                label: 'Forms',
                icon: 'description',
                href: forms.url(),
            },
            {
                label: 'Reports',
                icon: 'monitoring',
                href: reports.url(),
            },
        );
    }

    if (!auth.can.viewMembers && auth.memberId) {
        membershipItems.push({
            label: 'My Profile & Dues',
            icon: 'badge',
            href: memberShow.url(auth.memberId),
        });
    }

    if (membershipItems.length > 0) {
        groups.push({ label: 'Membership', items: membershipItems });
    }

    if (auth.can.manageSite) {
        groups.push({
            label: 'Website',
            items: [
                {
                    label: 'Site Settings',
                    icon: 'tune',
                    href: settingsEdit.url(),
                },
                {
                    label: 'Hero Stats',
                    icon: 'monitoring',
                    href: statsIndex.url(),
                },
                {
                    label: 'Four Pillars',
                    icon: 'account_balance',
                    href: pillarsIndex.url(),
                },
                {
                    label: 'Missions',
                    icon: 'volunteer_activism',
                    href: missionsIndex.url(),
                },
                {
                    label: 'Fund Allocation',
                    icon: 'pie_chart',
                    href: fundAllocationsIndex.url(),
                },
                {
                    label: 'Membership Steps',
                    icon: 'stairs',
                    href: membershipStepsIndex.url(),
                },
            ],
        });
    }

    return groups;
}

function Brand({ logo, subtitle }: { logo: string | null; subtitle: string }) {
    return (
        <Link href={dashboard()} className="flex min-w-0 items-center gap-3">
            {logo ? (
                <img
                    src={logo}
                    alt=""
                    className="h-10 w-10 shrink-0 object-contain drop-shadow"
                />
            ) : (
                <Icon
                    name="shield"
                    className="text-[28px] text-secondary-fixed"
                />
            )}
            <span className="flex min-w-0 flex-col">
                <span className="truncate font-serif text-title leading-tight font-bold text-on-primary">
                    District III
                </span>
                <span className="truncate text-label-sm tracking-widest text-secondary-fixed uppercase">
                    {subtitle}
                </span>
            </span>
        </Link>
    );
}

export default function AdminLayout({
    title,
    description,
    actions,
    children,
}: {
    title: string;
    description?: string;
    actions?: ReactNode;
    children: ReactNode;
}) {
    const { url, flash, props } = usePage();
    const { auth, siteLogo } = props;
    const [isSidebarOpen, setIsSidebarOpen] = useState(false);
    const [visibleMessage, setVisibleMessage] = useState<string | null>(null);

    useEffect(() => {
        if (!flash.success) {
            return;
        }

        setVisibleMessage(flash.success);
        const timeout = window.setTimeout(() => setVisibleMessage(null), 4000);

        return () => window.clearTimeout(timeout);
    }, [flash]);

    useEffect(() => {
        if (!isSidebarOpen) {
            return;
        }

        const closeOnEscape = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setIsSidebarOpen(false);
            }
        };

        document.addEventListener('keydown', closeOnEscape);
        document.body.style.overflow = 'hidden';

        return () => {
            document.removeEventListener('keydown', closeOnEscape);
            document.body.style.overflow = '';
        };
    }, [isSidebarOpen]);

    const navigationGroups = buildNavigation(auth);
    const allHrefs = navigationGroups.flatMap((group) =>
        group.items.map((item) => item.href),
    );
    const path = url.split('?')[0];
    const subtitle = roleLabel(auth.user?.role, auth.user?.position);

    /**
     * Highlight the most specific sidebar link that matches the current page.
     */
    const isActive = (href: string) => {
        const matches = (candidate: string) =>
            path === candidate || path.startsWith(`${candidate}/`);
        const bestMatch = allHrefs
            .filter(matches)
            .sort((first, second) => second.length - first.length)[0];

        return bestMatch === href;
    };

    return (
        <>
            <Head title={`${title} · Admin`} />
            <div className="min-h-screen bg-background font-sans text-on-surface lg:flex">
                {/* Mobile top bar */}
                <div className="sticky top-0 z-30 flex h-16 items-center gap-3 bg-primary px-3 text-on-primary shadow-md lg:hidden">
                    <button
                        type="button"
                        aria-label="Open menu"
                        aria-expanded={isSidebarOpen}
                        onClick={() => setIsSidebarOpen(true)}
                        className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg hover:bg-white/10"
                    >
                        <Icon name="menu" className="text-[26px]" />
                    </button>
                    <div className="min-w-0 flex-1">
                        <Brand logo={siteLogo} subtitle={subtitle} />
                    </div>
                    <UserMenu variant="compact" />
                </div>

                {/* Sidebar: drawer on mobile, fixed column on desktop */}
                <aside
                    className={cn(
                        'fixed inset-y-0 left-0 z-50 flex w-[85vw] max-w-72 flex-col bg-primary text-on-primary shadow-2xl transition-transform duration-200 lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:w-72 lg:max-w-none lg:translate-x-0 lg:shadow-none',
                        isSidebarOpen ? 'translate-x-0' : '-translate-x-full',
                    )}
                >
                    <div className="flex h-16 items-center justify-between gap-2 border-b border-white/10 px-4 lg:h-20 lg:px-5">
                        <Brand logo={siteLogo} subtitle={subtitle} />
                        <button
                            type="button"
                            aria-label="Close menu"
                            onClick={() => setIsSidebarOpen(false)}
                            className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg hover:bg-white/10 lg:hidden"
                        >
                            <Icon name="close" className="text-[24px]" />
                        </button>
                    </div>

                    <nav className="flex flex-1 flex-col gap-6 overflow-y-auto px-3 py-5">
                        {navigationGroups.map((group) => (
                            <div
                                key={group.label}
                                className="flex flex-col gap-0.5"
                            >
                                <span className="px-3 pb-1.5 text-label-sm tracking-widest text-on-primary-container uppercase">
                                    {group.label}
                                </span>
                                {group.items.map((item) => {
                                    const active = isActive(item.href);

                                    return (
                                        <Link
                                            key={item.href}
                                            href={item.href}
                                            onClick={() =>
                                                setIsSidebarOpen(false)
                                            }
                                            aria-current={
                                                active ? 'page' : undefined
                                            }
                                            className={cn(
                                                'relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-label-md transition-colors',
                                                active
                                                    ? 'bg-primary-container text-secondary-fixed before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-secondary-fixed-dim'
                                                    : 'text-primary-fixed-dim hover:bg-white/5 hover:text-on-primary',
                                            )}
                                        >
                                            <Icon
                                                name={item.icon}
                                                className="text-[20px]"
                                            />
                                            {item.label}
                                        </Link>
                                    );
                                })}
                            </div>
                        ))}
                    </nav>

                    <div className="hidden border-t border-white/10 p-3 lg:block">
                        <UserMenu variant="sidebar" />
                    </div>
                </aside>

                {isSidebarOpen && (
                    <button
                        type="button"
                        aria-label="Close menu"
                        onClick={() => setIsSidebarOpen(false)}
                        className="fixed inset-0 z-40 bg-[#0a1424]/65 backdrop-blur-[2px] lg:hidden"
                    />
                )}

                <div className="flex min-w-0 flex-1 flex-col">
                    <header className="border-b border-surface-container-high bg-surface/95 px-4 py-4 backdrop-blur-md md:px-margin lg:sticky lg:top-0 lg:z-20 lg:flex lg:h-20 lg:items-center lg:py-0">
                        <div className="mx-auto flex w-full max-w-5xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex min-w-0 flex-col">
                                <h1 className="font-serif text-title font-bold text-primary sm:truncate sm:text-headline-sm">
                                    {title}
                                </h1>
                                {description && (
                                    <p className="text-body-sm text-on-surface-variant sm:truncate">
                                        {description}
                                    </p>
                                )}
                            </div>
                            {actions && (
                                <div className="flex w-full shrink-0 items-center gap-2 sm:w-auto [&>*]:flex-1 sm:[&>*]:flex-none">
                                    {actions}
                                </div>
                            )}
                        </div>
                    </header>

                    <main className="mx-auto w-full max-w-5xl flex-1 px-4 py-space-lg md:px-margin md:py-space-xl">
                        {children}
                    </main>
                </div>
            </div>

            {visibleMessage && (
                <div
                    role="status"
                    className="fixed inset-x-4 bottom-4 z-50 flex items-center gap-3 rounded-lg border-t-[3px] border-secondary-fixed-dim bg-primary-container px-5 py-3 text-label-md text-on-primary shadow-[0_16px_32px_rgba(15,35,71,0.2)] sm:inset-x-auto sm:right-4"
                >
                    <Icon
                        name="check_circle"
                        className="shrink-0 text-[20px] text-secondary-fixed"
                    />
                    <span className="flex-1">{visibleMessage}</span>
                    <button
                        type="button"
                        aria-label="Dismiss"
                        onClick={() => setVisibleMessage(null)}
                        className="flex h-7 w-7 items-center justify-center rounded text-primary-fixed-dim hover:bg-white/10 hover:text-on-primary"
                    >
                        <Icon name="close" className="text-[18px]" />
                    </button>
                </div>
            )}
        </>
    );
}
