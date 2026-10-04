import { Form, Link } from '@inertiajs/react';
import {
    ImageField,
    SubmitButton,
    TextField,
} from '@/components/admin/form-fields';
import AdminLayout from '@/layouts/admin-layout';
import { index, store, update } from '@/routes/admin/clubs';

type Club = {
    id: number;
    name: string;
    location: string | null;
    charter_number: string | null;
    logo_url: string | null;
};

export default function ClubForm({ club }: { club: Club | null }) {
    return (
        <AdminLayout
            title={club ? `Edit ${club.name}` : 'Add club'}
            description="Clubs group members and officers. Only district admins can manage them."
        >
            <Form
                {...(club ? update.form(club.id) : store.form())}
                className="flex flex-col gap-space-lg rounded-lg border border-[#d8dee4] bg-surface-container-lowest p-space-lg shadow-[0_2px_4px_rgba(15,35,71,0.04)] md:p-space-xl"
            >
                {({ errors, processing }) => (
                    <>
                        <ImageField
                            name="logo"
                            label="Club logo"
                            currentUrl={club?.logo_url}
                            removeFieldName={club ? 'remove_logo' : undefined}
                            error={errors.logo}
                            help="A square PNG with a transparent background works best. Up to 5 MB."
                        />
                        <TextField
                            name="name"
                            label="Club name"
                            required
                            defaultValue={club?.name}
                            error={errors.name}
                            help='e.g. "Sorsogon City Eagles Club".'
                        />
                        <div className="grid grid-cols-1 gap-space-lg md:grid-cols-2">
                            <TextField
                                name="location"
                                label="Location"
                                defaultValue={club?.location}
                                error={errors.location}
                            />
                            <TextField
                                name="charter_number"
                                label="Charter number"
                                defaultValue={club?.charter_number}
                                error={errors.charter_number}
                            />
                        </div>
                        <div className="flex items-center justify-end gap-3 border-t border-surface-container pt-space-lg">
                            <Link
                                href={index.url()}
                                className="rounded px-4 py-2.5 text-label-md text-primary hover:bg-surface-container"
                            >
                                Cancel
                            </Link>
                            <SubmitButton processing={processing}>
                                {club ? 'Save changes' : 'Add club'}
                            </SubmitButton>
                        </div>
                    </>
                )}
            </Form>
        </AdminLayout>
    );
}
