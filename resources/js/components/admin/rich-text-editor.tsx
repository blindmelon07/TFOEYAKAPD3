import { TableKit } from '@tiptap/extension-table';
import TextAlign from '@tiptap/extension-text-align';
import { EditorContent, useEditor, useEditorState } from '@tiptap/react';
import type { Editor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import type { ReactNode } from 'react';
import { Icon } from '@/components/icon';
import { cn } from '@/lib/utils';

/**
 * Formatting the editor offers. Everything here is carried into the Word file
 * by App\Services\LetterheadRenderer, so keep the two in step.
 */
const extensions = [
    StarterKit.configure({
        heading: false,
        blockquote: false,
        code: false,
        codeBlock: false,
        horizontalRule: false,
        strike: false,
        link: false,
    }),
    TextAlign.configure({
        types: ['paragraph'],
        alignments: ['left', 'center', 'right', 'justify'],
    }),
    TableKit.configure({
        table: { resizable: false },
    }),
];

/**
 * Turn content saved before rich text existed (plain lines) into paragraphs.
 */
export function toEditorHtml(content: string | null): string {
    if (!content) {
        return '';
    }

    if (content.trimStart().startsWith('<')) {
        return content;
    }

    return content
        .split('\n')
        .map((line) =>
            line.trim() === ''
                ? '<p></p>'
                : `<p>${line.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</p>`,
        )
        .join('');
}

function ToolbarButton({
    label,
    icon,
    isActive = false,
    disabled = false,
    onClick,
    children,
}: {
    label: string;
    icon?: string;
    isActive?: boolean;
    disabled?: boolean;
    onClick: () => void;
    children?: ReactNode;
}) {
    return (
        <button
            type="button"
            title={label}
            aria-label={label}
            aria-pressed={isActive}
            disabled={disabled}
            onMouseDown={(event) => event.preventDefault()}
            onClick={onClick}
            className={cn(
                'flex h-9 min-w-9 shrink-0 items-center justify-center gap-1 rounded px-1.5 text-label-sm transition-colors disabled:opacity-35',
                isActive
                    ? 'bg-primary-container text-secondary-fixed'
                    : 'text-primary hover:bg-surface-container',
            )}
        >
            {icon && <Icon name={icon} className="text-[20px]" />}
            {children}
        </button>
    );
}

function Divider() {
    return <span className="mx-1 h-6 w-px shrink-0 bg-outline-variant" />;
}

function Toolbar({ editor }: { editor: Editor }) {
    const state = useEditorState({
        editor,
        selector: ({ editor: current }) => ({
            bold: current.isActive('bold'),
            italic: current.isActive('italic'),
            underline: current.isActive('underline'),
            bulletList: current.isActive('bulletList'),
            orderedList: current.isActive('orderedList'),
            alignLeft: current.isActive({ textAlign: 'left' }),
            alignCenter: current.isActive({ textAlign: 'center' }),
            alignRight: current.isActive({ textAlign: 'right' }),
            alignJustify: current.isActive({ textAlign: 'justify' }),
            inTable: current.isActive('table'),
            canUndo: current.can().undo(),
            canRedo: current.can().redo(),
        }),
    });

    const chain = () => editor.chain().focus();

    return (
        <div className="sticky top-16 z-10 flex flex-col border-b border-outline-variant bg-surface-container-low lg:top-20">
            <div className="flex items-center gap-0.5 overflow-x-auto px-1.5 py-1">
                <ToolbarButton
                    label="Bold (Ctrl+B)"
                    icon="format_bold"
                    isActive={state.bold}
                    onClick={() => chain().toggleBold().run()}
                />
                <ToolbarButton
                    label="Italic (Ctrl+I)"
                    icon="format_italic"
                    isActive={state.italic}
                    onClick={() => chain().toggleItalic().run()}
                />
                <ToolbarButton
                    label="Underline (Ctrl+U)"
                    icon="format_underlined"
                    isActive={state.underline}
                    onClick={() => chain().toggleUnderline().run()}
                />
                <Divider />
                <ToolbarButton
                    label="Bullet list"
                    icon="format_list_bulleted"
                    isActive={state.bulletList}
                    onClick={() => chain().toggleBulletList().run()}
                />
                <ToolbarButton
                    label="Numbered list"
                    icon="format_list_numbered"
                    isActive={state.orderedList}
                    onClick={() => chain().toggleOrderedList().run()}
                />
                <Divider />
                <ToolbarButton
                    label="Align left"
                    icon="format_align_left"
                    isActive={state.alignLeft}
                    onClick={() => chain().setTextAlign('left').run()}
                />
                <ToolbarButton
                    label="Center"
                    icon="format_align_center"
                    isActive={state.alignCenter}
                    onClick={() => chain().setTextAlign('center').run()}
                />
                <ToolbarButton
                    label="Align right"
                    icon="format_align_right"
                    isActive={state.alignRight}
                    onClick={() => chain().setTextAlign('right').run()}
                />
                <ToolbarButton
                    label="Justify"
                    icon="format_align_justify"
                    isActive={state.alignJustify}
                    onClick={() => chain().setTextAlign('justify').run()}
                />
                <Divider />
                <ToolbarButton
                    label="Insert table"
                    icon="table"
                    isActive={state.inTable}
                    onClick={() =>
                        chain()
                            .insertTable({
                                rows: 3,
                                cols: 3,
                                withHeaderRow: true,
                            })
                            .run()
                    }
                />
                <Divider />
                <ToolbarButton
                    label="Undo (Ctrl+Z)"
                    icon="undo"
                    disabled={!state.canUndo}
                    onClick={() => chain().undo().run()}
                />
                <ToolbarButton
                    label="Redo (Ctrl+Y)"
                    icon="redo"
                    disabled={!state.canRedo}
                    onClick={() => chain().redo().run()}
                />
            </div>

            {state.inTable && (
                <div className="flex items-center gap-0.5 overflow-x-auto border-t border-outline-variant bg-[#fffbeb] px-1.5 py-1">
                    <span className="mr-1 shrink-0 px-1 text-label-sm tracking-wider text-[#b45309] uppercase">
                        Table
                    </span>
                    <ToolbarButton
                        label="Add row below"
                        icon="add"
                        onClick={() => chain().addRowAfter().run()}
                    >
                        Row
                    </ToolbarButton>
                    <ToolbarButton
                        label="Add column right"
                        icon="add"
                        onClick={() => chain().addColumnAfter().run()}
                    >
                        Column
                    </ToolbarButton>
                    <ToolbarButton
                        label="Delete row"
                        icon="remove"
                        onClick={() => chain().deleteRow().run()}
                    >
                        Row
                    </ToolbarButton>
                    <ToolbarButton
                        label="Delete column"
                        icon="remove"
                        onClick={() => chain().deleteColumn().run()}
                    >
                        Column
                    </ToolbarButton>
                    <ToolbarButton
                        label="Toggle header row"
                        icon="table_rows"
                        onClick={() => chain().toggleHeaderRow().run()}
                    >
                        Header
                    </ToolbarButton>
                    <ToolbarButton
                        label="Merge or split cells"
                        icon="call_merge"
                        onClick={() => chain().mergeOrSplit().run()}
                    >
                        Merge
                    </ToolbarButton>
                    <Divider />
                    <ToolbarButton
                        label="Delete table"
                        icon="delete"
                        onClick={() => chain().deleteTable().run()}
                    >
                        Table
                    </ToolbarButton>
                </div>
            )}
        </div>
    );
}

/**
 * A rich text editor that reports its content as HTML ('' when empty).
 */
export function RichTextEditor({
    initialContent,
    onChange,
    hasError = false,
}: {
    initialContent: string;
    onChange: (html: string) => void;
    hasError?: boolean;
}) {
    const editor = useEditor({
        extensions,
        content: initialContent,
        immediatelyRender: false,
        editorProps: {
            attributes: {
                class: 'rich-text min-h-80 px-4 py-3 font-[family-name:Arial] text-body-md text-on-surface outline-none',
            },
        },
        onUpdate: ({ editor: current }) =>
            onChange(current.isEmpty ? '' : current.getHTML()),
    });

    return (
        <div
            className={cn(
                'overflow-clip rounded border bg-surface-container-lowest shadow-[0_2px_4px_rgba(15,35,71,0.04)] focus-within:border-primary-container focus-within:ring-2 focus-within:ring-secondary-fixed-dim',
                hasError ? 'border-red-600' : 'border-outline-variant',
            )}
        >
            {editor ? (
                <>
                    <Toolbar editor={editor} />
                    <EditorContent editor={editor} />
                </>
            ) : (
                <div className="flex min-h-80 animate-pulse flex-col gap-3 p-4">
                    <div className="h-8 rounded bg-surface-container" />
                    <div className="h-4 w-3/4 rounded bg-surface-container" />
                    <div className="h-4 w-1/2 rounded bg-surface-container" />
                </div>
            )}
        </div>
    );
}
