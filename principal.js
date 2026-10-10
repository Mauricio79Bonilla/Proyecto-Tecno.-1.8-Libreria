(function () {
  function enviarReserva(url, datos) {
    return fetch(url, { method: "POST", body: datos })
      .then(function (respuesta) { return respuesta.text(); })
      .then(function (texto) {
        var resultado;
        try {
          resultado = JSON.parse(texto);
        } catch (e) {
          return { ok: false, mensaje: "El servidor respondió: " + texto.substring(0, 200) };
        }
        if (resultado.success) {
          return { ok: true };
        }
        return { ok: false, mensaje: resultado.data || "No se pudo guardar" };
      })
      .catch(function (error) {
        return { ok: false, mensaje: "Error de conexión: " + error.message };
      });
  }

  function iniciarSplash() {
    var splash = document.getElementById("splash");
    if (!splash) {
      return;
    }

    var cerrado = false;
    document.body.classList.add("sin-scroll");

    function cerrar() {
      if (cerrado) {
        return;
      }
      cerrado = true;
      splash.classList.add("salir");
      document.body.classList.remove("sin-scroll");
      setTimeout(function () {
        if (splash.parentNode) {
          splash.parentNode.removeChild(splash);
        }
      }, 1000);
    }

    setTimeout(cerrar, 3000);
    splash.addEventListener("click", cerrar);
  }

  function iniciar() {
    iniciarSplash();

    var secciones = document.querySelectorAll(".seccion-pagina");
    var enlacesNavegacion = document.querySelectorAll('.navegacion a[href^="#"]');

    function mostrarSeccion(hash) {
      var seccionElegida = null;
      try {
        seccionElegida = document.querySelector(hash);
      } catch (e) {
        seccionElegida = null;
      }
      var existeSeccion = seccionElegida && seccionElegida.classList.contains("seccion-pagina");

      secciones.forEach(function (seccion) {
        seccion.hidden = existeSeccion && seccion !== seccionElegida;
      });
    }

    enlacesNavegacion.forEach(function (enlace) {
      enlace.addEventListener("click", function () {
        mostrarSeccion(enlace.getAttribute("href"));
      });
    });

    window.addEventListener("hashchange", function () {
      mostrarSeccion(window.location.hash);
    });
    mostrarSeccion("#inicio");
    if (window.location.hash && window.history && window.history.replaceState) {
      window.history.replaceState(null, "", window.location.pathname + window.location.search);
    }
    window.scrollTo(0, 0);

    var botonesReservar = document.querySelectorAll(".tarjeta-libro button");
    var modalReserva = document.getElementById("modalReserva");
    var productoSeleccionado = document.getElementById("productoSeleccionado");
    var formularioReserva = document.getElementById("formularioReserva");
    var botonCerrarModal = modalReserva ? modalReserva.querySelector(".modal-cerrar") : null;

    if (!modalReserva) {
      return;
    }

    function abrirModal(nombreProducto) {
      productoSeleccionado.textContent = nombreProducto;
      modalReserva.hidden = false;
      document.body.classList.add("sin-scroll");
    }

    function cerrarModal() {
      modalReserva.hidden = true;
      document.body.classList.remove("sin-scroll");
      formularioReserva.reset();
    }

    botonesReservar.forEach(function (boton) {
      boton.addEventListener("click", function () {
        var tarjeta = boton.closest(".tarjeta-libro");
        var titulo = tarjeta ? tarjeta.querySelector("h3") : null;
        abrirModal(titulo ? titulo.textContent.trim() : "");
      });
    });

    if (botonCerrarModal) {
      botonCerrarModal.addEventListener("click", cerrarModal);
    }

    modalReserva.addEventListener("click", function (evento) {
      if (evento.target === modalReserva) {
        cerrarModal();
      }
    });

    document.addEventListener("keydown", function (evento) {
      if (evento.key === "Escape" && !modalReserva.hidden) {
        cerrarModal();
      }
    });

    formularioReserva.addEventListener("submit", function (evento) {
      evento.preventDefault();

      var datos = new FormData(formularioReserva);
      datos.append("producto", productoSeleccionado.textContent);

      enviarReserva(formularioReserva.getAttribute("data-endpoint"), datos).then(function (res) {
        if (res.ok) {
          alert("¡Reserva guardada!");
          cerrarModal();
        } else {
          alert("Error al guardar: " + res.mensaje);
        }
      });
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", iniciar);
  } else {
    iniciar();
  }
})();
