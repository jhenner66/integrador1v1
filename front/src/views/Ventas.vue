<template>
  <div class="ad-page">

    <!-- Encabezado -->
    <div class="ad-head">
      <div>
        <div class="ad-eyebrow"><Icon name="lock" style="width:12px;height:12px" /> Acceso: Administrador y Vendedor</div>
        <h1 class="ad-title">Ventas y POS</h1>
        <p class="ad-subtitle">Registra una venta o consulta la recaudación de tu tienda.</p>
      </div>
      <span class="ad-badge neutral" style="font-size: 11px; padding: 6px 10px">Caja principal · {{ auth.usuario?.nombre || 'Usuario' }}</span>
    </div>

    <div class="ventas-grid">

      <!-- ============ REGISTRAR NUEVA VENTA ============ -->
      <section v-if="auth.esAdministrador || auth.esVendedor" class="ad-card" style="overflow: hidden">
        <div class="flex items-start justify-between gap-3" style="padding: 20px 22px 14px">
          <div>
            <h2 class="ad-card-title">Registrar nueva venta</h2>
            <p class="ad-card-sub">Venta en preparación · precios finales en soles</p>
          </div>
          <span class="ad-badge warn">Sin registrar</span>
        </div>

        <div style="padding: 0 22px 22px; display: flex; flex-direction: column; gap: 16px">

          <!-- Buscador de producto -->
          <div style="position: relative">
            <form class="flex gap-3" @submit.prevent="anadirDesdeBusqueda">
              <div class="ad-input-icon" style="flex: 1">
                <Icon name="search" />
                <input v-model="busquedaProducto" type="text" class="ad-input" placeholder="Código o nombre del producto" autocomplete="off" />
              </div>
              <button type="submit" class="ad-btn ad-btn-outline"><Icon name="plus" /> Añadir</button>
            </form>
            <div v-if="busquedaProducto.trim() && sugerencias.length" class="sugerencias">
              <button v-for="p in sugerencias" :key="p.id_producto" type="button" @click="anadirProducto(p)">
                <span><strong>{{ p.nombre }}</strong> <span class="ad-muted">· {{ p.codigo }}</span></span>
                <span class="ad-muted">S/ {{ Number(p.precio).toFixed(2) }} · {{ p.stock }} un.</span>
              </button>
            </div>
          </div>

          <!-- Líneas de la venta -->
          <div class="ad-table-wrap" style="border: 1px solid var(--ad-border); border-radius: 10px">
            <table class="ad-table">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Precio</th>
                  <th>Cant.</th>
                  <th>Importe</th>
                  <th style="width: 30px"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="lineas.length === 0">
                  <td colspan="5" class="ad-empty" style="padding: 24px">Busca un producto para empezar la venta.</td>
                </tr>
                <tr v-for="l in lineas" :key="l.producto.id_producto">
                  <td class="ad-strong">{{ l.producto.nombre }}</td>
                  <td>S/ {{ Number(l.producto.precio).toFixed(2) }}</td>
                  <td>
                    <div class="stepper">
                      <button type="button" @click="cambiarCantidad(l, -1)">−</button>
                      <span>{{ l.cantidad }}</span>
                      <button type="button" @click="cambiarCantidad(l, 1)">+</button>
                    </div>
                  </td>
                  <td>S/ {{ (Number(l.producto.precio) * l.cantidad).toFixed(2) }}</td>
                  <td><button type="button" class="ad-muted" style="background:none;border:0;cursor:pointer;font-size:16px" title="Quitar" @click="quitarLinea(l)">×</button></td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Comprobante -->
          <div>
            <label class="ad-label">Comprobante</label>
            <div class="ad-seg" style="grid-template-columns: 1fr 1fr">
              <button type="button" :class="{ on: nuevaVenta.tipo_comprobante === 'boleta' }" @click="nuevaVenta.tipo_comprobante = 'boleta'">Boleta</button>
              <button type="button" :class="{ on: nuevaVenta.tipo_comprobante === 'factura' }" @click="nuevaVenta.tipo_comprobante = 'factura'">Factura</button>
            </div>
          </div>

          <div v-if="nuevaVenta.tipo_comprobante === 'boleta'" class="ad-grid2" style="grid-template-columns: 1fr 1.6fr">
            <div>
              <label class="ad-label">DNI</label>
              <input v-model="nuevaVenta.dni_ruc" type="text" maxlength="8" class="ad-input" placeholder="Ej: 72839210" />
            </div>
            <div>
              <label class="ad-label">Nombre</label>
              <input v-model="nuevaVenta.nombre_cliente" type="text" class="ad-input" placeholder="Ej: Juan Pérez" />
            </div>
          </div>
          <div v-else class="ad-grid2" style="grid-template-columns: 1fr 1.6fr">
            <div>
              <label class="ad-label">RUC *</label>
              <input v-model="nuevaVenta.dni_ruc" type="text" maxlength="11" class="ad-input" placeholder="Ej: 20601234568" />
            </div>
            <div>
              <label class="ad-label">Razón social *</label>
              <input v-model="nuevaVenta.razon_social" type="text" class="ad-input" placeholder="Ej: Minimarket SAC" />
            </div>
          </div>

          <!-- Método de pago -->
          <div>
            <label class="ad-label">Método de pago</label>
            <div class="ad-seg" style="grid-template-columns: repeat(3, 1fr)">
              <button type="button" :class="{ on: nuevaVenta.metodo_pago === 'efectivo' }" @click="nuevaVenta.metodo_pago = 'efectivo'">Efectivo</button>
              <button type="button" :class="{ on: nuevaVenta.metodo_pago === 'tarjeta' }" @click="nuevaVenta.metodo_pago = 'tarjeta'">Tarjeta</button>
              <button type="button" :class="{ on: nuevaVenta.metodo_pago === 'billetera' }" @click="nuevaVenta.metodo_pago = 'billetera'">Billetera</button>
            </div>
          </div>

          <div v-if="nuevaVenta.metodo_pago === 'efectivo'" class="ad-grid2" style="grid-template-columns: 1fr 1fr; align-items: end">
            <div>
              <label class="ad-label">Importe recibido (S/) *</label>
              <input v-model.number="nuevaVenta.monto_efectivo" type="number" step="0.1" min="0" class="ad-input" placeholder="Ej: 50.00" />
            </div>
            <div class="ad-panel info" style="padding: 8px 14px">
              <div style="font-size: 11px">Vuelto a entregar</div>
              <div style="font-size: 18px; font-weight: 600">S/ {{ calcularVuelto.toFixed(2) }}</div>
            </div>
          </div>

          <!-- Totales -->
          <div class="ad-panel flex items-center justify-between gap-3 flex-wrap">
            <div class="ad-muted" style="font-size: 12px; line-height: 1.6">
              {{ lineas.length }} {{ lineas.length === 1 ? 'producto' : 'productos' }} · {{ totalUnidades }} unidades<br />
              Base: S/ {{ baseImponible.toFixed(2) }} · IGV (18%): S/ {{ igv.toFixed(2) }}
            </div>
            <div style="text-align: right">
              <div class="ad-muted" style="font-size: 10.5px; letter-spacing: .05em">TOTAL A COBRAR</div>
              <div style="font-size: 26px; font-weight: 600">S/ {{ calcularTotalVenta.toFixed(2) }}</div>
            </div>
          </div>

          <div v-if="errorVenta" class="ad-error">{{ errorVenta }}</div>
          <div v-if="mensajeVenta" class="ad-badge ok" style="padding: 10px 14px; font-size: 12px">{{ mensajeVenta }}</div>

          <div style="display: flex; justify-content: flex-end">
            <button type="button" class="ad-btn ad-btn-primary ad-btn-lg" :disabled="registrando" @click="registrarYImprimirVenta">
              <Icon name="printer" /> Registrar e imprimir comprobante
            </button>
          </div>
        </div>
      </section>

      <!-- ============ REPORTE DE VENTAS ============ -->
      <div style="display: flex; flex-direction: column; gap: 16px; min-width: 0">
        <section class="ad-card" style="overflow: hidden">
          <div style="padding: 20px 22px 14px">
            <h2 class="ad-card-title">Reporte de ventas</h2>
            <p class="ad-card-sub">Recaudación agrupada por día</p>

            <div class="ad-grid2" style="margin-top: 14px">
              <div>
                <label class="ad-label">Desde</label>
                <input v-model="fechaInicio" type="date" class="ad-input" />
              </div>
              <div>
                <label class="ad-label">Hasta</label>
                <input v-model="fechaFin" type="date" class="ad-input" />
              </div>
            </div>
            <div class="flex items-center justify-between" style="margin-top: 12px">
              <span class="ad-muted" style="font-size: 12px">{{ diasSeleccionados }} {{ diasSeleccionados === 1 ? 'día seleccionado' : 'días seleccionados' }}</span>
              <button class="ad-btn ad-btn-primary ad-btn-sm" @click="cargarReporte"><Icon name="filter" /> Filtrar</button>
            </div>
          </div>

          <div class="ad-panel info flex items-center justify-between" style="border-radius: 0; padding: 14px 22px">
            <div>
              <div style="font-size: 10.5px; letter-spacing: .05em">TOTAL RECAUDADO</div>
              <div style="font-size: 24px; font-weight: 600">S/ {{ formatearMiles(reporte.total_general) }}</div>
            </div>
            <div style="text-align: right">
              <div style="font-size: 10.5px; letter-spacing: .05em">VENTAS</div>
              <div style="font-size: 24px; font-weight: 600">{{ totalVentas }}</div>
            </div>
          </div>

          <div v-if="ventasPorDiaDesc.length" class="ad-table-wrap">
            <table class="ad-table">
              <thead>
                <tr>
                  <th>Día</th>
                  <th>N.º ventas</th>
                  <th>Total recaudado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="v in ventasPorDiaDesc" :key="v.dia">
                  <td>{{ etiquetaDia(v.dia) }}</td>
                  <td>{{ v.num_ventas }}</td>
                  <td class="ad-strong">S/ {{ formatearMiles(v.total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="ad-empty">No hay ventas registradas en el rango seleccionado.</div>

          <div style="padding: 14px 22px 18px; border-top: 1px solid var(--ad-border)">
            <div class="flex gap-3 flex-wrap">
              <button class="ad-btn ad-btn-outline ad-btn-sm" @click="abrirModalComprobantes"><Icon name="file" /> Ver comprobantes</button>
              <button class="ad-btn ad-btn-outline ad-btn-sm" @click="exportarExcel"><Icon name="download" /> Exportar CSV</button>
            </div>
            <p class="ad-muted" style="font-size: 11px; margin-top: 12px; line-height: 1.5">
              El reporte incluye ventas registradas. La venta en preparación aún no forma parte de estos totales.
            </p>
          </div>
        </section>

        <div class="ad-callout" style="margin-top: 0; background: #fff; border: 1px solid var(--ad-border)">
          <Icon name="info" /> Verifica el documento del cliente y el importe recibido antes de registrar la venta.
        </div>
      </div>
    </div>

    <!-- ============ MODAL: COMPROBANTES EMITIDOS ============ -->
    <div v-if="modalComprobantes" class="ad-overlay" @click.self="modalComprobantes = false">
      <div class="ad-modal wide">
        <div class="ad-modal-head">
          <div class="ad-modal-eyebrow">Ventas / Comprobantes</div>
          <h2 class="ad-modal-title">Comprobantes emitidos</h2>
          <p class="ad-modal-sub">Ventas del {{ formatearRango() }} · Consulta y reimpresión.</p>
          <button class="ad-modal-x" @click="modalComprobantes = false"><Icon name="x" /></button>
        </div>

        <div class="ad-modal-body">
          <div class="ad-input-icon">
            <Icon name="search" />
            <input v-model="busquedaComprobante" type="text" class="ad-input" placeholder="Buscar número, DNI o RUC" />
          </div>

          <div class="ad-table-wrap" style="border: 1px solid var(--ad-border); border-radius: 10px">
            <table class="ad-table">
              <thead>
                <tr>
                  <th>Comprobante</th>
                  <th>Cliente</th>
                  <th>Hora</th>
                  <th>Pago</th>
                  <th>Total</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="comprobantesFiltrados.length === 0">
                  <td colspan="6" class="ad-empty">No hay comprobantes registrados.</td>
                </tr>
                <tr v-for="v in comprobantesVisibles" :key="v.id_venta">
                  <td>
                    <div class="ad-strong">{{ numeroComprobante(v) }}</div>
                    <div class="ad-muted" style="font-size: 11px">{{ (v.tipo_comprobante || 'boleta') === 'factura' ? 'Factura' : 'Boleta' }}</div>
                  </td>
                  <td>
                    <div>{{ v.nombre_cliente || v.razon_social || 'Cliente varios' }}</div>
                    <div class="ad-muted" style="font-size: 11px">{{ v.dni_ruc ? ((v.tipo_comprobante === 'factura' ? 'RUC ' : 'DNI ') + v.dni_ruc) : '' }}</div>
                  </td>
                  <td>{{ horaVenta(v) }}</td>
                  <td style="text-transform: capitalize">{{ v.metodo_pago || '—' }}</td>
                  <td>S/ {{ Number(v.total || v.monto_total || 0).toFixed(2) }}</td>
                  <td>
                    <button class="ad-btn ad-btn-outline ad-btn-sm" @click="reimprimirComprobante(v)"><Icon name="printer" /> Reimprimir</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex items-center justify-between" style="font-size: 12px">
            <span class="ad-muted">Mostrando {{ comprobantesVisibles.length }} de {{ comprobantesFiltrados.length }} comprobantes · Total: S/ {{ formatearMiles(totalComprobantes) }}</span>
            <button v-if="comprobantesVisibles.length < comprobantesFiltrados.length" class="ad-btn ad-btn-outline ad-btn-sm" @click="limiteComprobantes += 10">Ver más</button>
          </div>
        </div>

        <div class="ad-modal-foot">
          <span>Consulta de registros</span>
          <div class="btns"><button class="ad-btn ad-btn-primary" @click="modalComprobantes = false">Cerrar</button></div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { onMounted, ref, computed, watch } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import Icon from '../components/Icon.vue'

const auth = useAuthStore()

const productos = ref([])
const busquedaProducto = ref('')
const lineas = ref([]) // [{ producto, cantidad }]

function ventaVacia() {
  return {
    metodo_pago: 'efectivo',
    tipo_comprobante: 'boleta',
    dni_ruc: '',
    nombre_cliente: '',
    razon_social: '',
    monto_efectivo: 0,
  }
}
const nuevaVenta = ref(ventaVacia())

const errorVenta = ref('')
const mensajeVenta = ref('')
const registrando = ref(false)

const fechaInicio = ref(new Date(Date.now() - 6 * 86400000).toISOString().slice(0, 10))
const fechaFin = ref(new Date().toISOString().slice(0, 10))
const reporte = ref({})

const modalComprobantes = ref(false)
const listaVentas = ref([])
const busquedaComprobante = ref('')
const limiteComprobantes = ref(10)

// ---------- Productos y líneas de la venta ----------
const sugerencias = computed(() => {
  const t = busquedaProducto.value.trim().toLowerCase()
  if (!t) return []
  return productos.value
    .filter(p => (p.nombre || '').toLowerCase().includes(t) || (p.codigo || '').toLowerCase().includes(t))
    .slice(0, 6)
})

function anadirProducto(p) {
  const existente = lineas.value.find(l => l.producto.id_producto === p.id_producto)
  if (existente) {
    cambiarCantidad(existente, 1)
  } else {
    lineas.value.push({ producto: p, cantidad: 1 })
  }
  busquedaProducto.value = ''
  errorVenta.value = ''
}

function anadirDesdeBusqueda() {
  const t = busquedaProducto.value.trim().toLowerCase()
  if (!t) return
  const exacto = productos.value.find(p => (p.codigo || '').toLowerCase() === t)
  const elegido = exacto || sugerencias.value[0]
  if (elegido) anadirProducto(elegido)
  else errorVenta.value = 'No se encontró ningún producto con ese código o nombre.'
}

function cambiarCantidad(linea, delta) {
  const nueva = linea.cantidad + delta
  if (nueva < 1) return
  const stock = Number(linea.producto.stock)
  if (!Number.isNaN(stock) && nueva > stock) {
    errorVenta.value = `Stock insuficiente de "${linea.producto.nombre}" (disponible: ${stock}).`
    return
  }
  errorVenta.value = ''
  linea.cantidad = nueva
}

function quitarLinea(linea) {
  lineas.value = lineas.value.filter(l => l !== linea)
}

const calcularTotalVenta = computed(() =>
  lineas.value.reduce((s, l) => s + Number(l.producto.precio) * l.cantidad, 0)
)
const totalUnidades = computed(() => lineas.value.reduce((s, l) => s + l.cantidad, 0))
const baseImponible = computed(() => calcularTotalVenta.value / 1.18)
const igv = computed(() => calcularTotalVenta.value - baseImponible.value)

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
    console.error('Error cargando productos:', error)
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

  if (lineas.value.length === 0) {
    errorVenta.value = 'Agrega al menos un producto a la venta.'
    return
  }

  if (nuevaVenta.value.metodo_pago === 'efectivo' && Number(nuevaVenta.value.monto_efectivo) < calcularTotalVenta.value) {
    errorVenta.value = 'El monto con el que paga es menor al total de la venta.'
    return
  }

  if (nuevaVenta.value.tipo_comprobante === 'factura' && !nuevaVenta.value.razon_social) {
    errorVenta.value = 'Para emitir factura debe ingresar la Razón Social.'
    return
  }

  registrando.value = true
  try {
    const itemsParaTicket = lineas.value.map(l => ({
      nombre: l.producto.nombre,
      cantidad: l.cantidad,
      precio: l.producto.precio,
    }))

    const payload = {
      detalles: lineas.value.map(l => ({ id_producto: l.producto.id_producto, cantidad: l.cantidad })),
      metodo_pago: nuevaVenta.value.metodo_pago,
      tipo_comprobante: nuevaVenta.value.tipo_comprobante,
      dni_ruc: nuevaVenta.value.dni_ruc,
      nombre_cliente: nuevaVenta.value.nombre_cliente,
      razon_social: nuevaVenta.value.razon_social,
      monto_efectivo: nuevaVenta.value.monto_efectivo,
      vuelto: calcularVuelto.value,
    }

    await api.post('/ventas', payload)

    mensajeVenta.value = '¡Venta registrada con éxito y comprobante emitido!'

    imprimirTicket(
      nuevaVenta.value,
      itemsParaTicket,
      calcularTotalVenta.value,
      nuevaVenta.value.monto_efectivo,
      calcularVuelto.value
    )

    lineas.value = []
    nuevaVenta.value = ventaVacia()

    cargarProductos()
    cargarReporte()
  } catch (e) {
    errorVenta.value = e.response?.data?.message || 'Error al registrar la venta en el servidor.'
  } finally {
    registrando.value = false
  }
}

// ---------- Reporte ----------
function aFecha(dia) {
  const [y, m, d] = String(dia).slice(0, 10).split('-').map(Number)
  return new Date(y, (m || 1) - 1, d || 1)
}

function etiquetaDia(dia) {
  const f = aFecha(dia)
  const sem = f.toLocaleDateString('es-PE', { weekday: 'short' }).replace('.', '')
  const mes = f.toLocaleDateString('es-PE', { month: 'short' }).replace('.', '')
  return `${sem.charAt(0).toUpperCase()}${sem.slice(1)} ${f.getDate()} ${mes}`
}

function formatearMiles(valor) {
  return Number(valor || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const diasSeleccionados = computed(() => {
  if (!fechaInicio.value || !fechaFin.value) return 0
  const dias = Math.round((aFecha(fechaFin.value) - aFecha(fechaInicio.value)) / 86400000) + 1
  return Math.max(dias, 0)
})

const ventasPorDiaDesc = computed(() => [...(reporte.value.ventas_por_dia || [])].reverse())
const totalVentas = computed(() => (reporte.value.ventas_por_dia || []).reduce((s, v) => s + Number(v.num_ventas || 0), 0))

async function cargarReporte() {
  try {
    const { data } = await api.get('/ventas/reporte', {
      params: { fecha_inicio: fechaInicio.value, fecha_fin: fechaFin.value },
    })
    reporte.value = data
  } catch (error) {
    console.error('Error cargando reporte:', error)
  }
}

// ---------- Comprobantes ----------
function formatearRango() {
  const fmt = (d) => aFecha(d).toLocaleDateString('es-PE', { day: 'numeric', month: 'long', year: 'numeric' })
  return fechaInicio.value === fechaFin.value ? fmt(fechaInicio.value) : `${fmt(fechaInicio.value)} al ${fmt(fechaFin.value)}`
}

function numeroComprobante(v) {
  const serie = (v.tipo_comprobante || 'boleta') === 'factura' ? 'F001' : 'B001'
  return `${serie}-${String(v.id_venta).padStart(4, '0')}`
}

function horaVenta(v) {
  const f = v.fecha || v.created_at
  if (!f) return '—'
  const d = new Date(f)
  if (Number.isNaN(d.getTime())) return '—'
  return d.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: false })
}

const comprobantesFiltrados = computed(() => {
  const t = busquedaComprobante.value.trim().toLowerCase()
  if (!t) return listaVentas.value
  return listaVentas.value.filter(v =>
    numeroComprobante(v).toLowerCase().includes(t) ||
    String(v.dni_ruc || '').toLowerCase().includes(t) ||
    String(v.nombre_cliente || v.razon_social || '').toLowerCase().includes(t)
  )
})
const comprobantesVisibles = computed(() => comprobantesFiltrados.value.slice(0, limiteComprobantes.value))
const totalComprobantes = computed(() => comprobantesFiltrados.value.reduce((s, v) => s + Number(v.total || v.monto_total || 0), 0))
watch(busquedaComprobante, () => { limiteComprobantes.value = 10 })

async function abrirModalComprobantes() {
  try {
    const { data } = await api.get('/ventas', {
      params: { fecha_inicio: fechaInicio.value, fecha_fin: fechaFin.value, per_page: 200 },
    })
    listaVentas.value = data.data || data
    busquedaComprobante.value = ''
    limiteComprobantes.value = 10
    modalComprobantes.value = true
  } catch (error) {
    console.error('Error al cargar listado de ventas:', error)
    alert('No se pudo obtener el historial de comprobantes.')
  }
}

function reimprimirComprobante(venta) {
  const items = venta.detalles ? venta.detalles.map(d => ({
    nombre: d.producto?.nombre || 'Producto',
    cantidad: d.cantidad,
    precio: d.precio_unitario || (d.pivot ? d.pivot.precio_unitario : 0)
  })) : [{ nombre: 'Consumo General / Venta #' + venta.id_venta, cantidad: 1, precio: venta.total || venta.monto_total }]

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
  )
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
    alert('No se pudo exportar el archivo.')
  }
}

onMounted(() => {
  cargarProductos()
  cargarReporte()
})
</script>

<style scoped>
.ventas-grid { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr); gap: 20px; align-items: start; }
.stepper { display: inline-flex; align-items: center; border: 1px solid var(--ad-border); border-radius: 6px; overflow: hidden; }
.stepper button { width: 24px; height: 26px; background: #fff; border: 0; cursor: pointer; color: var(--ad-brand); font-size: 14px; }
.stepper button:hover { background: var(--ad-head); }
.stepper span { min-width: 26px; text-align: center; font-weight: 600; font-size: 12px; }
.sugerencias {
  position: absolute; z-index: 20; left: 0; right: 0; top: calc(100% + 4px); background: #fff;
  border: 1px solid var(--ad-border); border-radius: 10px; box-shadow: 0 12px 30px rgba(0,0,0,.12); padding: 4px; max-height: 260px; overflow-y: auto;
}
.sugerencias button {
  display: flex; justify-content: space-between; gap: 12px; width: 100%; text-align: left; background: none; border: 0;
  cursor: pointer; padding: 9px 12px; border-radius: 6px; font-size: 13px;
}
.sugerencias button:hover { background: var(--ad-head); }
@media (max-width: 1000px) {
  .ventas-grid { grid-template-columns: minmax(0, 1fr); }
}
</style>
