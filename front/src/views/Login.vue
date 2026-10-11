<template>
  <div class="min-h-screen flex bg-white font-sans text-[#234E35]">
    
    <!-- LADO IZQUIERDO: Banner e Imagen (Oculto en móviles, visible en pantallas grandes) -->
    <div class="hidden lg:flex lg:w-1/2 bg-[#F1F6F0] p-12 flex-col justify-between rounded-r-3xl my-2 border-r border-[#E0EBE2]">
      
      <!-- Logo -->
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

      <!-- Textos Principales -->
      <div class="mt-12 mb-8">
        <h2 class="text-5xl xl:text-6xl font-bold leading-tight mb-6">
          Tu barrio.<br>
          Tu tienda.<br>
          Como siempre.
        </h2>
        <p class="text-lg text-[#3A704F] max-w-md">
          Lo que necesitas, cerquita de ti. Con la frescura y el cariño de todos los días.
        </p>
      </div>

      <!-- Imagen Destacada -->
      <div class="relative rounded-2xl overflow-hidden shadow-lg h-64 xl:h-80 w-full max-w-lg">
        <!-- Reemplaza el src con la imagen de tu tienda -->
        <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&q=80&w=1000" alt="Tienda D'Todo" class="object-cover w-full h-full" />
        
        <!-- Etiqueta Flotante sobre la imagen -->
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

    <!-- LADO DERECHO: Formulario de Inicio de Sesión -->
    <div class="w-full lg:w-1/2 flex flex-col relative p-6 sm:p-12">
      
      <!-- Botón Superior: Empleado -->
      <div class="absolute top-6 right-6 sm:top-8 sm:right-12">
        <button class="flex items-center gap-2 text-sm font-semibold text-[#3A704F] hover:text-[#234E35] transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          INICIA COMO EMPLEADO
        </button>
      </div>

      <!-- Contenedor del Formulario -->
      <div class="flex-1 flex flex-col justify-center max-w-md mx-auto w-full mt-16 lg:mt-0">
        
        <!-- Etiqueta de Bienvenida -->
        <div class="inline-flex items-center gap-2 bg-[#EAF2EC] text-[#234E35] px-3 py-1.5 rounded-full text-xs font-semibold w-max mb-6">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
          Qué bueno tenerte de vuelta
        </div>

        <h2 class="text-3xl font-bold mb-2">Inicia sesión</h2>
        <p class="text-gray-500 text-sm mb-8">Entra a tu cuenta y ten tu próxima compra a un paso.</p>

        <!-- Formulario Conectado a tu Lógica -->
        <form @submit.prevent="ingresarComoCliente" class="space-y-5">
          
          <!-- Input DNI -->
          <div>
            <label class="block text-sm font-bold mb-2 text-[#234E35]">DNI</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
              </div>
              <input 
                v-model="dni" 
                type="text" 
                maxlength="8"
                required
                placeholder="Ejm: 12345678" 
                class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#234E35] focus:border-[#234E35] outline-none transition-all"
              >
            </div>
          </div>

          <!-- Mensaje de Error (si falla el login) -->
          <p v-if="errorMsg" class="text-red-500 text-sm font-semibold">{{ errorMsg }}</p>

          <!-- Botón de Envío -->
          <button 
            type="submit" 
            :disabled="cargando"
            class="w-full bg-[#234E35] hover:bg-[#1A3A27] text-white font-bold py-3.5 px-4 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-lg shadow-[#234E35]/30 disabled:opacity-70"
          >
            {{ cargando ? 'Iniciando sesión...' : 'Iniciar sesión' }}
            <svg v-if="!cargando" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
          </button>
        </form>

        <p class="text-center mt-6 text-sm text-gray-500 font-medium">
          ¿Es tu primera visita? <a href="#" class="text-[#234E35] font-bold hover:underline">Crea tu cuenta</a>
        </p>
      </div>

      <!-- Footer Derecho -->
      <div class="mt-auto pt-8 text-center text-xs text-gray-400 flex flex-col items-center gap-2">
        <div class="flex items-center gap-1">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          Tus datos están en buenas manos.
        </div>
        <p>© 2026 D' Todo Minimarket · Privacidad · Términos y condiciones</p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth' // Asegúrate de que esta ruta sea correcta

const dni = ref('')
const cargando = ref(false)
const errorMsg = ref('')

const auth = useAuthStore()
const router = useRouter()

const ingresarComoCliente = async () => {
  cargando.value = true
  errorMsg.value = ''
  
  try {
    // Llama a la acción que creamos en el paso anterior
    await auth.loginCliente({ dni: dni.value })
    router.push('/catalogo') // Redirige al catálogo si todo sale bien
  } catch (error) {
    console.error(error)
    errorMsg.value = 'El DNI no está registrado o hubo un error de conexión.'
  } finally {
    cargando.value = false
  }
}
</script>