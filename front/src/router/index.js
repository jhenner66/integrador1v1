import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/Login.vue'),
    meta: { public: true },
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('../views/Dashboard.vue'),
    meta: { roles: ['administrador'] },
  },
  {
    path: '/inventario',
    name: 'inventario',
    component: () => import('../views/Inventario.vue'),
    meta: { roles: ['administrador', 'almacen'] },
  },
  {
    path: '/pedidos',
    name: 'pedidos',
    component: () => import('../views/Pedidos.vue'),
    meta: { roles: ['administrador', 'almacen'] },
  },
  {
    path: '/ventas',
    name: 'ventas',
    component: () => import('../views/Ventas.vue'),
    meta: { roles: ['administrador', 'vendedor'] },
  },
  {
    path: '/alertas',
    name: 'alertas',
    component: () => import('../views/Alertas.vue'),
    meta: { roles: ['administrador'] },
  },
  // NUEVA RUTA EXCLUSIVA PARA EL CLIENTE
  {
    path: '/catalogo',
    name: 'catalogo',
    component: () => import('../views/Catalogo.vue'),
    meta: { roles: ['administrador', 'cliente'] },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (!to.meta.public && !auth.estaAutenticado) {
    return { name: 'login' }
  }

  if (to.name === 'login' && auth.estaAutenticado) {
    const rol = (auth.usuario?.rol || auth.user?.rol || '').toLowerCase()
    if (rol === 'vendedor') return { name: 'ventas' }
    if (rol === 'almacen') return { name: 'inventario' }
    if (rol === 'cliente') return { name: 'catalogo' } // Redirigir al cliente
    return { name: 'dashboard' }
  }

  const rolesPermitidos = to.meta.roles
  if (rolesPermitidos && auth.estaAutenticado) {
    const rolUsuario = (auth.usuario?.rol || auth.user?.rol || '').toLowerCase()
    
    if (!rolesPermitidos.includes(rolUsuario)) {
      if (rolUsuario === 'vendedor') return { name: 'ventas' }
      if (rolUsuario === 'almacen') return { name: 'inventario' }
      if (rolUsuario === 'cliente') return { name: 'catalogo' } // Bloquear y enviar a catálogo
      return { name: 'dashboard' }
    }
  }
})

export default router