<?php
include 'conexionDB.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
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
// Consulta para llenar las listas desplegables
$sql_municipios = "SELECT d.nomb_depto, m.nomb_munic,m.cod_munic FROM municipios m Join departamentos d ON d.cod_depto = m.cod_depto";
$stmt_municipios = $conn->query($sql_municipios);
$municipios = $stmt_municipios->fetchAll(PDO::FETCH_ASSOC);

$sql_normas = "SELECT cod_norma, nomb_norma FROM norma_creacion";
$stmt_normas = $conn->query($sql_normas);
$normas = $stmt_normas->fetchAll(PDO::FETCH_ASSOC);

$sql_admin = "SELECT cod_admin, nomb_admin FROM acto_administrativo";
$stmt_admin = $conn->query($sql_admin);
$administrativos = $stmt_admin->fetchAll(PDO::FETCH_ASSOC);

$sql_seccional = "SELECT cod_seccional, nomb_seccional FROM seccional";
$stmt_seccional = $conn->query($sql_seccional);
$seccionales = $stmt_seccional->fetchAll(PDO::FETCH_ASSOC);

$sql_juridica = "SELECT cod_juridica, nomb_juridica FROM naturaleza_juridica";
$stmt_juridica = $conn->query($sql_juridica);
$juridicas = $stmt_juridica->fetchAll(PDO::FETCH_ASSOC);

$sql_estado = "SELECT cod_estado, nomb_estado FROM estado";
$stmt_estado = $conn->query($sql_estado);
$estados = $stmt_estado->fetchAll(PDO::FETCH_ASSOC);

$sql_inst="SELECT 
    i.cod_inst, 
    ins.nomb_inst, m.nomb_munic
FROM 
    public.inst_por_municipio i
JOIN    
    public.instituciones ins 
ON 
    i.cod_ies_padre = ins.cod_ies_padre
JOIN municipios m 
ON 
m.cod_munic = i.cod_munic
    ";
$stm_inst=$conn->query($sql_inst); 
$stm_inst->execute();
$instituciones= $stm_inst->fetchAll(PDO::FETCH_ASSOC);
?>
<?php   
session_start();
include 'inststyle.html';
    $form_display = false;// Manejo del botón "Atrás"


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        if (empty($_POST['cod_inst_update']) || empty($_POST['cod_munic']) || $_POST['cod_estado']==="" || $_POST['acreditada_update']  ==="") {
            echo "<script>alert('Por favor completa todos los campos obligatorios.');</script>";
        }
    //almacenar en session
    $_SESSION['cod_inst'] = $_POST['cod_inst_update'];
    $_SESSION['cod_munic'] = $_POST['cod_munic'];
    $_SESSION['cod_estado'] = $_POST['cod_estado'];
    if (isset($_POST['acreditada_update'])) {
        // Convertir "Sí" en 1 y "No" en 0
        $_SESSION['acreditada'] = ($_POST['acreditada_update'] === "Sí") ? 1 : 0;
    }
    
        // Campos opcionales: Asignar NULL si están vacíos
    $_SESSION['cod_norma'] = $_POST['cod_norma'] != "" ? $_POST['cod_norma'] : null;
    $_SESSION['cod_admin'] = $_POST['cod_admin'] != "" ? $_POST['cod_admin'] : null;
    $_SESSION['cod_seccional'] = $_POST['cod_seccional'] != "" ? $_POST['cod_seccional'] : null;
    $_SESSION['cod_juridica'] = $_POST['cod_juridica'] != "" ? $_POST['cod_juridica'] : null;

    $_SESSION['form_display'] = true;   

         }if (isset($_POST['update_inst'])) {
            /*if (empty($_POST['direccion_update']) || empty($_POST['fecha_crecion_update'])) {
                echo "<script>alert('Por favor completa todos los campos obligatorios.');</script>";
                exit;
            }*/
        // Capturar y validar campos obligatorios del segundo formulario
        $direccion= $_POST['direccion_update'];
        $fecha_creacion = $_POST['fecha_creacion_update'];
            // Comprobar si los campos obligatorios están vacíos
    if (empty($direccion) || empty($fecha_creacion)) {
        echo "<script>alert('Por favor completa todos los campos obligatorios.');</script>";
        exit;
    }
       
    if ($_SESSION['acreditada'] == 1) {
        $fecha_acreditacion = $_POST['fecha_acreditacion_update'];
    
        // Validar fecha de acreditación
        if (strtotime($fecha_acreditacion) < strtotime($fecha_creacion)) {
            echo "<script>alert('La fecha de acreditación no puede ser menor que la de creación.');</script>";
            exit;
        }
    } else {
        // Si no está acreditada, asignar NULL
        $fecha_acreditacion = null;
    }
    
        $telefono= $_POST['telefono_update'] != "" ? $_POST['telefono_update'] : null;
        $norma = $_POST['norma_update'] !== "" ? (int)$_POST['norma_update'] : null;
        $programas_vigente = $_POST['programas_vigente_update'] != "" ? $_POST['programas_vigente_update'] : null;
        $programas_convenio = $_POST['programas_convenio_update'] != "" ? $_POST['programas_convenio_update'] : null;
        $resolucion_acreditacion= $_POST['resolucion_acreditacion_update'] != "" ? $_POST['resolucion_acreditacion_update'] : null;
        $vigencia = $_POST['vigencia_update'] != "" ? (int)$_POST['vigencia_update'] : null;
        $nit= $_POST['nit_update'] != "" ? $_POST['nit_update'] : 'No disponible';
        $pagina_web = $_POST['pagina_web_update'] != "" ? $_POST['pagina_web_update'] : 'No disponible';

            // Actualizar el directivo en la base de datos
        $sql_update = "UPDATE inst_por_municipio SET cod_munic=?, cod_estado=?, acreditada=?, cod_norma=?, cod_admin=?, 
        cod_seccional=?, cod_juridica=?, direccion= ?, telefono = ?, norma=?, 
        fecha_creacion=?, programas_vigente=?, programas_convenio=?, fecha_acreditacion=?,
        resolucion_acreditacion=?, vigencia=?, nit=?, pagina_web=?  WHERE cod_inst = ?";
        $stmt_update = $conn->prepare($sql_update);
        $params = [
            $_SESSION['cod_munic'],
            $_SESSION['cod_estado'],
            $_SESSION['acreditada'],
            $_SESSION['cod_norma'],
            $_SESSION['cod_admin'],
            $_SESSION['cod_seccional'],
            $_SESSION['cod_juridica'],
            $direccion,
            $telefono,
            $norma,
            $fecha_creacion,
            $programas_vigente,
            $programas_convenio,
            $fecha_acreditacion,
            $resolucion_acreditacion,
            $vigencia,
            $nit,
            $pagina_web,
            $_SESSION['cod_inst']
        ];

        if ($stmt_update->execute($params)) {
            echo "<script>
                    alert('institucion actualizado con éxito.');
                    window.location.href = window.location.href; // Recarga la página
                  </script>";
                  session_unset();
                  session_destroy();
        } else {
            echo "<p>Error al actualizar la institución.</p>";
            // Mostrar detalles del error
            echo "<pre>" . implode(" ", $stmt_update->errorInfo()) . "</pre>";
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
     <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> 
</head>
<body>
<br>
<div class="header" style="margin-bottom: 10px; padding: 5px;">
    <img src="logo_men.png" alt="Logo de la institución" style="max-width: 100%; height: auto; margin-bottom: 20px;">
    </div>
<div class="container-wrapper">  
<div class="container2">
<div class="update-link">
    <a href="index.php" class="update-btn">volver a inicio</nav></a>
    </div>
    
<!-- Tabla para mostrar las instituciones -->
<table border="1">
    <thead>
            <tr>
                <th colspan="20" >Instituciones</th>
            </tr>
        <tr>
            <th>Código de Institución</th>
            <th>Código de Municipio</th>
            <th>Código de IES Padre</th>
            <th>Dirección</th>
            <th>Teléfono</th>
            <th>Norma</th>
            <th>Fecha de Creación</th>
            <th>Programas Vigentes</th>
            <th>Programas en Convenio</th>
            <th>Acreditada</th>
            <th>Fecha de Acreditación</th>
            <th>Resolución de Acreditación</th>
            <th>Vigencia</th>
            <th>NIT</th>
            <th>Página Web</th>
            <th>Código de Norma</th>
            <th>Código de Acto Administrativo</th>
            <th>Código Seccional</th>
            <th>Código Jurídico</th>
            <th>    Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($registros as $registro): ?>
        <tr>
            <td><?= htmlspecialchars($registro['cod_inst']); ?></td>
            <td><?= htmlspecialchars($registro['cod_munic']); ?></td>
            <td><?= htmlspecialchars($registro['cod_ies_padre']); ?></td>
            <td><?= htmlspecialchars($registro['direccion']); ?></td>
            <td><?= htmlspecialchars($registro['telefono']); ?></td>
            <td><?= htmlspecialchars($registro['norma']); ?></td>
            <td><?= htmlspecialchars($registro['fecha_creacion']); ?></td>
            <td><?= htmlspecialchars($registro['programas_vigente']); ?></td>
            <td><?= htmlspecialchars($registro['programas_convenio']); ?></td>
            <td><?= $registro['acreditada'] ? 'Sí' : 'No'; ?></td>
            <td><?= htmlspecialchars($registro['fecha_acreditacion']); ?></td>
            <td><?= htmlspecialchars($registro['resolucion_acreditacion']); ?></td>
            <td><?= htmlspecialchars($registro['vigencia']); ?></td>
            <td><?= htmlspecialchars($registro['nit']); ?></td>
            <td><?= htmlspecialchars($registro['pagina_web']); ?></td>
            <td><?= htmlspecialchars($registro['cod_norma']); ?></td>
            <td><?= htmlspecialchars($registro['cod_admin']); ?></td>
            <td><?= htmlspecialchars($registro['cod_seccional']); ?></td>
            <td><?= htmlspecialchars($registro['cod_juridica']); ?></td>
            <td><?= $registro['cod_estado'] ? 'Activa' : 'Inactiva'; ?></td>
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
            <a href="?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'style="background-color: #2994f9;"':''; ?>><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Siguiente</a>
        <?php endif; ?>
    </div>
</div>
<!-- Contenedor para el formulario -->
<div class="container">
    <h2>Actualizar Institución</h2>
    <form method="POST" id="formulario" action="">
    <?php if (!isset($_SESSION['form_display'])): ?>
        <label>Código de la Institución:</label>
    <select name="cod_inst_update" class='form-select' required>
        <option value="">-- Selecciona una institución  --</option>
        <?php foreach ($instituciones as $row): ?>
            <option value="<?= htmlspecialchars($row['cod_inst']) ?>">
                <?= htmlspecialchars($row['cod_inst'] . " - " . $row['nomb_inst']. " - " . $row['nomb_munic']) ?>
            </option>
        <?php endforeach; ?>
    </select>   
    <br>

    <label>Nuevo Municipio:</label>
    <select name="cod_munic" class="form-select" required>
        <option value="">-- Selecciona un municipio --</option>
        <?php foreach ($municipios as $munic): ?>
            <option value="<?= htmlspecialchars($munic['cod_munic']) ?>">
                <?= htmlspecialchars($munic['nomb_depto']. " - " . $munic['nomb_munic']. " - " .$munic['cod_munic']) ?>
            </option> 
        <?php endforeach; ?> 
    </select>
    <br>

    <label>Norma:</label>
    <select name="cod_norma" class="form-select">
        <option value="">-- Selecciona una norma --</option>
        <?php foreach ($normas as $norma): ?>
            <option value="<?= htmlspecialchars($norma['cod_norma']) ?>">
                <?= htmlspecialchars($norma['cod_norma']." - " .$norma['nomb_norma']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br>

    <label>Acto Administrativo:</label>
    <select name="cod_admin" class="form-select">
        <option value="">-- Selecciona un acto administrativo --</option>
        <?php foreach ($administrativos as $admin): ?>
            <option value="<?= htmlspecialchars($admin['cod_admin']) ?>">
                <?= htmlspecialchars($admin['cod_admin']." - " . $admin['nomb_admin']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br>

    <label>Seccional:</label>
    <select name="cod_seccional" class="form-select" >
        <option value="">-- Selecciona una seccional --</option>
        <?php foreach ($seccionales as $secc): ?>
            <option value="<?= htmlspecialchars($secc['cod_seccional']) ?>">
                <?= htmlspecialchars($secc['cod_seccional']. " - " .$secc['nomb_seccional']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br>

    <label>Naturaleza Jurídica:</label>
    <select name="cod_juridica" class="form-select">
        <option value="">-- Selecciona una naturaleza jurídica --</option>
        <?php foreach ($juridicas as $juridica): ?>
            <option value="<?= htmlspecialchars($juridica['cod_juridica']) ?>">
                <?= htmlspecialchars($juridica['cod_juridica']. " - ". $juridica['nomb_juridica']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br>

    <label>Estado:</label>
    <select name="cod_estado" class="form-select" required >
        <option value="">-- Selecciona un estado --</option>
        <?php foreach ($estados as $estado): ?>
            <option value="<?= htmlspecialchars($estado['cod_estado']) ?>">
                <?= htmlspecialchars($estado['nomb_estado']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br>
    <label for="acreditada_update">¿Acreditada?:</label>
            <select name="acreditada_update" required>
            <option value="Sí" >Sí</option>
            <option value="No">No</option>
        </select>
        <br>
            
        <button type="submit" name="update">Siguiente</button>

            <?php else: ?>
            <!--fromulario para actulizar -->
            <label for="cod_inst_update">Código de la Institución:</label>
            <input type="text" name="cod_inst_update" value="<?php echo $_SESSION['cod_inst']; ?>" readonly>
            <br>

                <label for="cod_munic_update">Código de Municipio:</label>
                <input type="text" id= "cod_munic_update" name="cod_munic_update" value="<?php echo $_SESSION['cod_munic']; ?>" readonly>
                <br>

                <label for="direccion_update">Nueva Dirección:</label>
        <input type="text" name="direccion_update" id="direccion_update" placeholder="Ej: Carrera 45 #26-85" required>
        <p id="mensaje" style="color: red;"></p>  
        <br>

        <label for="telefono_update">Nuevo Teléfono:</label>
        <input type="text" id= "telefono_update" name="telefono_update" id="telefono_update"  placeholder="Ej: 3165000">
        <p class="error-message" id="telefonoError">Solo se aceptan telefonos fijos de 7 digitos y celulares de 10 digitos y su número inicial es 3.</p>
        <br>    
            <label for="norma_update">Norma:</label>
            <input type="text" min = "0" id="norma_update" name="norma_update" >
            <br>
            <p class="error-message" id="errornorma">debe ser una cadena de solo números y positivos.</p>
            <p class="error-message" id="errorEspacio" style="display: none; color: red;">No se permiten espacios.</p>
            <p id="successNorma" style="display: none; color: green;">¡Valor válido!</p>
        <label for="fecha_creacion_update">Nueva Fecha de Creación:</label>
        <input type="date" name="fecha_creacion_update" id= "fecha_creacion_update"required>
        <p id="mensaje_fecha" style="color: red;"></p>
        <br>
        <label for="programas_vigente_update">Nueva cantidad de Programas Vigentes:</label>
        <input type="text" min="0" id="programas_vigente_update" name="programas_vigente_update"  >
        <br>
        <p class="error-message" id="errorvigentes">debe ser un número y positivo,no se permiten letras ni caracteres.</p>
        <p class="error-message" id="errorEspacio2" style="display: none; color: red;">No se permiten espacios.</p>
        <p id="successProgramas" style="display: none; color: green;">¡Valor válido!</p> 

        <label for="programas_convenio_update">Nueva Cantidad de Programas En Convenio:</label>
        <input type="text" min="0" id = "programas_convenio_update" name="programas_convenio_update" >
        <br>
        <p class="error-message" id="errorConvenio">debe ser un número y positivo,no se permiten letras ni caracteres.</p>
        <p class="error-message" id="errorEspacioConvenio" style="display: none; color: red;">No se permiten espacios.</p>
        <p id="successConvenio" style="display: none; color: green;">¡Valor válido!</p> 
            <br>

            <?php if ($_SESSION['acreditada'] == 1): ?>
    <label for="fecha_acreditacion_update">Nueva Fecha de Acreditación:</label>
    <input type="date" name="fecha_acreditacion_update" id="fecha_acreditacion_update">
    <br>
<?php endif; ?>

        <label for="resolucion_acreditacion_update">Nueva Resolución de Acreditación:</label>
        <input type="text" min = "0" id= resolucion_acreditacion_update name="resolucion_acreditacion_update">
        <br>
        <p class="error-message" id="errorAcreditacion">debe ser un número y positivo,no se permiten letras ni caracteres.</p>
        <p class="error-message" id="errorEspacioAcreditacion" style="display: none; color: red;">No se permiten espacios.</p>
        <p id="successAcreditacion" style="display: none; color: green;">¡Valor válido!</p> 

        <label for="vigencia_update">Nueva Vigencia:</label>
        <input type="text" min="0" id="vigencia_update" name="vigencia_update" >
        <br>
        <p class="error-message" id="errorVigencia">debe ser un número y positivo,no se permiten letras ni caracteres.</p>
        <p class="error-message" id="errorEspacioVigencia" style="display: none; color: red;">No se permiten espacios.</p>
        <p id="successVigencia" style="display: none; color: green;">¡Valor válido!</p> 

        
        <label for="nit_update">Nuevo NIT:</label>
        <input type="text" name="nit_update" id="nit_update" placeholder="Ej: 899.999.063-3" pattern="\d{3}\.\d{3}\.\d{3}-\d" 
        title="El formato debe ser: 899.999.063-3"> 
        <p class="error-message" id="nitError">ingresa una entrada valida ejemplo 899.999.063-3</p>
        <br>

        <label for="pagina_web_update">Nueva Página Web:</label>
        <input type="text" name="pagina_web_update" id="pagina_web_update" placeholder="Ej: https://www.unal.edu.co"  >
        <p class="error-message" id="paginaWebError">ingresa una url válida.</p>
        <br>




            <button type="submit" name="update_inst">Actualizar Institución</button>
<!------------------------------------Validaciones ---------------------------------------------->
<script>
    // Obtener referencias al input y a los mensajes
    const normaInput = document.getElementById('norma_update');
    const errorMessage = document.getElementById('errornorma');
    const successMessage = document.getElementById('successNorma');
    const spaceErrorMessage = document.getElementById('errorEspacio');
    const formularioActualizar3 = document.getElementById("formulario");
    
    const vigenciaInput = document.getElementById('vigencia_update');
    const errorMessageVigencia = document.getElementById('errorVigencia');
    const spaceErrorMessageVigencia = document.getElementById('errorEspacioVigencia');
    const successMessageVigencia = document.getElementById('successVigencia');
                
    const programasInput = document.getElementById('programas_vigente_update');
    const errorMessage2 = document.getElementById('errorvigentes');
    const spaceErrorMessage2 = document.getElementById('errorEspacio2');
    const successMessage2 = document.getElementById('successProgramas');

    const programasConvenioInput = document.getElementById('programas_convenio_update');
        const errorMessageConvenio = document.getElementById('errorConvenio');
        const spaceErrorMessageConvenio = document.getElementById('errorEspacioConvenio');
        const successMessageConvenio = document.getElementById('successConvenio');

        const resolucionAcreditacionInput = document.getElementById('resolucion_acreditacion_update');
    const errorMessageAcreditacion = document.getElementById('errorAcreditacion');
    const spaceErrorMessageAcreditacion = document.getElementById('errorEspacioAcreditacion');
    const successMessageAcreditacion = document.getElementById('successAcreditacion');

    const telefono = document.getElementById('telefono_update');
    const telefonoError = document.getElementById('telefonoError');
    const nit = document.getElementById('nit_update');
    const nitError = document.getElementById('nitError');
    const paginaWeb = document.getElementById('pagina_web_update');
    const paginaWebError = document.getElementById('paginaWebError');
    const formularioActualizar2 = document.getElementById("formulario");



    // Evitar la tecla de espacio
    normaInput.addEventListener('keydown', function (event) {
        if (event.code === 'Space') {
            event.preventDefault(); // Bloquear la tecla de espacio
            spaceErrorMessage.style.display = 'block'; // Mostrar mensaje de espacio
            successMessage.style.display = 'none'; // Esconder mensaje de éxito
            errorMessage.style.display = 'none'; // Esconder mensaje de error

            // Esconder el mensaje de espacio después de 2 segundos
            setTimeout(() => {
                spaceErrorMessage.style.display = 'none';
                // Validar el campo
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
            return true;
        } // Verificar si el valor excede 10 caracteres
    if (normaValue.length > 10) {
        errorMessage.style.display = 'none';
        successMessage.style.display = 'none';
        spaceErrorMessage.style.display = 'block';
        spaceErrorMessage.textContent = "El valor no debe exceder los 10 caracteres.";
        spaceErrorMessage.style.color = "red";
        return false;
    }
        // Verificar si es un número entero positivo
        else if (!/^\d+$/.test(normaValue)) {
            // Mostrar mensaje de error si contiene letras o caracteres no numéricos
            errorMessage.style.display = 'block';
            successMessage.style.display = 'none';
            spaceErrorMessage.style.display = 'none';
            return false;
        } else {
            // Mostrar mensaje de éxito si es válido
            errorMessage.style.display = 'none';
            successMessage.style.display = 'block';
            spaceErrorMessage.style.display = 'none';
            return true;
        }
    }

//-----------------------VALIDAR PROGRAMAS VIGENTES ---------------------------------
    // Obtener referencias al input y a los mensajes


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
        return true;
    } // Verificar si el valor excede 10 caracteres
    if (programasValue.length > 10) {
        errorMessage2.style.display = 'none';
        successMessage2.style.display = 'none';
        spaceErrorMessage2.style.display = 'block';
        spaceErrorMessage2.textContent = "El valor no debe exceder los 10 caracteres.";
        spaceErrorMessage2.style.color = "red";
        return false;
    }
    // Verificar si el valor contiene letras o caracteres no válidos
    else if (!/^\d+$/.test(programasValue)) {
        errorMessage2.style.display = 'block'; // Mostrar mensaje de error si no es un número válido
        successMessage2.style.display = 'none'; // Esconder el mensaje de éxito
        spaceErrorMessage2.style.display = 'none'; // Esconder mensaje de espacio
        return false;
    } // Verificar si el campo está vacío
    // Si el valor es válido (solo números)
    else {
        // Mostrar mensaje de éxito si es válido
        errorMessage2.style.display = 'none';
        successMessage2.style.display = 'block';
        spaceErrorMessage2.style.display = 'none'; // Esconder mensaje de espacio
        return true;
    }
}

        //-----------------------VALIDAR PROGRAMAS EN CONVENIO ---------------------------------
        // Obtener referencias al input y a los mensajes

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
                return true;
            } // Verificar si el valor excede 10 caracteres
    if (programasValue.length > 10) {
        errorMessageConvenio.style.display = 'none';
        successMessageConvenio.style.display = 'none';
        spaceErrorMessageConvenio.style.display = 'block';
        spaceErrorMessageConvenio.textContent = "El valor no debe exceder los 10 caracteres.";
        spaceErrorMessageConvenio.style.color = "red";
        return false;
    }
            // Verificar si el valor contiene letras o caracteres no válidos
            else if (!/^\d+$/.test(programasValue)) {
                errorMessageConvenio.style.display = 'block'; // Mostrar mensaje de error si no es un número válido
                successMessageConvenio.style.display = 'none'; // Esconder el mensaje de éxito
                spaceErrorMessageConvenio.style.display = 'none'; // Esconder mensaje de espacio
                return false;
            } 
            // Si el valor es válido (solo números)
            else {
                // Mostrar mensaje de éxito si es válido
                errorMessageConvenio.style.display = 'none';
                successMessageConvenio.style.display = 'block';
                spaceErrorMessageConvenio.style.display = 'none'; // Esconder mensaje de espacio
                return true;
            }
        }

    //-----------------------VALIDAR RESOLUCIÓN DE ACREDITACIÓN ---------------------------------
    
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
            return true;
        } if (resolucionValue.length > 10) {
            errorMessageAcreditacion.style.display = 'none'; // Esconder mensaje de error general
        successMessageAcreditacion.style.display = 'none'; // Esconder mensaje de éxito
        spaceErrorMessageAcreditacion.style.display = 'block'; // Mostrar mensaje de longitud
        spaceErrorMessageAcreditacion.textContent = "La vigencia no debe exceder los 10 caracteres.";
        spaceErrorMessageAcreditacion.style.color = "red";
        return false;
            } 
        // Verificar si el valor contiene letras o caracteres no válidos
        else if (!/^\d+$/.test(resolucionValue)) {
            errorMessageAcreditacion.style.display = 'block'; // Mostrar mensaje de error si no es un número válido
            successMessageAcreditacion.style.display = 'none'; // Esconder el mensaje de éxito
            spaceErrorMessageAcreditacion.style.display = 'none'; // Esconder mensaje de espacio
            return false;
        } 
        // Si el valor es válido (solo números)
        else {
            // Mostrar mensaje de éxito si es válido
            errorMessageAcreditacion.style.display = 'none';
            successMessageAcreditacion.style.display = 'block';
            spaceErrorMessageAcreditacion.style.display = 'none'; // Esconder mensaje de espacio
            return true;
        }
    }

    //-----------------------VALIDAR VIGENCIA ---------------------------------
    // Obtener referencias al input y a los mensajes

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
            return true;
        } if (vigenciaValue.length > 10) {
            errorMessageVigencia.style.display = 'none'; // Esconder mensaje de error general
        successMessageVigencia.style.display = 'none'; // Esconder mensaje de éxito
        spaceErrorMessageVigencia.style.display = 'block'; // Mostrar mensaje de longitud
        spaceErrorMessageVigencia.textContent = "La vigencia no debe exceder los 10 caracteres.";
        spaceErrorMessageVigencia.style.color = "red";
        return false;
            } 
        // Verificar si el valor contiene letras o caracteres no válidos
        else if (!/^\d+$/.test(vigenciaValue)) {
            errorMessageVigencia.style.display = 'block'; // Mostrar mensaje de error si no es un número válido
            successMessageVigencia.style.display = 'none'; // Esconder el mensaje de éxito
            spaceErrorMessageVigencia.style.display = 'none'; // Esconder mensaje de espacio
            return false;
        } 
        // Si el valor es válido (solo números)
        else {
            // Mostrar mensaje de éxito si es válido
            errorMessageVigencia.style.display = 'none';
            successMessageVigencia.style.display = 'block';
            spaceErrorMessageVigencia.style.display = 'none'; // Esconder mensaje de espacio
            return true;
        }
    }


    // Validaciones individuales
    const validarTelefono = () => {
        const esValido = telefono.value === "" || /^\d{7}$/.test(telefono.value) || /^3\d{9}$/.test(telefono.value);
        telefonoError.style.display = esValido ? 'none' : 'block';
        telefono.classList.toggle('input-error-message', !esValido);
        return esValido;
    };

    const validarNit = () => {
        const esValido = nit.value === "" || /^\d{3}\.\d{3}\.\d{3}-\d$/.test(nit.value);
        nitError.style.display = esValido ? 'none' : 'block';
        nit.classList.toggle('input-error-message', !esValido);
        return esValido;
    };

    const validarPaginaWeb = () => {
        if (paginaWeb.value === "") {
            paginaWebError.style.display = 'none';
            paginaWeb.classList.remove('input-error-message');
            return true;
        }
        try {
            new URL(paginaWeb.value);
            paginaWebError.style.display = 'none';
            paginaWeb.classList.remove('input-error-message');
            return true;
        } catch {
            paginaWebError.style.display = 'block';
            paginaWeb.classList.add('input-error-message');
            return false;
        }
    };

     // Función JavaScript para validar la dirección en tiempo real
 function validarDireccion() {
            const direccion = document.getElementById("direccion_update").value;
            const mensaje = document.getElementById("mensaje");
            // Expresión regular para validar las direcciones con "barrio" sin necesidad de "#" o "No"
            const patron = /\b(?:Calle|Carrera|Av|carretera|Avenida|Diagonal|sede|Vía|Via)\b.*(?:#|No\.?|Km)?|\bBarrio\b/i;

            // Validar longitud
            if (direccion.length > 100) {
                mensaje.textContent = "La dirección no debe exceder los 100 caracteres.";
                mensaje.style.color = "red";
                return false;
            } else if (!patron.test(direccion)) {
                mensaje.textContent = "La dirección debe contener términos válidos como Calle, Carrera, Av, Barrio, etc.";
                mensaje.style.color = "red";
                return false;
            } else {
                mensaje.textContent = "Dirección válida.";
                mensaje.style.color = "green";
                return true;
            }
        }
    telefono.addEventListener('input', validarTelefono);
    nit.addEventListener('input', validarNit);
    paginaWeb.addEventListener('input', validarPaginaWeb);

// Validación en tiempo real mientras el usuario escribe
const direccionInput = document.getElementById("direccion_update");
            direccionInput.addEventListener("input", validarDireccion);


     // Validación al enviar el formulario
     formularioActualizar3.addEventListener('submit', (e) => {
        const normaValida = validateNormaInput();
        const resolucionvalida =validarResolucionInput();
        const vigentesvalido = validateProgramaInput();
        const vigenciavalida =validarVigenciaInput();
        const coneveniovalido =ValidarConvenioInput();
        const telefonoValido = validarTelefono();
        const nitValido = validarNit();
        const paginaWebValida = validarPaginaWeb();
        const direccionvalida =validarDireccion();
        // Si el campo no es válido, prevenir el envío
        if (!normaValida || !resolucionvalida || !vigenciavalida || !vigentesvalido || !coneveniovalido || !telefonoValido || !nitValido || !paginaWebValida  || !direccionvalida) {
            e.preventDefault();
            alert("Por favor, corrige los errores antes de enviar el formulario.");
        }
    });

</script>


    <script>
       
  // Configurar la fecha actual al cargar la página
function establecerFechaActual() {
    const fechaHoy = new Date();
    const dia = String(fechaHoy.getDate()).padStart(2, '0');
    const mes = String(fechaHoy.getMonth() + 1).padStart(2, '0');
    const anio = fechaHoy.getFullYear();
    const fechaFormateada = `${anio}-${mes}-${dia}`;
    // Configurar el atributo 'max' para las fechas
    document.getElementById("fecha_creacion_update").max = fechaFormateada;
    document.getElementById("fecha_acreditacion_update").max = fechaFormateada;
}

// Normalizar fechas (quitar horas, minutos y segundos)
function normalizarFecha(fecha) {
    const nuevaFecha = new Date(fecha);
    nuevaFecha.setHours(0, 0, 0, 0); // Establecer hora a 00:00:00
    return nuevaFecha;
}
const estadoAcreditada = <?php echo json_encode($_SESSION['acreditada']); ?>;
// Validar fechas
function validarFechas() {
    const fechaCreacion = document.getElementById("fecha_creacion_update").value;
    const fechaAcreditacion = document.getElementById("fecha_acreditacion_update").value;
    const fechaHoy = normalizarFecha(new Date());
    const mensajeFecha = document.getElementById("mensaje_fecha");

    // Convertir fechas en objetos Date normalizados
    const fechaCreacionObj = fechaCreacion ? normalizarFecha(fechaCreacion) : null;
    const fechaAcreditacionObj = fechaAcreditacion ? normalizarFecha(fechaAcreditacion) : null;

    // Validaciones
    if (estadoAcreditada == 1) {
        // Validación: Fecha de acreditación no menor a fecha de creación
        if (fechaAcreditacionObj < fechaCreacionObj) {
            mensajeFecha.textContent = "La fecha de acreditación no puede ser anterior a la fecha de creación.";
            mensajeFecha.style.color = "red";
            return false;
        }

        // Validación: Fecha de acreditación no mayor a hoy
        if (fechaAcreditacionObj > fechaHoy) {
            mensajeFecha.textContent = "La fecha de acreditación no puede ser mayor a la fecha actual.";
            mensajeFecha.style.color = "red";
            return false;
        }

        // Validación: Fecha de acreditación obligatoria para instituciones acreditadas
        if (!fechaAcreditacion) {
            mensajeFecha.textContent = "La fecha de acreditación es obligatoria para instituciones acreditadas.";
            mensajeFecha.style.color = "red";
            return false;
        }
    }

    // Validación: Fecha de creación no mayor a hoy
    if (fechaCreacionObj > fechaHoy) {
        mensajeFecha.textContent = "La fecha de creación no puede ser mayor a la fecha actual.";
        mensajeFecha.style.color = "red";
        return false;
    }

    // Limpiar mensaje de error si todo es válido
    mensajeFecha.textContent = "";
    return true;
}

// Manejar el envío del formulario
function manejarEnvio(event) {
    const sonFechasValidas = validarFechas();

    if (!sonFechasValidas) {
        event.preventDefault(); // Evitar el envío si hay errores
        alert("Corrige los errores antes de enviar.");
    }
}

// Inicializar eventos y configuración al cargar la página
window.onload = function () {
    // Configurar la fecha actual al cargar
    establecerFechaActual();

    // Validación en tiempo real para los campos
    document.getElementById("fecha_creacion_update").addEventListener("input", validarFechas);
    document.getElementById("fecha_acreditacion_update").addEventListener("input", validarFechas);

    // Validar al enviar el formulario
    const formulario = document.getElementById("formulario");
    formulario.addEventListener("submit", manejarEnvio);
};

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
