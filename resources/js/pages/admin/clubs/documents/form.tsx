import { Form, Link } from '@inertiajs/react';
import { useState } from 'react';
import {
    FieldWrapper,
    inputClassName,
    SubmitButton,
} from '@/components/admin/form-fields';
import {
    RichTextEditor,
    toEditorHtml,
} from '@/components/admin/rich-text-editor';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { download, index, store, update } from '@/routes/admin/clubs/documents';

type EditableDocument = {
    id: number;
    title: string;
    document_date: string;
    body: string | null;
};

const longDate = (value: string) =>
    value
        ? new Date(`${value}T00:00:00`).toLocaleDateString('en-US', {
              year: 'numeric',
              month: 'long',
              day: 'numeric',
          })
        : '';

/**
 * A scaled-down long-bond page showing roughly how the .docx will look.
 */
function PaperPreview({
    title,
    date,
    body,
    hasOwnLetterhead,
}: {
    title: string;
    date: string;
    body: string;
    hasOwnLetterhead: boolean;
}) {
    return (
        <div className="aspect-[8.5/13] w-full overflow-hidden rounded bg-white px-[7%] py-[3%] shadow-[0_8px_16px_rgba(15,35,71,0.12)] ring-1 ring-[#d8dee4]">
            {hasOwnLetterhead ? (
                <div className="flex h-[6%] items-center justify-center rounded border border-dashed border-outline-variant text-[0.6rem] text-outline">
                    Club letterhead
                </div>
            ) : (
                <img
                    src="/images/letterhead-banner.png"
                    alt="District letterhead"
                    className="w-full"
                />
            )}
            <p className="mt-[3%] text-center font-[family-name:Arial] text-[0.7rem] font-bold text-black">
                {title || 'Title'}
            </p>
            <p className="mt-[2%] text-right font-[family-name:Arial] text-[0.55rem] text-black">
                {longDate(date)}
            </p>
            <div
                className="rich-text rich-text-preview mt-[3%] font-[family-name:Arial] text-[0.5rem] leading-snug text-black"
                // The HTML comes from the editor, whose schema only allows the
                // formatting configured in rich-text-editor.tsx.
                dangerouslySetInnerHTML={{ __html: body }}
            />
        </div>
    );
}

export default function DocumentForm({
    club,
    document,
    today,
    hasOwnLetterhead,
}: {
    club: { id: number; name: string };
    document: EditableDocument | null;
    today: string;
    hasOwnLetterhead: boolean;
}) {
    const [title, setTitle] = useState(document?.title ?? '');
    const [date, setDate] = useState(document?.document_date ?? today);
    const [body, setBody] = useState(() =>
        toEditorHtml(document?.body ?? null),
    );

    return (
        <AdminLayout
            title={document ? 'Edit form' : 'New form'}
            description={`${club.name} · printed on the ${hasOwnLetterhead ? 'club' : 'district'} letterhead`}
            actions={
                document && (
                    <a
                        href={download.url({
                            club: club.id,
                            document: document.id,
                        })}
                        onClick={(event) => {
                            const hasUnsavedChanges =
                                title !== document.title ||
                                date !== document.document_date ||
                                body !== toEditorHtml(document.body);

                            if (
                                hasUnsavedChanges &&
                                !window.confirm(
                                    'You have unsaved changes. The download uses the last saved version. Download anyway?',
                                )
                            ) {
                                event.preventDefault();
                            }
                        }}
                        className="inline-flex flex-1 items-center justify-center gap-2 rounded bg-primary-container px-4 py-2.5 text-label-md text-on-primary transition-colors hover:bg-[#1e3a8a] sm:flex-none"
                    >
                        <Icon name="download" className="text-[20px]" />
                        <span>Download .docx</span>
                    </a>
                )
            }
        >
            <div className="grid grid-cols-1 gap-space-lg lg:grid-cols-[minmax(0,1fr)_18rem]">
                <Form
                    {...(document
                        ? update.form({ club: club.id, document: document.id })
                        : store.form(club.id))}
                    className="flex flex-col gap-space-lg rounded-lg border border-[#d8dee4] bg-surface-container-lowest p-space-lg shadow-[0_2px_4px_rgba(15,35,71,0.04)] md:p-space-xl"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid grid-cols-1 gap-space-lg sm:grid-cols-[minmax(0,1fr)_12rem]">
                                <FieldWrapper
                                    name="title"
                                    label="Title"
                                    error={errors.title}
                                    help='Printed centered and bold, e.g. "MEMORANDUM" or "INVITATION".'
                                >
                                    <input
                                        id="title"
                                        name="title"
                                        required
                                        value={title}
                                        onChange={(event) =>
                                            setTitle(event.target.value)
                                        }
                                        className={inputClassName}
                                    />
                                </FieldWrapper>
                                <FieldWrapper
                                    name="document_date"
                                    label="Date"
                                    error={errors.document_date}
                                >
                                    <input
                                        id="document_date"
                                        name="document_date"
                                        type="date"
                                        required
                                        value={date}
                                        onChange={(event) =>
                                            setDate(event.target.value)
                                        }
                                        className={inputClassName}
                                    />
                                </FieldWrapper>
                            </div>

                            <FieldWrapper
                                name="body"
                                label="Content"
                                error={errors.body}
                                help="Bold, lists, alignment and tables all carry over into the Word file."
                            >
                                <RichTextEditor
                                    initialContent={body}
                                    onChange={setBody}
                                    hasError={Boolean(errors.body)}
                                />
                                <input type="hidden" name="body" value={body} />
                            </FieldWrapper>

                            <div className="flex flex-wrap items-center justify-end gap-3 border-t border-surface-container pt-space-lg">
                                <Link
                                    href={index.url(club.id)}
                                    className="rounded px-4 py-2.5 text-label-md text-primary hover:bg-surface-container"
                                >
                                    Cancel
                                </Link>
                                <SubmitButton processing={processing}>
                                    {document ? 'Save changes' : 'Save form'}
                                </SubmitButton>
                            </div>
                        </>
                    )}
                </Form>

                <aside className="flex flex-col gap-2 lg:sticky lg:top-24 lg:self-start">
                    <span className="flex items-center gap-1.5 text-label-sm tracking-wider text-on-surface-variant uppercase">
                        <Icon name="visibility" className="text-[16px]" />
                        Preview
                    </span>
                    <div className="mx-auto w-full max-w-72">
                        <PaperPreview
                            title={title}
                            date={date}
                            body={body}
                            hasOwnLetterhead={hasOwnLetterhead}
                        />
                    </div>
                    <p className="text-body-sm text-on-surface-variant">
                        Approximate. The downloaded Word file uses the exact
                        letterhead, fonts and 8.5 × 13 in page.
                    </p>
                </aside>
            </div>
        </AdminLayout>
    );
}
