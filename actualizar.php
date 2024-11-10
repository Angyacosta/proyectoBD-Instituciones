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
$inst = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    
        // Verificar si el código del directivo existe
        $sql_check = "SELECT COUNT(*) FROM inst_por_municipio WHERE cod_inst = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([$cod_inst]);
        $exists = $stmt_check->fetchColumn();
    
        if ($exists == 0) {
            // Si el código no existe, mostrar mensaje de error
            echo "<script>alert('El código no se encuentra en la tabla. Intenta de nuevo');</script>";
        } else{
            $cod_munic = $_POST['cod_munic_update'];
    
        // Verificar si el código del directivo existe
        $sql_check = "SELECT COUNT(*) FROM inst_por_municipio WHERE cod_inst=? && cod_munic = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([$cod_munic]);
        $exists = $stmt_check->fetchColumn();
        if ($exists == 0) {
            // Si el código no existe, mostrar mensaje de error
            echo "<script>alert('El código de municipio no coincide con la inst no se 
            encuentran instituciones en este municipio. Intenta de nuevo');</script>";
        }else {
            // Si el código existe, obtener los datos los datos de la institucion
            $sql_get = "SELECT direccion, telefono, norma, fecha_creacion, programas_vigentes, acreditada, fecha_acreditacion,resolucion_acreditacion,
            vigencia, nit, pagina_web FROM inst_por_municipio WHERE cod_inst = ? AND cod_munic=?";
            $stmt_get = $conn->prepare($sql_get);
            $stmt_get->execute([$cod_munic]);
            $institucion = $stmt_get->fetch(PDO::FETCH_ASSOC);
    
            // Guardar el código y los datos en la sesión para mantener el estado
        $_SESSION['cod_inst'] = $cod_inst;
        $_SESSION['cod_munic'] = $cod_munic;
        $_SESSION['direccion'] = $institucion['direccion'];
        $_SESSION['telefono'] = $institucion['telefono'];
        $_SESSION['norma'] = $institucion['norma'];
        $_SESSION['fecha_creacion'] = $institucion['fecha_creacion'];
        $_SESSION['programas_vigentes'] = $institucion['programas_vigentes'];
        $_SESSION['acreditada'] = $institucion['acreditada'];
        $_SESSION['fecha_acreditacion'] = $institucion['fecha_acreditacion'];
        $_SESSION['resolucion_acreditacion'] = $institucion['resolucion_acreditacion'];
        $_SESSION['vigencia'] = $institucion['vigencia'];
        $_SESSION['nit'] = $institucion['nit'];
        $_SESSION['pagina_web'] = $institucion['pagina_web'];


// Cambiar a verdadero para mostrar el formulario de actualización
        }
        $_SESSION['form_display'] = true; // Cambiar estado para mostrar el formulario de actualización
    }
}if (isset($_POST['update_inst'])) {
        $cod_inst = $_POST['cod_inst_update'];
        $cod_munic = $_POST['cod_munic_update'];
        $direccion = $_POST['direccion_update'];
        $telefono= $_POST['telefono_update'];
        $norma= $_POST['norma_update'];
        $fecha_creacion = $_POST['fecha_creacion_update'];
        $programas_vigentes = $_POST['programas_vigentes_update'];
        $acreditada = $_POST['acreditada_update'];
        $fecha_acreditacion = $_POST['fecha_acredtacion_update'];
        $resolucion_acreditacion= $_POST['resolucion_acreditacion_update'];
        $vigencia = $_POST['vigencia_update'];
        $nit= $_POST['nit_update'];
        $pagina_web= $_POST['pagina_web_update'];
            // Actualizar el directivo en la base de datos
        $sql_update = "UPDATE inst_por_municipio SET direccion= ?, telefono = ?, norma=?, 
        fecha_creacion=?, programas_vigentes=?, acreditada=?, fecha_acreditacion=?,
        resolucion_acreditacion=?, vigencia=?, nit=?, pagina_web=?  WHERE cod_inst = ? AND cod_munic=?";
        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update->execute([$cod_inst, $cod_munic, $direccion, $telefono, $norma, $fecha_creacion, $programas_vigentes, $acreditada, $fecha_acreditacion, $resolucion_acreditacion,
        $vigencia, $nit, $pagina_web])) {
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

<!-- Contenedor para el formulario -->
<div class="container">
    <h2>Actualizar Institución</h2>
    <form method="POST">
        <!-- Si ya se verificó el código de la institución, mostrar los campos de actualización -->
        <?php if (isset($_SESSION['cod_inst'])): ?>
            <label for="cod_directivo_update">Código de la institucion:</label>
            <input type="text" name="cod_inst_update" required>
            <br>
            <button type="submit" name="update">Siguiente</button>
            <?php else: ?>
            <label for="cod_inst_update">Código de la Institución:</label>
            <input type="text" name="cod_inst_update" value="<?php echo $_SESSION['cod_inst']; ?>" readonly>
            <br>

            <label for="cod_munic_update">Código del Municipio:</label>
            <input type="text" name="cod_munic_update" value="<?php echo $_SESSION['cod_munic']; ?>" readonly>
            <br>

            <label for="direccion_update">Dirección:</label>
            <input type="text" name="direccion_update" value="<?php echo $_SESSION['direccion']; ?>" required>
            <br>

            <label for="telefono_update">Teléfono:</label>
            <input type="text" name="telefono_update" value="<?php echo $_SESSION['telefono']; ?>" required>
            <br>

            <label for="norma_update">Norma:</label>
            <input type="text" name="norma_update" value="<?php echo $_SESSION['norma']; ?>" required>
            <br>

            <label for="fecha_creacion_update">Fecha de Creación:</label>
            <input type="date" name="fecha_creacion_update" value="<?php echo $_SESSION['fecha_creacion']; ?>" required>
            <br>

            <label for="programas_vigentes_update">Programas Vigentes:</label>
            <input type="text" name="programas_vigentes_update" value="<?php echo $_SESSION['programas_vigentes']; ?>" required>
            <br>

            <label for="acreditada_update">¿Acreditada?:</label>
            <select name="acreditada_update" required>
                <option value="Sí" <?php echo ($_SESSION['acreditada'] == 'Sí') ? 'selected' : ''; ?>>Sí</option>
                <option value="No" <?php echo ($_SESSION['acreditada'] == 'No') ? 'selected' : ''; ?>>No</option>
            </select>
            <br>

            <label for="fecha_acreditacion_update">Fecha de Acreditación:</label>
            <input type="date" name="fecha_acreditacion_update" value="<?php echo $_SESSION['fecha_acreditacion']; ?>" required>
            <br>

            <label for="resolucion_acreditacion_update">Resolución de Acreditación:</label>
            <input type="text" name="resolucion_acreditacion_update" value="<?php echo $_SESSION['resolucion_acreditacion']; ?>" required>
            <br>

            <label for="vigencia_update">Vigencia:</label>
            <input type="date" name="vigencia_update" value="<?php echo $_SESSION['vigencia']; ?>" required>
            <br>

            <label for="nit_update">NIT:</label>
            <input type="text" name="nit_update" value="<?php echo $_SESSION['nit']; ?>" required>
            <br>

            <label for="pagina_web_update">Página Web:</label>
            <input type="url" name="pagina_web_update" value="<?php echo $_SESSION['pagina_web']; ?>" required>
            <br>

            <button type="submit" name="update_inst">Actualizar Institución</button>
           
        <?php endif; ?>
    </form>
</div>

<!-- Tabla para mostrar las instituciones -->
<div class="container2">
    <h2>Instituciones Registradas</h2>
    <table>
        <thead>
            <tr>
                <th>Código de institucion</th>
                <th>codigo de municipio</th>
                <th>Dirección</th>
                <th>Teléfono</th>
                <th>norma</th>
                <th>fecha_creacion</th>
                <th>programas_vigentes</th>
                <th>acreditada</th>
                <th>fecha_acreditacion</th>
                <th>resolucion_acreditacion</th>
                <th>vigencia</th>
                <th>nit</th>
                <th>pagina_web</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($instituciones as $inst): ?>
            <tr>
                <td><?php echo htmlspecialchars($inst['cod_inst']); ?></td>
                <td><?php echo htmlspecialchars($inst['cod_munic']); ?></td>
                <td><?php echo htmlspecialchars($inst['direccion']); ?></td>
                <td><?php echo htmlspecialchars($inst['telefono']); ?></td>
                <td><?php echo htmlspecialchars($inst['norma']); ?></td>
                <td><?php echo htmlspecialchars($inst['fecha_creacion']); ?></td>
                <td><?php echo htmlspecialchars($inst['programas_vigentes']); ?></td>
                <td><?php echo htmlspecialchars($inst['acreditada']); ?></td>
                <td><?php echo htmlspecialchars($inst['fecha_acreditacion']); ?></td>
                <td><?php echo htmlspecialchars($inst['resolucion_acreditacion']); ?></td>
                <td><?php echo htmlspecialchars($inst['vigencia']); ?></td>
                <td><?php echo htmlspecialchars($inst['nit']); ?></td>
                <td><?php echo htmlspecialchars($inst['pagina_web']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php
}
?>
