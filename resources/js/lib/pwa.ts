import { useSyncExternalStore } from 'react';

type BeforeInstallPromptEvent = Event & {
    prompt: () => Promise<void>;
    userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
};

let deferredInstallPrompt: BeforeInstallPromptEvent | null = null;
const listeners = new Set<() => void>();

function notifyListeners(): void {
    listeners.forEach((listener) => listener());
}

function subscribe(listener: () => void): () => void {
    listeners.add(listener);

    return () => listeners.delete(listener);
}

/**
 * Register the service worker and hold on to the browser's install prompt so
 * an "Install app" button can trigger it later. Safe to call during SSR.
 */
export function initializePwa(): void {
    if (typeof window === 'undefined') {
        return;
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event as BeforeInstallPromptEvent;
        notifyListeners();
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        notifyListeners();
    });

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {
                // Installing is optional; the site works without it.
            });
        });
    }
}

/**
 * Determine whether the app is already running as an installed app.
 */
function isStandalone(): boolean {
    return (
        window.matchMedia('(display-mode: standalone)').matches ||
        (navigator as Navigator & { standalone?: boolean }).standalone === true
    );
}

/**
 * Determine whether this is iOS Safari, which has no install prompt and needs
 * "Share → Add to Home Screen" instead.
 */
function needsManualIosInstall(): boolean {
    const isIos =
        /iphone|ipad|ipod/i.test(navigator.userAgent) ||
        (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

    return isIos && !isStandalone();
}

/**
 * React to whether the app can be installed, and trigger the install.
 */
export function useInstallPrompt(): {
    canPrompt: boolean;
    showIosInstructions: boolean;
    install: () => Promise<void>;
} {
    const canPrompt = useSyncExternalStore(
        subscribe,
        () => deferredInstallPrompt !== null,
        () => false,
    );
    const showIosInstructions = useSyncExternalStore(
        subscribe,
        needsManualIosInstall,
        () => false,
    );

    const install = async (): Promise<void> => {
        if (!deferredInstallPrompt) {
            return;
        }

        await deferredInstallPrompt.prompt();
        await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;
        notifyListeners();
    };

    return { canPrompt, showIosInstructions, install };
}
