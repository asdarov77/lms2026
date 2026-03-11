# Vuex / Pinia Store Design

Recommendation: Use Pinia (Vue 3 + Composition API). If keeping Vuex, use namespaced modules and follow same state shapes.

Modules:
- auth
  - state: { user, token, roles, isAuthenticated }
  - actions: login(credentials), logout(), refreshToken()
  - getters: isAdmin, canEdit(courseId)
- courses
  - state: { list: [], current: null, filters: {}, pagination: {} }
  - actions: fetchList(), fetchById(id), create(payload), update(id,payload), duplicate(id)
  - mutations: setList, setCurrent, addCourse, updateCourse, removeCourse
- lessons
  - state: { byCourse: { [courseId]: [] }, currentLesson: null }
  - actions: fetchList(courseId), reorder(courseId, order), uploadContent(...)
- tests
  - state: { templates: [], activeTest: null, results: {} }
  - actions: start(testId), submit(testId, answers)
- assignments
  - state: { submissions: [], gradingQueue: [] }
  - actions: submit(assignmentId, file), grade(submissionId, grade)
- users, notifications, ui, admin/analytics

Caching: cache course list for 5–15 minutes. Use ETag or server-side Last-Modified to avoid stale reads.

Optimistic updates: enroll/unenroll, course edit — apply optimistic update in store, then confirm with server; on error rollback previous state and show toast with retry.

Example auth store (Pinia):
```js
import { defineStore } from 'pinia'
export const useAuth = defineStore('auth', {
  state: ()=>({ user: null, token: null, roles: [] }),
  getters: { isAuthenticated: s => !!s.token, isAdmin: s => s.roles.includes('admin') },
  actions: {
    async login(credentials) {
      const res = await api.post('/api/v1/auth/login', credentials)
      this.token = res.data.token
      this.user = res.data.user
      this.roles = res.data.user.roles || []
    }
  }
})
```