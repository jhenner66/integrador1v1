import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  // Inicializamos leyendo del localStorage para que persista al recargar (F5)
  const token = ref(localStorage.getItem('dtodo_token') || null)
  const usuario = ref(JSON.parse(localStorage.getItem('dtodo_usuario')) || null)

  const estaAutenticado = computed(() => !!token.value)

  function iniciarSesion(nuevoToken, nuevoUsuario) {
    token.value = nuevoToken
    usuario.value = nuevoUsuario
    localStorage.setItem('dtodo_token', nuevoToken)
    localStorage.setItem('dtodo_usuario', JSON.stringify(nuevoUsuario))
  }

  function cerrarSesion() {
    token.value = null
    usuario.value = null
    localStorage.removeItem('dtodo_token')
    localStorage.removeItem('dtodo_usuario')
  }

  return { token, usuario, estaAutenticado, iniciarSesion, cerrarSesion }
})