<template>
  <div class="ad-page">

    <!-- Encabezado -->
    <div class="ad-head">
      <div>
        <div class="ad-eyebrow"><Icon name="lock" style="width:12px;height:12px" /> Acceso: Administrador</div>
        <h1 class="ad-title">Alertas y vencimientos</h1>
        <p class="ad-subtitle">Monitorea el stock crítico y los productos próximos a caducar.</p>
      </div>
      <button class="ad-btn ad-btn-outline" @click="cargarAlertas"><Icon name="refresh" /> Actualizar</button>
    </div>

    <!-- Resumen -->
    <div class="ad-kpis" style="grid-template-columns: repeat(2, minmax(0, 1fr))">
      <div class="ad-card ad-kpi">
        <div class="ad-kpi-top"><span>Alertas de stock bajo</span><Icon name="alert" style="width:16px;height:16px" /></div>
        <div class="ad-kpi-value">{{ datos.alertas?.length || 0 }}</div>
        <span class="ad-badge" :class="datos.alertas?.length ? 'warn' : 'ok'">{{ datos.alertas?.length ? 'Requieren atención' : 'Todo en orden' }}</span>
      </div>
      <div class="ad-card ad-kpi">
        <div class="ad-kpi-top"><span>Próximos a vencer (30 días)</span><Icon name="clock" style="width:16px;height:16px" /></div>
        <div class="ad-kpi-value">{{ datos.productos_proximos_a_vencer?.length || 0 }}</div>
        <span class="ad-badge" :class="datos.productos_proximos_a_vencer?.length ? 'danger' : 'ok'">{{ datos.productos_proximos_a_vencer?.length ? 'En riesgo' : 'Sin riesgo' }}</span>
      </div>
    </div>

    <!-- Stock bajo -->
    <div class="ad-card" style="margin-bottom: 20px">
      <div style="padding: 20px 22px 16px">
        <h2 class="ad-card-title">Alertas de stock bajo</h2>
        <p class="ad-card-sub">Productos que han alcanzado o descendido de su stock mínimo</p>
      </div>

      <div v-if="datos.alertas?.length" class="ad-table-wrap">
        <table class="ad-table">
          <thead>
            <tr>
              <th>Producto</th>
              <th>Mensaje</th>
              <th>Fecha y hora</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in datos.alertas" :key="a.id_alerta">
              <td class="ad-strong">{{ a.producto?.nombre || 'Producto general' }}</td>
              <td>{{ a.mensaje }}</td>
              <td class="ad-muted">{{ formatearFecha(a.created_at) }}</td>
              <td>
                <button class="ad-btn ad-btn-outline ad-btn-sm" @click="atender(a)"><Icon name="check" style="width:14px;height:14px" /> Marcar atendida</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="ad-empty">No hay alertas activas de stock bajo.</div>
    </div>

    <!-- Próximos a vencer -->
    <div class="ad-card">
      <div style="padding: 20px 22px 16px">
        <h2 class="ad-card-title">Productos próximos a vencer</h2>
        <p class="ad-card-sub">Artículos con fecha de caducidad en los próximos 30 días</p>
      </div>

      <div v-if="datos.productos_proximos_a_vencer?.length" class="ad-table-wrap">
        <table class="ad-table">
          <thead>
            <tr>
              <th>Producto</th>
              <th class="num">Stock actual</th>
              <th class="num">Fecha de vencimiento</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in datos.productos_proximos_a_vencer" :key="p.id_producto">
              <td class="ad-strong">{{ p.nombre }}</td>
              <td class="num ad-danger-text">{{ p.stock }} un.</td>
              <td class="num">{{ formatearDia(p.fecha_vencimiento) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="ad-empty">No hay productos próximos a vencer en los siguientes 30 días.</div>

      <div class="ad-foot" style="justify-content: flex-start">
        <Icon name="refresh" style="width:13px;height:13px" />
        Datos actualizados hoy a las {{ horaSync }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../services/api'
import Icon from '../components/Icon.vue'

const datos = ref({})
const horaSync = ref('--:--')

function formatearFecha(fechaString) {
  if (!fechaString) return '-'
  const f = new Date(fechaString)
  const dia = f.toLocaleDateString('es-PE', { day: 'numeric', month: 'short' }).replace('.', '')
  const hora = f.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: false })
  return `${dia} · ${hora}`
}

function formatearDia(fecha) {
  if (!fecha) return '-'
  const [y, m, d] = String(fecha).slice(0, 10).split('-')
  return `${d}/${m}/${y}`
}

async function cargarAlertas() {
  try {
    const { data } = await api.get('/alertas')
    datos.value = data
    horaSync.value = new Date().toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: false })
  } catch (error) {
    console.error('Error al cargar las alertas:', error)
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
