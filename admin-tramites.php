<?php
// admin-tramites.php
// Panel para el editor de contenido: formulario para cargar un trámite
// nuevo (o editar uno existente, reusando el mismo id) y lista de los
// trámites que ya están cargados en la base de datos.
//
// Protegida por verificar_editor.php: si quien entra no tiene sesión
// iniciada, o su rol no es 'editor', es redirigido antes de que se
// muestre nada de esta página.

session_start();
require_once 'conexion.php';
require_once 'auto_login.php';
require_once 'verificar_editor.php';

// Mensaje de resultado, si venimos de admin-guardar-tramite.php
$mensaje = "";
if (isset($_GET['guardado']) && $_GET['guardado'] === '1') {
    $mensaje = "<div class='mensaje exito'>Trámite guardado correctamente.</div>";
} elseif (isset($_GET['importados'])) {
    $cantidad = (int) $_GET['importados'];
    $mensaje = "<div class='mensaje exito'>Se importaron/actualizaron $cantidad trámites desde la planilla.</div>";
    if (isset($_GET['omitidos'])) {
        $omitidos = (int) $_GET['omitidos'];
        $mensaje .= "<div class='mensaje error'>$omitidos fila(s) se omitieron por tener el ID vacío, con formato inválido, o el nombre vacío. Revisá esas filas y volvé a subir solo esas.</div>";
    }
} elseif (isset($_GET['error'])) {
    $errores = [
        'faltan_datos'     => 'Completá al menos el ID y el nombre del trámite.',
        'id_invalido'      => 'El ID solo puede tener minúsculas, números y guiones (ej: "ute-factura").',
        'nombre_muy_largo' => 'El nombre no puede tener más de 50 caracteres.',
        'imagen_invalida'  => 'La imagen debe ser JPG, PNG o WEBP.',
        'error_servidor'   => 'Ocurrió un error al guardar. Intentá nuevamente.',
        'archivo_invalido' => 'Subí un archivo .csv válido.',
        'columnas_faltantes' => 'La planilla debe tener las columnas: id, nombre, descripcion, modalidad, icono.',
    ];
    $texto = $errores[$_GET['error']] ?? 'Ocurrió un error.';
    $mensaje = "<div class='mensaje error'>$texto</div>";
} elseif (isset($_GET['estado_cambiado'])) {
    $mensaje = "<div class='mensaje exito'>Se actualizó el estado del trámite.</div>";
} elseif (isset($_GET['eliminado'])) {
    $mensaje = "<div class='mensaje exito'>Trámite eliminado correctamente.</div>";
}

// Traemos los trámites ya cargados para mostrarlos en una lista debajo del formulario
$tramites = $conexion->query("SELECT * FROM tramites ORDER BY fecha_creado DESC")->fetchAll(PDO::FETCH_ASSOC);

// Si venimos de hacer click en "Editar", precargamos el formulario con
// los datos existentes de ese trámite (incluye el contenido por modalidad)
$edicion = null;
$contenidoEdicion = [];

if (isset($_GET['editar'])) {
    $idEditar = trim($_GET['editar']);

    $stmt = $conexion->prepare("SELECT * FROM tramites WHERE id = :id");
    $stmt->bindParam(':id', $idEditar);
    $stmt->execute();
    $edicion = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($edicion) {
        $stmt = $conexion->prepare("SELECT * FROM tramite_contenido WHERE tramite_id = :id");
        $stmt->bindParam(':id', $idEditar);
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $contenidoEdicion[$fila['modalidad']] = $fila;
        }
    }
}

// Función chica para no repetir el patrón "valor guardado o vacío" en el formulario
function valorForm($valor): string
{
    return htmlspecialchars($valor ?? '');
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Trámites - TramiApp</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="shortcut icon" href="img/LogoICO.ico" type="image/x-icon">
    <style>
        /* Estilos propios de este panel. Reutilizan las variables de color
           que ya están definidas en estilos.css (--verde-principal, etc.) */
        .panel-admin-card {
            background: var(--blanco);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        .panel-admin-card h3 {
            margin-bottom: 15px;
            color: var(--texto-oscuro);
        }
        .campo-admin {
            margin-bottom: 15px;
        }
        .campo-admin label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: var(--texto-mutado);
        }
        .contador-limite {
            color: #b71c1c !important;
            font-weight: bold;
        }
        .campo-admin input[type="text"],
        .campo-admin textarea,
        .campo-admin select,
        .campo-admin input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #cccccc;
            border-radius: 12px;
            font-size: 14px;
            box-sizing: border-box;
            font-family: inherit;
        }
        .campo-admin textarea {
            resize: vertical;
            min-height: 70px;
        }
        .btn-admin-guardar {
            width: 100%;
            padding: 12px;
            background-color: var(--verde-principal);
            color: var(--blanco);
            border: none;
            border-radius: 25px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-admin-guardar:hover {
            background-color: var(--verde-oscuro);
        }
        .celda-nombre {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .tabla-tramites {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .tabla-tramites th, .tabla-tramites td {
            text-align: left;
            padding: 14px 8px; /* antes 10px 8px — un poco más de aire */
            font-size: 13px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .tabla-tramites td:nth-child(1) {
            overflow: hidden; /* evita que la imagen se desborde a la fila de abajo */
        }

        .tabla-tramites img {
            width: 36px;
            height: 36px;
            object-fit: cover;
            border-radius: 6px;
            vertical-align: middle;
            display: block;
        }
        
        .badge-inactivo {
            background: #fdecea;
            color: #b71c1c;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
        }
        .badge-activo {
            background: #e2f7ed;
            color: #00693e;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
        }
        .bloque-modalidad {
            background: var(--fondo-gris);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .bloque-modalidad.oculto {
            display: none;
        }
        .celda-acciones {
            white-space: nowrap;
        }
        .btn-accion {
            display: inline-block;
            border: none;
            background: none;
            font-size: 16px;
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 8px;
            text-decoration: none;
        }
        .btn-accion:hover {
            background: var(--fondo-gris);
        }
    </style>
</head>
<body>

    <header class="header">
        <a href="inicio.php" class="btn-volver">‹</a>
        <h1>Administrar Trámites</h1>
        <div style="width:24px;"></div>
    </header>

    <div class="main-container" style="margin-bottom: 70px;">

        <?php echo $mensaje; ?>

        <div class="panel-admin-card" id="form-tramite">
            <h3><?php echo $edicion ? 'Editar trámite: ' . htmlspecialchars($edicion['nombre']) : 'Cargar nuevo trámite'; ?></h3>

            <?php if ($edicion): ?>
                <p style="font-size:13px; color:var(--texto-mutado); margin-bottom:15px;">
                    Estás editando un trámite existente. <a href="admin-tramites.php">Cancelar edición</a>
                </p>
            <?php else: ?>
                <p style="font-size:13px; color:var(--texto-mutado); margin-bottom:15px;">
                    Si usás un ID que ya existe, se actualizan los datos de ese trámite en vez de crear uno duplicado.
                </p>
            <?php endif; ?>

            <form action="admin-guardar-tramite.php" method="POST" enctype="multipart/form-data">
                <div class="campo-admin">
                    <label for="id">ID único (sin espacios ni tildes, ej: "cedula-renovacion")</label>
                    <input type="text" id="id" name="id" placeholder="cedula-renovacion" required
                        pattern="[a-z0-9\-]+" title="Solo minúsculas, números y guiones"
                        value="<?php echo valorForm($edicion['id'] ?? ''); ?>"
                        <?php echo $edicion ? 'readonly style="background:#eee;"' : ''; ?>>
                </div>

                <div class="campo-admin">
                    <label for="nombre">Nombre visible (máx. 50 caracteres)</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Renovación de Cédula" required
                        maxlength="50" oninput="actualizarContadorNombre()"
                        value="<?php echo valorForm($edicion['nombre'] ?? ''); ?>">
                    <div id="contador-nombre" style="font-size:12px; color:var(--texto-mutado); margin-top:4px; text-align:right;">
                        <?php echo mb_strlen($edicion['nombre'] ?? ''); ?>/50
                    </div>
                </div>

                <div class="campo-admin">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" placeholder="Explicación breve del trámite..."><?php echo valorForm($edicion['descripcion'] ?? ''); ?></textarea>
                </div>

                <div class="campo-admin">
                    <label for="modalidad">Modalidad</label>
                    <?php $modalidadActual = $edicion['modalidad'] ?? 'ambas'; ?>
                    <select id="modalidad" name="modalidad">
                        <option value="online" <?php echo $modalidadActual === 'online' ? 'selected' : ''; ?>>Online</option>
                        <option value="presencial" <?php echo $modalidadActual === 'presencial' ? 'selected' : ''; ?>>Presencial</option>
                        <option value="ambas" <?php echo $modalidadActual === 'ambas' ? 'selected' : ''; ?>>Ambas</option>
                    </select>
                </div>

                <div class="campo-admin">
                    <label for="icono">Imagen (JPG, PNG o WEBP)<?php echo $edicion ? ' — dejá vacío para no cambiar la actual' : ''; ?></label>
                    <input type="file" id="icono" name="icono" accept="image/png, image/jpeg, image/webp">
                </div>

                <hr style="border:none; border-top:1px solid #eee; margin: 20px 0;">

                <p style="font-size:13px; color:var(--texto-mutado); margin-bottom:15px;">
                    Contenido detallado de la pantalla del trámite. Completá el bloque que corresponda
                    según la modalidad elegida arriba (si elegiste "Ambas", completá los dos).
                    Todos estos campos son opcionales.
                </p>

                <div id="bloque-presencial" class="bloque-modalidad">
                    <h4 style="margin-bottom:10px; color:var(--verde-oscuro);">📍 Contenido — Presencial</h4>

                    <div class="campo-admin">
                        <label for="descripcion_presencial">Texto de la franja superior (para esta modalidad)</label>
                        <textarea id="descripcion_presencial" name="descripcion_presencial"
                                  placeholder="Ej: El trámite se realiza de forma presencial en..."><?php echo valorForm($contenidoEdicion['presencial']['descripcion'] ?? ''); ?></textarea>
                    </div>
                    <div class="campo-admin">
                        <label for="requisitos_presencial">Requisitos (uno por línea)</label>
                        <textarea id="requisitos_presencial" name="requisitos_presencial"
                                  placeholder="Cédula de identidad&#10;Comprobante de domicilio"><?php echo valorForm($contenidoEdicion['presencial']['requisitos'] ?? ''); ?></textarea>
                    </div>
                    <div class="campo-admin">
                        <label for="donde_presencial">¿Dónde se realiza? (direcciones, horarios)</label>
                        <textarea id="donde_presencial" name="donde_presencial"
                                  placeholder="Abitab Guichón - 18 de Julio 308&#10;Lunes a viernes de 8:15 a 18:30 hs"><?php echo valorForm($contenidoEdicion['presencial']['donde'] ?? ''); ?></textarea>
                    </div>
                    <div class="campo-admin">
                        <label for="video_presencial">Link a video explicativo (opcional)</label>
                        <input type="text" id="video_presencial" name="video_presencial" placeholder="https://youtube.com/..." value="<?php echo valorForm($contenidoEdicion['presencial']['video'] ?? ''); ?>">
                    </div>
                </div>

                <div id="bloque-online" class="bloque-modalidad">
                    <h4 style="margin-bottom:10px; color:var(--verde-oscuro);">💻 Contenido — En línea</h4>

                    <div class="campo-admin">
                        <label for="descripcion_online">Texto de la franja superior (para esta modalidad)</label>
                        <textarea id="descripcion_online" name="descripcion_online"
                                  placeholder="Ej: Podés hacer este trámite las 24 horas desde..."><?php echo valorForm($contenidoEdicion['online']['descripcion'] ?? ''); ?></textarea>
                    </div>
                    <div class="campo-admin">
                        <label for="requisitos_online">Requisitos (uno por línea)</label>
                        <textarea id="requisitos_online" name="requisitos_online"
                                  placeholder="Correo electrónico&#10;Medio de pago habilitado"><?php echo valorForm($contenidoEdicion['online']['requisitos'] ?? ''); ?></textarea>
                    </div>
                    <div class="campo-admin">
                        <label for="donde_online">¿Dónde se realiza? (web, app, disponibilidad)</label>
                        <textarea id="donde_online" name="donde_online"
                                  placeholder="A través de la web o app oficial&#10;Disponible las 24 horas"><?php echo valorForm($contenidoEdicion['online']['donde'] ?? ''); ?></textarea>
                    </div>
                    <div class="campo-admin">
                        <label for="video_online">Link a video explicativo (opcional)</label>
                        <input type="text" id="video_online" name="video_online" placeholder="https://youtube.com/..." value="<?php echo valorForm($contenidoEdicion['online']['video'] ?? ''); ?>">
                    </div>
                </div>
                <hr style="border:none; border-top:1px solid #eee; margin: 20px 0;">

                <p style="font-size:13px; color:var(--texto-mutado); margin-bottom:15px;">
                    Preguntas del test de este trámite. Necesitás al menos 4 para que el test funcione
                    (con la misma exigencia de 75% que usa la app: aprobar requiere 3 de 4 correctas).
                </p>

                <div id="lista-preguntas"></div>

                <button type="button" class="btn-admin-guardar" style="background-color: var(--texto-oscuro); margin-bottom: 20px;" onclick="agregarPregunta()">
                    + Agregar pregunta
                </button>

                <button type="submit" class="btn-admin-guardar">Guardar trámite</button>
            </form>
        </div>

        <div class="panel-admin-card">
            <h3>Importar varios trámites desde una planilla</h3>
            <p style="font-size:13px; color:var(--texto-mutado); margin-bottom:10px;">
                Subí un archivo <strong>.csv</strong> con las columnas
                <code>id, nombre, descripcion, modalidad, icono</code> (en ese orden, con esos nombres
                en la primera fila). La columna <code>icono</code> debe tener solo el nombre del
                archivo de imagen (ej: <code>cedula.jpg</code>) — esa imagen tiene que estar subida
                de antemano en la carpeta <code>img/Tramites/</code> del servidor.
            </p>
            <p style="font-size:13px; color:var(--texto-mutado); margin-bottom:15px;">
                Si un ID de la planilla ya existe, ese trámite se actualiza en vez de duplicarse.
            </p>

            <form action="admin-importar-tramites.php" method="POST" enctype="multipart/form-data">
                <div class="campo-admin">
                    <input type="file" name="archivo" accept=".csv" required>
                </div>
                <button type="submit" class="btn-admin-guardar">Importar planilla</button>
            </form>
        </div>

       <div class="panel-admin-card">
            <h3>Trámites cargados (<?php echo count($tramites); ?>)</h3>

            <?php if (empty($tramites)): ?>
                <p style="color:var(--texto-mutado); font-size:14px;">Todavía no hay trámites cargados.</p>
            <?php else: ?>

                <input type="text" id="buscador-admin-tramites" class="search-bar"
                    placeholder="Buscar por nombre o ID..." onkeyup="filtrarTablaAdmin()"
                    style="margin-bottom:15px;">

                <p id="sin-resultados-admin" class="oculto" style="color:var(--texto-mutado); font-size:14px; margin-bottom:10px;">
                    No se encontraron trámites que coincidan con la búsqueda.
                </p>

                <table class="tabla-tramites" id="tabla-admin-tramites">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Nombre</th>
                            <th>ID</th>
                            <th>Modalidad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tramites as $t): ?>
                            <tr class="fila-tramite-admin"
                                data-nombre="<?php echo htmlspecialchars(mb_strtolower($t['nombre'])); ?>"
                                data-id="<?php echo htmlspecialchars(mb_strtolower($t['id'])); ?>">
                                <td>
                                    <?php if (!empty($t['icono'])): ?>
                                        <img src="<?php echo htmlspecialchars($t['icono']); ?>" alt="">
                                    <?php endif; ?>
                                </td>
                                <td class="celda-nombre" title="<?php echo htmlspecialchars($t['nombre']); ?>">
                                    <?php echo htmlspecialchars($t['nombre']); ?>
                                </td>
                                <td><code><?php echo htmlspecialchars($t['id']); ?></code></td>
                                <td><?php echo htmlspecialchars($t['modalidad']); ?></td>
                                <td>
                                    <?php if ($t['activo']): ?>
                                        <span class="badge-activo">Activo</span>
                                    <?php else: ?>
                                        <span class="badge-inactivo">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="celda-acciones">
                                    <a href="admin-tramites.php?editar=<?php echo urlencode($t['id']); ?>#form-tramite"
                                    class="btn-accion btn-editar" title="Editar">✏️</a>

                                    <form action="admin-cambiar-estado.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($t['id']); ?>">
                                        <button type="submit" class="btn-accion btn-toggle" title="<?php echo $t['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                            <?php echo $t['activo'] ? '🙈' : '👁️'; ?>
                                        </button>
                                    </form>

                                    <form action="admin-eliminar-tramite.php" method="POST" style="display:inline;"
                                        onsubmit="return confirm('¿Seguro que querés borrar «<?php echo htmlspecialchars($t['nombre'], ENT_QUOTES); ?>»? Esta acción no se puede deshacer.');">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($t['id']); ?>">
                                        <button type="submit" class="btn-accion btn-borrar" title="Borrar">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>

    <nav class="bottom-nav">
        <a href="inicio.php" class="nav-item">🏠</a>
        <a href="chat.html" class="nav-item">💬</a>
        <a href="configuracion.php" class="nav-item">⚙️</a>
    </nav>

    <script src="script.js"></script>
    <script>
        // Muestra u oculta los bloques de contenido "Presencial" / "En línea"
        // según la modalidad elegida en el selector del formulario. Si se
        // elige "Ambas", se muestran los dos bloques para completar los dos.
        function actualizarBloquesModalidad() {
            const modalidad = document.getElementById('modalidad').value;
            const bloquePresencial = document.getElementById('bloque-presencial');
            const bloqueOnline = document.getElementById('bloque-online');

            bloquePresencial.classList.toggle('oculto', modalidad === 'online');
            bloqueOnline.classList.toggle('oculto', modalidad === 'presencial');
        }
        
        let contadorPreguntas = 0;

        function agregarPregunta() {
            const indice = contadorPreguntas++;
            const contenedor = document.getElementById('lista-preguntas');

            const bloque = document.createElement('div');
            bloque.className = 'bloque-modalidad';
            bloque.id = `pregunta-${indice}`;
            bloque.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <h4 style="color:var(--verde-oscuro);">Pregunta</h4>
                    <button type="button" onclick="document.getElementById('pregunta-${indice}').remove()"
                            style="background:none; border:none; color:#b71c1c; cursor:pointer; font-size:13px;">
                        ✕ Quitar
                    </button>
                </div>
                <div class="campo-admin">
                    <label>Texto de la pregunta</label>
                    <textarea name="pregunta_texto[]" required></textarea>
                </div>
                <div class="campo-admin">
                    <label>Opción A</label>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="radio" name="correcta_${indice}" value="0" required>
                        <input type="text" name="opcion_a[]" style="flex:1;" required>
                    </div>
                </div>
                <div class="campo-admin">
                    <label>Opción B</label>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="radio" name="correcta_${indice}" value="1">
                        <input type="text" name="opcion_b[]" style="flex:1;" required>
                    </div>
                </div>
                <div class="campo-admin">
                    <label>Opción C</label>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="radio" name="correcta_${indice}" value="2">
                        <input type="text" name="opcion_c[]" style="flex:1;" required>
                    </div>
                </div>
                <div class="campo-admin">
                    <label>Opción D</label>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="radio" name="correcta_${indice}" value="3">
                        <input type="text" name="opcion_d[]" style="flex:1;" required>
                    </div>
                </div>
                <p style="font-size:12px; color:var(--texto-mutado);">Marcá con el círculo cuál opción es la correcta.</p>
            `;
            contenedor.appendChild(bloque);
        }

        // Arrancamos con 4 preguntas vacías, el mínimo que pide el test
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('lista-preguntas')) {
                for (let i = 0; i < 4; i++) agregarPregunta();
            }
        });

        function filtrarTablaAdmin() {
            const input = document.getElementById('buscador-admin-tramites');
            if (!input) return;

            const filtro = input.value.trim().toLowerCase();
            const filas = document.querySelectorAll('#tabla-admin-tramites .fila-tramite-admin');
            let visibles = 0;

            filas.forEach(fila => {
                const nombre = fila.dataset.nombre || '';
                const id = fila.dataset.id || '';
                const coincide = nombre.includes(filtro) || id.includes(filtro);

                fila.style.display = coincide ? '' : 'none';
                if (coincide) visibles++;
            });

            // Mensaje de "sin resultados" solo si hay texto buscado y nada coincidió
            const avisoSinResultados = document.getElementById('sin-resultados-admin');
            if (avisoSinResultados) {
                avisoSinResultados.classList.toggle('oculto', !(filtro !== '' && visibles === 0));
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('modalidad');
            if (!select) return;
            actualizarBloquesModalidad();
            select.addEventListener('change', actualizarBloquesModalidad);
        });

        function actualizarContadorNombre() {
            const input = document.getElementById('nombre');
            const contador = document.getElementById('contador-nombre');
            if (!input || !contador) return;

            const max = input.maxLength;
            const actual = input.value.length;

            contador.textContent = `${actual}/${max}`;

            // Avisamos visualmente cuando quedan pocos caracteres disponibles
            if (actual >= max - 10) {
                contador.classList.add('contador-limite');
            } else {
                contador.classList.remove('contador-limite');
            }
        }

        // Por si la página se carga con el campo ya completado (ej: al volver atrás
        // con el navegador y el campo quedó con texto), inicializamos el contador
        document.addEventListener('DOMContentLoaded', actualizarContadorNombre);
    </script>
</body>
</html>