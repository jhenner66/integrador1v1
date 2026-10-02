<template>
  <div class="min-h-screen bg-gray-50 font-sans">
    
    <!-- Barra de Navegación Superior -->
    <header class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        <!-- 1. Logo y Nombre (Apegado totalmente a la Izquierda) -->
        <div class="flex items-center gap-3">
          <span class="bg-teal-600 text-white font-extrabold px-3 py-2 rounded-xl text-sm shadow-sm">DT</span>
          <span class="font-extrabold text-gray-800 text-xl tracking-tight">D'Todo</span>
        </div>


        <!-- Enlaces de Navegación por Rol -->
        <nav class="hidden md:flex items-center gap-1 bg-gray-50 p-1 rounded-xl border border-gray-100">
          
          <!-- Enlaces de Administradores y Empleados -->
          <router-link v-if="auth.esAdministrador" to="/dashboard" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 hover:text-teal-700 hover:bg-white transition-all">Panel Principal</router-link>
          <router-link v-if="auth.esAdministrador || auth.esAlmacen" to="/inventario" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 hover:text-teal-700 hover:bg-white transition-all">Inventario</router-link>
          <router-link v-if="auth.esAdministrador || auth.esAlmacen" to="/pedidos" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 hover:text-teal-700 hover:bg-white transition-all">Pedidos</router-link>
          <router-link v-if="auth.esAdministrador || auth.esVendedor" to="/ventas" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 hover:text-teal-700 hover:bg-white transition-all">Ventas</router-link>
          <router-link v-if="auth.esAdministrador" to="/alertas" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 hover:text-teal-700 hover:bg-white transition-all">Alertas</router-link>
          
          <!-- NUEVO: Enlace exclusivo para el Cliente (y Admin para poder verlo) -->
          <router-link v-if="auth.esAdministrador || (auth.usuario?.rol || auth.user?.rol) === 'cliente'" to="/catalogo" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 hover:text-teal-700 hover:bg-white transition-all">Catálogo de Productos</router-link>
        
        </nav>

        <!-- 3. Perfil de Usuario y Salida (Apegado totalmente a la Derecha) -->
        <div class="flex items-center gap-3">
          <div class="hidden sm:flex flex-col text-right">
            <span class="text-xs font-bold text-gray-800">{{ auth.usuario?.nombre || auth.user?.nombre || 'Usuario' }}</span>
            <span class="text-[10px] font-extrabold bg-teal-50 text-teal-700 px-2 py-0.5 rounded-full uppercase tracking-wider w-fit ml-auto mt-0.5">
              {{ auth.usuario?.rol || auth.user?.rol || 'Rol' }}
            </span>
          </div>
          <div class="h-8 w-px bg-gray-200 hidden sm:block"></div>
          <button @click="cerrarSesion" class="bg-red-50 hover:bg-red-100 text-red-600 text-sm font-bold px-4 py-2 rounded-xl transition shadow-sm flex items-center gap-1.5">
            <span>Salir</span> 🚪
          </button>
        </div>

      </div>
    </header>

    <!-- Contenido de las vistas -->
    <main>
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from './stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

function cerrarSesion() {
  auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.router-link-active {
  background-color: #ffffff !important;
  color: #0d9488 !important;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
}
</style>