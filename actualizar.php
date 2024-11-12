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
        // Validaciones de fechas
        if (strtotime($fecha_acreditacion) < strtotime($fecha_creacion)) {
            echo "<script>alert('La fecha de acreditación no puede ser menor que la de creación.');</script>";
            exit;
        }
        if (strtotime($vigencia) < strtotime($fecha_acreditacion)) {
            echo "<script>alert('La fecha de vigencia no puede ser menor que la de acreditación.');</script>";
            exit;
        }

        // Si hay errores, devolver al formulario con los datos
        if (count($errores) > 0) {
            $_SESSION['errores'] = $errores;
            $_SESSION['form_data'] = $_POST;  // Almacenar los datos en sesión para restaurarlos
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
          // Verificar longitud del telefono
    if (strlen($cod_directivo) > 10) {
        echo "<script>alert('El telefono debe tener máximo 10 digitos. Intenta de nuevo.');</script>";
    } 
    // Verificar si el código de directivo ya existe
    $sql_check = "SELECT COUNT(*) FROM municipios WHERE cod_munic = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([$cod_munic]);
    $exist = $stmt_check->fetchColumn();

    if ($exist = 0) {
        echo "<script>alert('El municipio no existe. Intenta con un código diferente.');</script>";
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
    <link rel="stylesheet" href="style.css">
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
            <a href="?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'style="background-color: #0277bd;"' : ''; ?>><?php echo $i; ?></a>
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
            <input type="text" name="cod_munic_update" value="<?= isset($_SESSION['form_data']['cod_munic_update']) ? $_SESSION['form_data']['cod_munic_update'] : $_SESSION['cod_munic'] ?>" required>
            <br>

            <label for="direccion_update">Dirección:</label>
        <input type="text" name="direccion_update" value="<?= isset($_SESSION['form_data']['direccion_update']) ? $_SESSION['form_data']['direccion_update'] : $_SESSION['direccion'] ?>" required>
        <br>

        <label for="telefono_update">Teléfono:</label>
        <input type="text" name="telefono_update" value="<?= isset($_SESSION['form_data']['telefono_update']) ? $_SESSION['form_data']['telefono_update'] : $_SESSION['telefono'] ?>" required>
        <br>

        <label for="norma_update">Norma:</label>
        <input type="text" name="norma_update" value="<?= isset($_SESSION['form_data']['norma_update']) ? $_SESSION['form_data']['norma_update'] : $_SESSION['norma'] ?>" required>
        <br>

        <label for="fecha_creacion_update">Fecha de Creación:</label>
        <input type="date" name="fecha_creacion_update" value="<?= isset($_SESSION['form_data']['fecha_creacion_update']) ? $_SESSION['form_data']['fecha_creacion_update'] : $_SESSION['fecha_creacion'] ?>" required>
        <br>

        <label for="programas_vigente_update">Programas Vigentes:</label>
        <input type="number" min="0" name="programas_vigente_update" value="<?= isset($_SESSION['form_data']['programas_vigente_update']) ? $_SESSION['form_data']['programas_vigente_update'] : $_SESSION['programas_vigente'] ?>" required>
        <br>
        <label for="programas_convenio_update">Programas En Convenio:</label>
        <input type="number" min="0" name="programas_convenio_update" value="<?= isset($_SESSION['form_data']['programas_convenio_update']) ? $_SESSION['form_data']['programas_convenio_update'] : $_SESSION['programas_convenio'] ?>" required>
        <br>
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
        <input type="text" name="resolucion_acreditacion_update" value="<?= isset($_SESSION['form_data']['resolucion_acreditacion_update']) ? $_SESSION['form_data']['resolucion_acreditacion_update'] : $_SESSION['resolucion_acreditacion'] ?>" required>
        <br>

        <label for="vigencia_update">Vigencia:</label>
        <input type="date" name="vigencia_update" value="<?= isset($_SESSION['form_data']['vigencia_update']) ? $_SESSION['form_data']['vigencia_update'] : $_SESSION['vigencia'] ?>" required>
        <br>

        <label for="nit_update">NIT:</label>
        <input type="text" name="nit_update" value="<?= isset($_SESSION['form_data']['nit_update']) ? $_SESSION['form_data']['nit_update'] : $_SESSION['nit'] ?>" required>
        <br>

        <label for="pagina_web_update">Página Web:</label>
        <input type="url" name="pagina_web_update" value="<?= isset($_SESSION['form_data']['pagina_web_update']) ? $_SESSION['form_data']['pagina_web_update'] : $_SESSION['pagina_web'] ?>" required>
        <br>


            <button type="submit" name="update_inst">Actualizar Institución</button>
           
        <?php endif; ?>
    </form>
</div>
</div>  
</body>
</html>

<?php
}
?>
