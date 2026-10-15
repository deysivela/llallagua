(function () {
  "use strict";

  const CFG = window.GAMCredencial || {};
  const EMPLOYEES = CFG.employees || [];

  // Tope de la foto, en MB. El mismo valor esta en la validacion del
  // controller (max:8192); aqui se revisa antes de subir para no mandar un
  // archivo que el servidor va a rechazar.
  const FOTO_MAX_MB = 8;
  const FOTO_MAX_BYTES = FOTO_MAX_MB * 1024 * 1024;

  const employeeSelect = document.getElementById("employee");
  const employeeSearch = document.getElementById("employee-search");
  const employeeResults = document.getElementById("employee-results");
  const fotoPreview = document.getElementById("foto-preview");
  const fotoInput = document.getElementById("foto-input");
  const previewFront = document.getElementById("preview-front");
  const btnGenerar = document.getElementById("btn-generar");
  const btnGenerarText = document.getElementById("btn-generar-text");
  const btnPrintFront = document.getElementById("btn-print-front");
  const btnPrintBack = document.getElementById("btn-print-back");
  const btnReset = document.getElementById("btn-reset");
  const searchInput = document.getElementById("search-input");
  const searchResults = document.getElementById("search-results");
  const searchCount = document.getElementById("search-count");
  const alertMsg = document.getElementById("alert-msg");
  const generatedHint = document.getElementById("generated-hint");
  const generatedHash = document.getElementById("generated-hash");
  const generatedLink = document.getElementById("generated-link");

  const preview = {
    foto: document.getElementById("preview-foto"),
    fotoPlaceholder: document.getElementById("preview-foto-placeholder"),
    nombre: document.getElementById("preview-nombre"),
    cargo: document.getElementById("preview-cargo"),
  };

  try {
    document.title = "Credencial GAMLL - Llallagua";
  } catch (e) {}

  const printEl = {
    foto: document.getElementById("print-foto"),
    fotoPlaceholder: document.getElementById("print-foto-placeholder"),
    nombre: document.getElementById("print-nombre"),
    cargo: document.getElementById("print-cargo"),
  };

  const qrImg = {
    preview: document.getElementById("preview-qr"),
    print: document.getElementById("print-qr"),
  };

  const previewSheet = document.getElementById("preview-sheet");
  const previewStage = document.querySelector("#cred-app .preview-stage");

  let state = { employee: null, rec: null };

  function csrfToken() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute("content") : "";
  }

  // tipo "aviso" pinta el mensaje en tono informativo: se usa cuando el
  // archivo no se subio por una razon que no es un fallo (por ejemplo pesa
  // mas de 8 MB) y no debe verse como un error.
  function showAlert(msg, tipo) {
    alertMsg.textContent = msg || "";
    alertMsg.classList.toggle("alert-msg--aviso", msg !== "" && msg !== null && tipo === "aviso");
    alertMsg.style.display = msg ? "block" : "none";
  }

  function fotoEnMb(bytes) {
    return (bytes / (1024 * 1024)).toFixed(1).replace(".", ",") + " MB";
  }

  function showHint(rec) {
    generatedHint.style.display = rec ? "block" : "none";
    if (rec) {
      generatedHash.textContent = rec.hash || "";
      generatedLink.textContent = rec.url;
      generatedLink.href = rec.url;
    }
  }

  function pad(n) {
    return String(n).padStart(2, "0");
  }

  /* ---------- Medidas de la maqueta ----------
     El papel es 54 × 86 mm. La credencial se mide en px de diseño sobre una hoja
     de 1 px = 1 em, así que para llenar la tarjeta la proporción tiene que ser
     54/86 = 0.628: con el ancho de 1024 el alto sale 1024 × 86/54 = 1631.
     Estos dos valores tienen que coincidir con --design-w y --design-h de
     credencial.css; si se cambia uno hay que cambiar el otro. */

const DISENO_W = 1024;
const DISENO_H = 1631;

  /* ---------- Vista previa ---------- */

  let factorPrevio = 0;

  function fitPreview() {
    if (!previewSheet || !previewStage) return;
    const stacked = !window.matchMedia("(min-width: 901px)").matches;
    const sheetW = stacked ? DISENO_W : DISENO_W * 2 + 16;
    const sheetH = stacked ? DISENO_H * 2 + 16 : DISENO_H;

    const cs = getComputedStyle(previewStage);
    const padW = parseFloat(cs.paddingLeft) + parseFloat(cs.paddingRight);
    const padH = parseFloat(cs.paddingTop) + parseFloat(cs.paddingBottom);
    // El alto sale del propio panel (min-height: 460px), no de restarle a
    // window.innerHeight la posicion del panel: asi el tamaño no cambia al hacer
    // scroll ni depende de donde estaba la pagina cuando cargo.
    const minH = parseFloat(cs.minHeight);
    const alto = (isFinite(minH) ? minH : 460) - padH;
    const availH = stacked ? alto * 2 : alto;
    const availW = Math.max(previewStage.clientWidth - padW - 24, 10);

    // El limite vertical va por cara, no por la hoja: en movil las dos van
    // apiladas y medir sobre sheetH las encogeria a la mitad.
    const factor = Math.max(Math.min(availW / sheetW, availH / DISENO_H), 0.05);
    // El panel cambia de alto al cambiar el tamaño del contenido; sin esta
    // comparacion el ResizeObserver se realimentaria solo.
    if (Math.abs(factor - factorPrevio) < 0.001) return;
    factorPrevio = factor;

    previewSheet.style.transform = "scale(" + factor + ")";
    previewSheet.style.transformOrigin = "top left";
    previewSheet.style.width = sheetW * factor + "px";
    previewSheet.style.height = sheetH * factor + "px";
  }

  function setPhotoDisplay(imgEl, placeholderEl, src) {
    if (src) {
      imgEl.src = src;
      imgEl.hidden = false;
      placeholderEl.hidden = true;
    } else {
      imgEl.src = "";
      imgEl.hidden = true;
      placeholderEl.hidden = false;
    }
  }

  function linesOf(el) {
    const cs = getComputedStyle(el);
    const lh = parseFloat(cs.lineHeight) || parseFloat(cs.fontSize) * 1.2;
    return Math.round(el.scrollHeight / lh);
  }

  function fitText(el, maxSize, maxLines) {
    if (!el) return;
    el.style.fontSize = "";
    const text = el.textContent;
    if (!text || text === "—") return;
    maxLines = maxLines || 2;
    let size = maxSize;
    el.style.fontSize = size + "em";
    while (size > 18 && linesOf(el) > maxLines) {
      size -= 1;
      el.style.fontSize = size + "em";
    }
  }

  function fitTitles() {
    document.querySelectorAll(".cred-header__title").forEach(function (el) {
      fitText(el, 46, 3);
    });
  }

  function renderPreviews() {
    const nombre = stateNombre();
    const cargo = stateCargo();

    preview.nombre.textContent = nombre || "—";
    preview.cargo.textContent = cargo || "—";
    printEl.nombre.textContent = nombre;
    printEl.cargo.textContent = cargo;

    const foto = stateFoto();
    setPhotoDisplay(preview.foto, preview.fotoPlaceholder, foto);
    setPhotoDisplay(printEl.foto, printEl.fotoPlaceholder, foto);

    fitText(preview.nombre, 50, 2);
    fitText(preview.cargo, 44, 2);
    fitText(printEl.nombre, 50, 2);
    fitText(printEl.cargo, 44, 2);

    // Si el nombre y el cargo ocupan dos filas, el bloque de campos empieza mas
    // abajo y deja el QR y la ciudad pegados: el CSS baja ambos con esta clase.
    // El clon de impresion y el de descarga salen de previewFront, asi que la
    // clase viaja sola a las tres salidas.
    if (previewFront) {
      const dobleFila = linesOf(preview.nombre) > 1 && linesOf(preview.cargo) > 1;
      previewFront.classList.toggle("cred-page--doble", dobleFila);
    }
  }

  function drawQr(text) {
    if (!window.QRCode) return Promise.resolve();
    return QRCode.toDataURL(text, {
      errorCorrectionLevel: "M",
      margin: 1,
      width: 220,
      color: { dark: "#005029", light: "#ffffff" },
    })
      .then(function (dataUrl) {
        qrImg.preview.src = dataUrl;
        qrImg.print.src = dataUrl;
      })
      .catch(function () {});
  }

  function clearQr() {
    qrImg.preview.src = "";
    qrImg.print.src = "";
  }

  /* ---------- Nombre, cargo y foto del funcionario ---------- */

  function stateNombre() {
    return state.rec ? state.rec.nombre : (state.employee ? state.employee.nombre : "");
  }

  function stateCargo() {
    return state.rec ? state.rec.cargo : (state.employee ? state.employee.cargo : "");
  }

  function stateFoto() {
    if (state.rec && state.rec.foto) return state.rec.foto;
    return state.employee ? state.employee.foto : null;
  }

  function renderFotoDisplay() {
    const src = stateFoto();
    if (src) {
      fotoPreview.src = src;
      fotoPreview.removeAttribute("hidden");
    } else {
      fotoPreview.removeAttribute("src");
      fotoPreview.setAttribute("hidden", "");
    }
  }

  /* ---------- Foto del funcionario ---------- */

  function setEmployeeFotoUrl(url) {
    const id = state.employee ? state.employee.id : null;
    if (!id) return;
    const emp = EMPLOYEES.find(function (e) { return e.id === id; });
    if (emp) emp.foto = url;
    if (state.employee) state.employee.foto = url;
    if (state.rec && String(state.rec.employee_id) === String(id)) state.rec.foto = url;
  }

  async function subirFoto(data) {
    const fd = new FormData();
    fd.append("employee_id", state.employee ? state.employee.id : "");
    data.appendTo(fd);
    const res = await fetch(CFG.fotoUrl, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": csrfToken(),
        "X-Requested-With": "XMLHttpRequest",
      },
      body: fd,
    });
    return res.json().catch(function () {
      return { ok: false, error: "Respuesta inválida del servidor" };
    });
  }

  /* ---------- Encuadre de la foto: arrastrar y zoom ----------
     El encuadre no va en una columna nueva: se guarda pegado a la ruta de la
     foto dentro de la misma columna photo, como "?z=1.35&x=12&y=-8" (ver
     Employee::componerFoto). El PHP acota los mismos valores, pero aqui tambien
     se acotan para que la foto no pueda salirse del marco mientras se arrastra.

     El desplazamiento va en em porque .cred-page usa font-size: 1px: 1em es una
     unidad de diseno (1024 x 1631), el mismo numero sirve en la pantalla y en
     el papel y no hay que recalcular nada cuando la vista cambia de tamano. */

  const FOTO_ZOOM_MIN = 1;
  const FOTO_ZOOM_MAX = 4;
  // El marco mide 490em x 440em, pero con borde de 5em y box-sizing: border-box
  // lo que ve la foto son 480 x 430.
  const FOTO_MARCO_ANCHO = 480;
  const FOTO_MARCO_ALTO = 430;
  const FOTO_MARCO_BORDE = 5;
  const FOTO_MARCO_TOTAL_ANCHO = FOTO_MARCO_ANCHO + FOTO_MARCO_BORDE * 2;
  const FOTO_MARCO_TOTAL_ALTO = FOTO_MARCO_ALTO + FOTO_MARCO_BORDE * 2;

  const fotoAjuste = document.getElementById("foto-ajuste");
  const fotoZoom = document.getElementById("foto-zoom");
  const fotoZoomSalida = document.getElementById("foto-zoom-salida");
  const btnFotoCentrar = document.getElementById("foto-centrar");
  const btnFotoEncuadre = document.getElementById("foto-encuadre");
  const fotoAjusteEstado = document.getElementById("foto-ajuste-estado");

  let ajuste = { z: FOTO_ZOOM_MIN, x: 0, y: 0 };
  let arrastre = null;
  let temporizadorGuardado = null;
  let temporizadorZoom = null;

  function marcoFoto() {
    return preview.foto ? preview.foto.parentElement : null;
  }

  function acotarAjuste(a) {
    const z = Math.max(FOTO_ZOOM_MIN, Math.min(FOTO_ZOOM_MAX, a.z || FOTO_ZOOM_MIN));
    const margenX = (FOTO_MARCO_ANCHO * (z - 1)) / 2;
    const margenY = (FOTO_MARCO_ALTO * (z - 1)) / 2;
    return {
      z: z,
      x: Math.max(-margenX, Math.min(margenX, a.x || 0)),
      y: Math.max(-margenY, Math.min(margenY, a.y || 0)),
    };
  }

  function pintarAjuste() {
    const t = "translate(" + ajuste.x + "em, " + ajuste.y + "em) scale(" + ajuste.z + ")";
    // printEl es el markup estatico de impresion: se pinta igual para que las
    // dos copias de la credencial salgan con el mismo encuadre.
    if (preview.foto) preview.foto.style.transform = t;
    if (printEl.foto) printEl.foto.style.transform = t;
    if (fotoZoom) fotoZoom.value = String(ajuste.z);
    if (fotoZoomSalida) fotoZoomSalida.textContent = Math.round(ajuste.z * 100) + " %";
  }

  // Pixels de pantalla por unidad de diseno. La hoja de la credencial se
  // escala con transform: scale() para que entre en el panel, y eso hace que
  // getBoundingClientRect() devuelva medidas YA escaladas mientras que
  // clientWidth y getComputedStyle() devuelven las de maquetacion, sin escala.
  // Por eso el factor se saca del propio rect (comparando contra el ancho
  // conocido del marco en unidades de diseno) y no de las medidas de layout:
  // mezclarlas haria que la foto se moviera mas lento o mas rapido que el
  // mouse segun el tamaño del panel.
  function factorPantalla() {
    const marco = marcoFoto();
    if (!marco) return 1;
    const ancho = marco.getBoundingClientRect().width;
    if (!ancho) return 1;
    return ancho / FOTO_MARCO_TOTAL_ANCHO;
  }

  // Cuantas unidades de diseno equivalen a un pixel de pantalla.
  function unidadesPorPx() {
    const factor = factorPantalla();
    return factor > 0 ? 1 / factor : 1;
  }

  function estadoAjuste(texto, error) {
    if (!fotoAjusteEstado) return;
    fotoAjusteEstado.textContent = texto || "";
    if (error) fotoAjusteEstado.setAttribute("data-estado", "error");
    else fotoAjusteEstado.removeAttribute("data-estado");
  }

  function mostrarEditorFoto() {
    const visible = !!stateFoto();
    if (fotoAjuste) {
      if (visible) fotoAjuste.removeAttribute("hidden");
      else fotoAjuste.setAttribute("hidden", "");
    }
    const marco = marcoFoto();
    if (marco) marco.classList.toggle("cred-photo--editable", visible);
  }

  function cargarAjusteEmpleado() {
    const id = state.employee ? state.employee.id : null;
    const emp = id ? EMPLOYEES.find(function (e) { return e.id === id; }) : null;
    const guardado = (emp && emp.ajuste) || {};
    ajuste = acotarAjuste({
      z: guardado.z || FOTO_ZOOM_MIN,
      x: guardado.x || 0,
      y: guardado.y || 0,
    });
    estadoAjuste("");
    pintarAjuste();
  }

  function setAjuste(nuevo, guardar) {
    ajuste = acotarAjuste(nuevo);
    pintarAjuste();
    if (guardar) programarGuardado();
    return ajuste;
  }

  function programarGuardado() {
    if (temporizadorGuardado) clearTimeout(temporizadorGuardado);
    temporizadorGuardado = setTimeout(guardarAjuste, 600);
  }

  async function guardarAjuste() {
    temporizadorGuardado = null;
    const emp = state.employee;
    if (!emp || !emp.id || !CFG.ajusteUrl) return;

    const id = emp.id;
    const enviado = { z: ajuste.z, x: ajuste.x, y: ajuste.y };
    estadoAjuste("Guardando el encuadre…");

    const cuerpo = new URLSearchParams();
    cuerpo.append("employee_id", String(id));
    cuerpo.append("z", String(enviado.z));
    cuerpo.append("x", String(enviado.x));
    cuerpo.append("y", String(enviado.y));

    try {
      const res = await apiFetch(CFG.ajusteUrl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
        body: cuerpo.toString(),
      });

      if (!res || !res.ok) {
        estadoAjuste("No se pudo guardar el encuadre. Se conserva solo en esta pantalla.", true);
        return;
      }

      // El servidor acota los valores y devuelve los definitivos: se adoptan
      // para que lo que se ve en pantalla sea exactamente lo que quedo guardado.
      if (res.ajuste) ajuste = acotarAjuste(res.ajuste);
      const guardado = EMPLOYEES.find(function (e) { return e.id === id; });
      if (guardado) {
        guardado.ajuste = { z: ajuste.z, x: ajuste.x, y: ajuste.y };
        if (res.foto) guardado.foto = res.foto;
      }
      if (state.employee && state.employee.id === id) {
        state.employee.ajuste = { z: ajuste.z, x: ajuste.x, y: ajuste.y };
        if (res.foto) state.employee.foto = res.foto;
      }
      pintarAjuste();
      estadoAjuste("Encuadre guardado.");
      setTimeout(function () {
        estadoAjuste("");
      }, 2200);
    } catch (e) {
      estadoAjuste("No se pudo guardar el encuadre. Se conserva solo en esta pantalla.", true);
    }
  }

  /* ---------- Arrastre con el mouse ---------- */

  function iniciarArrastre(e) {
    if (!stateFoto() || arrastre) return;
    const marco = marcoFoto();
    if (!marco) return;

    // Se guarda la escala al empezar: si el panel cambia de tamano mientras se
    // arrastra, el delta se sigue midiendo en la misma unidad.
    arrastre = {
      punteroId: e.pointerId,
      x: ajuste.x,
      y: ajuste.y,
      inicioX: e.clientX,
      inicioY: e.clientY,
      unidad: unidadesPorPx(),
    };

    try {
      marco.setPointerCapture(e.pointerId);
    } catch (err) {}
    e.preventDefault();
    pintarAjuste();
  }

  function moverArrastre(e) {
    if (!arrastre || e.pointerId !== arrastre.punteroId) return;
    setAjuste({
      z: ajuste.z,
      x: arrastre.x + (e.clientX - arrastre.inicioX) * arrastre.unidad,
      y: arrastre.y + (e.clientY - arrastre.inicioY) * arrastre.unidad,
    }, false);
  }

  function terminarArrastre(e) {
    if (!arrastre || (e && e.pointerId !== arrastre.punteroId)) return;
    const marco = marcoFoto();
    if (marco) {
      try {
        marco.releasePointerCapture(arrastre.punteroId);
      } catch (err) {}
    }
    arrastre = null;
    programarGuardado();
  }

  /* ---------- Zoom con la rueda del mouse ---------- */

  function zoomSobreCursor(e) {
    if (!stateFoto()) return;
    const marco = marcoFoto();
    if (!marco) return;

    e.preventDefault();

    const nuevoZ = Math.max(
      FOTO_ZOOM_MIN,
      Math.min(FOTO_ZOOM_MAX, ajuste.z * (e.deltaY < 0 ? 1.12 : 1 / 1.12))
    );
    if (nuevoZ === ajuste.z) return;

    // Centro del area util de la foto. El rect ya viene escalado por la hoja, asi
    // que el centro se mide desde el borde exterior en unidades de diseno y se
    // multiplica por el factor para pasarlo a pixels de pantalla.
    const rect = marco.getBoundingClientRect();
    const factor = factorPantalla();
    const centroX = rect.left + (FOTO_MARCO_BORDE + FOTO_MARCO_ANCHO / 2) * factor;
    const centroY = rect.top + (FOTO_MARCO_BORDE + FOTO_MARCO_ALTO / 2) * factor;

    // Punto del marco bajo el cursor, en unidades de diseno desde el centro.
    const px = (e.clientX - centroX) / factor;
    const py = (e.clientY - centroY) / factor;

    // Se mantiene fijo ese punto: el punto de la foto que estaba ahi es
    // (p - t) / z, y con el nuevo zoom tiene que seguir cayendo en p.
    setAjuste({
      z: nuevoZ,
      x: px - ((px - ajuste.x) / ajuste.z) * nuevoZ,
      y: py - ((py - ajuste.y) / ajuste.z) * nuevoZ,
    }, false);

    clearTimeout(temporizadorZoom);
    temporizadorZoom = setTimeout(programarGuardado, 350);
  }

  /* ---------- Controles ---------- */

  if (fotoZoom) {
    fotoZoom.addEventListener("input", function () {
      setAjuste({ z: parseFloat(fotoZoom.value), x: ajuste.x, y: ajuste.y }, false);
    });
    fotoZoom.addEventListener("change", programarGuardado);
  }

  if (btnFotoCentrar) {
    btnFotoCentrar.addEventListener("click", function () {
      setAjuste({ z: ajuste.z, x: 0, y: 0 }, true);
    });
  }

  if (btnFotoEncuadre) {
    btnFotoEncuadre.addEventListener("click", function () {
      setAjuste({ z: FOTO_ZOOM_MIN, x: 0, y: 0 }, true);
    });
  }

const marcoInicial = marcoFoto();
  if (marcoInicial) {
    marcoInicial.addEventListener("pointerdown", iniciarArrastre);
    marcoInicial.addEventListener("pointermove", moverArrastre);
    marcoInicial.addEventListener("pointerup", terminarArrastre);
    marcoInicial.addEventListener("pointercancel", terminarArrastre);
    marcoInicial.addEventListener("wheel", zoomSobreCursor, { passive: false });
  }

  /* ---------- Empleado / registro ---------- */

  function applyEmployee(emp) {
    state.employee = emp || null;
    state.rec = null;
    if (emp) {
      employeeSearch.value = emp.nombre + (emp.cargo ? " — " + emp.cargo : "");
    }
    clearQr();
    showHint(null);
    btnGenerarText.textContent = "Generar credencial";
    renderFotoDisplay();
    renderPreviews();
    mostrarEditorFoto();
    cargarAjusteEmpleado();
  }

  function applyRecord(rec) {
    state.rec = rec || null;
    drawQr(rec.url);
    showHint(rec);
    btnGenerarText.textContent = rec ? "Regenerar credencial" : "Generar credencial";
    renderFotoDisplay();
    renderPreviews();
    mostrarEditorFoto();
  }

  function currentEmployeeId() {
    const id = parseInt(employeeSelect.value, 10);
    if (!id) return null;
    const emp = EMPLOYEES.find(function (e) { return e.id === id; });
    return emp || null;
  }

  async function apiFetch(url, options) {
    const opts = options || {};
    opts.headers = Object.assign({}, opts.headers || {}, {
      "X-CSRF-TOKEN": csrfToken(),
      "X-Requested-With": "XMLHttpRequest",
    });
    const res = await fetch(url, opts);
    return res.json().catch(function () {
      return { ok: false, error: "Respuesta inválida del servidor" };
    });
  }

  async function generate() {
    const emp = currentEmployeeId();
    if (!emp) {
      showAlert("Seleccione un empleado para generar la credencial.");
      return null;
    }
    showAlert("");

    const payload = {
      employee_id: emp.id,
      plantilla: CFG.plantilla || "frente1",
    };

    const res = await apiFetch(CFG.generateUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    if (!res.ok) {
      showAlert("No se pudo generar la credencial: " + (res.error || "Error desconocido"));
      return null;
    }

    applyRecord(res.data);
    refreshRecords();
    return res.data;
  }

  function ensureGenerated() {
    if (state.rec) return Promise.resolve(state.rec);
    return generate();
  }

  btnGenerar.addEventListener("click", function () {
    generate();
  });

  /* ---------- Selector de empleado con búsqueda ---------- */

  let activeOption = -1;

  function normalizeText(value) {
    return String(value == null ? "" : value)
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");
  }

  function renderEmployeeResults(term) {
    const q = normalizeText(term);
    const matches = EMPLOYEES.filter(function (e) {
      return !q || normalizeText(e.nombre).indexOf(q) !== -1 || normalizeText(e.cargo).indexOf(q) !== -1;
    }).slice(0, 50);

    activeOption = -1;

    if (!matches.length) {
      employeeResults.innerHTML = '<p class="employee-empty">Sin resultados para "' + escapeHtml(term) + '".</p>';
    } else {
      employeeResults.innerHTML = matches.map(function (e) {
        return (
          '<button type="button" class="employee-option" role="option" data-id="' + escapeHtml(e.id) + '">' +
          '<span class="employee-option__name">' + escapeHtml(e.nombre) + "</span>" +
          '<span class="employee-option__cargo">' + escapeHtml(e.cargo || "Sin cargo") + "</span>" +
          "</button>"
        );
      }).join("");
    }

    employeeResults.hidden = false;
    employeeSearch.setAttribute("aria-expanded", "true");
  }

  function hideEmployeeResults() {
    employeeResults.hidden = true;
    employeeSearch.setAttribute("aria-expanded", "false");
    activeOption = -1;
  }

  function selectEmployee(emp) {
    if (!emp) return;
    employeeSelect.value = String(emp.id);
    employeeSearch.value = emp.nombre + (emp.cargo ? " — " + emp.cargo : "");
    hideEmployeeResults();
    applyEmployee(emp);
  }

  employeeSearch.addEventListener("input", function () {
    employeeSelect.value = "";
    renderEmployeeResults(employeeSearch.value);
  });

  employeeSearch.addEventListener("focus", function () {
    if (!employeeSelect.value) renderEmployeeResults(employeeSearch.value);
  });

  employeeSearch.addEventListener("keydown", function (e) {
    const options = employeeResults.querySelectorAll(".employee-option");

    if (employeeResults.hidden || !options.length) {
      if (e.key === "ArrowDown") renderEmployeeResults(employeeSearch.value);
      return;
    }

    if (e.key === "ArrowDown") {
      e.preventDefault();
      activeOption = Math.min(activeOption + 1, options.length - 1);
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      activeOption = Math.max(activeOption - 1, 0);
    } else if (e.key === "Enter") {
      e.preventDefault();
      const opt = options[activeOption >= 0 ? activeOption : 0];
      if (opt) {
        selectEmployee(EMPLOYEES.find(function (x) { return String(x.id) === opt.getAttribute("data-id"); }));
      }
      return;
    } else if (e.key === "Escape") {
      hideEmployeeResults();
      return;
    } else {
      return;
    }

    options.forEach(function (o, i) { o.classList.toggle("is-active", i === activeOption); });
    if (options[activeOption]) options[activeOption].scrollIntoView({ block: "nearest" });
  });

  employeeResults.addEventListener("click", function (e) {
    const opt = e.target.closest(".employee-option");
    if (!opt) return;
    selectEmployee(EMPLOYEES.find(function (x) { return String(x.id) === opt.getAttribute("data-id"); }));
  });

  document.addEventListener("click", function (e) {
    const picker = document.getElementById("employee-picker");
    if (picker && !picker.contains(e.target)) hideEmployeeResults();
  });

  /* ---------- Registros ---------- */

  function escapeHtml(value) {
    return String(value == null ? "" : value).replace(/[&<>"']/g, function (ch) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
      }[ch];
    });
  }

  function renderRecords(records) {
    const list = records || [];
    searchCount.textContent = list.length + " credencial" + (list.length === 1 ? "" : "es");

    if (!list.length) {
      searchResults.innerHTML =
        '<p class="search-empty">No se encontraron registros. Seleccione un empleado y presione "Generar credencial".</p>';
      return;
    }

    searchResults.innerHTML = list.map(function (r) {
      const thumb = r.foto
        ? '<img src="' + escapeHtml(r.foto) + '" alt="">'
        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>';
      return (
        '<article class="record">' +
        '<div class="record__thumb">' + thumb + "</div>" +
        '<div class="record__info">' +
        '<p class="record__name">' + escapeHtml(r.nombre) + "</p>" +
        '<p class="record__cargo" title="' + escapeHtml(r.cargo) + '">' + escapeHtml(r.cargo) + "</p>" +
        '<p class="record__meta">' + escapeHtml(r.fecha) + " · " + escapeHtml(r.hash) + "</p>" +
        "</div>" +
        '<div class="record__actions">' +
        '<button type="button" class="btn btn--ghost btn--sm" data-action="edit" data-id="' + escapeHtml(r.id) + '">Editar</button>' +
        '<button type="button" class="btn btn--danger btn--sm" data-action="delete" data-id="' + escapeHtml(r.id) + '">Eliminar</button>' +
        "</div>" +
        "</article>"
      );
    }).join("");
  }

  function refreshRecords() {
    apiFetch(CFG.dataUrl + "?q=" + encodeURIComponent(searchInput.value.trim()))
      .then(function (res) {
        renderRecords(res.ok ? res.data : []);
      })
      .catch(function () {
        renderRecords([]);
      });
  }

  let searchTimer = null;
  searchInput.addEventListener("input", function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(refreshRecords, 250);
  });

  /* ---------- Impresion ---------- */

  // Las fuentes web deben estar cargadas antes de medir el texto de la maqueta y
  // de clonar la vista previa para imprimir, o el ajuste de linea sale con la
  // tipografia de respaldo.
  function ensureFonts() {
    if (!document.fonts || !document.fonts.load) return Promise.resolve();
    return Promise.all([
      document.fonts.load('110px "Yellowtail"').catch(function () {}),
      document.fonts.load('46px "Glacial Indifference"').catch(function () {}),
      document.fonts.load('700 46px "Glacial Indifference"').catch(function () {}),
      document.fonts.load("700 44px Arial").catch(function () {}),
      document.fonts.load("700 42px Arial").catch(function () {}),
    ]);
  }

  // La maqueta comparte proporción con la hoja: 1024 x 1631 = 0.628 y
  // 54 / 86 = 0.628. Con la proporción anterior (1024 x 1536 = 0.667) mandaba la
  // escala del ancho y la credencial salia de 54 x 81 mm, con 2.5 mm de blanco
  // arriba y abajo: el alto de la tarjeta quedaba sin usar. Ahora la maqueta
  // llena los 86 mm. Antes de esto se escalaba hasta cubrir los 86 mm de alto, la
  // tarjeta quedaba en 57.3 mm de ancho y se recortaban 1.67 mm por lado. Ese
  // recorte ampliaba un 6% todo el diseño y empujaba el pie del GAMLL contra el
  // borde izquierdo del papel, de forma que la impresion no se parecia a la
  // vista previa. Ninguna de las dos formas de recorte se usa ahora.
  const TICKET_MM = { ancho: 54, alto: 86 };
  const PX_A_MM = 25.4 / 96;

  // Imprime el HTML real de la vista previa (no un repintado en canvas) para que
  // el papel sea idéntico a la pantalla. Chrome y Edge agregan fecha, hora,
  // título y URL que desde CSS no se pueden quitar: hay que desmarcar
  function imprimirCredencial(ventana, lado) {
    const reverso = previewSheet ? previewSheet.querySelector(".cred-page--back") : null;
    if (!previewFront || !reverso) return false;

    const escala = Math.min(
      TICKET_MM.ancho / (DISENO_W * PX_A_MM),
      TICKET_MM.alto / (DISENO_H * PX_A_MM)
    );

    const anchoD = DISENO_W * PX_A_MM * escala;
    const altoD = DISENO_H * PX_A_MM * escala;

    ventana.document.open();
    ventana.document.write(
      '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8">' +
        "<title>Credencial GAMLL</title>" +
        '<link rel="stylesheet" href="' + (CFG.cssUrl || "") + '">' +
        "<style>" +
        // FORZAR TAMAÑO Y MÁRGENES CERO: para que el papel 54x86mm calce perfecto
        "@page { margin: 0; size: 54mm 86mm; }" +
        "html, body { margin: 0; padding: 0; background: transparent; width: 54mm; height: 86mm; }" +
        "*, *::before, *::after { -webkit-print-color-adjust: exact; print-color-adjust: exact; }" +
        // hoja en mm explicitos. El 99vh causaba que se cortara en pantallas pequeñas
        ".hoja { position: relative; width: 54mm; height: 86mm; overflow: hidden; break-inside: avoid; page-break-inside: avoid; page-break-after: auto; break-after: auto; }" +
        ".hoja + .hoja { break-before: page; page-break-before: always; }" +
        // La tarjeta se dibuja con transform, no con zoom. Es lo que permite que
        // las barras finas conserven su grosor real en el PDF: con zoom Chrome
        // acota cualquier elemento al 1 px CSS minimo (0.265 mm en una hoja de
        // 54 mm) y una barra de 3 px sale de 5. Con transform el dibujo es
        // vectorial y sale exacta.
        // El clon conserva 1024 x 1631 px de layout, asi que la caja va anclada
        // en absolute y con contain: strict. El contain es lo que evita que
        // Chrome fragmente la hoja en 4 paginas: sin el, el clon se desborda y
        // se parte. El tamano de .marco va en mm por CSS, asi que el contain
        // size no lo altera.
        ".marco { position: absolute; contain: strict; }" +
        ".marco > .cred-page { position: absolute; top: 0; left: 0; transform-origin: top left; }" +
        "</style></head><body class=\"solo-credencial\">" +
        '<div id="cred-app"><div id="hojas"></div></div>' +
        "</body></html>"
    );
    ventana.document.close();

    const hojas = ventana.document.getElementById("hojas");
    const paginas = [];
    if (lado === 'front' || lado === 'ambos') paginas.push(previewFront);
    if (lado === 'back' || lado === 'ambos') paginas.push(reverso);

    paginas.forEach(function (el) {
      const clon = el.cloneNode(true);
      // Las imagenes se copian tal cual, con rutas relativas. En la ventana de
      // impresion se resuelven contra su propia base, asi que se pasan a
      // absolutas: si no, un logo que no carga desaparece en silencio por el
      // onerror del markup y el papel sale sin escudo sin avisar.
      Array.prototype.forEach.call(clon.querySelectorAll("img[src]"), function (im) {
        try {
          const bruto = im.getAttribute("src");
          if (bruto && bruto.indexOf("data:") !== 0) im.setAttribute("src", new URL(bruto, document.baseURI).href);
        } catch (e) {}
      });
      // transform: scale y no zoom. Es lo que deja las barras finas con su
      // grosor real en el PDF; con zoom Chrome las redondea al 1 px CSS minimo.
      // El clon se ancla arriba a la izquierda y la caja la lleva .marco, que ya
      // tiene el tamano en mm, para que el clon no empuje la hoja.
      clon.style.transform = "scale(" + escala.toFixed(6) + ")";
      clon.style.boxShadow = "none";
      // La marca lleva los ajustes que solo aplican al papel (foto subida, pie
      // en negrita) desde el CSS, para que no se dupliquen en el JS.
      clon.classList.add("impresion-medida");
      const marco = ventana.document.createElement("div");
      marco.className = "marco";
      marco.style.width = anchoD.toFixed(4) + "mm";
      marco.style.height = altoD.toFixed(4) + "mm";
      marco.style.left = "0mm";
      marco.style.top = "0mm";
      marco.appendChild(clon);
      const hoja = ventana.document.createElement("div");
      hoja.className = "hoja";
      hoja.appendChild(marco);
      hojas.appendChild(hoja);
    });

    return true;
  }

  // Una imagen que se ve pero no cargo se queda invisible: el markup trae
  // onerror="this.hidden=true" para tolerar logos rotos en pantalla, y ese mismo
  // onerror borra el escudo del papel sin dejar rastro. Se listan aqui para
  // poder avisar en vez de entregar una credencial incompleta en silencio.
  function imagenesSinCargar(ventana) {
    var out = [];
    var imgs = ventana.document.images;
    for (var i = 0; i < imgs.length; i++) {
      var im = imgs[i];
      if (im.hidden) continue;
      if (!im.getClientRects().length) continue;
      if (im.naturalWidth !== 0) continue;
      out.push((im.className || im.id || "imagen") + " -> " + (im.currentSrc || im.src || "(sin src)"));
    }
    return out;
  }

  // Espera a que el clon este maquetado, con las hojas de estilo aplicadas, las
  // fuentes cargadas y las imagenes decodificadas: si se imprime antes, la
  // credencial sale sin estilo o con la tipografia de respaldo.
  function imprimirCuandoEsteListo(ventana) {
    var listo = false;
    var limite = setTimeout(imprimir, 5000);
    var reloj = setInterval(revisar, 100);

    function imprimir() {
      if (listo) return;
      listo = true;
      clearTimeout(limite);
      clearInterval(reloj);

      var fallidas = imagenesSinCargar(ventana);
      if (fallidas.length) {
        try {
          ventana.alert(
            "La credencial se va a imprimir sin " + fallidas.length +
            " imagen(es):\n\n" + fallidas.join("\n") +
            "\n\nRevisa que existan en public/client/images/credencial/ y que el servidor las entregue."
          );
        } catch (e) {}
      }

      try {
        // En lugar de escuchar afterprint desde el padre y cerrar de inmediato, 
        // inyectamos el script en la ventana hija para que gestione el cierre.
        // A veces window.print() desde el padre causa que el dialogo se cierre rapido.
        const script = ventana.document.createElement("script");
        script.textContent = `
          window.focus();
          // Esperamos un momento para asegurar el renderizado
          setTimeout(function() {
            try {
              window.print();
            } catch(e) {
              console.error("Error al imprimir", e);
            }
          }, 200);
        `;
        ventana.document.body.appendChild(script);
      } catch (e) {
        console.error("Error al inyectar script de impresión", e);
      }
    }

    function revisar() {
      if (ventana.closed) {
        clearInterval(reloj);
        clearTimeout(limite);
        return;
      }
      if (ventana.document.readyState !== "complete") return;
      var fuentes = ventana.document.fonts;
      // En algunos navegadores fonts.status se queda "loading"
      if (fuentes && fuentes.status === "loading") return;
      var imgs = ventana.document.images;
      for (var i = 0; i < imgs.length; i++) {
        if (!imgs[i].complete) return;
      }
      imprimir();
    }
  }

  /* ---------- Acciones de los registros ---------- */

  async function loadRecord(id) {
    const res = await apiFetch(CFG.dataUrl + "?id=" + encodeURIComponent(id));
    return res.ok && res.data ? res.data : null;
  }

  searchResults.addEventListener("click", async function (e) {
    const btn = e.target.closest("button[data-action]");
    if (!btn) return;

    const id = btn.getAttribute("data-id");
    const action = btn.getAttribute("data-action");
    const rec = await loadRecord(id);
    if (!rec) return;

    if (action === "edit") {
      const emp = EMPLOYEES.find(function (x) { return x.id === rec.employee_id; });
      if (emp) {
        employeeSelect.value = String(emp.id);
        applyEmployee(emp);
      }
      applyRecord(rec);
      window.scrollTo({ top: 0, behavior: "smooth" });
    } else if (action === "delete") {
      if (!confirm("¿Seguro que deseas eliminar la credencial emitida?\nEsta acción no se puede deshacer.")) return;
      const res = await apiFetch(CFG.deleteUrl.replace("__id__", encodeURIComponent(id)), { method: "DELETE" });
      if (res.ok) {
        refreshRecords();
        if (state.rec && String(state.rec.id) === String(id)) applyEmployee(state.employee);
      } else {
        alert("No se pudo eliminar la credencial.");
      }
    }
  });

  fotoInput.addEventListener("change", function () {
    const file = fotoInput.files && fotoInput.files[0];
    if (!file || !state.employee) return;

    // Se pesa antes de subir. Si el archivo pasa, no se toca nada mas: la foto
    // anterior sigue puesta y el campo se vacia para que al elegir el mismo
    // archivo otra vez se dispare el evento.
    if (file.size > FOTO_MAX_BYTES) {
      showAlert(
        "La foto pesa " + fotoEnMb(file.size) + " y el máximo es " +
          FOTO_MAX_MB + " MB. Se puede subir una versión más liviana.",
        "aviso"
      );
      fotoInput.value = "";
      return;
    }

    showAlert("");
    const data = { appendTo: function (fd) { fd.append("photo", file); } };

    subirFoto(data)
      .then(function (res) {
        if (res.ok) {
          setEmployeeFotoUrl(res.foto);
          // La foto nueva viene sin encuadre: se vuelve al ajuste de siempre
          // para que no quede el recorte de la imagen anterior encima.
          setAjuste({ z: FOTO_ZOOM_MIN, x: 0, y: 0 }, false);
          renderPreviews();
          renderFotoDisplay();
          mostrarEditorFoto();
          refreshRecords();
        } else {
          const detalle = (res.errors && res.errors.photo && res.errors.photo[0]) || res.error || res.message;
          showAlert(detalle ? "No se pudo cambiar la foto: " + detalle : "No se pudo cambiar la foto.");
        }
      })
      .catch(function () {
        showAlert("No se pudo cambiar la foto.");
      });

    fotoInput.value = "";
  });

  /* ---------- Descarga del frontal en imagen ----------

     La imagen se arma con el mismo HTML y el mismo CSS del papel, con los
     mismos ajustes de impresion (impresion-medida), para que lo que se descarga
     sea identico a lo que sale impreso. El dibujado va por SVG con
     foreignObject: el clon se mete en un SVG y ese SVG se pinta en un canvas.
     A diferencia de la ventana de impresion, aqui no hay red dentro del SVG,
     asi que el CSS y cada imagen viajan como data URI; si se dejara una
     referencia suelta, el escudo o el logo saldrian en blanco sin avisar.

     No se usa un repintado en canvas: eso obliga a redibujar el texto a mano y
     siempre difiere de la pantalla en tipografia, logo y tamaño del titular,
     que es justo lo que hay que evitar en una credencial. */

  const btnDescargar = document.getElementById("btn-descargar-img");
  const btnDescargarText = document.getElementById("btn-descargar-img-text");

  const EXPORT_ANCHO = DISENO_W;
  const EXPORT_ALTO = DISENO_H;
  // 2x: 2048 x 3262 px. Una tarjeta de 54 mm impresa a 600 ppp necesita unos
  // 1276 px de ancho, asi que a esta resolucion todavia se puede reimprimir la
  // descarga en una impresora de enroladora sin pixelarse.
  const EXPORT_ESCALA = 4;

  let cssIncrustado = null;

  function blobADataUri(blob) {
    return new Promise(function (res, rej) {
      const fr = new FileReader();
      fr.onload = function () {
        res(fr.result);
      };
      fr.onerror = function () {
        rej(new Error("No se pudo leer un recurso"));
      };
      fr.readAsDataURL(blob);
    });
  }

  // Trae un recurso como data URI. Las imagenes se reducen al tamano en que
  // quedan en la credencial: logo.png pesa 2 MB y en el diseno ocupa 178 x 196,
  // asi que mandarlo entero al lienzo solo haria la descarga mas lenta y pesada
  // sin ganar un pixel. maxW/maxH a 0 significan "no reducir".
  async function recursoADataUri(url, maxW, maxH) {
    const res = await fetch(url, { credentials: "same-origin" });
    if (!res.ok) throw new Error("No se pudo leer un recurso de la credencial (" + res.status + ")");
    const blob = await res.blob();

    if ((blob.type || "").indexOf("image/") !== 0) return blobADataUri(blob);

    let bmp = null;
    try {
      bmp = await createImageBitmap(blob);
    } catch (e) {
      bmp = null;
    }
    if (!bmp) return blobADataUri(blob);

    // El bitmap se cierra recien despues de dibujar. Cerrarlo antes lo deja
    // separado: drawImage lanza InvalidStateError y las medidas quedan en 0, asi
    // que ademas de fallar el dibujado se compararian contra cero.
    const naturalW = bmp.width;
    const naturalH = bmp.height;
    const w = maxW > 0 ? Math.min(maxW, naturalW) : naturalW;
    const h = maxH > 0 ? Math.min(maxH, naturalH) : naturalH;

    // Sin reduccion que hacer se devuelve el archivo original: es mas rapido y
    // evita un redibujado que solo perderia calidad.
    if (w === naturalW && h === naturalH) {
      if (bmp.close) bmp.close();
      return blobADataUri(blob);
    }

    try {
      const lienzo = document.createElement("canvas");
      lienzo.width = w;
      lienzo.height = h;
      lienzo.getContext("2d").drawImage(bmp, 0, 0, w, h);
      return lienzo.toDataURL("image/png");
    } finally {
      if (bmp.close) bmp.close();
    }
  }

  // El CSS va entero dentro del SVG, con las url() que encuentra convertidas en
  // data URI: son las @font-face de la credencial y van relativas al propio
  // credencial.css, asi que se resuelven contra el archivo, no contra la pagina.
  async function cssParaSvg() {
    if (cssIncrustado !== null) return cssIncrustado;
    const url = CFG.cssUrl || "";
    if (!url) {
      cssIncrustado = "";
      return cssIncrustado;
    }
    const res = await fetch(url, { credentials: "same-origin" });
    if (!res.ok) throw new Error("No se pudo leer la hoja de estilos (" + res.status + ")");
    let texto = await res.text();

    const base = url.split("?")[0];
    const rutas = texto.match(/url\(\s*['"]?[^'")]+['"]?\s*\)/gi) || [];
    const pendientes = [];
    rutas.forEach(function (u) {
      const ruta = u.replace(/^url\(\s*['"]?/i, "").replace(/['"]?\s*\)$/, "").trim();
      if (!ruta) return;
      if (ruta.indexOf("data:") === 0) return;
      if (/^(https?:)?\/\//i.test(ruta)) return;
      if (pendientes.indexOf(ruta) !== -1) return;
      pendientes.push(ruta);
    });

    for (const ruta of pendientes) {
      try {
        const abs = new URL(ruta, new URL(base, document.baseURI)).href;
        texto = texto.split(ruta).join(await recursoADataUri(abs, 0, 0));
      } catch (e) {}
    }

    cssIncrustado = texto;
    return cssIncrustado;
  }

  // offsetWidth solo existe si el elemento esta en el documento, asi que se mide
  // sobre el frontal vivo y despues se aplica al clon por indice: el clon es una
  // copia profunda, el orden de las imagenes es el mismo.
  function medidasDeImagenes(raiz) {
    return Array.prototype.map.call(raiz.querySelectorAll("img"), function (im) {
      const src = im.getAttribute("src") || "";
      if (!src || src.indexOf("data:") === 0) return null;
      return {
        src: src,
        w: Math.round(im.offsetWidth * EXPORT_ESCALA),
        h: Math.round(im.offsetHeight * EXPORT_ESCALA),
      };
    });
  }

  async function imagenesEnDataUri(clon, medidas) {
    const imgs = clon.querySelectorAll("img");
    await Promise.all(
      Array.prototype.map.call(imgs, async function (im, i) {
        const m = medidas[i];
        if (!m) return;
        try {
          const abs = new URL(m.src, document.baseURI).href;
          
          if (im.classList.contains("cred-photo__img")) {
            const res = await fetch(abs, { credentials: "same-origin" });
            const blob = await res.blob();
            let bmp = null;
            if (window.createImageBitmap) bmp = await createImageBitmap(blob).catch(function(){ return null; });
            
            if (bmp) {
              const outW = FOTO_MARCO_ANCHO * EXPORT_ESCALA;
              const outH = FOTO_MARCO_ALTO * EXPORT_ESCALA;
              const imgRatio = bmp.width / bmp.height;
              const boxRatio = outW / outH;
              
              let drawW, drawH;
              if (imgRatio > boxRatio) {
                drawH = outH;
                drawW = drawH * imgRatio;
              } else {
                drawW = outW;
                drawH = drawW / imgRatio;
              }
              
              const tX = ajuste.x * EXPORT_ESCALA;
              const tY = ajuste.y * EXPORT_ESCALA;
              
              const lienzo = document.createElement("canvas");
              lienzo.width = outW;
              lienzo.height = outH;
              const ctx = lienzo.getContext("2d");
              
              ctx.fillStyle = "#fff";
              ctx.fillRect(0, 0, outW, outH);
              ctx.translate(outW / 2 + tX, outH / 2 + tY);
              ctx.scale(ajuste.z, ajuste.z);
              // Dibujar centrado imitando object-fit: cover
              ctx.drawImage(bmp, -drawW / 2, -drawH / 2, drawW, drawH);
              
              im.setAttribute("src", lienzo.toDataURL("image/jpeg", 0.95));
              // Eliminar la distorsión del SVG anulando el transform
              im.style.transform = "none";
              im.style.objectFit = "fill";
              
              if (bmp.close) bmp.close();
            } else {
              im.setAttribute("src", await recursoADataUri(abs, m.w, m.h));
            }
          } else {
            im.setAttribute("src", await recursoADataUri(abs, m.w, m.h));
          }
        } catch (e) {}
      })
    );
  }

  async function frontalEnLienzo() {
    if (!previewFront) throw new Error("No se encontró la credencial en la vista previa.");

    const medidas = medidasDeImagenes(previewFront);

    // El contenedor lleva el tamano del diseño en px: .cred-page usa
    // font-size: 1px, asi que 1em es 1 px del diseño y todas sus medidas estan
    // en em. Sin este ancho fijo el clon no maquetaria igual que en pantalla.
    const caja = document.createElement("div");
    caja.setAttribute(
      "style",
      "width:" + EXPORT_ANCHO + "px;height:" + EXPORT_ALTO + "px;margin:0;padding:0;overflow:hidden;background:#fff;"
    );

    const estilo = document.createElement("style");
    estilo.textContent = await cssParaSvg();
    caja.appendChild(estilo);

    const clon = previewFront.cloneNode(true);
    clon.classList.add("impresion-medida");
    clon.style.margin = "0";
    clon.style.transform = "none";
    clon.style.boxShadow = "none";
    await imagenesEnDataUri(clon, medidas);
    caja.appendChild(clon);

    // XMLSerializer, no outerHTML: el markup viene de HTML y outerHTML deja los
    // <img> sin cerrar, que el SVG no parsea. El serializador escribe XML y el
    // clon queda bien formado dentro del foreignObject.
    const xml = new XMLSerializer().serializeToString(caja);

    const anchoPx = EXPORT_ANCHO * EXPORT_ESCALA;
    const altoPx = EXPORT_ALTO * EXPORT_ESCALA;
    // El viewBox no es cosmetico: sin el, el SVG no escala nada y el
    // foreignObject de EXPORT_ANCHO x EXPORT_ALTO se dibuja 1:1 dentro del
    // lienzo de EXPORT_ANCHO*2, o sea que la credencial saldia pegada en la
    // esquina superior izquierda y a la mitad de resolucion. Con el viewBox el
    // sistema de coordenadas del SVG es el del diseño y el viewport lo escala a
    // EXPORT_ESCALA, que es justo lo que mide medidasDeImagenes para incrustar
    // las imágenes a su tamaño real en el PNG final.
    const svg =
      '<svg xmlns="http://www.w3.org/2000/svg" width="' +
      anchoPx +
      '" height="' +
      altoPx +
      '" viewBox="0 0 ' +
      EXPORT_ANCHO +
      " " +
      EXPORT_ALTO +
      '">' +
      '<foreignObject x="0" y="0" width="' + EXPORT_ANCHO + '" height="' + EXPORT_ALTO + '">' +
      xml +
      "</foreignObject></svg>";

    const img = new Image();
    img.src = "data:image/svg+xml;charset=utf-8," + encodeURIComponent(svg);
    try {
      if (img.decode) {
        await img.decode();
      } else {
        await new Promise(function (res, rej) {
          img.onload = res;
          img.onerror = function () {
            rej(new Error("El navegador no pudo dibujar la credencial"));
          };
        });
      }
    } catch (e) {
      throw new Error("El navegador no pudo dibujar la credencial con estos recursos.");
    }

    const lienzo = document.createElement("canvas");
    lienzo.width = anchoPx;
    lienzo.height = altoPx;
    const ctx = lienzo.getContext("2d");
    ctx.fillStyle = "#fff";
    ctx.fillRect(0, 0, anchoPx, altoPx);
    ctx.drawImage(img, 0, 0, anchoPx, altoPx);
    return lienzo;
  }

  function nombreDeArchivo() {
    const base = normalizeText(stateNombre())
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-+|-+$/g, "");
    return base ? "credencial-" + base + ".png" : "credenencial.png";
  }

  async function descargarFrontal() {
    if (btnDescargar) btnDescargar.disabled = true;
    const textoAntes = btnDescargarText ? btnDescargarText.textContent : "";
    if (btnDescargarText) btnDescargarText.textContent = "Preparando…";
    showAlert("");

    try {
      const rec = await ensureGenerated();
      if (!rec) return;

      // El QR se resuelve de forma asincrona. Sin esperarlo, una descarga
      //rapida se llevaria el frontal sin el.
      await drawQr(rec.url);

      const lienzo = await frontalEnLienzo();

      const blob = await new Promise(function (res, rej) {
        try {
          lienzo.toBlob(function (b) {
            if (b) res(b);
            else rej(new Error("vacio"));
          }, "image/png");
        } catch (e) {
          rej(e);
        }
      }).catch(function () {
        // Chrome marca el lienzo como "no limpio" si algo quedo sirviendo el SVG
        // desde fuera; con todo incrustado no deberia pasar, pero si el motor lo
        // hace el mensaje tiene que decirlo en vez de fallar en silencio.
        throw new Error(
          "El navegador no permitió generar la imagen. Prueba con Chrome o Edge actualizados."
        );
      });

      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = nombreDeArchivo();
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      setTimeout(function () {
        URL.revokeObjectURL(url);
      }, 4000);

      showAlert("Se descargó el frontal en PNG (" + EXPORT_ANCHO * EXPORT_ESCALA + " × " + EXPORT_ALTO * EXPORT_ESCALA + " px).");
    } catch (e) {
      showAlert(e && e.message ? e.message : "No se pudo descargar el frontal.");
    } finally {
      if (btnDescargar) btnDescargar.disabled = false;
      if (btnDescargarText) btnDescargarText.textContent = textoAntes;
    }
  }

  if (btnDescargar) {
    btnDescargar.addEventListener("click", function () {
      descargarFrontal();
    });
  }

  /* ---------- Botones principales ---------- */

  // Imprime en una ventana nueva con el HTML real de la vista previa: es la
  // unica forma de que el papel salga identico a la pantalla. Un PDF generado
  // con canvas obliga a redibujar el texto a mano y siempre difiere
  // (tipografia, logo, tamanho de titular), por eso no hay opcion de descarga.
  function abrirCredencial(lado) {
    // Se abre en el mismo gesto del clic: despues del render el navegador la
    // bloquearia como ventana emergente.
    const ventana = window.open("", "_blank", "width=760,height=1040");
    if (!ventana) {
      showAlert("El navegador bloqueó la ventana. Permite las ventanas emergentes para este sitio.");
      return;
    }

    ensureGenerated().then(function (r) {
      if (!r) {
        if (!ventana.closed) ventana.close();
        return;
      }
      if (!imprimirCredencial(ventana, lado)) {
        ventana.close();
        showAlert("No se encontró la credencial en la vista previa.");
        return;
      }
      imprimirCuandoEsteListo(ventana);
    });
  }

  if (btnPrintFront) btnPrintFront.addEventListener("click", function () { abrirCredencial('front'); });
  if (btnPrintBack) btnPrintBack.addEventListener("click", function () { abrirCredencial('back'); });

  btnReset.addEventListener("click", function () {
    employeeSelect.value = "";
    employeeSearch.value = "";
    hideEmployeeResults();
    applyEmployee(null);
    searchInput.value = "";
    refreshRecords();
  });

  /* ---------- Init ---------- */

  applyEmployee(null);
  fitTitles();
  fitPreview();
  refreshRecords();
  ensureFonts().then(function () {
    fitTitles();
    fitPreview();
  });
  window.addEventListener("resize", fitPreview);
  // El panel puede cambiar de tamano sin que cambie la ventana (por ejemplo si
  // se pliega la barra lateral), asi que tambien se observa al propio panel.
  if (window.ResizeObserver && previewStage) {
    new ResizeObserver(fitPreview).observe(previewStage);
  }
  window.addEventListener("load", function () {
    fitTitles();
    fitPreview();
  });
})();
