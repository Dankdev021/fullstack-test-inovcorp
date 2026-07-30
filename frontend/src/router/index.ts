import { createRouter, createWebHistory } from 'vue-router'
import ProjectDetailView from '../views/ProjectDetailView.vue'
import ProjectsView from '../views/ProjectsView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'projects',
      component: ProjectsView,
    },
    {
      path: '/projects/:id',
      name: 'project-detail',
      component: ProjectDetailView,
    },
  ],
})

export default router
