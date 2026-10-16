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
        'Notifikasi',
        'Laporan',
    ],
    trainer: [
        'Dashboard',
        'Kelola course',
        'Pembelajaran saya',
        'Jalur belajar saya',
        'Katalog course',
        'Sertifikat',
        'Progres saya',
        'Notifikasi',
    ],
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
    management: [
        'Dashboard',
        'Pembelajaran saya',
        'Jalur belajar saya',
        'Katalog course',
        'Sertifikat',
        'Progres saya',
        'Notifikasi',
        'Laporan',
    ],
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
