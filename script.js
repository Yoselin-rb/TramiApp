/* pantallaa de bienvenida */
if(window.location.pathname.includes("index.html")){
    setTimeout(() => {
        window.location.href = ("registro.php");
    }, 3000);
}


/* ---------- Tamaño de letra (aplica en toda la app) ---------- */

function getCookie(nombre) {
    const match = document.cookie.match(new RegExp('(?:^|; )' + nombre + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
}

function setCookie(nombre, valor, dias) {
    const fecha = new Date();
    fecha.setTime(fecha.getTime() + dias * 24 * 60 * 60 * 1000);
    document.cookie = `${nombre}=${encodeURIComponent(valor)}; expires=${fecha.toUTCString()}; path=/`;
}

// Convierte el nivel del slider (1, 2 o 3) en el multiplicador real de tamaño
function escalaDesdeNivel(nivel) {
    switch (parseInt(nivel, 10)) {
        case 1: return 0.85;  // Pequeño
        case 3: return 1.15;  // Grande
        default: return 1;    // Mediano
    }
}

// Aplica la escala a TODA la página actual (afecta cualquier CSS que use var(--font-scale))
function aplicarTamanoLetra(nivel) {
    document.documentElement.style.setProperty('--font-scale', escalaDesdeNivel(nivel));
}

// Actualiza únicamente el recuadro de "vista previa" dentro de la pantalla de ajustes
function actualizarVistaPrevia(nivel) {
    const preview = document.getElementById('texto-vista-previa');
    if (!preview) return;
    preview.style.fontSize = `${16 * escalaDesdeNivel(nivel)}px`;
}

// Envía el nuevo tamaño al servidor: si hay sesión iniciada, se guarda en la cuenta
// del usuario (queda para siempre, en cualquier dispositivo); si no, solo queda
// guardado en este navegador mediante la cookie.
function guardarTamanoLetra() {
    const slider = document.getElementById('slider-tamano');
    if (!slider) return;
    const nivel = slider.value;
    const boton = document.getElementById('btn-guardar-letra');

    if (boton) {
        boton.textContent = 'Guardando...';
    }

    fetch('guardar_tamano_letra.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ tamano: nivel })
    })
        .then(res => res.json())
        .then(data => {
            if (data && data.ok) {
                setCookie('tamano_letra', nivel, 365);
                window.location.href = 'configuracion.php';
            } else {
                alert('No se pudo guardar el tamaño de letra. Intentá nuevamente.');
                if (boton) boton.textContent = 'Guardar cambios';
            }
        })
        .catch(() => {
            alert('No se pudo guardar el tamaño de letra. Intentá nuevamente.');
            if (boton) boton.textContent = 'Guardar cambios';
        });
}

// Al cargar cualquier página del sitio, aplicamos el tamaño de letra guardado
document.addEventListener('DOMContentLoaded', () => {
    const nivelGuardado = getCookie('tamano_letra') || '2';
    aplicarTamanoLetra(nivelGuardado);

    // Si estamos en la pantalla de ajuste de tamaño de letra, conectamos el slider
    const slider = document.getElementById('slider-tamano');
    if (slider) {
        slider.value = nivelGuardado;
        actualizarVistaPrevia(nivelGuardado);

        slider.addEventListener('input', () => {
            aplicarTamanoLetra(slider.value);      // vista previa en vivo de toda la app
            actualizarVistaPrevia(slider.value);   // vista previa dentro del recuadro
        });
    }
});



function filtrarTramites() {
    const input = document.getElementById('buscador');

    if (!input) return;
    const filtro = input.value.toLowerCase();
    const contenedor = document.getElementById('lista-tramites');
    const tarjetas = contenedor.getElementsByClassName('card-tramite');

    // Recorre todas las tarjetas y oculta las que no coincidan con la búsqueda
    for (let i = 0; i < tarjetas.length; i++) {
        let textoTarjeta =
        tarjetas[i]
        .getElementsByTagName('span')[0]
        .innerText;

        if (textoTarjeta.toLowerCase().includes(filtro)) {
            tarjetas[i].style.display = ""; // Muestra la tarjeta
        } else {
            tarjetas[i].style.display = "none"; // Oculta la tarjeta
        }
    }
}


function togglePassword(inputId, icono) {
    const input = document.getElementById(inputId);
    if (!input) return;
 
    const svg = icono.querySelector('svg');
 
    if (input.type === "password") {
        input.type = "text";
        // Ícono de "ojo tachado" (contraseña visible)
        svg.innerHTML = `
            <path d="M1 12C1 12 5 5 12 5C19 5 23 12 23 12C23 12 19 19 12 19C5 19 1 12 1 12Z" stroke="#666666" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="3" stroke="#666666" stroke-width="1.6"/>
            <line x1="3" y1="3" x2="21" y2="21" stroke="#666666" stroke-width="1.6" stroke-linecap="round"/>
        `;
    } else {
        input.type = "password";
        // Ícono de ojo abierto (contraseña oculta)
        svg.innerHTML = `
            <path d="M1 12C1 12 5 5 12 5C19 5 23 12 23 12C23 12 19 19 12 19C5 19 1 12 1 12Z" stroke="#666666" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="3" stroke="#666666" stroke-width="1.6"/>
        `;
    }
}


// Al cargar la página, si hay un botón de favorito, consultamos si el
// trámite ya está marcado como favorito por el usuario logueado
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-favorito');
    if (!btn) return;

    const tramiteId = btn.dataset.tramiteId;
    if (!tramiteId) return;

    fetch(`favorito.php?tramite_id=${encodeURIComponent(tramiteId)}`)
        .then(res => res.json())
        .then(data => {
            if (data.favorito) {
                btn.innerText = '★';
            }
        })
        .catch(() => {
            // Si falla la consulta (ej: no hay sesión), dejamos la estrella vacía
        });
});

function alternarFavorito() {
    const btn = document.getElementById('btn-favorito');
    if (!btn) return;

    const tramiteId = btn.dataset.tramiteId;
    if (!tramiteId) return;

    fetch('favorito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ tramite_id: tramiteId })
    })
        .then(res => {
            if (res.status === 401) {
                // El usuario no tiene sesión iniciada
                alert('Iniciá sesión para guardar trámites como favoritos.');
                return null;
            }
            return res.json();
        })
        .then(data => {
            if (!data) return;
            // Actualizamos la estrella según lo que confirmó el servidor
            btn.innerText = data.favorito ? '★' : '☆';
        })
        .catch(() => {
            alert('No se pudo actualizar el favorito. Intentá nuevamente.');
        });
}


/* ---------- Test de trámite ("Evaluación rápida") ---------- */

// Banco de preguntas por trámite. Para agregar el test de un nuevo trámite,
// alcanza con sumar una entrada nueva acá con su tramite_id como clave.
const MAPA_TESTS = {
    'ute-factura': [
        {
            texto: '¿Qué documento principal necesitás para el trámite de factura de UTE?',
            opciones: [
                'N° de Cuenta de UTE (está en la factura)',
                'Cédula de identidad',
                'Partida de nacimiento',
                'Comprobante de domicilio'
            ],
            correcta: 0
        },
        {
            texto: '¿Dónde podés realizar el trámite de UTE?',
            opciones: [
                'Únicamente en las oficinas de UTE',
                'Solo se puede hacer por teléfono',
                'A través de la web/App de UTE o aplicaciones de pago habilitadas',
                'No se puede pagar en Guichón'
            ],
            correcta: 2
        },
        {
            texto: '¿Qué medio de pago podés utilizar para realizar el pago en línea?',
            opciones: [
                'Solo efectivo',
                'Tarjeta de débito o crédito',
                'Cheque',
                'Giro postal'
            ],
            correcta: 1
        },
        {
            texto: 'Pagaste la factura. ¿Qué es lo más recomendable hacer?',
            opciones: [
                'Borrar el comprobante enseguida',
                'Llamar a UTE para avisar',
                'Guardar el comprobante de pago',
                'No hace falta guardar nada'
            ],
            correcta: 2
        }
    ]
};

// Estado del test que se está rindiendo actualmente
let testEstado = {
    tramiteId: '',
    preguntas: [],
    actual: 0,
    respuestas: []
};

// Se llama desde el botón "Comenzar test" (onclick="comenzarTest(this)")
function comenzarTest(boton) {
    const tramiteId = boton.dataset.tramiteId;
    const preguntas = MAPA_TESTS[tramiteId];
    if (!preguntas) return;

    testEstado = {
        tramiteId: tramiteId,
        preguntas: preguntas,
        actual: 0,
        respuestas: new Array(preguntas.length).fill(null)
    };

    document.getElementById('test-intro').classList.add('oculto');
    document.getElementById('test-quiz').classList.remove('oculto');
    document.getElementById('test-resultado').classList.add('oculto');

    renderPreguntaTest();
}

function renderPreguntaTest() {
    const { preguntas, actual, respuestas } = testEstado;
    const pregunta = preguntas[actual];

    document.getElementById('test-contador').textContent = `Pregunta ${actual + 1} de ${preguntas.length}`;

    // Barra de progreso: un segmento por pregunta, se completan hasta la actual
    const barra = document.getElementById('test-barra-progreso');
    barra.innerHTML = '';
    preguntas.forEach((_, i) => {
        const segmento = document.createElement('div');
        segmento.className = 'test-progress-segment' + (i <= actual ? ' completado' : '');
        barra.appendChild(segmento);
    });

    document.getElementById('test-texto-pregunta').textContent = pregunta.texto;

    // Opciones de respuesta
    const contenedorOpciones = document.getElementById('test-opciones');
    contenedorOpciones.innerHTML = '';
    pregunta.opciones.forEach((opcion, i) => {
        const div = document.createElement('div');
        div.className = 'test-opcion' + (respuestas[actual] === i ? ' seleccionada' : '');
        div.innerHTML = `<span class="test-opcion-circulo">${respuestas[actual] === i ? '✓' : ''}</span><span>${opcion}</span>`;
        div.addEventListener('click', () => seleccionarOpcionTest(i));
        contenedorOpciones.appendChild(div);
    });

    document.getElementById('btn-test-anterior').disabled = actual === 0;
}

function seleccionarOpcionTest(indice) {
    testEstado.respuestas[testEstado.actual] = indice;
    renderPreguntaTest();
}

function irPreguntaAnteriorTest() {
    if (testEstado.actual === 0) return;
    testEstado.actual -= 1;
    renderPreguntaTest();
}

function irSiguientePreguntaTest() {
    const { actual, preguntas, respuestas } = testEstado;

    if (respuestas[actual] === null) {
        alert('Seleccioná una opción para continuar.');
        return;
    }

    if (actual < preguntas.length - 1) {
        testEstado.actual += 1;
        renderPreguntaTest();
    } else {
        finalizarTest();
    }
}

function finalizarTest() {
    const { preguntas, respuestas, tramiteId } = testEstado;

    let correctas = 0;
    preguntas.forEach((pregunta, i) => {
        if (respuestas[i] === pregunta.correcta) correctas += 1;
    });

    const total = preguntas.length;
    const aprobo = correctas >= Math.ceil(total * 0.75);

    document.getElementById('test-quiz').classList.add('oculto');
    document.getElementById('test-resultado').classList.remove('oculto');

    document.getElementById('test-resultado-icono').textContent = aprobo ? '👍' : '🙁';
    document.getElementById('test-resultado-titulo').textContent = aprobo ? '¡Excelente!' : 'Casi lo lográs';

    const subtitulo = document.getElementById('test-resultado-subtitulo');
    subtitulo.textContent = aprobo ? 'Aprobaste el test' : 'No aprobaste el test';
    subtitulo.className = aprobo ? 'test-subtitulo-aprobado' : 'test-subtitulo-no-aprobado';

    document.getElementById('test-resultado-correctas').textContent = `Respuestas correctas: ${correctas} de ${total}`;

    const filaEstado = document.getElementById('test-resultado-fila-estado');
    filaEstado.style.display = aprobo ? 'flex' : 'none';

    const btnReintentar = document.getElementById('btn-test-reintentar');
    if (btnReintentar) {
        btnReintentar.style.display = aprobo ? 'none' : 'block';
    }

    const avisoSesion = document.getElementById('test-resultado-aviso-sesion');
    if (avisoSesion) {
        avisoSesion.style.display = 'none';
    }

    // Si aprobó, guardamos el avance en la cuenta del usuario (igual que favoritos)
    if (aprobo) {
        fetch('finalizar_tramite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tramite_id: tramiteId, correctas: correctas, total: total })
        })
            .then(res => {
                if (res.status === 401) {
                    // Sin sesión iniciada: mostramos el resultado igual, pero avisamos que no quedó guardado
                    if (avisoSesion) avisoSesion.style.display = 'block';
                    return null;
                }
                return res.json();
            })
            .catch(() => {
                // Si falla el guardado no interrumpimos la experiencia del usuario
            });
    }
}

function reintentarTest() {
    comenzarTest({ dataset: { tramiteId: testEstado.tramiteId } });
}


/* ---------- Página de detalle de trámite (Presencial / En línea) ---------- */

// Cambia qué contenido se muestra (Presencial o En línea) dentro de
// tramite-detalle.php, y marca el botón correspondiente como activo.
function mostrarModalidadTramite(modalidad) {
    document.querySelectorAll('.btn-modalidad').forEach(b => b.classList.remove('active'));
    const boton = document.getElementById('btn-modalidad-' + modalidad);
    if (boton) boton.classList.add('active');

    document.querySelectorAll('.contenido-presencial, .contenido-online').forEach(el => {
        el.classList.add('oculto');
    });
    document.querySelectorAll('.contenido-' + modalidad).forEach(el => {
        el.classList.remove('oculto');
    });
}

// Al cargar tramite-detalle.php, mostramos la modalidad indicada en el
// atributo data-modalidad-inicial del selector (la primera disponible)
document.addEventListener('DOMContentLoaded', () => {
    const selector = document.querySelector('.modalidad-selector');
    if (!selector) return;

    const inicial = selector.dataset.modalidadInicial || 'presencial';
    mostrarModalidadTramite(inicial);
});

/* ---------- Mi actividad (Finalizados / Favoritos) ---------- */

// Cambia entre la pestaña "Finalizados" y "Favoritos" en mi-actividad.php
function mostrarTabActividad(tab) {
    document.querySelectorAll('.tab-actividad').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.panel-actividad').forEach(p => p.classList.remove('active'));

    const tabBtn = document.getElementById('tab-' + tab);
    const panel = document.getElementById('panel-' + tab);
    if (!tabBtn || !panel) return;

    tabBtn.classList.add('active');
    panel.classList.add('active');

    // Guardamos la pestaña elegida en la URL (sin recargar la página) para
    // que un link como "mi-actividad.php?tab=favoritos" abra directo ahí
    if (window.history && window.history.replaceState) {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        window.history.replaceState(null, '', url);
    }
}

// Al entrar a mi-actividad.php, abrimos la pestaña indicada en la URL
// (?tab=favoritos), o "Finalizados" por defecto
document.addEventListener('DOMContentLoaded', () => {
    const tabsActividad = document.querySelector('.tabs-actividad');
    if (!tabsActividad) return;

    const params = new URLSearchParams(window.location.search);
    const tabInicial = params.get('tab') === 'favoritos' ? 'favoritos' : 'finalizados';
    mostrarTabActividad(tabInicial);
});

// Quita un trámite de favoritos directamente desde la lista de "Mi actividad"
function quitarFavoritoActividad(boton, tramiteId) {
    fetch('favorito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ tramite_id: tramiteId })
    })
        .then(res => res.json())
        .then(data => {
            if (!data || data.favorito !== false) return;

            const card = boton.closest('.card-actividad');
            if (card) card.remove();

            // Si ya no queda ningún favorito, mostramos el mensaje de vacío
            const panel = document.getElementById('panel-favoritos');
            if (panel && !panel.querySelector('.card-actividad')) {
                panel.innerHTML = '<div class="estado-vacio-actividad"><span>☆</span>Todavía no marcaste trámites como favoritos.</div>';
            }
        })
        .catch(() => {
            alert('No se pudo quitar el favorito. Intentá nuevamente.');
        });
}