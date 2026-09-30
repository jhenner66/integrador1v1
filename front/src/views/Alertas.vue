<template>
  <div class="min-h-screen bg-fondo p-4 md:p-8 font-sans">
    
    <!-- Encabezado -->
    <div class="mb-8">
      <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Centro de Alertas & Vencimientos</h1>
      <p class="text-gray-500 mt-1">Monitorea el stock crítico y los productos próximos a caducar</p>
    </div>

    <!-- SECCIÓN 1: ALERTAS DE STOCK BAJO -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
      <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-amber-50/50">
        <div class="flex items-center gap-3">
          <span class="text-2xl">⚠️</span>
          <div>
            <h2 class="text-lg font-bold text-gray-800">Alertas de Stock Bajo</h2>
            <p class="text-xs text-gray-500">Productos que han alcanzado o descendido de su stock mínimo</p>
          </div>
        </div>
        <span class="bg-amber-100 text-amber-800 font-bold px-3 py-1 rounded-full text-xs">
          {{ datos.alertas?.length || 0 }} Activas
        </span>
      </div>

      <div class="overflow-x-auto" v-if="datos.alertas?.length">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
              <th class="p-4 font-semibold">Producto</th>
              <th class="p-4 font-semibold">Mensaje de Alerta</th>
              <th class="p-4 font-semibold">Fecha y Hora</th>
              <th class="p-4 font-semibold text-center">Acción</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
            <tr v-for="a in datos.alertas" :key="a.id_alerta" class="hover:bg-gray-50 transition-colors">
              <td class="p-4 font-bold text-gray-900">{{ a.producto?.nombre || 'Producto general' }}</td>
              <td class="p-4 text-gray-600">{{ a.mensaje }}</td>
              <td class="p-4 text-gray-500 text-xs">{{ formatearFecha(a.created_at) }}</td>
              <td class="p-4 text-center">
                <button @click="atender(a)" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-lg text-xs transition shadow-sm">
                  ✓ Marcar atendida
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="p-12 text-center text-gray-500 font-medium flex flex-col items-center justify-center gap-2">
        <span class="text-3xl">🎉</span>
        <span>No hay alertas activas de stock bajo. Todo en orden.</span>
      </div>
    </div>

    <!-- SECCIÓN 2: PRODUCTOS PRÓXIMOS A VENCER -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-red-50/50">
        <div class="flex items-center gap-3">
          <span class="text-2xl">⏰</span>
          <div>
            <h2 class="text-lg font-bold text-gray-800">Productos Próximos a Vencer (30 días)</h2>
            <p class="text-xs text-gray-500">Artículos con fecha de caducidad próxima que requieren atención comercial</p>
          </div>
        </div>
        <span class="bg-red-100 text-red-800 font-bold px-3 py-1 rounded-full text-xs">
          {{ datos.productos_proximos_a_vencer?.length || 0 }} en riesgo
        </span>
      </div>

      <div class="overflow-x-auto" v-if="datos.productos_proximos_a_vencer?.length">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
              <th class="p-4 font-semibold">Producto</th>
              <th class="p-4 font-semibold text-center">Stock Actual</th>
              <th class="p-4 font-semibold text-right">Fecha de Vencimiento</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
            <tr v-for="p in datos.productos_proximos_a_vencer" :key="p.id_producto" class="hover:bg-gray-50 transition-colors">
              <td class="p-4 font-bold text-gray-900">{{ p.nombre }}</td>
              <td class="p-4 text-center font-bold text-red-600">{{ p.stock }} un.</td>
              <td class="p-4 text-right font-semibold text-gray-700">{{ p.fecha_vencimiento }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="p-12 text-center text-gray-500 font-medium">
        No hay productos próximos a vencer en los siguientes 30 días.
      </div>
    </div>

  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../services/api'

const datos = ref({})

function formatearFecha(fechaString) {
  if (!fechaString) return '-';
  const fecha = new Date(fechaString);
  return fecha.toLocaleString('es-PE', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

async function cargarAlertas() {
  try {
    const { data } = await api.get('/alertas')
    datos.value = data
  } catch (error) {
    console.error("Error al cargar las alertas:", error)
  }
}

async function atender(alerta) {
  try {
    await api.patch(`/alertas/${alerta.id_alerta}/atender`)
    cargarAlertas()
  } catch (e) {
    alert('No se pudo actualizar el estado de la alerta.')
  }
}

onMounted(cargarAlertas)
</script>