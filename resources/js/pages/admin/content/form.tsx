import { Form, Link } from '@inertiajs/react';
import {
    IconField,
    ImageField,
    SelectField,
    SubmitButton,
    TextAreaField,
    TextField,
} from '@/components/admin/form-fields';
import AdminLayout from '@/layouts/admin-layout';
import { adminResources, toDisplayText } from '@/lib/admin-resources';
import type { ContentItem, FieldDefinition } from '@/lib/admin-resources';

function ContentField({
    field,
    item,
    nextSortOrder,
    error,
}: {
    field: FieldDefinition;
    item: ContentItem | null;
    nextSortOrder?: number;
    error?: string;
}) {
    const rawValue =
        item?.[field.name] ??
        (field.name === 'sort_order' ? nextSortOrder : null);
    const defaultValue = toDisplayText(rawValue);

    switch (field.type) {
        case 'textarea':
            return (
                <TextAreaField
                    name={field.name}
                    label={field.label}
                    help={field.help}
                    required={field.required}
                    defaultValue={defaultValue}
                    error={error}
                />
            );
        case 'select':
            return (
                <SelectField
                    name={field.name}
                    label={field.label}
                    help={field.help}
                    required={field.required}
                    options={field.options ?? []}
                    defaultValue={defaultValue}
                    error={error}
                />
            );
        case 'icon':
            return (
                <IconField
                    name={field.name}
                    label={field.label}
                    required={field.required}
                    defaultValue={defaultValue}
                    error={error}
                />
            );
        case 'image':
            return (
                <ImageField
                    name={field.name}
                    label={field.label}
                    help={field.help}
                    currentUrl={
                        field.previewAttribute && item
                            ? (item[field.previewAttribute] as string | null)
                            : null
                    }
                    removeFieldName={item ? `remove_${field.name}` : undefined}
                    error={error}
                />
            );
        default:
            return (
                <TextField
                    name={field.name}
                    label={field.label}
                    help={field.help}
                    required={field.required}
                    type={field.type === 'number' ? 'number' : 'text'}
                    defaultValue={defaultValue}
                    error={error}
                />
            );
    }
}

export default function ContentForm({
    resource,
    item,
    nextSortOrder,
}: {
    resource: string;
    item: ContentItem | null;
    nextSortOrder?: number;
}) {
    const definition = adminResources[resource];
    const formAttributes = item
        ? definition.routes.update.form(item.id)
        : definition.routes.store.form();

    return (
        <AdminLayout
            title={
                item
                    ? `Edit ${definition.singular}`
                    : `Add ${definition.singular}`
            }
            description={definition.description}
        >
            <Form
                {...formAttributes}
                className="flex flex-col gap-space-lg rounded-lg border border-[#d8dee4] bg-surface-container-lowest p-space-lg shadow-[0_2px_4px_rgba(15,35,71,0.04)] md:p-space-xl"
            >
                {({ errors, processing }) => (
                    <>
                        {definition.fields.map((field) => (
                            <ContentField
                                key={field.name}
                                field={field}
                                item={item}
                                nextSortOrder={nextSortOrder}
                                error={errors[field.name]}
                            />
                        ))}

                        <div className="flex items-center justify-end gap-3 border-t border-surface-container pt-space-lg">
                            <Link
                                href={definition.routes.index.url()}
                                className="rounded px-4 py-2.5 text-label-md text-primary hover:bg-surface-container"
                            >
                                Cancel
                            </Link>
                            <SubmitButton processing={processing}>
                                {item
                                    ? 'Save changes'
                                    : `Add ${definition.singular}`}
                            </SubmitButton>
                        </div>
                    </>
                )}
            </Form>
        </AdminLayout>
    );
}
