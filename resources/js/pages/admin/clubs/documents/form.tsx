import { Form, Link } from '@inertiajs/react';
import { useState } from 'react';
import {
    FieldWrapper,
    inputClassName,
    SubmitButton,
} from '@/components/admin/form-fields';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { cn } from '@/lib/utils';
import { index, store, update } from '@/routes/admin/clubs/documents';

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
            <div className="mt-[3%] flex flex-col gap-[0.35rem] font-[family-name:Arial] text-[0.5rem] leading-snug text-black">
                {body.split('\n').map((line, lineNumber) => (
                    <p
                        key={lineNumber}
                        className="min-h-[0.5rem] text-justify whitespace-pre-wrap"
                    >
                        {line}
                    </p>
                ))}
            </div>
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
    const [body, setBody] = useState(document?.body ?? '');

    return (
        <AdminLayout
            title={document ? 'Edit document' : 'New document'}
            description={`${club.name} · printed on the ${hasOwnLetterhead ? 'club' : 'district'} letterhead`}
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
                                help="Each line becomes a paragraph. Leave a blank line for extra space."
                            >
                                <textarea
                                    id="body"
                                    name="body"
                                    rows={16}
                                    value={body}
                                    onChange={(event) =>
                                        setBody(event.target.value)
                                    }
                                    placeholder={
                                        'To: All Kuya and Ate of the club\nFrom: The Club Secretary\nSubject: …\n\nDear Brothers and Sisters,\n…'
                                    }
                                    className={cn(
                                        inputClassName,
                                        'font-[family-name:Arial] leading-relaxed',
                                    )}
                                />
                            </FieldWrapper>

                            <div className="flex flex-wrap items-center justify-end gap-3 border-t border-surface-container pt-space-lg">
                                <Link
                                    href={index.url(club.id)}
                                    className="rounded px-4 py-2.5 text-label-md text-primary hover:bg-surface-container"
                                >
                                    Cancel
                                </Link>
                                <SubmitButton processing={processing}>
                                    {document
                                        ? 'Save changes'
                                        : 'Save document'}
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
