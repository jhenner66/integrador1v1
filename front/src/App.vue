<template>
  <div class="min-h-screen bg-[#F9FBF9] font-sans text-[#234E35]">
    
    <!-- NAVEGACIÓN PRINCIPAL GLOBAL (NUEVO DISEÑO PARA TODOS) -->
    <header v-if="auth.estaAutenticado" class="bg-white border-b border-[#E0EBE2] sticky top-0 z-50 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        
        <!-- Logo -->
        <div class="flex items-center gap-3 cursor-pointer" @click="irAlInicio">
          <div class="bg-[#234E35] text-white p-2 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
          </div>
          <div>
            <h1 class="text-xl font-bold leading-tight">D' Todo</h1>
            <p class="text-[9px] font-bold tracking-widest text-gray-400 uppercase">Minimarket</p>
          </div>
        </div>

        <!-- Menú central dinámico -->
        <nav class="hidden md:flex items-center gap-8 font-bold text-sm">
          
          <!-- Enlaces para EMPLEADOS/ADMIN -->
          <template v-if="!auth.esCliente">
            <router-link v-if="auth.esAdministrador" to="/dashboard" class="nav-link">Panel</router-link>
            <router-link v-if="auth.esAdministrador || auth.esAlmacen" to="/inventario" class="nav-link">Inventario</router-link>
            <router-link v-if="auth.esAdministrador || auth.esAlmacen" to="/pedidos" class="nav-link">Pedidos</router-link>
            <router-link v-if="auth.esAdministrador || auth.esVendedor" to="/ventas" class="nav-link">Ventas</router-link>
            <router-link v-if="auth.esAdministrador" to="/alertas" class="nav-link">Alertas</router-link>
            <router-link v-if="auth.esAdministrador" to="/catalogo" class="nav-link">Catálogo</router-link>
          </template>
          
          <!-- Enlaces para CLIENTES -->
          <template v-else>
            <router-link to="/catalogo" class="nav-link">Tienda</router-link>
            <button @click="alertaProximamente('Mis pedidos')" class="nav-link">Mis pedidos</button>
            <button @click="alertaProximamente('Centro de ayuda')" class="nav-link">Ayuda</button>
          </template>

        </nav>
        
        <!-- Usuario, Salir y Contenedor del Carrito -->
        <div class="flex items-center gap-4">
          <!-- Etiqueta del Usuario -->
          <div class="hidden sm:flex items-center gap-2 bg-[#F1F6F0] px-4 py-2 rounded-full text-sm font-bold">
            <div class="w-6 h-6 bg-[#D2E4D5] rounded-full flex items-center justify-center text-[#234E35] text-xs">
              {{ auth.usuario?.nombre?.charAt(0) || 'U' }}
            </div>
            Hola, {{ auth.usuario?.nombre?.split(' ')[0] || 'Usuario' }}
            <span class="text-[10px] font-extrabold bg-[#234E35] text-white px-2 py-0.5 rounded-full uppercase ml-1">
              {{ auth.usuario?.rol || auth.user?.rol || 'Rol' }}
            </span>
          </div>

          <!-- Botón Salir -->
          <button @click="cerrarSesion" class="text-red-500 hover:text-white hover:bg-red-500 font-bold text-sm px-4 py-2 rounded-full transition-colors">
            Salir
          </button>

          <!-- ESPACIO RESERVADO PARA EL CARRITO (Se llenará mágicamente desde Catalogo.vue) -->
          <div id="carrito-slot"></div>
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

const cerrarSesion = () => {
  auth.logout()
  router.push('/login')
}

const irAlInicio = () => {
  if (auth.esCliente) router.push('/catalogo')
  else router.push('/dashboard')
}

const alertaProximamente = (seccion) => {
  alert(`La sección "${seccion}" estará disponible próximamente.`)
}
</script>

<style scoped>
.nav-link {
  color: #9ca3af; /* Gris claro por defecto */
  transition: color 0.2s;
}
.nav-link:hover {
  color: #234E35; /* Verde oscuro al pasar el mouse */
}
.router-link-active {
  color: #234E35 !important;
  border-bottom: 2px solid #234E35;
  padding-bottom: 4px;
}
</style>