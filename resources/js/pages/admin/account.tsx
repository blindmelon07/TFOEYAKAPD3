import { Form, Link, usePage } from '@inertiajs/react';
import { SubmitButton, TextField } from '@/components/admin/form-fields';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { update } from '@/routes/admin/account';
import { edit as memberEdit } from '@/routes/admin/members';

export default function Account({
    identityManagedByProfile,
}: {
    identityManagedByProfile: boolean;
}) {
    const { auth } = usePage().props;

    return (
        <AdminLayout
            title="Login & Password"
            description="The details you use to sign in."
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
                        {identityManagedByProfile && auth.memberId ? (
                            <div className="flex items-start gap-3 rounded bg-surface-container-low px-4 py-3 text-body-sm text-on-surface-variant">
                                <Icon
                                    name="info"
                                    className="text-[20px] text-primary"
                                />
                                <p>
                                    You sign in as{' '}
                                    <strong className="text-primary">
                                        {auth.user?.email}
                                    </strong>
                                    . To change your name or email, edit{' '}
                                    <Link
                                        href={memberEdit.url(auth.memberId)}
                                        className="font-semibold text-primary underline underline-offset-2"
                                    >
                                        your member profile
                                    </Link>
                                    .
                                </p>
                            </div>
                        ) : (
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
                            </div>
                        )}
                        <div className="grid grid-cols-1 gap-space-lg md:grid-cols-2">
                            <TextField
                                name="password"
                                label="New password"
                                type="password"
                                required={identityManagedByProfile}
                                help={
                                    identityManagedByProfile
                                        ? undefined
                                        : 'Leave blank to keep your current password.'
                                }
                                error={errors.password}
                            />
                            <TextField
                                name="password_confirmation"
                                label="Confirm new password"
                                type="password"
                                required={identityManagedByProfile}
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
                                {identityManagedByProfile
                                    ? 'Change password'
                                    : 'Save account'}
                            </SubmitButton>
                        </div>
                    </>
                )}
            </Form>
        </AdminLayout>
    );
}
