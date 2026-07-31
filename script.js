/* pantallaa de bienvenida */
if(window.location.pathname.includes("index.html")){
    setTimeout(() => {
        window.location.href = ("registro.php");
    }, 3000);
}



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