<?php
include ('conexionDB.php');


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
$cod_directivo = readline("codigo: ");
$nomb_directivo = readline("Nombre: ");
$sql = "INSERT INTO directivos(cod_directivo,nomb_directivo) VALUES (?,?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$cod_directivo ,$nomb_directivo ]);
echo "Directivo creado con éxito.\n";
break;
case 'read':
$sql = "SELECT * FROM directivos";
$stmt = $conn->query($sql);
$directivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($directivos as $directivo) {
echo "codigo: {$directivo['cod_directivo']} | Nombre: {$directivo['nomb_directivo']} \n";
}
break;
case 'update':
$cod_directivo = readline("codigo del usuario a actualizar: ");
$nomb_directivo = readline("Nuevo nombre: ");
$sql = "UPDATE directivos SET nomb_directivo = ? WHERE cod_directivo = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$nomb_directivo, $cod_directivo]);
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
// Modo web
$sql = "SELECT * FROM directivos";
$stmt = $conn->query($sql);
$directivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Directivos</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #ffffff; /* Fondo azul claro */
        margin: 0;
        padding: 0;
    }
    h1 {
        color: #333;
        text-align: center;
        margin-top: 20px;
    }
    .table-container {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        background-color : #90cafe; 
        border:2;
        
    }
    th, td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: center;
        border: black 1px solid;
    }
    th {
        background-color: #0288d1; /* Azul fuerte para los encabezados */
        color: white;
    }
    .table-title {
        font-size: 24px;
        font-weight: bold;
        color: #0288d1;
    }
    .form-container {
        display: flex;
        justify-content: space-between;
        padding: 20px;
        box-sizing: border-box;
        background-color: #ffffff; 
    }
    .form-section {
        width: 32%; /* Cada formulario ocupa un tercio del espacio */
        background-color: #ffffff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .form-section h2 {
        font-size: 18px;
        color: #333;
        text-align: center;
    }
    input[type="text"] {
        width: 100%;
        padding: 8px;
        margin: 5px 0;
        box-sizing: border-box;
    }
    button {
        background-color: #0288d1; /* Azul fuerte */
        color: white;
        padding: 10px;
        border: none;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
        margin-top: 10px;
        border-radius: 5px;
    }
    button:hover {
        background-color: #0277bd;
    }
</style>
</head>
<body>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th colspan="2" >Directivos</th>
            </tr>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($directivos as $directivo): ?>
            <tr>
                <td><?php echo htmlspecialchars($directivo['cod_directivo']); ?></td>
                <td><?php echo htmlspecialchars($directivo['nomb_directivo']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<div class="form-container">
    <!-- Formulario de creación -->
    <div class="form-section">
        <h2>Crear Directivo</h2>
        <form method="POST">
            <label for="cod_directivo_create">Código del Directivo:</label>
            <input type="text" name="cod_directivo_create" required>
            <br>
            <button type="submit" name="check_create">siguiente</button>
        </form>
    </div>

    <!-- Formulario de actualización -->
    <div class="form-section">
        <h2>Actualizar Directivo</h2>
        <form method="POST">
            <label for="cod_directivo_update">Código del Directivo:</label>
            <input type="text" name="cod_directivo_update" required>
            <br>
            <label for="nomb_directivo_update">Nuevo Nombre:</label>
            <input type="text" name="nomb_directivo_update" required>
            <br>
            <button type="submit" name="update">Actualizar</button>
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


<?php

// Maneja la creación, actualización y eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check_create'])) {
        $cod_directivo = $_POST['cod_directivo_create'];
    
        // Verificar longitud del código
        if (strlen($cod_directivo) > 3) {
            echo "<script>alert('El código de directivo debe tener máximo 3 caracteres. Intenta con un código diferente.');</script>";
        } else {
            // Verificar si el código de directivo ya existe
            $sql_check = "SELECT COUNT(*) FROM directivos WHERE cod_directivo = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->execute([$cod_directivo]);
            $exists = $stmt_check->fetchColumn();
    
            if ($exists > 0) {
                echo "<script>alert('El código de directivo ya existe. Intenta con un código diferente.');</script>";
            } else {
                // Si el código no existe, solicitar el nombre
                echo '<div class="form-section">
                <h2>Crear Directivo</h2>
                <form method="POST">
                    <input type="hidden" name="cod_directivo_create" value="'.htmlspecialchars($cod_directivo).'">
                    <label for="nomb_directivo_create">Nombre:</label>
                    <input type="text" name="nomb_directivo_create" required>
                    <br>
                    <button type="submit" name="create">Crear Directivo</button>
                </form>
              </div>';
            }
        }
    }
    
    if (isset($_POST['create'])) {
        // Obtener los datos del formulario
        $cod_directivo = $_POST['cod_directivo_create'];
        $nomb_directivo = $_POST['nomb_directivo_create'];
    
        // Insertar el nuevo directivo
        $sql = "INSERT INTO directivos (cod_directivo, nomb_directivo) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$cod_directivo, $nomb_directivo])) {
            echo "<script>
                    alert('Directivo creado con éxito.');
                    window.location.href = window.location.href;
                  </script>";
        } else {
            echo "<p>Error al crear el directivo.</p>";
        }
    }elseif (isset($_POST['update'])) {
    // Actualizar directivo
    $cod_directivo = $_POST['cod_directivo_update'];
    $nomb_directivo = $_POST['nomb_directivo_update'];
        
    // Verificar si el código de directivo existe
    $sql_check = "SELECT COUNT(*) FROM directivos WHERE cod_directivo = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([$cod_directivo]);
    $exists = $stmt_check->fetchColumn();

    if ($exists == 0) {
        echo "<script>alert('El código que desea actualizar no se encuentra en la tabla. Intenta de nuevo');</script>";
    } else {
        // Actualizar el directivo
        $sql = "UPDATE directivos SET nomb_directivo = ? WHERE cod_directivo = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$nomb_directivo, $cod_directivo])) {
            echo "<script>
        alert('Directivo actualizado con éxito.');
        window.location.href = window.location.href;
      </script>";
            exit;
        } else {
            echo "<p>Error al actualizar el directivo.</p>";
        }
    }

    } elseif (isset($_POST['delete'])) {
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
                exit;
            } else {
                echo "<p>Error al eliminar el directivo.</p>";
            }
        }
    }
}    
?>
</body>
</html>

<?php
}
?>
