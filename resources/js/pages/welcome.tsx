import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import { EaglesText } from '@/components/eagles-text';
import { Icon } from '@/components/icon';
import { fundAllocationColorClasses } from '@/lib/content';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import { dashboard } from '@/routes/admin';
import type {
    ChapterStat,
    FundAllocation,
    MembershipStep,
    Mission,
    Pillar,
    SiteSettings,
} from '@/types';

type WelcomeProps = {
    settings: SiteSettings;
    stats: ChapterStat[];
    pillars: Pillar[];
    missions: Mission[];
    fundAllocations: FundAllocation[];
    membershipSteps: MembershipStep[];
};

const navigationLinks = [
    { label: 'About Our Order', href: '#principles' },
    { label: 'Principles & Creed', href: '#creed' },
    { label: 'Community Outreach', href: '#outreach' },
    { label: 'Kuya & Ate Roster', href: '#pathway' },
];

const footerLinks = [
    { label: 'The Philippine Eagles Story', href: '#principles' },
    { label: 'The Eagles Creed', href: '#creed' },
    { label: 'Chapter Officers & Roll', href: '#pathway' },
    { label: 'Alay Agila Initiatives', href: '#outreach' },
];

function SectionHeading({
    eyebrow,
    title,
    description,
}: {
    eyebrow: string | null;
    title: string | null;
    description: string | null;
}) {
    return (
        <div className="mx-auto flex max-w-2xl flex-col items-center gap-space-xs text-center">
            {eyebrow && (
                <span className="text-label-sm tracking-[0.2em] text-secondary uppercase">
                    {eyebrow}
                </span>
            )}
            <h2 className="font-serif text-headline-lg-mobile text-primary md:text-headline-lg">
                <EaglesText
                    text={title}
                    scriptClassName="text-[2.25rem] text-secondary md:text-[2.75rem]"
                />
            </h2>
            <div className="my-space-xs flex items-center gap-space-sm">
                <span className="h-0.5 w-12 bg-secondary-fixed-dim" />
                <Icon name="shield" className="text-[20px] text-secondary" />
                <span className="h-0.5 w-12 bg-secondary-fixed-dim" />
            </div>
            {description && (
                <p className="text-body-md text-on-surface-variant">
                    {description}
                </p>
            )}
        </div>
    );
}

function EagleBackdrop({ image }: { image: string | null }) {
    return (
        <div className="pointer-events-none absolute inset-0 overflow-hidden">
            <div className="absolute inset-y-0 right-0 w-full md:w-1/2 lg:w-5/12">
                {image && (
                    <img
                        src={image}
                        alt=""
                        className="h-full w-full object-cover object-[60%_center]"
                    />
                )}
                <div className="absolute inset-0 bg-linear-to-r from-primary via-primary/40 to-transparent" />
                <div className="absolute inset-0 bg-linear-to-t from-primary via-transparent to-primary/20" />
            </div>
        </div>
    );
}

function ExternalOrAnchor({
    href,
    className,
    children,
}: {
    href: string | null;
    className: string;
    children: ReactNode;
}) {
    const isExternal = href?.startsWith('http');

    return (
        <a
            href={href || '#'}
            className={className}
            {...(isExternal ? { target: '_blank', rel: 'noreferrer' } : {})}
        >
            {children}
        </a>
    );
}

/**
 * The Member Portal opens this site's member area (the login page for
 * guests) unless a custom link has been set in the admin settings.
 */
function memberPortalUrl(customUrl: string | null): string {
    return customUrl && customUrl !== '#' ? customUrl : dashboard.url();
}

function SiteHeader({ settings }: { settings: SiteSettings }) {
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const portalUrl = memberPortalUrl(settings.member_portal_url);

    return (
        <header className="fixed top-0 z-50 w-full bg-surface/95 shadow-[0_1px_8px_rgba(15,35,71,0.06)] backdrop-blur-md">
            <div className="mx-auto flex h-20 max-w-7xl items-center justify-between gap-gutter px-4 md:px-margin">
                <Link href={home()} className="flex items-center gap-space-md">
                    {settings.logo && (
                        <img
                            src={settings.logo}
                            alt={`${settings.header_title ?? ''} logo`}
                            className="h-12 w-12 object-contain p-0.5"
                        />
                    )}
                    <div className="flex flex-col">
                        <span className="font-serif text-body-md leading-tight font-bold tracking-tight text-primary sm:text-headline-sm sm:leading-tight">
                            <EaglesText
                                text={settings.header_title}
                                scriptClassName="text-[1.4rem] text-secondary sm:text-[1.65rem]"
                            />
                        </span>
                        {settings.header_subtitle && (
                            <span className="hidden text-label-sm tracking-widest text-secondary uppercase sm:block">
                                {settings.header_subtitle}
                            </span>
                        )}
                    </div>
                </Link>

                <nav className="hidden items-center gap-space-sm xl:flex">
                    <Link
                        href={home()}
                        aria-current="page"
                        className="rounded bg-primary-container px-space-sm py-space-xs text-label-md text-on-primary"
                    >
                        Home
                    </Link>
                    {navigationLinks.map((link) => (
                        <a
                            key={link.label}
                            href={link.href}
                            className="px-space-sm py-space-xs text-label-md text-on-surface-variant transition-colors hover:text-on-surface"
                        >
                            {link.label}
                        </a>
                    ))}
                </nav>

                <div className="flex items-center gap-space-md">
                    <ExternalOrAnchor
                        href={portalUrl}
                        className="hidden items-center justify-center rounded bg-secondary-container px-space-md py-space-xs text-label-md text-on-secondary-container shadow-sm transition-colors hover:bg-secondary-fixed hover:text-on-secondary-fixed md:inline-flex"
                    >
                        Member Portal
                    </ExternalOrAnchor>
                    <a
                        href={portalUrl}
                        aria-label="Member Portal"
                        className="flex h-8 w-8 items-center justify-center rounded-full bg-primary transition-colors hover:bg-primary-container"
                    >
                        <Icon
                            name="person"
                            className="text-[18px] text-on-primary"
                        />
                    </a>
                    <button
                        type="button"
                        onClick={() => setIsMenuOpen((isOpen) => !isOpen)}
                        aria-expanded={isMenuOpen}
                        aria-label="Toggle navigation"
                        className="flex h-10 w-10 items-center justify-center rounded text-primary hover:bg-surface-container xl:hidden"
                    >
                        <Icon
                            name={isMenuOpen ? 'close' : 'menu'}
                            className="text-[24px]"
                        />
                    </button>
                </div>
            </div>

            {isMenuOpen && (
                <nav className="flex flex-col gap-space-xs border-t border-surface-container bg-surface px-4 py-space-md md:px-margin xl:hidden">
                    {navigationLinks.map((link) => (
                        <a
                            key={link.label}
                            href={link.href}
                            onClick={() => setIsMenuOpen(false)}
                            className="rounded px-space-sm py-space-sm text-label-md text-on-surface-variant hover:bg-surface-container hover:text-on-surface"
                        >
                            {link.label}
                        </a>
                    ))}
                    <ExternalOrAnchor
                        href={portalUrl}
                        className="mt-space-xs rounded bg-secondary-container px-space-sm py-space-sm text-center text-label-md text-on-secondary-container md:hidden"
                    >
                        Member Portal
                    </ExternalOrAnchor>
                </nav>
            )}
        </header>
    );
}

function HeroSection({
    settings,
    stats,
}: {
    settings: SiteSettings;
    stats: ChapterStat[];
}) {
    return (
        <section className="relative w-full overflow-hidden bg-primary text-on-primary">
            <EagleBackdrop image={settings.hero_background} />
            <div className="pointer-events-none absolute top-1/4 left-1/2 h-96 w-96 -translate-x-1/2 -translate-y-1/2 rounded-full bg-secondary-container/15 blur-3xl" />

            <div className="relative z-10 mx-auto flex max-w-7xl flex-col items-center px-4 pt-space-xl pb-28 text-center md:px-margin">
                <div className="group mb-space-lg flex flex-col items-center">
                    {settings.logo && (
                        <div className="relative flex h-48 w-48 items-center justify-center rounded-full bg-primary/90 p-2 shadow-2xl md:h-56 md:w-56">
                            <div className="absolute -inset-2 rounded-full bg-linear-to-r from-secondary-fixed via-secondary to-secondary-fixed opacity-40 blur-md transition duration-500 group-hover:opacity-75" />
                            <img
                                src={settings.logo}
                                alt={`${settings.hero_location ?? ''} seal`}
                                className="relative z-10 h-full w-full object-contain drop-shadow-lg"
                            />
                        </div>
                    )}
                    {settings.hero_location && (
                        <div className="mt-space-md inline-flex items-center gap-space-xs text-label-md tracking-wider uppercase drop-shadow">
                            <Icon name="location_on" className="text-[18px]" />
                            <span>{settings.hero_location}</span>
                        </div>
                    )}
                </div>

                {settings.hero_motto && (
                    <div className="mb-space-md inline-flex items-center gap-space-xs rounded-full bg-primary-container/80 px-space-md py-space-xs text-secondary-fixed shadow-md backdrop-blur-md">
                        <Icon name="verified" className="text-[18px]" />
                        <span className="text-label-sm tracking-widest uppercase">
                            {settings.hero_motto}
                        </span>
                    </div>
                )}

                <div className="mb-space-lg flex max-w-4xl flex-col items-center gap-space-sm">
                    {settings.hero_eyebrow && (
                        <span className="text-label-md tracking-[0.25em] text-primary-fixed-dim uppercase">
                            <EaglesText
                                text={settings.hero_eyebrow}
                                scriptClassName="text-[1.4rem] text-secondary-fixed"
                            />
                        </span>
                    )}
                    <h1 className="font-serif text-headline-lg-mobile tracking-tight text-surface-bright sm:text-display md:text-[3.25rem] md:leading-[3.75rem]">
                        {settings.hero_title}
                    </h1>
                    {settings.hero_tagline && (
                        <p className="font-serif text-headline-sm font-normal tracking-wide text-secondary-fixed">
                            {settings.hero_tagline}
                        </p>
                    )}
                    {settings.hero_description && (
                        <p className="mt-space-xs max-w-2xl text-body-lg leading-relaxed text-primary-fixed-dim">
                            {settings.hero_description}
                        </p>
                    )}
                </div>

                <div className="flex flex-wrap items-center justify-center gap-space-md">
                    {settings.hero_primary_cta && (
                        <a
                            href="#principles"
                            className="inline-flex items-center justify-center gap-space-xs rounded bg-secondary-container px-space-lg py-space-sm text-label-md text-on-secondary-container shadow-lg shadow-secondary-container/20 transition-all duration-200 hover:bg-secondary-fixed hover:text-on-secondary-fixed"
                        >
                            <Icon name="explore" className="text-[20px]" />
                            {settings.hero_primary_cta}
                        </a>
                    )}
                    {settings.hero_secondary_cta && (
                        <a
                            href="#creed"
                            className="inline-flex items-center justify-center gap-space-xs rounded bg-primary-container/90 px-space-lg py-space-sm text-label-md text-on-primary shadow-md backdrop-blur-md transition-all duration-200 hover:bg-primary-container"
                        >
                            <Icon
                                name="menu_book"
                                className="text-[20px] text-secondary-fixed"
                            />
                            {settings.hero_secondary_cta}
                        </a>
                    )}
                </div>
            </div>

            {stats.length > 0 && (
                <div className="relative z-20 w-full bg-primary-container py-space-md shadow-2xl">
                    <div className="mx-auto grid max-w-7xl grid-cols-2 gap-gutter px-4 text-center md:px-margin lg:grid-cols-4">
                        {stats.map((stat) => (
                            <div
                                key={stat.id}
                                className="flex flex-col items-center"
                            >
                                <span className="font-serif text-headline-md text-secondary-fixed md:text-headline-lg">
                                    {stat.value}
                                </span>
                                <span className="text-label-sm tracking-wider text-primary-fixed-dim uppercase">
                                    {stat.label}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </section>
    );
}

function PillarsSection({
    settings,
    pillars,
}: {
    settings: SiteSettings;
    pillars: Pillar[];
}) {
    return (
        <section id="principles" className="w-full bg-background py-space-xl">
            <div className="mx-auto flex max-w-7xl flex-col gap-space-xl px-4 md:px-margin">
                <SectionHeading
                    eyebrow={settings.pillars_eyebrow}
                    title={settings.pillars_title}
                    description={settings.pillars_description}
                />

                <div className="grid grid-cols-1 gap-gutter md:grid-cols-2 lg:grid-cols-4">
                    {pillars.map((pillar) => (
                        <div
                            key={pillar.id}
                            className="group flex flex-col justify-between gap-space-md rounded-lg bg-surface-container-lowest p-space-lg shadow-sm transition-shadow hover:shadow-md"
                        >
                            <div className="flex flex-col gap-space-sm">
                                <div className="flex h-12 w-12 items-center justify-center rounded bg-surface-container text-primary transition-colors group-hover:bg-primary-container group-hover:text-secondary-fixed">
                                    <Icon
                                        name={pillar.icon}
                                        className="text-[28px]"
                                    />
                                </div>
                                <span className="text-label-sm tracking-wider text-secondary uppercase">
                                    {pillar.tag}
                                </span>
                                <h3 className="font-serif text-headline-sm text-primary">
                                    {pillar.title}
                                </h3>
                                <p className="text-body-sm leading-relaxed text-on-surface-variant">
                                    {pillar.description}
                                </p>
                            </div>
                            <div className="h-1 w-full overflow-hidden rounded-full bg-surface-container">
                                <div className="h-full w-1/3 bg-secondary-fixed transition-all duration-500 group-hover:w-full" />
                            </div>
                        </div>
                    ))}
                </div>

                <div
                    id="creed"
                    className="relative w-full overflow-hidden rounded-lg bg-primary-container p-space-lg text-on-primary shadow-md md:p-space-xl"
                >
                    <div className="flex flex-col items-center gap-space-xl lg:flex-row">
                        <div className="flex w-full flex-col gap-space-xs text-center lg:w-1/3 lg:text-left">
                            {settings.creed_eyebrow && (
                                <span className="text-label-sm tracking-widest text-secondary-fixed uppercase">
                                    {settings.creed_eyebrow}
                                </span>
                            )}
                            <h3 className="font-serif text-headline-lg text-surface-bright">
                                <EaglesText
                                    text={settings.creed_title}
                                    scriptClassName="text-[2.6rem] text-secondary-fixed"
                                />
                            </h3>
                            {settings.creed_description && (
                                <p className="text-body-sm leading-relaxed text-primary-fixed-dim">
                                    {settings.creed_description}
                                </p>
                            )}
                        </div>
                        <figure className="flex w-full flex-col gap-space-sm rounded bg-primary/70 p-space-lg shadow-inner backdrop-blur-md lg:w-2/3">
                            <blockquote className="font-serif text-body-lg leading-relaxed text-secondary-fixed italic">
                                “{settings.creed_text}”
                            </blockquote>
                            <figcaption className="flex flex-wrap items-center justify-between gap-space-xs pt-space-xs text-label-sm">
                                <span className="tracking-wider text-primary-fixed-dim uppercase">
                                    {settings.creed_source}
                                </span>
                                <span className="text-secondary-fixed">
                                    {settings.creed_code}
                                </span>
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </div>
        </section>
    );
}

function OutreachSection({
    settings,
    missions,
    fundAllocations,
}: {
    settings: SiteSettings;
    missions: Mission[];
    fundAllocations: FundAllocation[];
}) {
    return (
        <section
            id="outreach"
            className="w-full bg-surface-container-low py-space-xl"
        >
            <div className="mx-auto flex max-w-7xl flex-col gap-space-xl px-4 md:px-margin">
                <div className="flex flex-col justify-between gap-space-md md:flex-row md:items-end">
                    <div className="flex flex-col gap-space-xs">
                        {settings.outreach_eyebrow && (
                            <span className="text-label-sm tracking-widest text-secondary uppercase">
                                {settings.outreach_eyebrow}
                            </span>
                        )}
                        <h2 className="font-serif text-headline-lg-mobile text-primary md:text-headline-lg">
                            {settings.outreach_title}
                        </h2>
                        {settings.outreach_description && (
                            <p className="max-w-xl text-body-md text-on-surface-variant">
                                {settings.outreach_description}
                            </p>
                        )}
                    </div>
                    {settings.outreach_badge && (
                        <div className="inline-flex items-center gap-space-xs self-start rounded bg-surface-container px-space-md py-space-xs md:self-auto">
                            <Icon
                                name="event_available"
                                className="text-[18px] text-secondary"
                            />
                            <span className="text-label-sm">
                                {settings.outreach_badge}
                            </span>
                        </div>
                    )}
                </div>

                <div className="grid grid-cols-1 gap-gutter md:grid-cols-2 lg:grid-cols-4">
                    {missions.map((mission) => (
                        <article
                            key={mission.id}
                            className="flex flex-col overflow-hidden rounded-lg bg-surface-container-lowest shadow-sm transition-all duration-300 hover:shadow-lg"
                        >
                            <div className="relative h-48 w-full overflow-hidden bg-primary">
                                {mission.image_url && (
                                    <img
                                        src={mission.image_url}
                                        alt={mission.image_alt ?? mission.title}
                                        loading="lazy"
                                        className="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                                    />
                                )}
                                <div className="absolute top-space-xs left-space-xs rounded bg-primary/80 px-space-xs py-0.5 text-label-sm text-secondary-fixed backdrop-blur-md">
                                    {mission.category}
                                </div>
                            </div>
                            <div className="flex grow flex-col justify-between gap-space-md p-space-md">
                                <div className="flex flex-col gap-space-xs">
                                    <span className="text-label-sm text-on-surface-variant">
                                        {mission.program}
                                    </span>
                                    <h3 className="text-title text-primary">
                                        {mission.title}
                                    </h3>
                                    <p className="text-body-sm leading-relaxed text-on-surface-variant">
                                        {mission.description}
                                    </p>
                                </div>
                                <div className="flex items-center justify-between gap-space-sm rounded bg-surface-container-low p-space-xs">
                                    <span className="text-label-sm text-on-surface-variant">
                                        {mission.metric_label}
                                    </span>
                                    <span className="text-label-md font-bold text-primary">
                                        {mission.metric_value}
                                    </span>
                                </div>
                            </div>
                        </article>
                    ))}
                </div>

                {fundAllocations.length > 0 && (
                    <div className="flex flex-col items-center justify-between gap-space-lg rounded-lg bg-surface-container-lowest p-space-lg shadow-sm md:flex-row">
                        <div className="flex max-w-md flex-col gap-space-xs">
                            {settings.fund_eyebrow && (
                                <span className="text-label-sm tracking-wider text-secondary uppercase">
                                    {settings.fund_eyebrow}
                                </span>
                            )}
                            <h4 className="font-serif text-headline-sm text-primary">
                                {settings.fund_title}
                            </h4>
                            {settings.fund_description && (
                                <p className="text-body-sm text-on-surface-variant">
                                    {settings.fund_description}
                                </p>
                            )}
                        </div>
                        <div className="flex w-full grow flex-col gap-space-xs md:w-auto">
                            <div
                                className="flex h-6 w-full overflow-hidden rounded bg-surface-container"
                                role="img"
                                aria-label={fundAllocations
                                    .map(
                                        (fund) =>
                                            `${fund.label} ${fund.percentage}%`,
                                    )
                                    .join(', ')}
                            >
                                {fundAllocations.map((fund) => (
                                    <div
                                        key={fund.id}
                                        className={cn(
                                            'h-full',
                                            fundAllocationColorClasses[
                                                fund.color
                                            ],
                                        )}
                                        style={{ width: `${fund.percentage}%` }}
                                        title={`${fund.label}: ${fund.percentage}%`}
                                    />
                                ))}
                            </div>
                            <div className="flex flex-wrap items-center justify-between gap-space-xs pt-space-xs text-label-sm text-on-surface-variant">
                                {fundAllocations.map((fund) => (
                                    <span
                                        key={fund.id}
                                        className="flex items-center gap-1"
                                    >
                                        <span
                                            className={cn(
                                                'inline-block h-3 w-3 rounded-full',
                                                fundAllocationColorClasses[
                                                    fund.color
                                                ],
                                            )}
                                        />
                                        {fund.label} ({fund.percentage}%)
                                    </span>
                                ))}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </section>
    );
}

function MembershipSection({
    settings,
    membershipSteps,
}: {
    settings: SiteSettings;
    membershipSteps: MembershipStep[];
}) {
    return (
        <section id="pathway" className="w-full bg-background py-space-xl">
            <div className="mx-auto flex max-w-7xl flex-col gap-space-xl px-4 md:px-margin">
                <SectionHeading
                    eyebrow={settings.membership_eyebrow}
                    title={settings.membership_title}
                    description={settings.membership_description}
                />

                <div className="grid grid-cols-1 gap-gutter md:grid-cols-3">
                    {membershipSteps.map((step, index) => (
                        <div
                            key={step.id}
                            className="flex flex-col gap-space-md rounded-lg bg-surface-container-lowest p-space-lg shadow-sm"
                        >
                            <div className="flex items-center justify-between">
                                <span className="font-serif text-headline-lg text-secondary/30">
                                    {String(index + 1).padStart(2, '0')}
                                </span>
                                <div className="flex h-10 w-10 items-center justify-center rounded bg-surface-container text-primary">
                                    <Icon
                                        name={step.icon}
                                        className="text-[24px]"
                                    />
                                </div>
                            </div>
                            <div className="flex flex-col gap-space-xs">
                                <h3 className="font-serif text-headline-sm text-primary">
                                    {step.title}
                                </h3>
                                <p className="text-body-sm leading-relaxed text-on-surface-variant">
                                    {step.description}
                                </p>
                            </div>
                            <div className="mt-auto pt-space-xs text-label-sm text-secondary">
                                Requirement: {step.requirement}
                            </div>
                        </div>
                    ))}
                </div>

                <div className="flex w-full flex-col items-center justify-between gap-space-lg rounded-lg bg-surface-container p-space-lg shadow-sm md:flex-row md:p-space-xl">
                    <div className="flex flex-col gap-space-xs text-center md:text-left">
                        {settings.secretariat_eyebrow && (
                            <span className="text-label-sm tracking-widest text-secondary uppercase">
                                {settings.secretariat_eyebrow}
                            </span>
                        )}
                        <h3 className="font-serif text-headline-sm text-primary">
                            {settings.secretariat_title}
                        </h3>
                        {settings.secretariat_description && (
                            <p className="max-w-lg text-body-sm text-on-surface-variant">
                                {settings.secretariat_description}
                            </p>
                        )}
                    </div>
                    <div className="flex w-full flex-col items-center gap-space-sm sm:flex-row md:w-auto">
                        {settings.contact_email && settings.secretariat_cta && (
                            <a
                                href={`mailto:${settings.contact_email}`}
                                className="inline-flex w-full items-center justify-center gap-space-xs rounded bg-primary px-space-lg py-space-sm text-label-md text-on-primary shadow transition-colors hover:bg-primary-container sm:w-auto"
                            >
                                <Icon name="mail" className="text-[20px]" />
                                {settings.secretariat_cta}
                            </a>
                        )}
                        {settings.charter_guidelines_label && (
                            <ExternalOrAnchor
                                href={settings.charter_guidelines_url}
                                className="inline-flex w-full items-center justify-center gap-space-xs rounded bg-surface-container-lowest px-space-lg py-space-sm text-label-md text-primary shadow-sm transition-colors hover:bg-surface-container-high sm:w-auto"
                            >
                                <Icon
                                    name="download"
                                    className="text-[20px] text-secondary"
                                />
                                {settings.charter_guidelines_label}
                            </ExternalOrAnchor>
                        )}
                    </div>
                </div>
            </div>
        </section>
    );
}

function SiteFooter({ settings }: { settings: SiteSettings }) {
    return (
        <footer className="w-full bg-primary pt-space-xl pb-space-lg text-on-primary">
            <div className="mx-auto flex max-w-7xl flex-col gap-space-xl px-4 md:px-margin">
                <div className="grid grid-cols-1 gap-gutter md:grid-cols-4">
                    <div className="flex flex-col gap-space-sm md:col-span-2">
                        <span className="font-serif text-headline-sm text-primary-fixed">
                            {settings.footer_name}
                        </span>
                        {settings.footer_motto && (
                            <p className="font-serif text-headline-sm text-secondary-fixed italic">
                                {settings.footer_motto}
                            </p>
                        )}
                        {settings.footer_description && (
                            <p className="max-w-md text-body-md text-primary-fixed-dim">
                                {settings.footer_description}
                            </p>
                        )}
                        {settings.footer_charter && (
                            <span className="mt-space-xs text-label-sm tracking-wider text-secondary-fixed uppercase">
                                {settings.footer_charter}
                            </span>
                        )}
                    </div>
                    <div className="flex flex-col gap-space-xs">
                        <span className="mb-space-xs text-label-sm tracking-widest text-secondary-fixed uppercase">
                            Fraternal Links
                        </span>
                        {footerLinks.map((link) => (
                            <a
                                key={link.label}
                                href={link.href}
                                className="text-body-sm text-primary-fixed-dim transition-colors hover:text-on-primary"
                            >
                                {link.label}
                            </a>
                        ))}
                    </div>
                    <div className="flex flex-col gap-space-xs text-body-sm text-primary-fixed-dim">
                        <span className="mb-space-xs text-label-sm tracking-widest text-secondary-fixed uppercase">
                            Fraternal Secretariat
                        </span>
                        {settings.contact_address && (
                            <span>{settings.contact_address}</span>
                        )}
                        {settings.contact_email && (
                            <a
                                href={`mailto:${settings.contact_email}`}
                                className="hover:text-on-primary"
                            >
                                {settings.contact_email}
                            </a>
                        )}
                        {settings.contact_phone && (
                            <span>{settings.contact_phone}</span>
                        )}
                        {settings.footer_accreditation && (
                            <div className="mt-space-sm flex items-center gap-space-sm">
                                <Icon
                                    name="verified"
                                    className="text-[20px] text-secondary-fixed"
                                />
                                <span className="text-label-sm">
                                    {settings.footer_accreditation}
                                </span>
                            </div>
                        )}
                    </div>
                </div>
                <div className="flex flex-col items-center justify-between gap-space-sm rounded bg-primary-container/40 px-space-md py-space-md text-center md:flex-row md:text-left">
                    <span className="text-body-sm text-primary-fixed-dim">
                        © {new Date().getFullYear()} {settings.footer_copyright}
                    </span>
                    {settings.footer_latin_motto && (
                        <span className="text-label-sm text-secondary-fixed">
                            {settings.footer_latin_motto}
                        </span>
                    )}
                </div>
            </div>
        </footer>
    );
}

export default function Welcome({
    settings,
    stats,
    pillars,
    missions,
    fundAllocations,
    membershipSteps,
}: WelcomeProps) {
    return (
        <>
            <Head title={settings.hero_title ?? undefined} />
            <div className="min-h-screen bg-background font-sans text-body-md text-on-surface">
                <SiteHeader settings={settings} />
                <main className="w-full pt-20">
                    <HeroSection settings={settings} stats={stats} />
                    <PillarsSection settings={settings} pillars={pillars} />
                    <OutreachSection
                        settings={settings}
                        missions={missions}
                        fundAllocations={fundAllocations}
                    />
                    <section
                        aria-hidden="true"
                        className="relative h-20 w-full overflow-hidden bg-primary"
                    >
                        <EagleBackdrop image={settings.hero_background} />
                    </section>
                    <MembershipSection
                        settings={settings}
                        membershipSteps={membershipSteps}
                    />
                </main>
                <SiteFooter settings={settings} />
            </div>
        </>
    );
}
