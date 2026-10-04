import { Form, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import {
    ImageField,
    SelectField,
    SubmitButton,
    TextField,
} from '@/components/admin/form-fields';
import { Icon } from '@/components/icon';
import AdminLayout from '@/layouts/admin-layout';
import { index, show, store, update } from '@/routes/admin/members';
import type { ClubOption, SelectOption } from '@/types';

type EditableMember = {
    id: number;
    club_id: number;
    member_number: string | null;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    email: string | null;
    phone: string | null;
    address: string | null;
    photo_url: string | null;
    birthday: string | null;
    inducted_at: string | null;
    position: string;
    status: string;
    has_login: boolean;
};

function FormSection({
    title,
    icon,
    description,
    children,
}: {
    title: string;
    icon: string;
    description?: string;
    children: ReactNode;
}) {
    return (
        <section className="rounded-lg border border-[#d8dee4] bg-surface-container-lowest shadow-[0_2px_4px_rgba(15,35,71,0.04)]">
            <header className="flex items-start gap-3 border-b border-surface-container px-space-lg py-space-md">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-surface-container text-primary">
                    <Icon name={icon} className="text-[22px]" />
                </div>
                <div>
                    <h2 className="font-serif text-title font-bold text-primary">
                        {title}
                    </h2>
                    {description && (
                        <p className="text-body-sm text-on-surface-variant">
                            {description}
                        </p>
                    )}
                </div>
            </header>
            <div className="grid grid-cols-1 gap-space-lg p-space-lg md:grid-cols-2">
                {children}
            </div>
        </section>
    );
}

export default function MemberForm({
    member,
    clubs,
    clubName,
    positions,
    statuses,
    can,
}: {
    member: EditableMember | null;
    clubs: ClubOption[];
    clubName: string | null;
    positions: SelectOption[];
    statuses: SelectOption[];
    can: {
        changeClub: boolean;
        updateMembership: boolean;
        assignPosition: boolean;
    };
}) {
    const showsMembership =
        can.changeClub || can.updateMembership || can.assignPosition;

    return (
        <AdminLayout
            title={
                member
                    ? `Edit ${member.first_name} ${member.last_name}`
                    : 'Add member'
            }
            description={
                member
                    ? undefined
                    : clubName
                      ? `New member of ${clubName}.`
                      : 'Add a member to one of the District III clubs.'
            }
        >
            <Form
                {...(member ? update.form(member.id) : store.form())}
                resetOnSuccess={['password', 'password_confirmation', 'photo']}
                className="flex flex-col gap-space-lg"
            >
                {({ errors, processing, hasErrors }) => (
                    <>
                        {showsMembership && (
                            <FormSection
                                title="Membership"
                                icon="workspace_premium"
                                description="Club, office and standing within the order."
                            >
                                {can.changeClub ? (
                                    <SelectField
                                        name="club_id"
                                        label="Club"
                                        required
                                        defaultValue={
                                            member ? String(member.club_id) : ''
                                        }
                                        options={clubs.map((club) => ({
                                            value: String(club.id),
                                            label: club.name,
                                        }))}
                                        error={errors.club_id}
                                        help={
                                            clubs.length === 0
                                                ? 'Create a club first under Membership → Clubs.'
                                                : undefined
                                        }
                                    />
                                ) : (
                                    <div className="flex flex-col gap-1.5">
                                        <span className="text-label-md text-primary">
                                            Club
                                        </span>
                                        <span className="rounded bg-surface-container px-3 py-2.5 text-body-md text-on-surface-variant">
                                            {clubName}
                                        </span>
                                    </div>
                                )}
                                {can.assignPosition ? (
                                    <SelectField
                                        name="position"
                                        label="Position"
                                        required
                                        defaultValue={
                                            member?.position ?? 'member'
                                        }
                                        options={positions}
                                        error={errors.position}
                                        help="Each club can have only one of each officer position."
                                    />
                                ) : (
                                    <div className="flex flex-col gap-1.5">
                                        <span className="text-label-md text-primary">
                                            Position
                                        </span>
                                        <span className="rounded bg-surface-container px-3 py-2.5 text-body-md text-on-surface-variant">
                                            {
                                                positions.find(
                                                    (position) =>
                                                        position.value ===
                                                        (member?.position ??
                                                            'member'),
                                                )?.label
                                            }
                                        </span>
                                        <span className="text-body-sm text-on-surface-variant">
                                            Only the club president or a
                                            district admin can change positions.
                                        </span>
                                    </div>
                                )}
                                {can.updateMembership && (
                                    <>
                                        <TextField
                                            name="member_number"
                                            label="Member number"
                                            defaultValue={member?.member_number}
                                            error={errors.member_number}
                                            help="TFOE ID or chapter roll number."
                                        />
                                        <SelectField
                                            name="status"
                                            label="Status"
                                            required
                                            defaultValue={
                                                member?.status ?? 'active'
                                            }
                                            options={statuses}
                                            error={errors.status}
                                            help="Suspended and deceased members cannot sign in."
                                        />
                                        <TextField
                                            name="inducted_at"
                                            label="Date inducted"
                                            type="date"
                                            defaultValue={member?.inducted_at}
                                            error={errors.inducted_at}
                                        />
                                    </>
                                )}
                            </FormSection>
                        )}

                        <FormSection title="Personal details" icon="person">
                            <TextField
                                name="first_name"
                                label="First name"
                                required
                                defaultValue={member?.first_name}
                                error={errors.first_name}
                            />
                            <TextField
                                name="middle_name"
                                label="Middle name"
                                defaultValue={member?.middle_name}
                                error={errors.middle_name}
                            />
                            <TextField
                                name="last_name"
                                label="Last name"
                                required
                                defaultValue={member?.last_name}
                                error={errors.last_name}
                            />
                            <TextField
                                name="birthday"
                                label="Birthday"
                                type="date"
                                defaultValue={member?.birthday}
                                error={errors.birthday}
                            />
                            <div className="md:col-span-2">
                                <ImageField
                                    name="photo"
                                    label="Photo"
                                    currentUrl={member?.photo_url}
                                    removeFieldName={
                                        member ? 'remove_photo' : undefined
                                    }
                                    error={errors.photo}
                                    help="A clear head-and-shoulders photo, up to 5 MB."
                                />
                            </div>
                        </FormSection>

                        <FormSection title="Contact" icon="contact_phone">
                            <TextField
                                name="email"
                                label="Email"
                                type="email"
                                required={member?.has_login}
                                defaultValue={member?.email}
                                error={errors.email}
                                help={
                                    member?.has_login
                                        ? 'Also used to sign in.'
                                        : undefined
                                }
                            />
                            <TextField
                                name="phone"
                                label="Mobile number"
                                defaultValue={member?.phone}
                                error={errors.phone}
                            />
                            <TextField
                                name="address"
                                label="Address"
                                defaultValue={member?.address}
                                error={errors.address}
                                className="md:col-span-2"
                            />
                        </FormSection>

                        {can.updateMembership && (
                            <FormSection
                                title="Login"
                                icon="key"
                                description={
                                    member?.has_login
                                        ? 'This member can sign in with their email. Set a new password only to reset it.'
                                        : 'Set a password to let this member sign in with their email address.'
                                }
                            >
                                <TextField
                                    name="password"
                                    label={
                                        member?.has_login
                                            ? 'New password'
                                            : 'Password'
                                    }
                                    type="password"
                                    error={errors.password}
                                    help="Leave blank to keep things as they are."
                                />
                                <TextField
                                    name="password_confirmation"
                                    label="Confirm password"
                                    type="password"
                                />
                            </FormSection>
                        )}

                        <div className="sticky bottom-4 flex flex-wrap items-center justify-end gap-3 rounded-lg border border-[#d8dee4] bg-surface-container-lowest/95 px-space-lg py-space-md shadow-[0_8px_16px_rgba(15,35,71,0.08)] backdrop-blur-md">
                            {hasErrors && (
                                <p className="w-full text-body-sm text-red-700 sm:mr-auto sm:w-auto">
                                    Some fields need attention.
                                </p>
                            )}
                            <Link
                                href={
                                    member ? show.url(member.id) : index.url()
                                }
                                className="rounded px-4 py-2.5 text-label-md text-primary hover:bg-surface-container"
                            >
                                Cancel
                            </Link>
                            <SubmitButton processing={processing}>
                                {member ? 'Save changes' : 'Add member'}
                            </SubmitButton>
                        </div>
                    </>
                )}
            </Form>
        </AdminLayout>
    );
}
