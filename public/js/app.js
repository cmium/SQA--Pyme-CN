// Script para validaciones y funcionalidades del cliente
document.addEventListener("DOMContentLoaded", () => {
  // Validar cantidad > 0 en formularios
  const cantidadInput = document.getElementById("cantidad")
  if (cantidadInput) {
    cantidadInput.addEventListener("change", function () {
      if (this.value <= 0) {
        alert("La cantidad debe ser mayor a 0")
        this.value = ""
      }
    })
  }

  // Confirmar eliminación
  const deleteButtons = document.querySelectorAll(".btn-danger")
  deleteButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      if (!confirm("¿Estás seguro de que deseas eliminar?")) {
        e.preventDefault()
      }
    })
  })
})
