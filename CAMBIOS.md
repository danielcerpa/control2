# Bitácora de Cambios, Estandarización y Optimización del Sistema (Control Escolar)

Este documento detalla de manera integral la refactorización arquitectónica, las correcciones lógicas y las optimizaciones de interfaz que se han aplicado al sistema **Control Escolar** para asegurar un entorno de trabajo robusto, moderno y libre de código heredado problemático (legacy code).

---

## 1. Migración de Interfaz y Eliminación de Código Obsoleto

Se ejecutó una purga generalizada en las mecánicas de administración de las tablas de datos de todos los módulos del sistema (Alumnos, Docentes, Materias, Grupos, Salones, Usuarios y Ciclos Escolares), transitando a un ecosistema más rápido y seguro.

### 1.1 Limpieza de Tablas (Vistas Index)
- **Erradicación de Acciones Embebidas:** Se suprimió la columna de "Acciones" (los tradicionales botones de *Editar* y *Eliminar* para cada fila) en *todas* las listas del sistema.
- **Supresión de Scripts Inseguros:** Se eliminaron las funciones Javascript en línea (como `confirmDelete()`) que ponían en riesgo de exposición la lógica de ruteo del borrado, reduciendo además el peso bruto de las páginas.
- **Botonera Global Estratégica:** En las cabeceras de cada listado se incorporaron botones macro de acción (**Borrar Registro** y **Editar Registro**) dirigiendo a nuevas herramientas de manipulación unificadas.

### 1.2 Implementación de Selectores de Búsqueda (Vistas Search-Edit y Search-Delete)
- **Ingeniería de Vistas Dedicadas:** Para reemplazar las acciones en tabla, se diseñaron dos archivos estándar por módulo: `vista_search_edit.php` y `vista_search_delete.php`.
- **Autocompletado con Vanilla JS:** Se construyó un poderoso componente de autocompletado en tiempo real sin llamar repetidamente al servidor (No AJAX delay). Al cargar la vista, los datos son consolidados en un arreglo JSON local (por ejemplo `json_encode($docentes)`); permitiendo al usuario teclear nombres, matrículas o identificadores y recibir coincidencias instantáneas.
- **Mapeo Instantáneo de DOM:** Al seleccionar una coincidencia en el buscador, el Javascript mapea y distribuye la granulación de los datos en tiempo real hacia los inputs correspondientes del formulario de Lectura o Edición.
- **Blindaje Funcional:** La vista de eliminación (`search_delete`) obliga la revisión exhaustiva de un listado detallado (solo lectura) antes de activar el botón rojo de "Eliminación Definitiva", previniendo clics trágicos accidentales.

---

## 2. Compatibilidad del Esquema y Limpieza de Formularios (Grupos y Materias)

Se descubrió que los formularios de captura y edición estaban solicitando y enviando datos falsos que no se relacionaban verdaderamente con el diseño de la base de datos MySQL (Schema).

- **Módulo Grupos:** 
  - Se eliminaron las referencias ficticias e insertables de capacidad o dependencia lineal no relacional y se restauró el soporte estricto al selector `Turno`.
  - Se corrigió el selector del Ciclo Escolar.
- **Módulo Materias:**
  - Se purgaron de las vistas los campos heredados `Área` y `Clave` ya que interferían y no residían en la estructura de DB actual.
  - Se habilitaron formalmente los inputs que sí demandaba la arquitectura, tales como el `grado` y el límite lógico de `cupo_maximo`.
- **Módulo Alumnos:** 
  - Corrección silente a fallos del Servidor sobre la variable de Sexo/Género. El DOM imprimía strings fijos completos (`Masculino` / `Femenino`), pero la DB estipulaba un riguroso `CHAR(1)`. La lógica backend se ajustó para mapear y guardar pulcramente el carácter `M` o `F`, corrigiendo fallas de saturación de bits MySQL.

---

## 3. Integración Completa de Acceso Dual (Cuentas de Docentes)

Históricamente, los Docentes eran simplemente un registro de personal sin un usuario atado. Se implementó un enrutamiento de clases cruzado para automatizar la creación de sus accesos.

- **Fusión Backend (Usuario-Profesor):** Al momento en que Control Escolar procesa la alta (Create) de un Profesor, el `DocentesController` ahora instancia imperativamente un objeto del modelo de `Usuarios`. 
- **Hash de Seguridad e Inyección Dinámica:** El controlador secuestra la credencial propuesta, le asigna una encriptación irrompible mediante `password_hash()` y la inserta en la tabla de Usuarios.
- **Vínculo Constreñido (lastInsertId):** Se modificó el motor de conexión PDO para la creación de Usuarios forzándole a retornar su ID originario (`lastInsertId()`). Gracias a esto, el ID virtual creado es trasladado a la tabla de Profesores encajando perfectamente como la llave foránea de conectividad `id_usuario`.
- **Manipulación Modular (Update):** En las ventanas de edición (`search_edit`) se integró nativamente una *Columna de Credenciales* que busca dinámicamente si el Profesor posee permisos de conectividad a la plataforma para que los administradores reasignen contraseñas en tiempo real. 

---

## 4. Reparación Completa del Flujo de Archivos Multimedia (Fotos de Perfil)

El manejo de las imágenes de perfil (Alumnos y Docentes) adolecía de cuellos de botella de compatibilidad, validaciones estrictas excesivas y omisiones de llamado en sentencias SQL. 

- **Solución al Sistema de Subida Restrictivo:** Las validaciones de Javascript heredadas abortaban sigilosamente la subida de fotos bloqueando imágenes superiores a los 2MB de peso (algo irrisorio para cámaras modernas). Se incrementó la cota bruta a **10MB** para permitir una ingesta fluida.
- **Escritura Base64 en Almacenamiento Primario:** El procesamiento de imágenes ahora recolecta el hilo asíncrono Base64, lo destila (`preg_match`), re-ensambla y almacena el archivo físico correctamente bajo el formato `uniqid()` en los subdirectorios `assets/img/alumnos/` y `assets/img/docentes` respectivamente, guardando en la DB exclusivamente la ruta lógica consumible (`URL`).
- **Rescate de Query Muerto:** La tabla de docentes (`vista_index`) no lograba renderizar las fotografías existentes. El análisis dedujo que el método de recolección universal `Docente::getAll()` había descartado solicitar la columna vital `ruta_foto`. Tras reparar la inyección en `conexion.php`, los avatares recuperaron visibilidad general.
- **Homologación Visual Redonda (UI/UX):** Para evitar rupturas visuales debido al enorme rango de resoluciones tomadas por celulares o webcams, las tarjetas en `vista_index.php` recubrieron su renderizado HTML de imagen (`<img>`) con parámetros absolutos CSS `object-fit:cover; border-radius:50%`, enclaustrando toda evidencia visual a un tamaño miniatura idóneo.
- **Parches para Editores Rápidos (Search-Edit):** Debido a que se originaron las vistas de auto-búsqueda (`vista_search_edit.php`) en una época de transición, les faltaba la columna derecha de carga de fotografías de las plantillas predeterminadas de `vista_edit.php`. Estas fueron incrustadas íntegramente con los Event Listeners de Javascript permitiendo una interfaz interactiva e idéntica en cualquier ruta que se tome para editar.

---

## 5. Refactorización Intelectual del Módulo de Materias y Horarios (Esquema Multi-días)

El sistema operaba originalmente sobre un paradigma lineal arcaico que limitaba el dictado de una Materia a un único bloque de día y hora. Esto imposibilitaba agendar clases que requirieran impartirse más de una vez en la semana o a horas distintas. Se realizó una intervención quirúrgica total en la base de datos y la arquitectura del Modelo-Vista-Controlador.

### 5.1 Adaptación del Esquema SQL (`schema.sql`)
- **Migración a Base de Datos Relacional:** Se suprimieron permanentemente las fajas limitantes (`dia`, `hora_inicio`, `hora_fin`) adheridas directamente a la tabla `materias`.
- **Nueva Entidad Multi-Temporal:** Se instauró la súper-tabla `horarios_materia`, la cual permite atar de manera foránea (LLave externa) de 1 a N días diferentes con rangos de hora totalmente específicos a una misma materia sin generar registros flotantes indeseados.

### 5.2 Expansión del MVC al Sistema de Checkboxes
- **Adaptación Intermedia y Evolución UI:** Inicialmente se probó un diseño dinámico de bloques de Javascript que permitía al usuario "agregar filas", pero fue rápidamente abandonado e iterado en favor del **Sistema de Check-Boxes**, el cual brinda una visibilidad limpia, cómoda y mucho más fácil de interactuar.
- **Transmutación de Formularios (`vista_create.php` / `vista_search_edit.php`):** La estructura del DOM fue reconstruida. Mediante el uso de selectores matriciales de Checkbox `name="dias[]"`, el administrador decide libremente qué días compartirá la clase, acompañándolo de un reloj selector unificado de **Hora Inicial y Final**. En edición, el propio Javascript se reconfiguró para parsear nativamente la sub-petición JSON y auto-revisar las "palomitas" correctas, simulando memoria instantánea.
- **Logística Recursiva del Controlador:** `logica.php` abandonó el envío tradicional. Ahora, el script intercepta el conjunto (Array) y fusiona una matriz mediante herramientas nativas PHP (`array_fill`) para generar los sub-arrays idénticos de las horas, logrando transferir una malla completa de "N" registros puros hacia el modelo.
- **Inserción Masiva en Modelo:** `conexion.php` implementó a su vez la funcionalidad `syncHorarios()`. Cuando el usuario da _Guardar_, el script desecha transparentemente cualquier día viejo, itera el nuevo arreglo recibido e introduce cada día limpio de nuevo. Así se evitan duplicidades y archivos basura.

### 5.3 Vitrinas Sensibles (Vista Index)
- **Extracción de Variables Anidadas:** Las solicitudes del Backend sobre la base de datos se alteraron de recolección plana y lineal a anidación. Ahora, bajo del capó, todas las Materias y Horarios vienen con la colección interna de días asociada a ella.
- **Renders de Badges Estéticos:** `vista_index.php` (de Materia) abandonó el formato `<campo vacío>`, mutando en agrupaciones visualmente estéticamente ordenadas mediante insignias de CSS (`badges`), mientras que la gran vitrina de tabulación universal (`modulos/horarios/conexion.php`) acopló el esquema a través de un `JOIN` logrando preservar la impecable cronológica académica (ordenados del Lunes al Sábado) sin importar bajo qué orden o jerarquía se agregaron en su edición.
