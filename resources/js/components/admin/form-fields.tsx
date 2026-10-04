import { useState } from 'react';
import type { ReactNode } from 'react';
import { Icon } from '@/components/icon';
import { cn } from '@/lib/utils';

export const inputClassName =
    'w-full rounded border border-outline-variant bg-surface-container-lowest px-3 py-2.5 text-body-md text-on-surface shadow-[0_2px_4px_rgba(15,35,71,0.04)] transition outline-none focus:border-primary-container focus:ring-2 focus:ring-secondary-fixed-dim';

export function InputError({ message }: { message?: string }) {
    if (!message) {
        return null;
    }

    return <p className="text-body-sm text-red-700">{message}</p>;
}

export function FieldWrapper({
    name,
    label,
    help,
    error,
    children,
    className,
}: {
    name: string;
    label: string;
    help?: ReactNode;
    error?: string;
    children: ReactNode;
    className?: string;
}) {
    return (
        <div className={cn('flex flex-col gap-1.5', className)}>
            <label htmlFor={name} className="text-label-md text-primary">
                {label}
            </label>
            {children}
            {help && (
                <p className="text-body-sm text-on-surface-variant">{help}</p>
            )}
            <InputError message={error} />
        </div>
    );
}

export function TextField({
    name,
    label,
    defaultValue,
    error,
    help,
    type = 'text',
    required,
    className,
}: {
    name: string;
    label: string;
    defaultValue?: string | number | null;
    error?: string;
    help?: ReactNode;
    type?: 'text' | 'number' | 'email' | 'password';
    required?: boolean;
    className?: string;
}) {
    return (
        <FieldWrapper
            name={name}
            label={label}
            help={help}
            error={error}
            className={className}
        >
            <input
                id={name}
                name={name}
                type={type}
                defaultValue={defaultValue ?? ''}
                required={required}
                className={inputClassName}
            />
        </FieldWrapper>
    );
}

export function TextAreaField({
    name,
    label,
    defaultValue,
    error,
    help,
    required,
    className,
}: {
    name: string;
    label: string;
    defaultValue?: string | null;
    error?: string;
    help?: ReactNode;
    required?: boolean;
    className?: string;
}) {
    return (
        <FieldWrapper
            name={name}
            label={label}
            help={help}
            error={error}
            className={className}
        >
            <textarea
                id={name}
                name={name}
                rows={4}
                defaultValue={defaultValue ?? ''}
                required={required}
                className={inputClassName}
            />
        </FieldWrapper>
    );
}

export function SelectField({
    name,
    label,
    defaultValue,
    options,
    error,
    help,
    required,
}: {
    name: string;
    label: string;
    defaultValue?: string | null;
    options: { value: string; label: string }[];
    error?: string;
    help?: ReactNode;
    required?: boolean;
}) {
    return (
        <FieldWrapper name={name} label={label} help={help} error={error}>
            <select
                id={name}
                name={name}
                defaultValue={defaultValue ?? ''}
                required={required}
                className={inputClassName}
            >
                <option value="" disabled>
                    Choose…
                </option>
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
        </FieldWrapper>
    );
}

export function IconField({
    name,
    label,
    defaultValue,
    error,
    required,
}: {
    name: string;
    label: string;
    defaultValue?: string | null;
    error?: string;
    required?: boolean;
}) {
    const [iconName, setIconName] = useState(defaultValue ?? '');

    return (
        <FieldWrapper
            name={name}
            label={label}
            error={error}
            help={
                <>
                    Any icon name from{' '}
                    <a
                        href="https://fonts.google.com/icons"
                        target="_blank"
                        rel="noreferrer"
                        className="font-semibold text-primary underline underline-offset-2"
                    >
                        Google Material Symbols
                    </a>
                    , written with underscores (e.g.{' '}
                    <code>volunteer_activism</code>).
                </>
            }
        >
            <div className="flex items-center gap-3">
                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded bg-surface-container text-primary">
                    <Icon name={iconName || 'help'} className="text-[26px]" />
                </div>
                <input
                    id={name}
                    name={name}
                    type="text"
                    value={iconName}
                    onChange={(event) => setIconName(event.target.value)}
                    required={required}
                    className={inputClassName}
                />
            </div>
        </FieldWrapper>
    );
}

export function ImageField({
    name,
    label,
    currentUrl,
    error,
    help,
    removeFieldName,
}: {
    name: string;
    label: string;
    currentUrl?: string | null;
    error?: string;
    help?: ReactNode;
    removeFieldName?: string;
}) {
    const [previewUrl, setPreviewUrl] = useState(currentUrl ?? null);

    return (
        <FieldWrapper name={name} label={label} help={help} error={error}>
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div className="flex h-28 w-40 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-outline-variant bg-primary">
                    {previewUrl ? (
                        <img
                            src={previewUrl}
                            alt=""
                            className="h-full w-full object-contain"
                        />
                    ) : (
                        <Icon
                            name="image"
                            className="text-[32px] text-on-primary-container"
                        />
                    )}
                </div>
                <div className="flex flex-col gap-2">
                    <input
                        id={name}
                        name={name}
                        type="file"
                        accept="image/*"
                        onChange={(event) => {
                            const file = event.target.files?.[0];
                            setPreviewUrl(
                                file
                                    ? URL.createObjectURL(file)
                                    : (currentUrl ?? null),
                            );
                        }}
                        className="text-body-sm text-on-surface-variant file:mr-3 file:rounded file:border-0 file:bg-primary-container file:px-4 file:py-2 file:text-label-md file:text-on-primary hover:file:bg-primary"
                    />
                    {removeFieldName && currentUrl && (
                        <label className="flex items-center gap-2 text-body-sm text-on-surface-variant">
                            <input
                                type="checkbox"
                                name={removeFieldName}
                                value="1"
                                className="h-4 w-4 rounded accent-primary-container"
                            />
                            Remove current image
                        </label>
                    )}
                </div>
            </div>
        </FieldWrapper>
    );
}

export function SubmitButton({
    processing,
    children,
}: {
    processing: boolean;
    children: ReactNode;
}) {
    return (
        <button
            type="submit"
            disabled={processing}
            className="inline-flex items-center justify-center gap-2 rounded border border-primary-container bg-primary-container px-5 py-2.5 text-label-md text-on-primary transition-colors hover:bg-[#1e3a8a] disabled:opacity-60"
        >
            {processing && (
                <Icon
                    name="progress_activity"
                    className="animate-spin text-[18px]"
                />
            )}
            {children}
        </button>
    );
}
