<template>
  <div class="min-h-screen bg-fondo p-4 md:p-8 font-sans">
    
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Panel Principal</h1>
        <p class="text-gray-500 mt-1">Estadísticas en tiempo real de tu base de datos</p>
      </div>
      <button @click="cargarDashboard" class="bg-primario hover:bg-teal-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition transform hover:-translate-y-0.5">
        Actualizar Datos
      </button>
    </div>

    <!-- 4 Tarjetas de Métricas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      
      <!-- Ventas del Mes -->
      <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Ventas del Mes</p>
            <h3 class="text-3xl font-bold text-gray-800">S/ {{ formatear(datos.ventas_del_mes) }}</h3>
            <p v-if="datos.variacion_ventas_pct !== undefined && datos.variacion_ventas_pct !== null" 
               :class="Number(datos.variacion_ventas_pct) >= 0 ? 'text-emerald-500' : 'text-red-500'" 
               class="text-xs font-semibold mt-2 flex items-center gap-1">
              <span>{{ Number(datos.variacion_ventas_pct) >= 0 ? '↑' : '↓' }}</span> 
              {{ datos.variacion_ventas_pct }}% vs mes anterior
            </p>
          </div>
          <div class="bg-emerald-100 p-3 rounded-xl text-2xl">💰</div>
        </div>
      </div>

      <!-- Alertas Stock -->
      <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Alertas Stock Bajo</p>
            <h3 class="text-3xl font-bold text-gray-800">{{ datos.alertas_stock_bajo ?? '0' }}</h3>
            <p class="text-xs font-semibold mt-2 text-red-500">Productos en mínimo</p>
          </div>
          <div class="bg-red-100 p-3 rounded-xl text-2xl">⚠️</div>
        </div>
      </div>

      <!-- Pedidos Pendientes -->
      <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Pedidos Pendientes</p>
            <h3 class="text-3xl font-bold text-gray-800">{{ datos.pedidos_pendientes ?? '0' }}</h3>
            <p class="text-xs font-semibold mt-2 text-amber-600">Órdenes a proveedores</p>
          </div>
          <div class="bg-amber-100 p-3 rounded-xl text-2xl">📦</div>
        </div>
      </div>

      <!-- Rotación -->
      <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Inventario</p>
            <h3 class="text-lg font-bold text-gray-800 leading-tight mt-1">Sincronizado<br>con MySQL</h3>
          </div>
          <div class="bg-blue-100 p-3 rounded-xl text-2xl">🔄</div>
        </div>
      </div>

    </div>

    <!-- Zona Inferior: Gráfica y Tabla -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      
      <!-- Gráfica de Ventas -->
      <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Ventas últimos 7 días</h2>
        <div class="h-72" v-if="datos.ventas_ultimos_7_dias && datos.ventas_ultimos_7_dias.length > 0">
          <apexchart type="area" height="100%" :options="chartOpcionesVentas" :series="chartSeriesVentas"></apexchart>
        </div>
        <div v-else class="h-72 flex items-center justify-center text-gray-400 font-medium">
          Sin registros de ventas en los últimos días.
        </div>
      </div>

      <!-- Tabla de Stock Crítico -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
          <h2 class="text-lg font-bold text-gray-800">Atención: Stock Crítico</h2>
        </div>
        
        <div class="overflow-x-auto" v-if="datos.productos_stock_bajo && datos.productos_stock_bajo.length > 0">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                <th class="p-4 font-semibold">Producto</th>
                <th class="p-4 font-semibold text-center">Actual</th>
                <th class="p-4 font-semibold text-center">Mínimo</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
              <tr v-for="p in datos.productos_stock_bajo" :key="p.id_producto" class="hover:bg-gray-50 transition">
                <td class="p-4 font-medium text-gray-800">{{ p.nombre }}</td>
                <td class="p-4 text-center font-bold text-red-600">{{ p.stock }}</td>
                <td class="p-4 text-center text-gray-500">{{ p.stock_minimo }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="p-8 text-center text-gray-500 font-medium">
          No hay productos con stock bajo. 🎉
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../services/api'
import VueApexCharts from "vue3-apexcharts"

const apexchart = VueApexCharts
const datos = ref({
  ventas_del_mes: 0,
  alertas_stock_bajo: 0,
  pedidos_pendientes: 0,
  ventas_ultimos_7_dias: [],
  productos_stock_bajo: []
})

const chartSeriesVentas = ref([{ name: 'Ventas (S/)', data: [] }]);
const chartOpcionesVentas = ref({
  chart: { type: 'area', toolbar: { show: false }, fontFamily: 'inherit' },
  colors: ['#0d9488'], 
  fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 3 },
  xaxis: { categories: [], tooltip: { enabled: false } },
  yaxis: { labels: { formatter: (value) => `S/ ${Number(value).toFixed(2)}` } },
  grid: { borderColor: '#f3f4f6', strokeDashArray: 4 }
});

function formatear(valor) {
  return Number(valor || 0).toFixed(2)
}

async function cargarDashboard() {
  try {
    const { data } = await api.get('/dashboard')
    if (data) {
      datos.value = data

      if (data.ventas_ultimos_7_dias && Array.isArray(data.ventas_ultimos_7_dias)) {
        chartSeriesVentas.value = [{
          name: 'Ventas (S/)',
          data: data.ventas_ultimos_7_dias.map(v => Number(v.total || 0))
        }];

        chartOpcionesVentas.value = {
          ...chartOpcionesVentas.value,
          xaxis: {
            categories: data.ventas_ultimos_7_dias.map(v => v.dia || '')
          }
        };
      }
    }
  } catch (error) {
    console.error("Error al conectar con la base de datos del dashboard:", error)
  }
}

onMounted(cargarDashboard)
</script>