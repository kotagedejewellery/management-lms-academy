import { describe, expect, it } from 'vitest';
import { buildSidebarNavigation, type SidebarCapabilities } from './sidebar';

const capabilityProfiles: Record<string, SidebarCapabilities> = {
    'super-admin': {
        manageCourses: true,
        manageLearningPaths: true,
        manageEnrollments: true,
        manageUsers: true,
        manageOrganization: true,
        isSuperAdmin: true,
        isTrainer: false,
    },
    'lms-admin': {
        manageCourses: true,
        manageLearningPaths: true,
        manageEnrollments: true,
        manageUsers: true,
        manageOrganization: true,
        isSuperAdmin: false,
        isTrainer: false,
    },
    trainer: {
        manageCourses: true,
        manageLearningPaths: false,
        manageEnrollments: false,
        manageUsers: false,
        manageOrganization: false,
        isSuperAdmin: false,
        isTrainer: true,
    },
    employee: {
        manageCourses: false,
        manageLearningPaths: false,
        manageEnrollments: false,
        manageUsers: false,
        manageOrganization: false,
        isSuperAdmin: false,
        isTrainer: false,
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
    employee: [
        'Dashboard',
        'Pembelajaran saya',
        'Jalur belajar saya',
        'Katalog course',
        'Sertifikat',
        'Progres saya',
        'Notifikasi',
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
