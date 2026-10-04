import { useState } from 'react';
import { Icon } from '@/components/icon';
import { useInstallPrompt } from '@/lib/pwa';
import { cn } from '@/lib/utils';

/**
 * Offers to install District III as an app. Shows the browser's install
 * prompt where supported, and step-by-step instructions on iPhone and iPad.
 * Renders nothing when the app is installed or cannot be installed.
 */
export function InstallAppButton({
    variant,
    className,
}: {
    variant: 'menu-item' | 'banner';
    className?: string;
}) {
    const { canPrompt, showIosInstructions, install } = useInstallPrompt();
    const [isShowingIosSteps, setIsShowingIosSteps] = useState(false);

    if (!canPrompt && !showIosInstructions) {
        return null;
    }

    const handleClick = () => {
        if (canPrompt) {
            void install();

            return;
        }

        setIsShowingIosSteps((showing) => !showing);
    };

    const iosSteps = isShowingIosSteps && (
        <ol className="flex list-decimal flex-col gap-1 pl-5 text-body-sm">
            <li>
                Tap the <strong>Share</strong> button{' '}
                <Icon name="ios_share" className="align-middle text-[16px]" />{' '}
                in Safari.
            </li>
            <li>
                Choose <strong>Add to Home Screen</strong>.
            </li>
            <li>
                Tap <strong>Add</strong>.
            </li>
        </ol>
    );

    if (variant === 'menu-item') {
        return (
            <div className={className}>
                <button
                    type="button"
                    role="menuitem"
                    onClick={handleClick}
                    className="flex w-full items-center gap-3 px-4 py-2.5 text-left text-label-md text-on-surface transition-colors hover:bg-surface-container-low"
                >
                    <Icon
                        name="install_mobile"
                        className="text-[20px] text-primary"
                    />
                    Install app
                </button>
                {iosSteps && (
                    <div className="px-4 pb-3 text-on-surface-variant">
                        {iosSteps}
                    </div>
                )}
            </div>
        );
    }

    return (
        <div
            className={cn(
                'flex flex-col gap-2 rounded-lg border border-white/15 bg-white/5 px-4 py-3 text-left text-primary-fixed-dim',
                className,
            )}
        >
            <button
                type="button"
                onClick={handleClick}
                className="flex items-center gap-3 text-label-md text-on-primary"
            >
                <Icon
                    name="install_mobile"
                    className="text-[22px] text-secondary-fixed"
                />
                <span className="flex-1">
                    Install District III on this device
                </span>
                <Icon
                    name={canPrompt ? 'download' : 'expand_more'}
                    className="text-[20px]"
                />
            </button>
            {iosSteps}
        </div>
    );
}
