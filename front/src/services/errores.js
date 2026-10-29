// Convierte la respuesta de error del backend en un mensaje legible.
// Laravel devuelve { errors: { campo: ['mensaje'] } } con código 422.
export function mensajeError(e, porDefecto = 'Ocurrió un error. Inténtalo de nuevo.') {
  const data = e?.response?.data
  if (data?.errors) {
    const primero = Object.values(data.errors).flat()[0]
    if (primero) return String(primero)
  }
  if (data?.message) return String(data.message)
  return porDefecto
}
