<template>
  <div class="min-h-screen flex bg-white font-sans text-[#234E35]">
    
    <!-- LADO IZQUIERDO: Banner e Imagen -->
    <div class="hidden lg:flex lg:w-1/2 bg-[#F1F6F0] p-12 flex-col justify-between rounded-r-3xl my-2 border-r border-[#E0EBE2]">
      <div class="flex items-center gap-3">
        <div class="bg-[#234E35] text-white p-2 rounded-xl">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-bold leading-tight">D' Todo</h1>
          <p class="text-[10px] font-semibold tracking-widest text-gray-500 uppercase">Minimarket</p>
        </div>
      </div>
      <div class="mt-12 mb-8">
        <h2 class="text-5xl xl:text-6xl font-bold leading-tight mb-6">
          Tu barrio.<br>Tu tienda.<br>Como siempre.
        </h2>
        <p class="text-lg text-[#3A704F] max-w-md">
          Lo que necesitas, cerquita de ti. Con la frescura y el cariño de todos los días.
        </p>
      </div>
      <div class="relative rounded-2xl overflow-hidden shadow-lg h-64 xl:h-80 w-full max-w-lg">
        <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&q=80&w=1000" alt="Tienda D'Todo" class="object-cover w-full h-full" />
        <div class="absolute bottom-4 left-4 right-4 bg-[#234E35]/90 backdrop-blur-sm text-white p-3 rounded-xl flex items-center gap-3">
          <div class="bg-[#D2E4D5] text-[#234E35] p-2 rounded-full">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          </div>
          <div>
            <p class="font-bold text-sm">Fresco, rico y de aquí.</p>
            <p class="text-xs text-gray-200">Tu compra de siempre, ahora más fácil.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- LADO DERECHO: Formulario Dinámico -->
    <div class="w-full lg:w-1/2 flex flex-col relative p-6 sm:p-12">
      
      <!-- Botón Superior para alternar entre Cliente y Empleado -->
      <div class="absolute top-6 right-6 sm:top-8 sm:right-12">
        <button 
          v-if="modo !== 'empleado'" 
          @click="cambiarModo('empleado')" 
          class="flex items-center gap-2 text-sm font-semibold text-[#3A704F] hover:text-[#234E35] transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          INICIA COMO EMPLEADO
        </button>
        <button 
          v-else 
          @click="cambiarModo('cliente')" 
          class="flex items-center gap-2 text-sm font-semibold text-[#3A704F] hover:text-[#234E35] transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
          SOY CLIENTE
        </button>
      </div>

      <!-- Contenedor del Formulario -->
      <div class="flex-1 flex flex-col justify-center max-w-md mx-auto w-full mt-16 lg:mt-0">
        
        <div class="inline-flex items-center gap-2 bg-[#EAF2EC] text-[#234E35] px-3 py-1.5 rounded-full text-xs font-semibold w-max mb-6">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
          {{ modo === 'registro' ? 'Únete a nuestra familia' : 'Qué bueno tenerte de vuelta' }}
        </div>

        <h2 class="text-3xl font-bold mb-2">
          {{ modo === 'registro' ? 'Crea tu cuenta' : 'Inicia sesión' }}
        </h2>
        <p class="text-gray-500 text-sm mb-8">
          {{ modo === 'empleado' ? 'Acceso exclusivo para el personal.' : 'Entra a tu cuenta y ten tu próxima compra a un paso.' }}
        </p>

        <!-- Formulario -->
        <form @submit.prevent="procesarFormulario" class="space-y-5">
          
          <!-- DNI (Solo para Cliente o Registro) -->
          <div v-if="modo !== 'empleado'">
            <label class="block text-sm font-bold mb-2 text-[#234E35]">DNI</label>
            <input 
              v-model="formulario.dni" 
              type="text" 
              maxlength="8"
              required
              placeholder="Ejm: 12345678" 
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#234E35] focus:border-[#234E35] outline-none transition-all"
            >
          </div>

          <!-- Correo (Solo para Empleado) -->
          <div v-if="modo === 'empleado'">
            <label class="block text-sm font-bold mb-2 text-[#234E35]">Correo Electrónico</label>
            <input 
              v-model="formulario.email" 
              type="email" 
              required
              placeholder="empleado@dtodo.com" 
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#234E35] outline-none transition-all"
            >
          </div>

          <!-- Contraseña (Solo para Empleado) -->
          <div v-if="modo === 'empleado'">
            <label class="block text-sm font-bold mb-2 text-[#234E35]">Contraseña</label>
            <input 
              v-model="formulario.password" 
              type="password" 
              required
              placeholder="••••••••" 
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#234E35] outline-none transition-all"
            >
          </div>

          <!-- Nombres (Solo para Registro) -->
          <div v-if="modo === 'registro'">
            <label class="block text-sm font-bold mb-2 text-[#234E35]">Nombres Completos</label>
            <input 
              v-model="formulario.nombre" 
              type="text" 
              required
              placeholder="Ejm: Juan Pérez" 
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#234E35] outline-none transition-all"
            >
          </div>

          <!-- Mensaje de Error -->
          <p v-if="errorMsg" class="text-red-500 text-sm font-semibold">{{ errorMsg }}</p>

          <!-- Botón de Acción -->
          <button 
            type="submit" 
            :disabled="cargando"
            class="w-full bg-[#234E35] hover:bg-[#1A3A27] text-white font-bold py-3.5 px-4 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-lg shadow-[#234E35]/30 disabled:opacity-70"
          >
            {{ cargando ? 'Cargando...' : (modo === 'registro' ? 'Registrarme' : 'Ingresar al Sistema') }}
          </button>
        </form>

        <!-- Enlaces Inferiores -->
        <p v-if="modo === 'cliente'" class="text-center mt-6 text-sm text-gray-500 font-medium">
          ¿Es tu primera visita? <button type="button" @click="cambiarModo('registro')" class="text-[#234E35] font-bold hover:underline">Crea tu cuenta</button>
        </p>
        <p v-if="modo === 'registro'" class="text-center mt-6 text-sm text-gray-500 font-medium">
          ¿Ya tienes cuenta? <button type="button" @click="cambiarModo('cliente')" class="text-[#234E35] font-bold hover:underline">Inicia sesión</button>
        </p>
      </div>

      <!-- Footer Derecho -->
      <div class="mt-auto pt-8 text-center text-xs text-gray-400 flex flex-col items-center gap-2">
        <p>© 2026 D' Todo Minimarket · Privacidad · Términos y condiciones</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../services/api' // Para llamar al endpoint de registro

const auth = useAuthStore()
const router = useRouter()

const cargando = ref(false)
const errorMsg = ref('')

// Controla qué formulario se muestra ('cliente', 'empleado' o 'registro')
const modo = ref('cliente') 

const formulario = ref({
  dni: '',
  email: '',
  password: '',
  nombre: ''
})

const cambiarModo = (nuevoModo) => {
  modo.value = nuevoModo
  errorMsg.value = ''
  // Limpiamos el formulario al cambiar de modo
  formulario.value = { dni: '', email: '', password: '', nombre: '' }
}

const procesarFormulario = async () => {
  cargando.value = true
  errorMsg.value = ''
  
  try {
    if (modo.value === 'cliente') {
      await auth.loginCliente({ dni: formulario.value.dni })
      router.push('/catalogo')
    } 
    else if (modo.value === 'empleado') {
      await auth.login({ email: formulario.value.email, password: formulario.value.password })
      // Redirige usando Vue Router (tu router beforeEach decidirá a qué pantalla enviarlo según su rol)
      router.push('/dashboard') 
    }
    else if (modo.value === 'registro') {
      // 1. Registramos al usuario en la base de datos
      await api.post('/registro-cliente', {
        dni: formulario.value.dni,
        nombre: formulario.value.nombre
      })
      // 2. Si el registro fue exitoso, lo logueamos automáticamente
      await auth.loginCliente({ dni: formulario.value.dni })
      router.push('/catalogo')
    }
  } catch (error) {
    console.error(error)
    errorMsg.value = error.response?.data?.message || 'Error al procesar la solicitud. Verifica tus datos.'
  } finally {
    cargando.value = false
  }
}
</script>