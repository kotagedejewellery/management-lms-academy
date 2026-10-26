import { describe, expect, it } from 'vitest';
import { buildSidebarNavigation, type SidebarCapabilities } from './sidebar';

const capabilityProfiles: Record<string, SidebarCapabilities> = {
    'super-admin': {
        manageCourses: true,
        manageLearningPaths: true,
        manageEnrollments: true,
        manageUsers: true,
        manageOrganization: true,
        viewReports: true,
        viewTeam: true,
        isSuperAdmin: true,
        isTrainer: false,
        isManagement: false,
    },
    'lms-admin': {
        manageCourses: true,
        manageLearningPaths: true,
        manageEnrollments: true,
        manageUsers: true,
        manageOrganization: true,
        viewReports: false,
        viewTeam: false,
        isSuperAdmin: false,
        isTrainer: false,
        isManagement: false,
    },
    trainer: {
        manageCourses: true,
        manageLearningPaths: false,
        manageEnrollments: false,
        manageUsers: false,
        manageOrganization: false,
        viewReports: false,
        viewTeam: false,
        isSuperAdmin: false,
        isTrainer: true,
        isManagement: false,
    },
    manager: {
        manageCourses: false,
        manageLearningPaths: false,
        manageEnrollments: false,
        manageUsers: false,
        manageOrganization: false,
        viewReports: false,
        viewTeam: true,
        isSuperAdmin: false,
        isTrainer: false,
        isManagement: false,
    },
    employee: {
        manageCourses: false,
        manageLearningPaths: false,
        manageEnrollments: false,
        manageUsers: false,
        manageOrganization: false,
        viewReports: false,
        viewTeam: false,
        isSuperAdmin: false,
        isTrainer: false,
        isManagement: false,
    },
    management: {
        manageCourses: false,
        manageLearningPaths: false,
        manageEnrollments: false,
        manageUsers: false,
        manageOrganization: false,
        viewReports: true,
        viewTeam: false,
        isSuperAdmin: false,
        isTrainer: false,
        isManagement: true,
    },
};

const expectedItems: Record<string, string[]> = {
    'super-admin': [
        'Dashboard',
        'Kelola course',
        'Kelola jalur belajar',
        'Sertifikat',
        'Pengguna',
        'Organisasi',
        'Penugasan',
        'Penugasan jalur',
        'Pengumuman',
        'Progres peserta',
        'Notifikasi',
        'Laporan',
        'Progres tim',
        'Aktivitas audit',
    ],
    'lms-admin': [
        'Dashboard',
        'Kelola course',
        'Kelola jalur belajar',
        'Sertifikat',
        'Pengguna',
        'Organisasi',
        'Penugasan',
        'Penugasan jalur',
        'Pengumuman',
        'Progres peserta',
        'Notifikasi',
        'Laporan',
    ],
    trainer: ['Dashboard', 'Course binaan', 'Progres peserta', 'Notifikasi'],
    manager: [
        'Dashboard',
        'Pembelajaran saya',
        'Jalur belajar saya',
        'Katalog course',
        'Sertifikat',
        'Progres saya',
        'Notifikasi',
        'Progres tim',
    ],
    employee: [
        'Dashboard',
        'Pembelajaran saya',
        'Jalur belajar saya',
        'Katalog course',
        'Sertifikat',
        'Progres saya',
        'Notifikasi',
    ],
    management: ['Dashboard', 'Progres peserta', 'Notifikasi', 'Laporan'],
};

describe('sidebar navigation', () => {
    it('uses a path string for the dashboard link', () => {
        const dashboardItem = buildSidebarNavigation(
            capabilityProfiles.employee,
        )[0].items[0];

        expect(dashboardItem.href).toBe('/dashboard');
    });

    it.each(Object.keys(capabilityProfiles))(
        'shows the approved items for %s',
        (role) => {
            const items = buildSidebarNavigation(capabilityProfiles[role])
                .flatMap((group) => group.items)
                .map((item) => item.title);

            expect(items).toEqual(expectedItems[role]);
        },
    );
});
