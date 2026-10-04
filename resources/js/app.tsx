import { createInertiaApp } from '@inertiajs/react';
import { initializePwa } from '@/lib/pwa';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: {
        color: '#e9c349',
    },
});

initializePwa();
