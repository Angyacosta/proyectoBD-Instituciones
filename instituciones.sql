-- Configuración inicial de la sesión
SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;
SET default_tablespace = '';
SET default_table_access_method = heap;

-- Creación de tablas según el UML del profesor

-- Tabla: sectores
CREATE TABLE public.sectores (
    cod_sector VARCHAR(2) NOT NULL,
    nomb_sector VARCHAR(50)
);
ALTER TABLE public.sectores OWNER TO postgres;

-- Tabla: caracter_academico
CREATE TABLE public.caracter_academico (
    cod_academ VARCHAR(2) NOT NULL,
    nomb_academ VARCHAR(50)
);
ALTER TABLE public.caracter_academico OWNER TO postgres;

-- Tabla: instituciones
CREATE TABLE public.instituciones (
    cod_inst VARCHAR(3) NOT NULL,
    nomb_ins VARCHAR(50),
    cod_sector VARCHAR(2) NOT NULL,
    cod_academ VARCHAR(2) NOT NULL
);
ALTER TABLE public.instituciones OWNER TO postgres;

-- Tabla: departamentos
CREATE TABLE public.departamentos (
    cod_depto VARCHAR(2) NOT NULL,
    nomb_depto VARCHAR(20)
);
ALTER TABLE public.departamentos OWNER TO postgres;

-- Tabla: municipios
CREATE TABLE public.municipios (
    cod_munic VARCHAR(4) NOT NULL,
    nomb_munic VARCHAR(20),
    cod_depto VARCHAR(2) NOT NULL
);
ALTER TABLE public.municipios OWNER TO postgres;

-- Tabla: norma_creacion
CREATE TABLE public.norma_creacion (
    cod_norma VARCHAR(3) NOT NULL,
    nomb_norma VARCHAR(50)
);
ALTER TABLE public.norma_creacion OWNER TO postgres;

-- Tabla: acto_admon
CREATE TABLE public.acto_admon (
    cod_admon VARCHAR(3) NOT NULL,
    nomb_admon VARCHAR(50)
);
ALTER TABLE public.acto_admon OWNER TO postgres;

-- Tabla: seccional
CREATE TABLE public.seccional (
    cod_seccional VARCHAR(4) NOT NULL,
    nomb_seccional VARCHAR(50)
);
ALTER TABLE public.seccional OWNER TO postgres;

-- Tabla: naturaleza_juridica
CREATE TABLE public.naturaleza_juridica (
    cod_juridica VARCHAR(2) NOT NULL,
    nomb_juridica VARCHAR(20)
);
ALTER TABLE public.naturaleza_juridica OWNER TO postgres;

-- Tabla: estados
CREATE TABLE public.estados (
    cod_estado VARCHAR(2) NOT NULL,
    nomb_estado VARCHAR(20)
);
ALTER TABLE public.estados OWNER TO postgres;

-- Tabla: inst_por_municipio
CREATE TABLE public.inst_por_municipio (
    cod_munic VARCHAR(4) NOT NULL,
    cod_inst VARCHAR(3) NOT NULL,
    direccion VARCHAR(100),
    telefono VARCHAR(10),
    norma VARCHAR(50),
    fecha_creacion DATE,
    programas_vigentes INT,
    acreditada BOOLEAN,
    fecha_acreditacion DATE,
    resolucion_acreditacion VARCHAR(50),
    vigencia INT,
    nit VARCHAR(20),
    pagina_web VARCHAR(100),
    cod_norma VARCHAR(3) NOT NULL,
    cod_admon VARCHAR(3) NOT NULL,
    cod_seccional VARCHAR(4) NOT NULL,
    cod_juridica VARCHAR(2) NOT NULL,
    cod_estado VARCHAR(2) NOT NULL
);
ALTER TABLE public.inst_por_municipio OWNER TO postgres;

-- Tabla: cargos
CREATE TABLE public.cargos (
    cod_cargo VARCHAR(2) NOT NULL,
    nomb_cargo VARCHAR(40)
);
ALTER TABLE public.cargos OWNER TO postgres;

-- Tabla: acto_nombramiento
CREATE TABLE public.acto_nombramiento (
    cod_nombram VARCHAR(3) NOT NULL,
    nomb_nombram VARCHAR(50)
);
ALTER TABLE public.acto_nombramiento OWNER TO postgres;

-- Tabla: directivos
CREATE TABLE public.directivos (
    cod_directivo SERIAL NOT NULL,
    nomb_directivo VARCHAR(50),
    apell_directivo VARCHAR(50)
);
ALTER TABLE public.directivos OWNER TO postgres;

-- Tabla: rectoria
CREATE TABLE public.rectoria (
    fecha_inicio DATE,
    fecha_final DATE,
    cod_directivo SERIAL NOT NULL,
    cod_munic VARCHAR(4) NOT NULL,
    cod_inst VARCHAR(3) NOT NULL,
    cod_nombram VARCHAR(3) NOT NULL,
    cod_cargo VARCHAR(2) NOT NULL
);
ALTER TABLE public.rectoria OWNER TO postgres;

-- Claves primarias
ALTER TABLE ONLY public.sectores ADD CONSTRAINT sectores_pkey PRIMARY KEY (cod_sector);
ALTER TABLE ONLY public.caracter_academico ADD CONSTRAINT caracter_academico_pkey PRIMARY KEY (cod_academ);
ALTER TABLE ONLY public.instituciones ADD CONSTRAINT instituciones_pkey PRIMARY KEY (cod_inst);
ALTER TABLE ONLY public.cargos ADD CONSTRAINT cargos_pkey PRIMARY KEY (cod_cargo);
ALTER TABLE ONLY public.acto_nombramiento ADD CONSTRAINT acto_nombramiento_pkey PRIMARY KEY (cod_nombram);
ALTER TABLE ONLY public.directivos ADD CONSTRAINT directivos_pkey PRIMARY KEY (cod_directivo);
ALTER TABLE ONLY public.departamentos ADD CONSTRAINT departamentos_pkey PRIMARY KEY (cod_depto);
ALTER TABLE ONLY public.municipios ADD CONSTRAINT municipios_pkey PRIMARY KEY (cod_munic);
ALTER TABLE ONLY public.norma_creacion ADD CONSTRAINT norma_creacion_pkey PRIMARY KEY (cod_norma);
ALTER TABLE ONLY public.acto_admon ADD CONSTRAINT acto_admon_pkey PRIMARY KEY (cod_admon);
ALTER TABLE ONLY public.seccional ADD CONSTRAINT seccional_pkey PRIMARY KEY (cod_seccional);
ALTER TABLE ONLY public.naturaleza_juridica ADD CONSTRAINT naturaleza_juridica_pkey PRIMARY KEY (cod_juridica);
ALTER TABLE ONLY public.estados ADD CONSTRAINT estados_pkey PRIMARY KEY (cod_estado);
ALTER TABLE ONLY public.inst_por_municipio ADD CONSTRAINT inst_por_municipio_pkey PRIMARY KEY (cod_munic, cod_inst);
ALTER TABLE ONLY public.rectoria ADD CONSTRAINT rectoria_pkey PRIMARY KEY (cod_munic, cod_inst, cod_directivo);

-- Claves foráneas
ALTER TABLE ONLY public.instituciones
    ADD CONSTRAINT fk_institucionesect FOREIGN KEY (cod_sector) REFERENCES public.sectores (cod_sector),
    ADD CONSTRAINT fk_institucionescaract FOREIGN KEY (cod_academ) REFERENCES public.caracter_academico (cod_academ);
ALTER TABLE ONLY public.municipios
    ADD CONSTRAINT fk_municipiosdepto FOREIGN KEY (cod_depto) REFERENCES public.departamentos (cod_depto);
ALTER TABLE ONLY public.inst_por_municipio
    ADD CONSTRAINT fk_inst_mun FOREIGN KEY (cod_munic) REFERENCES public.municipios (cod_munic),
    ADD CONSTRAINT fk_inst_inst FOREIGN KEY (cod_inst) REFERENCES public.instituciones (cod_inst),
    ADD CONSTRAINT fk_inst_norma FOREIGN KEY (cod_norma) REFERENCES public.norma_creacion (cod_norma),
    ADD CONSTRAINT fk_inst_estado FOREIGN KEY (cod_estado) REFERENCES public.estados (cod_estado),
    ADD CONSTRAINT fk_inst_admon FOREIGN KEY (cod_admon) REFERENCES public.acto_admon (cod_admon),
    ADD CONSTRAINT fk_inst_juridica FOREIGN KEY (cod_juridica) REFERENCES public.naturaleza_juridica (cod_juridica),
    ADD CONSTRAINT fk_inst_seccional FOREIGN KEY (cod_seccional) REFERENCES public.seccional (cod_seccional);
ALTER TABLE ONLY public.rectoria
    ADD CONSTRAINT fk_rectoria_inst_mun FOREIGN KEY (cod_munic, cod_inst) REFERENCES public.inst_por_municipio (cod_munic, cod_inst),
    ADD CONSTRAINT fk_rectoria_acto FOREIGN KEY (cod_nombram) REFERENCES public.acto_nombramiento (cod_nombram),
    ADD CONSTRAINT fk_rectoria_dir FOREIGN KEY (cod_directivo) REFERENCES public.directivos (cod_directivo),
    ADD CONSTRAINT fk_rectoria_cargos FOREIGN KEY (cod_cargo) REFERENCES public.cargos (cod_cargo);

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
