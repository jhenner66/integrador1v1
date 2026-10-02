<template>
  <div class="min-h-screen bg-fondo p-4 md:p-8 font-sans">
    
    <div class="mb-8 text-center">
      <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Bienvenido a D'Todo</h1>
      <p class="text-gray-500 mt-1">Explora nuestros productos disponibles</p>
    </div>

    <!-- Lista de Productos para el Cliente -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <div v-for="p in productos" :key="p.id_producto" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center text-center hover:shadow-md transition">
        <!-- Puedes cambiar el icono por una imagen si agregas fotos a tu BD -->
        <div class="h-24 w-24 bg-teal-50 rounded-full flex items-center justify-center text-3xl mb-4">🛒</div>
        <h3 class="font-bold text-gray-800">{{ p.nombre }}</h3>
        <p class="text-xs text-gray-500 mb-2">{{ p.categoria?.nombre || 'General' }}</p>
        <div class="mt-auto pt-4 w-full border-t border-gray-50">
          <span class="text-xl font-black text-teal-600">S/ {{ Number(p.precio).toFixed(2) }}</span>
        </div>
      </div>
    </div>

    <div v-if="productos.length === 0" class="text-center text-gray-500 py-12">
      Cargando catálogo...
    </div>

  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../services/api'

const productos = ref([])

async function cargarProductos() {
  try {
    // Solicitamos los productos disponibles (Idealmente el backend debería filtrar solo los activos)
    const { data } = await api.get('/productos', { params: { per_page: 100 } })
    productos.value = data.data ?? data
  } catch (error) {
    console.error("Error cargando el catálogo:", error)
  }
}

onMounted(cargarProductos)
</script>