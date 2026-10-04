import { Link, router } from '@inertiajs/react';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { adminResources, toDisplayText } from '@/lib/admin-resources';
import type { ContentItem } from '@/lib/admin-resources';
import { cn } from '@/lib/utils';

export default function ContentIndex({
    resource,
    items,
}: {
    resource: string;
    items: ContentItem[];
}) {
    const definition = adminResources[resource];

    const deleteItem = (item: ContentItem) => {
        if (
            !window.confirm(
                `Delete this ${definition.singular}? This cannot be undone.`,
            )
        ) {
            return;
        }

        router.delete(definition.routes.destroy.url(item.id), {
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout
            title={definition.title}
            description={definition.description}
            actions={
                <Link
                    href={definition.routes.create.url()}
                    className="inline-flex flex-1 items-center justify-center gap-2 rounded bg-primary-container px-4 py-2.5 text-label-md text-on-primary transition-colors hover:bg-[#1e3a8a] sm:flex-none"
                >
                    <Icon name="add" className="text-[20px]" />
                    <span>Add {definition.singular}</span>
                </Link>
            }
        >
            <div className="overflow-x-auto rounded-lg border border-[#d8dee4] bg-surface-container-lowest shadow-[0_2px_4px_rgba(15,35,71,0.04)]">
                {items.length === 0 ? (
                    <div className="flex flex-col items-center gap-3 px-6 py-16 text-center">
                        <Icon
                            name="inventory_2"
                            className="text-[40px] text-outline"
                        />
                        <p className="text-body-md text-on-surface-variant">
                            Nothing here yet. This section is hidden on the
                            website until you add a {definition.singular}.
                        </p>
                        <Link
                            href={definition.routes.create.url()}
                            className="text-label-md text-primary underline underline-offset-2"
                        >
                            Add the first {definition.singular}
                        </Link>
                    </div>
                ) : (
                    <>
                        <ul className="divide-y divide-[#d8dee4] sm:hidden">
                            {items.map((item) => (
                                <li
                                    key={item.id}
                                    className="flex items-center gap-3 px-4 py-3"
                                >
                                    <Link
                                        href={definition.routes.edit.url(
                                            item.id,
                                        )}
                                        className="flex min-w-0 flex-1 items-center gap-3 text-body-sm"
                                    >
                                        {definition.columns.map(
                                            (column, index) => (
                                                <div
                                                    key={index}
                                                    className={cn(
                                                        'min-w-0',
                                                        column.label === ''
                                                            ? 'shrink-0'
                                                            : 'flex-1',
                                                        column.className?.includes(
                                                            'hidden',
                                                        ) && 'hidden',
                                                    )}
                                                >
                                                    {column.render(item)}
                                                </div>
                                            ),
                                        )}
                                    </Link>
                                    <button
                                        type="button"
                                        aria-label="Delete"
                                        onClick={() => deleteItem(item)}
                                        className="flex h-10 w-10 shrink-0 items-center justify-center rounded text-red-700 hover:bg-red-50"
                                    >
                                        <Icon
                                            name="delete"
                                            className="text-[20px]"
                                        />
                                    </button>
                                </li>
                            ))}
                        </ul>
                        <table className="hidden w-full text-left text-body-sm sm:table">
                            <thead className="border-b border-[#d8dee4] bg-surface-container-low text-label-sm tracking-wider text-on-surface-variant uppercase">
                                <tr>
                                    <th className="w-16 px-4 py-3">Pos.</th>
                                    {definition.columns.map((column, index) => (
                                        <th
                                            key={index}
                                            className={cn(
                                                'px-4 py-3',
                                                column.className,
                                            )}
                                        >
                                            {column.label}
                                        </th>
                                    ))}
                                    <th className="px-4 py-3 text-right">
                                        <span className="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#d8dee4]">
                                {items.map((item) => (
                                    <tr
                                        key={item.id}
                                        className="transition-colors hover:bg-surface-container-low"
                                    >
                                        <td className="px-4 py-3 text-label-sm text-outline">
                                            {toDisplayText(item.sort_order)}
                                        </td>
                                        {definition.columns.map(
                                            (column, index) => (
                                                <td
                                                    key={index}
                                                    className={cn(
                                                        'px-4 py-3',
                                                        column.className,
                                                    )}
                                                >
                                                    {column.render(item)}
                                                </td>
                                            ),
                                        )}
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-end gap-1">
                                                <Link
                                                    href={definition.routes.edit.url(
                                                        item.id,
                                                    )}
                                                    aria-label="Edit"
                                                    className="flex h-9 w-9 items-center justify-center rounded text-primary hover:bg-surface-container"
                                                >
                                                    <Icon
                                                        name="edit"
                                                        className="text-[20px]"
                                                    />
                                                </Link>
                                                <button
                                                    type="button"
                                                    aria-label="Delete"
                                                    onClick={() =>
                                                        deleteItem(item)
                                                    }
                                                    className="flex h-9 w-9 items-center justify-center rounded text-red-700 hover:bg-red-50"
                                                >
                                                    <Icon
                                                        name="delete"
                                                        className="text-[20px]"
                                                    />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </>
                )}
            </div>
        </AdminLayout>
    );
}
