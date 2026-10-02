<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <div class="flex justify-center items-center gap-2 mb-4">
        <span class="bg-teal-600 text-white font-extrabold px-4 py-3 rounded-xl text-xl shadow-sm">DT</span>
      </div>
      <h2 class="text-3xl font-extrabold text-gray-900">D'Todo</h2>
      <p class="mt-2 text-sm text-gray-600">Bienvenido a nuestra plataforma</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow-sm sm:rounded-2xl sm:px-10 border border-gray-100">
        
        <!-- Selector de tipo de acceso -->
        <div class="flex p-1 bg-gray-100 rounded-xl mb-8">
          <button @click="cambiarPestana('cliente')" :class="tipoLogin === 'cliente' ? 'bg-white shadow-sm text-teal-700' : 'text-gray-500'" class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-all">🛍️ Soy Cliente</button>
          <button @click="cambiarPestana('personal')" :class="tipoLogin === 'personal' ? 'bg-white shadow-sm text-teal-700' : 'text-gray-500'" class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-all">👔 Soy Personal</button>
        </div>

        <!-- FORMULARIO CLIENTE (Login Inteligente / Registro) -->
        <form v-if="tipoLogin === 'cliente'" @submit.prevent="procesarCliente" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700">Número de DNI</label>
            <div class="mt-2">
              <input v-model="dni" type="text" maxlength="8" required :disabled="necesitaRegistro" :class="necesitaRegistro ? 'bg-gray-100 text-gray-500' : 'bg-white'" class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition-all" placeholder="Ej: 72839210" />
            </div>
            <p v-if="!necesitaRegistro" class="text-xs text-gray-500 mt-2">Ingresa tu DNI para ver nuestro catálogo.</p>
          </div>

          <!-- Campo de Nombre (Aparece suavemente si el DNI no está registrado) -->
          <div v-if="necesitaRegistro" class="transition-all transform duration-300">
            <label class="block text-sm font-medium text-gray-700">¿Cómo te llamas?</label>
            <div class="mt-2">
              <input v-model="nombreCliente" type="text" required class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm" placeholder="Ej: Juan Pérez" />
            </div>
            <p class="text-xs text-teal-600 mt-2 font-bold">¡Parece que eres nuevo! Déjanos tu nombre para registrarte rápido.</p>
          </div>

          <div v-if="errorMsg" class="text-red-500 text-sm font-semibold bg-red-50 p-3 rounded-lg">{{ errorMsg }}</div>
          
          <div class="flex gap-2">
            <button v-if="necesitaRegistro" type="button" @click="cancelarRegistro" class="w-1/3 py-3 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 transition-all">
              Volver
            </button>
            <button type="submit" :disabled="cargando" class="flex-1 flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-teal-600 hover:bg-teal-700 transition-all">
              {{ cargando ? 'Cargando...' : (necesitaRegistro ? 'Registrar e Ingresar' : 'Ingresar al Catálogo') }}
            </button>
          </div>
        </form>

        <!-- FORMULARIO PERSONAL (Email + Password) -->
        <form v-else @submit.prevent="ingresarComoPersonal" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
            <div class="mt-2">
              <input v-model="email" type="email" required class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm" placeholder="admin@dtodo.com" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Contraseña</label>
            <div class="mt-2">
              <input v-model="password" type="password" required class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm" placeholder="••••••••" />
            </div>
          </div>
          <div v-if="errorMsg" class="text-red-500 text-sm font-semibold bg-red-50 p-3 rounded-lg">{{ errorMsg }}</div>
          
          <button type="submit" :disabled="cargando" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-gray-800 hover:bg-gray-900 transition-all">
            {{ cargando ? 'Ingresando...' : 'Ingresar al Sistema' }}
          </button>
        </form>

      </div>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'

const router = useRouter()
const auth = useAuthStore()

const tipoLogin = ref('cliente')
const dni = ref('')
const nombreCliente = ref('')
const email = ref('')
const password = ref('')
const errorMsg = ref('')
const cargando = ref(false)

// Estado para saber si debemos mostrar el campo de Nombre
const necesitaRegistro = ref(false)

function cambiarPestana(tipo) {
  tipoLogin.value = tipo
  errorMsg.value = ''
  cancelarRegistro()
}

function cancelarRegistro() {
  necesitaRegistro.value = false
  nombreCliente.value = ''
}

async function procesarCliente() {
  errorMsg.value = ''
  cargando.value = true

  if (necesitaRegistro.value) {
    try {
      const { data } = await api.post('/registro-cliente', { 
        dni: dni.value, 
        nombre: nombreCliente.value 
      })
      
      // Usamos la nueva función del store para guardar todo perfectamente
      auth.setSesion(data.token, data.usuario || data.user)
      
      // Viajamos al catálogo sin recargar la página
      router.push('/catalogo')
      
    } catch (error) {
      errorMsg.value = 'El DNI ya existe o hubo un error al registrarte.'
    } finally {
      cargando.value = false
    }
  } else {
    try {
      const { data } = await api.post('/login-cliente', { dni: dni.value })
      
      // Usamos la nueva función del store
      auth.setSesion(data.token, data.usuario || data.user)
      
      // Viajamos al catálogo sin recargar la página
      router.push('/catalogo')
      
    } catch (error) {
      if (error.response?.status === 404) {
        necesitaRegistro.value = true
      } else {
        errorMsg.value = 'Error de conexión con el servidor.'
      }
    } finally {
      cargando.value = false
    }
  }
}

async function ingresarComoPersonal() {
  errorMsg.value = ''
  cargando.value = true
  try {
    await auth.login({ email: email.value, password: password.value })
    const rol = (auth.usuario?.rol || auth.user?.rol || '').toLowerCase()
    if (rol === 'vendedor') router.push('/ventas')
    else if (rol === 'almacen') router.push('/inventario')
    else router.push('/dashboard')
  } catch (error) {
    errorMsg.value = 'Correo o contraseña incorrectos.'
  } finally {
    cargando.value = false
  }
}
</script>