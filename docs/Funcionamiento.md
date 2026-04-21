# 📚 Guía de Uso — Sistema Control Escolar

> **¿Para quién es esta guía?**  
> Para cualquier persona que vaya a usar el sistema por primera vez y no sabe por dónde empezar. No necesitas saber de computadoras para entender esto.

---

## Tabla de contenidos

1. [¿Qué es el sistema y para qué sirve?](#1-qué-es-el-sistema-y-para-qué-sirve)
2. [¿Cómo entrar al sistema? (Inicio de sesión)](#2-cómo-entrar-al-sistema-inicio-de-sesión)
3. [El Tablero Principal (Dashboard)](#3-el-tablero-principal-dashboard)
4. [Módulo: Alumnos](#4-módulo-alumnos)
5. [Módulo: Docentes](#5-módulo-docentes)
6. [Módulo: Materias](#6-módulo-materias)
7. [Módulo: Grupos](#7-módulo-grupos)
8. [Módulo: Salones](#8-módulo-salones)
9. [Módulo: Horarios](#9-módulo-horarios)
10. [Módulo: Inscripciones](#10-módulo-inscripciones)
11. [Módulo: Calificaciones](#11-módulo-calificaciones)
12. [Módulo: Reportes](#12-módulo-reportes)
13. [Módulo: Usuarios](#13-módulo-usuarios)
14. [Portal del Alumno (Vista personal)](#14-portal-del-alumno-vista-personal)
15. [¿Qué puede hacer cada tipo de usuario?](#15-qué-puede-hacer-cada-tipo-de-usuario)
16. [Preguntas frecuentes](#16-preguntas-frecuentes)

---

## 1. ¿Qué es el sistema y para qué sirve?

El **Sistema de Control Escolar** es un programa en línea que ayuda a la escuela a llevar el control de:

- 👩‍🎓 Los **alumnos** registrados
- 👨‍🏫 Los **docentes** que trabajan en la escuela
- 📖 Las **materias** que se imparten
- 🏫 Los **grupos** y **salones**
- 📅 Los **horarios** de clases
- 📝 Las **calificaciones** de cada alumno
- 📄 La generación de **reportes** imprimibles

Todo se maneja desde el navegador web (como Chrome o Firefox), sin necesidad de instalar nada extra.

---

## 2. ¿Cómo entrar al sistema? (Inicio de sesión)

### Paso a paso

1. Abre tu navegador web (Chrome, Firefox, Edge, etc.).
2. En la barra de dirección, escribe la dirección que te dio el administrador de la escuela.  
   *Ejemplo: `http://localhost/ControlEscolar2/`*
3. Aparecerá una pantalla con dos campos:
   - **Usuario:** escribe tu nombre de usuario (te lo dio el administrador).
   - **Contraseña:** escribe tu contraseña.
4. Haz clic en el botón **"Iniciar sesión"**.

### ¿Qué pasa si me equivoco?

- Si el usuario o la contraseña son incorrectos, el sistema te avisará con un mensaje en rojo. Solo vuelve a intentarlo con los datos correctos.
- Si tu cuenta está **desactivada**, el sistema te lo indicará. Contacta al administrador.

### ¿Cómo cerrar sesión?

Cuando termines de usar el sistema, busca tu nombre o el ícono de usuario en la parte superior derecha de la pantalla y haz clic en **"Cerrar sesión"**. Esto es importante para que nadie más pueda ver tu información.

---

## 3. El Tablero Principal (Dashboard)

Después de iniciar sesión, lo primero que verás es el **tablero principal**. Es como la página de inicio del sistema.

### ¿Qué muestra?

Depende de quién seas:

| Si eres… | Verás… |
|----------|--------|
| **Director o Administrador** | Un panel con estadísticas generales del sistema (total de alumnos, docentes, materias activas, etc.) |
| **Docente/Profesor** | Tus clases del **día de hoy**: a qué hora, en qué salón y con qué grupo |
| **Alumno** | Accesos rápidos a tu horario y tus calificaciones personales |

### El menú lateral (barra de la izquierda)

En el lado izquierdo de la pantalla hay un menú con todas las secciones del sistema. Los elementos que ves dependen de tus permisos. No te preocupes si no ves todas las opciones: solo verás lo que puedes usar.

---

## 4. Módulo: Alumnos

> 🔐 **Acceso:** Solo directores y administradores.

Este módulo sirve para **registrar y administrar a todos los alumnos** de la escuela.

### ¿Cómo llego aquí?

En el menú lateral, busca la sección **"Comunidad Escolar"** y haz clic en **"Alumnos"**.

### ¿Qué puedo hacer?

#### Ver la lista de alumnos
Al entrar al módulo, verás una tabla con todos los alumnos registrados. Puedes:
- **Buscar** un alumno escribiendo su nombre o matrícula en el cuadro de búsqueda.
- **Filtrar** por estado: ver solo los activos o solo los inactivos.

#### Registrar un alumno nuevo
1. Haz clic en el botón **"Nuevo Alumno"** (generalmente en la parte superior derecha).
2. Llena el formulario con los datos del alumno:
   - **Matrícula:** La clave única del alumno (no puede repetirse).
   - **Nombre, Apellido Paterno, Apellido Materno.**
   - **CURP:** 18 caracteres, único por persona.
   - **Género:** Masculino o Femenino.
   - **Domicilio:** Dirección donde vive.
   - **Escuela de procedencia:** De dónde viene el alumno.
   - **Foto de perfil** (opcional).
   - **Nombre del tutor** y **Teléfono del tutor** (solo números, máximo 10 dígitos).
   - **Comentarios** (opcional): cualquier nota adicional.
   - **Estado:** Activo o Inactivo.
3. Haz clic en **"Guardar"**.

> ⚠️ **Nota:** Al crear un alumno, el sistema también crea automáticamente una cuenta de usuario para que pueda iniciar sesión. Asegúrate de anotarla.

#### Editar un alumno existente
1. En la lista de alumnos, busca al alumno que quieres modificar.
2. Haz clic en el ícono de **lápiz** (✏️) o en el botón **"Editar"**.
3. Modifica los datos que necesites y haz clic en **"Guardar"**.

#### Eliminar un alumno
1. En la lista, haz clic en el ícono de **papelera** (🗑️) junto al alumno.
2. El sistema te pedirá que confirmes antes de borrar.

> ⚠️ **Cuidado:** No se puede eliminar un alumno que tenga inscripciones o calificaciones registradas. Primero tendrías que eliminar esos registros.

---

## 5. Módulo: Docentes

> 🔐 **Acceso:** Solo directores y administradores.

Este módulo sirve para **registrar y administrar a los profesores** de la institución.

### ¿Cómo llego aquí?

En el menú lateral, sección **"Comunidad Escolar"** → **"Docentes"**.

### ¿Qué puedo hacer?

#### Ver la lista de docentes
Verás una tabla con todos los docentes. Puedes buscar por nombre o número de empleado, y filtrar por estado (activo/inactivo).

#### Registrar un docente nuevo
1. Haz clic en **"Nuevo Docente"**.
2. Llena el formulario:
   - **Número de empleado:** Clave única del docente.
   - **Nombre, Apellido Paterno, Apellido Materno.**
   - **CURP.**
   - **Teléfono** (solo números, máximo 10 dígitos).
   - **Domicilio.**
   - **Grado académico:** Licenciatura, Maestría, Doctorado, etc.
   - **Estado:** Activo o Inactivo.
3. Haz clic en **"Guardar"**.

> ⚠️ **Nota:** Al igual que con los alumnos, al registrar un docente se crea automáticamente una cuenta de acceso al sistema.

#### Editar o eliminar un docente
El proceso es igual que con los alumnos: usa el ícono de lápiz para editar o la papelera para eliminar.

---

## 6. Módulo: Materias

> 🔐 **Acceso:** Directores, administradores y profesores.

Aquí se maneja el **catálogo de todas las materias** que se imparten en la escuela.

### ¿Cómo llego aquí?

Menú lateral, sección **"Académico"** → **"Materias"**.

### ¿Qué puedo hacer?

#### Registrar una materia nueva
1. Haz clic en **"Nueva Materia"**.
2. Llena los datos:
   - **Nombre de la materia** (ej: "Matemáticas", "Historia").
   - **Docente asignado:** Elige al profesor que la impartirá.
   - **Grupo:** El grupo que tomará esa materia.
   - **Salón:** El aula donde se dará la clase.
   - **Día:** El día de la semana (Lunes, Martes, etc.).
   - **Hora de inicio** y **Hora de fin** de la clase.
   - **Ciclo escolar:** El período al que pertenece (ej: "2025-2026").
   - **Cupo máximo:** Número máximo de alumnos que pueden inscribirse.
3. Haz clic en **"Guardar"**.

> 💡 **Tip:** Una materia define cuándo, dónde y con quién se imparte una clase. Si la misma asignatura se da en grupos o horarios distintos, debes crear una entrada por cada combinación.

---

## 7. Módulo: Grupos

> 🔐 **Acceso:** Solo directores y administradores.

Los grupos son la manera de organizar a los alumnos. Por ejemplo: **1°A Matutino**, **2°B Vespertino**.

### ¿Cómo llego aquí?

Menú lateral, sección **"Académico"** → **"Grupos"**.

### ¿Qué puedo hacer?

#### Crear un grupo nuevo
1. Haz clic en **"Nuevo Grupo"**.
2. Llena el formulario:
   - **Grado:** El grado o nivel (ej: "1°", "2°", "3°").
   - **Sección:** La letra del grupo (ej: "A", "B", "C").
   - **Ciclo escolar:** El año o período al que pertenece (ej: "2025-2026").
   - **Turno:** Matutino o Vespertino.
3. Haz clic en **"Guardar"**.

> 💡 **Tip:** Los grupos se usan en otros módulos como Materias e Inscripciones. Crea primero todos los grupos antes de asignar materias.

---

## 8. Módulo: Salones

> 🔐 **Acceso:** Solo directores y administradores.

Aquí se registran los **espacios físicos** (aulas) disponibles en la escuela.

### ¿Cómo llego aquí?

Menú lateral, sección **"Infraestructura"** → **"Salones"**.

### ¿Qué puedo hacer?

#### Registrar un salón nuevo
1. Haz clic en **"Nuevo Salón"**.
2. Llena los datos:
   - **Nombre:** El identificador del aula (ej: "Aula 101", "Laboratorio de Cómputo").
   - **Capacidad:** El número máximo de alumnos que caben en ese espacio.
3. Haz clic en **"Guardar"**.

> ⚠️ **Importante:** La capacidad del salón limita cuántos alumnos pueden inscribirse a una materia impartida en ese espacio.

---

## 9. Módulo: Horarios

> 🔐 **Acceso:** Directores, administradores y profesores.

Este módulo muestra una **vista general de todos los horarios** de la escuela. Es una vista de consulta, no se edita desde aquí.

### ¿Cómo llego aquí?

Menú lateral, sección **"Infraestructura"** → **"Horarios"**.

### ¿Qué puedo ver?

Una tabla o agenda visual que muestra:
- Qué materia se da en cada hora y día.
- Qué profesor la imparte.
- En qué salón se da.
- A qué grupo va dirigida.

Puedes usar los filtros de la pantalla para buscar por día, grupo o profesor específico.

> 💡 **Tip:** Los horarios se generan automáticamente a partir de la información registrada en el módulo de **Materias**. Si algo no aparece aquí, verifica que la materia tenga correctamente asignado el día, la hora de inicio y la hora de fin.

---

## 10. Módulo: Inscripciones

> 🔐 **Acceso:** Solo directores y administradores.

Las inscripciones son el **vínculo entre un alumno y una materia**. Sin inscripción, no puede haber calificación.

### ¿Cómo llego aquí?

Menú lateral, sección **"Infraestructura"** → **"Inscripciones"**.

### ¿Qué puedo hacer?

#### Ver las inscripciones actuales
En la lista verás: el nombre del alumno, la materia en la que está inscrito, la fecha de inscripción y el estado (activo o baja).

#### Inscribir a un alumno en una materia
1. Haz clic en **"Nueva Inscripción"**.
2. Selecciona al **alumno** de la lista desplegable.
3. Selecciona la **materia** a la que se va a inscribir.
4. Haz clic en **"Guardar"**.

> ⚠️ **Importante:** Un alumno **no puede inscribirse dos veces a la misma materia**. Si intentas hacerlo, el sistema te mostrará un error.

#### Cancelar una inscripción
Si un alumno ya no tomará una materia, puedes hacer clic en la papelera 🗑️ junto a su inscripción para eliminarla.

> ⚠️ **Cuidado:** Eliminar una inscripción también eliminará las calificaciones asociadas a ella.

---

## 11. Módulo: Calificaciones

> 🔐 **Acceso:** Directores, administradores y profesores.

Aquí se **registran y consultan las calificaciones** de los alumnos por materia y período.

### ¿Cómo llego aquí?

Menú lateral, sección **"Académico"** → **"Calificaciones"**.

### ¿Qué puedo hacer?

#### Ver la lista de calificaciones
Verás una tabla con: matrícula del alumno, nombre completo, materia, período evaluado (ej. "Parcial 1", "Final") y la calificación obtenida.

Puedes usar los filtros para buscar por alumno, materia o período.

#### Registrar una calificación nueva
1. Haz clic en **"Nueva Calificación"**.
2. Selecciona la **inscripción** (alumno + materia) a la que corresponde.
3. Escribe el **nombre del período** (ej: "Parcial 1", "Parcial 2", "Final").
4. Escribe el **puntaje** (número decimal, ej: `8.5`, `10`, `6.75`).
5. Haz clic en **"Guardar"**.

#### Editar una calificación
1. Busca la calificación en la lista.
2. Haz clic en el ícono de lápiz ✏️.
3. Modifica el puntaje o el período y guarda.

#### Eliminar una calificación
Haz clic en la papelera 🗑️. Se te pedirá confirmar antes de borrar.

---

## 12. Módulo: Reportes

> 🔐 **Acceso:** Solo directores y administradores.

Este módulo genera **documentos imprimibles**. No guarda ni modifica datos, solo los presenta en formato listo para imprimir.

### ¿Cómo llego aquí?

Menú lateral, sección **"Administración"** → **"Reportes"**.

### Tipos de reporte disponibles

#### 📋 Reporte 1: Lista de grupo
Genera la lista de todos los alumnos activos que pertenecen a un grupo.

**¿Cómo hacerlo?**
1. En la pantalla de reportes, selecciona un **grupo** del menú desplegable.
2. Haz clic en el botón para generar la lista.
3. Se abrirá una nueva pantalla con la lista ordenada alfabéticamente, lista para imprimir.
4. Usa **Ctrl + P** (o el botón de imprimir de tu navegador) para imprimir o guardar como PDF.

**La lista incluye:** número consecutivo, nombre completo del alumno, matrícula y género.

---

#### 📄 Reporte 2: Boleta de calificaciones
Genera la boleta individual de un alumno con todas sus calificaciones activas.

**¿Cómo hacerlo?**
1. En la pantalla de reportes, escribe la **matrícula** del alumno en el campo de texto.
2. Haz clic en el botón para generar la boleta.
3. Se abrirá una pantalla con la boleta lista para imprimir, que incluye:
   - Datos del alumno (nombre, grupo, matrícula).
   - Lista de materias con su calificación y período.
   - **Promedio general** calculado automáticamente.
4. Usa **Ctrl + P** para imprimir o guardar como PDF.

---

## 13. Módulo: Usuarios

> 🔐 **Acceso:** Solo directores y administradores.

Aquí se administran las **cuentas de acceso** al sistema (los "usuarios" y contraseñas con los que la gente inicia sesión).

### ¿Cómo llego aquí?

Menú lateral, sección **"Administración"** → **"Usuarios"**.

### ¿Qué puedo hacer?

#### Ver la lista de usuarios
Verás todos los usuarios del sistema con su nombre de usuario y estado (activo/inactivo).

#### Crear un usuario nuevo
1. Haz clic en **"Nuevo Usuario"**.
2. Llena el formulario:
   - **Nombre de usuario:** El login con el que entrará al sistema (ej: `juan.perez`).
   - **Contraseña:** Debe ser segura. El sistema la guardará de forma cifrada.
   - **Confirmar contraseña:** Escríbela de nuevo para verificar que no te hayas equivocado.
   - **Estado:** Activo o Inactivo.
3. Haz clic en **"Guardar"**.

> 💡 **Nota:** Normalmente no necesitas crear usuarios manualmente, ya que el sistema los crea automáticamente cuando registras un alumno o un docente.

#### Editar un usuario
1. Haz clic en el ícono de lápiz ✏️ junto al usuario.
2. Puedes cambiar el nombre de usuario o el estado.
3. Si quieres cambiar la contraseña, escríbela en el campo correspondiente. **Si dejas el campo vacío, la contraseña no cambia.**
4. Haz clic en **"Guardar"**.

#### Desactivar un usuario (en lugar de eliminar)
Se recomienda **cambiar el estado a "Inactivo"** en vez de eliminar un usuario, para conservar el historial. Un usuario inactivo no puede iniciar sesión.

---

## 14. Portal del Alumno (Vista personal)

> 🔐 **Acceso:** Solo alumnos.

Si inicias sesión con una cuenta de alumno, verás un portal personal con dos secciones.

### Mi Horario

Muestra tu **horario semanal** en formato de tabla (Lunes a Viernes). Cada celda muestra:
- El nombre de la materia.
- El nombre del profesor.
- El salón donde se imparte.
- La hora de inicio y fin.

> 💡 El horario se genera automáticamente a partir del grupo al que perteneces y el ciclo escolar activo.

### Mis Calificaciones

Muestra todas tus **calificaciones del ciclo escolar actual**:
- Nombre de las materias en las que estás inscrito.
- La calificación obtenida en cada período evaluado.
- Tu **promedio general** calculado automáticamente.

---

## 15. ¿Qué puede hacer cada tipo de usuario?

| Acción | Director / Admin | Profesor | Alumno |
|--------|:---:|:---:|:---:|
| Ver y gestionar alumnos | ✅ | ❌ | ❌ |
| Ver y gestionar docentes | ✅ | ❌ | ❌ |
| Gestionar materias | ✅ | ✅ | ❌ |
| Gestionar grupos | ✅ | ❌ | ❌ |
| Gestionar salones | ✅ | ❌ | ❌ |
| Ver horarios generales | ✅ | ✅ | ❌ |
| Gestionar inscripciones | ✅ | ❌ | ❌ |
| Registrar calificaciones | ✅ | ✅ | ❌ |
| Generar reportes | ✅ | ❌ | ❌ |
| Gestionar usuarios | ✅ | ❌ | ❌ |
| Ver mi horario personal | ❌ | ❌ | ✅ |
| Ver mis calificaciones | ❌ | ❌ | ✅ |
| Ver tablero con clases del día | ❌ | ✅ | ❌ |

---

## 16. Preguntas frecuentes

**❓ No recuerdo mi contraseña, ¿qué hago?**  
Contacta al administrador del sistema. Él puede cambiar tu contraseña desde el módulo de Usuarios.

---

**❓ Registré un alumno pero no aparece en la lista.**  
Verifica que el filtro de estado esté en "Activo" o "Todos". Si acabas de guardarlo, recarga la página.

---

**❓ Intenté inscribir a un alumno en una materia y me dio error.**  
Puede ser porque el alumno ya está inscrito en esa materia. Cada alumno solo puede aparecer una vez por materia. Revisa la lista de inscripciones antes de intentarlo de nuevo.

---

**❓ Al eliminar un docente me salió un error.**  
Esto ocurre porque el docente está asignado a alguna materia. Primero edita esas materias y asigna a otro profesor, o elimínalas. Luego podrás eliminar al docente.

---

**❓ Generé una boleta pero no aparecen calificaciones.**  
Verifica que el alumno tenga inscripciones activas y que esas inscripciones tengan calificaciones registradas con estado "ACTIVO".

---

**❓ No veo algunos módulos en el menú.**  
El menú muestra solo lo que tu tipo de usuario puede usar. Si crees que debería tener acceso a algo más, habla con el administrador.

---

**❓ ¿Qué pasa si cierro el navegador sin cerrar sesión?**  
Tu sesión permanecerá activa por un tiempo. Para mayor seguridad, siempre cierra sesión antes de cerrar el navegador, especialmente en computadoras compartidas.

---

*Guía de uso redactada para el Sistema Control Escolar — Versión 1.0 · Abril 2026*
