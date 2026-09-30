import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('dtodo_token') || null,
    usuario: JSON.parse(localStorage.getItem('dtodo_usuario') || 'null'),
  }),

  getters: {
    estaAutenticado: (state) => !!state.token,
    rol: (state) => state.usuario?.rol || null,
    esAdministrador: (state) => state.usuario?.rol === 'administrador',
    esAlmacen: (state) => state.usuario?.rol === 'almacen',
    esVendedor: (state) => state.usuario?.rol === 'vendedor',
  },

  actions: {
    async login({ email, password, rol }) {
      const { data } = await api.post('/login', { email, password, rol })
      this.token = data.token
      this.usuario = data.usuario
      localStorage.setItem('dtodo_token', data.token)
      localStorage.setItem('dtodo_usuario', JSON.stringify(data.usuario))
    },

    async logout() {
      try {
        await api.post('/logout')
      } catch (e) {
        // Continuar aunque falle la petición al backend
      }
      this.token = null
      this.usuario = null
      localStorage.removeItem('dtodo_token')
      localStorage.removeItem('dtodo_usuario')
    },
  },
})
