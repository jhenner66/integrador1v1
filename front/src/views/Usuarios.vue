<template>
  <div class="ad-page">

    <!-- Encabezado -->
    <div class="ad-head">
      <div>
        <div class="ad-eyebrow"><Icon name="lock" style="width:12px;height:12px" /> Acceso: Administrador</div>
        <h1 class="ad-title">Usuarios y roles</h1>
        <p class="ad-subtitle">Crea cuentas del personal, asigna su rol y desactiva accesos sin perder el historial.</p>
      </div>
      <button class="ad-btn ad-btn-primary" @click="abrirNuevo"><Icon name="plus" /> Nuevo usuario</button>
    </div>

    <!-- Resumen -->
    <div class="ad-kpis">
      <div class="ad-card ad-kpi">
        <div class="ad-kpi-top"><span>Cuentas activas</span><Icon name="userCheck" style="width:16px;height:16px" /></div>
        <div class="ad-kpi-value">{{ activos.length }}</div>
        <span class="ad-badge ok">Con acceso al sistema</span>
      </div>
      <div class="ad-card ad-kpi">
        <div class="ad-kpi-top"><span>Administradores</span><Icon name="shield" style="width:16px;height:16px" /></div>
        <div class="ad-kpi-value">{{ contarRol('administrador') }}</div>
        <span class="ad-badge neutral">Acceso total</span>
      </div>
      <div class="ad-card ad-kpi">
        <div class="ad-kpi-top"><span>Vendedores</span><Icon name="cart" style="width:16px;height:16px" /></div>
        <div class="ad-kpi-value">{{ contarRol('vendedor') }}</div>
        <span class="ad-badge info">Ventas y POS</span>
      </div>
      <div class="ad-card ad-kpi">
        <div class="ad-kpi-top"><span>Almacén</span><Icon name="box" style="width:16px;height:16px" /></div>
        <div class="ad-kpi-value">{{ contarRol('almacen') }}</div>
        <span class="ad-badge info">Inventario y pedidos</span>
      </div>
    </div>

    <!-- Tabla -->
    <div class="ad-card">
      <div class="flex items-start justify-between gap-3 flex-wrap" style="padding: 20px 22px 16px">
        <div>
          <h2 class="ad-card-title">Cuentas del personal</h2>
          <p class="ad-card-sub">{{ usuarios.length }} cuentas registradas</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
          <div class="ad-input-icon" style="width: 240px">
            <Icon name="search" />
            <input v-model="busqueda" type="text" class="ad-input" placeholder="Buscar por nombre o correo" />
          </div>
          <select v-model="filtroRol" class="ad-select" style="width: 160px">
            <option value="">Todos los roles</option>
            <option value="administrador">Administrador</option>
            <option value="vendedor">Vendedor</option>
            <option value="almacen">Almacén</option>
          </select>
          <select v-model="filtroEstado" class="ad-select" style="width: 150px">
            <option value="">Todos</option>
            <option value="activo">Activos</option>
            <option value="inactivo">Inactivos</option>
          </select>
        </div>
      </div>

      <div v-if="cargando" class="ad-empty">Cargando usuarios...</div>
      <div v-else-if="errorCarga" class="ad-empty">No se pudo cargar la lista de usuarios.</div>
      <div v-else-if="filtrados.length" class="ad-table-wrap">
        <table class="ad-table">
          <thead>
            <tr>
              <th>Usuario</th>
              <th>Correo</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in pagina_items" :key="u.id_usuario">
              <td>
                <div class="flex items-center gap-3">
                  <span class="ad-avatar" style="width: 32px; height: 32px; font-size: 12px">{{ iniciales(u.nombre) }}</span>
                  <span class="ad-strong">{{ u.nombre }}<span v-if="esYo(u)" class="ad-muted" style="font-weight: 400"> (tú)</span></span>
                </div>
              </td>
              <td>{{ u.email }}</td>
              <td><span class="ad-badge" :class="claseRol(u.rol)">{{ etiquetaRol(u.rol) }}</span></td>
              <td><span class="ad-badge" :class="u.activo ? 'ok' : 'neutral'">{{ u.activo ? 'Activo' : 'Inactivo' }}</span></td>
              <td>
                <button class="ad-link ok" @click="abrirEditar(u)">Editar</button>
                <button v-if="u.activo && !esYo(u)" class="ad-link danger" @click="pedirConfirmacion(u)">Desactivar</button>
                <button v-else-if="!u.activo" class="ad-link ok" @click="reactivar(u)">Reactivar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="ad-empty">No hay usuarios que coincidan con la búsqueda.</div>

      <div class="ad-foot">
        <span>Mostrando {{ pagina_items.length }} de {{ filtrados.length }} usuarios</span>
        <div class="ad-pager">
          <button class="ad-btn ad-btn-outline ad-btn-sm" :disabled="pagina <= 1" @click="pagina--">Anterior</button>
          <span class="cur">{{ pagina }}</span>
          <button class="ad-btn ad-btn-outline ad-btn-sm" :disabled="pagina >= totalPaginas" @click="pagina++">Siguiente</button>
        </div>
      </div>
    </div>

    <div class="ad-callout info">
      <Icon name="info" /> Las cuentas no se eliminan: al desactivarlas pierden el acceso pero se conserva el historial de ventas y movimientos.
    </div>

    <!-- ============ MODAL: CREAR / EDITAR ============ -->
    <div v-if="modal" class="ad-overlay" @click.self="modal = false">
      <form class="ad-modal" @submit.prevent="guardar">
        <div class="ad-modal-head">
          <div class="ad-modal-eyebrow">Usuarios / {{ editandoId ? 'Editar' : 'Nuevo' }}</div>
          <h2 class="ad-modal-title">{{ editandoId ? 'Editar usuario' : 'Nuevo usuario' }}</h2>
          <p class="ad-modal-sub">{{ editandoId ? 'Actualiza los datos o el rol de la cuenta.' : 'La cuenta quedará activa al guardarse.' }}</p>
          <button type="button" class="ad-modal-x" @click="modal = false"><Icon name="x" /></button>
        </div>

        <div class="ad-modal-body">
          <div v-if="errorModal" class="ad-error">{{ errorModal }}</div>

          <div>
            <label class="ad-label">Nombre completo *</label>
            <input v-model.trim="form.nombre" type="text" class="ad-input" maxlength="150" required />
          </div>
          <div>
            <label class="ad-label">Correo electrónico *</label>
            <input v-model.trim="form.email" type="email" class="ad-input" placeholder="empleado@dtodo.com" required />
          </div>
          <div class="ad-grid2">
            <div>
              <label class="ad-label">Rol *</label>
              <select v-model="form.rol" class="ad-select" required>
                <option value="administrador">Administrador</option>
                <option value="vendedor">Vendedor</option>
                <option value="almacen">Almacén</option>
              </select>
            </div>
            <div>
              <label class="ad-label">{{ editandoId ? 'Nueva contraseña' : 'Contraseña *' }}</label>
              <input v-model="form.password" type="password" class="ad-input" minlength="8" autocomplete="new-password"
                :required="!editandoId" :placeholder="editandoId ? 'Dejar vacío para no cambiar' : 'Mínimo 8 caracteres'" />
            </div>
          </div>
          <div class="ad-note"><Icon name="info" /> El rol define a qué módulos puede entrar esta persona.</div>
        </div>

        <div class="ad-modal-foot">
          <span>Los campos marcados con * son obligatorios</span>
          <div class="btns">
            <button type="button" class="ad-btn ad-btn-outline" @click="modal = false">Cancelar</button>
            <button type="submit" class="ad-btn ad-btn-primary" :disabled="guardando">{{ guardando ? 'Guardando...' : (editandoId ? 'Guardar cambios' : 'Crear usuario') }}</button>
          </div>
        </div>
      </form>
    </div>

    <!-- ============ MODAL: CONFIRMAR DESACTIVACIÓN ============ -->
    <div v-if="aDesactivar" class="ad-overlay" @click.self="aDesactivar = null">
      <div class="ad-modal" style="max-width: 440px">
        <div class="ad-modal-head">
          <div class="ad-modal-eyebrow">Usuarios / Desactivar</div>
          <h2 class="ad-modal-title">Desactivar cuenta</h2>
          <p class="ad-modal-sub">{{ aDesactivar.nombre }} ya no podrá iniciar sesión. Podrás reactivar la cuenta cuando quieras.</p>
          <button type="button" class="ad-modal-x" @click="aDesactivar = null"><Icon name="x" /></button>
        </div>
        <div class="ad-modal-body" style="padding-top: 12px">
          <div v-if="errorModal" class="ad-error">{{ errorModal }}</div>
        </div>
        <div class="ad-modal-foot">
          <span></span>
          <div class="btns">
            <button class="ad-btn ad-btn-outline" @click="aDesactivar = null">Cancelar</button>
            <button class="ad-btn ad-btn-primary" style="background: var(--ad-danger)" :disabled="guardando" @click="desactivar">Desactivar</button>
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

const usuarios = ref([])
const cargando = ref(true)
const errorCarga = ref(false)

const busqueda = ref('')
const filtroRol = ref('')
const filtroEstado = ref('')
const pagina = ref(1)
const POR_PAGINA = 8

const modal = ref(false)
const editandoId = ref(null)
const guardando = ref(false)
const errorModal = ref('')
const aDesactivar = ref(null)
const form = ref(formVacio())

function formVacio() {
  return { nombre: '', email: '', rol: 'vendedor', password: '' }
}

// ---------- Utilidades ----------
const etiquetas = { administrador: 'Administrador', vendedor: 'Vendedor', almacen: 'Almacén' }
const etiquetaRol = (r) => etiquetas[r] || r
const claseRol = (r) => (r === 'administrador' ? 'ok' : r === 'vendedor' ? 'info' : 'warn')
const esYo = (u) => u.id_usuario === auth.usuario?.id_usuario
function iniciales(nombre) {
  const partes = (nombre || 'U').trim().split(/\s+/)
  return ((partes[0]?.[0] || 'U') + (partes[1]?.[0] || '')).toUpperCase()
}

// ---------- Datos derivados ----------
const activos = computed(() => usuarios.value.filter(u => u.activo))
const contarRol = (rol) => activos.value.filter(u => u.rol === rol).length

const filtrados = computed(() => {
  const t = busqueda.value.trim().toLowerCase()
  return usuarios.value.filter(u => {
    const texto = !t || (u.nombre || '').toLowerCase().includes(t) || (u.email || '').toLowerCase().includes(t)
    const rol = !filtroRol.value || u.rol === filtroRol.value
    const estado = !filtroEstado.value || (filtroEstado.value === 'activo' ? !!u.activo : !u.activo)
    return texto && rol && estado
  })
})
const totalPaginas = computed(() => Math.max(1, Math.ceil(filtrados.value.length / POR_PAGINA)))
const pagina_items = computed(() => {
  const ini = (pagina.value - 1) * POR_PAGINA
  return filtrados.value.slice(ini, ini + POR_PAGINA)
})
watch([busqueda, filtroRol, filtroEstado], () => { pagina.value = 1 })

// ---------- Carga ----------
async function cargar() {
  try {
    const { data } = await api.get('/usuarios')
    usuarios.value = Array.isArray(data) ? data : (data.data || [])
    errorCarga.value = false
    if (pagina.value > totalPaginas.value) pagina.value = totalPaginas.value
  } catch (e) {
    console.error('Error cargando usuarios:', e)
    errorCarga.value = true
  } finally {
    cargando.value = false
  }
}

// ---------- Crear / editar ----------
function abrirNuevo() {
  editandoId.value = null
  form.value = formVacio()
  errorModal.value = ''
  modal.value = true
}

function abrirEditar(u) {
  editandoId.value = u.id_usuario
  form.value = { nombre: u.nombre, email: u.email, rol: u.rol, password: '' }
  errorModal.value = ''
  modal.value = true
}

async function guardar() {
  errorModal.value = ''
  guardando.value = true
  try {
    const datos = { nombre: form.value.nombre, email: form.value.email, rol: form.value.rol }
    if (form.value.password) datos.password = form.value.password
    if (editandoId.value) {
      await api.put(`/usuarios/${editandoId.value}`, datos)
    } else {
      await api.post('/usuarios', datos)
    }
    modal.value = false
    await cargar()
  } catch (e) {
    errorModal.value = mensajeError(e, 'No se pudo guardar el usuario.')
  } finally {
    guardando.value = false
  }
}

// ---------- Desactivar / reactivar ----------
function pedirConfirmacion(u) {
  errorModal.value = ''
  aDesactivar.value = u
}

async function desactivar() {
  guardando.value = true
  errorModal.value = ''
  try {
    await api.delete(`/usuarios/${aDesactivar.value.id_usuario}`)
    aDesactivar.value = null
    await cargar()
  } catch (e) {
    errorModal.value = mensajeError(e, 'No se pudo desactivar la cuenta.')
  } finally {
    guardando.value = false
  }
}

async function reactivar(u) {
  try {
    await api.put(`/usuarios/${u.id_usuario}`, { activo: true })
    await cargar()
  } catch (e) {
    alert(mensajeError(e, 'No se pudo reactivar la cuenta.'))
  }
}

onMounted(cargar)
</script>

<style scoped>
.ad-avatar {
  border-radius: 99px; background: var(--ad-brand-soft); color: var(--ad-brand); font-weight: 700;
  display: flex; align-items: center; justify-content: center; flex: none;
}
</style>
