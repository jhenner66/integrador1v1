<template>
  <div class="min-h-screen bg-fondo p-4 md:p-8 font-sans">
    
    <!-- Encabezado y Botón Nuevo -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <div>
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Gestión de Pedidos</h1>
        <p class="text-gray-500 mt-1">Controla las órdenes de compra y abastecimiento de proveedores</p>
      </div>
      <button v-if="auth.esAdministrador || auth.esAlmacen" @click="abrirModalNuevoPedido" 
        class="bg-primario hover:bg-teal-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
        <span>+</span> Nuevo pedido
      </button>
    </div>

    <!-- 4 Tarjetas KPI de Resumen de Estados -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 flex justify-between items-center">
        <div>
          <p class="text-sm font-medium text-gray-500 mb-1">Pendientes</p>
          <h3 class="text-3xl font-bold text-gray-800">{{ resumen.pendientes ?? 0 }}</h3>
        </div>
        <div class="bg-amber-100 p-3 rounded-xl text-2xl">⏳</div>
      </div>
      <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 flex justify-between items-center">
        <div>
          <p class="text-sm font-medium text-gray-500 mb-1">En camino</p>
          <h3 class="text-3xl font-bold text-gray-800">{{ resumen.en_camino ?? 0 }}</h3>
        </div>
        <div class="bg-blue-100 p-3 rounded-xl text-2xl">🚚</div>
      </div>
      <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 flex justify-between items-center">
        <div>
          <p class="text-sm font-medium text-gray-500 mb-1">Recibidos</p>
          <h3 class="text-3xl font-bold text-gray-800">{{ resumen.recibidos ?? 0 }}</h3>
        </div>
        <div class="bg-emerald-100 p-3 rounded-xl text-2xl">✅</div>
      </div>
      <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 flex justify-between items-center">
        <div>
          <p class="text-sm font-medium text-gray-500 mb-1">Cancelados</p>
          <h3 class="text-3xl font-bold text-gray-800">{{ resumen.cancelados ?? 0 }}</h3>
        </div>
        <div class="bg-red-100 p-3 rounded-xl text-2xl">❌</div>
      </div>
    </div>

    <!-- Panel Principal y Filtro -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <h2 class="text-lg font-bold text-gray-800">Pedidos a proveedores</h2>
        <div class="w-full md:w-auto">
          <select v-model="filtroEstado" @change="cargarPedidos" 
            class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-teal-500 w-full md:w-auto">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="en_camino">En camino</option>
            <option value="recibido">Recibido</option>
            <option value="cancelado">Cancelado</option>
          </select>
        </div>
      </div>

      <div class="overflow-x-auto" v-if="pedidos.length">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
              <th class="p-4 font-semibold">N° Pedido</th>
              <th class="p-4 font-semibold">Proveedor</th>
              <th class="p-4 font-semibold">Fecha</th>
              <th class="p-4 font-semibold text-center">Estado</th>
              <th class="p-4 font-semibold text-right">Total</th>
              <th class="p-4 font-semibold text-center">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
            <tr v-for="p in pedidos" :key="p.id_pedido" class="hover:bg-gray-50 transition-colors">
              <td class="p-4 font-bold text-gray-900">#{{ p.id_pedido }}</td>
              <td class="p-4 font-medium text-gray-700">{{ p.proveedor?.nombre }}</td>
              <td class="p-4 text-gray-500">{{ p.fecha }}</td>
              <td class="p-4 text-center">
                <span :class="obtenerColorEstado(p.estado)" class="px-3 py-1 rounded-full text-xs font-bold uppercase">
                  {{ etiquetaEstado(p.estado) }}
                </span>
              </td>
              <td class="p-4 text-right font-bold text-gray-800">S/ {{ Number(p.total).toFixed(2) }}</td>
              <td class="p-4 text-center">
                <select
                  v-if="(auth.esAdministrador || auth.esAlmacen) && p.estado !== 'recibido' && p.estado !== 'cancelado'"
                  @change="cambiarEstado(p, $event.target.value)"
                  class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs bg-gray-50 focus:outline-none focus:ring-2 focus:ring-teal-500"
                >
                  <option value="">Cambiar estado...</option>
                  <option value="en_camino" :disabled="p.estado==='en_camino'">En camino</option>
                  <option value="recibido">Recibido</option>
                  <option value="cancelado">Cancelado</option>
                </select>
                <span v-else class="text-gray-400">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="p-12 text-center text-gray-500 font-medium">
        No hay pedidos registrados.
      </div>
    </div>

    <!-- MODAL: NUEVO PEDIDO -->
    <div v-if="modalNuevo" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Nuevo Pedido a Proveedor</h2>
        
        <form @submit.prevent="guardarPedido" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
            <select v-model="nuevoPedido.id_proveedor" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 bg-white">
              <option disabled value="">Seleccione un proveedor</option>
              <option v-for="pr in proveedores" :key="pr.id_proveedor" :value="pr.id_proveedor">{{ pr.nombre }}</option>
            </select>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
              <input v-model="nuevoPedido.fecha" type="date" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Fecha estimada de entrega</label>
              <input v-model="nuevoPedido.fecha_estimada_entrega" type="date" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none" />
            </div>
          </div>

          <div class="border-t border-gray-100 pt-4 mt-4">
            <h3 class="font-bold text-gray-800 mb-2">Productos del Pedido</h3>
            
            <div v-for="(d, idx) in nuevoPedido.detalles" :key="idx" class="flex flex-col md:flex-row gap-2 mb-3 items-center bg-gray-50 p-3 rounded-xl border border-gray-100">
              <select v-model="d.id_producto" required class="w-full md:flex-2 p-2 border border-gray-300 rounded-lg bg-white text-sm">
                <option disabled value="">Seleccione producto</option>
                <option v-for="pr in productos" :key="pr.id_producto" :value="pr.id_producto">{{ pr.nombre }}</option>
              </select>
              <input v-model.number="d.cantidad" type="number" min="1" placeholder="Cant." class="w-full md:w-24 p-2 border border-gray-300 rounded-lg text-sm" required />
              <input v-model.number="d.precio_unitario" type="number" step="0.01" placeholder="P. Unit." class="w-full md:w-28 p-2 border border-gray-300 rounded-lg text-sm" required />
              <button type="button" @click="nuevoPedido.detalles.splice(idx,1)" class="text-red-500 hover:text-red-700 font-bold px-2 py-1">✕</button>
            </div>

            <button type="button" @click="agregarLinea" class="text-sm font-bold text-primario hover:text-teal-700 mt-2 flex items-center gap-1">
              + Agregar otro producto
            </button>
          </div>

          <p v-if="errorModal" class="text-red-600 text-sm font-semibold bg-red-50 p-3 rounded-lg">{{ errorModal }}</p>
          
          <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="modalNuevo=false" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 font-semibold rounded-lg">Cancelar</button>
            <button type="submit" class="px-5 py-2 bg-primario hover:bg-teal-700 text-white font-bold rounded-lg shadow-md">GUARDAR PEDIDO</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()

const pedidos = ref([])
const resumen = ref({})
const filtroEstado = ref('')
const proveedores = ref([])
const productos = ref([])

const modalNuevo = ref(false)
const errorModal = ref('')
const nuevoPedido = ref({ id_proveedor: '', fecha: '', fecha_estimada_entrega: '', detalles: [] })

const etiquetas = { pendiente: 'Pendiente', en_camino: 'En camino', recibido: 'Recibido', cancelado: 'Cancelado' }
function etiquetaEstado(e) { return etiquetas[e] || e }

function obtenerColorEstado(estado) {
  if (!estado) return 'bg-gray-100 text-gray-700';
  const e = estado.toLowerCase();
  if (e === 'recibido') return 'bg-emerald-100 text-emerald-700';
  if (e === 'en_camino') return 'bg-blue-100 text-blue-700';
  if (e === 'pendiente') return 'bg-amber-100 text-amber-700';
  if (e === 'cancelado') return 'bg-red-100 text-red-700';
  return 'bg-gray-100 text-gray-700';
}

async function cargarPedidos() {
  try {
    const { data } = await api.get('/pedidos', { params: { estado: filtroEstado.value } })
    pedidos.value = data.pedidos.data ?? data.pedidos
    resumen.value = data.resumen_estados
  } catch (error) {
    console.error("Error al cargar pedidos:", error)
  }
}

async function cargarProveedores() {
  try {
    const { data } = await api.get('/proveedores')
    proveedores.value = data
  } catch (error) {
    console.error("Error al cargar proveedores:", error)
  }
}

async function cargarProductosBase() {
  try {
    const { data } = await api.get('/productos', { params: { per_page: 100 } })
    productos.value = data.data ?? data
  } catch (error) {
    console.error("Error al cargar productos base:", error)
  }
}

function abrirModalNuevoPedido() {
  nuevoPedido.value = { id_proveedor: '', fecha: new Date().toISOString().slice(0, 10), fecha_estimada_entrega: '', detalles: [] }
  agregarLinea()
  errorModal.value = ''
  modalNuevo.value = true
}

function agregarLinea() {
  nuevoPedido.value.detalles.push({ id_producto: '', cantidad: 1, precio_unitario: 0 })
}

async function guardarPedido() {
  errorModal.value = ''
  try {
    await api.post('/pedidos', nuevoPedido.value)
    modalNuevo.value = false
    cargarPedidos()
  } catch (e) {
    errorModal.value = e.response?.data?.message || 'Error al registrar el pedido.'
  }
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