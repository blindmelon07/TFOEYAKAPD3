import { Fragment } from 'react';
import { cn } from '@/lib/utils';

const eaglesWordPattern = /(\bEagle(?:'s|’s|s)?\b)/;

/**
 * Renders heading text, setting every "Eagle", "Eagles" or "Eagle's" in the
 * chapter's script typeface so admin-entered headlines keep the brand look.
 */
export function EaglesText({
    text,
    scriptClassName,
}: {
    text: string | null | undefined;
    scriptClassName?: string;
}) {
    if (!text) {
        return null;
    }

    return text.split(eaglesWordPattern).map((part, index) =>
        eaglesWordPattern.test(part) ? (
            <span
                key={index}
                className={cn(
                    'inline-block font-ballpark leading-none font-normal tracking-[0.03em] normal-case',
                    scriptClassName,
                )}
            >
                {part}
            </span>
        ) : (
            <Fragment key={index}>{part}</Fragment>
        ),
    );
}
