<?php   
include 'conexionDB.php';  
ini_set('display_errors', 1);
error_reporting(E_ALL);
try {   
    $query = "  
        SELECT   
            i.nomb_inst,   
            ip.cod_inst,   
            i.cod_ies_padre,  
            s.nomb_sector, 
            ca.nomb_academ, 
            d.nomb_depto, 
            m.nomb_munic, 
            e.nomb_estado, 
            ip.programas_vigente,  
            ip.programas_convenio,  
            ip.acreditada   
        FROM   
            public.instituciones i  
        JOIN   
            public.sectores s ON i.cod_sector = s.cod_sector  
        JOIN   
            public.caracter_academico ca ON i.cod_academ = ca.cod_academ  
        JOIN   
            public.inst_por_municipio ip ON i.cod_ies_padre = ip.cod_ies_padre  
        JOIN   
            public.municipios m ON ip.cod_munic = m.cod_munic  
        JOIN   
            public.departamentos d ON m.cod_depto = d.cod_depto  
        JOIN   
            public.estado e ON ip.cod_estado = e.cod_estado  
    ";  

    $stmt = $conn->prepare($query);  
    $stmt->execute();  
    $instituciones = $stmt->fetchAll(PDO::FETCH_ASSOC);  

    //funcion generica para extraer las consultas
    function funcion($conn, $query) {
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $instS = funcion($conn, "SELECT nomb_inst FROM public.instituciones");
    $codinstS = funcion($conn, "SELECT cod_inst FROM public.inst_por_municipio ");
    $departamentos = funcion($conn, "SELECT nomb_depto FROM public.departamentos");
    $actos_administrativos = funcion($conn, "SELECT nomb_admin FROM public.acto_administrativo");
    $normas_creaciones = funcion($conn, "SELECT nomb_norma FROM public.norma_creacion");

    // Pasar los valores de los filtros como parámetros a la función filtros
    $nombre_estado = $_POST['nombre_estado'] !== "Todos" ? $_POST['nombre_estado'] : null;
    $nombre_sede = $_POST['nombre_sede'] !== "Todos" ? $_POST['nombre_sede'] : null;
    $nombre_sector = $_POST['nombre_sector'] !== "Todos" ? $_POST['nombre_sector'] : null;
    $nombre_caracter = $_POST['nombre_caracter'] !== "Todos" ? $_POST['nombre_caracter'] : null;
    $nombre_acto = $_POST['nombre_acto'] !== "Todos" ? $_POST['nombre_acto'] : null;
    $nombre_norma = $_POST['nombre_norma'] !== "Todos" ? $_POST['nombre_norma'] : null;
    $nombre_depar = $_POST['nombre_depar'] !== "Todos" ? $_POST['nombre_depar'] : null;
    $nombre_insti = $_POST['nombre_insti'] !== "Todos" ? $_POST['nombre_insti'] : null;
    $codigo_insti = $_POST['codigo_insti'] !== "Todos" ? $_POST['codigo_insti'] : null;

    // Llamar a la función y pasar los valores por parámetro
    $queryFiltros = "
        SELECT * 
        FROM filtros(:nombre_estado, :nombre_sede, :nombre_sector, :nombre_caracter, :nombre_acto, :nombre_norma, :nombre_depar, :nombre_insti, :codigo_insti);
    ";
    $stmt = $conn->prepare($queryFiltros);

    // Vincular parámetros
    $stmt->bindParam(':nombre_estado', $nombre_estado, PDO::PARAM_STR);
    $stmt->bindParam(':nombre_sede', $nombre_sede, PDO::PARAM_STR);
    $stmt->bindParam(':nombre_sector', $nombre_sector, PDO::PARAM_STR);
    $stmt->bindParam(':nombre_caracter', $nombre_caracter, PDO::PARAM_STR);
    $stmt->bindParam(':nombre_acto', $nombre_acto, PDO::PARAM_STR);
    $stmt->bindParam(':nombre_norma', $nombre_norma, PDO::PARAM_STR);
    $stmt->bindParam(':nombre_depar', $nombre_depar, PDO::PARAM_STR);
    $stmt->bindParam(':nombre_insti', $nombre_insti, PDO::PARAM_STR);
    $stmt->bindParam(':codigo_insti', $codigo_insti, PDO::PARAM_INT);


    // Ejecutar la consulta
    $stmt->execute();

    // Obtener los resultados
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
} catch (PDOException $e) {  
    echo "Error: " . $e->getMessage();  
    exit;  
} 
?>  
<style>  
    select.form-control {  
       position: relative; /* Asegura el contexto de posicionamiento */  
    }  
</style> 

<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">  
    <title>Inicio - Instituciones</title>  
    <link rel="stylesheet" href="styles.css">  
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>  
<body style="background-color: #f0f0f0; color: #333; font-family: Arial, sans-serif; margin: 0; padding: 0;">

<div class="header" style="margin-bottom: 20px; padding: 10px; width: 100%; background-color: #fff;">
    <img src="logo_men.png" alt="Logo de la institución" style="max-width: 100%; height: auto; margin-bottom: 20px;">
    
    <!-- Contenedor de botones -->
    <div class="buttons" style="margin-top: 10px;">
        <a href="actualizar.php" style="text-decoration: none; padding: 10px 20px; color: white; background-color: #5bc0de; border-radius: 5px; display: inline-block; margin: 10px;">Consultar Instituciones</a>
        <a href=".php" style="text-decoration: none; padding: 10px 20px; color: white; background-color: #5bc0de; border-radius: 5px; display: inline-block; margin: 10px;">Consultar Directivos</a>
    </div>
</div>



<div class="container-fluid m-3">
    <div class="row">
        <div class="col-md-3">
            <!-- Formulario de Filtros de Institución -->
            <div class="mb-4 p-4 bg-light rounded border" style="margin: 3px; background-color: #f8f9fa;">
                <h2>Institución Universitaria</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Nombre de la Institución</label>
                        <select class="form-control" name="nombre_insti">
                        <option value="Todos" <?= isset($nombre_insti) && $nombre_insti === 'Todos' ? 'selected' : '' ?>>Todos</option>
                            <?php 
                                foreach ($instS as $inst): 
                            ?>  
                        <option value="<?= htmlspecialchars($inst['nomb_inst']) ?>" <?= isset($nombre_insti) && $nombre_insti === $inst['nomb_inst'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($inst['nomb_inst']) ?>
                        </option>  
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Código de la Institución</label>
                        <select class="form-control" name="codigo_insti">
                        <option value="Todos" <?= isset($codigo_insti) && $codigo_insti === 'Todos' ? 'selected' : '' ?>>Todos</option>
                            <?php 
                                foreach ($codinstS as $codinst): 
                            ?>  
                        <option value="<?= htmlspecialchars($codinst['cod_inst']) ?>" <?= isset($codigo_insti) && $codigo_insti === $codinst['cod_inst'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($codinst['cod_inst']) ?>
                        </option>  
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <button type="reset" class="btn btn-secondary">Limpiar</button> 
                </form>
            </div>
            <!-- Filtros Generales -->
        <div class="mb-4 p-4 bg-light rounded border" style="margin: 3px; background-color: #f8f9fa;">
            <h2 class="mt-3">Filtros Generales</h2>
            <form method="POST" action="">  
                <div class="form-group"> 
                    <label>Estado de la Institución:</label><br>  
                    <input type="radio" name="nombre_estado" value="Todos" <?= $nombre_estado === 'Todos' ? 'checked' : '' ?> checked> Todos<br>
                    <input type="radio" name="nombre_estado" value="Activa" <?= $nombre_estado === 'Activa' ? 'checked' : '' ?>> Activo  <br>
                    <input type="radio" name="nombre_estado" value="Inactiva" <?= $nombre_estado === 'Inactiva' ? 'checked' : '' ?>> Inactivo  <br>
                </div>  
                <div class="form-group">
                    <label>Nombre norma:</label><br>
                    <input type="radio" name="nombre_norma" value="Todos" <?= $nombre_norma=== 'Todos' ? 'checked' : '' ?> checked> Todos<br>
                    <?php foreach ($normas_creaciones as $norma_creacion): ?>
                        <div>
                            <input type="radio" name="nombre_norma" value="<?= htmlspecialchars($norma_creacion['nomb_norma']) ?>" 
                                <?= isset($nombre_norma) && $nombre_norma === $norma_creacion['nomb_norma'] ? 'checked' : '' ?>>
                            <?= htmlspecialchars($norma_creacion['nomb_norma']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-group">
                    <label>Seleccione un Departamento:</label>
                    <select class="form-control" name="nombre_depar">
                        <option value="Todos" <?= isset($nombre_depar) && $nombre_depar === 'Todos' ? 'selected' : '' ?>>Todos</option>
                            <?php 
                                foreach ($departamentos as $departamento): 
                            ?>  
                        <option value="<?= htmlspecialchars($departamento['nomb_depto']) ?>" <?= isset($nombre_depar) && $nombre_depar === $departamento['nomb_depto'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($departamento['nomb_depto']) ?>
                        </option>  
                            <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">  
                    <label>Tipo de sede:</label><br>  
                    <select class="form-control" name="nombre_sede">  
                        <option value="Todos" <?= $nombre_sede === 'Todos' ? 'selected' : '' ?>>Todos</option>  
                        <option value="Principal" <?= $nombre_sede === 'Principal' ? 'selected' : '' ?>>Principal</option>  
                        <option value="Seccional" <?= $nombre_sede === 'Seccional' ? 'selected' : '' ?>>Seccional</option>  
                    </select>  
                </div>  
                <div class="form-group">  
                    <label>Sector:</label><br>  
                    <select class="form-control" name="nombre_sector">  
                        <option value="Todos" <?= $nombre_sector === 'Todos' ? 'selected' : '' ?>>Todos</option>  
                        <option value="Oficial" <?= $nombre_sector === 'Oficial' ? 'selected' : '' ?>>Pública</option>  
                        <option value="Privado" <?= $nombre_sector === 'Privado' ? 'selected' : '' ?>>Privada</option>  
                    </select>  
                </div> 
                <div class="form-group">  
                    <label>Carácter académico:</label><br>  
                    <select class="form-control" name="nombre_caracter">  
                        <option value="Todos" <?= $nombre_caracter === 'Todos' ? 'selected' : '' ?>>Todos</option>  
                        <option value="Institución Técnica Profesional" <?= $nombre_caracter === 'Institución Técnica Profesional' ? 'selected' : '' ?>>Institución Técnica Profesional</option>  
                        <option value="Institución Tecnológica" <?= $nombre_caracter === 'Institución Tecnológica' ? 'selected' : '' ?>>Institución Tecnológica</option>  
                        <option value="Institución Universitaria/Escuela Tecnológica" <?= $nombre_caracter === 'Institución Universitaria/Escuela Tecnológica' ? 'selected' : '' ?>>Institución Universitaria/Escuela Tecnológica</option>
                        <option value="Universidad" <?= $nombre_caracter === 'Universidad' ? 'selected' : '' ?>>Universidad</option>  
                    </select>  
                </div>  
                <div class="form-group">  
                    <label>Acto administrativo:</label><br>  
                    <select class="form-control" name="nombre_acto">
                        <option value="Todos" <?= isset($nombre_acto) && $nombre_acto === 'Todos' ? 'selected' : '' ?>>Todos</option>
                            <?php 
                                foreach ($actos_administrativos as $acto_administrativo): 
                            ?>  
                        <!-- aqui va es el el nomb_admin por como se llama en la base que es en postgres-->  
                        <option value="<?= htmlspecialchars($acto_administrativo['nomb_admin']) ?>" <?= isset($nombre_acto) && $nombre_acto === $acto_administrativo['nomb_admin'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($acto_administrativo['nomb_admin']) ?>
                        </option>  
                        <?php endforeach; ?>
                    </select>  
                </div> 
                <button type="submit" class="btn btn-primary">Buscar</button>  
                <button type="reset" class="btn btn-secondary">Limpiar</button>  
            </form>
        </div>
    </div>
        <!-- Tabla de Resultados -->
        <div class="col-md-9 mb-4 p-3 bg-light rounded border" style="background-color: #f8f9fa;">
            <div class="col-auto" style="margin: 4px">
                <label for="num_registros" class="col-form-label">Mostrar </label>
                <select name="num_registros" id="num_registros" class="form-select">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <label for="num_registros" class="col-form-label">Registros</label>
            </div>

            <div class="col-auto m-4" >  

                <table class="table table-bordered mt-1" style="text-align: center;">  
                    <thead class="thead-dark">  
                        <tr>  
                            <th>Nombre IES</th>  
                            <th>Código IES</th>  
                            <th>IES Padre</th>  
                            <th>Sector</th>  
                            <th>Carácter académico</th>  
                            <th>Departamento / Municipio</th>  
                            <th>Estado IES</th>  
                            <th>Programas vigentes</th>  
                            <th>Programas en convenio</th>  
                            <th>¿Acreditada?</th>  
                        </tr>  
                    </thead>  
                    <tbody>         

                        <?php if (!empty($resultados)): ?>
                            <?php foreach ($resultados as $institucion): ?>
                                <tr>
                                    <td><?= htmlspecialchars($institucion['nomb_inst']) ?></td>
                                    <td><?= htmlspecialchars($institucion['cod_inst']) ?></td>
                                    <td><?= htmlspecialchars($institucion['cod_ies_padre']) ?></td>
                                    <td><?= htmlspecialchars($institucion['nomb_sector']) ?></td>
                                    <td><?= htmlspecialchars($institucion['nomb_academ']) ?></td>
                                    <td><?= htmlspecialchars($institucion['nomb_depto'] . " / " . $institucion['nomb_munic']) ?></td>
                                    <td><?= htmlspecialchars($institucion['nomb_estado']) ?></td>
                                    <td><?= htmlspecialchars($institucion['programas_vigente']) ?></td>
                                    <td><?= htmlspecialchars($institucion['programas_convenio']) ?></td>
                                    <td><?= $institucion['acreditada'] ? 'Sí' : 'No' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10">No se encontraron datos.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    
                </table>  
            </div>  
        </div>
    </div>
</div>
</body>
</html>
