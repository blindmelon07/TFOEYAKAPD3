import { Head, Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import { Icon } from '@/components/icon';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import { logout } from '@/routes/admin';
import { edit as accountEdit } from '@/routes/admin/account';
import { index as fundAllocationsIndex } from '@/routes/admin/fund-allocations';
import { index as membershipStepsIndex } from '@/routes/admin/membership-steps';
import { index as missionsIndex } from '@/routes/admin/missions';
import { index as pillarsIndex } from '@/routes/admin/pillars';
import { edit as settingsEdit } from '@/routes/admin/settings';
import { index as statsIndex } from '@/routes/admin/stats';

const navigationGroups = [
    {
        label: 'Landing page',
        items: [
            { label: 'Site Settings', icon: 'tune', href: settingsEdit.url() },
            { label: 'Hero Stats', icon: 'monitoring', href: statsIndex.url() },
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
    },
    {
        label: 'Administrator',
        items: [
            {
                label: 'My Account',
                icon: 'manage_accounts',
                href: accountEdit.url(),
            },
        ],
    },
];

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

    const isActive = (href: string) =>
        url === href || url.startsWith(`${href}/`);

    return (
        <>
            <Head title={`${title} · Admin`} />
            <div className="min-h-screen bg-background font-sans text-on-surface lg:flex">
                <aside
                    className={cn(
                        'fixed inset-y-0 left-0 z-40 flex w-72 flex-col bg-primary text-on-primary transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0',
                        isSidebarOpen ? 'translate-x-0' : '-translate-x-full',
                    )}
                >
                    <div className="flex items-center gap-3 border-b border-white/10 px-6 py-5">
                        {siteLogo ? (
                            <img
                                src={siteLogo}
                                alt="District logo"
                                className="h-12 w-12 shrink-0 object-contain drop-shadow"
                            />
                        ) : (
                            <Icon
                                name="shield"
                                className="text-[28px] text-secondary-fixed"
                            />
                        )}
                        <div className="flex flex-col">
                            <span className="font-serif text-title font-bold">
                                District Admin
                            </span>
                            <span className="text-label-sm tracking-widest text-secondary-fixed uppercase">
                                Content Manager
                            </span>
                        </div>
                    </div>

                    <nav className="flex flex-1 flex-col gap-6 overflow-y-auto px-4 py-6">
                        {navigationGroups.map((group) => (
                            <div
                                key={group.label}
                                className="flex flex-col gap-1"
                            >
                                <span className="px-3 pb-1 text-label-sm tracking-widest text-on-primary-container uppercase">
                                    {group.label}
                                </span>
                                {group.items.map((item) => (
                                    <Link
                                        key={item.href}
                                        href={item.href}
                                        onClick={() => setIsSidebarOpen(false)}
                                        className={cn(
                                            'flex items-center gap-3 rounded px-3 py-2 text-label-md transition-colors',
                                            isActive(item.href)
                                                ? 'bg-primary-container text-secondary-fixed'
                                                : 'text-primary-fixed-dim hover:bg-white/5 hover:text-on-primary',
                                        )}
                                    >
                                        <Icon
                                            name={item.icon}
                                            className="text-[20px]"
                                        />
                                        {item.label}
                                    </Link>
                                ))}
                            </div>
                        ))}
                    </nav>

                    <div className="flex flex-col gap-1 border-t border-white/10 px-4 py-4">
                        <a
                            href={home.url()}
                            target="_blank"
                            rel="noreferrer"
                            className="flex items-center gap-3 rounded px-3 py-2 text-label-md text-primary-fixed-dim hover:bg-white/5 hover:text-on-primary"
                        >
                            <Icon name="open_in_new" className="text-[20px]" />
                            View website
                        </a>
                        <Link
                            href={logout()}
                            as="button"
                            className="flex items-center gap-3 rounded px-3 py-2 text-left text-label-md text-primary-fixed-dim hover:bg-white/5 hover:text-on-primary"
                        >
                            <Icon name="logout" className="text-[20px]" />
                            Log out
                            {auth.user && (
                                <span className="ml-auto truncate text-body-sm text-on-primary-container">
                                    {auth.user.name}
                                </span>
                            )}
                        </Link>
                    </div>
                </aside>

                {isSidebarOpen && (
                    <button
                        type="button"
                        aria-label="Close menu"
                        onClick={() => setIsSidebarOpen(false)}
                        className="fixed inset-0 z-30 bg-[#0a1424]/65 lg:hidden"
                    />
                )}

                <div className="flex min-w-0 flex-1 flex-col">
                    <header className="sticky top-0 z-20 flex items-center gap-3 border-b border-surface-container-high bg-surface/95 px-4 py-4 backdrop-blur-md md:px-margin">
                        <button
                            type="button"
                            aria-label="Open menu"
                            onClick={() => setIsSidebarOpen(true)}
                            className="flex h-10 w-10 items-center justify-center rounded text-primary hover:bg-surface-container lg:hidden"
                        >
                            <Icon name="menu" className="text-[24px]" />
                        </button>
                        <div className="flex min-w-0 flex-1 flex-col">
                            <h1 className="truncate font-serif text-headline-sm text-primary">
                                {title}
                            </h1>
                            {description && (
                                <p className="hidden text-body-sm text-on-surface-variant sm:block">
                                    {description}
                                </p>
                            )}
                        </div>
                        {actions}
                    </header>

                    <main className="mx-auto w-full max-w-5xl flex-1 px-4 py-space-xl md:px-margin">
                        {children}
                    </main>
                </div>
            </div>

            {visibleMessage && (
                <div
                    role="status"
                    className="fixed right-4 bottom-4 z-50 flex items-center gap-3 rounded-lg border-t-[3px] border-secondary-fixed-dim bg-primary-container px-5 py-3 text-label-md text-on-primary shadow-[0_16px_32px_rgba(15,35,71,0.2)]"
                >
                    <Icon
                        name="check_circle"
                        className="text-[20px] text-secondary-fixed"
                    />
                    {visibleMessage}
                </div>
            )}
        </>
    );
}
