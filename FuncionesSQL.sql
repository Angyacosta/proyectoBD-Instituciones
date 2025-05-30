
CREATE OR REPLACE FUNCTION filtros(nombre_estado varchar,nombre_sede varchar, nombre_sector varchar, nombre_caracter varchar, nombre_acto varchar, nombre_norma varchar, nombre_depar varchar, nombre_institucion text, codigo_institucion integer)
RETURNS TABLE (nomb_inst text, cod_inst integer, cod_ies_padre integer, nomb_sector varchar, nomb_academ varchar, nomb_depto varchar, nomb_munic varchar, nomb_estado varchar, programas_vigente integer, programas_convenio integer, acreditada boolean) AS $$
BEGIN
    RETURN QUERY
    SELECT
    i.nomb_inst, ipm.cod_inst, i.cod_ies_padre, s.nomb_sector, ca.nomb_academ, d.nomb_depto, m.nomb_munic, e.nomb_estado, ipm.programas_vigente, ipm.programas_convenio, ipm.acreditada
    
    FROM public.inst_por_municipio ipm
    JOIN public.instituciones i ON ipm.cod_ies_padre = i.cod_ies_padre
    LEFT JOIN public.sectores s ON i.cod_sector = s.cod_sector
    LEFT JOIN public.caracter_academico ca ON i.cod_academ = ca.cod_academ
    LEFT JOIN public.municipios m ON ipm.cod_munic = m.cod_munic
    LEFT JOIN public.departamentos d ON m.cod_depto = d.cod_depto
    LEFT JOIN public.estado e ON ipm.cod_estado = e.cod_estado
    LEFT JOIN public.acto_administrativo act ON ipm.cod_admin = act.cod_admin
    LEFT JOIN public.norma_creacion nc ON ipm.cod_norma = nc.cod_norma
    LEFT JOIN public.seccional sc ON ipm.cod_seccional = sc.cod_seccional
    WHERE 
    (nombre_estado IS NULL OR e.nomb_estado = nombre_estado) AND
    (nombre_sede IS NULL OR sc.nomb_seccional = nombre_sede) AND
    (nombre_sector IS NULL OR s.nomb_sector = nombre_sector) AND
    (nombre_caracter IS NULL OR ca.nomb_academ = nombre_caracter) AND
    (nombre_acto IS NULL OR act.nomb_admin = nombre_acto) AND
    (nombre_norma IS NULL OR nc.nomb_norma = nombre_norma) AND
    (nombre_depar IS NULL OR d.nomb_depto = nombre_depar) AND
    (nombre_institucion IS NULL OR i.nomb_inst = nombre_institucion) AND
    (codigo_institucion IS NULL OR ipm.cod_inst = codigo_institucion);
END;
$$ LANGUAGE plpgsql;









CREATE OR REPLACE FUNCTION contar_filtros(nombre_estado varchar, nombre_sede varchar, nombre_sector varchar, nombre_caracter varchar, nombre_acto varchar, nombre_norma varchar, nombre_depar varchar) RETURNS integer AS $$
DECLARE
    cantidad integer;
BEGIN
    SELECT COUNT(*)
    INTO cantidad
    FROM public.inst_por_municipio ipm
    JOIN public.instituciones i ON ipm.cod_ies_padre = i.cod_ies_padre
    LEFT JOIN public.sectores s ON i.cod_sector = s.cod_sector
    LEFT JOIN public.caracter_academico ca ON i.cod_academ = ca.cod_academ
    LEFT JOIN public.municipios m ON ipm.cod_munic = m.cod_munic
    LEFT JOIN public.departamentos d ON m.cod_depto = d.cod_depto
    LEFT JOIN public.estado e ON ipm.cod_estado = e.cod_estado
    LEFT JOIN public.acto_administrativo act ON ipm.cod_admin = act.cod_admin
    LEFT JOIN public.norma_creacion nc ON ipm.cod_norma = nc.cod_norma
    LEFT JOIN public.seccional sc ON ipm.cod_seccional = sc.cod_seccional
    WHERE 
        (nombre_estado IS NULL OR e.nomb_estado = nombre_estado) AND
        (nombre_sede IS NULL OR sc.nomb_seccional = nombre_sede) AND
        (nombre_sector IS NULL OR s.nomb_sector = nombre_sector) AND
        (nombre_caracter IS NULL OR ca.nomb_academ = nombre_caracter) AND
        (nombre_acto IS NULL OR act.nomb_admin = nombre_acto) AND
        (nombre_norma IS NULL OR nc.nomb_norma = nombre_norma) AND
        (nombre_depar IS NULL OR d.nomb_depto = nombre_depar);

    RETURN cantidad;
END;
$$ LANGUAGE plpgsql;



CREATE OR REPLACE FUNCTION contar_filtros(nombre_estado varchar, nombre_norma varchar) RETURNS integer AS $$
DECLARE
    cantidad integer;
BEGIN
    SELECT COUNT(*)
    INTO cantidad
    FROM public.inst_por_municipio ipm
    JOIN public.instituciones i ON ipm.cod_ies_padre = i.cod_ies_padre
    LEFT JOIN public.estado e ON ipm.cod_estado = e.cod_estado
    LEFT JOIN public.norma_creacion nc ON ipm.cod_norma = nc.cod_norma
    WHERE 
        (nombre_estado IS NULL OR e.nomb_estado = nombre_estado) AND
        (nombre_norma IS NULL OR nc.nomb_norma = nombre_norma);

    RETURN cantidad;
END;
$$ LANGUAGE plpgsql;








CREATE OR REPLACE FUNCTION busqueda(nombre_institucion text, codigo_institucion integer)
RETURNS TABLE (nomb_inst text, cod_inst integer, cod_ies_padre integer, nomb_sector varchar, nomb_academ varchar, nomb_depto varchar, nomb_munic varchar, nomb_estado varchar, programas_vigente integer, programas_convenio integer, acreditada boolean) AS $$
BEGIN
    RETURN QUERY
    SELECT
    i.nomb_inst, ipm.cod_inst, i.cod_ies_padre, s.nomb_sector, ca.nomb_academ, d.nomb_depto, m.nomb_munic, e.nomb_estado, ipm.programas_vigente, ipm.programas_convenio, ipm.acreditada
    
    FROM public.inst_por_municipio ipm
    JOIN public.instituciones i ON ipm.cod_ies_padre = i.cod_ies_padre
    LEFT JOIN public.sectores s ON i.cod_sector = s.cod_sector
    LEFT JOIN public.caracter_academico ca ON i.cod_academ = ca.cod_academ
    LEFT JOIN public.municipios m ON ipm.cod_munic = m.cod_munic
    LEFT JOIN public.departamentos d ON m.cod_depto = d.cod_depto
    LEFT JOIN public.estado e ON ipm.cod_estado = e.cod_estado
    LEFT JOIN public.acto_administrativo act ON ipm.cod_admin = act.cod_admin
    LEFT JOIN public.norma_creacion nc ON ipm.cod_norma = nc.cod_norma
    LEFT JOIN public.seccional sc ON ipm.cod_seccional = sc.cod_seccional
    WHERE 
        (nombre_institucion IS NULL OR i.nomb_inst = nombre_institucion) AND
        (codigo_institucion IS NULL OR ipm.cod_inst = codigo_institucion);
END;
$$ LANGUAGE plpgsql;


-----funciones de eliminacion, creacion y actuaizacion ---


---reorganizara los codigos despues de su eliminacion 

create or replace function reajustar_cod()
returns trigger as $$
begin
update directivos 
SET cod_directivo = cod_directivo -1
where cod_directivo> OLD.cod_directivo;
return OLD;
END;
$$ LANGUAGE  plpgsql;

CREATE TRIGGER trigger_reajustar_cod
AFTER DELETE ON directivos
FOR EACH ROW
EXECUTE FUNCTION reajustar_cod();

CREATE OR REPLACE FUNCTION asignar_cod_faltante()
RETURNS TRIGGER AS $$
DECLARE
    menor_cod_disponible INT;
BEGIN
    -- Encuentra el menor código faltante
    SELECT min(cod_directivo + 1) 
    INTO menor_cod_disponible
    FROM directivos
    WHERE (cod_directivo + 1) NOT IN (SELECT cod_directivo FROM directivos);

    -- Si existe un código faltante, úsalo; si no, utiliza la secuencia por defecto
    IF menor_cod_disponible IS NOT NULL THEN
        NEW.cod_directivo = menor_cod_disponible;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Crear el trigger
CREATE TRIGGER trigger_asignar_cod
BEFORE INSERT ON directivos
FOR EACH ROW
EXECUTE FUNCTION asignar_cod_faltante();


--tiggers
CREATE OR REPLACE FUNCTION convertir_a_mayuscula()
RETURNS TRIGGER AS $$
BEGIN
    NEW.nomb_directivo := UPPER(NEW.nomb_directivo);
    NEW.apell_directivo := UPPER(NEW.apell_directivo);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER before_insert_directivos
BEFORE INSERT OR UPDATE ON directivos
FOR EACH ROW
EXECUTE FUNCTION convertir_a_mayuscula();

--crearen mayusculas los nombres 

