//por ahora esinicio.php despues es index.php


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instituciones por Municipio</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            color: #333;
        }.container {
            width: 30%; /* Controlamos el tamaño del contenedor */
            margin: 0 auto; /* Centra el contenedor */
        }
        .table-container {
            margin: 10px auto;
            width: 90%;
            margin-left: 10px /* Agrega margen desde el lado izquierdo */
            float: right; /* Hace que la tabla se mueva a la derecha */
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
            box-sizing: border-box;
            font-size: 12px;
        }
        table {
        width: 100%;
        border-collapse: collapse;
        }
        th, td {
            font-size: 14px;
            padding: 12px 15px;
            text-align: center;
            border: black 1px solid;
        }
        th {
            font-size: 16px;
            background-color: #0277bd;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e3f2fd;
        }
        td {
            border-bottom: 1px solid #ddd;
        }
        .update-link {
            margin: 20px 0;
        } 
        .update-link a {
            display: inline-block;
            padding: 12px 20px;
            background-color: #0277bd;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .update-link a:hover {
            background-color: #01579b;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            margin: 0 5px;
            padding: 10px 15px;
            text-decoration: none;
            background-color: #f2f2f2;
            color: black;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .pagination a:hover {
            background-color: #ddd;
        }
        .pagination a[style] {
            background-color: #0277bd !important;
            color: #fff;
        }
    </style>
</head>
<body>
    <!-- Mostrar registros de instituciones -->
    <div class="container">
            <!-- Enlace para actualizar -->
    <div class="update-link">
    <a href="actualizar.php" class="update-btn">Actualizar Institución</nav></a>
    <a href="consultasDirectivos.php" class="update-btn"> Consulta Directivos</nav></a>
    </div>

        <table>
            <thead>
                <tr>
                <th colspan="10" >Instituciones</th>
                </tr>
                <tr>   
                    <th>Nombre IES</th>
                    <th>Código IES</th>
                    <th>Sector</th>
                    <th>Carater Academico</th>
                    <th>Departamento / municipio</th>
                    <th>Estado </th>
                    <th>Programas Vigentes</th>
                    <th>Acreditación</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>                    
                </tr>
            </thead>
            <tbody>
                <?php foreach ($instituciones as $inst): ?>
                <tr>
                    <td><?php echo htmlspecialchars($inst['nomb_inst']); ?></td>
                    <td><?php echo htmlspecialchars($inst['cod_inst']); ?></td>
                    <td><?php echo htmlspecialchars($inst['nomb_sector']); ?></td>
                    <td><?php echo htmlspecialchars($inst['nomb_academ']); ?></td>
                    <td><?php echo htmlspecialchars($inst['depto_munic']); ?></td>
                    <td><?php echo htmlspecialchars($inst['nomb_estado']); ?></td>
                    <td><?php echo htmlspecialchars($inst['programas_vigentes']); ?></td>
                    <td><?php echo htmlspecialchars($inst['acreditada']); ?></td>
                    <td><?php echo htmlspecialchars($inst['direccion']); ?></td>
                    <td><?php echo htmlspecialchars($inst['telefono']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="pagination">
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
</body>
</html>


<?php
include 'conexionDB.php';


$sql = "
    SELECT 
    i.cod_munic, 
    m.nombre_munic, 
    d.nombre_departamento, 
    i.cod_inst,
    I.nomb_inst, 
    i.direccion, 
    i.telefono, 
    i.fecha_creacion,
    S.nomb_sector,
    C.nomb_academ,
    E.nomb_estado,
    i.programas_vigentes, 
    i.acreditada,
    CONCAT(d.nombre_departamento, ' / ', m.nombre_munic) AS depto_munic
FROM 
    inst_por_municipio i
JOIN 
    municipios m ON i.cod_munic = m.cod_munic
JOIN 
    departamentos d ON m.cod_departamento = d.cod_departamento
JOIN 
    estados E ON i.cod_estado = E.cod_estado
JOIN
    instituciones I ON i.cod_inst = I.cod_inst
JOIN 
    sectores S ON I.cod_sector = S.cod_sector
JOIN 
    caracter_academico C ON I.cod_academ = C.cod_academ
";
$stmt = $conn->prepare($sql);
$stmt->execute();

// Recuperar los resultados de la consulta
$instituciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Obtener la página actual, por defecto es la 1
$offset = ($page - 1) * $limit; // Calcular el desplazamiento de la consulta

// Obtener los directivos limitados
$sql = "SELECT * FROM inst_por_municipio ORDER BY cod_inst ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$limit, $offset]);
$institucione = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener el total de directivos para calcular las páginas
$sql_count = "SELECT COUNT(*) FROM inst_por_municipio";
$stmt_count = $conn->query($sql_count);
$total_instituciones = $stmt_count->fetchColumn();
$total_pages = ceil($total_instituciones / $limit); // Calcular el total de páginas
   
?>
