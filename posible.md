Diagrama de Entidad-Relación Mejorado
Entidades Principales
desarrollos
id_desarrollo (PK)

nombre

descripcion

ubicacion

m2_totales

imagen

fecha_creacion

activo (Para indicar si el desarrollo está activo)

departamentos
id_departamento (PK)

id_desarrollo (FK, referencia a desarrollos)

numero_de_depto

m2

precio_venta

estado (Vendido, Disponible, En Construcción, etc.)

fecha_registro

activo

usuarios
id_usuario (PK)

nombre

apellido

email

password

id_rol (FK, referencia a roles)

activo

usuario_desarrollos
id_usuario_desarrollo (PK)

id_usuario (FK, referencia a usuarios)

id_departamento (FK, referencia a departamentos)

fecha_asignacion

vigencia

estatus

pagos
id_pago (PK)

id_usuario_desarrollo (FK, referencia a usuario_desarrollos)

monto

fecha_pago

concepto (Mensualidad, Enganche, etc.)

estatus (Pendiente, Aprobado, Rechazado)

url_comprobante (En lugar de id_archivo)

comentario_admin

created_at

updated_at

desarrollos_costos_mensuales
id (PK)

id_desarrollo (FK, referencia a desarrollos)

m2_mensual

mes

anio

created_at

updated_at

avances_desarrollos
id_avance (PK)

id_desarrollo (FK, referencia a desarrollos)

categoria (Planificación, Excavación, etc.)

valor_actual

valor_objetivo

unidad (Ej: porcentaje, unidades)

updated_at

avance_departamentos
id_avance_depto (PK)

id_departamento (FK, referencia a departamentos)

categoria

valor_actual

valor_objetivo

unidad

updated_at

Tablas de Referencia y Auxiliares
catalogos_estado_pago
id_estado_pago (PK)

nombre

roles
id_rol (PK)

nombre

descripcion

archivos
id_archivo (PK)

id_usuario (FK, referencia a usuarios)

id_tipo_archivo (FK, referencia a catalogos_tipos_archivo)

nombre_archivo

ruta

extension

comentario_admin

created_at

updated_at

catalogos_tipos_archivo
id_tipo_archivo (PK)

nombre

facturas
id_factura (PK)

id_pago (FK, referencia a pagos)

rfc

direccion

email_factura

id_archivo (FK, referencia a archivos si se sube el PDF de la factura)

fecha_emision

created_at

notificaciones
id_notificacion (PK)

id_usuario (FK, referencia a usuarios)

mensaje

leido (boolean)

fecha

Explicación de los Cambios y Relaciones
Renombre de Tablas:

proyectos se cambió a desarrollos.

usuarios_proyectos se cambió a usuario_desarrollos.

La tabla tbr_desarrollos_costo_mensual se simplificó a desarrollos_costos_mensuales.

Nueva Estructura de Avance de Obra:

Para dar seguimiento al avance, se crearon dos tablas: avances_desarrollos y avance_departamentos.

avances_desarrollos se enlaza a desarrollos para el avance del proyecto en general (ej. Avance de Obra Complejo).

avance_departamentos se enlaza a departamentos para el avance específico de cada unidad.

Esto permite una granularidad más fina y facilita la consulta de datos tanto a nivel general como individual.

Relación de Pagos:

La tabla pagos se relaciona directamente con usuario_desarrollos. Esta es una relación clave porque un pago siempre está asociado a un usuario y al departamento que le fue asignado.

Se eliminaron las columnas como m2_inicial, precio_compraventa de la tabla pagos porque esa información ya debería estar en la tabla departamentos.

Se agregó un campo url_comprobante en lugar de id_archivo para una referencia más directa y simple del comprobante de pago. Si se necesita un sistema de archivos más robusto, se puede mantener la tabla archivos y relacionarla con la tabla pagos a través de un id.

Relación entre Usuarios y Desarrollos:

La tabla usuario_desarrollos actúa como un intermediario entre usuarios y departamentos (antes proyectos). Esto es esencial porque un usuario está vinculado a uno o varios departamentos, y un departamento pertenece a un desarrollo. Esto modela perfectamente la asignación de un cliente a su propiedad.

Requerimiento del Dashboard:

Con esta estructura, puedes obtener la información para el dashboard de forma sencilla:

0 Desarrollos: Contar el número de registros en la tabla desarrollos.

0 Departamentos: Contar el número de registros en la tabla departamentos.

0.00 M2 Totales: Sumar la columna m2 de la tabla departamentos.

Esta estructura te dará la flexibilidad necesaria para que el usuario suba sus pagos, el admin los valide y se dé un seguimiento completo al avance de cada proyecto y departamento.

Espero que esta propuesta te sea de gran ayuda. ¿Te gustaría que te ayudara a definir un script para crear estas tablas en algún motor de base de datos como MySQL o PostgreSQL?