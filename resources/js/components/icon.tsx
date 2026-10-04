import type { CSSProperties } from 'react';
import { cn } from '@/lib/utils';

export function Icon({
    name,
    className,
    style,
}: {
    name: string;
    className?: string;
    style?: CSSProperties;
}) {
    return (
        <span
            aria-hidden="true"
            className={cn('material-symbols', className)}
            style={style}
        >
            {name}
        </span>
    );
}
