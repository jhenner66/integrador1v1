<template>
  <div class="ad-page">

    <!-- Encabezado -->
    <div class="ad-head">
      <div>
        <div class="ad-eyebrow"><Icon name="lock" style="width:12px;height:12px" /> Acceso: Administrador y Almacén</div>
        <h1 class="ad-title">Inventario</h1>
        <p class="ad-subtitle">Consulta productos, controla existencias y registra movimientos.</p>
      </div>
      <div class="ad-actions">
        <button class="ad-btn ad-btn-outline" @click="abrirReporte"><Icon name="history" /> Historial de movimientos</button>
        <button class="ad-btn ad-btn-primary" @click="abrirModalNuevo"><Icon name="plus" /> Nuevo producto</button>
      </div>
    </div>

    <!-- Resumen -->
    <div class="ad-card flex items-center justify-between flex-wrap gap-3" style="padding: 14px 22px; margin-bottom: 18px; font-size: 13px">
      <div class="flex items-center gap-6 flex-wrap">
        <span>{{ productos.length }} productos registrados</span>
        <span :class="criticos > 0 ? 'ad-danger-text' : 'ad-muted'" style="font-weight: 400">{{ criticos }} con stock crítico</span>
        <span class="ad-muted">{{ categorias.length }} categorías</span>
      </div>
      <span class="ad-muted flex items-center gap-2" style="font-size: 12px">
        <Icon name="cloud" style="width:14px;height:14px" /> Sincronizado · {{ horaSync }}
      </span>
    </div>

    <!-- Catálogo de productos -->
    <div class="ad-card" style="overflow: hidden">
      <div style="padding: 20px 22px 16px">
        <h2 class="ad-card-title">Catálogo de productos</h2>
        <p class="ad-card-sub">Precios de venta en soles · stock en unidades</p>

        <form class="flex gap-3 flex-wrap" style="margin-top: 16px" @submit.prevent="aplicarBusqueda">
          <div class="ad-input-icon" style="flex: 1; min-width: 220px">
            <Icon name="search" />
            <input v-model="busqueda" type="text" class="ad-input" placeholder="Buscar por código o nombre" />
          </div>
          <select v-model="categoriaFiltro" class="ad-select" style="width: 220px">
            <option value="">Todas las categorías</option>
            <option v-for="cat in categorias" :key="cat.id_categoria" :value="cat.id_categoria">{{ cat.nombre }}</option>
          </select>
          <button type="submit" class="ad-btn ad-btn-outline">Buscar</button>
        </form>
      </div>

      <div class="ad-table-wrap">
        <table class="ad-table">
          <thead>
            <tr>
              <th>Código</th>
              <th>Producto</th>
              <th>Categoría</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="productosFiltrados.length === 0">
              <td colspan="6" class="ad-empty">No se encontraron productos.</td>
            </tr>
            <tr v-for="p in productosPagina" :key="p.id_producto">
              <td class="ad-muted">{{ p.codigo }}</td>
              <td class="ad-strong">{{ p.nombre }}</td>
              <td>{{ obtenerNombreCategoria(p.id_categoria) }}</td>
              <td>S/ {{ formatear(p.precio) }}</td>
              <td>
                <span :class="esCritico(p) ? 'ad-danger-text' : ''">{{ p.stock }} un.</span>
                <span v-if="esCritico(p)" class="ad-badge danger" style="margin-left: 8px">
                  {{ Number(p.stock) < Number(p.stock_minimo) ? 'Bajo mínimo' : 'En mínimo' }}
                </span>
              </td>
              <td style="white-space: nowrap">
                <button class="ad-btn ad-btn-outline ad-btn-sm" title="Ajustar stock" @click="abrirModalMovimiento(p)">± Stock</button>
                <button class="ad-link ok" style="margin-left: 8px" @click="editarProducto(p)">Editar</button>
                <button class="ad-link danger" @click="borrarProducto(p.id_producto, p.nombre)">Borrar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="ad-foot">
        <span>Mostrando {{ productosPagina.length }} de {{ productosFiltrados.length }} productos</span>
        <div class="ad-pager">
          <button class="ad-btn ad-btn-outline ad-btn-sm" :disabled="pagina <= 1" @click="pagina--">Anterior</button>
          <span class="cur">{{ pagina }}</span>
          <button class="ad-btn ad-btn-outline ad-btn-sm" :disabled="pagina >= totalPaginas" @click="pagina++">Siguiente</button>
        </div>
      </div>
    </div>

    <div class="ad-callout danger">
      <Icon name="alert" /> Stock crítico: igual o inferior al mínimo configurado. La etiqueta complementa el color de alerta.
    </div>

    <!-- ============ MODAL: HISTORIAL DE MOVIMIENTOS ============ -->
    <div v-if="modalReporte" class="ad-overlay" @click.self="modalReporte = false">
      <div class="ad-modal wide">
        <div class="ad-modal-head">
          <div class="ad-modal-eyebrow">Inventario / Movimientos</div>
          <h2 class="ad-modal-title">Historial de movimientos</h2>
          <p class="ad-modal-sub">Trazabilidad de entradas y salidas del inventario.</p>
          <button class="ad-modal-x" @click="modalReporte = false"><Icon name="x" /></button>
        </div>

        <div class="ad-modal-body">
          <div class="flex gap-3 flex-wrap">
            <select v-model="histProducto" class="ad-select" style="flex: 1; min-width: 200px">
              <option value="">Todos los productos</option>
              <option v-for="n in productosEnHistorial" :key="n" :value="n">{{ n }}</option>
            </select>
            <div class="flex items-center gap-2">
              <input v-model="histDesde" type="date" class="ad-input" style="width: 150px" />
              <span class="ad-muted">—</span>
              <input v-model="histHasta" type="date" class="ad-input" style="width: 150px" />
            </div>
          </div>

          <div class="ad-table-wrap" style="border: 1px solid var(--ad-border); border-radius: 10px">
            <table class="ad-table">
              <thead>
                <tr>
                  <th>Fecha y hora</th>
                  <th>Producto</th>
                  <th>Tipo</th>
                  <th>Cantidad</th>
                  <th>Motivo</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="historialFiltrado.length === 0">
                  <td colspan="5" class="ad-empty">No hay movimientos registrados.</td>
                </tr>
                <tr v-for="h in historialFiltrado" :key="h.id_movimiento">
                  <td style="white-space: nowrap">{{ formatearFecha(h.created_at) }}</td>
                  <td>{{ nombreMovimiento(h) }}</td>
                  <td><span class="ad-badge" :class="claseTipoMovimiento(h.tipo_movimiento)">{{ etiquetaTipo(h.tipo_movimiento) }}</span></td>
                  <td style="white-space: nowrap">{{ cantidadConSigno(h) }}</td>
                  <td>{{ h.motivo || 'Sin detalle' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p class="ad-muted" style="font-size: 12px">{{ historialFiltrado.length }} movimientos · Las cantidades indican unidades de producto.</p>
        </div>

        <div class="ad-modal-foot">
          <span>Consulta de registros</span>
          <div class="btns"><button class="ad-btn ad-btn-primary" @click="modalReporte = false">Cerrar</button></div>
        </div>
      </div>
    </div>

    <!-- ============ MODAL: NUEVO / EDITAR PRODUCTO ============ -->
    <div v-if="modalProducto" class="ad-overlay" @click.self="modalProducto = false">
      <form class="ad-modal" @submit.prevent="guardarProducto">
        <div class="ad-modal-head">
          <div class="ad-modal-eyebrow">Inventario / Ficha de producto</div>
          <h2 class="ad-modal-title">{{ productoEditando.id_producto ? 'Editar producto' : 'Nuevo producto' }}</h2>
          <p class="ad-modal-sub">
            {{ productoEditando.id_producto ? 'Actualiza la ficha de ' + productoEditando.nombre + '.' : 'Completa la ficha para agregar un producto al inventario.' }}
          </p>
          <button type="button" class="ad-modal-x" @click="modalProducto = false"><Icon name="x" /></button>
        </div>

        <div class="ad-modal-body">
          <div v-if="errorModal" class="ad-error">{{ errorModal }}</div>

          <div>
            <label class="ad-label">Nombre del producto *</label>
            <input v-model="productoEditando.nombre" type="text" class="ad-input" required />
          </div>
          <div class="ad-grid2">
            <div>
              <label class="ad-label">Código *</label>
              <input v-model="productoEditando.codigo" type="text" class="ad-input" required />
            </div>
            <div>
              <label class="ad-label">Categoría *</label>
              <select v-model="productoEditando.id_categoria" class="ad-select" required>
                <option value="" disabled>Selecciona…</option>
                <option v-for="cat in categorias" :key="cat.id_categoria" :value="cat.id_categoria">{{ cat.nombre }}</option>
              </select>
            </div>
          </div>
          <div>
            <label class="ad-label">Precio de venta (S/) *</label>
            <input v-model.number="productoEditando.precio" type="number" step="0.01" min="0" class="ad-input" required />
          </div>
          <div class="ad-grid2">
            <div>
              <label class="ad-label">{{ productoEditando.id_producto ? 'Stock actual *' : 'Stock inicial *' }}</label>
              <input v-model.number="productoEditando.stock" type="number" min="0" class="ad-input" required />
            </div>
            <div>
              <label class="ad-label">Stock mínimo *</label>
              <input v-model.number="productoEditando.stock_minimo" type="number" min="0" class="ad-input" required />
            </div>
          </div>
          <div class="ad-note">
            <Icon name="info" />
            {{ productoEditando.id_producto ? 'Para registrar entradas o salidas con un motivo, utiliza ± Stock.' : 'Se mostrará una alerta cuando el stock sea igual o inferior al mínimo.' }}
          </div>
        </div>

        <div class="ad-modal-foot">
          <span>Los campos marcados con * son obligatorios</span>
          <div class="btns">
            <button type="button" class="ad-btn ad-btn-outline" @click="modalProducto = false">Cancelar</button>
            <button type="submit" class="ad-btn ad-btn-primary">{{ productoEditando.id_producto ? 'Guardar cambios' : 'Crear producto' }}</button>
          </div>
        </div>
      </form>
    </div>

    <!-- ============ MODAL: AJUSTAR STOCK ============ -->
    <div v-if="modalMovimiento" class="ad-overlay" @click.self="modalMovimiento = false">
      <form class="ad-modal" @submit.prevent="guardarMovimiento">
        <div class="ad-modal-head">
          <div class="ad-modal-eyebrow">Inventario / Ajuste</div>
          <h2 class="ad-modal-title">Ajustar stock</h2>
          <p class="ad-modal-sub">Registra un movimiento manual y su motivo.</p>
          <button type="button" class="ad-modal-x" @click="modalMovimiento = false"><Icon name="x" /></button>
        </div>

        <div class="ad-modal-body">
          <div v-if="errorModal" class="ad-error">{{ errorModal }}</div>

          <div class="ad-panel flex items-center justify-between gap-3">
            <div>
              <div class="ad-strong" style="font-size: 14px">{{ productoMovimiento?.nombre }}</div>
              <div class="ad-muted" style="font-size: 12px; margin-top: 4px">{{ productoMovimiento?.codigo }} · {{ obtenerNombreCategoria(productoMovimiento?.id_categoria) }}</div>
            </div>
            <span class="ad-badge" :class="productoMovimiento && esCritico(productoMovimiento) ? 'danger' : 'neutral'">
              Stock actual: {{ productoMovimiento?.stock }} un.
            </span>
          </div>

          <div>
            <label class="ad-label">Tipo de movimiento *</label>
            <div class="ad-seg" style="grid-template-columns: 1fr 1fr">
              <button type="button" :class="{ on: movimiento.tipo_movimiento === 'entrada' }" @click="movimiento.tipo_movimiento = 'entrada'">Entrada</button>
              <button type="button" :class="{ on: movimiento.tipo_movimiento === 'salida' }" @click="movimiento.tipo_movimiento = 'salida'">Salida</button>
            </div>
          </div>
          <div>
            <label class="ad-label">Cantidad *</label>
            <input v-model.number="movimiento.cantidad" type="number" min="1" class="ad-input" required />
          </div>
          <div>
            <label class="ad-label">Motivo *</label>
            <input v-model="movimiento.motivo" type="text" class="ad-input" placeholder="Ej: Compra, Merma…" required />
          </div>

          <div class="ad-panel info flex items-center justify-between gap-3" style="font-size: 13px">
            <span>
              {{ productoMovimiento?.stock }} actuales
              {{ movimiento.tipo_movimiento === 'entrada' ? '+' : '−' }}
              {{ Number(movimiento.cantidad) || 0 }} de {{ movimiento.tipo_movimiento }}
            </span>
            <strong style="font-size: 15px">Nuevo stock: {{ nuevoStock }} un.</strong>
          </div>
          <p class="ad-muted" style="font-size: 12px">El movimiento quedará asociado a {{ auth.usuario?.nombre || 'tu usuario' }} en el historial.</p>
        </div>

        <div class="ad-modal-foot">
          <span>Los campos marcados con * son obligatorios</span>
          <div class="btns">
            <button type="button" class="ad-btn ad-btn-outline" @click="modalMovimiento = false">Cancelar</button>
            <button type="submit" class="ad-btn ad-btn-primary">Guardar ajuste</button>
          </div>
        </div>
      </form>
    </div>

  </div>
</template>

<script setup>
import { onMounted, ref, computed, watch } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import Icon from '../components/Icon.vue'

const auth = useAuthStore()

// Variables reactivas
const productos = ref([])
const categorias = ref([])
const busqueda = ref('')
const terminoAplicado = ref('')
const categoriaFiltro = ref('')
const pagina = ref(1)
const POR_PAGINA = 8
const horaSync = ref('--:--')

const modalProducto = ref(false)
const productoEditando = ref({})
const errorModal = ref('')

const modalMovimiento = ref(false)
const productoMovimiento = ref(null)
const movimiento = ref({ tipo_movimiento: 'entrada', cantidad: 1, motivo: '' })

// Historial
const modalReporte = ref(false)
const historial = ref([])
const histProducto = ref('')
const histDesde = ref('')
const histHasta = ref('')

// ---------- Utilidades ----------
function formatear(valor) {
  return Number(valor || 0).toFixed(2)
}

function formatearFecha(fechaString) {
  if (!fechaString) return '-'
  const f = new Date(fechaString)
  const dia = f.toLocaleDateString('es-PE', { day: 'numeric', month: 'short' }).replace('.', '')
  const hora = f.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: false })
  return `${dia} · ${hora}`
}

function esCritico(p) {
  return Number(p.stock) <= Number(p.stock_minimo)
}

function obtenerNombreCategoria(id) {
  const cat = categorias.value.find(c => c.id_categoria === id)
  return cat ? cat.nombre : 'Sin categoría'
}

const criticos = computed(() => productos.value.filter(esCritico).length)

// ---------- Tabla: filtro + paginación ----------
function aplicarBusqueda() {
  terminoAplicado.value = busqueda.value
  pagina.value = 1
}
watch(busqueda, (v) => { if (v === '') aplicarBusqueda() })
watch(categoriaFiltro, () => { pagina.value = 1 })

const productosFiltrados = computed(() => {
  const t = terminoAplicado.value.toLowerCase()
  return productos.value.filter(p => {
    const coincideTexto = (p.nombre || '').toLowerCase().includes(t) || (p.codigo && p.codigo.toLowerCase().includes(t))
    const coincideCategoria = categoriaFiltro.value === '' || p.id_categoria === categoriaFiltro.value
    return coincideTexto && coincideCategoria
  })
})

const totalPaginas = computed(() => Math.max(1, Math.ceil(productosFiltrados.value.length / POR_PAGINA)))
const productosPagina = computed(() => {
  const ini = (pagina.value - 1) * POR_PAGINA
  return productosFiltrados.value.slice(ini, ini + POR_PAGINA)
})

// ---------- Carga de datos ----------
async function cargarDatos() {
  try {
    const resCat = await api.get('/categorias')
    categorias.value = resCat.data
    const resProd = await api.get('/productos')
    productos.value = resProd.data.data ?? resProd.data
    horaSync.value = new Date().toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: false })
    if (pagina.value > totalPaginas.value) pagina.value = totalPaginas.value
  } catch (error) {
    console.error('Error cargando inventario:', error)
  }
}

// ---------- Producto: nuevo, editar, borrar ----------
function abrirModalNuevo() {
  productoEditando.value = { id_categoria: '', codigo: '', codigo_barras: '', nombre: '', precio: 0, stock: 0, stock_minimo: 5 }
  errorModal.value = ''
  modalProducto.value = true
}

function editarProducto(p) {
  productoEditando.value = { ...p }
  errorModal.value = ''
  modalProducto.value = true
}

async function guardarProducto() {
  errorModal.value = ''
  try {
    if (productoEditando.value.id_producto) {
      await api.put(`/productos/${productoEditando.value.id_producto}`, productoEditando.value)
    } else {
      await api.post('/productos', productoEditando.value)
    }
    modalProducto.value = false
    cargarDatos()
  } catch (e) {
    errorModal.value = e.response?.data?.message || 'Error al guardar el producto.'
  }
}

async function borrarProducto(id, nombre) {
  if (!id) {
    alert('Error: El ID del producto no es válido.')
    return
  }
  if (!confirm(`¿Dar de baja el producto "${nombre}"?`)) return

  try {
    await api.delete(`/productos/${id}`)
    cargarDatos()
  } catch (e) {
    console.error('Error detallado al eliminar:', e)
    alert(e.response?.data?.message || 'Error al eliminar el producto. Revisa la consola.')
  }
}

// ---------- Ajuste de stock ----------
function abrirModalMovimiento(p) {
  productoMovimiento.value = p
  movimiento.value = { tipo_movimiento: 'entrada', cantidad: 1, motivo: '' }
  errorModal.value = ''
  modalMovimiento.value = true
}

const nuevoStock = computed(() => {
  const actual = Number(productoMovimiento.value?.stock || 0)
  const cant = Number(movimiento.value.cantidad) || 0
  return movimiento.value.tipo_movimiento === 'entrada' ? actual + cant : actual - cant
})

async function guardarMovimiento() {
  errorModal.value = ''
  if (nuevoStock.value < 0) {
    errorModal.value = 'La salida supera el stock disponible.'
    return
  }
  try {
    const payload = {
      tipo_movimiento: movimiento.value.tipo_movimiento,
      cantidad: parseInt(movimiento.value.cantidad),
      motivo: movimiento.value.motivo,
      id_usuario: auth.usuario?.id_usuario || auth.user?.id_usuario || 1,
    }
    await api.post(`/productos/${productoMovimiento.value.id_producto}/movimiento`, payload)
    modalMovimiento.value = false
    cargarDatos()
  } catch (e) {
    errorModal.value = e.response?.data?.message || e.response?.data?.error || 'Error interno del servidor.'
  }
}

// ---------- Historial de movimientos ----------
function nombreMovimiento(h) {
  return h.producto?.nombre || h.nombre_referencia || 'Producto eliminado'
}

function etiquetaTipo(tipo) {
  const t = (tipo || '').toLowerCase()
  return t.charAt(0).toUpperCase() + t.slice(1)
}

function claseTipoMovimiento(tipo) {
  const t = (tipo || '').toLowerCase()
  if (t === 'entrada' || t === 'creacion' || t === 'creación') return 'info'
  if (t === 'salida') return 'warn'
  if (t === 'eliminacion' || t === 'eliminación') return 'danger'
  return 'neutral'
}

function cantidadConSigno(h) {
  if (h.cantidad === null || h.cantidad === undefined || h.cantidad === '') return '-'
  const n = Math.abs(Number(h.cantidad))
  const t = (h.tipo_movimiento || '').toLowerCase()
  if (t === 'salida') return `−${n} un.`
  if (t === 'entrada') return `+${n} un.`
  return `${n} un.`
}

const productosEnHistorial = computed(() => {
  const nombres = new Set(historial.value.map(nombreMovimiento))
  return [...nombres].sort((a, b) => a.localeCompare(b))
})

const historialFiltrado = computed(() => {
  return historial.value.filter(h => {
    if (histProducto.value && nombreMovimiento(h) !== histProducto.value) return false
    const dia = String(h.created_at || '').slice(0, 10)
    if (histDesde.value && dia && dia < histDesde.value) return false
    if (histHasta.value && dia && dia > histHasta.value) return false
    return true
  })
})

async function abrirReporte() {
  try {
    const { data } = await api.get('/movimientos')
    historial.value = data.data || data
    histProducto.value = ''
    histDesde.value = ''
    histHasta.value = ''
    modalReporte.value = true
  } catch (error) {
    console.error('Error al cargar historial:', error)
    alert("No se pudo cargar el historial. Asegúrate de que el endpoint '/movimientos' exista en tu backend de Laravel.")
  }
}

onMounted(cargarDatos)
</script>
