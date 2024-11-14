<?php   
include 'conexionDB.php';  
try {   
    $query = "  
        SELECT   
            i.nomb_inst AS nombre_ies,  
            ip.cod_inst AS codigo_ies,  
            i.cod_ies_padre AS ies_padre,  
            s.nomb_sector AS sector,  
            ca.nomb_academ AS caracter_academico,  
            d.nomb_depto AS departamento,  
            m.nomb_munic AS municipio,  
            e.nomb_estado AS estado_ies,  
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
    
} catch (PDOException $e) {  
    echo "Error: " . $e->getMessage();  
    exit;  
}  


?>  


<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">  
    <title>Inicio - Instituciones</title>  
    <link rel="stylesheet" href="styles.css">  
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>  
<body>  

<!--biblioteca CSS Bootstrap.-->

<div class="container-fluid m-2"><!--ocupa la pantalla completa, borde de 3 por todos lados-->
    <div class="row"><!-- alinea siempre de izq a dereche-->
        <div class="col-md-3"><!--ocupa 3 col, alineada izq-->
            <h2>Seleccione los filtros para la búsqueda</h2>

                <!-- Formulario de Filtros de Institución -->
            <div class="mb-4 p-3 bg-light rounded"> <!--  -->
                <h5>Institución de Educación Superior</h5>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Nombre de la Institución</label>
                        <input type="text" class="form-control" name="nombre_institucion">
                    </div>
                    <div class="form-group">
                        <label>Código de la Institución</label>
                        <input type="text" class="form-control" name="codigo_institucion">
                    </div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <button type="reset" class="btn btn-warning">Limpiar</button>
                </form>
            </div>
        

            <h1>Reporte de Instituciones por Departamento</h1>
            <form method="POST" action="">  
                <div class="form-group">  
                    <label>Seleccione un Departamento:</label>  
                    <select class="form-control" name="departamento">  
                        <option value="todos">Todos</option>  
                        <?php 
                            //Consulta SQL para obtener los nombres de los departamentos
                            $dep_query = "SELECT nomb_depto FROM public.departamentos";  // La consulta busca los departamentos
                            $stmt = $conn->query($dep_query);  // Ejecutamos la consulta con el método query de PDO
                            $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Obtenemos todos los resultados como un array asociativo

                            //Bucle que recorre los departamentos para mostrarlos en el select
                            foreach ($departamentos as $departamento): 
                        ?>  
                        <!--Para cada departamento, mostramos una opción en el select , value es lo que se enviara al formulario-->
                        <option value="<?= htmlspecialchars($departamento['nomb_depto']) ?>">
                            <?= htmlspecialchars($departamento['nomb_depto']) ?>  
                        </option>  
                        <?php endforeach; ?>  
                    </select>  
                 </div>  
                <button type="submit" class="btn btn-primary">Buscar</button>  
                <button type="reset" class="btn btn-warning">Limpiar</button>  
            </form>  

            
            <!-- Filtros Generales -->
            <h1 class="mt-3">Filtros Generales</h1>  
            <form method="POST" action="">  
                <div class="form-group">  
                    <label>Estado de la Institución:</label><br>  
                    <input type="radio" name="estado" value="todos" checked> Todos  
                    <input type="radio" name="estado" value="activo"> Activo (<?= $count_activo ?>)  
                    <input type="radio" name="estado" value="inactivo"> Inactivo (<?= $count_inactivo ?>)  
                </div>  
                <div class="form-group">  
                    <label>Tipo de sede:</label><br>  
                    <select class="form-control" name="tipo_sede">  
                        <option value="todos">Todos (<?= $count_tipo_sede_todos ?>)</option>  
                        <option value="principal">Principal (<?= $count_tipo_sede_principal ?>)</option>  
                        <option value="seccional">Seccional (<?= $count_tipo_sede_seccional ?>)</option>  
                    </select>  
                </div>  
                <div class="form-group">  
                    <label>Carácter académico:</label><br>  
                    <select class="form-control" name="caracter_academico">  
                        <option value="todos">Todos (<?= $count_academico_todos ?>)</option>  
                        <option value="tecnica_profesional">Institución Técnica Profesional (<?= $count_tecnica_profesional ?>)</option>  
                        <option value="tecnologica">Institución Tecnológica (<?= $count_tecnologica ?>)</option>  
                        <option value="universidad">Universidad (<?= $count_universidad ?>)</option>  
                    </select>  
                </div>  
                <div class="form-group">  
                    <label>Sector:</label><br>  
                    <select class="form-control" name="sector">  
                        <option value="todos">Todos (<?= $count_sector_todos ?>)</option>  
                        <option value="publica">Pública (<?= $count_sector_publica ?>)</option>  
                        <option value="privada">Privada (<?= $count_sector_privada ?>)</option>  
                    </select>  
                </div>  
                <button type="submit" class="btn btn-primary">Buscar</button>  
                <button type="reset" class="btn btn-warning">Limpiar</button>  
            </form>
        </div>

        <!-- Tabla de Resultados a la Derecha -->
        <div class="col-md-9">
            <div class="container" style="overflow-x: auto;">  
                <table class="table table-bordered mt-2" style="text-align: center;">  
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
                        <?php if ($instituciones): ?>  
                            <?php foreach ($instituciones as $inst): ?>  
                                <tr>  
                                    <td><?= htmlspecialchars($inst['nombre_ies']) ?></td>  
                                    <td><?= htmlspecialchars($inst['codigo_ies']) ?></td>  
                                    <td><?= htmlspecialchars($inst['ies_padre']) ?></td>  
                                    <td><?= htmlspecialchars($inst['sector']) ?></td>  
                                    <td><?= htmlspecialchars($inst['caracter_academico']) ?></td>  
                                    <td><?= htmlspecialchars($inst['departamento'] . " / " . $inst['municipio']) ?></td>  
                                    <td><?= htmlspecialchars($inst['estado_ies']) ?></td>  
                                    <td><?= htmlspecialchars($inst['programas_vigente']) ?></td>  
                                    <td><?= htmlspecialchars($inst['programas_convenio']) ?></td>  
                                    <td><?= $inst['acreditada'] ? 'Sí' : 'No' ?></td>  
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

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>  
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>  
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>  
</body>  
</html>

