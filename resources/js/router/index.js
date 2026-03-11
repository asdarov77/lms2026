//import { createRouter,createWebHistory } from 'vue-router';
import { createRouter, createWebHistory } from 'vue-router'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('@/Pages/Home.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/Pages/Auth/Login.vue'),
      meta: { guest: true }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/Pages/Dashboard.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/courses',
      name: 'courses',
      component: () => import('@/Pages/Courses.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/courses/list',
      name: 'courses.list',
      component: () => import('@/Pages/Courses.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/courses/my',
      name: 'my-courses',
      component: () => import('@/Pages/CoursesMy.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/courses/create',
      name: 'course-create',
      component: () => import('@/Pages/CourseCreate.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/courses/add',
      name: 'course.add',
      component: () => import('@/Pages/CourseCreate.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/users',
      name: 'users',
      component: () => import('@/Pages/Users.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/user/list',
      name: 'user.list',
      component: () => import('@/Pages/UserList.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/user/edit/:idEdit',
      name: 'user.edit',
      component: () => import('@/Pages/User/UserItemEdit.vue'),
      props: route => ({ idEdit: Number(route.params.idEdit) }),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/users/create',
      name: 'user-create',
      component: () => import('@/Pages/UserCreate.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/groups',
      name: 'groups',
      component: () => import('@/Pages/GroupList.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/groups/list',
      name: 'groups.index',
      component: () => import('@/Pages/GroupList.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/groups/add',
      name: 'groups.create',
      component: () => import('@/Pages/Group/CreateGroup.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/group-courses',
      name: 'group-courses',
      component: () => import('@/Pages/GroupCourses.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/group-courses/edit/:id',
      name: 'edit-enrollment',
      component: () => import('@/Pages/EditEnrollment.vue'),
      props: route => ({ id: Number(route.params.id) }),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/schedule',
      name: 'schedule',
      component: () => import('@/Pages/Calendar.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/learning-progress',
      name: 'learning-progress',
      component: () => import('@/Pages/Progress.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/assignments',
      name: 'assignments.index',
      component: () => import('@/Pages/Assignments/Index.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/assignments/create',
      name: 'assignments.create',
      component: () => import('@/Pages/Assignments/Create.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/assignments/my',
      name: 'assignments.my',
      component: () => import('@/Pages/Assignments/My.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/messages',
      name: 'messages',
      component: () => import('@/Pages/Messages.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/reports/analytics',
      name: 'reports.analytics',
      component: () => import('@/Pages/Reports/Analytics.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/reports/courses',
      name: 'reports.courses',
      component: () => import('@/Pages/Reports/Courses.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/reports/students',
      name: 'reports.students',
      component: () => import('@/Pages/Reports/Students.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/settings',
      name: 'settings',
      component: () => import('@/Pages/Settings.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'settings-profile',
          component: () => import('@/Pages/Settings/Profile.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'security',
          name: 'settings-security',
          component: () => import('@/Pages/Settings/Security.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'notifications',
          name: 'settings-notifications',
          component: () => import('@/Pages/Settings/Notifications.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'appearance',
          name: 'settings-appearance',
          component: () => import('@/Pages/Settings/Appearance.vue'),
          meta: { requiresAuth: true }
        }
      ]
    },
    {
      path: '/my',
      name: 'myaccount',
      component: () => import('@/Pages/Settings/Profile.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/test-page',
      name: 'test-page',
      component: () => import('@/Pages/TestPage.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/import-courses',
      name: 'importCourses',
      component: () => import('@/Pages/ImportCourses.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/categories',
      name: 'categories',
      component: () => import('@/Pages/Category/CategoryList.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/categories/list',
      name: 'categories.list',
      component: () => import('@/Pages/Category/CategoryList.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/categories/create',
      name: 'categories.store',
      component: () => import('@/Pages/Category/RegisterCategory.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/categories/edit/:idEdit',
      name: 'categories.update',
      component: () => import('@/Pages/Category/UpdateCategory.vue'),
      props: route => ({ idEdit: Number(route.params.idEdit) }),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('@/Pages/About.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/Pages/NotFound.vue')
    }
  ]
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  // If token exists but user data not loaded yet, load it first
  if (!authStore.user && localStorage.getItem('token')) {
    try {
      await authStore.fetchUser();
    } catch (err) {
      localStorage.removeItem('token');
      if (to.meta.requiresAuth) {
        return next({ name: 'login' });
      }
    }
  }

  const isAuthenticated = authStore.isAuthenticated;
  
  // Guest routes (login, register, etc.)
  if (to.meta.guest) {
    if (isAuthenticated) {
      return next({ name: 'dashboard' });
    }
    return next();
  }
  
  // Protected routes
  if (to.meta.requiresAuth) {
    if (!isAuthenticated) {
      return next({ name: 'login' });
    }
    
    // Check admin requirement
    if (to.meta.requiresAdmin) {
      if (!authStore.isAdmin) {
        return next({ name: 'dashboard' });
      }
    }
  }
  
  next();
});

export default router