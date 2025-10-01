Resumen de Arquitectura de la Aplicación
El proyecto está construido sobre el patrón de diseño Modelo-Vista-Controlador (MVC), 
que organiza la lógica de la aplicación en tres capas distintas para hacer el código 
más estructurado, escalable y fácil de mantener.

Los componentes principales que identificamos son:

1. Controlador Frontal y Enrutamiento
Todo el tráfico web se dirige a un único punto de entrada: index.php. Este archivo, 
junto con el .htaccess, actúa como un controlador frontal que analiza la URL y el método 
HTTP para decidir qué clase (controlador) y qué método deben manejar la petición.

2. Controladores (C)
Las clases en la carpeta app/controllers son la capa de control. Su función principal es:

Recibir la petición del usuario.

Interactuar con los modelos para obtener datos.

Preparar los datos.

Pasar los datos a la vista adecuada para su presentación.

3. Modelos (M)
Las clases en app/models gestionan la lógica de negocio y la interacción con la base de datos. 
Los modelos son responsables de las operaciones CRUD (crear, leer, actualizar, eliminar). 
El proyecto utiliza:

Clases estáticas (Pago, Desarrollo) para organizar las consultas.

El patrón Singleton en la clase Database para asegurar que solo exista una única conexión a la 
base de datos en toda la aplicación.

4. Vistas (V)
Las vistas en app/views son la capa de presentación. Contienen el HTML y el código PHP mínimo 
necesario para mostrar la información que el controlador les ha enviado.

5. Archivos de Core
Estos archivos son la base de la aplicación:

Autoload.php: Carga automáticamente las clases, lo que evita tener que usar require_once manualmente en cada archivo.

config.php: Centraliza las constantes de configuración de la base de datos.

Resumen de la Estructura
Componente

Archivos

Función Principal

Punto de Entrada

index.php, .htaccess

Controla el flujo de peticiones y enruta la URL.

Controladores

HomeController, ApiController

Lógica de negocio y manejo de peticiones.

Modelos

Database, Pago, Desarrollo

Interacción segura con la base de datos.

Vistas

homeUser.php, etc.

Presentación de los datos al usuario.

Core

Autoload.php, config.php

Configuración base y carga automática de clases.