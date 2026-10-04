import { Form } from '@inertiajs/react';
import { SubmitButton, TextField } from '@/components/admin/form-fields';
import AuthLayout from '@/layouts/auth-layout';
import { store } from '@/routes/password';

export default function ResetPassword({
    token,
    email,
}: {
    token: string;
    email: string;
}) {
    return (
        <AuthLayout
            title="Choose a new password"
            description="Pick a password you haven't used here before."
        >
            <Form
                {...store.form()}
                resetOnError={['password', 'password_confirmation']}
                className="flex flex-col gap-space-lg"
            >
                {({ errors, processing }) => (
                    <>
                        <input type="hidden" name="token" value={token} />
                        <TextField
                            name="email"
                            label="Email"
                            type="email"
                            required
                            defaultValue={email}
                            error={errors.email}
                        />
                        <TextField
                            name="password"
                            label="New password"
                            type="password"
                            required
                            error={errors.password}
                        />
                        <TextField
                            name="password_confirmation"
                            label="Confirm new password"
                            type="password"
                            required
                        />
                        <SubmitButton processing={processing}>
                            Reset password
                        </SubmitButton>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
