let tablaExpandida = false;
const filasMostradasInicial = 4;

// Mostrar formulario modal
function mostrarFormulario(id = null) {
  document.getElementById("formularioModal").style.display = "block";
  document.getElementById("modalBackdrop").style.display = "block";

  if (id) {
    const fila = [...document.querySelectorAll("tbody tr")].find(
      (tr) => tr.children[0].textContent == id
    );
    document.getElementById("accion").value = "editar";
    document.getElementById("productor_id").value = id;
    document.getElementById("nombre").value = fila.children[1].textContent;
    document.getElementById("dni").value = fila.children[2].textContent;
    document.getElementById("email").value = fila.children[3].textContent;
    document.getElementById("localidad").value = fila.children[4].textContent;
    document.getElementById("latitud").value = fila.children[5].textContent;
    document.getElementById("longitud").value = fila.children[6].textContent;
  } else {
    document.getElementById("accion").value = "crear";
    document.getElementById("productor_id").value = "";
    document.getElementById("nombre").value = "";
    document.getElementById("dni").value = "";
    document.getElementById("email").value = "";
    document.getElementById("localidad").value = "";
    document.getElementById("latitud").value = "";
    document.getElementById("longitud").value = "";
  }
}

function cerrarFormulario() {
  document.getElementById("formularioModal").style.display = "none";
  document.getElementById("modalBackdrop").style.display = "none";
}

function validarFormulario() {
  const latitud = parseFloat(
    document.getElementById("latitud").value.replace(",", ".")
  );
  const longitud = parseFloat(
    document.getElementById("longitud").value.replace(",", ".")
  );
  const dni = document.getElementById("dni").value;
  const email = document.getElementById("email").value;

  if (!/^\d{7,8}$/.test(dni)) {
    alert("El DNI debe contener 7 u 8 dígitos numéricos.");
    return false;
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    alert("Por favor ingrese un email válido.");
    return false;
  }

  if (isNaN(latitud) || latitud < -90 || latitud > 90) {
    alert("La latitud debe estar entre -90 y 90.");
    return false;
  }

  if (isNaN(longitud) || longitud < -180 || longitud > 180) {
    alert("La longitud debe estar entre -180 y 180.");
    return false;
  }

  return true;
}

// Modal eliminar
function mostrarEliminar(id, nombre) {
  document.getElementById("eliminar_id").value = id;
  document.getElementById("eliminarTexto").innerHTML = `Estás a punto de eliminar a <strong>${nombre}</strong>. Esta acción no se puede deshacer.`;
  document.getElementById("eliminarModal").style.display = "block";
  document.getElementById("modalBackdrop").style.display = "block";
}

function cerrarEliminar() {
  document.getElementById("eliminarModal").style.display = "none";
  document.getElementById("modalBackdrop").style.display = "none";
}

// Buscar por DNI
document.getElementById("busquedaInput").addEventListener("keyup", function (e) {
  if (e.key === "Enter") {
    const dniBuscado = this.value.trim();
    const filas = document.querySelectorAll(".tabla-productores tbody tr");
    const toggleBtn = document.getElementById("toggleTabla");
    let encontrado = false;

    filas.forEach((fila) => {
      const dni = fila.children[2].textContent.trim();

      if (dni === dniBuscado) {
        if (!tablaExpandida) {
          // Expandir si está contraída
          tablaExpandida = true;
          updateToggleIcon();
          filas.forEach((f) => f.classList.remove("fila-oculta"));
        }

        fila.classList.remove("fila-oculta");
        fila.scrollIntoView({ behavior: "smooth", block: "center" });
        fila.classList.add("resaltado-temporal");
        encontrado = true;

        setTimeout(() => {
          fila.classList.remove("resaltado-temporal");
        }, 3500);
      } else {
        fila.classList.remove("resaltado-temporal");
      }
    });

    if (encontrado) {
      this.value = "";
    } else if (dniBuscado !== "") {
      alert("No se encontró un productor con ese DNI.");
    }
  }
});

// Alternar visibilidad de la tabla
function toggleTabla() {
  const filas = document.querySelectorAll(".tabla-productores tbody tr");
  tablaExpandida = !tablaExpandida;

  filas.forEach((fila, index) => {
    if (!tablaExpandida && index >= filasMostradasInicial) {
      fila.classList.add("fila-oculta");
    } else {
      fila.classList.remove("fila-oculta");
    }
  });

  updateToggleIcon();
}

// Cambiar icono de botón expandir/ocultar
function updateToggleIcon() {
  const icon = document.getElementById("toggleIcon");
  if (tablaExpandida) {
    icon.src = "assets/chevron-up.svg";   // para contraer
    icon.alt = "Contraer";
  } else {
    icon.src = "assets/chevron-down.svg"; // para expandir
    icon.alt = "Expandir";
  }
}

// Estado inicial
document.addEventListener("DOMContentLoaded", () => {
  tablaExpandida = true;
  toggleTabla();
});
