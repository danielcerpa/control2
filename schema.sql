CREATE DATABASE control_escolar;

USE control_escolar;

-- tabla de usuarios, maneja el login de todos (alumnos, profes, director)
CREATE TABLE usuarios (
    id_usuario BIGINT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    estado BOOLEAN DEFAULT TRUE
);

-- modulos del sistema, para el control de permisos
CREATE TABLE modulos (
    id_modulo BIGINT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(150) NOT NULL,
    area VARCHAR(50) NOT NULL,
    estado BOOLEAN NOT NULL
);

-- ciclos escolares
CREATE TABLE ciclos_escolares (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado VARCHAR(20) DEFAULT 'Activo'
);

-- perfiles definen que puede hacer un usuario dentro de un modulo
CREATE TABLE perfiles (
    id_perfil BIGINT AUTO_INCREMENT PRIMARY KEY,
    apodo VARCHAR(50),
    descripcion VARCHAR(300),
    clave_agregar BOOLEAN NOT NULL DEFAULT FALSE,
    clave_eliminar BOOLEAN NOT NULL DEFAULT FALSE,
    clave_editar BOOLEAN NOT NULL DEFAULT FALSE,
    clave_exportar BOOLEAN NOT NULL DEFAULT FALSE
);

-- relaciona usuarios con modulos usando un perfil especifico
CREATE TABLE permisos (
    id_permiso BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_usuario BIGINT NOT NULL,
    id_modulo BIGINT NOT NULL,
    id_perfil BIGINT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario),
    FOREIGN KEY (id_modulo) REFERENCES modulos (id_modulo),
    FOREIGN KEY (id_perfil) REFERENCES perfiles (id_perfil),
    UNIQUE (id_usuario, id_modulo)
);

-- alumnos
CREATE TABLE alumnos (
    id_alumno BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_usuario BIGINT UNIQUE,
    matricula VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(50),
    apellido_materno VARCHAR(50),
    curp VARCHAR(18) UNIQUE,
    genero CHAR(1),
    fecha_nac DATE,
    domicilio VARCHAR(150),
    escuela_procedencia VARCHAR(100),
    ruta_foto VARCHAR(255),
    nombre_tutor VARCHAR(100),
    telefono_tutor VARCHAR(20),
    comentarios TEXT,
    estado BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
);

-- profesores
CREATE TABLE profesores (
    id_profesor BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_usuario BIGINT UNIQUE,
    numero_empleado VARCHAR(20) UNIQUE,
    nombre_completo VARCHAR(100) NOT NULL,
    curp VARCHAR(18) UNIQUE,
    telefono VARCHAR(20),
    domicilio VARCHAR(150),
    escuela_procedencia VARCHAR(100),
    ruta_foto VARCHAR(255),
    grado_academico VARCHAR(100),
    estado BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
);

-- salones
CREATE TABLE salones (
    id_salon BIGINT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20),
    capacidad INT
);

-- grupos tipo 1A, 2B, etc.
CREATE TABLE grupos (
    id_grupo BIGINT AUTO_INCREMENT PRIMARY KEY,
    grado VARCHAR(10),
    seccion VARCHAR(5),
    ciclo_escolar VARCHAR(20),
    turno ENUM('MATUTINO', 'VESPERTINO') DEFAULT 'MATUTINO'
);

-- que alumnos pertenecen a que grupo
CREATE TABLE alumno_grupo (
    id_ag BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_alumno BIGINT NOT NULL,
    id_grupo BIGINT NOT NULL,
    FOREIGN KEY (id_alumno) REFERENCES alumnos (id_alumno),
    FOREIGN KEY (id_grupo) REFERENCES grupos (id_grupo),
    UNIQUE (id_alumno, id_grupo)
);

-- materias, el director define la materia junto con su horario y asignación
CREATE TABLE materias (
    id_materia BIGINT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    cupo_maximo INT NOT NULL, -- cupo maximo de alumnos por materia
    vigencia_inicio DATE,
    vigencia_fin DATE,
    id_profesor BIGINT,
    id_salon BIGINT,
    id_grupo BIGINT,
    ciclo_escolar VARCHAR(20),
    FOREIGN KEY (id_profesor) REFERENCES profesores (id_profesor),
    FOREIGN KEY (id_salon) REFERENCES salones (id_salon),
    FOREIGN KEY (id_grupo) REFERENCES grupos (id_grupo)
);

-- relacion muchos a muchos entre profesores y materias
-- define que materias puede impartir cada profe
CREATE TABLE profesor_materia (
    id_pm BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_profesor BIGINT NOT NULL,
    id_materia BIGINT NOT NULL,
    FOREIGN KEY (id_profesor) REFERENCES profesores (id_profesor),
    FOREIGN KEY (id_materia) REFERENCES materias (id_materia),
    UNIQUE (id_profesor, id_materia)
);

-- Nueva tabla para horarios multiples (desacoplada)
CREATE TABLE materia_horarios (
    id_horario BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_materia BIGINT NOT NULL,
    dia ENUM(
        'LUNES',
        'MARTES',
        'MIERCOLES',
        'JUEVES',
        'VIERNES',
        'SABADO'
    ),
    hora_inicio TIME,
    hora_fin TIME,
    FOREIGN KEY (id_materia) REFERENCES materias (id_materia) ON DELETE CASCADE
);

-- el alumno se inscribe a una materia especifica
CREATE TABLE inscripciones (
    id_inscripcion BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_alumno BIGINT NOT NULL,
    id_materia BIGINT NOT NULL,
    fecha_inscripcion DATE DEFAULT(CURRENT_DATE),
    estado BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_alumno) REFERENCES alumnos (id_alumno),
    FOREIGN KEY (id_materia) REFERENCES materias (id_materia),
    UNIQUE (id_alumno, id_materia)
);

-- calificaciones, el profe decide como nombrar el periodo
CREATE TABLE calificaciones (
    id_calificacion BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_inscripcion BIGINT NOT NULL,
    etiqueta_periodo VARCHAR(50), -- el profe lo nombra como quiera: "Parcial 1", "Final", etc.
    puntaje DECIMAL(4, 2),
    fecha_registro DATE DEFAULT(CURRENT_DATE),
    estado ENUM('ACTIVO', 'HISTORICO') DEFAULT 'ACTIVO',
    FOREIGN KEY (id_inscripcion) REFERENCES inscripciones (id_inscripcion)
);

INSERT INTO
    usuarios (
        nombre_usuario,
        contrasena,
        estado
    )
VALUES ('admin', '123', 1);

USE control_escolar;

-- 1. Usuarios adicionales (Alumnos y Profesores)
INSERT INTO
    usuarios (
        nombre_usuario,
        contrasena,
        estado
    )
VALUES ('profe_juan', '123', 1),
    ('profe_maria', '123', 1),
    ('alumno_pedro', '123', 1),
    ('alumno_ana', '123', 1),
    ('alumno_luis', '123', 1);

-- 2. Modulos
INSERT INTO
    modulos (
        nombre,
        descripcion,
        area,
        estado
    )
VALUES (
        'Usuarios',
        'Gestión de usuarios del sistema',
        'Admin',
        1
    ),
    (
        'Alumnos',
        'Control de expedientes de alumnos',
        'Escolar',
        1
    ),
    (
        'Materias',
        'Catálogo y asignación de materias',
        'Académica',
        1
    ),
    (
        'Calificaciones',
        'Registro de notas y evaluaciones',
        'Académica',
        1
    );

-- 3. Ciclos Escolares
INSERT INTO
    ciclos_escolares (
        nombre,
        fecha_inicio,
        fecha_fin,
        estado
    )
VALUES (
        '2023-2024 Anual',
        '2023-08-01',
        '2024-06-30',
        'Cerrado'
    ),
    (
        '2024-2025 Anual',
        '2024-08-01',
        '2025-06-30',
        'Activo'
    );

-- 4. Perfiles (Niveles de acceso)
INSERT INTO
    perfiles (
        apodo,
        descripcion,
        clave_agregar,
        clave_eliminar,
        clave_editar,
        clave_exportar
    )
VALUES (
        'SuperAdmin',
        'Control total del sistema',
        1,
        1,
        1,
        1
    ),
    (
        'Administrativo',
        'Gestión escolar básica',
        1,
        0,
        1,
        1
    ),
    (
        'Profesor',
        'Solo lectura y carga de notas',
        0,
        0,
        0,
        0
    );

-- 5. Permisos (Asignar perfiles a usuarios en módulos)
INSERT INTO
    permisos (
        id_usuario,
        id_modulo,
        id_perfil
    )
VALUES (1, 1, 1), -- Admin tiene SuperAdmin en Usuarios
    (1, 2, 1), -- Admin tiene SuperAdmin en Alumnos
    (2, 3, 3), -- Profe Juan (id 2) tiene acceso a Materias
    (3, 4, 3);
-- Profe Maria (id 3) tiene acceso a Calificaciones

-- 6. Profesores
INSERT INTO
    profesores (
        id_usuario,
        numero_empleado,
        nombre_completo,
        curp,
        telefono,
        grado_academico
    )
VALUES (
        2,
        'EMP001',
        'Juan Pérez Gómez',
        'PEGJ800101HDFRRN01',
        '555-0101',
        'Maestría en Ciencias'
    ),
    (
        3,
        'EMP002',
        'Maria Rodríguez Luis',
        'ROLM850202MDFRRN02',
        '555-0202',
        'Licenciatura en Educación'
    );

-- 7. Alumnos
INSERT INTO
    alumnos (
        id_usuario,
        matricula,
        nombre,
        apellido_paterno,
        apellido_materno,
        curp,
        genero,
        fecha_nac,
        nombre_tutor
    )
VALUES (
        4,
        'MAT2024001',
        'Pedro',
        'Infante',
        'Cruz',
        'INCP050505HDFRRN01',
        'H',
        '2005-05-05',
        'Josefa Cruz'
    ),
    (
        5,
        'MAT2024002',
        'Ana',
        'Gabriel',
        'Lozano',
        'GALA060606MDFRRN02',
        'M',
        '2006-06-06',
        'Roberto Lozano'
    ),
    (
        6,
        'MAT2024003',
        'Luis',
        'Miguel',
        'Gallego',
        'MAGL070707HDFRRN03',
        'H',
        '2007-07-07',
        'Marcela Basteri'
    );

-- 8. Salones
INSERT INTO
    salones (nombre, capacidad)
VALUES ('Aula 101', 30),
    ('Aula 102', 25),
    ('Laboratorio A', 20);

-- 9. Grupos
INSERT INTO
    grupos (
        grado,
        seccion,
        ciclo_escolar,
        turno
    )
VALUES (
        '1',
        'A',
        '2024-2025',
        'MATUTINO'
    ),
    (
        '2',
        'B',
        '2024-2025',
        'VESPERTINO'
    );

-- 10. Alumno_Grupo (Asignar alumnos a sus grupos)
INSERT INTO
    alumno_grupo (id_alumno, id_grupo)
VALUES (1, 1),
    (2, 1),
    (3, 2);

-- 11. Materias
INSERT INTO
    materias (
        nombre,
        cupo_maximo,
        vigencia_inicio,
        vigencia_fin,
        id_profesor,
        id_salon,
        id_grupo,
        ciclo_escolar
    )
VALUES (
        'Matemáticas I',
        30,
        '2024-08-01',
        '2025-06-30',
        1,
        1,
        1,
        '2024-2025'
    ),
    (
        'Historia Universal',
        25,
        '2024-08-01',
        '2025-06-30',
        2,
        2,
        1,
        '2024-2025'
    );

-- 12. Materia Horarios
INSERT INTO
    materia_horarios (
        id_materia,
        dia,
        hora_inicio,
        hora_fin
    )
VALUES (
        1,
        'LUNES',
        '07:00:00',
        '09:00:00'
    ),
    (
        1,
        'MIERCOLES',
        '07:00:00',
        '09:00:00'
    ),
    (
        2,
        'MARTES',
        '10:00:00',
        '12:00:00'
    ),
    (
        2,
        'JUEVES',
        '10:00:00',
        '12:00:00'
    );

-- 13. Inscripciones (Alumnos inscritos a materias)
INSERT INTO
    inscripciones (id_alumno, id_materia, estado)
VALUES (1, 1, 1),
    (2, 1, 1),
    (1, 2, 1);

-- 14. Calificaciones
INSERT INTO
    calificaciones (
        id_inscripcion,
        etiqueta_periodo,
        puntaje,
        estado
    )
VALUES (1, 'Parcial 1', 9.5, 'ACTIVO'),
    (2, 'Parcial 1', 8.0, 'ACTIVO'),
    (
        3,
        'Parcial 1',
        10.0,
        'ACTIVO'
    );