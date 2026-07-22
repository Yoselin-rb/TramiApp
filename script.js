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


function alternarFavorito() {
    const btn = document.getElementById('btn-favorito');
 // Si tiene la estrella vacía, la cambia por la llena, y viceversa
    if (!btn) return;
    if (btn.innerText === '☆') {
        btn.innerText = '★';
    } else {
        btn.innerText = '☆';
    }
}
