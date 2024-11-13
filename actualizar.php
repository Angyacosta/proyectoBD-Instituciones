<?php
include 'conexionDB.php';
if (php_sapi_name() === 'cli') {
}else {
// Definir cuántos registros mostrar por página
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Obtener la página actual, por defecto es la 1
$offset = ($page - 1) * $limit; // Calcular el desplazamiento de la consulta

// Obtener laas instituciones limitados
$sql = "SELECT * FROM inst_por_municipio ORDER BY cod_inst ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$limit, $offset]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener el total de instituciones para calcular las páginas
$sql_count = "SELECT COUNT(*) FROM inst_por_municipio";
$stmt_count = $conn->query($sql_count);
$total_inst = $stmt_count->fetchColumn();
$total_pages = ceil($total_inst / $limit); // Calcular el total de páginas
?>
<?php
include 'inststyle.html';
    session_start();
    $form_display = false;
// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $cod_inst = $_POST['cod_inst_update'];
        // Verificar si existe la combinación cod_inst + cod_munic
        $sql_check = "SELECT COUNT(*) FROM inst_por_municipio WHERE cod_inst = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([$cod_inst]);
        $exists = $stmt_check->fetchColumn();

        if ($exists == 0) {
            // Si el código no existe, mostrar mensaje de error
            echo "<script>alert('La institución no existen. Intenta de nuevo');</script>";
        } else{
            // Si el código existe, obtener los datos los datos de la institucion
            $sql_get = "SELECT cod_munic,direccion, telefono, norma, fecha_creacion, 
            programas_vigente, programas_convenio, acreditada, fecha_acreditacion,
            resolucion_acreditacion, vigencia, nit, pagina_web FROM inst_por_municipio 
            WHERE cod_inst = ?";
            $stmt_get = $conn->prepare($sql_get);
            $stmt_get->execute([$cod_munic]);
            $institucion = $stmt_get->fetch(PDO::FETCH_ASSOC);
            // Guardar el código y los datos en la sesión para mantener el estado
            $_SESSION['cod_inst'] = $cod_inst;
            $_SESSION['cod_munic'] = $cod_munic['cod_munic'];
            $_SESSION['direccion'] = $institucion['direccion'];
            $_SESSION['telefono'] = $institucion['telefono'];
            $_SESSION['norma'] = $institucion['norma'];
            $_SESSION['fecha_creacion'] = $institucion['fecha_creacion'];
            $_SESSION['programas_vigente'] = $institucion['programas_vigente'];
            $_SESSION['programas_convenio'] = $institucion['programas_convenio'];
            $_SESSION['acreditada'] = $institucion['acreditada'];
            $_SESSION['fecha_acreditacion'] = $institucion['fecha_acreditacion'];
            $_SESSION['resolucion_acreditacion'] = $institucion['resolucion_acreditacion'];
            $_SESSION['vigencia'] = $institucion['vigencia'];
            $_SESSION['nit'] = $institucion['nit'];
            $_SESSION['pagina_web'] = $institucion['pagina_web'];
// Cambiar a verdadero para mostrar el formulario de actualización
$_SESSION['form_display'] = true;
            }
        }if (isset($_POST['update_inst'])) {
        
        $cod_munic= $_POST['cod_munic_update'];    
        $direccion = $_POST['direccion_update'];
        $telefono= $_POST['telefono_update'];
        $norma= $_POST['norma_update'];
        $fecha_creacion = $_POST['fecha_creacion_update'];
        $programas_vigente = $_POST['programas_vigente_update'];
        $programas_convenio = $_POST['programas_convenio_update'];
        $acreditada = $_POST['acreditada_update'];
        $fecha_acreditacion = $_POST['fecha_acredtacion_update'];
        $resolucion_acreditacion= $_POST['resolucion_acreditacion_update'];
        $vigencia = $_POST['vigencia_update'];
        $nit= $_POST['nit_update'];
        $pagina_web= $_POST['pagina_web_update'];
        // Validaciones de fechas  creacion,acreditacion y vigencia 
        if (strtotime($fecha_acreditacion) < strtotime($fecha_creacion)) {
            echo "<script>alert('La fecha de acreditación no puede ser menor que la de creación.');</script>";
            exit;
        }
          // Verificar longitud del telefono
    if (strlen($telefono) > 10 ) {
        echo "<script>alert('El telefono debe tener máximo 10 digitos. Intenta de nuevo.');</script>";
    } 
    // Verificar si el municipio existe

    $sql_check = "SELECT COUNT(*) FROM municipios WHERE cod_munic = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([$cod_munic]);
    $exist = $stmt_check->fetchColumn();
if ($exist = 0) {
        echo "<script>alert('El municipio no existe. Intenta con un código diferente.');</script>";
    }

    if (!empty($_POST['cod_munic'])) {
        $cod_munic = $_POST['cod_munic']; // Capturar el código enviado por POST
    
        // Validar formato del código (opcional, según tus requisitos)
        if (!ctype_digit($cod_munic)) {
            echo json_encode(['existe' => false, 'error' => 'El código debe ser numérico.']);
            exit;
        }
    
        // Consulta para verificar si el código existe
        $stmt = $conn->prepare("SELECT COUNT(*) FROM municipios WHERE cod_munic = :cod_munic");
        $stmt->bindParam(':cod_munic', $cod_munic, PDO::PARAM_STR);
        $stmt->execute();
    
        // Obtener si existe el municipio
        $existe = $stmt->fetchColumn() > 0;
    
        // Devolver el resultado como JSON
        echo json_encode(['existe' => $existe]);
    } else {
        // Si no se envía el código, devolver un error
        echo json_encode(['existe' => false, 'error' => 'No se envió el código de municipio.']);
    }
    

        // Si hay errores, devolver al formulario con los datos
        if (count($errores) > 0) {
            $_SESSION['errores'] = $errores;
            $_SESSION['form_data'] = $_POST;  // Almacenar los datos en sesión para restaurarlos
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

            // Actualizar el directivo en la base de datos
        $sql_update = "UPDATE inst_por_municipio SET COD_MUNIC=?, direccion= ?, telefono = ?, norma=?, 
        fecha_creacion=?, programas_vigente=?, programas_convenio=?, acreditada=?, fecha_acreditacion=?,
        resolucion_acreditacion=?, vigencia=?, nit=?, pagina_web=?  WHERE cod_inst = ?";
        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update->execute([$cod_munic,$direccion, $telefono, $norma, $fecha_creacion, 
        $programas_vigente, $programas_convenio, $acreditada, $fecha_acreditacion, $resolucion_acreditacion,
        $vigencia, $nit, $pagina_web,$_SESSION['cod_inst']])) {
            echo "<script>
                    alert('institucion actualizado con éxito.');
                    window.location.href = window.location.href; // Recarga la página
                  </script>";
                  session_unset();
                  session_destroy();
        } else {
            echo "<p>Error al actualizar la institucion.</p>";
        }
    }
}
 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Instituciones</title>
</head>
<body>
<div class="container-wrapper">  
<div class="container2">
    <div class="update-link">
    <a href="inicio.php" class="update-btn">volver a inicio</nav></a>
    </div>
<!-- Tabla para mostrar las instituciones -->
    <h2>Instituciones</h2>
    <table>
        <thead>
            <tr>
                <th>Código de institucion</th>
                <th>codigo de municipio</th>
                <th>Dirección</th>
                <th>Teléfono</th>
                <th>norma</th>
                <th>fecha de creacion</th>
                <th>programas vigentes</th>
                <th>programas en convenio </th>
                <th>acreditada</th>
                <th>fecha de acreditacion</th>
                <th>resolucion de acreditacion</th>
                <th>vigencia</th>
                <th>nit</th>
                <th>pagina web</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registros as $registro): ?>
            <tr>
                <td><?php echo htmlspecialchars($registro['cod_inst']); ?></td>
                <td><?php echo htmlspecialchars($registro['cod_munic']); ?></td>
                <td><?php echo htmlspecialchars($registro['direccion']); ?></td>
                <td><?php echo htmlspecialchars($registro['telefono']); ?></td>
                <td><?php echo htmlspecialchars($registro['norma']); ?></td>
                <td><?php echo htmlspecialchars($registro['fecha_creacion']); ?></td>
                <td><?php echo htmlspecialchars($registro['programas_vigente']); ?></td>
                <td><?php echo htmlspecialchars($registro['programas_convenio']); ?></td>
                <td><?php echo htmlspecialchars($registro['acreditada']); ?></td>
                <td><?php echo htmlspecialchars($registro['fecha_acreditacion']); ?></td>
                <td><?php echo htmlspecialchars($registro['resolucion_acreditacion']); ?></td>
                <td><?php echo htmlspecialchars($registro['vigencia']); ?></td>
                <td><?php echo htmlspecialchars($registro['nit']); ?></td>
                <td><?php echo htmlspecialchars($registro['pagina_web']); ?></td>
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
            <a href="?page=<?php echo $i; ?>" <?php echo ($i == $page) ? :''; ?>><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Siguiente</a>
        <?php endif; ?>
    </div>
</div>

<!-- Contenedor para el formulario -->
<div class="container">
    <h2>Actualizar Institución</h2>
    <form method="POST" action="">
        <!-- Si ya se verificó el código de la institución, mostrar los campos de actualización -->
        <?php if (!isset($_SESSION['cod_inst']) ):  ?>
        <label for="cod_inst_update">Código de la Institución:</label>
        <input type="text" name="cod_inst_update" value="<?= isset($_SESSION['form_data']['cod_inst_update']) ? $_SESSION['form_data']['cod_inst_update'] : '' ?>" required>
        <br>
        <button type="submit" name="update">Siguiente</button>

            <?php else: ?>
            <!--fromulario para actulizar -->
            <label for="cod_inst_update">Código de la Institución:</label>
            <input type="text" name="cod_inst_update" value="<?php echo $_SESSION['cod_inst']; ?>" readonly>
            <br>

                <label for="cod_munic_update">Código del Municipio:</label>
                <input type="text" id= "cod_munic_update" name="cod_munic_update" value="<?= isset($_SESSION['form_data']['cod_munic_update']) ? $_SESSION['form_data']['cod_munic_update'] : $_SESSION['cod_munic'] ?>" required>
                <br>
                <p class="error-mesagge" id="errorMunicipio" style="color: red; display: none;">El código ingresado no existe.</p>
                <p class="error-mesagge" id="successMunicipio" style="color: green; display: none;">Código válido.</p>

            <label for="direccion_update">Dirección:</label>
        <input type="text" name="direccion_update" value="<?= isset($_SESSION['form_data']['direccion_update']) ? $_SESSION['form_data']['direccion_update'] : $_SESSION['direccion'] ?>" required>
        <br>

        <label for="telefono_update">Teléfono:</label>
        <input type="text" name="telefono_update" value="<?= isset($_SESSION['form_data']['telefono_update']) ? $_SESSION['form_data']['telefono_update'] : $_SESSION['telefono'] ?>" required>
        <br>    
            <label for="norma_update">Norma:</label>
            <input type="text" min = "0" id="norma_update" name="norma_update" value="<?= isset($_SESSION['form_data']['norma_update']) ? $_SESSION['form_data']['norma_update'] : $_SESSION['norma'] ?>" required>
            <br>
            <p class="error-message" id="errornorma">debe ser una cadena de solo números y positivos.</p>
            <p class="error-message" id="errorEspacio" style="display: none; color: red;">No se permiten espacios.</p>
            <p id="successNorma" style="display: none; color: green;">¡Valor válido!</p>
        <label for="fecha_creacion_update">Fecha de Creación:</label>
        <input type="date" name="fecha_creacion_update" value="<?= isset($_SESSION['form_data']['fecha_creacion_update']) ? $_SESSION['form_data']['fecha_creacion_update'] : $_SESSION['fecha_creacion'] ?>" required>
        <br>
        <label for="programas_vigente_update">Programas Vigentes:</label>
        <input type="text" min="0" id="programas_vigente_update" name="programas_vigente_update" value="<?= isset($_SESSION['form_data']['programas_vigente_update']) ? $_SESSION['form_data']['programas_vigente_update'] : $_SESSION['programas_vigente'] ?>" required>
        <br>
        <p class="error-message" id="errorvigentes">debe ser un número y positivo,no se permiten letras ni caracteres.</p>
        <p class="error-message" id="errorEspacio2" style="display: none; color: red;">No se permiten espacios.</p>
        <p id="successProgramas" style="display: none; color: green;">¡Valor válido!</p> 

        <label for="programas_convenio_update">Programas En Convenio:</label>
        <input type="text" min="0" id = "programas_convenio_update" name="programas_convenio_update" value="<?= isset($_SESSION['form_data']['programas_convenio_update']) ? $_SESSION['form_data']['programas_convenio_update'] : $_SESSION['programas_convenio'] ?>" required>
        <br>
        <p class="error-message" id="errorConvenio">debe ser un número y positivo,no se permiten letras ni caracteres.</p>
        <p class="error-message" id="errorEspacioConvenio" style="display: none; color: red;">No se permiten espacios.</p>
        <p id="successConvenio" style="display: none; color: green;">¡Valor válido!</p> 

            <label for="acreditada_update">¿Acreditada?:</label>
            <select name="acreditada_update" required>
            <option value="Sí" <?= (isset($_SESSION['form_data']['acreditada_update']) && $_SESSION['form_data']['acreditada_update'] == 'Sí') ? 'selected' : ($_SESSION['acreditada'] == 'Sí' ? 'selected' : '') ?>>Sí</option>
            <option value="No" <?= (isset($_SESSION['form_data']['acreditada_update']) && $_SESSION['form_data']['acreditada_update'] == 'No') ? 'selected' : ($_SESSION['acreditada'] == 'No' ? 'selected' : '') ?>>No</option>
        </select>
            <br>

            <label for="fecha_acreditacion_update">Fecha de Acreditación:</label>
        <input type="date" name="fecha_acreditacion_update" value="<?= isset($_SESSION['form_data']['fecha_acreditacion_update']) ? $_SESSION['form_data']['fecha_acreditacion_update'] : $_SESSION['fecha_acreditacion'] ?>" <?= $_SESSION['acreditada'] == 'No' ? 'disabled' : '' ?>>
        <br>

        <label for="resolucion_acreditacion_update">Resolución de Acreditación:</label>
        <input type="text" min = "0" id= resolucion_acreditacion_update name="resolucion_acreditacion_update" value="<?= isset($_SESSION['form_data']['resolucion_acreditacion_update']) ? $_SESSION['form_data']['resolucion_acreditacion_update'] : $_SESSION['resolucion_acreditacion'] ?>" required>
        <br>
        <p class="error-message" id="errorAcreditacion">debe ser un número y positivo,no se permiten letras ni caracteres.</p>
        <p class="error-message" id="errorEspacioAcreditacion" style="display: none; color: red;">No se permiten espacios.</p>
        <p id="successAcreditacion" style="display: none; color: green;">¡Valor válido!</p> 

        <label for="vigencia_update">Vigencia:</label>
        <input type="text" min="0" id="vigencia_update" name="vigencia_update" value="<?= isset($_SESSION['form_data']['vigencia_update']) ? $_SESSION['form_data']['vigencia_update'] : $_SESSION['vigencia'] ?>" required>
        <br>
        <p class="error-message" id="errorVigencia">debe ser un número y positivo,no se permiten letras ni caracteres.</p>
        <p class="error-message" id="errorEspacioVigencia" style="display: none; color: red;">No se permiten espacios.</p>
        <p id="successVigencia" style="display: none; color: green;">¡Valor válido!</p> 

        
        <label for="nit_update">NIT:</label>
        <input type="text" name="nit_update" value="<?= isset($_SESSION['form_data']['nit_update']) ? $_SESSION['form_data']['nit_update'] : $_SESSION['nit'] ?>" required>
        <br>

        <label for="pagina_web_update">Página Web:</label>
        <input type="url" name="pagina_web_update" value="<?= isset($_SESSION['form_data']['pagina_web_update']) ? $_SESSION['form_data']['pagina_web_update'] : $_SESSION['pagina_web'] ?>" required>
        <br>


            <button type="submit" name="update_inst">Actualizar Institución</button>
<!---------------------validaciones---------------------------------------------->
           <script>

    //--------------------------validacion NORMA
        // Obtener referencias al input y a los mensajes
        const normaInput = document.getElementById('norma_update');
        const errorMessage = document.getElementById('errornorma');
        const successMessage = document.getElementById('successNorma');
        const spaceErrorMessage = document.getElementById('errorEspacio');

// Evitar la tecla de espacio
normaInput.addEventListener('keydown', function (event) {
    if (event.code === 'Space') {
        event.preventDefault(); // Bloquear la tecla de espacio
        spaceErrorMessage.style.display = 'block'; // Mostrar mensaje de espacio
        successMessage.style.display = 'none'; // Esconder mensaje de éxito
        errorMessage.style.display = 'none'; // Esconder mensaje de error

        // Esconder el mensaje de espacio después de 3 segundos
        setTimeout(() => {
            spaceErrorMessage.style.display = 'none';
            // Si el campo es válido y no está vacío, mostrar el mensaje de éxito
            validateNormaInput();
        }, 2000); // 2000 ms = 2 segundos
    }
});

   // Validar el campo en tiempo real
normaInput.addEventListener('input', validateNormaInput);

function validateNormaInput() {
    const normaValue = normaInput.value.trim(); // Eliminar espacios antes/después del texto
    
    if (normaValue === '') {
        // Si el campo está vacío, ocultar todos los mensajes
        errorMessage.style.display = 'none';
        successMessage.style.display = 'none';
        spaceErrorMessage.style.display = 'none';
    } 
    // Verificar si es un número entero positivo o cero
    else if (!/^\d+$/.test(normaValue)) {
        // Mostrar mensaje de error si contiene letras, caracteres especiales o múltiples cadenas
        errorMessage.style.display = 'block';
        successMessage.style.display = 'none';
        spaceErrorMessage='none';
    } else {
        // Mostrar mensaje de éxito si es válido
        errorMessage.style.display = 'none';
        successMessage.style.display = 'block';
        spaceErrorMessage='none';
    }

    }
        // Prevenir el envío del formulario si hay un error
        document.getElementById('normaForm').addEventListener('submit', function (e) {
            const normaValue = parseInt(normaInput.value);

            if (normaValue < 0 || !/^\d+$/.test(normaValue)) {
                e.preventDefault(); // Prevenir envío
                alert("Por favor, corrige los errores antes de enviar.");
            }
        });
    </script>  
    <script>
            //-----------------------VALIDAR PROGRAMAS VIGENTES ---------------------------------
    // Obtener referencias al input y a los mensajes
    const programasInput = document.getElementById('programas_vigente_update');
    const errorMessage2 = document.getElementById('errorvigentes');
    const spaceErrorMessage2 = document.getElementById('errorEspacio2');
    const successMessage2 = document.getElementById('successProgramas');

    // Evitar la tecla de espacio
programasInput.addEventListener('keydown', function (event) {
    if (event.code === 'Space') {
        event.preventDefault(); // Bloquear la tecla de espacio
        spaceErrorMessage2.style.display = 'block'; // Mostrar mensaje de error por espacio
        errorMessage2.style.display = 'none'; // Esconder el mensaje de número inválido
        successMessage2.style.display = 'none'; // Esconder el mensaje de éxito

        // Esconder el mensaje de espacio después de 3 segundos
        setTimeout(() => {
            spaceErrorMessage2.style.display = 'none'; 
           validateProgramaInput();
    }, 2000); // 2000 ms = 2 segundos
    }
});



programasInput.addEventListener('input', validateProgramaInput);
    function validateProgramaInput() {

    const programasValue = programasInput.value.trim(); // Eliminar espacios al inicio y final
    
    if (programasValue === '') {
        // Si el campo está vacío, ocultar todos los mensajes
        errorMessage2.style.display = 'none';
        successMessage2.style.display = 'none';
        spaceErrorMessage2.style.display = 'none';
    } 
    // Verificar si el valor contiene letras o caracteres no válidos
    else if (!/^\d+$/.test(programasValue)) {
        errorMessage2.style.display = 'block'; // Mostrar mensaje de error si no es un número válido
        successMessage2.style.display = 'none'; // Esconder el mensaje de éxito
        spaceErrorMessage2.style.display = 'none'; // Esconder mensaje de espacio
    } // Verificar si el campo está vacío
    // Si el valor es válido (solo números)
    else {
        // Mostrar mensaje de éxito si es válido
        errorMessage2.style.display = 'none';
        successMessage2.style.display = 'block';
        spaceErrorMessage2.style.display = 'none'; // Esconder mensaje de espacio
    }
}

    // Prevenir el envío del formulario si hay un error
    document.getElementById('programasForm').addEventListener('submit', function (e) {
        const programasValue = programasInput.value.trim();

        // Verificar si el valor está vacío o no es un número positivo
        if (programasValue < 0 || !/^\d+$/.test(programasValue)) {
            e.preventDefault(); // Prevenir envío
            alert("Por favor, corrige los errores antes de enviar.");
        }
    });

    </script>
        <script>
        //-----------------------VALIDAR PROGRAMAS EN CONVENIO ---------------------------------
        // Obtener referencias al input y a los mensajes
        const programasConvenioInput = document.getElementById('programas_convenio_update');
        const errorMessageConvenio = document.getElementById('errorConvenio');
        const spaceErrorMessageConvenio = document.getElementById('errorEspacioConvenio');
        const successMessageConvenio = document.getElementById('successConvenio');

        // Evitar la tecla de espacio
        programasConvenioInput.addEventListener('keydown', function (event) {
            if (event.code === 'Space') {
                event.preventDefault(); // Bloquear la tecla de espacio
                spaceErrorMessageConvenio.style.display = 'block'; // Mostrar mensaje de error por espacio
                errorMessageConvenio.style.display = 'none'; // Esconder el mensaje de número inválido
                successMessageConvenio.style.display = 'none'; // Esconder el mensaje de éxito

                // Esconder el mensaje de espacio después de 3 segundos
                setTimeout(() => {
                    spaceErrorMessageConvenio.style.display = 'none'; 
                    ValidarConvenioInput();
                }, 2000); // 2000 ms = 2 segundos
            }
        });

        // Validar el campo en tiempo real
        programasConvenioInput.addEventListener('input', ValidarConvenioInput);
        function ValidarConvenioInput(){

            const programasValue = programasConvenioInput.value.trim(); // Eliminar espacios al inicio y final
            // Verificar si el campo está vacío
            if (programasValue === '') {
                // Si el campo está vacío, ocultar todos los mensajes
                errorMessageConvenio.style.display = 'none';
                successMessageConvenio.style.display = 'none';
                spaceErrorMessageConvenio.style.display = 'none';
            } 
            // Verificar si el valor contiene letras o caracteres no válidos
            else if (!/^\d+$/.test(programasValue)) {
                errorMessageConvenio.style.display = 'block'; // Mostrar mensaje de error si no es un número válido
                successMessageConvenio.style.display = 'none'; // Esconder el mensaje de éxito
                spaceErrorMessageConvenio.style.display = 'none'; // Esconder mensaje de espacio
            } 
            // Si el valor es válido (solo números)
            else {
                // Mostrar mensaje de éxito si es válido
                errorMessageConvenio.style.display = 'none';
                successMessageConvenio.style.display = 'block';
                spaceErrorMessageConvenio.style.display = 'none'; // Esconder mensaje de espacio
            }
        }

        // Prevenir el envío del formulario si hay un error
        document.getElementById('programasConvenioForm').addEventListener('submit', function (e) {
            const programasValue = programasConvenioInput.value.trim();

            // Verificar si el valor está vacío o no es un número positivo
            if (programasValue < 0 || !/^\d+$/.test(programasValue)) {
                e.preventDefault(); // Prevenir envío
                alert("Por favor, corrige los errores antes de enviar.");
            }
        });
    </script>
           <script>
    //-----------------------VALIDAR RESOLUCIÓN DE ACREDITACIÓN ---------------------------------
    // Obtener referencias al input y a los mensajes
    const resolucionAcreditacionInput = document.getElementById('resolucion_acreditacion_update');
    const errorMessageAcreditacion = document.getElementById('errorAcreditacion');
    const spaceErrorMessageAcreditacion = document.getElementById('errorEspacioAcreditacion');
    const successMessageAcreditacion = document.getElementById('successAcreditacion');

    // Evitar la tecla de espacio
    resolucionAcreditacionInput.addEventListener('keydown', function (event) {
        if (event.code === 'Space') {
            event.preventDefault(); // Bloquear la tecla de espacio
            spaceErrorMessageAcreditacion.style.display = 'block'; // Mostrar mensaje de error por espacio
            errorMessageAcreditacion.style.display = 'none'; // Esconder el mensaje de número inválido
            successMessageAcreditacion.style.display = 'none'; // Esconder el mensaje de éxito

            // Esconder el mensaje de espacio después de 3 segundos
            setTimeout(() => {
                spaceErrorMessageAcreditacion.style.display = 'none'; 
                validarResolucionInput();
            }, 2000); // 2000 ms = 2 segundos
        }
    });

    // Validar el campo en tiempo real
    resolucionAcreditacionInput.addEventListener('input', validarResolucionInput);
    function validarResolucionInput() {

        const resolucionValue = resolucionAcreditacionInput.value.trim(); // Eliminar espacios al inicio y final
        
         // Verificar si el campo está vacío
        if (resolucionValue === '') {
            // Si el campo está vacío, ocultar todos los mensajes
            errorMessageAcreditacion.style.display = 'none';
            successMessageAcreditacion.style.display = 'none';
            spaceErrorMessageAcreditacion.style.display = 'none';
        } 
        // Verificar si el valor contiene letras o caracteres no válidos
        else if (!/^\d+$/.test(resolucionValue)) {
            errorMessageAcreditacion.style.display = 'block'; // Mostrar mensaje de error si no es un número válido
            successMessageAcreditacion.style.display = 'none'; // Esconder el mensaje de éxito
            spaceErrorMessageAcreditacion.style.display = 'none'; // Esconder mensaje de espacio
        } 
        // Si el valor es válido (solo números)
        else {
            // Mostrar mensaje de éxito si es válido
            errorMessageAcreditacion.style.display = 'none';
            successMessageAcreditacion.style.display = 'block';
            spaceErrorMessageAcreditacion.style.display = 'none'; // Esconder mensaje de espacio
        }
    }

    // Prevenir el envío del formulario si hay un error
    document.getElementById('resolucionAcreditacionForm').addEventListener('submit', function (e) {
        const resolucionValue = resolucionAcreditacionInput.value.trim();

        // Verificar si el valor está vacío o no es un número positivo
        if (resolucionValue < 0 || !/^\d+$/.test(resolucionValue)) {
            e.preventDefault(); // Prevenir envío
            alert("Por favor, corrige los errores antes de enviar.");
        }
    });
</script>
<script>
    //-----------------------VALIDAR VIGENCIA ---------------------------------
    // Obtener referencias al input y a los mensajes
    const vigenciaInput = document.getElementById('vigencia_update');
    const errorMessageVigencia = document.getElementById('errorVigencia');
    const spaceErrorMessageVigencia = document.getElementById('errorEspacioVigencia');
    const successMessageVigencia = document.getElementById('successVigencia');

    // Evitar la tecla de espacio
    vigenciaInput.addEventListener('keydown', function (event) {
        if (event.code === 'Space') {
            event.preventDefault(); // Bloquear la tecla de espacio
            spaceErrorMessageVigencia.style.display = 'block'; // Mostrar mensaje de error por espacio
            errorMessageVigencia.style.display = 'none'; // Esconder el mensaje de número inválido
            successMessageVigencia.style.display = 'none'; // Esconder el mensaje de éxito

            // Esconder el mensaje de espacio después de 3 segundos
            setTimeout(() => {
                spaceErrorMessageVigencia.style.display = 'none'; 
                validarVigenciaInput();
            }, 2000); // 2000 ms = 2 segundos
        }
    });

    // Validar el campo en tiempo real
    vigenciaInput.addEventListener('input', validarVigenciaInput);
    function validarVigenciaInput() {

        const vigenciaValue = vigenciaInput.value.trim(); // Eliminar espacios al inicio y final
        
          // Verificar si el campo está vacío
        if (vigenciaValue === '') {
            // Si el campo está vacío, ocultar todos los mensajes
            errorMessageVigencia.style.display = 'none';
            successMessageVigencia.style.display = 'none';
            spaceErrorMessageVigencia.style.display = 'none';
        } 
        // Verificar si el valor contiene letras o caracteres no válidos
        else if (!/^\d+$/.test(vigenciaValue)) {
            errorMessageVigencia.style.display = 'block'; // Mostrar mensaje de error si no es un número válido
            successMessageVigencia.style.display = 'none'; // Esconder el mensaje de éxito
            spaceErrorMessageVigencia.style.display = 'none'; // Esconder mensaje de espacio
        } 
        // Si el valor es válido (solo números)
        else {
            // Mostrar mensaje de éxito si es válido
            errorMessageVigencia.style.display = 'none';
            successMessageVigencia.style.display = 'block';
            spaceErrorMessageVigencia.style.display = 'none'; // Esconder mensaje de espacio
        }
    }

    // Prevenir el envío del formulario si hay un error
    document.getElementById('vigenciaForm').addEventListener('submit', function (e) {
        const vigenciaValue = vigenciaInput.value.trim();

        // Verificar si el valor está vacío o no es un número positivo
        if (vigenciaValue < 0 || !/^\d+$/.test(vigenciaValue)) {
            e.preventDefault(); // Prevenir envío
            alert("Por favor, corrige los errores antes de enviar.");
        }
    });
</script>
<script>
   const municipioInput = document.getElementById('cod_munic_update');
        const errorMunicipio = document.getElementById('errorMunicipio');
        const successMunicipio = document.getElementById('successMunicipio');

        municipioInput.addEventListener('input', function () {
            const codigoMunicipio = municipioInput.value.trim();

            // Si el campo está vacío, ocultar los mensajes
            if (codigoMunicipio === '') {
                successMunicipio.style.display = 'none';
                errorMunicipio.style.display = 'none';
                return;
            }

            // Enviar el código al servidor para validación (el mismo archivo PHP)
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ cod_munic: codigoMunicipio }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.existe) {
                    successMunicipio.style.display = 'block';
                    errorMunicipio.style.display = 'none';
                } else {
                    errorMunicipio.style.display = 'block';
                    successMunicipio.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error al conectar con el servidor:', error);
            });
        });


</script>



        <?php endif; ?>
    </form>
</div>
</div>  
</body>
</html>

<?php
}
?>
