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