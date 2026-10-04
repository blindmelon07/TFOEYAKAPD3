import { Form, Link, router, usePage } from '@inertiajs/react';
import { SubmitButton } from '@/components/admin/form-fields';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { forms } from '@/routes/admin';
import {
    create,
    destroy,
    download,
    duplicate,
    edit,
} from '@/routes/admin/clubs/documents';
import {
    destroy as destroyLetterhead,
    show as showLetterhead,
    store as storeLetterhead,
} from '@/routes/admin/clubs/letterhead';

type DocumentRow = {
    id: number;
    title: string;
    document_date: string;
    excerpt: string;
    author: string | null;
};

const longDate = (value: string) =>
    new Date(`${value}T00:00:00`).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

function LetterheadPanel({
    clubId,
    hasOwnLetterhead,
    canManage,
}: {
    clubId: number;
    hasOwnLetterhead: boolean;
    canManage: boolean;
}) {
    const removeLetterhead = () => {
        if (
            !window.confirm(
                'Remove this club’s letterhead and go back to the district letterhead?',
            )
        ) {
            return;
        }

        router.delete(destroyLetterhead.url(clubId), { preserveScroll: true });
    };

    return (
        <section className="rounded-lg border border-[#d8dee4] bg-surface-container-lowest shadow-[0_2px_4px_rgba(15,35,71,0.04)]">
            <div className="flex flex-col gap-3 p-space-lg sm:flex-row sm:items-center">
                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded bg-primary-container text-secondary-fixed">
                    <Icon name="description" className="text-[26px]" />
                </div>
                <div className="flex min-w-0 flex-1 flex-col">
                    <h2 className="font-serif text-title font-bold text-primary">
                        {hasOwnLetterhead
                            ? 'Club letterhead'
                            : 'District letterhead'}
                    </h2>
                    <p className="text-body-sm text-on-surface-variant">
                        {hasOwnLetterhead
                            ? 'Documents print on this club’s own uploaded letterhead.'
                            : 'Documents print on the shared TFOE-PE letterhead.'}
                    </p>
                </div>
                <a
                    href={showLetterhead.url(clubId)}
                    className="inline-flex items-center justify-center gap-2 rounded border border-secondary-fixed-dim px-4 py-2 text-label-md text-primary transition-colors hover:bg-[#d4af37]/10"
                >
                    <Icon name="download" className="text-[18px]" />
                    Download template
                </a>
            </div>

            {canManage && (
                <details className="group border-t border-surface-container">
                    <summary className="flex cursor-pointer list-none items-center gap-2 px-space-lg py-3 text-label-md text-primary hover:bg-surface-container-low">
                        <Icon
                            name="chevron_right"
                            className="text-[20px] transition-transform group-open:rotate-90"
                        />
                        {hasOwnLetterhead
                            ? 'Replace or remove the club letterhead'
                            : 'Use a different letterhead for this club'}
                    </summary>
                    <div className="flex flex-col gap-space-md px-space-lg pb-space-lg">
                        <p className="text-body-sm text-on-surface-variant">
                            Upload a Word (.docx) file. Put{' '}
                            <code className="rounded bg-surface-container px-1">
                                [title]
                            </code>{' '}
                            and{' '}
                            <code className="rounded bg-surface-container px-1">
                                {'{date}'}
                            </code>{' '}
                            where the title and date go. Optional:{' '}
                            <code className="rounded bg-surface-container px-1">
                                {'{club_name}'}
                            </code>
                            ,{' '}
                            <code className="rounded bg-surface-container px-1">
                                {'{club_location}'}
                            </code>
                            ,{' '}
                            <code className="rounded bg-surface-container px-1">
                                {'{charter_number}'}
                            </code>{' '}
                            and{' '}
                            <code className="rounded bg-surface-container px-1">
                                {'{body}'}
                            </code>
                            . Tip: download the template above and edit it.
                        </p>
                        <Form
                            {...storeLetterhead.form(clubId)}
                            resetOnSuccess
                            options={{ preserveScroll: true }}
                            className="flex flex-col gap-3 sm:flex-row sm:items-start"
                        >
                            {({ errors, processing }) => (
                                <>
                                    <div className="flex flex-1 flex-col gap-1">
                                        <input
                                            type="file"
                                            name="letterhead"
                                            required
                                            accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                            className="text-body-sm text-on-surface-variant file:mr-3 file:rounded file:border-0 file:bg-primary-container file:px-4 file:py-2 file:text-label-md file:text-on-primary hover:file:bg-primary"
                                        />
                                        {errors.letterhead && (
                                            <p className="text-body-sm text-red-700">
                                                {errors.letterhead}
                                            </p>
                                        )}
                                    </div>
                                    <SubmitButton processing={processing}>
                                        Upload letterhead
                                    </SubmitButton>
                                </>
                            )}
                        </Form>
                        {hasOwnLetterhead && (
                            <button
                                type="button"
                                onClick={removeLetterhead}
                                className="self-start text-label-md text-red-700 hover:underline"
                            >
                                Remove club letterhead and use the district one
                            </button>
                        )}
                    </div>
                </details>
            )}
        </section>
    );
}

export default function ClubDocuments({
    club,
    documents,
    hasOwnLetterhead,
    canManageLetterhead,
}: {
    club: { id: number; name: string };
    documents: DocumentRow[];
    hasOwnLetterhead: boolean;
    canManageLetterhead: boolean;
}) {
    const { auth } = usePage().props;

    const deleteDocument = (document: DocumentRow) => {
        if (!window.confirm(`Delete “${document.title}”?`)) {
            return;
        }

        router.delete(destroy.url({ club: club.id, document: document.id }), {
            preserveScroll: true,
        });
    };

    const duplicateDocument = (document: DocumentRow) => {
        router.post(duplicate.url({ club: club.id, document: document.id }));
    };

    return (
        <AdminLayout
            title={`${club.name} Forms`}
            description="Forms, letters and memos, printed on your letterhead automatically."
            actions={
                <Link
                    href={create.url(club.id)}
                    className="inline-flex flex-1 items-center justify-center gap-2 rounded bg-primary-container px-4 py-2.5 text-label-md text-on-primary transition-colors hover:bg-[#1e3a8a] sm:flex-none"
                >
                    <Icon name="note_add" className="text-[20px]" />
                    <span>New form</span>
                </Link>
            }
        >
            <div className="flex flex-col gap-space-lg">
                <LetterheadPanel
                    clubId={club.id}
                    hasOwnLetterhead={hasOwnLetterhead}
                    canManage={canManageLetterhead}
                />

                {documents.length === 0 ? (
                    <div className="flex flex-col items-center gap-3 rounded-lg border-2 border-dashed border-outline-variant bg-surface-container-lowest px-6 py-16 text-center">
                        <Icon
                            name="draft"
                            className="text-[44px] text-outline"
                        />
                        <h2 className="font-serif text-title font-bold text-primary">
                            No forms yet
                        </h2>
                        <p className="max-w-sm text-body-md text-on-surface-variant">
                            Write a letter, memo or form once, then download it
                            as a Word file with the letterhead already in place.
                            Duplicate it whenever you need it again.
                        </p>
                        <Link
                            href={create.url(club.id)}
                            className="mt-2 inline-flex items-center gap-2 rounded bg-primary-container px-4 py-2.5 text-label-md text-on-primary hover:bg-[#1e3a8a]"
                        >
                            <Icon name="note_add" className="text-[20px]" />
                            Create the first form
                        </Link>
                    </div>
                ) : (
                    <ul className="flex flex-col gap-space-sm">
                        {documents.map((document) => (
                            <li
                                key={document.id}
                                className="flex flex-col gap-3 rounded-lg border border-[#d8dee4] bg-surface-container-lowest p-space-md shadow-[0_2px_4px_rgba(15,35,71,0.04)] transition-shadow hover:shadow-[0_8px_16px_rgba(15,35,71,0.08)] sm:flex-row sm:items-center"
                            >
                                <Link
                                    href={edit.url({
                                        club: club.id,
                                        document: document.id,
                                    })}
                                    className="flex min-w-0 flex-1 items-start gap-3"
                                >
                                    <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded bg-surface-container text-primary">
                                        <Icon
                                            name="article"
                                            className="text-[24px]"
                                        />
                                    </div>
                                    <div className="flex min-w-0 flex-col">
                                        <span className="truncate text-label-md text-primary">
                                            {document.title}
                                        </span>
                                        <span className="text-label-sm font-normal text-on-surface-variant">
                                            {longDate(document.document_date)}
                                            {document.author &&
                                                ` · by ${document.author}`}
                                        </span>
                                        {document.excerpt && (
                                            <span className="mt-1 line-clamp-1 text-body-sm text-outline">
                                                {document.excerpt}
                                            </span>
                                        )}
                                    </div>
                                </Link>
                                <div className="flex shrink-0 items-center gap-1 border-t border-surface-container pt-3 sm:border-0 sm:pt-0">
                                    <a
                                        href={download.url({
                                            club: club.id,
                                            document: document.id,
                                        })}
                                        className="inline-flex flex-1 items-center justify-center gap-2 rounded bg-primary-container px-3 py-2 text-label-md text-on-primary transition-colors hover:bg-[#1e3a8a] sm:flex-none"
                                    >
                                        <Icon
                                            name="download"
                                            className="text-[18px]"
                                        />
                                        .docx
                                    </a>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            duplicateDocument(document)
                                        }
                                        aria-label="Duplicate"
                                        title="Duplicate"
                                        className="flex h-9 w-9 items-center justify-center rounded text-primary hover:bg-surface-container"
                                    >
                                        <Icon
                                            name="content_copy"
                                            className="text-[20px]"
                                        />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => deleteDocument(document)}
                                        aria-label="Delete"
                                        title="Delete"
                                        className="flex h-9 w-9 items-center justify-center rounded text-red-700 hover:bg-red-50"
                                    >
                                        <Icon
                                            name="delete"
                                            className="text-[20px]"
                                        />
                                    </button>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}

                {auth.can.manageClubs && (
                    <Link
                        href={forms.url()}
                        className="self-start text-label-md text-primary hover:underline"
                    >
                        ← Back to all clubs
                    </Link>
                )}
            </div>
        </AdminLayout>
    );
}
