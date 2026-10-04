import { Form, usePage } from '@inertiajs/react';
import { SubmitButton, TextField } from '@/components/admin/form-fields';
import AdminLayout from '@/layouts/admin-layout';
import { update } from '@/routes/admin/account';

export default function Account() {
    const { auth } = usePage().props;

    return (
        <AdminLayout
            title="My Account"
            description="Your administrator login details."
        >
            <Form
                {...update.form()}
                resetOnSuccess={[
                    'current_password',
                    'password',
                    'password_confirmation',
                ]}
                options={{ preserveScroll: true }}
                className="flex flex-col gap-space-lg rounded-lg border border-[#d8dee4] bg-surface-container-lowest p-space-lg shadow-[0_2px_4px_rgba(15,35,71,0.04)] md:p-space-xl"
            >
                {({ errors, processing }) => (
                    <>
                        <div className="grid grid-cols-1 gap-space-lg md:grid-cols-2">
                            <TextField
                                name="name"
                                label="Name"
                                required
                                defaultValue={auth.user?.name}
                                error={errors.name}
                            />
                            <TextField
                                name="email"
                                label="Email"
                                type="email"
                                required
                                defaultValue={auth.user?.email}
                                error={errors.email}
                            />
                            <TextField
                                name="password"
                                label="New password"
                                type="password"
                                help="Leave blank to keep your current password."
                                error={errors.password}
                            />
                            <TextField
                                name="password_confirmation"
                                label="Confirm new password"
                                type="password"
                            />
                        </div>
                        <div className="flex flex-col gap-space-lg border-t border-surface-container pt-space-lg md:flex-row md:items-end">
                            <TextField
                                name="current_password"
                                label="Current password"
                                type="password"
                                required
                                help="Required to save any change."
                                error={errors.current_password}
                                className="md:flex-1"
                            />
                            <SubmitButton processing={processing}>
                                Save account
                            </SubmitButton>
                        </div>
                    </>
                )}
            </Form>
        </AdminLayout>
    );
}
