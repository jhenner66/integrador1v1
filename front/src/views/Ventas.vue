<template>
  <div class="min-h-screen bg-fondo p-4 md:p-8 font-sans">
    
    <!-- Encabezado -->
    <div class="mb-8">
      <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Gestión de Ventas & POS</h1>
      <p class="text-gray-500 mt-1">Registra transacciones, métodos de pago y emite comprobantes oficiales</p>
    </div>

    <!-- SECCIÓN: REGISTRAR VENTA -->
    <div v-if="auth.esAdministrador || auth.esVendedor" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
      <h2 class="text-xl font-bold text-gray-800 mb-4">Registrar Nueva Venta</h2>
      
      <!-- Líneas de Productos -->
      <div class="space-y-3 mb-4">
        <div v-for="(d, idx) in nuevaVenta.detalles" :key="idx" class="flex flex-col md:flex-row gap-3 items-center bg-gray-50 p-3 rounded-xl border border-gray-100">
          <select v-model="d.id_producto" required class="w-full md:flex-2 p-2.5 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-teal-500">
            <option value="" disabled>Seleccione producto</option>
            <option v-for="p in productos" :key="p.id_producto" :value="p.id_producto">
              {{ p.nombre }} (Stock: {{ p.stock }}) — S/ {{ Number(p.precio).toFixed(2) }}
            </option>
          </select>
          <input v-model.number="d.cantidad" type="number" min="1" placeholder="Cantidad" class="w-full md:w-32 p-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500" required />
          <button type="button" @click="nuevaVenta.detalles.splice(idx,1)" class="text-red-500 hover:text-red-700 font-bold px-3 py-2">✕</button>
        </div>
      </div>

      <div class="flex justify-between items-center mb-6">
        <button type="button" @click="nuevaVenta.detalles.push({ id_producto: '', cantidad: 1 })" class="text-sm font-bold text-primario hover:text-teal-700 flex items-center gap-1">
          + Agregar otro producto
        </button>
        <div class="text-right">
          <span class="text-sm text-gray-500 mr-2">Total a Pagar:</span>
          <span class="text-2xl font-black text-gray-800">S/ {{ calcularTotalVenta.toFixed(2) }}</span>
        </div>
      </div>

      <!-- DATOS DE COMPROBANTE Y CLIENTE -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100 mb-6">
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Comprobante</label>
          <select v-model="nuevaVenta.tipo_comprobante" class="w-full p-2.5 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-teal-500">
            <option value="boleta">Boleta de Venta</option>
            <option value="factura">Factura</option>
          </select>
        </div>

        <!-- DNI o RUC -->
        <div v-if="nuevaVenta.tipo_comprobante === 'boleta'">
          <label class="block text-sm font-medium text-gray-700 mb-1">DNI del Cliente</label>
          <input v-model="nuevaVenta.dni_ruc" type="text" maxlength="8" placeholder="Ej: 72839210" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-teal-500" />
        </div>
        <div v-else>
          <label class="block text-sm font-medium text-gray-700 mb-1">RUC de la Empresa</label>
          <input v-model="nuevaVenta.dni_ruc" type="text" maxlength="11" placeholder="Ej: 20601234568" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-teal-500" />
        </div>

        <!-- Nombre del Cliente (Solo Boleta) -->
        <div v-if="nuevaVenta.tipo_comprobante === 'boleta'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Cliente</label>
          <input v-model="nuevaVenta.nombre_cliente" type="text" placeholder="Ej: Juan Pérez" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-teal-500" />
        </div>

        <!-- Razón Social (Solo Factura) -->
        <div v-if="nuevaVenta.tipo_comprobante === 'factura'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Razón Social</label>
          <input v-model="nuevaVenta.razon_social" type="text" placeholder="Ej: Minimarket SAC" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-teal-500" />
        </div>

        <!-- Método de Pago -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago</label>
          <select v-model="nuevaVenta.metodo_pago" class="w-full p-2.5 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-teal-500">
            <option value="efectivo">Efectivo 💵</option>
            <option value="tarjeta">Tarjeta 💳</option>
            <option value="billetera">Billetera Digital 📱</option>
          </select>
        </div>

        <!-- Efectivo con el que paga -->
        <div v-if="nuevaVenta.metodo_pago === 'efectivo'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Paga con (S/)</label>
          <input v-model.number="nuevaVenta.monto_efectivo" type="number" step="0.1" placeholder="Ej: 50.00" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-teal-500" />
        </div>

      </div>

      <div v-if="nuevaVenta.metodo_pago === 'efectivo' && nuevaVenta.monto_efectivo > 0" class="mb-4 p-3 bg-teal-50 border border-teal-100 rounded-lg flex justify-between items-center text-teal-800 font-semibold text-sm">
        <span>Cálculo de Vuelto:</span>
        <span class="text-lg">S/ {{ calcularVuelto.toFixed(2) }}</span>
      </div>

      <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-4 border-t border-gray-100">
        <div>
          <p v-if="errorVenta" class="text-red-600 text-sm font-semibold bg-red-50 p-3 rounded-lg">{{ errorVenta }}</p>
          <p v-if="mensajeVenta" class="text-emerald-700 text-sm font-bold bg-emerald-50 p-3 rounded-lg">{{ mensajeVenta }}</p>
        </div>
        <button @click="registrarYImprimirVenta" class="w-full sm:w-auto bg-primario hover:bg-teal-700 text-white font-bold py-3 px-8 rounded-xl shadow-md transition-all transform hover:-translate-y-0.5">
          Registrar e Imprimir Comprobante 🖨️
        </button>
      </div>
    </div>

    <!-- SECCIÓN: REPORTE DE VENTAS -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
      <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
        <h2 class="text-xl font-bold text-gray-800">Reporte de Ventas</h2>
        <div class="flex flex-wrap gap-2 items-center w-full lg:w-auto">
          <input v-model="fechaInicio" type="date" class="p-2 border border-gray-200 rounded-lg text-sm bg-gray-50" />
          <span class="text-gray-400">a</span>
          <input v-model="fechaFin" type="date" class="p-2 border border-gray-200 rounded-lg text-sm bg-gray-50" />
          <button @click="cargarReporte" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-sm transition">Filtrar</button>
          <button @click="abrirModalComprobantes" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-sm transition shadow-sm flex items-center gap-1">
            📄 Ver Comprobantes
          </button>
          <button @click="exportarExcel" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-sm transition shadow-sm">Exportar CSV</button>
        </div>
      </div>

      <div class="overflow-x-auto" v-if="reporte.ventas_por_dia?.length">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
              <th class="p-4 font-semibold">Fecha</th>
              <th class="p-4 font-semibold text-center">N° Vtas.</th>
              <th class="p-4 font-semibold text-right">Total Recaudado</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
            <tr v-for="v in reporte.ventas_por_dia" :key="v.dia" class="hover:bg-gray-50">
              <td class="p-4 font-medium text-gray-800">{{ v.dia }}</td>
              <td class="p-4 text-center">{{ v.num_ventas }}</td>
              <td class="p-4 text-right font-bold text-gray-900">S/ {{ Number(v.total).toFixed(2) }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="bg-gray-50 font-bold text-gray-900 border-t-2 border-gray-100">
              <td class="p-4">Total General</td>
              <td class="p-4 text-center">—</td>
              <td class="p-4 text-right text-primario">S/ {{ Number(reporte.total_general || 0).toFixed(2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
      <div v-else class="p-12 text-center text-gray-500 font-medium">
        No hay ventas registradas en el rango seleccionado.
      </div>
    </div>

    <!-- MODAL: HISTORIAL DE COMPROBANTES EMITIDOS -->
    <div v-if="modalComprobantes" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl w-full max-w-4xl shadow-xl flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-2xl">
          <div>
            <h2 class="text-xl font-bold text-gray-800">Comprobantes Emitidos</h2>
            <p class="text-sm text-gray-500 mt-1">Listado histórico de transacciones realizadas</p>
          </div>
          <button @click="modalComprobantes = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
        </div>
        
        <div class="overflow-y-auto p-4 flex-1">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                <th class="p-3 font-semibold">ID / N°</th>
                <th class="p-3 font-semibold">Fecha</th>
                <th class="p-3 font-semibold">Tipo</th>
                <th class="p-3 font-semibold">Cliente / Razón Social</th>
                <th class="p-3 font-semibold text-right">Total</th>
                <th class="p-3 font-semibold text-center">Acción</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
              <tr v-if="listaVentas.length === 0">
                <td colspan="6" class="p-8 text-center text-gray-500">No hay comprobantes registrados.</td>
              </tr>
              <tr v-for="v in listaVentas" :key="v.id_venta" class="hover:bg-gray-50">
                <td class="p-3 font-bold text-gray-900">#{{ v.id_venta }}</td>
                <td class="p-3 text-gray-500">{{ v.created_at || v.fecha }}</td>
                <td class="p-3 uppercase font-semibold text-xs">
                  <span :class="v.tipo_comprobante === 'factura' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'" class="px-2.5 py-1 rounded-full">
                    {{ v.tipo_comprobante || 'boleta' }}
                  </span>
                </td>
                <td class="p-3">{{ v.nombre_cliente || v.razon_social || 'Cliente Varios' }} <br><span class="text-xs text-gray-400">{{ v.dni_ruc ? 'Doc: ' + v.dni_ruc : '' }}</span></td>
                <td class="p-3 text-right font-bold text-gray-800">S/ {{ Number(v.total || v.monto_total || 0).toFixed(2) }}</td>
                <td class="p-3 text-center">
                  <button @click="reimprimirComprobante(v)" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-xs transition">
                    🖨️ Reimprimir
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl text-right">
          <button @click="modalComprobantes = false" class="px-6 py-2 bg-gray-800 hover:bg-gray-900 text-white font-bold rounded-lg shadow-md">Cerrar</button>
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

const productos = ref([])
const nuevaVenta = ref({ 
  detalles: [{ id_producto: '', cantidad: 1 }],
  metodo_pago: 'efectivo',
  tipo_comprobante: 'boleta',
  dni_ruc: '',
  nombre_cliente: '',
  razon_social: '',
  monto_efectivo: 0
})

const errorVenta = ref('')
const mensajeVenta = ref('')

const fechaInicio = ref(new Date(Date.now() - 6 * 86400000).toISOString().slice(0, 10))
const fechaFin = ref(new Date().toISOString().slice(0, 10))
const reporte = ref({})

const modalComprobantes = ref(false)
const listaVentas = ref([])

const calcularTotalVenta = computed(() => {
  let total = 0
  for (const d of nuevaVenta.value.detalles) {
    if (d.id_producto) {
      const prod = productos.value.find(p => p.id_producto === d.id_producto)
      if (prod) {
        total += Number(prod.precio) * Number(d.cantidad || 0)
      }
    }
  }
  return total
})

const calcularVuelto = computed(() => {
  const efectivo = Number(nuevaVenta.value.monto_efectivo || 0)
  const total = calcularTotalVenta.value
  return efectivo > total ? efectivo - total : 0
})

async function cargarProductos() {
  try {
    const { data } = await api.get('/productos', { params: { per_page: 100 } })
    productos.value = data.data ?? data
  } catch (error) {
    console.error("Error cargando productos:", error)
  }
}

function imprimirTicket(datosVenta, itemsDetalle, total, efectivo, vuelto) {
  const ventanaImpresion = window.open('', '_blank', 'width=450,height=650');
  if (!ventanaImpresion) {
    alert("Por favor, permite las ventanas emergentes (pop-ups) para imprimir el comprobante.");
    return;
  }
  
  const tipoComp = (datosVenta.tipo_comprobante || 'boleta').toUpperCase();
  const metodoPago = (datosVenta.metodo_pago || 'efectivo').toUpperCase();
  const nroAleatorio = Math.floor(Math.random() * 90000) + 10000;
  const fechaActual = new Date().toLocaleString();

  let filasProductos = '';
  itemsDetalle.forEach(item => {
    filasProductos += `
      <tr><td colspan="2" style="font-weight:bold;">${item.nombre}</td></tr>
      <tr><td>${item.cantidad} x ${Number(item.precio).toFixed(2)}</td><td style="text-align:right;">${(item.cantidad * item.precio).toFixed(2)}</td></tr>
    `;
  });

  const opGravada = (total / 1.18).toFixed(2);
  const igv = (total - (total / 1.18)).toFixed(2);

  // LOGICA PARA MOSTRAR LOS DATOS CORRECTOS EN EL TICKET
  let nombreAMostrar = datosVenta.tipo_comprobante === 'boleta' 
    ? (datosVenta.nombre_cliente || 'CLIENTE VARIOS') 
    : (datosVenta.razon_social || 'SIN RAZON SOCIAL');
  
  let documentoAMostrar = datosVenta.tipo_comprobante === 'boleta' 
    ? `DNI: ${datosVenta.dni_ruc || '00000000'}` 
    : `RUC: ${datosVenta.dni_ruc || '00000000000'}`;

  const htmlTicket = `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <title>Comprobante de Pago</title>
      <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; width: 320px; margin: 0 auto; padding: 10px; color: #000; background: #fff; }
        .center { text-align: center; }
        .line { border-bottom: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 3px 0; font-size: 11px; }
      </style>
    </head>
    <body>
      <div class="center" style="font-size: 14px; font-weight: bold;">MINIMARKET D'TODO</div>
      <div class="center">RUC: 20600000001</div>
      <div class="center">Av. Principal 123 - Chiclayo</div>
      <div class="line"></div>
      <div class="center" style="font-size: 13px; font-weight: bold;">${tipoComp} ELECTRÓNICA</div>
      <div>N°: B001 - 0000${nroAleatorio}</div>
      <div>Fecha: ${fechaActual}</div>
      <div>Cliente: ${nombreAMostrar}</div>
      <div>${documentoAMostrar}</div>
      <div class="line"></div>
      <table>
        <thead>
          <tr><th>Cant. / Descripción</th><th style="text-align:right;">Importe</th></tr>
        </thead>
        <tbody>
          ${filasProductos}
        </tbody>
      </table>
      <div class="line"></div>
      <table>
        <tr><td><b>OP. GRAVADA:</b></td><td style="text-align:right;">S/ ${opGravada}</td></tr>
        <tr><td><b>I.G.V. (18%):</b></td><td style="text-align:right;">S/ ${igv}</td></tr>
        <tr><td><b style="font-size:13px;">TOTAL A PAGAR:</b></td><td style="text-align:right; font-weight:bold; font-size:13px;">S/ ${total.toFixed(2)}</td></tr>
      </table>
      <div class="line"></div>
      <div>Método de Pago: ${metodoPago}</div>
      ${datosVenta.metodo_pago === 'efectivo' ? `
        <div>Efectivo Recibido: S/ ${Number(efectivo).toFixed(2)}</div>
        <div>Vuelto: S/ ${Number(vuelto).toFixed(2)}</div>
      ` : ''}
      <div class="line"></div>
      <div class="center" style="margin-top: 10px;">¡Gracias por su compra!</div>
      <script>
        window.onload = function() {
          window.print();
        }
      <\/script>
    </body>
    </html>
  `;

  ventanaImpresion.document.open();
  ventanaImpresion.document.write(htmlTicket);
  ventanaImpresion.document.close();
}

async function registrarYImprimirVenta() {
  errorVenta.value = ''
  mensajeVenta.value = ''

  if (nuevaVenta.value.metodo_pago === 'efectivo' && Number(nuevaVenta.value.monto_efectivo) < calcularTotalVenta.value) {
    errorVenta.value = 'El monto con el que paga es menor al total de la venta.'
    return
  }

  if (nuevaVenta.value.tipo_comprobante === 'factura' && !nuevaVenta.value.razon_social) {
    errorVenta.value = 'Para emitir factura debe ingresar la Razón Social.'
    return
  }

  try {
    const itemsParaTicket = []
    for (const d of nuevaVenta.value.detalles) {
      const prod = productos.value.find(p => p.id_producto === d.id_producto)
      if (prod) {
        itemsParaTicket.push({
          nombre: prod.nombre,
          cantidad: d.cantidad,
          precio: prod.precio
        })
      }
    }

    const payload = {
      detalles: nuevaVenta.value.detalles,
      metodo_pago: nuevaVenta.value.metodo_pago,
      tipo_comprobante: nuevaVenta.value.tipo_comprobante,
      dni_ruc: nuevaVenta.value.dni_ruc,
      nombre_cliente: nuevaVenta.value.nombre_cliente,
      razon_social: nuevaVenta.value.razon_social,
      monto_efectivo: nuevaVenta.value.monto_efectivo,
      vuelto: calcularVuelto.value
    }

    await api.post('/ventas', payload)
    
    mensajeVenta.value = `¡Venta registrada con éxito y comprobante emitido!`
    
    imprimirTicket(
      nuevaVenta.value, 
      itemsParaTicket, 
      calcularTotalVenta.value, 
      nuevaVenta.value.monto_efectivo, 
      calcularVuelto.value
    )
    
    nuevaVenta.value = { 
      detalles: [{ id_producto: '', cantidad: 1 }],
      metodo_pago: 'efectivo',
      tipo_comprobante: 'boleta',
      dni_ruc: '',
      nombre_cliente: '',
      razon_social: '',
      monto_efectivo: 0
    }
    
    cargarProductos()
    cargarReporte()
  } catch (e) {
    errorVenta.value = e.response?.data?.message || 'Error al registrar la venta en el servidor.'
  }
}

async function cargarReporte() {
  try {
    const { data } = await api.get('/ventas/reporte', {
      params: { fecha_inicio: fechaInicio.value, fecha_fin: fechaFin.value },
    })
    reporte.value = data
  } catch (error) {
    console.error("Error cargando reporte:", error)
  }
}

async function abrirModalComprobantes() {
  try {
    const { data } = await api.get('/ventas', {
      params: { fecha_inicio: fechaInicio.value, fecha_fin: fechaFin.value }
    })
    listaVentas.value = data.data || data
    modalComprobantes.value = true
  } catch (error) {
    console.error("Error al cargar listado de ventas:", error)
    alert("No se pudo obtener el historial de comprobantes.");
  }
}

function reimprimirComprobante(venta) {
  const items = venta.detalles ? venta.detalles.map(d => ({
    nombre: d.producto?.nombre || 'Producto',
    cantidad: d.cantidad,
    precio: d.precio_unitario || (d.pivot ? d.pivot.precio_unitario : 0)
  })) : [{ nombre: 'Consumo General / Venta #' + venta.id_venta, cantidad: 1, precio: venta.total || venta.monto_total }];

  imprimirTicket(
    { 
      tipo_comprobante: venta.tipo_comprobante || 'boleta', 
      dni_ruc: venta.dni_ruc, 
      nombre_cliente: venta.nombre_cliente,
      razon_social: venta.razon_social, 
      metodo_pago: venta.metodo_pago 
    },
    items,
    Number(venta.total || venta.monto_total || 0),
    Number(venta.monto_efectivo || venta.total || 0),
    Number(venta.vuelto || 0)
  );
}

async function exportarExcel() {
  try {
    const response = await api.get('/ventas/exportar/excel', {
      params: { fecha_inicio: fechaInicio.value, fecha_fin: fechaFin.value },
      responseType: 'blob',
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'reporte_ventas.csv')
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    alert("No se pudo exportar el archivo.")
  }
}

onMounted(() => {
  cargarProductos()
  cargarReporte()
})
</script>