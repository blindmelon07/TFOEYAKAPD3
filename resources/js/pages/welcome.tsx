import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';
import { home } from '@/routes';

const images = {
    districtSeal: '/images/sed-removebg-preview.png',
    philippineEagle:
        '/images/majestic_philippine_eagle_pithecophaga_jefferyi_with_crown_of_feathers_powerful.png',
};

const navigationLinks = [
    { label: 'About Our Order', href: '#principles' },
    { label: 'Principles & Creed', href: '#creed' },
    { label: 'Community Outreach', href: '#outreach' },
    { label: 'Kuya & Ate Roster', href: '#pathway' },
];

const chapterStats = [
    { value: '500+', label: 'Kuya & Ate Members' },
    { value: '50+', label: 'Community Outreach Missions' },
    { value: '100%', label: 'Civic & Humanitarian Service' },
    { value: 'YKP-042', label: 'Officially Chartered Chapter' },
];

const pillars = [
    {
        icon: 'diversity_3',
        tag: 'Pillar I • Kapatiran',
        title: 'Brotherhood',
        description:
            'Unconditional camaraderie, mutual respect, and lifetime fraternal fidelity. We stand shoulder-to-shoulder through all trials of nationhood.',
    },
    {
        icon: 'volunteer_activism',
        tag: 'Pillar II • Serbisyo',
        title: 'Service to Humanity',
        description:
            'The living essence of “YAKAP”—to embrace, shelter, and empower the underserved with genuine philanthropic devotion.',
    },
    {
        icon: 'flag',
        tag: 'Pillar III • Nasyonalismo',
        title: 'Patriotism',
        description:
            'Deep, unyielding allegiance to the Republic of the Philippines, promoting indigenous culture, national peace, and sovereignty.',
    },
    {
        icon: 'balance',
        tag: 'Pillar IV • Dangal',
        title: 'Integrity & Honor',
        description:
            "Upholding upright conduct, ethical governance in private and public affairs, and the sacred sanctity of one's sworn fraternal pledge.",
    },
];

const missions = [
    {
        category: 'Health & Wellness',
        program: 'Project Yakap Kalinga',
        title: 'Barangay Medical & Dental Mission',
        description:
            'Free clinical consultations, tooth extraction, optical checks, and maintenance medicines for 1,200 indigent families.',
        metricLabel: 'Beneficiaries',
        metricValue: '1,240 Citizens',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAUiObp7e-YPBLyqw6-YA-fwl_hzHR1nwVlCtG2WpjoViHQBng8YEcJCjiMsqq9Vvgf3X-0x6wnQj0VqOfcX59kfgLr6veMgWgMytrA9ET_twUoEo4k3tcb9qVJ5QOAuTThmyYcvUmQZphEg9axfQ5mEOqFo05eorJbvJtVb5B6-R0lqAG2rn_QNj3NbIm8LFcG8yZ7EnIxHxy1bhhO4ViAnWrLsumNa_0Q8oS6TXWBvFr4XxqjwCAP',
        imageAlt:
            'Volunteer doctors and nurses in Eagles vests conducting a community medical mission',
    },
    {
        category: 'Education',
        program: 'Eagle Wings Grant',
        title: 'Youth Scholarship Endowment',
        description:
            'Full semester tuition, stipends, and school supplies provided to promising students from rural public high schools.',
        metricLabel: 'Active Scholars',
        metricValue: '85 Students',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDL9_4yKdwOiqqLVRtAChny8Qqzo5VVEX8qY07aaEN8b9gyMKtUxIdrBmB1EM8Jt3Ef4o7H0lJH9P895YPoYr9BwzR2fdaVFppEKrfhWLwV_tMfVHN1pxIxmqKieQjgHrIy1JDn_FQebIfC8NoztzNiwk01TjXBQzANevBJ1WY9Ht3kPiUC6fZ9lFn2ywzkpCeGL6m4H0_EC-pgESbD6IfqNxLTZr8ATmShDFE0oiMU6vrvRv3Wvzqs',
        imageAlt:
            'Young scholars receiving educational grant certificates from Eagles members',
    },
    {
        category: 'Rapid Response',
        program: 'Alay Agila Calamity Unit',
        title: 'Typhoon & Flood Relief Dispatch',
        description:
            'Immediate deployment of clean water filtration, dry rations, and hygiene sets within 36 hours of natural emergencies.',
        metricLabel: 'Emergency Aid Packs',
        metricValue: '3,500 Families',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuB7ry4R6GcDxOpDSXHaApKX5L2UbzRdSVx3TiKCnEwTTPIeIpDBcWGDV-oACzAATegrWw-l8-0ogi0A70X85JwZAPK-xOAQ5Nk_wbfflp0R8AdFLub2hqP_wSJSD7mEgWi-W5Rg_17JMVsp7ZsmXPWXLtDlON6wMswjG8EokcYC9pgK_D3goOEAbHmygku5o991ZCiXoVQXFoy8TYg3PbXAUUtZGUggHq9TO8gM1EMdOxs_Dl70aq-b',
        imageAlt:
            'Relief convoy trucks arriving in a storm-affected coastal town',
    },
    {
        category: 'Ecology',
        program: 'Bantay Kalikasan',
        title: 'Philippine Eagle Habitat Care',
        description:
            "Reforestation of Sierra Madre corridors and educational school caravans advocating for the national bird's survival.",
        metricLabel: 'Trees Planted',
        metricValue: '12,000 Saplings',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBApiZrV-symWoxPAYusyFPqidhZUoz7HAIsq9xJ7ozEe3uhjFhmlQOY5YKNMB4lB0YkCw3cqSS3A3MPG4lxNBjT6gJqGQxUwMFkmTyrtNIAeyTavdDianDr7lfyL6d0w97r454KDsqFXyRIDTeZeM1vGsghrAMl2zfWmtK5JvSec0us1hTt94vUi3Sk6tg3r55bPZY8qQqjxoS-PZCeNvwcbRIAdqKB18ItVrmAv7J7tC87-aymnOP',
        imageAlt:
            'Volunteers planting endemic hardwood saplings in a rainforest',
    },
];

const fundAllocation = [
    { label: 'Health', percentage: 45, colorClassName: 'bg-primary' },
    { label: 'Relief', percentage: 25, colorClassName: 'bg-primary-container' },
    { label: 'Grants', percentage: 20, colorClassName: 'bg-secondary' },
    {
        label: 'Nature',
        percentage: 10,
        colorClassName: 'bg-secondary-container',
    },
];

const membershipSteps = [
    {
        number: '01',
        icon: 'group_add',
        title: 'Fraternal Sponsorship',
        description:
            'Every applicant must be formally sponsored by an active Kuya or Ate in good standing within the YAKAP Chapter or the National Assembly.',
        requirement: '1 Primary & 1 Co-Sponsor',
    },
    {
        number: '02',
        icon: 'fact_check',
        title: 'Review & Orientation',
        description:
            'Thorough background review by the Committee on Membership, followed by attendance in the official Eagle Aspirant Pre-Induction Seminar (PIS).',
        requirement: 'Moral & Civic Clearance',
    },
    {
        number: '03',
        icon: 'workspace_premium',
        title: 'Rite of Passage & Oath',
        description:
            "Fulfillment of fraternal initiation rites and solemn swearing of the Eagle's Oath before the National Assembly and Chapter Council.",
        requirement: 'Lifetime Commitment',
    },
];

function Icon({ name, className }: { name: string; className?: string }) {
    return (
        <span aria-hidden="true" className={cn('material-symbols', className)}>
            {name}
        </span>
    );
}

function Eagles({ className }: { className?: string }) {
    return (
        <span
            className={cn(
                'inline-block font-ballpark leading-none font-normal tracking-[0.03em] normal-case',
                className,
            )}
        >
            Eagles
        </span>
    );
}

function SectionHeading({
    eyebrow,
    children,
    description,
}: {
    eyebrow: string;
    children: ReactNode;
    description: string;
}) {
    return (
        <div className="mx-auto flex max-w-2xl flex-col items-center gap-space-xs text-center">
            <span className="text-label-sm tracking-[0.2em] text-secondary uppercase">
                {eyebrow}
            </span>
            <h2 className="font-serif text-headline-lg-mobile text-primary md:text-headline-lg">
                {children}
            </h2>
            <div className="my-space-xs flex items-center gap-space-sm">
                <span className="h-0.5 w-12 bg-secondary-fixed-dim" />
                <Icon name="shield" className="text-[20px] text-secondary" />
                <span className="h-0.5 w-12 bg-secondary-fixed-dim" />
            </div>
            <p className="text-body-md text-on-surface-variant">
                {description}
            </p>
        </div>
    );
}

function EagleBackdrop() {
    return (
        <div className="pointer-events-none absolute inset-0 overflow-hidden">
            <div className="absolute inset-y-0 right-0 w-full md:w-1/2 lg:w-5/12">
                <img
                    src={images.philippineEagle}
                    alt=""
                    className="h-full w-full object-cover object-[60%_center]"
                />
                <div className="absolute inset-0 bg-linear-to-r from-primary via-primary/40 to-transparent" />
                <div className="absolute inset-0 bg-linear-to-t from-primary via-transparent to-primary/20" />
            </div>
        </div>
    );
}

function SiteHeader() {
    const [isMenuOpen, setIsMenuOpen] = useState(false);

    return (
        <header className="fixed top-0 z-50 w-full bg-surface/95 shadow-[0_1px_8px_rgba(15,35,71,0.06)] backdrop-blur-md">
            <div className="mx-auto flex h-20 max-w-7xl items-center justify-between gap-gutter px-4 md:px-margin">
                <Link href={home()} className="flex items-center gap-space-md">
                    <img
                        src={images.districtSeal}
                        alt="TFOE Philippine Eagles Sorsogon District Seal"
                        className="h-12 w-12 object-contain p-0.5"
                    />
                    <div className="flex flex-col">
                        <span className="font-serif text-body-md leading-tight font-bold tracking-tight text-primary sm:text-headline-sm sm:leading-tight">
                            The Fraternal Order of{' '}
                            <Eagles className="text-[1.4rem] text-secondary sm:text-[1.65rem]" />
                        </span>
                        <span className="hidden text-label-sm tracking-widest text-secondary uppercase sm:block">
                            Philippine Eagles | YAKAP Chapter
                        </span>
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
                    <a
                        href="#"
                        className="hidden items-center justify-center rounded bg-secondary-container px-space-md py-space-xs text-label-md text-on-secondary-container shadow-sm transition-colors hover:bg-secondary-fixed hover:text-on-secondary-fixed md:inline-flex"
                    >
                        Member Portal
                    </a>
                    <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary">
                        <Icon
                            name="person"
                            className="text-[18px] text-on-primary"
                        />
                    </div>
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
                    <a
                        href="#"
                        className="mt-space-xs rounded bg-secondary-container px-space-sm py-space-sm text-center text-label-md text-on-secondary-container md:hidden"
                    >
                        Member Portal
                    </a>
                </nav>
            )}
        </header>
    );
}

function HeroSection() {
    return (
        <section className="relative w-full overflow-hidden bg-primary text-on-primary">
            <EagleBackdrop />
            <div className="pointer-events-none absolute top-1/4 left-1/2 h-96 w-96 -translate-x-1/2 -translate-y-1/2 rounded-full bg-secondary-container/15 blur-3xl" />

            <div className="relative z-10 mx-auto flex max-w-7xl flex-col items-center px-4 pt-space-xl pb-28 text-center md:px-margin">
                <div className="group mb-space-lg flex flex-col items-center">
                    <div className="relative flex h-48 w-48 items-center justify-center rounded-full bg-primary/90 p-2 shadow-2xl md:h-56 md:w-56">
                        <div className="absolute -inset-2 rounded-full bg-linear-to-r from-secondary-fixed via-secondary to-secondary-fixed opacity-40 blur-md transition duration-500 group-hover:opacity-75" />
                        <img
                            src={images.districtSeal}
                            alt="Sorsogon Eagles District III - Sorsogon City, Philippines Seal"
                            className="relative z-10 h-full w-full object-contain drop-shadow-lg"
                        />
                    </div>
                    <div className="mt-space-md inline-flex items-center gap-space-xs text-label-md tracking-wider uppercase drop-shadow">
                        <Icon name="location_on" className="text-[18px]" />
                        <span>
                            Sorsogon Eagles District III • Sorsogon City,
                            Philippines
                        </span>
                    </div>
                </div>

                <div className="mb-space-md inline-flex items-center gap-space-xs rounded-full bg-primary-container/80 px-space-md py-space-xs text-secondary-fixed shadow-md backdrop-blur-md">
                    <Icon name="verified" className="text-[18px]" />
                    <span className="text-label-sm tracking-widest uppercase">
                        Alang-Alang sa Diyos at Sambayanang Pilipino
                    </span>
                </div>

                <div className="mb-space-lg flex max-w-4xl flex-col items-center gap-space-sm">
                    <span className="text-label-md tracking-[0.25em] text-primary-fixed-dim uppercase">
                        The Fraternal Order of{' '}
                        <Eagles className="text-[1.4rem] text-secondary-fixed" />{' '}
                        — Philippine{' '}
                        <Eagles className="text-[1.4rem] text-secondary-fixed" />
                    </span>
                    <h1 className="font-serif text-headline-lg-mobile tracking-tight text-surface-bright sm:text-display md:text-[3.25rem] md:leading-[3.75rem]">
                        YAKAP CHAPTER
                    </h1>
                    <p className="font-serif text-headline-sm font-normal tracking-wide text-secondary-fixed">
                        Unity, Service, and Unbroken Brotherhood
                    </p>
                    <p className="mt-space-xs max-w-2xl text-body-lg leading-relaxed text-primary-fixed-dim">
                        Pioneering indigenous socio-civic leadership across the
                        archipelago. We bind our strength to uplift the
                        marginalized, defend civic honor, and preserve the
                        legacy of true Filipino brotherhood.
                    </p>
                </div>

                <div className="flex flex-wrap items-center justify-center gap-space-md">
                    <a
                        href="#principles"
                        className="inline-flex items-center justify-center gap-space-xs rounded bg-secondary-container px-space-lg py-space-sm text-label-md text-on-secondary-container shadow-lg shadow-secondary-container/20 transition-all duration-200 hover:bg-secondary-fixed hover:text-on-secondary-fixed"
                    >
                        <Icon name="explore" className="text-[20px]" />
                        Explore Our Brotherhood
                    </a>
                    <a
                        href="#creed"
                        className="inline-flex items-center justify-center gap-space-xs rounded bg-primary-container/90 px-space-lg py-space-sm text-label-md text-on-primary shadow-md backdrop-blur-md transition-all duration-200 hover:bg-primary-container"
                    >
                        <Icon
                            name="menu_book"
                            className="text-[20px] text-secondary-fixed"
                        />
                        Read Eagle's Creed
                    </a>
                </div>
            </div>

            <div className="relative z-20 w-full bg-primary-container py-space-md shadow-2xl">
                <div className="mx-auto grid max-w-7xl grid-cols-2 gap-gutter px-4 text-center md:px-margin lg:grid-cols-4">
                    {chapterStats.map((stat) => (
                        <div
                            key={stat.label}
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
        </section>
    );
}

function PillarsSection() {
    return (
        <section id="principles" className="w-full bg-background py-space-xl">
            <div className="mx-auto flex max-w-7xl flex-col gap-space-xl px-4 md:px-margin">
                <SectionHeading
                    eyebrow="Foundation of the Order"
                    description="As the first Philippine-born fraternal socio-civic movement, every member of YAKAP Chapter lives, leads, and serves by four sacred tenets."
                >
                    The Four Pillars of the Philippine{' '}
                    <Eagles className="text-[2.25rem] text-secondary md:text-[2.75rem]" />
                </SectionHeading>

                <div className="grid grid-cols-1 gap-gutter md:grid-cols-2 lg:grid-cols-4">
                    {pillars.map((pillar) => (
                        <div
                            key={pillar.title}
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
                            <span className="text-label-sm tracking-widest text-secondary-fixed uppercase">
                                The Sovereign Pledge
                            </span>
                            <h3 className="font-serif text-headline-lg text-surface-bright">
                                The{' '}
                                <Eagles className="text-[2.6rem] text-secondary-fixed" />
                                's Creed
                            </h3>
                            <p className="text-body-sm leading-relaxed text-primary-fixed-dim">
                                Recited at every formal assembly, regular agape,
                                and sacred charter ceremony since our founding
                                in 1979.
                            </p>
                        </div>
                        <figure className="flex w-full flex-col gap-space-sm rounded bg-primary/70 p-space-lg shadow-inner backdrop-blur-md lg:w-2/3">
                            <blockquote className="font-serif text-body-lg leading-relaxed text-secondary-fixed italic">
                                “I am an Eagle, the Philippine Eagle. I fly high
                                above the petty jealousies and animosities of
                                mortal men. I serve my God, my country, and my
                                fellowmen with honor, loyalty, and true
                                fraternal love.”
                            </blockquote>
                            <figcaption className="flex flex-wrap items-center justify-between gap-space-xs pt-space-xs text-label-sm">
                                <span className="tracking-wider text-primary-fixed-dim uppercase">
                                    TFOE-PE Fundamental Charter
                                </span>
                                <span className="text-secondary-fixed">
                                    Honorary Code of 1979
                                </span>
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </div>
        </section>
    );
}

function OutreachSection() {
    return (
        <section
            id="outreach"
            className="w-full bg-surface-container-low py-space-xl"
        >
            <div className="mx-auto flex max-w-7xl flex-col gap-space-xl px-4 md:px-margin">
                <div className="flex flex-col justify-between gap-space-md md:flex-row md:items-end">
                    <div className="flex flex-col gap-space-xs">
                        <span className="text-label-sm tracking-widest text-secondary uppercase">
                            Action in the Field
                        </span>
                        <h2 className="font-serif text-headline-lg-mobile text-primary md:text-headline-lg">
                            YAKAP Socio-Civic Missions
                        </h2>
                        <p className="max-w-xl text-body-md text-on-surface-variant">
                            Real impact measured in lives transformed, barangays
                            empowered, and civic responsibility fulfilled.
                        </p>
                    </div>
                    <div className="inline-flex items-center gap-space-xs self-start rounded bg-surface-container px-space-md py-space-xs md:self-auto">
                        <Icon
                            name="event_available"
                            className="text-[18px] text-secondary"
                        />
                        <span className="text-label-sm">
                            Year-to-Date 2024 Civic Summary
                        </span>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-gutter md:grid-cols-2 lg:grid-cols-4">
                    {missions.map((mission) => (
                        <article
                            key={mission.title}
                            className="flex flex-col overflow-hidden rounded-lg bg-surface-container-lowest shadow-sm transition-all duration-300 hover:shadow-lg"
                        >
                            <div className="relative h-48 w-full overflow-hidden bg-primary">
                                <img
                                    src={mission.image}
                                    alt={mission.imageAlt}
                                    loading="lazy"
                                    className="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                                />
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
                                        {mission.metricLabel}
                                    </span>
                                    <span className="text-label-md font-bold text-primary">
                                        {mission.metricValue}
                                    </span>
                                </div>
                            </div>
                        </article>
                    ))}
                </div>

                <div className="flex flex-col items-center justify-between gap-space-lg rounded-lg bg-surface-container-lowest p-space-lg shadow-sm md:flex-row">
                    <div className="flex max-w-md flex-col gap-space-xs">
                        <span className="text-label-sm tracking-wider text-secondary uppercase">
                            Audit &amp; Accountability
                        </span>
                        <h4 className="font-serif text-headline-sm text-primary">
                            Annual Civic Fund Allocation
                        </h4>
                        <p className="text-body-sm text-on-surface-variant">
                            All member dues, alumni endowments, and fraternal
                            benefit proceeds go directly toward certified
                            philanthropic initiatives.
                        </p>
                    </div>
                    <div className="flex w-full grow flex-col gap-space-xs md:w-auto">
                        <div
                            className="flex h-6 w-full overflow-hidden rounded bg-surface-container"
                            role="img"
                            aria-label={fundAllocation
                                .map(
                                    (fund) =>
                                        `${fund.label} ${fund.percentage}%`,
                                )
                                .join(', ')}
                        >
                            {fundAllocation.map((fund) => (
                                <div
                                    key={fund.label}
                                    className={cn(
                                        'h-full',
                                        fund.colorClassName,
                                    )}
                                    style={{ width: `${fund.percentage}%` }}
                                    title={`${fund.label}: ${fund.percentage}%`}
                                />
                            ))}
                        </div>
                        <div className="flex flex-wrap items-center justify-between gap-space-xs pt-space-xs text-label-sm text-on-surface-variant">
                            {fundAllocation.map((fund) => (
                                <span
                                    key={fund.label}
                                    className="flex items-center gap-1"
                                >
                                    <span
                                        className={cn(
                                            'inline-block h-3 w-3 rounded-full',
                                            fund.colorClassName,
                                        )}
                                    />
                                    {fund.label} ({fund.percentage}%)
                                </span>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}

function MembershipSection() {
    return (
        <section id="pathway" className="w-full bg-background py-space-xl">
            <div className="mx-auto flex max-w-7xl flex-col gap-space-xl px-4 md:px-margin">
                <SectionHeading
                    eyebrow="Fraternal Aspirants"
                    description="Membership in the Philippine Eagles is a sacred lifetime honor granted only to men and women of proven civic integrity, goodwill, and dedication to public service."
                >
                    How to Soar With the{' '}
                    <Eagles className="text-[2.25rem] text-secondary md:text-[2.75rem]" />
                </SectionHeading>

                <div className="grid grid-cols-1 gap-gutter md:grid-cols-3">
                    {membershipSteps.map((step) => (
                        <div
                            key={step.number}
                            className="flex flex-col gap-space-md rounded-lg bg-surface-container-lowest p-space-lg shadow-sm"
                        >
                            <div className="flex items-center justify-between">
                                <span className="font-serif text-headline-lg text-secondary/30">
                                    {step.number}
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
                        <span className="text-label-sm tracking-widest text-secondary uppercase">
                            Fraternal Secretariat
                        </span>
                        <h3 className="font-serif text-headline-sm text-primary">
                            Seeking Affiliation with YAKAP Chapter?
                        </h3>
                        <p className="max-w-lg text-body-sm text-on-surface-variant">
                            Connect with our Chapter Secretariat for upcoming
                            Assembly dates, sponsor introductions, or transfer
                            credentials from sister chapters.
                        </p>
                    </div>
                    <div className="flex w-full flex-col items-center gap-space-sm sm:flex-row md:w-auto">
                        <a
                            href="mailto:secretariat@yakapeagles.ph"
                            className="inline-flex w-full items-center justify-center gap-space-xs rounded bg-primary px-space-lg py-space-sm text-label-md text-on-primary shadow transition-colors hover:bg-primary-container sm:w-auto"
                        >
                            <Icon name="mail" className="text-[20px]" />
                            Contact Secretariat
                        </a>
                        <a
                            href="#"
                            className="inline-flex w-full items-center justify-center gap-space-xs rounded bg-surface-container-lowest px-space-lg py-space-sm text-label-md text-primary shadow-sm transition-colors hover:bg-surface-container-high sm:w-auto"
                        >
                            <Icon
                                name="download"
                                className="text-[20px] text-secondary"
                            />
                            Charter Guidelines
                        </a>
                    </div>
                </div>
            </div>
        </section>
    );
}

function SiteFooter() {
    return (
        <footer className="w-full bg-primary pt-space-xl pb-space-lg text-on-primary">
            <div className="mx-auto flex max-w-7xl flex-col gap-space-xl px-4 md:px-margin">
                <div className="grid grid-cols-1 gap-gutter md:grid-cols-4">
                    <div className="flex flex-col gap-space-sm md:col-span-2">
                        <span className="font-serif text-headline-sm text-primary-fixed">
                            TFOE-PE YAKAP Chapter
                        </span>
                        <p className="font-serif text-headline-sm text-secondary-fixed italic">
                            Alang-alang sa Diyos at Bayan
                        </p>
                        <p className="max-w-md text-body-md text-primary-fixed-dim">
                            Service Through Strong Brotherhood. Committed to
                            philanthropic service, civic stewardship, and
                            lasting fraternal solidarity across the Philippines.
                        </p>
                        <span className="mt-space-xs text-label-sm tracking-wider text-secondary-fixed uppercase">
                            Charter No. YKP-042 | Regional Assembly VII
                        </span>
                    </div>
                    <div className="flex flex-col gap-space-xs">
                        <span className="mb-space-xs text-label-sm tracking-widest text-secondary-fixed uppercase">
                            Fraternal Links
                        </span>
                        {[
                            {
                                label: 'The Philippine Eagles Story',
                                href: '#principles',
                            },
                            { label: 'The Eagles Creed', href: '#creed' },
                            {
                                label: 'Chapter Officers & Roll',
                                href: '#pathway',
                            },
                            {
                                label: 'Alay Agila Initiatives',
                                href: '#outreach',
                            },
                        ].map((link) => (
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
                        <span>YAKAP Chapter Fraternal Hall</span>
                        <a
                            href="mailto:secretariat@yakapeagles.ph"
                            className="hover:text-on-primary"
                        >
                            secretariat@yakapeagles.ph
                        </a>
                        <span>+63 (02) 8920-EAGLE</span>
                        <div className="mt-space-sm flex items-center gap-space-sm">
                            <Icon
                                name="verified"
                                className="text-[20px] text-secondary-fixed"
                            />
                            <span className="text-label-sm">
                                Accredited Civic Order
                            </span>
                        </div>
                    </div>
                </div>
                <div className="flex flex-col items-center justify-between gap-space-sm rounded bg-primary-container/40 px-space-md py-space-md text-center md:flex-row md:text-left">
                    <span className="text-body-sm text-primary-fixed-dim">
                        © {new Date().getFullYear()} The Fraternal Order of
                        Eagles - Philippine Eagles (TFOE-PE) YAKAP Chapter. All
                        Rights Reserved.
                    </span>
                    <span className="text-label-sm text-secondary-fixed">
                        Pro Deo et Patria
                    </span>
                </div>
            </div>
        </footer>
    );
}

export default function Welcome() {
    return (
        <>
            <Head title="YAKAP Chapter" />
            <div className="min-h-screen bg-background font-sans text-body-md text-on-surface">
                <SiteHeader />
                <main className="w-full pt-20">
                    <HeroSection />
                    <PillarsSection />
                    <OutreachSection />
                    <section
                        aria-hidden="true"
                        className="relative h-20 w-full overflow-hidden bg-primary"
                    >
                        <EagleBackdrop />
                    </section>
                    <MembershipSection />
                </main>
                <SiteFooter />
            </div>
        </>
    );
}
