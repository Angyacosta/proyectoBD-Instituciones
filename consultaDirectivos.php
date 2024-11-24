<?php
include 'conexionDB.php';

// Detecta si se está ejecutando en la consola o en un navegador
if (php_sapi_name() === 'cli') {
// Modo consola
if ($argc < 2) {
echo "Uso: php index.php [create/read/update/delete]\n";
exit(1);
}
$action = $argv[1];
switch ($action) {
case 'create':
$nomb_directivo = readline("Nombres: ");
$apell_directivo = readline("Apellidos: ");
$sql = "INSERT INTO directivos(nomb_directivo,apell_directivo) VALUES (?,?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$nomb_directivo,$apell_directivo ]);
echo "Directivo creado con éxito.\n";
break;
case 'read':
$sql = "SELECT * FROM directivos";
$stmt = $conn->query($sql);
$instituciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($instituciones as $directivo) {
echo "codigo: {$directivo['cod_directivo']} | Nombres: {$directivo['nomb_directivo']} | Apellidos: {$directivo['apell_directivo']} \n";
}
break;
case 'update':
$cod_directivo = readline("codigo del usuario a actualizar: ");
$nomb_directivo = readline("Nuevo nombres: ");
$apell_directivo = readline("Nuevos apellidos: ");
$sql = "UPDATE directivos SET nomb_directivo = ?, apell_directivo= ? WHERE cod_directivo = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$nomb_directivo,$apell_directivo, $cod_directivo]);
echo "Directivo actualizado con éxito.\n";
break;
case 'delete':
$cod_directivo = readline("codigo del usuario a eliminar: ");
$sql = "DELETE FROM directivos WHERE cod_directivo = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$cod_directivo]);
echo "Directivo eliminado con éxito.\n";
break;
default:
echo "Acción no válida.\n";
}
} else {


// Definir cuántos registros mostrar por página
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Obtener la página actual, por defecto es la 1
$offset = ($page - 1) * $limit; // Calcular el desplazamiento de la consulta

// Obtener los directivos limitados
$sql = "SELECT * FROM directivos ORDER BY cod_directivo ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$limit, $offset]);
$instituciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener el total de directivos para calcular las páginas
$sql_count = "SELECT COUNT(*) FROM directivos";
$stmt_count = $conn->query($sql_count);
$total_directivos = $stmt_count->fetchColumn();
$total_pages = ceil($total_directivos / $limit); // Calcular el total de páginas

?>
<?php
 session_start();
include 'dirstyle.html';
$form_display = false;
// Maneja la creación, actualización y eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $nomb_directivo = $_POST['nomb_directivo_create'];
        $apell_directivo= $_POST['apell_directivo_create'];
        // Insertar el nuevo directivo
        $sql = "INSERT INTO directivos (nomb_directivo, apell_directivo) VALUES ( ?, ?)";
        
        $stmt = $conn->prepare($sql);
    
        if ($stmt->execute([$nomb_directivo, $apell_directivo])) {
            echo "<script>
                    alert('Directivo creado con éxito.');
                    window.location.href = window.location.href;
                  </script>";
        } else {
            echo "<p>Error al crear el directivo.</p>";
        }
    }if (isset($_POST['update'])) {
        $cod_directivo = $_POST['cod_directivo_update'];
    
        // Verificar si el código del directivo existe
        $sql_check = "SELECT COUNT(*) FROM directivos WHERE cod_directivo = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([$cod_directivo]);
        $exists = $stmt_check->fetchColumn();
    
        if ($exists == 0) {
            // Si el código no existe, mostrar mensaje de error
            echo "<script>alert('El código no se encuentra en la tabla. Intenta de nuevo');</script>";
        } else {
            // Si el código existe, obtener los datos del directivo
            $sql_get = "SELECT nomb_directivo, apell_directivo, cod_directivo FROM directivos WHERE cod_directivo = ?";
            $stmt_get = $conn->prepare($sql_get);
            $stmt_get->execute([$cod_directivo]);
            $directivo = $stmt_get->fetch(PDO::FETCH_ASSOC);
    
            // Guardar el código y los datos en la sesión para mantener el estado
        $_SESSION['cod_directivo'] = $cod_directivo;
        $_SESSION['nomb_directivo'] = $directivo['nomb_directivo'];
        $_SESSION['apell_directivo'] = $directivo['apell_directivo'];

        // Cambiar a verdadero para mostrar el formulario de actualización
        $_SESSION['form_display'] = true; // Cambiar estado para mostrar el formulario de actualización
        }
        $_SESSION['active_form'] = 'update-form'; // Formulario de actualización
    }if (isset($_POST['update_directivo'])) {
        $cod_directivo = $_POST['cod_directivo_update'];
        $nomb_directivo = $_POST['nomb_directivo_update'];
        $apell_directivo = $_POST['apell_directivo_update'];
    
        // Actualizar el directivo en la base de datos
        $sql_update = "UPDATE directivos SET nomb_directivo = ?, apell_directivo = ? WHERE cod_directivo = ?";
        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update->execute([$nomb_directivo, $apell_directivo, $cod_directivo])) {
            echo "<script>
                    alert('Directivo actualizado con éxito.');
                    window.location.href = window.location.href; // Recarga la página
                  </script>";
                  session_unset();
                  session_destroy();
        } else {
            echo "<p>Error al actualizar el directivo.</p>";
        }
    }elseif (isset($_POST['delete'])) {
        // Obtener el código del directivo a eliminar desde el formulario
    $cod_directivo = $_POST['cod_directivo_delete'];

    try {
        // Verificar si el código del directivo existe en la tabla
        $sql_check = "SELECT COUNT(*) FROM directivos WHERE cod_directivo = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([$cod_directivo]);
        $exists = $stmt_check->fetchColumn();

        if ($exists == 0) {
            // Mostrar alerta si el código no existe
            echo "<script>
                alert('El código que desea eliminar no se encuentra en la tabla. Intenta de nuevo.');
            </script>";
        } else {
            // Intentar eliminar el directivo
            $sql = "DELETE FROM directivos WHERE cod_directivo = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt->execute([$cod_directivo])) {
                // Mostrar mensaje de éxito y recargar la página
                echo "<script>
                    alert('Directivo eliminado con éxito.');
                    window.location.href = window.location.href;
                </script>";
            } else {
                // Mostrar mensaje en caso de error al ejecutar la consulta
                echo "<script>
                    alert('Hubo un problema al eliminar el directivo. Intenta de nuevo.');
                </script>";
            }
        }
    } catch (PDOException $e) {
        // Manejo de errores específicos como restricciones de clave foránea
        if ($e->getCode() == '23503') { // Código específico de PostgreSQL para clave foránea
            echo "<script>
                alert('¡INTENTO DE VIOLACIÓN!   No se puede eliminar porque está relacionado con otra tabla.');
            </script>";
        } else {
            // Mostrar un error genérico
            echo "<script>
                alert('Error inesperado: " . $e->getMessage() . "');
            </script>";
        }
    }
    }
}
$activeForm = isset($_SESSION['active_form']) ? $_SESSION['active_form'] : null;    
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Actualizar Directivos</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> 
</head>
<body>
    <br>
    <div class="header" style="margin-bottom: 10px; padding: 10px;">
    <img src="logo_men.png" alt="Logo de la institución" style="max-width: 100%; height: auto; margin-bottom: 20px;">
    <!-- Contenedor de botones -->
    <div class="update-link">
    <a href="index.php" class="update-btn">volver a inicio</nav></a>
    </div>

</div>

<div class="container-wrapper"> 
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th colspan="3" >Directivos</th>
            </tr>
            <tr>
                <th>Código</th>
                <th>Nombres</th>
                <th>Apellidos</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($instituciones as $directivo): ?>
            <tr>
                <td><?php echo htmlspecialchars($directivo['cod_directivo']); ?></td>
                <td><?php echo htmlspecialchars($directivo['nomb_directivo']); ?></td>
                <td><?php echo htmlspecialchars($directivo['apell_directivo']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<div class="pagination">
        <!-- Crear los enlaces de paginación -->
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">Anterior</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'style="background-color: #0277bd;"' : ''; ?>><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Siguiente</a>
        <?php endif; ?>
    </div>
    <br>
    <br>
    <div class="menu-links">
    <a href="#" id="link-create">Crear</a>
    <a href="#" id="link-update">Actualizar</a>
    <a href="#" id="link-delete">Eliminar</a>
</div>

</div>


<div class="form-container">
    
    <!-- Formulario de creación -->
    <div class="form-section" id="create-form">
        <h2>Crear Directivo</h2>
        <form method="POST" id="createForm">
            <label for="nomb_directivo_create">Nombre:</label>
            <input type="text" id= "nomb_directivo_create" name="nomb_directivo_create" required style="text-transform:uppercase;">
            <p class="error-message" id="errorCrearDir">EL nombre debe contener solo letras y no vacio.</p>
            <p class="error-message" id="longnomb">Máximo 3 nombres permitidos.</p>
            <br>
            <label for="apell_directivo_create">Apellidos: </label>
        <input type="text" id="apell_directivo_create" name="apell_directivo_create" required style="text-transform:uppercase;">
        <p class="error-message" id="errorCrearApell" >El apellido debe contener solo letras y no vacio.</p>
        <p class="error-message" id="longapell" >Máximo 3 apellidos permitidos.</p>
        <br>
            <button type="submit" name="create">Crear</button>
        </form>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Referencias a los campos y mensajes de error
        const nombreInput = document.getElementById('nomb_directivo_create');
        const apellidoInput = document.getElementById('apell_directivo_create');
        const form = document.getElementById('createForm');
        const errorNombre = document.getElementById('errorCrearDir');
        const errorApellido = document.getElementById('errorCrearApell');
        const longN = document.getElementById('longnomb');
        const longA = document.getElementById('longapell');

        // Expresiones regulares
        const soloLetras = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/; // Solo letras y espacios
        const maxCadenas = /^(\S+\s*){1,3}$/; // Máximo 3 palabras (aplica para nombre y apellido)

        // Validación de un campo
        const validarCampo = (input, errorMensaje, errorLongitud, maxPalabras) => {
            const valor = input.value.trim(); // Eliminar espacios adicionales

            // Validar caracteres
            if (valor === '' || !soloLetras.test(valor)) {
                errorMensaje.style.display = 'block';
                errorLongitud.style.display = 'none';
                return false;
            }

            // Validar número de palabras
            const palabras = valor.split(/\s+/); // Divide por espacios
            if (palabras.length > maxPalabras) {
                errorLongitud.style.display = 'block';
                errorMensaje.style.display = 'none';
                return false;
            }

            // Si es válido
            errorMensaje.style.display = 'none';
            errorLongitud.style.display = 'none';
            return true;
        };

        // Eventos en tiempo real
        nombreInput.addEventListener('input', () => {
            validarCampo(nombreInput, errorNombre, longN, 3);
        });

        apellidoInput.addEventListener('input', () => {
            validarCampo(apellidoInput, errorApellido, longA, 3);
        });

        // Validación al enviar el formulario
        form.addEventListener('submit', (e) => {
            const nombreValido = validarCampo(nombreInput, errorNombre, longN, 3);
            const apellidoValido = validarCampo(apellidoInput, errorApellido, longA, 3);

            // Bloquear el envío si hay errores
            if (!nombreValido || !apellidoValido) {
                e.preventDefault();
                alert("Por favor, corrige los errores antes de enviar.");
            }
        });
    });
</script>

   
<!-- Formulario de eliminación -->
<div class="form-section" id="delete-form">
    <h2>Eliminar Directivo</h2>
    <form method="POST" id="deleteDirectivoForm">
        <label for="cod_directivo_delete">Código del Directivo:</label>
        <input type="text" name="cod_directivo_delete" id="cod_directivo_delete" required 
               pattern="^[1-9]\d*$" title="Debe ser un número entero positivo" placeholder="Ej: 123">
        <br>
        <button type="submit" name="delete">Eliminar</button>
    </form>
</div>

<script>
    // Variables de referencia
    const campoCodigoDelete = document.getElementById('cod_directivo_delete');
    const formularioEliminar = document.getElementById('deleteDirectivoForm');

    // Expresión regular para validar enteros positivos
    const regexSoloNumerosPositivos = /^[1-9]\d*$/; // Solo números enteros positivos

    // Validación al enviar el formulario
    formularioEliminar.addEventListener('submit', (e) => {
        const valorCodigoDelete = campoCodigoDelete.value.trim();

        // Validar que el valor ingresado sea un número entero positivo
        if (!regexSoloNumerosPositivos.test(valorCodigoDelete)) {
            e.preventDefault();
            alert("El código debe ser un número entero positivo.");
            return;
        }
    });

    // Validación en tiempo real para el campo de código del directivo
    if (campoCodigoDelete) {
        campoCodigoDelete.addEventListener('input', () => {
            const valorCodigo = campoCodigoDelete.value.trim();
            if (regexSoloNumerosPositivos.test(valorCodigo)) {
                campoCodigoDelete.setCustomValidity('');
            } else {
                campoCodigoDelete.setCustomValidity('El código debe ser un número entero positivo.');
            }
        });
    }
</script>


</div>
<div class="form-container2">
 <!-- Formulario de actualización -->
 <div class="form-section" id="update-form">
        <h2>Actualizar Directivo</h2>
        <form method="POST" id="updateDirectivoForm">
        <!-- Si aún no se ha verificado el código, mostrar el campo para el código -->
        <?php if (!isset($_SESSION['cod_directivo'])): ?>
            <label for="cod_directivo_update">Código del Directivo:</label>
            <input type="text" id= "cod_directivo_update" name="cod_directivo_update" required>
            <p class="error-message" id="codigoError" style="display:none; color:red;">Solo se permiten números positivos.</p>
            <br>
            <button type="submit" name="update">Siguiente</button>
        <?php else: ?>
            <!-- Si ya se verificó el código, mostrar los campos de actualización -->
            <label for="cod_directivo_update">Código:</label>
            <input type="text" name="cod_directivo_update" id="cod_directivo_update" value="<?php echo $_SESSION['cod_directivo']; ?>" readonly>
            <br>
            <label for="nomb_directivo_update">Nombres:</label>
            <input type="text" id="nomb_directivo_update" name="nomb_directivo_update" value="<?php echo $_SESSION['nomb_directivo']; ?>" required style="text-transform:uppercase;">
            <p class="error-message" id="nombreError" style="display:none; color:red;">Solo se permiten letras y máximos 3 nombres.</p>
            <br>
            <label for="apell_directivo_update">Apellidos:</label>
            <input type="text" id="apell_directivo_update" name="apell_directivo_update" value="<?php echo $_SESSION['apell_directivo']; ?>" required style="text-transform:uppercase;">
            <p class="error-message" id="apellidoError" style="display:none; color:red;">Solo se permiten letras y máximos 3 apellidos.</p>
            <br>
            <button type="submit" name="update_directivo">Actualizar</button>
        <?php endif; ?>
   
     </form>
</div>
<script>    
    // Variables de referencia
    const campoCodigo = document.getElementById('cod_directivo_update');
    const campoNombre = document.getElementById('nomb_directivo_update');
    const campoApellido = document.getElementById('apell_directivo_update');
    const mensajeCodigoError = document.getElementById('codigoError');
    const mensajeNombreError = document.getElementById('nombreError');
    const mensajeApellidoError = document.getElementById('apellidoError');
    const formularioActualizar = document.getElementById('updateDirectivoForm');

    // Expresiones regulares
    const regexSoloNumeros = /^[1-9]\d*$/; // Solo números positivos
    const regexSoloLetras = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/; // Solo letras y espacios
    const regexSinEspacios = /^(?!\s*$).+/; // No permitir solo espacios (al menos un caracter)
    const regexMaximoCadenas = /^(\S+\s*){1,3}$/; // Permitir 1 a 3 palabras, separadas por espacios

    // Validar el código (solo números positivos y no vacío)
    if (campoCodigo) {
        campoCodigo.addEventListener('input', () => {
            const valorCodigo = campoCodigo.value.trim();
            if (regexSoloNumeros.test(valorCodigo) && regexSinEspacios.test(valorCodigo)) {
                mensajeCodigoError.style.display = 'none';
            } else {
                mensajeCodigoError.style.display = 'block';
            }
        });
    }

    // Validar el nombre (solo letras, no vacío, máximo 3 palabras)
    if (campoNombre) {
        campoNombre.addEventListener('input', () => {
            const valorNombre = campoNombre.value.trim();
            if (regexSoloLetras.test(valorNombre) && regexSinEspacios.test(valorNombre) && regexMaximoCadenas.test(valorNombre)) {
                mensajeNombreError.style.display = 'none';
            } else {
                mensajeNombreError.style.display = 'block';
            }
        });
    }

    // Validar el apellido (solo letras, no vacío, máximo 3 palabras)
    if (campoApellido) {
        campoApellido.addEventListener('input', () => {
            const valorApellido = campoApellido.value.trim();
            if (regexSoloLetras.test(valorApellido) && regexSinEspacios.test(valorApellido) && regexMaximoCadenas.test(valorApellido)) {
                mensajeApellidoError.style.display = 'none';
            } else {
                mensajeApellidoError.style.display = 'block';
            }
        });
    }

    // Validación al enviar el formulario
    formularioActualizar.addEventListener('submit', (e) => {
        if (campoCodigo) {
            const valorCodigo = campoCodigo.value.trim();
            if (!regexSoloNumeros.test(valorCodigo) || !regexSinEspacios.test(valorCodigo)) {
                e.preventDefault();
                alert("El código debe ser un número positivo válido.");
                return;
            }
        }

        if (campoNombre) {
            const valorNombre = campoNombre.value.trim();
            if (!regexSoloLetras.test(valorNombre) || !regexSinEspacios.test(valorNombre) || !regexMaximoCadenas.test(valorNombre)) {
                e.preventDefault();
                alert("El nombre debe contener solo letras, no puede estar vacío y un máximo de 3 nombres.");
                return;
            }
        }

        if (campoApellido) {
            const valorApellido = campoApellido.value.trim();
            if (!regexSoloLetras.test(valorApellido) || !regexSinEspacios.test(valorApellido) || !regexMaximoCadenas.test(valorApellido)) {
                e.preventDefault();
                alert("El apellido debe contener solo letras, no puede estar vacío y un máximo de 3 apellidos.");
                return;
            }
        }
    });
</script>
</div>
</div>

<script>
        document.addEventListener('DOMContentLoaded', () => {
            
    // Referencias a los enlaces y formularios
    const linkCreate = document.getElementById('link-create');
    const linkUpdate = document.getElementById('link-update');
    const linkDelete = document.getElementById('link-delete');
    const createForm = document.getElementById('create-form');
    const updateForm = document.getElementById('update-form');
    const deleteForm = document.getElementById('delete-form');


  // Función para mostrar un formulario y ocultar los demás
  const mostrarFormulario = (formularioSeleccionado) => {
                [createForm, updateForm, deleteForm].forEach(formulario => {
                    if (formulario === formularioSeleccionado) {
                        formulario.classList.add('active');
                    } else {
                        formulario.classList.remove('active');
                    }
                });
            };

   
    // Eventos para mostrar formularios
    linkCreate.addEventListener('click', (e) => {
        e.preventDefault();
        mostrarFormulario(createForm);
    });

    linkUpdate.addEventListener('click', (e) => {
        e.preventDefault();
        mostrarFormulario(updateForm);
    });

    linkDelete.addEventListener('click', (e) => {
        e.preventDefault();
        mostrarFormulario(deleteForm);
    });
           // Verificar si debe mantenerse el formulario de Actualizar abierto
    const mantenerAbierto = <?= isset($_POST['siguiente']) || isset($_SESSION['cod_directivo']) ? 'true' : 'false'; ?>;
    if (mantenerAbierto) {
        mostrarFormulario(updateForm);
    }
        });
    </script>
</body>

</html>


<?php
}
?>
