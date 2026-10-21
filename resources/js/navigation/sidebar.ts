import {
    Award,
    Bell,
    BookOpen,
    GraduationCap,
    LayoutGrid,
    ListTree,
    Network,
    ShieldCheck,
    Users,
} from '@lucide/vue';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

export type SidebarCapabilities = {
    manageCourses: boolean;
    manageLearningPaths: boolean;
    manageEnrollments: boolean;
    manageUsers: boolean;
    manageOrganization: boolean;
    viewReports: boolean;
    viewTeam: boolean;
    isSuperAdmin: boolean;
};

type NavigationGroup = {
    title: string;
    items: NavItem[];
};

export function buildSidebarNavigation(
    capabilities: SidebarCapabilities,
): NavigationGroup[] {
    const learning: NavItem[] = [
        ...(capabilities.manageCourses
            ? [
                  {
                      title: 'Kelola course',
                      href: '/courses',
                      icon: GraduationCap,
                  },
              ]
            : []),
        ...(capabilities.manageCourses ||
        capabilities.manageEnrollments ||
        capabilities.viewReports
            ? [
                  {
                      title: 'Progres course',
                      href: '/trainer-progress',
                      icon: ListTree,
                  },
              ]
            : []),
        ...(capabilities.manageLearningPaths
            ? [
                  {
                      title: 'Kelola jalur belajar',
                      href: '/learning-paths',
                      icon: ListTree,
                  },
              ]
            : []),
        ...(!capabilities.manageEnrollments
            ? [
                  {
                      title: 'Pembelajaran saya',
                      href: '/my-learning',
                      icon: GraduationCap,
                  },
                  {
                      title: 'Jalur belajar saya',
                      href: '/my-learning-paths',
                      icon: ListTree,
                  },
                  {
                      title: 'Katalog course',
                      href: '/course-library',
                      icon: BookOpen,
                  },
              ]
            : []),
        { title: 'Sertifikat', href: '/certificates', icon: Award },
    ];
    const management: NavItem[] = [
        ...(capabilities.manageUsers
            ? [{ title: 'Pengguna', href: '/users', icon: Users }]
            : []),
        ...(capabilities.manageOrganization
            ? [
                  {
                      title: 'Organisasi',
                      href: '/organization/divisions',
                      icon: Network,
                  },
              ]
            : []),
        ...(capabilities.manageEnrollments
            ? [
                  { title: 'Penugasan', href: '/enrollments', icon: Users },
                  {
                      title: 'Penugasan jalur',
                      href: '/learning-path-assignments',
                      icon: ListTree,
                  },
              ]
            : []),
    ];
    const monitoring: NavItem[] = [
        ...(!capabilities.manageEnrollments
            ? [
                  {
                      title: 'Progres saya',
                      href: '/my-progress',
                      icon: BookOpen,
                  },
              ]
            : []),
        ...(capabilities.manageEnrollments
            ? [
                  {
                      title: 'Pengumuman',
                      href: '/announcements',
                      icon: Bell,
                  },
              ]
            : []),
        { title: 'Notifikasi', href: '/notifications', icon: Bell },
        ...(capabilities.manageEnrollments || capabilities.viewReports
            ? [{ title: 'Laporan', href: '/reports', icon: BookOpen }]
            : []),
        ...(capabilities.viewTeam
            ? [{ title: 'Progres tim', href: '/team-progress', icon: Users }]
            : []),
        ...(capabilities.isSuperAdmin
            ? [
                  {
                      title: 'Aktivitas audit',
                      href: '/audit-logs',
                      icon: ShieldCheck,
                  },
              ]
            : []),
    ];

    return [
        {
            title: 'Utama',
            items: [
                {
                    title: 'Dashboard',
                    href: dashboard.url(),
                    icon: LayoutGrid,
                },
            ],
        },
        ...(learning.length
            ? [{ title: 'Pembelajaran', items: learning }]
            : []),
        ...(management.length ? [{ title: 'Kelola', items: management }] : []),
        { title: 'Pantau', items: monitoring },
    ];
}
