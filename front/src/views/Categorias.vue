<template>
  <div class="ad-page">

    <!-- Encabezado -->
    <div class="ad-head">
      <div>
        <div class="ad-eyebrow"><Icon name="lock" style="width:12px;height:12px" /> Acceso: Administrador y Almacén</div>
        <h1 class="ad-title">Categorías</h1>
        <p class="ad-subtitle">Organiza el catálogo en grupos para encontrar los productos más rápido.</p>
      </div>
      <button class="ad-btn ad-btn-primary" @click="abrirNueva"><Icon name="plus" /> Nueva categoría</button>
    </div>

    <!-- Tabla -->
    <div class="ad-card">
      <div class="flex items-start justify-between gap-3 flex-wrap" style="padding: 20px 22px 16px">
        <div>
          <h2 class="ad-card-title">Categorías de productos</h2>
          <p class="ad-card-sub">{{ categorias.length }} categorías registradas</p>
        </div>
        <div class="ad-input-icon" style="width: 260px">
          <Icon name="search" />
          <input v-model="busqueda" type="text" class="ad-input" placeholder="Buscar categoría" />
        </div>
      </div>

      <div v-if="cargando" class="ad-empty">Cargando categorías...</div>
      <div v-else-if="errorCarga" class="ad-empty">No se pudo cargar la lista de categorías.</div>
      <div v-else-if="filtradas.length" class="ad-table-wrap">
        <table class="ad-table">
          <thead>
            <tr>
              <th>Categoría</th>
              <th>Descripción</th>
              <th v-if="conteoDisponible" class="num">Productos</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in paginaItems" :key="c.id_categoria">
              <td class="ad-strong">{{ c.nombre }}</td>
              <td>{{ c.descripcion || '-' }}</td>
              <td v-if="conteoDisponible" class="num">{{ conteo[c.id_categoria] || 0 }}</td>
              <td style="white-space: nowrap">
                <button class="ad-link ok" @click="abrirEditar(c)">Editar</button>
                <button v-if="auth.esAdministrador" class="ad-link danger" @click="pedirEliminar(c)">Eliminar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="ad-empty">No hay categorías que coincidan con la búsqueda.</div>

      <div class="ad-foot">
        <span>Mostrando {{ paginaItems.length }} de {{ filtradas.length }} categorías</span>
        <div class="ad-pager">
          <button class="ad-btn ad-btn-outline ad-btn-sm" :disabled="pagina <= 1" @click="pagina--">Anterior</button>
          <span class="cur">{{ pagina }}</span>
          <button class="ad-btn ad-btn-outline ad-btn-sm" :disabled="pagina >= totalPaginas" @click="pagina++">Siguiente</button>
        </div>
      </div>
    </div>

    <div class="ad-callout info">
      <Icon name="info" /> Solo el administrador puede eliminar categorías. Una categoría con productos asociados no se puede eliminar.
    </div>

    <!-- ============ MODAL: CREAR / EDITAR ============ -->
    <div v-if="modal" class="ad-overlay" @click.self="modal = false">
      <form class="ad-modal" @submit.prevent="guardar">
        <div class="ad-modal-head">
          <div class="ad-modal-eyebrow">Categorías / {{ editandoId ? 'Editar' : 'Nueva' }}</div>
          <h2 class="ad-modal-title">{{ editandoId ? 'Editar categoría' : 'Nueva categoría' }}</h2>
          <p class="ad-modal-sub">El nombre debe ser único.</p>
          <button type="button" class="ad-modal-x" @click="modal = false"><Icon name="x" /></button>
        </div>

        <div class="ad-modal-body">
          <div v-if="errorModal" class="ad-error">{{ errorModal }}</div>
          <div>
            <label class="ad-label">Nombre *</label>
            <input v-model.trim="form.nombre" type="text" class="ad-input" maxlength="100" required />
          </div>
          <div>
            <label class="ad-label">Descripción</label>
            <input v-model.trim="form.descripcion" type="text" class="ad-input" maxlength="255" />
          </div>
        </div>

        <div class="ad-modal-foot">
          <span>Los campos marcados con * son obligatorios</span>
          <div class="btns">
            <button type="button" class="ad-btn ad-btn-outline" @click="modal = false">Cancelar</button>
            <button type="submit" class="ad-btn ad-btn-primary" :disabled="guardando">{{ guardando ? 'Guardando...' : (editandoId ? 'Guardar cambios' : 'Crear categoría') }}</button>
          </div>
        </div>
      </form>
    </div>

    <!-- ============ MODAL: CONFIRMAR ELIMINACIÓN ============ -->
    <div v-if="aEliminar" class="ad-overlay" @click.self="aEliminar = null">
      <div class="ad-modal" style="max-width: 440px">
        <div class="ad-modal-head">
          <div class="ad-modal-eyebrow">Categorías / Eliminar</div>
          <h2 class="ad-modal-title">Eliminar categoría</h2>
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

const categorias = ref([])
const conteo = ref({})
const conteoDisponible = ref(false)
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
const form = ref({ nombre: '', descripcion: '' })

const filtradas = computed(() => {
  const t = busqueda.value.trim().toLowerCase()
  if (!t) return categorias.value
  return categorias.value.filter(c =>
    (c.nombre || '').toLowerCase().includes(t) || (c.descripcion || '').toLowerCase().includes(t)
  )
})
const totalPaginas = computed(() => Math.max(1, Math.ceil(filtradas.value.length / POR_PAGINA)))
const paginaItems = computed(() => {
  const ini = (pagina.value - 1) * POR_PAGINA
  return filtradas.value.slice(ini, ini + POR_PAGINA)
})
watch(busqueda, () => { pagina.value = 1 })

async function cargar() {
  try {
    const { data } = await api.get('/categorias')
    categorias.value = Array.isArray(data) ? data : (data.data || [])
    errorCarga.value = false
    if (pagina.value > totalPaginas.value) pagina.value = totalPaginas.value
  } catch (e) {
    console.error('Error cargando categorías:', e)
    errorCarga.value = true
  } finally {
    cargando.value = false
  }
  cargarConteo()
}

// Cuenta de productos por categoría (si el listado de productos no responde, la columna se oculta)
async function cargarConteo() {
  try {
    const { data } = await api.get('/productos')
    const lista = Array.isArray(data) ? data : (data.data || [])
    const mapa = {}
    for (const p of lista) mapa[p.id_categoria] = (mapa[p.id_categoria] || 0) + 1
    conteo.value = mapa
    conteoDisponible.value = true
  } catch (e) {
    conteoDisponible.value = false
  }
}

function abrirNueva() {
  editandoId.value = null
  form.value = { nombre: '', descripcion: '' }
  errorModal.value = ''
  modal.value = true
}

function abrirEditar(c) {
  editandoId.value = c.id_categoria
  form.value = { nombre: c.nombre || '', descripcion: c.descripcion || '' }
  errorModal.value = ''
  modal.value = true
}

async function guardar() {
  errorModal.value = ''
  guardando.value = true
  try {
    const datos = { nombre: form.value.nombre, descripcion: form.value.descripcion || null }
    if (editandoId.value) {
      await api.put(`/categorias/${editandoId.value}`, datos)
    } else {
      await api.post('/categorias', datos)
    }
    modal.value = false
    await cargar()
  } catch (e) {
    errorModal.value = mensajeError(e, 'No se pudo guardar la categoría.')
  } finally {
    guardando.value = false
  }
}

function pedirEliminar(c) {
  errorModal.value = ''
  aEliminar.value = c
}

async function eliminar() {
  guardando.value = true
  errorModal.value = ''
  try {
    await api.delete(`/categorias/${aEliminar.value.id_categoria}`)
    aEliminar.value = null
    await cargar()
  } catch (e) {
    errorModal.value = e?.response?.status >= 500
      ? 'No se pudo eliminar. Es probable que la categoría tenga productos asociados.'
      : mensajeError(e, 'No se pudo eliminar la categoría.')
  } finally {
    guardando.value = false
  }
}

onMounted(cargar)
</script>
