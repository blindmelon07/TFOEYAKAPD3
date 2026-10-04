import type { FundAllocationColor } from '@/types';

export const fundAllocationColorClasses: Record<FundAllocationColor, string> = {
    primary: 'bg-primary',
    'primary-container': 'bg-primary-container',
    secondary: 'bg-secondary',
    'secondary-container': 'bg-secondary-container',
};

export const fundAllocationColorLabels: Record<FundAllocationColor, string> = {
    primary: 'Deep navy',
    'primary-container': 'Navy',
    secondary: 'Dark gold',
    'secondary-container': 'Gold',
};
