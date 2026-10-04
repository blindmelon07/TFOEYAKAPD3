import { Form, Link } from '@inertiajs/react';
import { SubmitButton, TextField } from '@/components/admin/form-fields';
import AuthLayout from '@/layouts/auth-layout';
import { login } from '@/routes';
import { email } from '@/routes/password';

export default function ForgotPassword() {
    return (
        <AuthLayout
            title="Forgot password"
            description="Enter the email you sign in with and we'll send you a link to choose a new password."
            footer={
                <Link
                    href={login()}
                    className="text-label-md text-secondary-fixed hover:underline"
                >
                    Remembered it? Sign in
                </Link>
            }
        >
            <Form
                {...email.form()}
                resetOnSuccess
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
                        <SubmitButton processing={processing}>
                            Email me a reset link
                        </SubmitButton>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
