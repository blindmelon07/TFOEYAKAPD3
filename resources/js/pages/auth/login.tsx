import { Form, Head, Link, usePage } from '@inertiajs/react';
import { SubmitButton, TextField } from '@/components/admin/form-fields';
import { home } from '@/routes';
import { store } from '@/routes/login';

export default function Login() {
    const { siteLogo: logo } = usePage().props;

    return (
        <>
            <Head title="Admin Login" />
            <div className="flex min-h-screen items-center justify-center bg-primary px-4 py-space-xl font-sans">
                <div className="w-full max-w-md">
                    <div className="mb-space-lg flex flex-col items-center gap-2 text-center">
                        {logo && (
                            <div className="relative mb-space-xs flex h-32 w-32 items-center justify-center">
                                <div className="absolute -inset-1 rounded-full bg-linear-to-r from-secondary-fixed via-secondary to-secondary-fixed opacity-40 blur-md" />
                                <img
                                    src={logo}
                                    alt="District logo"
                                    className="relative h-full w-full object-contain drop-shadow-lg"
                                />
                            </div>
                        )}
                        <h1 className="font-serif text-headline-md text-on-primary">
                            District Admin
                        </h1>
                        <p className="text-body-sm text-primary-fixed-dim">
                            Sign in to manage the landing page content.
                        </p>
                    </div>

                    <Form
                        {...store.form()}
                        resetOnError={['password']}
                        className="flex flex-col gap-space-lg rounded-lg border-t-[3px] border-secondary-fixed-dim bg-surface-container-lowest p-space-xl shadow-[0_16px_32px_rgba(15,35,71,0.2)]"
                    >
                        {({ errors, processing }) => (
                            <>
                                <TextField
                                    name="email"
                                    label="Email"
                                    type="email"
                                    required
                                    error={errors.email}
                                />
                                <TextField
                                    name="password"
                                    label="Password"
                                    type="password"
                                    required
                                    error={errors.password}
                                />
                                <label className="flex items-center gap-2 text-body-sm text-on-surface-variant">
                                    <input
                                        type="checkbox"
                                        name="remember"
                                        value="1"
                                        className="h-4 w-4 rounded accent-primary-container"
                                    />
                                    Keep me signed in
                                </label>
                                <SubmitButton processing={processing}>
                                    Sign in
                                </SubmitButton>
                            </>
                        )}
                    </Form>

                    <div className="mt-space-lg text-center">
                        <Link
                            href={home()}
                            className="text-label-md text-primary-fixed-dim hover:text-on-primary"
                        >
                            ← Back to website
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
