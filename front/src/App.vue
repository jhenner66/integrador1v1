<template>
  <div class="min-h-screen font-sans" style="color: var(--ad-text)">

    <!-- ============================================================
         LAYOUT DE PERSONAL (administrador, almacén, vendedor):
         barra lateral + cabecera con migas de pan
         ============================================================ -->
    <div v-if="auth.estaAutenticado && !auth.esCliente" class="ad-shell">

      <!-- Fondo oscuro para el menú en móvil -->
      <div v-if="menuLateralAbierto" class="ad-backdrop" @click="menuLateralAbierto = false"></div>

      <!-- BARRA LATERAL -->
      <aside class="ad-sidebar" :class="{ open: menuLateralAbierto }">
        <div class="ad-brand" @click="irAlInicio">
          <div class="ad-brand-logo"><Icon name="store" /></div>
          <div>
            <div class="ad-brand-name">D' Todo</div>
            <div class="ad-brand-tag">MINIMARKET</div>
          </div>
        </div>

        <div class="ad-side-label">ESPACIO DE TRABAJO</div>
        <nav class="ad-side-nav">
          <router-link v-for="item in menuPrincipal" :key="item.to" :to="item.to" class="ad-side-link" @click="menuLateralAbierto = false">
            <Icon :name="item.icon" /> {{ item.label }}
          </router-link>
        </nav>

        <template v-if="menuExtra.length">
          <div class="ad-side-label" style="margin-top: 18px">OTROS MÓDULOS</div>
          <nav class="ad-side-nav">
            <router-link v-for="item in menuExtra" :key="item.to" :to="item.to" class="ad-side-link" @click="menuLateralAbierto = false">
              <Icon :name="item.icon" /> {{ item.label }}
            </router-link>
          </nav>
        </template>

        <div class="ad-side-info">
          <Icon name="shield" />
          <strong>Área de empleados</strong>
          <p>Acceso según tu rol. La tienda mantiene su espacio independiente.</p>
        </div>

        <div class="ad-side-user">
          <button class="ad-side-profile" @click="abrirMisDatos" title="Mis datos">
            <span class="ad-avatar">{{ iniciales }}</span>
            <span class="ad-side-profile-text">
              <span class="ad-side-name">{{ auth.usuario?.nombre || 'Usuario' }}</span>
              <span class="ad-side-role">{{ rolLegible }}</span>
            </span>
          </button>
          <button class="ad-side-logout" @click="cerrarSesion"><Icon name="logout" /> Cerrar sesión</button>
        </div>
      </aside>

      <!-- ÁREA PRINCIPAL -->
      <div class="ad-main">
        <header class="ad-topbar">
          <div class="flex items-center gap-3">
            <button class="ad-burger" @click="menuLateralAbierto = true" aria-label="Abrir menú"><Icon name="menu" /></button>
            <div class="ad-crumbs">
              <span>{{ seccionRaiz }}</span>
              <span class="sep">/</span>
              <strong>{{ tituloActual }}</strong>
            </div>
          </div>
          <div class="ad-topbar-right">
            <div id="carrito-slot"></div>
            <span class="ad-date">{{ fechaHoy }}</span>
            <span class="ad-badge ok">{{ rolEtiqueta }}</span>
            <button v-if="auth.esAdministrador" class="ad-bell" @click="router.push('/alertas')" title="Ver alertas">
              <Icon name="bell" />
            </button>
            <span v-else class="ad-bell" style="cursor: default"><Icon name="bell" /></span>
          </div>
        </header>

        <main class="ad-content">
          <router-view />
        </main>

        <footer class="ad-footer">
          <span>D' Todo · Gestión interna</span>
          <span>Moneda: PEN (S/) · Hora de Lima</span>
        </footer>
      </div>
    </div>

    <!-- ============================================================
         LAYOUT DE CLIENTE: cabecera original de la tienda
         ============================================================ -->
    <header v-else-if="auth.estaAutenticado" class="bg-white border-b border-[#E0EBE2] sticky top-0 z-50 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">

        <div class="flex items-center gap-3 cursor-pointer" @click="irAlInicio">
          <div class="bg-[#234E35] text-white p-2 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
          </div>
          <div>
            <h1 class="text-xl font-bold leading-tight">D' Todo</h1>
            <p class="text-[9px] font-bold tracking-widest text-gray-400 uppercase">Minimarket</p>
          </div>
        </div>

        <nav class="hidden md:flex items-center gap-8 font-bold text-sm">
          <router-link to="/catalogo" class="nav-link">Tienda</router-link>
          <button @click="alertaProximamente('Mis pedidos')" class="nav-link">Mis pedidos</button>
          <button @click="alertaProximamente('Centro de ayuda')" class="nav-link">Ayuda</button>
        </nav>

        <div class="flex items-center gap-4">
          <div class="relative">
            <div @click="menuUsuarioAbierto = !menuUsuarioAbierto" class="hidden sm:flex items-center gap-2 bg-[#F1F6F0] px-4 py-2 rounded-full text-sm font-bold cursor-pointer hover:bg-[#D2E4D5] transition select-none">
              <div class="w-6 h-6 bg-[#D2E4D5] rounded-full flex items-center justify-center text-[#234E35] text-xs">
                {{ auth.usuario?.nombre?.charAt(0) || 'U' }}
              </div>
              Hola, {{ auth.usuario?.nombre?.split(' ')[0] || 'Usuario' }}
              <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="{'rotate-180': menuUsuarioAbierto}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>

            <div v-if="menuUsuarioAbierto" class="absolute right-0 mt-2 w-64 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 py-2 overflow-hidden">
              <div class="px-4 py-3 border-b border-gray-50">
                <p class="text-sm font-bold text-[#234E35] truncate">{{ auth.usuario?.nombre || 'Usuario' }}</p>
                <p class="text-xs text-gray-400 uppercase tracking-widest font-extrabold mt-1">{{ auth.usuario?.rol || 'Cliente' }}</p>
              </div>

              <button @click="abrirMisDatos" class="w-full text-left px-4 py-2.5 text-sm text-gray-600 hover:bg-[#F1F6F0] hover:text-[#234E35] flex items-center gap-3 font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Mis datos
              </button>
              <button @click="alertaProximamente('Mis direcciones')" class="w-full text-left px-4 py-2.5 text-sm text-gray-600 hover:bg-[#F1F6F0] hover:text-[#234E35] flex items-center gap-3 font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Mis direcciones
              </button>
              <button @click="alertaProximamente('Métodos de pago')" class="w-full text-left px-4 py-2.5 text-sm text-gray-600 hover:bg-[#F1F6F0] hover:text-[#234E35] flex items-center gap-3 font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg> Métodos de pago
              </button>
              <button @click="alertaProximamente('Configuración')" class="w-full text-left px-4 py-2.5 text-sm text-gray-600 hover:bg-[#F1F6F0] hover:text-[#234E35] flex items-center gap-3 font-semibold transition border-b border-gray-50 pb-3 mb-1">
                Configuración
              </button>
              <button @click="cerrarSesion" class="w-full text-left px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 flex items-center gap-3 font-bold transition">
                Cerrar sesión
              </button>
            </div>
          </div>

          <!-- ESPACIO RESERVADO PARA EL CARRITO (Se llena desde Catalogo.vue) -->
          <div id="carrito-slot"></div>
        </div>
      </div>
    </header>

    <!-- MODAL MIS DATOS -->
    <div v-if="mostrarModalDatos" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
      <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden transform transition-all">
        <div class="bg-[#234E35] text-white px-6 py-4 flex justify-between items-center">
          <h3 class="font-bold text-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Mis Datos
          </h3>
          <button @click="mostrarModalDatos = false" class="text-white/80 hover:text-white transition bg-[#1A3A27] p-1.5 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>

        <form @submit.prevent="actualizarDatos" class="p-6 space-y-5">

          <div class="bg-blue-50/50 border border-blue-100 text-blue-800 text-xs p-3.5 rounded-2xl flex gap-3 items-start">
            <svg class="w-5 h-5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="leading-relaxed font-medium">Los nombres y apellidos se obtienen automáticamente mediante la conexión con <strong>RENIEC</strong> al ingresar tu DNI. Por seguridad, no pueden ser modificados manualmente.</p>
          </div>

          <div class="flex gap-4 items-end">
            <div class="flex-1">
              <label class="block text-xs font-bold text-gray-500 mb-1">DNI</label>
              <input v-model="datosForm.dni" type="text" maxlength="8" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#234E35] focus:ring-1 focus:ring-[#234E35] outline-none transition">
            </div>
            <button type="button" class="bg-[#F1F6F0] text-[#234E35] text-xs font-bold px-4 py-3 rounded-xl border border-[#D2E4D5] hover:bg-[#EAF2EC] transition flex items-center gap-2 shadow-sm">
              RENIEC
            </button>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-500 mb-1">Nombres</label>
              <input v-model="datosForm.nombre" type="text" disabled class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 text-sm text-gray-500 cursor-not-allowed font-medium">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 mb-1">Apellidos</label>
              <input v-model="datosForm.apellidos" type="text" disabled class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 text-sm text-gray-500 cursor-not-allowed font-medium">
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Correo Electrónico</label>
            <input v-model="datosForm.correo" type="email" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#234E35] outline-none transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Celular</label>
            <input v-model="datosForm.celular" type="text" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#234E35] outline-none transition">
          </div>

          <div class="pt-2">
            <button type="submit" class="w-full bg-[#234E35] hover:bg-[#1A3A27] text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-[#234E35]/30 flex justify-center items-center gap-2">
              Actualizar datos
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Vistas sin sesión (login) o de cliente -->
    <main v-if="!(auth.estaAutenticado && !auth.esCliente)">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from './stores/auth'
import { useRouter, useRoute } from 'vue-router'
import Icon from './components/Icon.vue'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

// Variables para el menú y modal
const menuUsuarioAbierto = ref(false)
const menuLateralAbierto = ref(false)
const mostrarModalDatos = ref(false)
const datosForm = ref({
  dni: '', nombre: '', apellidos: '', correo: '', celular: ''
})

// ----- Navegación lateral según el rol -----
const rol = computed(() => (auth.usuario?.rol || '').toLowerCase())

const menuPrincipal = computed(() => {
  const items = []
  if (auth.esAdministrador) items.push({ to: '/dashboard', label: 'Panel', icon: 'panel' })
  if (auth.esAdministrador || auth.esAlmacen) {
    items.push({ to: '/inventario', label: 'Inventario', icon: 'box' })
    items.push({ to: '/pedidos', label: 'Pedidos', icon: 'truck' })
  }
  if (auth.esAdministrador || auth.esVendedor) items.push({ to: '/ventas', label: 'Ventas y POS', icon: 'cart' })
  return items
})

// Módulos que existen en el sistema pero que no aparecen en el diseño base
const menuExtra = computed(() => {
  if (!auth.esAdministrador) return []
  return [
    { to: '/alertas', label: 'Alertas', icon: 'bell' },
    { to: '/catalogo', label: 'Catálogo', icon: 'shop' },
  ]
})

const titulos = {
  dashboard: 'Panel',
  inventario: 'Inventario',
  pedidos: 'Pedidos',
  ventas: 'Ventas y POS',
  alertas: 'Alertas',
  catalogo: 'Catálogo',
}
const tituloActual = computed(() => titulos[route.name] || 'Panel')

const rolLegible = computed(() => {
  const mapa = { administrador: 'Administrador/a', almacen: 'Almacén', vendedor: 'Vendedor/a' }
  return mapa[rol.value] || auth.usuario?.rol || ''
})
const rolEtiqueta = computed(() => {
  const mapa = { administrador: 'Administrador', almacen: 'Almacén', vendedor: 'Vendedor' }
  return mapa[rol.value] || auth.usuario?.rol || ''
})
const seccionRaiz = computed(() => (auth.esAdministrador ? 'Administración' : 'Mi espacio'))

const iniciales = computed(() => {
  const partes = (auth.usuario?.nombre || 'U').trim().split(/\s+/)
  return ((partes[0]?.[0] || 'U') + (partes[1]?.[0] || '')).toUpperCase()
})

const fechaHoy = computed(() => {
  const f = new Date().toLocaleDateString('es-PE', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
  return f.charAt(0).toUpperCase() + f.slice(1)
})

const cerrarSesion = () => {
  menuUsuarioAbierto.value = false
  menuLateralAbierto.value = false
  auth.logout()
  router.push('/login')
}

const irAlInicio = () => {
  menuLateralAbierto.value = false
  if (auth.esCliente) router.push('/catalogo')
  else if (auth.esAlmacen) router.push('/inventario')
  else if (auth.esVendedor) router.push('/ventas')
  else router.push('/dashboard')
}

const alertaProximamente = (seccion) => {
  menuUsuarioAbierto.value = false
  alert(`La sección "${seccion}" estará disponible próximamente en futuras actualizaciones.`)
}

// Abrir el modal copiando los datos actuales de Pinia
const abrirMisDatos = () => {
  menuUsuarioAbierto.value = false
  menuLateralAbierto.value = false
  datosForm.value = {
    dni: auth.usuario?.dni || '',
    nombre: auth.usuario?.nombre || '',
    apellidos: auth.usuario?.apellidos || '',
    correo: auth.usuario?.correo || '',
    celular: auth.usuario?.celular || ''
  }
  mostrarModalDatos.value = true
}

// Guardar datos temporalmente en el Store (En el futuro esto hará un PUT a Laravel)
const actualizarDatos = () => {
  const usuarioActualizado = { ...auth.usuario, ...datosForm.value }
  auth.setSesion(auth.token, usuarioActualizado)
  mostrarModalDatos.value = false
}
</script>

<style scoped>
/* ---------- Cabecera de cliente (original) ---------- */
.nav-link {
  color: #9ca3af;
  transition: color 0.2s;
}
.nav-link:hover {
  color: #234E35;
}
.router-link-active.nav-link {
  color: #234E35 !important;
  border-bottom: 2px solid #234E35;
  padding-bottom: 4px;
}

/* ---------- Layout de personal ---------- */
.ad-shell { display: flex; min-height: 100vh; background: var(--ad-bg); }

.ad-sidebar {
  width: 186px; flex: none; background: #fff; border-right: 1px solid var(--ad-border);
  display: flex; flex-direction: column; padding: 14px 15px 14px;
  position: sticky; top: 0; height: 100vh; overflow-y: auto; z-index: 60;
}
.ad-brand { display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 2px 0 22px; }
.ad-brand-logo { width: 32px; height: 32px; border-radius: 8px; background: var(--ad-brand); color: #fff; display: flex; align-items: center; justify-content: center; }
.ad-brand-logo svg { width: 18px; height: 18px; }
.ad-brand-name { font-size: 16px; font-weight: 700; line-height: 1.1; }
.ad-brand-tag { font-size: 8.5px; letter-spacing: .12em; color: var(--ad-muted); margin-top: 2px; }

.ad-side-label { font-size: 9.5px; letter-spacing: .08em; font-weight: 600; color: var(--ad-muted); margin: 0 0 8px 2px; }
.ad-side-nav { display: flex; flex-direction: column; gap: 4px; }
.ad-side-link {
  display: flex; align-items: center; gap: 12px; height: 38px; padding: 0 14px; border-radius: 8px;
  font-size: 13px; font-weight: 500; color: #4b5a52; transition: background .15s, color .15s; text-decoration: none;
}
.ad-side-link svg { width: 16px; height: 16px; flex: none; }
.ad-side-link:hover { background: var(--ad-head); }
.ad-side-link.router-link-active { background: var(--ad-brand); color: #fff; }

.ad-side-info { margin-top: 22px; background: var(--ad-head); border-radius: 10px; padding: 12px 12px 14px; font-size: 10.5px; color: var(--ad-muted); line-height: 1.5; }
.ad-side-info svg { width: 15px; height: 15px; color: var(--ad-brand); margin-bottom: 8px; display: block; }
.ad-side-info strong { display: block; color: var(--ad-text); font-size: 11px; margin-bottom: 4px; }
.ad-side-info p { margin: 0; }

.ad-side-user { margin-top: auto; padding-top: 18px; }
.ad-side-profile { display: flex; align-items: center; gap: 10px; width: 100%; background: none; border: 0; cursor: pointer; padding: 4px 0; text-align: left; }
.ad-avatar { width: 30px; height: 30px; border-radius: 99px; background: var(--ad-brand-soft); color: var(--ad-brand); font-size: 10.5px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex: none; }
.ad-side-profile-text { display: flex; flex-direction: column; min-width: 0; }
.ad-side-name { font-size: 11.5px; font-weight: 600; color: var(--ad-text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ad-side-role { font-size: 10px; color: var(--ad-muted); }
.ad-side-logout { display: flex; align-items: center; gap: 8px; margin-top: 10px; background: none; border: 0; cursor: pointer; font-size: 11px; color: var(--ad-muted); padding: 4px 0; }
.ad-side-logout svg { width: 14px; height: 14px; }
.ad-side-logout:hover { color: var(--ad-danger); }

.ad-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.ad-topbar {
  height: 60px; background: #fff; border-bottom: 1px solid var(--ad-border); display: flex; align-items: center;
  justify-content: space-between; padding: 0 28px; position: sticky; top: 0; z-index: 40; gap: 12px;
}
.ad-crumbs { font-size: 12px; color: var(--ad-muted); display: flex; gap: 8px; }
.ad-crumbs strong { color: var(--ad-text); font-weight: 600; }
.ad-topbar-right { display: flex; align-items: center; gap: 12px; }
.ad-date { font-size: 11.5px; color: var(--ad-muted); }
.ad-bell { background: none; border: 0; cursor: pointer; color: var(--ad-muted); display: flex; padding: 4px; }
.ad-bell svg { width: 18px; height: 18px; }
.ad-bell:hover { color: var(--ad-text); }
.ad-burger { display: none; background: none; border: 0; cursor: pointer; color: var(--ad-text); padding: 4px; }
.ad-burger svg { width: 22px; height: 22px; }
.ad-content { flex: 1; }
.ad-footer { display: flex; justify-content: space-between; gap: 12px; padding: 18px 28px 22px; font-size: 10.5px; color: var(--ad-muted); max-width: 1256px; width: 100%; margin: 0 auto; }
.ad-backdrop { display: none; }

@media (max-width: 900px) {
  .ad-sidebar { position: fixed; left: 0; top: 0; transform: translateX(-100%); transition: transform .2s; box-shadow: 0 0 40px rgba(0,0,0,.2); }
  .ad-sidebar.open { transform: translateX(0); }
  .ad-backdrop { display: block; position: fixed; inset: 0; background: rgba(15,30,22,.4); z-index: 55; }
  .ad-burger { display: flex; }
  .ad-date { display: none; }
  .ad-topbar { padding: 0 16px; }
  .ad-footer { padding: 16px; flex-direction: column; }
}
</style>
