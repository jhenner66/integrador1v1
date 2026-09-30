<template>
  <div class="min-h-screen bg-fondo p-4 md:p-8 font-sans">
    
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <div>
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Inventario de Productos</h1>
        <p class="text-gray-500 mt-1">Gestiona tu catálogo, precios y niveles de stock</p>
      </div>
      <div class="flex gap-3 w-full md:w-auto">
        <!-- Nuevo Botón de Reporte -->
        <button @click="abrirReporte" class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all flex items-center gap-2">
          📄 Historial
        </button>
        <button @click="abrirModalNuevo" class="bg-primario hover:bg-teal-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
          <span>+</span> Nuevo Producto
        </button>
      </div>
    </div>

    <!-- Buscador y Filtros -->
    <div class="bg-white p-4 rounded-t-2xl border-b border-gray-100 flex flex-col md:flex-row gap-4 items-center justify-between shadow-sm">
      <div class="w-full md:w-1/2 relative">
        <input type="text" v-model="busqueda" placeholder="Buscar por código o nombre..." 
          class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500 text-gray-700 bg-gray-50 focus:bg-white transition-all">
      </div>
      <div class="w-full md:w-auto flex gap-2">
        <select v-model="categoriaFiltro" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-teal-500 appearance-none">
          <option value="">Todas las Categorías</option>
          <option v-for="cat in categorias" :key="cat.id_categoria" :value="cat.id_categoria">
            {{ cat.nombre }}
          </option>
        </select>
      </div>
    </div>

    <!-- Tabla Principal Conectada -->
    <div class="bg-white rounded-b-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
              <th class="p-4 font-semibold w-24">Código</th>
              <th class="p-4 font-semibold">Producto</th>
              <th class="p-4 font-semibold">Categoría</th>
              <th class="p-4 font-semibold text-right">Precio Venta</th>
              <th class="p-4 font-semibold text-center">Stock</th>
              <th class="p-4 font-semibold text-center">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
            <tr v-if="productosFiltrados.length === 0">
              <td colspan="6" class="p-8 text-center text-gray-500 font-medium">No se encontraron productos.</td>
            </tr>
            <tr v-for="p in productosFiltrados" :key="p.id_producto" class="hover:bg-gray-50 transition-colors">
              <td class="p-4 font-medium text-gray-500">{{ p.codigo }}</td>
              <td class="p-4 font-bold text-gray-900">{{ p.nombre }}</td>
              <td class="p-4"><span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-semibold">{{ obtenerNombreCategoria(p.id_categoria) }}</span></td>
              <td class="p-4 text-right font-bold text-gray-800">S/ {{ formatear(p.precio) }}</td>
              <td class="p-4 text-center">
                <span :class="p.stock <= p.stock_minimo ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'" class="px-3 py-1 rounded-full text-xs font-bold">{{ p.stock }}</span>
              </td>
              <td class="p-4 text-center">
                <button @click="abrirModalMovimiento(p)" class="text-emerald-600 hover:text-emerald-800 font-bold px-2 transition-colors" title="Ajustar Stock">± Stock</button>
                <button @click="editarProducto(p)" class="text-blue-500 hover:text-blue-700 font-bold px-2 transition-colors">Editar</button>
                <!-- Botón Borrar Activado -->
                <button @click="borrarProducto(p.id_producto, p.nombre)" class="text-red-500 hover:text-red-700 font-bold px-2 transition-colors">Borrar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="p-4 border-t border-gray-100 text-sm text-gray-500">
        Mostrando <strong>{{ productosFiltrados.length }}</strong> productos en pantalla
      </div>
    </div>

    <!-- MODAL: REPORTE DE HISTORIAL -->
    <div v-if="modalReporte" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl w-full max-w-4xl shadow-xl flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-2xl">
          <div>
            <h2 class="text-xl font-bold text-gray-800">Historial de Movimientos</h2>
            <p class="text-sm text-gray-500 mt-1">Entradas, salidas, creaciones y eliminaciones recientes</p>
          </div>
          <button @click="modalReporte = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
        </div>
        
        <div class="overflow-y-auto p-0 flex-1">
          <table class="w-full text-left border-collapse">
            <thead class="sticky top-0 bg-white shadow-sm">
              <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                <th class="p-4 font-semibold">Fecha y Hora</th>
                <th class="p-4 font-semibold">Producto</th>
                <th class="p-4 font-semibold">Tipo</th>
                <th class="p-4 font-semibold text-center">Cant.</th>
                <th class="p-4 font-semibold">Motivo / Detalle</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
              <tr v-if="historial.length === 0">
                <td colspan="5" class="p-8 text-center text-gray-500">No hay movimientos registrados.</td>
              </tr>
              <tr v-for="h in historial" :key="h.id_movimiento" class="hover:bg-gray-50">
                <td class="p-4 text-gray-500 font-medium">{{ formatearFecha(h.created_at) }}</td>
                <td class="p-4 font-bold text-gray-800">{{ h.producto?.nombre || h.nombre_referencia || 'Producto Eliminado' }}</td>
                <td class="p-4">
                  <span :class="colorTipoMovimiento(h.tipo_movimiento)" class="px-3 py-1 rounded-full text-xs font-bold uppercase">
                    {{ h.tipo_movimiento }}
                  </span>
                </td>
                <td class="p-4 text-center font-bold">{{ h.cantidad || '-' }}</td>
                <td class="p-4 text-gray-600">{{ h.motivo || 'Sin detalle' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl text-right">
          <button @click="modalReporte = false" class="px-6 py-2 bg-gray-800 hover:bg-gray-900 text-white font-bold rounded-lg shadow-md">Cerrar Reporte</button>
        </div>
      </div>
    </div>

    <!-- MODALES DE PRODUCTO Y STOCK (se mantienen igual que antes, ocultos por brevedad visual pero funcionales en el código) -->
    <div v-if="modalProducto" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
        <h2 class="text-xl font-bold text-gray-800 mb-4">{{ productoEditando.id_producto ? 'Editar Producto' : 'Nuevo Producto' }}</h2>
        <div v-if="errorModal" class="mb-4 text-red-600 text-sm font-semibold bg-red-50 p-3 rounded-lg">{{ errorModal }}</div>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" v-model="productoEditando.nombre" class="mt-1 w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Código</label>
              <input type="text" v-model="productoEditando.codigo" class="mt-1 w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Categoría</label>
              <select v-model="productoEditando.id_categoria" class="mt-1 w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 bg-white">
                <option v-for="cat in categorias" :key="cat.id_categoria" :value="cat.id_categoria">{{ cat.nombre }}</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Precio</label>
              <input type="number" step="0.1" v-model="productoEditando.precio" class="mt-1 w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Stock</label>
              <input type="number" v-model="productoEditando.stock" class="mt-1 w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Mínimo</label>
              <input type="number" v-model="productoEditando.stock_minimo" class="mt-1 w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
            </div>
          </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
          <button @click="modalProducto = false" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 font-semibold rounded-lg">Cancelar</button>
          <button @click="guardarProducto" class="px-4 py-2 bg-primario hover:bg-teal-700 text-white font-bold rounded-lg shadow-md">Guardar</button>
        </div>
      </div>
    </div>

    <div v-if="modalMovimiento" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
        <h2 class="text-xl font-bold text-gray-800 mb-2">Ajustar Stock</h2>
        <p class="text-sm text-gray-500 mb-4">{{ productoMovimiento.nombre }} (Stock actual: {{ productoMovimiento.stock }})</p>
        <div v-if="errorModal" class="mb-4 text-red-600 text-sm font-semibold bg-red-50 p-3 rounded-lg">{{ errorModal }}</div>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Tipo de movimiento</label>
            <select v-model="movimiento.tipo_movimiento" class="mt-1 w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 bg-white">
              <option value="entrada">Entrada (+)</option>
              <option value="salida">Salida (-)</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Cantidad</label>
            <input type="number" min="1" v-model="movimiento.cantidad" class="mt-1 w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Motivo</label>
            <input type="text" v-model="movimiento.motivo" placeholder="Ej: Compra, Merma..." class="mt-1 w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
          </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
          <button @click="modalMovimiento = false" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 font-semibold rounded-lg">Cancelar</button>
          <button @click="guardarMovimiento" class="px-4 py-2 bg-primario hover:bg-teal-700 text-white font-bold rounded-lg shadow-md">Confirmar</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()

// Variables Reactivas
const productos = ref([])
const categorias = ref([])
const busqueda = ref('')
const categoriaFiltro = ref('')

const modalProducto = ref(false)
const productoEditando = ref({})
const errorModal = ref('')

const modalMovimiento = ref(false)
const productoMovimiento = ref(null)
const movimiento = ref({ tipo_movimiento: 'entrada', cantidad: 1, motivo: '' })

// Variables del Nuevo Reporte
const modalReporte = ref(false)
const historial = ref([])

// Utilidades
function formatear(valor) {
  return Number(valor || 0).toFixed(2)
}

function formatearFecha(fechaString) {
  if (!fechaString) return '-';
  const fecha = new Date(fechaString);
  return fecha.toLocaleString('es-PE', { 
    year: 'numeric', month: 'short', day: 'numeric', 
    hour: '2-digit', minute: '2-digit', hour12: true 
  });
}

function colorTipoMovimiento(tipo) {
  const t = tipo.toLowerCase();
  if (t === 'entrada' || t === 'creacion' || t === 'creación') return 'bg-emerald-100 text-emerald-700';
  if (t === 'salida') return 'bg-amber-100 text-amber-700';
  if (t === 'eliminacion' || t === 'eliminación') return 'bg-red-100 text-red-700';
  return 'bg-gray-100 text-gray-700';
}

function obtenerNombreCategoria(id) {
  const cat = categorias.value.find(c => c.id_categoria === id)
  return cat ? cat.nombre : 'Sin categoría'
}

// Cargas de Datos
async function cargarDatos() {
  try {
    const resCat = await api.get('/categorias')
    categorias.value = resCat.data
    const resProd = await api.get('/productos')
    productos.value = resProd.data.data ?? resProd.data
  } catch (error) {
    console.error("Error cargando inventario:", error)
  }
}

// Filtro en Tiempo Real
const productosFiltrados = computed(() => {
  return productos.value.filter(p => {
    const coincideTexto = p.nombre.toLowerCase().includes(busqueda.value.toLowerCase()) || 
                          (p.codigo && p.codigo.toLowerCase().includes(busqueda.value.toLowerCase()))
    const coincideCategoria = categoriaFiltro.value === '' || p.id_categoria === categoriaFiltro.value
    return coincideTexto && coincideCategoria
  })
})

// Acciones de Productos (Nuevo, Editar, Borrar)
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

// NUEVO: Lógica para Borrar Producto
async function borrarProducto(id, nombre) {
  console.log("Intentando borrar producto con ID:", id);
  
  if (!id) {
    alert("Error: El ID del producto no es válido.");
    return;
  }

  try {
    console.log(`Enviando petición DELETE a /productos/${id}`);
    const res = await api.delete(`/productos/${id}`);
    console.log("Respuesta exitosa del servidor:", res.data);
    alert("¡Producto dado de baja correctamente!");
    cargarDatos(); // Recarga la tabla
  } catch (e) {
    console.error("Error detallado al eliminar:", e);
    alert(e.response?.data?.message || 'Error al eliminar el producto. Revisa la consola.');
  }
}

// Acciones de Movimientos de Stock
function abrirModalMovimiento(p) {
  productoMovimiento.value = p
  movimiento.value = { tipo_movimiento: 'entrada', cantidad: 1, motivo: '' }
  errorModal.value = ''
  modalMovimiento.value = true
}

async function guardarMovimiento() {
  errorModal.value = ''
  try {
    const payload = {
      tipo_movimiento: movimiento.value.tipo_movimiento,
      cantidad: parseInt(movimiento.value.cantidad),
      motivo: movimiento.value.motivo,
      id_usuario: auth.usuario?.id_usuario || auth.user?.id_usuario || 1 
    }
    await api.post(`/productos/${productoMovimiento.value.id_producto}/movimiento`, payload)
    modalMovimiento.value = false
    cargarDatos()
  } catch (e) {
    errorModal.value = e.response?.data?.message || e.response?.data?.error || 'Error interno del servidor.'
  }
}

// NUEVO: Lógica del Reporte
async function abrirReporte() {
  try {
    // Intentamos cargar el historial desde el backend
    const { data } = await api.get('/movimientos')
    historial.value = data.data || data
    modalReporte.value = true
  } catch (error) {
    console.error("Error al cargar historial:", error)
    alert("No se pudo cargar el historial. Asegúrate de que el endpoint '/movimientos' exista en tu backend de Laravel.");
  }
}

onMounted(cargarDatos)
</script>