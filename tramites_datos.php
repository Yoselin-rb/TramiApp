<?php
// tramites_datos.php
// Mapeo central de trámites: tramite_id (interno) => nombre visible + imagen.
//
// favoritos.tramite_id y finalizados.tramite_id solo guardan el ID
// (ej: "ute-factura"), no el nombre ni la imagen. Este archivo es el único
// lugar donde tenemos que mantener esa relación, para que "Mi actividad"
// (mi-actividad.php) pueda mostrar el nombre y la imagen de cada trámite.
//
// IMPORTANTE: si agregás un trámite nuevo a la app, sumalo acá con el
// MISMO tramite_id que uses en:
//   - el botón de favorito, atributo data-tramite-id (ver favorito.php)
//   - el test del trámite, en MAPA_TESTS de script.js (ver finalizar_tramite.php)

return [
    'cedula-renovacion' => [
        'nombre' => 'Renovación de Cédula',
        'imagen' => 'img/Tramites/Cedula.jpg',
    ],
    'ute-factura' => [
        'nombre' => 'Pago de factura de UTE',
        'imagen' => 'img/Tramites/factura-ute.webp',
    ],
    'beca-butia' => [
        'nombre' => 'Inscripción para beca Butiá',
        'imagen' => 'img/Tramites/BUTIA.jpeg',
    ],
    'gub-uy' => [
        'nombre' => 'Registro en gub.uy',
        'imagen' => 'img/Tramites/gub.png',
    ],
    'licencia-conducir' => [
        'nombre' => 'Licencia de Conducir',
        'imagen' => 'img/Tramites/licenciaConducir.png',
    ],
    'partida-nacimiento' => [
        'nombre' => 'Partida de nacimiento',
        'imagen' => 'img/Tramites/partida_nacimiento.jpg',
    ],
    'pago-patente' => [
        'nombre' => 'Pago de patente',
        'imagen' => 'img/Tramites/patente.jpg',
    ],
    'historia-laboral-bps' => [
        'nombre' => 'Historia laboral BPS',
        'imagen' => 'img/Tramites/historia_laboral.webp',
    ],
    'credencial-civica' => [
        'nombre' => 'Credencial Cívica',
        'imagen' => 'img/Tramites/credencial_civica.jpg',
    ],
    'app-ebrou' => [
        'nombre' => 'App eBROU',
        'imagen' => 'img/Tramites/ebrou.png',
    ],
];
