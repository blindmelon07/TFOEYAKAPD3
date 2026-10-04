export type User = {
    id: number;
    name: string;
    email: string;
    role: 'district_admin' | 'member';
    position: string | null;
};

export type Auth = {
    user: User | null;
    memberId: number | null;
    officerClubId: number | null;
    can: {
        manageSite: boolean;
        manageClubs: boolean;
        viewMembers: boolean;
        useForms: boolean;
    };
};
