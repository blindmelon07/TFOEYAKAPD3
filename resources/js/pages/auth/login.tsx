import { Form, Link } from '@inertiajs/react';
import { SubmitButton, TextField } from '@/components/admin/form-fields';
import AuthLayout from '@/layouts/auth-layout';
import { store } from '@/routes/login';
import { request as passwordRequest } from '@/routes/password';

export default function Login() {
    return (
        <AuthLayout
            title="Welcome Kuya & Ate!"
            description="Sign in to manage your club, membership and dues."
        >
            <Form
                {...store.form()}
                resetOnError={['password']}
                className="flex flex-col gap-space-lg"
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
                        <div className="flex flex-col gap-1.5">
                            <TextField
                                name="password"
                                label="Password"
                                type="password"
                                required
                                error={errors.password}
                            />
                            <Link
                                href={passwordRequest()}
                                className="self-end text-label-md text-primary underline-offset-2 hover:underline"
                            >
                                Forgot password?
                            </Link>
                        </div>
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
        </AuthLayout>
    );
}
