<template>
  <div class="ad-page">

    <!-- Encabezado -->
    <div class="ad-head">
      <div>
        <div class="ad-eyebrow"><Icon name="lock" style="width:12px;height:12px" /> Acceso: Administrador y Almacén</div>
        <h1 class="ad-title">Pedidos a proveedores</h1>
        <p class="ad-subtitle">Organiza la reposición y sigue cada entrega a tu tienda.</p>
      </div>
      <button v-if="auth.esAdministrador || auth.esAlmacen" class="ad-btn ad-btn-primary" @click="abrirModalNuevoPedido">
        <Icon name="plus" /> Nuevo pedido
      </button>
    </div>

    <!-- 4 tarjetas de estado -->
    <div class="ad-kpis">
      <div class="ad-card ad-kpi">
        <div class="ad-kpi-top"><span>Pendientes</span><Icon name="clock" style="width:16px;height:16px" /></div>
        <div class="ad-kpi-value">{{ resumen.pendiente ?? resumen.pendientes ?? 0 }}</div>
        <span class="ad-badge warn">Por confirmar</span>
      </div>
      <div class="ad-card ad-kpi">
        <div class="ad-kpi-top"><span>En camino</span><Icon name="truck" style="width:16px;height:16px" /></div>
        <div class="ad-kpi-value">{{ resumen.en_camino ?? 0 }}</div>
        <span class="ad-badge info">Entrega en curso</span>
      </div>
      <div class="ad-card ad-kpi">
        <div class="ad-kpi-top"><span>Recibidos</span><Icon name="box" style="width:16px;height:16px" /></div>
        <div class="ad-kpi-value">{{ resumen.recibido ?? resumen.recibidos ?? 0 }}</div>
        <span class="ad-badge neutral">Entrega completada</span>
      </div>
      <div class="ad-card ad-kpi">
        <div class="ad-kpi-top"><span>Cancelados</span><Icon name="xCircle" style="width:16px;height:16px" /></div>
        <div class="ad-kpi-value">{{ resumen.cancelado ?? resumen.cancelados ?? 0 }}</div>
        <span class="ad-badge neutral">Sin entrega</span>
      </div>
    </div>

    <!-- Tabla de pedidos -->
    <div class="ad-card" style="overflow: visible">
      <div class="flex items-start justify-between gap-3 flex-wrap" style="padding: 20px 22px 16px">
        <div>
          <h2 class="ad-card-title">{{ filtroEstado ? 'Pedidos ' + etiquetaEstado(filtroEstado).toLowerCase() + 's' : 'Todos los pedidos' }}</h2>
          <p class="ad-card-sub">{{ pedidos.length }} pedidos registrados</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="ad-muted" style="font-size: 12px">Estado</span>
          <select v-model="filtroEstado" class="ad-select" style="width: 180px" @change="cambiarFiltro">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="en_camino">En camino</option>
            <option value="recibido">Recibido</option>
            <option value="cancelado">Cancelado</option>
          </select>
        </div>
      </div>

      <div v-if="pedidos.length" class="ad-table-wrap" style="overflow: visible">
        <table class="ad-table">
          <thead>
            <tr>
              <th>Número</th>
              <th>Proveedor</th>
              <th>Fecha</th>
              <th>Estado</th>
              <th>Total</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in pedidosPagina" :key="p.id_pedido">
              <td class="ad-strong">{{ numeroPedido(p.id_pedido) }}</td>
              <td>{{ p.proveedor?.nombre }}</td>
              <td>{{ formatearFecha(p.fecha) }}</td>
              <td><span class="ad-badge" :class="claseEstado(p.estado)">{{ etiquetaEstado(p.estado) }}</span></td>
              <td>S/ {{ Number(p.total || 0).toFixed(2) }}</td>
              <td>
                <div v-if="(auth.esAdministrador || auth.esAlmacen) && p.estado !== 'recibido' && p.estado !== 'cancelado'" style="position: relative; display: inline-block">
                  <button class="ad-btn ad-btn-outline ad-btn-sm" @click.stop="menuAbierto = menuAbierto === p.id_pedido ? null : p.id_pedido">
                    <Icon name="chevronDown" style="width:14px;height:14px" /> Cambiar estado
                  </button>
                  <div v-if="menuAbierto === p.id_pedido" class="ad-menu">
                    <button v-if="p.estado !== 'en_camino'" @click="elegirEstado(p, 'en_camino')">En camino</button>
                    <button @click="elegirEstado(p, 'recibido')">Recibido</button>
                    <button class="danger" @click="elegirEstado(p, 'cancelado')">Cancelado</button>
                  </div>
                </div>
                <span v-else class="ad-muted" style="font-size: 12px">Estado final</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="ad-empty">No hay pedidos registrados.</div>

      <div class="ad-foot">
        <span>Mostrando {{ pedidosPagina.length }} de {{ pedidos.length }} pedidos</span>
        <div class="ad-pager">
          <button class="ad-btn ad-btn-outline ad-btn-sm" :disabled="pagina <= 1" @click="pagina--">Anterior</button>
          <span class="cur">{{ pagina }}</span>
          <button class="ad-btn ad-btn-outline ad-btn-sm" :disabled="pagina >= totalPaginas" @click="pagina++">Siguiente</button>
        </div>
      </div>
    </div>

    <div class="ad-callout info">
      <Icon name="info" /> Solo los pedidos pendientes o en camino permiten cambiar de estado. Los recibidos y cancelados conservan su estado final.
    </div>

    <!-- ============ MODAL: NUEVO PEDIDO ============ -->
    <div v-if="modalNuevo" class="ad-overlay" @click.self="modalNuevo = false">
      <form class="ad-modal xl" @submit.prevent="guardarPedido">
        <div class="ad-modal-head">
          <div class="ad-modal-eyebrow">Pedidos / Nuevo pedido</div>
          <h2 class="ad-modal-title">Nuevo pedido a proveedor</h2>
          <p class="ad-modal-sub">Prepara la reposición. El pedido se creará como pendiente.</p>
          <button type="button" class="ad-modal-x" @click="modalNuevo = false"><Icon name="x" /></button>
        </div>

        <div class="ad-modal-body">
          <div v-if="errorModal" class="ad-error">{{ errorModal }}</div>

          <div>
            <label class="ad-label">Proveedor *</label>
            <select v-model="nuevoPedido.id_proveedor" class="ad-select" required>
              <option disabled value="">Seleccione un proveedor</option>
              <option v-for="pr in proveedores" :key="pr.id_proveedor" :value="pr.id_proveedor">{{ pr.nombre }}</option>
            </select>
          </div>

          <div class="ad-grid2">
            <div>
              <label class="ad-label">Fecha del pedido *</label>
              <input v-model="nuevoPedido.fecha" type="date" class="ad-input" required />
            </div>
            <div>
              <label class="ad-label">Entrega estimada *</label>
              <input v-model="nuevoPedido.fecha_estimada_entrega" type="date" class="ad-input" required />
            </div>
          </div>

          <div class="flex items-center justify-between" style="margin-top: 4px">
            <h3 class="ad-strong" style="font-size: 14px">Productos del pedido</h3>
            <button type="button" class="ad-btn ad-btn-outline ad-btn-sm" @click="agregarLinea"><Icon name="plus" /> Añadir producto</button>
          </div>

          <div class="ad-table-wrap" style="border: 1px solid var(--ad-border); border-radius: 10px">
            <table class="ad-table">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th style="width: 100px">Cantidad</th>
                  <th style="width: 120px">P. unitario</th>
                  <th style="width: 100px">Importe</th>
                  <th style="width: 40px"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(d, idx) in nuevoPedido.detalles" :key="idx">
                  <td>
                    <select v-model="d.id_producto" class="ad-select" required style="height: 36px">
                      <option disabled value="">Seleccione producto</option>
                      <option v-for="pr in productos" :key="pr.id_producto" :value="pr.id_producto">{{ pr.nombre }}{{ pr.codigo ? ' · ' + pr.codigo : '' }}</option>
                    </select>
                  </td>
                  <td><input v-model.number="d.cantidad" type="number" min="1" class="ad-input" style="height: 36px" required /></td>
                  <td><input v-model.number="d.precio_unitario" type="number" min="0" step="0.01" class="ad-input" style="height: 36px" required /></td>
                  <td class="ad-strong" style="white-space: nowrap">S/ {{ importeLinea(d).toFixed(2) }}</td>
                  <td>
                    <button type="button" class="ad-modal-x" style="position: static" title="Quitar" @click="quitarLinea(idx)"><Icon name="trash" /></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="ad-panel flex items-center justify-between gap-3">
            <span class="ad-muted" style="font-size: 12px">{{ nuevoPedido.detalles.length }} {{ nuevoPedido.detalles.length === 1 ? 'producto' : 'productos' }} · {{ totalUnidades }} unidades · Importes en soles</span>
            <div style="text-align: right">
              <div class="ad-muted" style="font-size: 10.5px; letter-spacing: .05em">TOTAL DEL PEDIDO</div>
              <div style="font-size: 24px; font-weight: 600">S/ {{ totalPedido.toFixed(2) }}</div>
            </div>
          </div>
          <p class="ad-muted" style="font-size: 12px">La creación del pedido no modifica el stock. La recepción se registra por separado.</p>
        </div>

        <div class="ad-modal-foot">
          <span>Los campos marcados con * son obligatorios</span>
          <div class="btns">
            <button type="button" class="ad-btn ad-btn-outline" @click="modalNuevo = false">Cancelar</button>
            <button type="submit" class="ad-btn ad-btn-primary">Crear pedido</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Cierra el menú de estado al hacer clic fuera -->
    <div v-if="menuAbierto !== null" style="position: fixed; inset: 0; z-index: 5" @click="menuAbierto = null"></div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import Icon from '../components/Icon.vue'

const auth = useAuthStore()

const pedidos = ref([])
const resumen = ref({})
const filtroEstado = ref('')
const proveedores = ref([])
const productos = ref([])
const pagina = ref(1)
const POR_PAGINA = 8
const menuAbierto = ref(null)

const modalNuevo = ref(false)
const errorModal = ref('')
const nuevoPedido = ref({ id_proveedor: '', fecha: '', fecha_estimada_entrega: '', detalles: [] })

const etiquetas = { pendiente: 'Pendiente', en_camino: 'En camino', recibido: 'Recibido', cancelado: 'Cancelado' }
function etiquetaEstado(e) { return etiquetas[e] || e }

function claseEstado(estado) {
  const e = (estado || '').toLowerCase()
  if (e === 'en_camino') return 'info'
  if (e === 'pendiente') return 'warn'
  return 'neutral' // recibido y cancelado: estados finales
}

function numeroPedido(id) {
  return 'OC-' + String(id).padStart(4, '0')
}

function formatearFecha(f) {
  if (!f) return '-'
  const [y, m, d] = String(f).slice(0, 10).split('-')
  return `${d}/${m}/${y}`
}

const totalPaginas = computed(() => Math.max(1, Math.ceil(pedidos.value.length / POR_PAGINA)))
const pedidosPagina = computed(() => {
  const ini = (pagina.value - 1) * POR_PAGINA
  return pedidos.value.slice(ini, ini + POR_PAGINA)
})

async function cargarPedidos() {
  try {
    const { data } = await api.get('/pedidos', { params: { estado: filtroEstado.value } })
    pedidos.value = data.pedidos.data ?? data.pedidos
    resumen.value = data.resumen_estados || {}
    if (pagina.value > totalPaginas.value) pagina.value = totalPaginas.value
  } catch (error) {
    console.error('Error al cargar pedidos:', error)
  }
}

function cambiarFiltro() {
  pagina.value = 1
  cargarPedidos()
}

async function cargarProveedores() {
  try {
    const { data } = await api.get('/proveedores')
    proveedores.value = data
  } catch (error) {
    console.error('Error al cargar proveedores:', error)
  }
}

async function cargarProductosBase() {
  try {
    const { data } = await api.get('/productos', { params: { per_page: 100 } })
    productos.value = data.data ?? data
  } catch (error) {
    console.error('Error al cargar productos base:', error)
  }
}

// ---------- Nuevo pedido ----------
function abrirModalNuevoPedido() {
  nuevoPedido.value = { id_proveedor: '', fecha: new Date().toISOString().slice(0, 10), fecha_estimada_entrega: '', detalles: [] }
  agregarLinea()
  errorModal.value = ''
  modalNuevo.value = true
}

function agregarLinea() {
  nuevoPedido.value.detalles.push({ id_producto: '', cantidad: 1, precio_unitario: 0 })
}

function quitarLinea(idx) {
  nuevoPedido.value.detalles.splice(idx, 1)
}

function importeLinea(d) {
  return Number(d.cantidad || 0) * Number(d.precio_unitario || 0)
}

const totalPedido = computed(() => nuevoPedido.value.detalles.reduce((s, d) => s + importeLinea(d), 0))
const totalUnidades = computed(() => nuevoPedido.value.detalles.reduce((s, d) => s + Number(d.cantidad || 0), 0))

async function guardarPedido() {
  errorModal.value = ''
  if (!nuevoPedido.value.detalles.length) {
    errorModal.value = 'Agrega al menos un producto al pedido.'
    return
  }
  try {
    await api.post('/pedidos', nuevoPedido.value)
    modalNuevo.value = false
    cargarPedidos()
  } catch (e) {
    errorModal.value = e.response?.data?.message || 'Error al registrar el pedido.'
  }
}

// ---------- Cambio de estado ----------
function elegirEstado(pedido, nuevoEstado) {
  menuAbierto.value = null
  cambiarEstado(pedido, nuevoEstado)
}

async function cambiarEstado(pedido, nuevoEstado) {
  if (!nuevoEstado) return
  try {
    await api.patch(`/pedidos/${pedido.id_pedido}/estado`, { estado: nuevoEstado })
    cargarPedidos()
  } catch (e) {
    alert('No se pudo actualizar el estado del pedido.')
  }
}

onMounted(() => {
  cargarPedidos()
  cargarProveedores()
  cargarProductosBase()
})
</script>

<style scoped>
.ad-menu {
  position: absolute; z-index: 10; top: calc(100% + 4px); left: 0; min-width: 150px; background: #fff;
  border: 1px solid var(--ad-border); border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,.12); padding: 4px;
}
.ad-menu button {
  display: block; width: 100%; text-align: left; background: none; border: 0; cursor: pointer;
  padding: 8px 12px; border-radius: 6px; font-size: 13px; color: var(--ad-text);
}
.ad-menu button:hover { background: var(--ad-head); }
.ad-menu button.danger { color: var(--ad-danger); }
</style>
