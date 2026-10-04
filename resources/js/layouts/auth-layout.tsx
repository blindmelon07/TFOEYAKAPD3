import { Head, Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Icon } from '@/components/icon';
import { InstallAppButton } from '@/components/install-app-button';
import { home } from '@/routes';

/**
 * Navy sign-in shell shared by the login and password reset pages.
 */
export default function AuthLayout({
    title,
    description,
    footer,
    children,
}: {
    title: string;
    description: string;
    footer?: ReactNode;
    children: ReactNode;
}) {
    const { props, flash } = usePage();
    const logo = props.siteLogo;

    return (
        <>
            <Head title={title} />
            <div className="flex min-h-screen items-center justify-center bg-primary px-4 py-space-xl font-sans">
                <div className="w-full max-w-md">
                    <div className="mb-space-lg flex flex-col items-center gap-2 text-center">
                        {logo && (
                            <div className="relative mb-space-xs flex h-32 w-32 items-center justify-center">
                                <div className="absolute -inset-1 rounded-full bg-linear-to-r from-secondary-fixed via-secondary to-secondary-fixed opacity-40 blur-md" />
                                <img
                                    src={logo}
                                    alt="District logo"
                                    className="relative h-full w-full object-contain drop-shadow-lg"
                                />
                            </div>
                        )}
                        <h1 className="font-serif text-headline-md text-on-primary">
                            {title}
                        </h1>
                        <p className="text-body-sm text-primary-fixed-dim">
                            {description}
                        </p>
                    </div>

                    {flash.success && (
                        <div
                            role="status"
                            className="mb-space-md flex items-start gap-2 rounded-lg bg-primary-container px-4 py-3 text-body-sm text-on-primary"
                        >
                            <Icon
                                name="check_circle"
                                className="text-[20px] text-secondary-fixed"
                            />
                            {flash.success}
                        </div>
                    )}

                    <div className="rounded-lg border-t-[3px] border-secondary-fixed-dim bg-surface-container-lowest p-space-xl shadow-[0_16px_32px_rgba(15,35,71,0.2)]">
                        {children}
                    </div>

                    <InstallAppButton
                        variant="banner"
                        className="mt-space-md"
                    />

                    <div className="mt-space-lg flex flex-col items-center gap-2 text-center">
                        {footer}
                        <Link
                            href={home()}
                            className="text-label-md text-primary-fixed-dim hover:text-on-primary"
                        >
                            ← Back to website
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
