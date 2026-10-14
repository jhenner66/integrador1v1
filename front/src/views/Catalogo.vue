<template>
  <div>
    <!-- ============================================================== -->
    <!-- PANTALLA NORMAL DE LA APLICACIÓN (Se oculta al imprimir)       -->
    <!-- ============================================================== -->
    <div class="pantalla-normal min-h-screen bg-[#F9FBF9] font-sans text-[#234E35] pb-20">
      
      <!-- TOP BAR Y AVISO -->
      <div class="bg-[#EAF2EC] text-[#3A704F] text-xs font-bold text-center py-2 flex justify-center gap-2">
        <span>Tu compra de siempre, ahora más fácil.</span>
        <span class="text-gray-400">·</span>
        <span>Abiertos todos los días de 7:00 a 22:00</span>
      </div>

      <!-- BOTÓN DE CARRITO TELETRANSPORTADO A LA BARRA GLOBAL -->
      <Teleport to="#carrito-slot">
        <button @click="irAlCarrito" class="flex items-center gap-2 bg-[#EAF2EC] text-[#234E35] px-4 py-2 rounded-full font-bold transition hover:bg-[#D2E4D5]">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
          Carrito
          <span class="bg-[#234E35] text-white text-xs px-2 py-0.5 rounded-full">{{ totalItems }}</span>
        </button>
      </Teleport>

      <!-- STEPPER (Solo visible en pasos 1, 2 y 3) -->
      <div v-if="pasoActual > 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center gap-4 text-sm font-bold border-b border-[#E0EBE2] pb-6">
          <div class="flex items-center gap-2" :class="pasoActual >= 1 ? 'text-[#234E35]' : 'text-gray-300'">
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs text-white" :class="pasoActual > 1 ? 'bg-[#234E35]' : (pasoActual === 1 ? 'bg-[#234E35]' : 'bg-gray-200')">
              <svg v-if="pasoActual > 1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              <span v-else>1</span>
            </div>
            Carrito
          </div>
          <div class="w-12 h-px" :class="pasoActual >= 2 ? 'bg-[#234E35]' : 'bg-gray-200'"></div>
          
          <div class="flex items-center gap-2" :class="pasoActual >= 2 ? 'text-[#234E35]' : 'text-gray-300'">
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs text-white" :class="pasoActual > 2 ? 'bg-[#234E35]' : (pasoActual === 2 ? 'bg-[#234E35]' : 'bg-gray-200')">
              <svg v-if="pasoActual > 2" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              <span v-else>2</span>
            </div>
            Finalizar compra
          </div>
          <div class="w-12 h-px" :class="pasoActual >= 3 ? 'bg-[#234E35]' : 'bg-gray-200'"></div>

          <div class="flex items-center gap-2" :class="pasoActual === 3 ? 'text-[#234E35]' : 'text-gray-300'">
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs" :class="pasoActual === 3 ? 'bg-[#234E35] text-white' : 'bg-gray-200 text-gray-400'">3</div>
            Confirmación
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- PASO 0: CATÁLOGO DE PRODUCTOS              -->
      <!-- ========================================== -->
      <main v-if="pasoActual === 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <!-- Ubicación -->
        <div class="flex items-center text-sm font-bold text-gray-500 mb-6">
          <svg class="w-4 h-4 mr-1 text-[#3A704F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
          Entrega en {{ direccionEntregaGlobal || 'Agrega tu dirección' }} 
          <button @click="cambiarDireccionGlobal" class="ml-2 text-[#234E35] underline hover:text-[#3A704F]">Cambiar</button>
          <span class="ml-auto text-[#3A704F] bg-[#EAF2EC] px-3 py-1 rounded-full text-xs">En tu puerta en 45-60 min</span>
        </div>

        <!-- Banner Hero -->
        <div class="bg-[#EAF2EC] rounded-3xl p-8 md:p-12 flex flex-col-reverse md:flex-row items-center justify-between overflow-hidden relative mb-12">
          <div class="md:w-1/2 relative z-10">
            <span class="text-[10px] font-extrabold tracking-widest text-[#3A704F] uppercase mb-4 block">Fresco, rico y de aquí</span>
            <h2 class="text-4xl md:text-6xl font-extrabold leading-tight mb-6">Tu barrio. Tu tienda.<br>Ahora, a un clic.</h2>
            <p class="text-lg text-[#3A704F] mb-8">Hola, {{ auth.usuario?.nombre?.split(' ')[0] || 'Valeria' }}. Llena tu despensa con lo que más te gusta.</p>
            <button class="bg-[#234E35] text-white px-6 py-3 rounded-full font-bold shadow-lg flex items-center gap-2 hover:bg-[#1A3A27] transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
              Explorar productos
            </button>
          </div>
          <div class="md:w-1/2 h-64 md:h-96 w-full relative rounded-2xl overflow-hidden mb-6 md:mb-0">
            <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&q=80&w=1000" class="w-full h-full object-cover rounded-2xl">
            <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur text-[#234E35] text-xs font-bold px-4 py-2 rounded-full flex items-center gap-2 shadow-sm">
              <svg class="w-4 h-4 text-[#3A704F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              El cariño de todos los días
            </div>
          </div>
        </div>

        <!-- Buscador y Categorías -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 mb-8">
          <h3 class="text-2xl font-extrabold">¿Qué necesitas hoy?</h3>
          <div class="relative w-full md:w-96">
            <svg class="w-5 h-5 absolute left-4 top-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" placeholder="Busca leche, frutas, pan y mucho más..." class="w-full pl-12 pr-4 py-3 rounded-full border border-gray-200 focus:outline-none focus:border-[#234E35] focus:ring-1 focus:ring-[#234E35] shadow-sm">
          </div>
        </div>

        <div class="flex flex-wrap gap-2 mb-10">
          <button v-for="cat in categorias" :key="cat" @click="categoriaActiva = cat" 
                  :class="categoriaActiva === cat ? 'bg-[#234E35] text-white border-[#234E35]' : 'bg-white text-gray-500 border-gray-200 hover:border-[#3A704F] hover:text-[#3A704F]'" 
                  class="px-5 py-2 rounded-full text-sm font-bold border transition-colors">
            {{ cat }}
          </button>
        </div>

        <!-- Grid de Productos -->
        <div class="flex justify-between text-sm text-gray-500 font-bold mb-6">
          <span>Los favoritos del barrio · {{ productosFiltrados.length }} productos</span>
          <div class="flex items-center gap-1 cursor-pointer hover:text-[#234E35]">
            Ordenar por: Recomendados
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-24">
          <div v-for="prod in productosFiltrados" :key="prod.id" class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition group relative">
            <!-- Etiqueta En carrito -->
            <div v-if="obtenerCantidad(prod.id) > 0" class="absolute top-6 left-6 bg-[#EAF2EC] text-[#234E35] text-[10px] font-extrabold px-2 py-1 rounded-md z-10 border border-[#D2E4D5]">
              En tu carrito
            </div>
            
            <div class="bg-[#F9FBF9] h-40 rounded-2xl mb-4 p-4 flex items-center justify-center relative overflow-hidden group-hover:bg-[#F1F6F0] transition">
              <img :src="prod.imagen" :alt="prod.nombre" class="h-full object-contain mix-blend-multiply">
            </div>
            
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ prod.categoria }}</div>
            <h4 class="font-extrabold text-base leading-tight mb-1 text-[#234E35]">{{ prod.nombre }}</h4>
            <p class="text-xs text-gray-400 font-medium mb-4">{{ prod.presentacion }}</p>
            
            <div class="flex items-center justify-between">
              <span class="text-lg font-extrabold">S/ {{ prod.precio.toFixed(2) }}</span>
              
              <!-- Botón Agregar o Controles -->
              <button v-if="obtenerCantidad(prod.id) === 0" @click="agregarAlCarrito(prod)" class="bg-[#234E35] text-white text-sm font-bold px-4 py-2 rounded-xl flex items-center gap-1 hover:bg-[#1A3A27] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Añadir
              </button>
              <div v-else class="flex items-center gap-3 border border-gray-200 rounded-xl px-2 py-1">
                <button @click="disminuirCantidad(prod.id)" class="text-gray-400 hover:text-[#234E35] font-extrabold w-6 h-6 flex items-center justify-center">-</button>
                <span class="text-sm font-bold w-4 text-center">{{ obtenerCantidad(prod.id) }}</span>
                <button @click="aumentarCantidad(prod.id)" class="text-[#3A704F] hover:text-[#234E35] font-extrabold w-6 h-6 flex items-center justify-center">+</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Flotante del Carrito -->
        <div v-if="totalItems > 0" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 w-[95%] max-w-4xl bg-[#EAF2EC] border border-[#D2E4D5] rounded-2xl p-4 shadow-xl flex items-center justify-between z-50">
          <div class="flex items-center gap-4">
            <div class="bg-white p-3 rounded-xl shadow-sm">
              <svg class="w-6 h-6 text-[#234E35]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <div>
              <h4 class="font-extrabold text-[#234E35]">Tu carrito ya tiene {{ totalItems }} unidades</h4>
              <p class="text-sm text-[#3A704F]">{{ carrito.length }} productos · Subtotal S/ {{ totalPrecio.toFixed(2) }} · Entrega calculada al comprar</p>
            </div>
          </div>
          <button @click="pasoActual = 1" class="bg-[#234E35] text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 hover:bg-[#1A3A27] transition shadow-md">
            Ver carrito - S/ {{ totalPrecio.toFixed(2) }}
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
          </button>
        </div>
      </main>

      <!-- ========================================== -->
      <!-- PASO 1: RESUMEN DE CARRITO                 -->
      <!-- ========================================== -->
      <main v-if="pasoActual === 1" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
          <h2 class="text-4xl font-extrabold mb-2">Tu carrito</h2>
          <p class="text-gray-500">Todo lo que necesitas, en una sola compra. Revisa tus productos antes de continuar.</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
          <!-- Lista de Productos -->
          <div class="lg:w-2/3 bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
            <div class="flex justify-between text-sm font-bold text-gray-400 mb-6 border-b border-gray-100 pb-4">
              <span>{{ carrito.length }} productos · {{ totalItems }} unidades</span>
              <span>Todos disponibles</span>
            </div>

            <div class="space-y-6">
              <div v-for="item in carrito" :key="item.id" class="flex items-center gap-4 border-b border-gray-50 pb-6 last:border-0 last:pb-0">
                <div class="w-20 h-20 bg-[#F9FBF9] rounded-2xl p-2 flex-shrink-0">
                  <img :src="item.imagen" class="w-full h-full object-contain mix-blend-multiply">
                </div>
                <div class="flex-1">
                  <h4 class="font-extrabold text-base">{{ item.nombre }}</h4>
                  <p class="text-xs text-gray-400 mb-1">{{ item.presentacion }}</p>
                  <p class="text-xs text-gray-400 font-bold">S/ {{ item.precio.toFixed(2) }} / unidad</p>
                </div>
                
                <div class="flex items-center gap-4">
                  <div class="flex items-center gap-3 border border-gray-200 rounded-xl px-3 py-2">
                    <button @click="disminuirCantidad(item.id)" class="text-gray-400 hover:text-[#234E35] font-extrabold w-4 flex justify-center">-</button>
                    <span class="text-sm font-bold w-6 text-center">{{ item.cantidad }}</span>
                    <button @click="aumentarCantidad(item.id)" class="text-[#3A704F] hover:text-[#234E35] font-extrabold w-4 flex justify-center">+</button>
                  </div>
                  
                  <div class="w-20 text-right font-extrabold text-[#234E35]">
                    S/ {{ (item.precio * item.cantidad).toFixed(2) }}
                  </div>
                  
                  <button @click="eliminarDelCarrito(item.id)" class="text-gray-300 hover:text-red-500 flex flex-col items-center justify-center p-2">
                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span class="text-[9px] font-bold">Eliminar</span>
                  </button>
                </div>
              </div>
            </div>

            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-100">
              <button @click="pasoActual = 0" class="text-[#3A704F] font-bold text-sm flex items-center gap-2 hover:underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Seguir comprando
              </button>
              <button @click="carrito = []" class="text-gray-400 text-xs font-bold underline hover:text-red-500">Vaciar carrito</button>
            </div>
            
            <div class="mt-6 bg-[#EAF2EC] rounded-2xl p-4 flex gap-3 text-sm text-[#3A704F]">
              <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              <div>
                <span class="font-extrabold block">Elegimos tus productos como si fueran para casa.</span>
                Frescos, bien cuidados y preparados con cariño por tu tienda de siempre.
              </div>
            </div>
          </div>

          <!-- Sidebar Resumen -->
          <div class="lg:w-1/3">
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm sticky top-24">
              <h3 class="text-xl font-extrabold mb-6">Resumen de tu compra</h3>
              
              <div class="bg-[#F9FBF9] border border-[#E0EBE2] rounded-2xl p-4 mb-6">
                <div class="flex items-center gap-2 font-extrabold text-[#3A704F] mb-1 text-sm">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                  Entrega a domicilio
                </div>
                <p class="text-xs text-gray-500 mb-2">Av. Los Olivos 245, Pueblo Libre Hoy, en 45-60 min</p>
                <p class="text-[10px] text-gray-400">Puedes elegir recojo gratis en el siguiente paso.</p>
              </div>

              <div class="space-y-3 text-sm font-bold text-gray-500 mb-6 border-b border-gray-100 pb-6">
                <div class="flex justify-between">
                  <span>Subtotal ({{ totalItems }} unidades)</span>
                  <span>S/ {{ totalPrecio.toFixed(2) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Entrega a domicilio</span>
                  <span>S/ 5.00</span>
                </div>
              </div>

              <div class="flex justify-between items-end mb-2">
                <span class="font-extrabold text-gray-400">Total</span>
                <span class="text-3xl font-extrabold text-[#234E35]">S/ {{ (totalPrecio + 5).toFixed(2) }}</span>
              </div>
              <p class="text-xs text-gray-400 mb-6 text-right">Precios en soles. Incluyen IGV.</p>

              <button @click="pasoActual = 2" class="w-full bg-[#234E35] text-white py-4 rounded-2xl font-bold flex items-center justify-center gap-2 hover:bg-[#1A3A27] transition shadow-lg shadow-[#234E35]/20 mb-4">
                Continuar con la compra
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
              </button>
              <div class="flex items-center justify-center gap-1 text-[10px] text-gray-400 font-bold">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                Compra segura, sin cargos sorpresa.
              </div>
            </div>
          </div>
        </div>
      </main>

      <!-- ========================================== -->
      <!-- PASO 2: CHECKOUT (DATOS Y PAGO)            -->
      <!-- ========================================== -->
      <main v-if="pasoActual === 2" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
          <h2 class="text-4xl font-extrabold mb-2">Ya casi está tu compra</h2>
          <p class="text-gray-500">Cuéntanos cómo quieres recibirla. Nosotros nos encargamos del resto.</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
          <!-- Formularios -->
          <div class="lg:w-2/3 space-y-6">
            
            <!-- 1. Tus datos -->
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
              <h3 class="flex items-center gap-3 text-lg font-extrabold mb-6">
                <span class="bg-[#EAF2EC] text-[#234E35] w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span>
                Tus datos
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold mb-1 text-gray-500">Nombres</label>
                  <input v-model="form.nombres" type="text" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#234E35] outline-none">
                </div>
                <div>
                  <label class="block text-xs font-bold mb-1 text-gray-500">Apellidos</label>
                  <input v-model="form.apellidos" type="text" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#234E35] outline-none">
                </div>
                <div>
                  <label class="block text-xs font-bold mb-1 text-gray-500">Correo electrónico</label>
                  <input v-model="form.correo" type="email" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#234E35] outline-none">
                </div>
                <div>
                  <label class="block text-xs font-bold mb-1 text-gray-500">Celular</label>
                  <input v-model="form.celular" type="text" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#234E35] outline-none">
                </div>
              </div>
            </div>

            <!-- 2. Entrega -->
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
              <h3 class="flex items-center gap-3 text-lg font-extrabold mb-6">
                <span class="bg-[#EAF2EC] text-[#234E35] w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span>
                ¿Cómo recibes tu pedido?
              </h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div @click="form.tipoEntrega = 'domicilio'" :class="form.tipoEntrega === 'domicilio' ? 'border-[#3A704F] bg-[#F9FBF9]' : 'border-gray-200'" class="border-2 rounded-2xl p-4 cursor-pointer flex items-start gap-3">
                  <div class="w-5 h-5 rounded-full border-2 mt-0.5 flex items-center justify-center" :class="form.tipoEntrega === 'domicilio' ? 'border-[#234E35]' : 'border-gray-300'">
                    <div v-if="form.tipoEntrega === 'domicilio'" class="w-2.5 h-2.5 bg-[#234E35] rounded-full"></div>
                  </div>
                  <div>
                    <div class="font-bold flex items-center gap-2 text-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg> Entrega a domicilio · S/ 5.00</div>
                    <div class="text-xs text-gray-400 mt-1">Hoy, en 45-60 min</div>
                  </div>
                </div>
                <div @click="form.tipoEntrega = 'recojo'" :class="form.tipoEntrega === 'recojo' ? 'border-[#3A704F] bg-[#F9FBF9]' : 'border-gray-200'" class="border-2 rounded-2xl p-4 cursor-pointer flex items-start gap-3">
                  <div class="w-5 h-5 rounded-full border-2 mt-0.5 flex items-center justify-center" :class="form.tipoEntrega === 'recojo' ? 'border-[#234E35]' : 'border-gray-300'">
                    <div v-if="form.tipoEntrega === 'recojo'" class="w-2.5 h-2.5 bg-[#234E35] rounded-full"></div>
                  </div>
                  <div>
                    <div class="font-bold flex items-center gap-2 text-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg> Recojo en tienda · Gratis</div>
                    <div class="text-xs text-gray-400 mt-1">Av. Sucre 890 · Listo en 20 min</div>
                  </div>
                </div>
              </div>

              <div v-if="form.tipoEntrega === 'domicilio'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div class="md:col-span-2">
                    <label class="block text-xs font-bold mb-1 text-gray-500">Dirección</label>
                    <input v-model="form.direccion" type="text" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#234E35] outline-none">
                  </div>
                  <div>
                    <label class="block text-xs font-bold mb-1 text-gray-500">Distrito</label>
                    <input v-model="form.distrito" type="text" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#234E35] outline-none">
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-bold mb-1 text-gray-500">Referencia para la entrega (opcional)</label>
                  <input v-model="form.referencia" type="text" placeholder="Frente al parque. Tocar el timbre 302." class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#234E35] outline-none">
                </div>
                <p class="text-[10px] font-bold text-[#3A704F]">Entrega estimada: Hoy, 11 de octubre · 10:45-11:00</p>
              </div>
            </div>

            <!-- 3. Pago -->
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
              <h3 class="flex items-center gap-3 text-lg font-extrabold mb-6">
                <span class="bg-[#EAF2EC] text-[#234E35] w-8 h-8 rounded-full flex items-center justify-center text-sm">3</span>
                ¿Cómo prefieres pagar?
              </h3>
              
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div @click="form.metodoPago = 'yape'" :class="form.metodoPago === 'yape' ? 'border-[#3A704F] bg-[#F9FBF9]' : 'border-gray-200'" class="border-2 rounded-2xl p-4 cursor-pointer flex items-start gap-3">
                  <div class="w-4 h-4 rounded-full border mt-0.5 flex items-center justify-center" :class="form.metodoPago === 'yape' ? 'border-[#234E35]' : 'border-gray-300'">
                    <div v-if="form.metodoPago === 'yape'" class="w-2 h-2 bg-[#234E35] rounded-full"></div>
                  </div>
                  <div>
                    <div class="font-bold text-sm flex items-center gap-1">Yape / Plin</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Al recibir tu pedido</div>
                  </div>
                </div>
                <div @click="form.metodoPago = 'tarjeta'" :class="form.metodoPago === 'tarjeta' ? 'border-[#3A704F] bg-[#F9FBF9]' : 'border-gray-200'" class="border-2 rounded-2xl p-4 cursor-pointer flex items-start gap-3">
                  <div class="w-4 h-4 rounded-full border mt-0.5 flex items-center justify-center" :class="form.metodoPago === 'tarjeta' ? 'border-[#234E35]' : 'border-gray-300'">
                    <div v-if="form.metodoPago === 'tarjeta'" class="w-2 h-2 bg-[#234E35] rounded-full"></div>
                  </div>
                  <div>
                    <div class="font-bold text-sm flex items-center gap-1">Tarjeta</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">POS al recibir</div>
                  </div>
                </div>
                <div @click="form.metodoPago = 'efectivo'" :class="form.metodoPago === 'efectivo' ? 'border-[#3A704F] bg-[#F9FBF9]' : 'border-gray-200'" class="border-2 rounded-2xl p-4 cursor-pointer flex items-start gap-3">
                  <div class="w-4 h-4 rounded-full border mt-0.5 flex items-center justify-center" :class="form.metodoPago === 'efectivo' ? 'border-[#234E35]' : 'border-gray-300'">
                    <div v-if="form.metodoPago === 'efectivo'" class="w-2 h-2 bg-[#234E35] rounded-full"></div>
                  </div>
                  <div>
                    <div class="font-bold text-sm flex items-center gap-1">Efectivo</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Al recibir tu pedido</div>
                  </div>
                </div>
              </div>
              <p v-if="form.metodoPago === 'yape'" class="text-xs text-gray-500">Paga S/ {{ (totalPrecio + (form.tipoEntrega === 'domicilio' ? 5 : 0)).toFixed(2) }} al recibir. Nuestro repartidor te mostrará el QR de la tienda.</p>
            </div>

            <!-- 4. Comprobante -->
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
              <h3 class="flex items-center gap-3 text-lg font-extrabold mb-6">
                <span class="bg-[#EAF2EC] text-[#234E35] w-8 h-8 rounded-full flex items-center justify-center text-sm">4</span>
                Elige tu comprobante
              </h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div @click="form.comprobante = 'boleta'" :class="form.comprobante === 'boleta' ? 'border-[#3A704F] bg-[#F9FBF9]' : 'border-gray-200'" class="border-2 rounded-2xl p-4 cursor-pointer flex items-start gap-3">
                  <div class="w-5 h-5 rounded-full border-2 mt-0.5 flex items-center justify-center" :class="form.comprobante === 'boleta' ? 'border-[#234E35]' : 'border-gray-300'">
                    <div v-if="form.comprobante === 'boleta'" class="w-2.5 h-2.5 bg-[#234E35] rounded-full"></div>
                  </div>
                  <div>
                    <div class="font-bold text-sm flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Boleta</div>
                    <div class="text-xs text-gray-400 mt-1">Para tu compra personal</div>
                  </div>
                </div>
                <div @click="form.comprobante = 'factura'" :class="form.comprobante === 'factura' ? 'border-[#3A704F] bg-[#F9FBF9]' : 'border-gray-200'" class="border-2 rounded-2xl p-4 cursor-pointer flex items-start gap-3">
                  <div class="w-5 h-5 rounded-full border-2 mt-0.5 flex items-center justify-center" :class="form.comprobante === 'factura' ? 'border-[#234E35]' : 'border-gray-300'">
                    <div v-if="form.comprobante === 'factura'" class="w-2.5 h-2.5 bg-[#234E35] rounded-full"></div>
                  </div>
                  <div>
                    <div class="font-bold text-sm flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg> Factura</div>
                    <div class="text-xs text-gray-400 mt-1">Para tu negocio · Requiere RUC</div>
                  </div>
                </div>
              </div>

              <div v-if="form.comprobante === 'boleta'" class="space-y-4">
                <span class="text-xs font-extrabold tracking-widest text-gray-400 uppercase">Boleta Seleccionada</span>
                <div>
                  <label class="block text-xs font-bold mb-1 text-gray-500">DNI</label>
                  <input v-model="form.dni" type="text" class="w-full md:w-1/2 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#234E35] outline-none">
                </div>
              </div>

              <div v-if="form.comprobante === 'factura'" class="bg-[#F9FBF9] border border-[#E0EBE2] p-4 rounded-2xl space-y-4">
                <span class="text-xs font-extrabold tracking-widest text-[#3A704F] uppercase">Si eliges Factura</span>
                <p class="text-[10px] text-gray-500 mb-2">El DNI se reemplaza por los datos de tu empresa:</p>
                <div>
                  <label class="block text-xs font-bold mb-1 text-gray-500">RUC (11 dígitos)</label>
                  <input v-model="form.ruc" type="text" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#234E35] outline-none">
                </div>
                <div>
                  <label class="block text-xs font-bold mb-1 text-gray-500">Razón social</label>
                  <input v-model="form.razonSocial" type="text" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#234E35] outline-none">
                </div>
              </div>
            </div>

            <button @click="pasoActual = 1" class="text-sm font-bold flex items-center gap-2 text-gray-500 hover:text-[#234E35] mt-6">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
              Volver al carrito
            </button>
          </div>

          <!-- Sidebar Resumen Final -->
          <div class="lg:w-1/3">
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm sticky top-24">
              <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-extrabold">Tu pedido</h3>
                <button @click="pasoActual = 1" class="text-xs font-bold text-[#3A704F] hover:underline">Editar</button>
              </div>
              
              <div class="space-y-4 mb-6 border-b border-gray-100 pb-6">
                <div v-for="item in carrito" :key="item.id" class="flex items-center gap-3 text-sm">
                  <img :src="item.imagen" class="w-10 h-10 object-contain mix-blend-multiply bg-gray-50 rounded-lg p-1">
                  <div class="flex-1 leading-tight">
                    <div class="font-bold">{{ item.nombre }}</div>
                    <div class="text-xs text-gray-400">{{ item.cantidad }} × S/ {{ item.precio.toFixed(2) }}</div>
                  </div>
                  <div class="font-bold text-gray-500">S/ {{ (item.precio * item.cantidad).toFixed(2) }}</div>
                </div>
              </div>

              <div class="space-y-3 text-sm font-bold text-gray-500 mb-6 border-b border-gray-100 pb-6">
                <div class="flex justify-between">
                  <span>Subtotal ({{ totalItems }} unidades)</span>
                  <span>S/ {{ totalPrecio.toFixed(2) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>{{ form.tipoEntrega === 'domicilio' ? 'Entrega a domicilio' : 'Recojo en tienda' }}</span>
                  <span>{{ form.tipoEntrega === 'domicilio' ? 'S/ 5.00' : 'Gratis' }}</span>
                </div>
              </div>

              <div class="flex justify-between items-end mb-2">
                <span class="font-extrabold text-gray-400">Total</span>
                <span class="text-3xl font-extrabold text-[#234E35]">S/ {{ (totalPrecio + (form.tipoEntrega === 'domicilio' ? 5 : 0)).toFixed(2) }}</span>
              </div>
              <p class="text-xs text-gray-400 mb-6">Precios en soles. Incluyen IGV.</p>

              <div class="bg-[#F9FBF9] border border-gray-100 rounded-xl p-4 text-xs font-bold text-gray-500 mb-6 space-y-1">
                <div class="flex items-center gap-2 text-[#3A704F]">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                  {{ form.tipoEntrega === 'domicilio' ? 'Entrega a domicilio - Hoy' : 'Recojo en tienda - Hoy' }}
                </div>
                <div class="flex items-center gap-2">
                  <span class="w-1 h-1 bg-gray-300 rounded-full ml-1"></span>
                  Pago: {{ form.metodoPago.toUpperCase() }} al recibir
                </div>
                <div class="flex items-center gap-2">
                  <span class="w-1 h-1 bg-gray-300 rounded-full ml-1"></span>
                  {{ form.comprobante === 'boleta' ? 'Boleta electrónica' : 'Factura electrónica' }}
                </div>
              </div>

              <label class="flex items-start gap-3 text-xs text-gray-500 mb-6 cursor-pointer">
                <input type="checkbox" checked class="mt-0.5 rounded text-[#234E35] focus:ring-[#234E35]">
                <span>Acepto los términos y condiciones, y la política de privacidad.</span>
              </label>

              <button @click="procesarCompra" class="w-full bg-[#234E35] text-white py-4 rounded-2xl font-bold flex items-center justify-center gap-2 hover:bg-[#1A3A27] transition shadow-lg shadow-[#234E35]/20 mb-3">
                Confirmar pedido · S/ {{ (totalPrecio + (form.tipoEntrega === 'domicilio' ? 5 : 0)).toFixed(2) }}
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
              </button>
              <p class="text-[10px] text-gray-400 text-center font-bold">No se realizará ningún cobro ahora. Pagarás cuando recibas tu pedido.</p>
            </div>
          </div>
        </div>
      </main>

      <!-- ========================================== -->
      <!-- PASO 3: CONFIRMACIÓN Y BOLETA              -->
      <!-- ========================================== -->
      <main v-if="pasoActual === 3" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Banner Success -->
        <div class="bg-[#EAF2EC] border border-[#D2E4D5] rounded-3xl p-8 flex items-center gap-6 mb-8">
          <div class="w-16 h-16 bg-[#234E35] text-white rounded-full flex items-center justify-center flex-shrink-0 shadow-lg shadow-[#234E35]/20">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
          </div>
          <div>
            <h2 class="text-3xl font-extrabold mb-1">¡Gracias, {{ form.nombres.split(' ')[0] }}! Ya recibimos tu pedido.</h2>
            <p class="text-[#3A704F] font-medium">Lo prepararemos con el cariño de siempre. Te enviamos el detalle a <span class="font-bold">{{ form.correo }}</span>.</p>
          </div>
        </div>

        <!-- Resumen Fila -->
        <div class="flex flex-wrap gap-8 lg:gap-16 border-b border-gray-100 pb-8 mb-8 px-4">
          <div>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Número de pedido</p>
            <p class="font-extrabold text-[#234E35]">LE-2026-0148</p>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Fecha</p>
            <p class="font-extrabold text-[#234E35]">11 de octubre de 2026 · 10:00</p>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Entrega estimada</p>
            <p class="font-extrabold text-[#234E35]">Hoy, 10:45–11:00</p>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Estado</p>
            <p class="font-extrabold text-[#234E35]">Pedido confirmado</p>
          </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
          <!-- Detalle de Productos -->
          <div class="lg:w-2/3">
            <h3 class="text-xl font-extrabold mb-6 flex justify-between">
              Esto llegará a tu puerta
              <span class="text-sm font-bold text-gray-400">{{ carrito.length }} productos · {{ totalItems }} unidades</span>
            </h3>
            
            <div class="space-y-6 mb-8">
              <div v-for="item in carrito" :key="item.id" class="flex items-center gap-4">
                <div class="w-16 h-16 bg-[#F9FBF9] border border-gray-100 rounded-xl p-2 flex-shrink-0">
                  <img :src="item.imagen" class="w-full h-full object-contain mix-blend-multiply">
                </div>
                <div class="flex-1">
                  <h4 class="font-extrabold text-sm">{{ item.nombre }}</h4>
                  <p class="text-xs text-gray-400">{{ item.presentacion }}</p>
                </div>
                <div class="text-sm text-gray-400 font-bold">{{ item.cantidad }} × S/ {{ item.precio.toFixed(2) }}</div>
                <div class="w-20 text-right font-extrabold text-[#234E35]">S/ {{ (item.precio * item.cantidad).toFixed(2) }}</div>
              </div>
            </div>

            <div class="space-y-3 text-sm font-bold text-gray-500 mb-6 border-t border-b border-gray-100 py-6">
              <div class="flex justify-between">
                <span>Subtotal ({{ totalItems }} unidades)</span>
                <span>S/ {{ totalPrecio.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Entrega a domicilio</span>
                <span>S/ {{ form.tipoEntrega === 'domicilio' ? '5.00' : '0.00' }}</span>
              </div>
            </div>
            
            <div class="flex justify-between items-end mb-2">
              <span class="font-extrabold text-xl">Total</span>
              <span class="text-3xl font-extrabold text-[#234E35]">S/ {{ (totalPrecio + (form.tipoEntrega === 'domicilio' ? 5 : 0)).toFixed(2) }}</span>
            </div>
            <p class="text-xs text-gray-400">Precios en soles. Incluyen IGV.</p>

            <div class="mt-8 flex items-center gap-2 text-sm font-bold text-[#3A704F]">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
              ¿Algo que contarnos? Escríbenos por WhatsApp.
            </div>
          </div>

          <!-- Tarjetas Laterales de Info -->
          <div class="lg:w-1/3 space-y-6">
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
              <h3 class="flex items-center gap-2 font-extrabold mb-4">
                <svg class="w-5 h-5 text-[#3A704F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                {{ form.tipoEntrega === 'domicilio' ? 'Entrega a domicilio' : 'Recojo en tienda' }}
              </h3>
              <div class="text-sm space-y-1 mb-6">
                <p class="font-bold">{{ form.nombres }} {{ form.apellidos }}</p>
                <p class="text-gray-500">{{ form.direccion }}, {{ form.distrito }}</p>
                <p class="text-gray-500">Cel: {{ form.celular }}</p>
              </div>
              
              <h3 class="flex items-center gap-2 font-extrabold mb-2 border-t border-gray-100 pt-4">
                <svg class="w-5 h-5 text-[#3A704F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Pago contra entrega
              </h3>
              <p class="text-sm font-bold text-gray-500">{{ form.metodoPago.toUpperCase() }} al recibir</p>
              <p class="text-xs text-gray-400">Pendiente de pago - S/ {{ (totalPrecio + (form.tipoEntrega === 'domicilio' ? 5 : 0)).toFixed(2) }}</p>
            </div>

            <div class="bg-[#F9FBF9] border border-[#E0EBE2] rounded-3xl p-6">
              <h3 class="flex items-center gap-2 font-extrabold mb-4">
                <svg class="w-5 h-5 text-[#3A704F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                {{ form.comprobante === 'boleta' ? 'Tu boleta electrónica' : 'Tu factura electrónica' }}
              </h3>
              <p class="text-sm font-extrabold mb-1">B001-000148 · {{ form.comprobante === 'boleta' ? 'DNI ' + form.dni : 'RUC ' + form.ruc }}</p>
              <p class="text-xs text-gray-500 mb-6">Consulta tu comprobante y guarda una copia de esta compra.</p>
              
              <button @click="verBoleta" class="w-full bg-[#234E35] text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-[#1A3A27] transition mb-3 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                Ver boleta
              </button>
              <button @click="descargarPDF" class="w-full bg-white border border-gray-200 text-[#234E35] py-3 rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Descargar PDF
              </button>
            </div>

            <div class="flex justify-between items-center mt-8">
              <button class="text-sm font-bold text-gray-500 hover:text-[#234E35]">Ver mis pedidos</button>
              <button @click="pasoActual = 0; carrito = []" class="bg-[#234E35] text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 hover:bg-[#1A3A27] transition shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Volver a la tienda
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- ============================================================== -->
    <!-- FORMATO FORMAL DE BOLETA (Solo visible al guardar/imprimir)    -->
    <!-- ============================================================== -->
    <div class="solo-impresion bg-white text-black p-8 font-sans max-w-2xl mx-auto">
      
      <!-- Cabecera de la Empresa -->
      <div class="text-center mb-6 border-b-2 border-black pb-6">
        <h1 class="text-2xl font-bold uppercase tracking-wide">D' Todo Minimarket S.A.C.</h1>
        <p class="text-sm mt-1">RUC: 20123456789</p>
        <p class="text-sm">Av. Sucre 890, Pueblo Libre, Lima - Perú</p>
        <p class="text-sm">Teléfono: (01) 555-1234</p>
        
        <div class="mt-6 border-2 border-black p-3 font-bold text-lg uppercase inline-block min-w-[300px]">
          {{ form.comprobante === 'factura' ? 'Factura' : 'Boleta' }} DE VENTA ELECTRÓNICA
          <br>
          <span class="text-2xl tracking-widest mt-2 block">
            {{ form.comprobante === 'factura' ? 'F001' : 'B001' }}-000148
          </span>
        </div>
      </div>

      <!-- Datos del Cliente -->
      <div class="mb-8 text-sm grid grid-cols-2 gap-y-2 border-b-2 border-black pb-6">
        <div class="col-span-2">
          <span class="font-bold">Fecha de Emisión:</span> {{ fechaActual }}
        </div>
        <div class="col-span-2">
          <span class="font-bold">Señor(es):</span> 
          {{ form.comprobante === 'factura' ? form.razonSocial : (form.nombres + ' ' + form.apellidos) }}
        </div>
        <div class="col-span-2">
          <span class="font-bold">{{ form.comprobante === 'factura' ? 'RUC' : 'DNI' }}:</span> 
          {{ form.comprobante === 'factura' ? form.ruc : form.dni }}
        </div>
        <div class="col-span-2">
          <span class="font-bold">Dirección:</span> 
          {{ form.tipoEntrega === 'domicilio' ? (form.direccion + ', ' + form.distrito) : 'Recojo en tienda (Av. Sucre 890)' }}
        </div>
        <div class="col-span-2">
          <span class="font-bold">Método de Pago:</span> {{ form.metodoPago.toUpperCase() }}
        </div>
      </div>

      <!-- Tabla de Productos -->
      <table class="w-full text-sm mb-6 border-collapse">
        <thead>
          <tr class="border-b-2 border-black text-left">
            <th class="py-2 w-12">Cant.</th>
            <th class="py-2">Descripción</th>
            <th class="py-2 text-right">P. Unit</th>
            <th class="py-2 text-right">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in carrito" :key="item.id" class="border-b border-gray-200">
            <td class="py-3">{{ item.cantidad }}</td>
            <td class="py-3">{{ item.nombre }}</td>
            <td class="py-3 text-right">S/ {{ item.precio.toFixed(2) }}</td>
            <td class="py-3 text-right">S/ {{ (item.precio * item.cantidad).toFixed(2) }}</td>
          </tr>
          <!-- Fila de Delivery (si aplica) -->
          <tr v-if="form.tipoEntrega === 'domicilio'" class="border-b border-gray-200">
            <td class="py-3">1</td>
            <td class="py-3">Servicio de Delivery</td>
            <td class="py-3 text-right">S/ 5.00</td>
            <td class="py-3 text-right">S/ 5.00</td>
          </tr>
        </tbody>
      </table>

      <!-- Resumen de Pagos y SUNAT -->
      <div class="flex justify-end text-sm mb-12">
        <div class="w-72">
          <div class="flex justify-between mb-2">
            <span>OP. GRAVADAS:</span>
            <span>S/ {{ ((totalPrecio + (form.tipoEntrega === 'domicilio' ? 5 : 0)) / 1.18).toFixed(2) }}</span>
          </div>
          <div class="flex justify-between mb-2">
            <span>IGV (18%):</span>
            <span>S/ {{ ((totalPrecio + (form.tipoEntrega === 'domicilio' ? 5 : 0)) - ((totalPrecio + (form.tipoEntrega === 'domicilio' ? 5 : 0)) / 1.18)).toFixed(2) }}</span>
          </div>
          <div class="flex justify-between font-extrabold text-lg mt-3 border-t-2 border-black pt-3">
            <span>TOTAL A PAGAR:</span>
            <span>S/ {{ (totalPrecio + (form.tipoEntrega === 'domicilio' ? 5 : 0)).toFixed(2) }}</span>
          </div>
        </div>
      </div>

      <!-- Pie de página formal -->
      <div class="text-center text-xs text-gray-500 mt-8">
        <p>Representación impresa de la {{ form.comprobante === 'factura' ? 'Factura' : 'Boleta' }} de Venta Electrónica.</p>
        <p>Autorizado mediante resolución de Superintendencia Nro. 000-2026/SUNAT</p>
        <p class="mt-4 font-bold text-black text-sm uppercase">¡Gracias por su preferencia!</p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue' 
import { useAuthStore } from '../stores/auth'
import api from '../services/api' // <-- Importante: tu conexión con Laravel

const auth = useAuthStore()
const pasoActual = ref(0) 

// 1. OBTENER PRODUCTOS REALES DESDE LARAVEL
const productos = ref([])
const categorias = ref(['Todos los productos'])
const categoriaActiva = ref('Todos los productos')

const cargarProductos = async () => {
  try {
    // Llama a tu ProductoController en el backend
    const { data } = await api.get('/productos')
    productos.value = data.data || data
    
    // Extraer categorías únicas dinámicamente
    const cats = [...new Set(productos.value.map(p => p.categoria))]
    categorias.value = ['Todos los productos', ...cats]
  } catch (error) {
    console.error("Error al cargar productos desde la base de datos:", error)
  }
}

// Ejecutar al entrar a la página
onMounted(() => {
  cargarProductos()
})

const productosFiltrados = computed(() => {
  if (categoriaActiva.value === 'Todos los productos') return productos.value
  return productos.value.filter(p => p.categoria === categoriaActiva.value)
})

// --- Lógica del Carrito ---
const carrito = ref([])

const agregarAlCarrito = (producto) => {
  carrito.value.push({ ...producto, cantidad: 1 })
}
const aumentarCantidad = (id) => {
  const item = carrito.value.find(item => item.id === id)
  if (item) item.cantidad++
}
const disminuirCantidad = (id) => {
  const item = carrito.value.find(item => item.id === id)
  if (item && item.cantidad > 1) item.cantidad--
  else eliminarDelCarrito(id)
}
const eliminarDelCarrito = (id) => {
  carrito.value = carrito.value.filter(item => item.id !== id)
}
const obtenerCantidad = (id) => {
  const item = carrito.value.find(item => item.id === id)
  return item ? item.cantidad : 0
}

const totalItems = computed(() => carrito.value.reduce((sum, item) => sum + item.cantidad, 0))
const totalPrecio = computed(() => carrito.value.reduce((sum, item) => sum + (item.precio * item.cantidad), 0))

const irAlCarrito = () => {
  if (totalItems.value > 0) pasoActual.value = 1
}

// --- Lógica de Dirección Global ---
const direccionEntregaGlobal = ref('Av. Los Olivos 245, Pueblo Libre')
const cambiarDireccionGlobal = () => {
  const nueva = prompt('Ingresa tu nueva dirección de entrega:', direccionEntregaGlobal.value)
  if (nueva && nueva.trim() !== '') {
    direccionEntregaGlobal.value = nueva
    form.value.direccion = nueva 
  }
}

// --- Formulario Checkout ---
const form = ref({
  nombres: auth.usuario?.nombre || '',
  apellidos: auth.usuario?.apellidos || '',
  correo: auth.usuario?.correo || '',
  celular: auth.usuario?.celular || '',
  tipoEntrega: 'domicilio',
  direccion: direccionEntregaGlobal.value,
  distrito: 'Pueblo Libre',
  referencia: '',
  metodoPago: 'yape',
  comprobante: 'boleta',
  dni: auth.usuario?.dni || '',
  ruc: '',
  razonSocial: ''
})

watch(() => auth.usuario, (nuevo) => {
  if (nuevo) {
    form.value.nombres = nuevo.nombre || form.value.nombres
    form.value.apellidos = nuevo.apellidos || form.value.apellidos
    form.value.correo = nuevo.correo || form.value.correo
    form.value.celular = nuevo.celular || form.value.celular
    form.value.dni = nuevo.dni || form.value.dni
  }
}, { deep: true, immediate: true })


// 2. ENVIAR LA COMPRA A LARAVEL
const cargandoCompra = ref(false)
const numeroPedidoGenerado = ref('')
const fechaActual = computed(() => new Date().toLocaleDateString('es-PE', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }))

const procesarCompra = async () => {
  cargandoCompra.value = true
  try {
    // Empaquetamos los datos como los espera tu base de datos
    const payload = {
      usuario_id: auth.usuario?.id, // ID del cliente
      tipo_entrega: form.value.tipoEntrega,
      metodo_pago: form.value.metodoPago,
      tipo_comprobante: form.value.comprobante,
      direccion_envio: form.value.direccion,
      total: totalPrecio.value + (form.value.tipoEntrega === 'domicilio' ? 5 : 0),
      // Mapeamos el carrito para la tabla detalle_pedidos o detalle_ventas
      detalles: carrito.value.map(item => ({
        producto_id: item.id,
        cantidad: item.cantidad,
        precio_unitario: item.precio,
        subtotal: item.precio * item.cantidad
      }))
    }

    // Llamamos a tu PedidoController o VentaController
    const { data } = await api.post('/pedidos', payload)

    // Capturamos el código devuelto por el backend
    numeroPedidoGenerado.value = data.numero_pedido || `PE-${new Date().getTime()}`
    
    // Si la compra es exitosa, avanzamos a la confirmación
    window.scrollTo({ top: 0, behavior: 'smooth' })
    pasoActual.value = 3

  } catch (error) {
    console.error("Error procesando la compra:", error)
    alert("Hubo un problema al conectar con el servidor. " + (error.response?.data?.message || ''))
  } finally {
    cargandoCompra.value = false
  }
}

const verBoleta = () => window.print()
const descargarPDF = async () => alert('El PDF será generado por dompdf en el backend.')
</script>

<style scoped>
/* Ocultar el ticket formal en la vista web normal */
.solo-impresion {
  display: none;
}

/* Magia de Impresión: Al guardar como PDF o imprimir */
@media print {
  /* 1. Ocultar todos los colores y diseño interactivo de la app normal */
  .pantalla-normal {
    display: none !important;
  }
  
  /* 2. Mostrar la boleta formal en blanco y negro */
  .solo-impresion {
    display: block !important;
    width: 100%;
  }

  /* 3. Limpiar márgenes para que el PDF se genere perfecto en hoja A4 */
  @page {
    margin: 1.5cm;
    size: A4 portrait;
  }
}
</style>