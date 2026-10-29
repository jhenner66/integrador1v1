<template>
  <div class="ad-page">

    <!-- Encabezado -->
    <div class="ad-head">
      <div>
        <div class="ad-eyebrow"><Icon name="lock" style="width:12px;height:12px" /> Acceso: Administrador y Almacén</div>
        <h1 class="ad-title">Proveedores</h1>
        <p class="ad-subtitle">Mantén al día los datos de contacto de quienes abastecen la tienda.</p>
      </div>
      <button class="ad-btn ad-btn-primary" @click="abrirNuevo"><Icon name="plus" /> Nuevo proveedor</button>
    </div>

    <!-- Tabla -->
    <div class="ad-card">
      <div class="flex items-start justify-between gap-3 flex-wrap" style="padding: 20px 22px 16px">
        <div>
          <h2 class="ad-card-title">Directorio de proveedores</h2>
          <p class="ad-card-sub">{{ proveedores.length }} proveedores registrados</p>
        </div>
        <div class="ad-input-icon" style="width: 280px">
          <Icon name="search" />
          <input v-model="busqueda" type="text" class="ad-input" placeholder="Buscar por nombre, contacto o correo" />
        </div>
      </div>

      <div v-if="cargando" class="ad-empty">Cargando proveedores...</div>
      <div v-else-if="errorCarga" class="ad-empty">No se pudo cargar la lista de proveedores.</div>
      <div v-else-if="filtrados.length" class="ad-table-wrap">
        <table class="ad-table">
          <thead>
            <tr>
              <th>Proveedor</th>
              <th>Contacto</th>
              <th>Teléfono</th>
              <th>Correo</th>
              <th>Dirección</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in paginaItems" :key="p.id_proveedor">
              <td class="ad-strong">{{ p.nombre }}</td>
              <td>{{ p.contacto || '-' }}</td>
              <td>{{ p.telefono || '-' }}</td>
              <td>{{ p.email || '-' }}</td>
              <td>{{ p.direccion || '-' }}</td>
              <td style="white-space: nowrap">
                <button class="ad-link ok" @click="abrirEditar(p)">Editar</button>
                <button v-if="auth.esAdministrador" class="ad-link danger" @click="pedirEliminar(p)">Eliminar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="ad-empty">No hay proveedores que coincidan con la búsqueda.</div>

      <div class="ad-foot">
        <span>Mostrando {{ paginaItems.length }} de {{ filtrados.length }} proveedores</span>
        <div class="ad-pager">
          <button class="ad-btn ad-btn-outline ad-btn-sm" :disabled="pagina <= 1" @click="pagina--">Anterior</button>
          <span class="cur">{{ pagina }}</span>
          <button class="ad-btn ad-btn-outline ad-btn-sm" :disabled="pagina >= totalPaginas" @click="pagina++">Siguiente</button>
        </div>
      </div>
    </div>

    <div class="ad-callout info">
      <Icon name="info" /> Solo el administrador puede eliminar proveedores. Un proveedor con pedidos registrados no se puede eliminar.
    </div>

    <!-- ============ MODAL: CREAR / EDITAR ============ -->
    <div v-if="modal" class="ad-overlay" @click.self="modal = false">
      <form class="ad-modal" @submit.prevent="guardar">
        <div class="ad-modal-head">
          <div class="ad-modal-eyebrow">Proveedores / {{ editandoId ? 'Editar' : 'Nuevo' }}</div>
          <h2 class="ad-modal-title">{{ editandoId ? 'Editar proveedor' : 'Nuevo proveedor' }}</h2>
          <p class="ad-modal-sub">Estos datos aparecen al crear pedidos de reposición.</p>
          <button type="button" class="ad-modal-x" @click="modal = false"><Icon name="x" /></button>
        </div>

        <div class="ad-modal-body">
          <div v-if="errorModal" class="ad-error">{{ errorModal }}</div>

          <div>
            <label class="ad-label">Nombre o razón social *</label>
            <input v-model.trim="form.nombre" type="text" class="ad-input" maxlength="150" required />
          </div>
          <div class="ad-grid2">
            <div>
              <label class="ad-label">Persona de contacto</label>
              <input v-model.trim="form.contacto" type="text" class="ad-input" maxlength="150" />
            </div>
            <div>
              <label class="ad-label">Teléfono</label>
              <input v-model.trim="form.telefono" type="text" class="ad-input" maxlength="30" />
            </div>
          </div>
          <div>
            <label class="ad-label">Correo electrónico</label>
            <input v-model.trim="form.email" type="email" class="ad-input" maxlength="150" />
          </div>
          <div>
            <label class="ad-label">Dirección</label>
            <input v-model.trim="form.direccion" type="text" class="ad-input" maxlength="255" />
          </div>
        </div>

        <div class="ad-modal-foot">
          <span>Los campos marcados con * son obligatorios</span>
          <div class="btns">
            <button type="button" class="ad-btn ad-btn-outline" @click="modal = false">Cancelar</button>
            <button type="submit" class="ad-btn ad-btn-primary" :disabled="guardando">{{ guardando ? 'Guardando...' : (editandoId ? 'Guardar cambios' : 'Crear proveedor') }}</button>
          </div>
        </div>
      </form>
    </div>

    <!-- ============ MODAL: CONFIRMAR ELIMINACIÓN ============ -->
    <div v-if="aEliminar" class="ad-overlay" @click.self="aEliminar = null">
      <div class="ad-modal" style="max-width: 440px">
        <div class="ad-modal-head">
          <div class="ad-modal-eyebrow">Proveedores / Eliminar</div>
          <h2 class="ad-modal-title">Eliminar proveedor</h2>
          <p class="ad-modal-sub">Se eliminará "{{ aEliminar.nombre }}". Esta acción no se puede deshacer.</p>
          <button type="button" class="ad-modal-x" @click="aEliminar = null"><Icon name="x" /></button>
        </div>
        <div class="ad-modal-body" style="padding-top: 12px">
          <div v-if="errorModal" class="ad-error">{{ errorModal }}</div>
        </div>
        <div class="ad-modal-foot">
          <span></span>
          <div class="btns">
            <button class="ad-btn ad-btn-outline" @click="aEliminar = null">Cancelar</button>
            <button class="ad-btn ad-btn-primary" style="background: var(--ad-danger)" :disabled="guardando" @click="eliminar">Eliminar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import api from '../services/api'
import { mensajeError } from '../services/errores'
import { useAuthStore } from '../stores/auth'
import Icon from '../components/Icon.vue'

const auth = useAuthStore()

const proveedores = ref([])
const cargando = ref(true)
const errorCarga = ref(false)

const busqueda = ref('')
const pagina = ref(1)
const POR_PAGINA = 8

const modal = ref(false)
const editandoId = ref(null)
const guardando = ref(false)
const errorModal = ref('')
const aEliminar = ref(null)
const form = ref(formVacio())

function formVacio() {
  return { nombre: '', contacto: '', telefono: '', email: '', direccion: '' }
}

const filtrados = computed(() => {
  const t = busqueda.value.trim().toLowerCase()
  if (!t) return proveedores.value
  return proveedores.value.filter(p =>
    (p.nombre || '').toLowerCase().includes(t) ||
    (p.contacto || '').toLowerCase().includes(t) ||
    (p.email || '').toLowerCase().includes(t)
  )
})
const totalPaginas = computed(() => Math.max(1, Math.ceil(filtrados.value.length / POR_PAGINA)))
const paginaItems = computed(() => {
  const ini = (pagina.value - 1) * POR_PAGINA
  return filtrados.value.slice(ini, ini + POR_PAGINA)
})
watch(busqueda, () => { pagina.value = 1 })

async function cargar() {
  try {
    const { data } = await api.get('/proveedores')
    proveedores.value = Array.isArray(data) ? data : (data.data || [])
    errorCarga.value = false
    if (pagina.value > totalPaginas.value) pagina.value = totalPaginas.value
  } catch (e) {
    console.error('Error cargando proveedores:', e)
    errorCarga.value = true
  } finally {
    cargando.value = false
  }
}

function abrirNuevo() {
  editandoId.value = null
  form.value = formVacio()
  errorModal.value = ''
  modal.value = true
}

function abrirEditar(p) {
  editandoId.value = p.id_proveedor
  form.value = {
    nombre: p.nombre || '',
    contacto: p.contacto || '',
    telefono: p.telefono || '',
    email: p.email || '',
    direccion: p.direccion || '',
  }
  errorModal.value = ''
  modal.value = true
}

async function guardar() {
  errorModal.value = ''
  guardando.value = true
  try {
    // Los campos vacíos se envían como null para que pasen la validación "nullable"
    const datos = {}
    for (const [k, v] of Object.entries(form.value)) datos[k] = v === '' ? null : v
    if (editandoId.value) {
      await api.put(`/proveedores/${editandoId.value}`, datos)
    } else {
      await api.post('/proveedores', datos)
    }
    modal.value = false
    await cargar()
  } catch (e) {
    errorModal.value = mensajeError(e, 'No se pudo guardar el proveedor.')
  } finally {
    guardando.value = false
  }
}

function pedirEliminar(p) {
  errorModal.value = ''
  aEliminar.value = p
}

async function eliminar() {
  guardando.value = true
  errorModal.value = ''
  try {
    await api.delete(`/proveedores/${aEliminar.value.id_proveedor}`)
    aEliminar.value = null
    await cargar()
  } catch (e) {
    errorModal.value = e?.response?.status >= 500
      ? 'No se pudo eliminar. Es probable que el proveedor tenga pedidos registrados.'
      : mensajeError(e, 'No se pudo eliminar el proveedor.')
  } finally {
    guardando.value = false
  }
}

onMounted(cargar)
</script>
