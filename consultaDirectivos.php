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
include 'style.html';
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
        // Eliminar directivo
        $cod_directivo = $_POST['cod_directivo_delete'];
        
        // Verificar si el código ya existe
        $sql_check = "SELECT COUNT(*) FROM directivos WHERE cod_directivo = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([$cod_directivo]);
        $exists = $stmt_check->fetchColumn();
    
        if ($exists == 0) {
            echo "<script>alert('El código que desea eliminar no se encuentra en la tabla. Intenta de nuevo');</script>";
        } else {
            // Eliminar el directivo
            $sql = "DELETE FROM directivos WHERE cod_directivo = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$cod_directivo])) {
                echo "<script>
                alert('Directivo eliminado con éxito.');
                window.location.href = window.location.href;
              </script>";
        
            } else {
                echo "<p>Error al eliminar el directivo.</p>";
            }
        }
    }
}    
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Directivos</title>
</head>
<body>
<div class="update-link">
    <a href="inicio.php" class="update-btn">volver a inicio</nav></a>
    </div>
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
</div>
<div class="form-container">
    <!-- Formulario de creación -->
    <div class="form-section">
        <h2>Crear Directivo</h2>
        <form method="POST">
            <label for="nomb_directivo_create">Nombre:</label>
            <input type="text" name="nomb_directivo_create" required>
            <br>
            <label for="apell_directivo_create">Apellidos: </label>
            <input type="text" name="apell_directivo_create"required>
            <br>
            <button type="submit" name="create">Crear</button>
        </form>
    </div>

    <!-- Formulario de actualización -->
    <div class="form-section">
        <h2>Actualizar Directivo</h2>
        <form method="POST">
        <!-- Si aún no se ha verificado el código, mostrar el campo para el código -->
        <?php if (!isset($_SESSION['cod_directivo'])): ?>
            <label for="cod_directivo_update">Código del Directivo:</label>
            <input type="text" name="cod_directivo_update" required>
            <br>
            <button type="submit" name="update">Siguiente</button>
        <?php else: ?>
            <!-- Si ya se verificó el código, mostrar los campos de actualización -->
            <label for="cod_directivo_update">Código:</label>
            <input type="text" name="cod_directivo_update" value="<?php echo $_SESSION['cod_directivo']; ?>" readonly>
            <br>
            <label for="nomb_directivo_update">Nombres:</label>
            <input type="text" name="nomb_directivo_update" value="<?php echo $_SESSION['nomb_directivo']; ?>" required>
            <br>
            <label for="apell_directivo_update">Apellidos:</label>
            <input type="text" name="apell_directivo_update" value="<?php echo $_SESSION['apell_directivo']; ?>" required>
            <br>
            <button type="submit" name="update_directivo">Actualizar</button>
        <?php endif; ?>
    </form>
    </div>

    <!-- Formulario de eliminación -->
    <div class="form-section">
        <h2>Eliminar Directivo</h2>
        <form method="POST">
            <label for="cod_directivo_delete">Código del Directivo:</label>
            <input type="text" name="cod_directivo_delete" required>
            <br>
            <button type="submit" name="delete">Eliminar</button>
        </form>
    </div>
</div>

</body>

</html>


</body>
</html>

<?php
}
?>
