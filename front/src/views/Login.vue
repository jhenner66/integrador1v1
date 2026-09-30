<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden font-sans">
    
    <!-- Fondo decorativo sutil -->
    <div class="absolute inset-0 z-0">
      <div class="absolute -top-24 -left-24 w-96 h-96 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
      <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
    </div>

    <!-- Tarjeta central de Login -->
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-3xl shadow-xl z-10 border border-gray-100">
      
      <!-- Logo y Título -->
      <div>
        <div class="mx-auto w-16 h-16 bg-teal-600 text-white flex items-center justify-center rounded-2xl shadow-lg mb-6 transform rotate-3">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
          </svg>
        </div>
        <h2 class="text-center text-3xl font-extrabold text-gray-900">D'Todo</h2>
        <p class="mt-2 text-center text-sm text-gray-500">Sistema de pedidos, ventas e inventario</p>
      </div>

      <!-- Formulario -->
      <form class="mt-8 space-y-5" @submit.prevent="handleLogin">
        
        <div>
          <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Usuario / Correo</label>
          <input id="email" v-model="email" type="email" required 
            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-800 bg-gray-50 focus:bg-white" 
            placeholder="admin@gmail.com">
        </div>

        <div>
          <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Contraseña</label>
          <input id="password" v-model="password" type="password" required 
            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-800 bg-gray-50 focus:bg-white" 
            placeholder="••••••••">
        </div>

        <div>
          <label for="rol" class="block text-sm font-semibold text-gray-700 mb-1">Rol de Ingreso</label>
          <select id="rol" v-model="rol" 
            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-800 bg-gray-50 focus:bg-white appearance-none">
            <option value="administrador">Administrador</option>
            <option value="vendedor">Vendedor</option>
            <option value="almacen">Almacén</option>
          </select>
        </div>

        <div class="pt-2">
          <button type="submit" 
            class="w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 shadow-md transition-all transform hover:-translate-y-0.5">
            INGRESAR AL SISTEMA
          </button>
        </div>
      </form>

      <!-- Recordatorio de credenciales -->
      <div class="mt-6 text-center bg-gray-50 p-4 rounded-xl border border-gray-100">
        <p class="text-xs text-gray-500 font-medium leading-relaxed">
          Demo: admin@gmail.com<br/>
          Clave universal: <span class="font-bold text-gray-700">password</span>
        </p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const email = ref('')
const password = ref('')
const rol = ref('')
const error = ref('')
const cargando = ref(false)

const auth = useAuthStore()
const router = useRouter()

async function handleLogin() {
  error.value = ''
  cargando.value = true
  try {
    await auth.login({ email: email.value, password: password.value, rol: rol.value || undefined })
    router.push({ name: 'dashboard' })
  } catch (e) {
    error.value = e.response?.data?.message || 'No se pudo iniciar sesión.'
  } finally {
    cargando.value = false
  }
}
</script>
