router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Si la ruta es pública, dejar pasar
  if (to.meta.public) {
    return true
  }

  // Si no está autenticado, al login
  if (!auth.estaAutenticado) {
    return { name: 'login' }
  }

  // Si hay token pero los datos del usuario aún no se cargan en Pinia (caso típico de F5)
  if (auth.estaAutenticado && (!auth.usuario && !auth.user)) {
    try {
      // Opcional: si tienes una acción en tu store para recargar el perfil, lánzala aquí
      // await auth.fetchUser() 
      return true // Deja pasar para que cargue la vista y monte el store
    } catch (error) {
      return { name: 'login' }
    }
  }

  if (to.name === 'login' && auth.estaAutenticado) {
    const rol = (auth.usuario?.rol || auth.user?.rol || '').toLowerCase()
    if (rol === 'vendedor') return { name: 'ventas' }
    if (rol === 'almacen') return { name: 'inventario' }
    if (rol === 'cliente') return { name: 'catalogo' }
    return { name: 'dashboard' }
  }

  const rolesPermitidos = to.meta.roles
  if (rolesPermitidos && auth.estaAutenticado) {
    const rolUsuario = (auth.usuario?.rol || auth.user?.rol || '').toLowerCase()
    
    // Si el rol aún no está disponible por la recarga F5, evitamos el bucle temporalmente
    if (!rolUsuario) return true

    if (!rolesPermitidos.includes(rolUsuario)) {
      if (rolUsuario === 'vendedor') return { name: 'ventas' }
      if (rolUsuario === 'almacen') return { name: 'inventario' }
      if (rolUsuario === 'cliente') return { name: 'catalogo' }
      return { name: 'dashboard' }
    }
  }
})