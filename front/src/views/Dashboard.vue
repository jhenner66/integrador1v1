<template>
  <div class="ad-page">

    <!-- Encabezado -->
    <div class="ad-head">
      <div>
        <div class="ad-eyebrow"><Icon name="lock" style="width:12px;height:12px" /> Acceso: solo Administrador</div>
        <h1 class="ad-title">Un vistazo a tu negocio</h1>
        <p class="ad-subtitle">Todo lo que necesitas para empezar el día en D' Todo.</p>
      </div>
      <button class="ad-btn ad-btn-primary" @click="cargarDashboard">
        <Icon name="refresh" /> Actualizar datos
      </button>
    </div>

    <!-- 4 tarjetas de métricas -->
    <div class="ad-kpis">
      <div class="ad-card ad-kpi">
        <div class="ad-kpi-top"><span>Ventas de {{ mesActual }}</span><Icon name="wallet" style="width:16px;height:16px" /></div>
        <div class="ad-kpi-value">S/ {{ formatear(datos.ventas_del_mes) }}</div>
        <span v-if="tieneVariacion" class="ad-badge" :class="Number(datos.variacion_ventas_pct) >= 0 ? 'ok' : 'danger'">
          {{ Number(datos.variacion_ventas_pct) >= 0 ? '+' : '-' }}{{ Math.abs(Number(datos.variacion_ventas_pct)) }}% vs. mes anterior
        </span>
        <span v-else class="ad-badge neutral">Sin mes anterior para comparar</span>
      </div>

      <div class="ad-card ad-kpi">
        <div class="ad-kpi-top"><span>Alertas de stock bajo</span><Icon name="alert" style="width:16px;height:16px" /></div>
        <div class="ad-kpi-value">{{ datos.alertas_stock_bajo ?? 0 }} {{ Number(datos.alertas_stock_bajo) === 1 ? 'producto' : 'productos' }}</div>
        <span class="ad-badge" :class="Number(datos.alertas_stock_bajo) > 0 ? 'danger' : 'ok'">
          {{ Number(datos.alertas_stock_bajo) > 0 ? 'Requieren reposición' : 'Todo en orden' }}
        </span>
      </div>

      <div class="ad-card ad-kpi">
        <div class="ad-kpi-top"><span>Pedidos pendientes</span><Icon name="truck" style="width:16px;height:16px" /></div>
        <div class="ad-kpi-value">{{ datos.pedidos_pendientes ?? 0 }} {{ Number(datos.pedidos_pendientes) === 1 ? 'pedido' : 'pedidos' }}</div>
        <span class="ad-badge warn">Por confirmar con proveedor</span>
      </div>

      <div class="ad-card ad-kpi">
        <div class="ad-kpi-top"><span>Sincronización de inventario</span><Icon name="cloud" style="width:16px;height:16px" /></div>
        <div class="ad-kpi-value">{{ errorCarga ? 'Sin conexión' : 'Al día' }}</div>
        <span class="ad-badge" :class="errorCarga ? 'danger' : 'info'">
          {{ errorCarga ? 'No se pudo sincronizar' : 'Última sincronización · ' + horaSync }}
        </span>
      </div>
    </div>

    <!-- Gráfica de ventas -->
    <div class="ad-card ad-card-pad" style="margin-bottom: 20px">
      <div class="flex items-start justify-between gap-3 flex-wrap">
        <div>
          <h2 class="ad-card-title">Ventas de los últimos 7 días</h2>
          <p class="ad-card-sub">{{ rangoGrafica }}</p>
        </div>
        <span v-if="ventas7.length" class="ad-badge neutral" style="font-size:11px; padding: 6px 10px">Total · S/ {{ formatearMiles(totalSemana) }}</span>
      </div>

      <div v-if="ventas7.length" style="height: 280px; margin-top: 8px">
        <apexchart type="area" height="100%" :options="chartOpciones" :series="chartSeries"></apexchart>
      </div>
      <div v-else class="ad-empty" style="height: 200px; display:flex; align-items:center; justify-content:center">
        Sin registros de ventas en los últimos días.
      </div>
    </div>

    <!-- Stock crítico -->
    <div class="ad-card" style="overflow: hidden">
      <div class="flex items-start justify-between gap-3 flex-wrap" style="padding: 20px 22px 16px">
        <div>
          <h2 class="ad-card-title">Stock crítico</h2>
          <p class="ad-card-sub">Productos en el mínimo o por debajo. Prioriza su reposición.</p>
        </div>
        <router-link to="/inventario" class="ad-btn ad-btn-outline ad-btn-sm" style="text-decoration:none">
          <Icon name="arrowUpRight" /> Ir a inventario
        </router-link>
      </div>

      <div v-if="critico.length" class="ad-table-wrap">
        <table class="ad-table">
          <thead>
            <tr>
              <th>Producto</th>
              <th>Código</th>
              <th>Stock actual</th>
              <th>Mínimo</th>
              <th>Indicación</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in critico" :key="p.id_producto">
              <td class="ad-strong">{{ p.nombre }}</td>
              <td>{{ p.codigo || '—' }}</td>
              <td class="ad-danger-text">{{ p.stock }} un.</td>
              <td>{{ p.stock_minimo }} un.</td>
              <td>
                <span class="ad-badge" :class="Number(p.stock) < Number(p.stock_minimo) ? 'danger' : 'warn'">
                  {{ Number(p.stock) < Number(p.stock_minimo) ? 'Bajo el mínimo' : 'En el mínimo' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="ad-empty">No hay productos con stock bajo.</div>

      <div class="ad-foot" style="justify-content: flex-start">
        <Icon name="refresh" style="width:13px;height:13px" />
        Datos actualizados hoy a las {{ horaSync }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import api from '../services/api'
import VueApexCharts from 'vue3-apexcharts'
import Icon from '../components/Icon.vue'

const apexchart = VueApexCharts

const datos = ref({
  ventas_del_mes: 0,
  alertas_stock_bajo: 0,
  pedidos_pendientes: 0,
  ventas_ultimos_7_dias: [],
  productos_stock_bajo: [],
})
const errorCarga = ref(false)
const horaSync = ref('--:--')

const ventas7 = computed(() => datos.value.ventas_ultimos_7_dias || [])
const critico = computed(() => datos.value.productos_stock_bajo || [])
const tieneVariacion = computed(() => datos.value.variacion_ventas_pct !== undefined && datos.value.variacion_ventas_pct !== null)
const totalSemana = computed(() => ventas7.value.reduce((s, v) => s + Number(v.total || 0), 0))

const mesActual = new Date().toLocaleDateString('es-PE', { month: 'long' })

// 'YYYY-MM-DD' -> Date local (evita el desfase por zona horaria)
function aFecha(dia) {
  const [y, m, d] = String(dia).slice(0, 10).split('-').map(Number)
  return new Date(y, (m || 1) - 1, d || 1)
}
function etiquetaDia(dia) {
  const f = aFecha(dia)
  const nombre = f.toLocaleDateString('es-PE', { weekday: 'short' }).replace('.', '')
  return `${nombre.charAt(0).toUpperCase()}${nombre.slice(1)} ${f.getDate()}`
}

const rangoGrafica = computed(() => {
  if (!ventas7.value.length) return 'Sin datos en el periodo'
  const a = aFecha(ventas7.value[0].dia)
  const b = aFecha(ventas7.value[ventas7.value.length - 1].dia)
  const fmt = (f, conMes) => f.toLocaleDateString('es-PE', conMes ? { day: 'numeric', month: 'long', year: 'numeric' } : { day: 'numeric' })
  return `Del ${fmt(a, false)} al ${fmt(b, true)}`
})

function formatear(valor) {
  return Number(valor || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
function formatearMiles(valor) {
  return formatear(valor)
}

const chartSeries = computed(() => [{
  name: 'Ventas diarias (S/)',
  data: ventas7.value.map(v => Number(v.total || 0)),
}])

const chartOpciones = computed(() => ({
  chart: { type: 'area', toolbar: { show: false }, fontFamily: 'Inter, sans-serif', zoom: { enabled: false }, animations: { enabled: true } },
  colors: ['#2f5f8a'],
  fill: { type: 'solid', opacity: 0.1 },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  markers: { size: 0, hover: { size: 4 } },
  legend: { show: true, position: 'top', horizontalAlign: 'left', fontSize: '12px', markers: { size: 5 } },
  xaxis: {
    categories: ventas7.value.map(v => etiquetaDia(v.dia)),
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: { style: { colors: '#6b7a72', fontSize: '12px' } },
    tooltip: { enabled: false },
  },
  yaxis: { min: 0, labels: { style: { colors: '#6b7a72', fontSize: '11px' }, formatter: (v) => Number(v).toLocaleString('es-PE', { maximumFractionDigits: 0 }) } },
  grid: { borderColor: '#e9eeea', strokeDashArray: 0, yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } } },
  tooltip: { theme: 'dark', y: { formatter: (v) => `S/ ${formatear(v)}` } },
}))

async function cargarDashboard() {
  try {
    const { data } = await api.get('/dashboard')
    if (data) datos.value = data
    errorCarga.value = false
    horaSync.value = new Date().toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: false })
  } catch (error) {
    errorCarga.value = true
    console.error('Error al conectar con la base de datos del dashboard:', error)
  }
}

onMounted(cargarDashboard)
</script>
