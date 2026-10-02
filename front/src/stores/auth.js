import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || null,
    // Leemos el usuario del disco duro al recargar la página
    usuario: JSON.parse(localStorage.getItem('usuario') || 'null') 
  }),
  getters: {
    estaAutenticado: (state) => !!state.token,
    esAdministrador: (state) => state.usuario?.rol === 'administrador',
    esVendedor: (state) => state.usuario?.rol === 'vendedor',
    esAlmacen: (state) => state.usuario?.rol === 'almacen',
    esCliente: (state) => state.usuario?.rol === 'cliente'
  },
  actions: {
    // Función para guardar todo de forma sincronizada
    setSesion(token, usuario) {
      this.token = token
      this.usuario = usuario
      localStorage.setItem('token', token)
      localStorage.setItem('usuario', JSON.stringify(usuario))
      // Le decimos a Axios que use este token para futuras peticiones
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`
    },
    async login(credenciales) {
      const { data } = await api.post('/login', credenciales)
      this.setSesion(data.token, data.usuario)
    },
    logout() {
      this.token = null
      this.usuario = null
      localStorage.removeItem('token')
      localStorage.removeItem('usuario')
      delete api.defaults.headers.common['Authorization']
    }
  }
})