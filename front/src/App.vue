<template>
  <div class="min-h-screen bg-[#F9FBF9] font-sans text-[#234E35]">
    
    <!-- NAVEGACIÓN PRINCIPAL GLOBAL -->
    <header v-if="auth.estaAutenticado" class="bg-white border-b border-[#E0EBE2] sticky top-0 z-50 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        
        <div class="flex items-center gap-3 cursor-pointer" @click="irAlInicio">
          <div class="bg-[#234E35] text-white p-2 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
          </div>
          <div>
            <h1 class="text-xl font-bold leading-tight">D' Todo</h1>
            <p class="text-[9px] font-bold tracking-widest text-gray-400 uppercase">Minimarket</p>
          </div>
        </div>

        <nav class="hidden md:flex items-center gap-8 font-bold text-sm">
          <template v-if="!auth.esCliente">
            <router-link v-if="auth.esAdministrador" to="/dashboard" class="nav-link">Panel</router-link>
            <router-link v-if="auth.esAdministrador || auth.esAlmacen" to="/inventario" class="nav-link">Inventario</router-link>
            <router-link v-if="auth.esAdministrador || auth.esAlmacen" to="/pedidos" class="nav-link">Pedidos</router-link>
            <router-link v-if="auth.esAdministrador || auth.esVendedor" to="/ventas" class="nav-link">Ventas</router-link>
            <router-link v-if="auth.esAdministrador" to="/alertas" class="nav-link">Alertas</router-link>
            <router-link v-if="auth.esAdministrador" to="/catalogo" class="nav-link">Catálogo</router-link>
          </template>
          <template v-else>
            <router-link to="/catalogo" class="nav-link">Tienda</router-link>
            <button @click="alertaProximamente('Mis pedidos')" class="nav-link">Mis pedidos</button>
            <button @click="alertaProximamente('Centro de ayuda')" class="nav-link">Ayuda</button>
          </template>
        </nav>
        
        <div class="flex items-center gap-4">
          
          <!-- MENÚ DESPLEGABLE DEL USUARIO -->
          <div class="relative">
            <!-- Botón del Perfil -->
            <div @click="menuUsuarioAbierto = !menuUsuarioAbierto" class="hidden sm:flex items-center gap-2 bg-[#F1F6F0] px-4 py-2 rounded-full text-sm font-bold cursor-pointer hover:bg-[#D2E4D5] transition select-none">
              <div class="w-6 h-6 bg-[#D2E4D5] rounded-full flex items-center justify-center text-[#234E35] text-xs">
                {{ auth.usuario?.nombre?.charAt(0) || 'U' }}
              </div>
              Hola, {{ auth.usuario?.nombre?.split(' ')[0] || 'Usuario' }}
              <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="{'rotate-180': menuUsuarioAbierto}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>

            <!-- Dropdown -->
            <div v-if="menuUsuarioAbierto" class="absolute right-0 mt-2 w-64 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 py-2 overflow-hidden">
              <div class="px-4 py-3 border-b border-gray-50">
                <p class="text-sm font-bold text-[#234E35] truncate">{{ auth.usuario?.nombre || 'Usuario' }}</p>
                <p class="text-xs text-gray-400 uppercase tracking-widest font-extrabold mt-1">{{ auth.usuario?.rol || 'Cliente' }}</p>
              </div>
              
              <button @click="abrirMisDatos" class="w-full text-left px-4 py-2.5 text-sm text-gray-600 hover:bg-[#F1F6F0] hover:text-[#234E35] flex items-center gap-3 font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Mis datos
              </button>
              <button @click="alertaProximamente('Mis direcciones')" class="w-full text-left px-4 py-2.5 text-sm text-gray-600 hover:bg-[#F1F6F0] hover:text-[#234E35] flex items-center gap-3 font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Mis direcciones
              </button>
              <button @click="alertaProximamente('Métodos de pago')" class="w-full text-left px-4 py-2.5 text-sm text-gray-600 hover:bg-[#F1F6F0] hover:text-[#234E35] flex items-center gap-3 font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg> Métodos de pago
              </button>
              <button @click="alertaProximamente('Configuración')" class="w-full text-left px-4 py-2.5 text-sm text-gray-600 hover:bg-[#F1F6F0] hover:text-[#234E35] flex items-center gap-3 font-semibold transition border-b border-gray-50 pb-3 mb-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Configuración
              </button>
              <button @click="cerrarSesion" class="w-full text-left px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 flex items-center gap-3 font-bold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg> Cerrar sesión
              </button>
            </div>
          </div>

          <!-- ESPACIO RESERVADO PARA EL CARRITO (Se llena desde Catalogo.vue) -->
          <div id="carrito-slot"></div>
        </div>
      </div>
    </header>

    <!-- MODAL MIS DATOS -->
    <div v-if="mostrarModalDatos" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
      <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden transform transition-all">
        <div class="bg-[#234E35] text-white px-6 py-4 flex justify-between items-center">
          <h3 class="font-bold text-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Mis Datos
          </h3>
          <button @click="mostrarModalDatos = false" class="text-white/80 hover:text-white transition bg-[#1A3A27] p-1.5 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        
        <form @submit.prevent="actualizarDatos" class="p-6 space-y-5">
          
          <!-- Aviso RENIEC -->
          <div class="bg-blue-50/50 border border-blue-100 text-blue-800 text-xs p-3.5 rounded-2xl flex gap-3 items-start">
            <svg class="w-5 h-5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="leading-relaxed font-medium">Los nombres y apellidos se obtienen automáticamente mediante la conexión con <strong>RENIEC</strong> al ingresar tu DNI. Por seguridad, no pueden ser modificados manualmente.</p>
          </div>

          <div class="flex gap-4 items-end">
            <div class="flex-1">
              <label class="block text-xs font-bold text-gray-500 mb-1">DNI</label>
              <input v-model="datosForm.dni" type="text" maxlength="8" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#234E35] focus:ring-1 focus:ring-[#234E35] outline-none transition">
            </div>
            <!-- Botón preparado para tu futura API -->
            <button type="button" class="bg-[#F1F6F0] text-[#234E35] text-xs font-bold px-4 py-3 rounded-xl border border-[#D2E4D5] hover:bg-[#EAF2EC] transition flex items-center gap-2 shadow-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
              RENIEC
            </button>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-500 mb-1">Nombres</label>
              <input v-model="datosForm.nombre" type="text" disabled class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 text-sm text-gray-500 cursor-not-allowed font-medium">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 mb-1">Apellidos</label>
              <input v-model="datosForm.apellidos" type="text" disabled class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 text-sm text-gray-500 cursor-not-allowed font-medium">
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Correo Electrónico</label>
            <input v-model="datosForm.correo" type="email" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#234E35] outline-none transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Celular</label>
            <input v-model="datosForm.celular" type="text" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#234E35] outline-none transition">
          </div>

          <div class="pt-2">
            <button type="submit" class="w-full bg-[#234E35] hover:bg-[#1A3A27] text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-[#234E35]/30 flex justify-center items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              Actualizar datos
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Contenido de las vistas -->
    <main>
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from './stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

// Variables para el menú y modal
const menuUsuarioAbierto = ref(false)
const mostrarModalDatos = ref(false)
const datosForm = ref({
  dni: '', nombre: '', apellidos: '', correo: '', celular: ''
})

const cerrarSesion = () => {
  menuUsuarioAbierto.value = false
  auth.logout()
  router.push('/login')
}

const irAlInicio = () => {
  if (auth.esCliente) router.push('/catalogo')
  else router.push('/dashboard')
}

const alertaProximamente = (seccion) => {
  menuUsuarioAbierto.value = false
  alert(`La sección "${seccion}" estará disponible próximamente en futuras actualizaciones.`)
}

// Abrir el modal copiando los datos actuales de Pinia
const abrirMisDatos = () => {
  menuUsuarioAbierto.value = false
  datosForm.value = {
    dni: auth.usuario?.dni || '',
    nombre: auth.usuario?.nombre || '',
    apellidos: auth.usuario?.apellidos || '',
    correo: auth.usuario?.correo || '',
    celular: auth.usuario?.celular || ''
  }
  mostrarModalDatos.value = true
}

// Guardar datos temporalmente en el Store (En el futuro esto hará un PUT a Laravel)
const actualizarDatos = () => {
  const usuarioActualizado = { ...auth.usuario, ...datosForm.value }
  auth.setSesion(auth.token, usuarioActualizado) // Actualiza el localStorage y el Store
  mostrarModalDatos.value = false
}
</script>

<style scoped>
.nav-link {
  color: #9ca3af;
  transition: color 0.2s;
}
.nav-link:hover {
  color: #234E35;
}
.router-link-active {
  color: #234E35 !important;
  border-bottom: 2px solid #234E35;
  padding-bottom: 4px;
}
</style>