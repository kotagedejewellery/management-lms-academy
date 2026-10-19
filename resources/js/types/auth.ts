export type User = {
    id: number;
    name: string;
    email: string;
    employee_id?: string | null;
    status?: 'active' | 'inactive';
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
    isSuperAdmin: boolean;
    can: {
        manageUsers: boolean;
        manageOrganization: boolean;
        manageCourses: boolean;
        manageLearningPaths: boolean;
        manageEnrollments: boolean;
        viewReports: boolean;
        viewTeam: boolean;
    };
};
