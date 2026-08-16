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
        'imagen_invalida'  => 'La imagen debe ser JPG, PNG o WEBP.',
        'error_servidor'   => 'Ocurrió un error al guardar. Intentá nuevamente.',
        'archivo_invalido' => 'Subí un archivo .csv válido.',
        'columnas_faltantes' => 'La planilla debe tener las columnas: id, nombre, descripcion, modalidad, icono.',
    ];
    $texto = $errores[$_GET['error']] ?? 'Ocurrió un error.';
    $mensaje = "<div class='mensaje error'>$texto</div>";
}

// Traemos los trámites ya cargados para mostrarlos en una lista debajo del formulario
$tramites = $conexion->query("SELECT * FROM tramites ORDER BY fecha_creado DESC")->fetchAll(PDO::FETCH_ASSOC);
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
        .tabla-tramites {
            width: 100%;
            border-collapse: collapse;
        }
        .tabla-tramites th,
        .tabla-tramites td {
            text-align: left;
            padding: 10px 8px;
            font-size: 13px;
            border-bottom: 1px solid #eee;
        }
        .tabla-tramites img {
            width: 36px;
            height: 36px;
            object-fit: cover;
            border-radius: 6px;
            vertical-align: middle;
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

        <div class="panel-admin-card">
            <h3>Cargar nuevo trámite</h3>
            <p style="font-size:13px; color:var(--texto-mutado); margin-bottom:15px;">
                Si usás un ID que ya existe, se actualizan los datos de ese trámite en vez de crear uno duplicado.
            </p>

            <form action="admin-guardar-tramite.php" method="POST" enctype="multipart/form-data">
                <div class="campo-admin">
                    <label for="id">ID único (sin espacios ni tildes, ej: "cedula-renovacion")</label>
                    <input type="text" id="id" name="id" placeholder="cedula-renovacion" required
                           pattern="[a-z0-9\-]+" title="Solo minúsculas, números y guiones">
                </div>

                <div class="campo-admin">
                    <label for="nombre">Nombre visible</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Renovación de Cédula" required>
                </div>

                <div class="campo-admin">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" placeholder="Explicación breve del trámite..."></textarea>
                </div>

                <div class="campo-admin">
                    <label for="modalidad">Modalidad</label>
                    <select id="modalidad" name="modalidad">
                        <option value="online">Online</option>
                        <option value="presencial">Presencial</option>
                        <option value="ambas" selected>Ambas</option>
                    </select>
                </div>

                <div class="campo-admin">
                    <label for="icono">Imagen (JPG, PNG o WEBP)</label>
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
                                  placeholder="Ej: El trámite se realiza de forma presencial en..."></textarea>
                    </div>
                    <div class="campo-admin">
                        <label for="requisitos_presencial">Requisitos (uno por línea)</label>
                        <textarea id="requisitos_presencial" name="requisitos_presencial"
                                  placeholder="Cédula de identidad&#10;Comprobante de domicilio"></textarea>
                    </div>
                    <div class="campo-admin">
                        <label for="donde_presencial">¿Dónde se realiza? (direcciones, horarios)</label>
                        <textarea id="donde_presencial" name="donde_presencial"
                                  placeholder="Abitab Guichón - 18 de Julio 308&#10;Lunes a viernes de 8:15 a 18:30 hs"></textarea>
                    </div>
                    <div class="campo-admin">
                        <label for="video_presencial">Link a video explicativo (opcional)</label>
                        <input type="text" id="video_presencial" name="video_presencial" placeholder="https://youtube.com/...">
                    </div>
                </div>

                <div id="bloque-online" class="bloque-modalidad">
                    <h4 style="margin-bottom:10px; color:var(--verde-oscuro);">💻 Contenido — En línea</h4>

                    <div class="campo-admin">
                        <label for="descripcion_online">Texto de la franja superior (para esta modalidad)</label>
                        <textarea id="descripcion_online" name="descripcion_online"
                                  placeholder="Ej: Podés hacer este trámite las 24 horas desde..."></textarea>
                    </div>
                    <div class="campo-admin">
                        <label for="requisitos_online">Requisitos (uno por línea)</label>
                        <textarea id="requisitos_online" name="requisitos_online"
                                  placeholder="Correo electrónico&#10;Medio de pago habilitado"></textarea>
                    </div>
                    <div class="campo-admin">
                        <label for="donde_online">¿Dónde se realiza? (web, app, disponibilidad)</label>
                        <textarea id="donde_online" name="donde_online"
                                  placeholder="A través de la web o app oficial&#10;Disponible las 24 horas"></textarea>
                    </div>
                    <div class="campo-admin">
                        <label for="video_online">Link a video explicativo (opcional)</label>
                        <input type="text" id="video_online" name="video_online" placeholder="https://youtube.com/...">
                    </div>
                </div>

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
                <table class="tabla-tramites">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Nombre</th>
                            <th>ID</th>
                            <th>Modalidad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tramites as $t): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($t['icono'])): ?>
                                        <img src="<?php echo htmlspecialchars($t['icono']); ?>" alt="">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($t['nombre']); ?></td>
                                <td><code><?php echo htmlspecialchars($t['id']); ?></code></td>
                                <td><?php echo htmlspecialchars($t['modalidad']); ?></td>
                                <td>
                                    <?php if ($t['activo']): ?>
                                        <span class="badge-activo">Activo</span>
                                    <?php else: ?>
                                        <span class="badge-inactivo">Inactivo</span>
                                    <?php endif; ?>
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

        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('modalidad');
            if (!select) return;
            actualizarBloquesModalidad();
            select.addEventListener('change', actualizarBloquesModalidad);
        });
    </script>
</body>
</html>