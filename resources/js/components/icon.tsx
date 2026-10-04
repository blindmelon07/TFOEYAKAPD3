import { cn } from '@/lib/utils';

export function Icon({
    name,
    className,
}: {
    name: string;
    className?: string;
}) {
    return (
        <span aria-hidden="true" className={cn('material-symbols', className)}>
            {name}
        </span>
    );
}
