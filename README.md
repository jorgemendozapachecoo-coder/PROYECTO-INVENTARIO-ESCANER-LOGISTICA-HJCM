# Sistema de Inventario - CMI José Carlos Mariátegui

Desarrollado por: **Jorge Eduardo Mendoza Pacheco**
[cite_start]Correo: **jorge@hotmail.com** [cite: 84] | [cite_start]Teléfono: **+51 948051742** [cite: 85]

[cite_start]Este proyecto es un Sistema de Inventario web desarrollado como parte de las Prácticas Pre-Profesionales II [cite: 89] [cite_start]de la carrera de Ingeniería de Sistemas en la Universidad Autónoma del Perú[cite: 88, 101].

## 🎯 Problema Solucionado

[cite_start]El Centro Materno Infantil José Carlos Mariátegui gestionaba su inventario de forma manual usando hojas de cálculo en Excel[cite: 99]. [cite_start]Este método generaba problemas críticos[cite: 207]:

* [cite_start]Retrasos en el control de inventario[cite: 209].
* [cite_start]Errores frecuentes en los registros (duplicados, omisiones)[cite: 211].
* [cite_start]Desabastecimiento de productos críticos[cite: 213].
* [cite_start]Falta de información en tiempo real para la toma de decisiones[cite: 215].
* [cite_start]Ausencia de trazabilidad con documentos (PECOSA)[cite: 217].

## 💡 Solución Implementada

[cite_start]Se implementó un sistema de inventario digital y automatizado que utiliza tecnología de escaneo de códigos de barras para optimizar la gestión logística[cite: 101, 1126]. [cite_start]El sistema centraliza la información, reduce los errores humanos en un 87% y disminuye el tiempo de gestión en un 75%[cite: 1916, 1919].

## 📸 Vistas del Sistema

Aquí puedes añadir capturas de pantalla de tu aplicación.

| Login | Inventario Principal |
| :---: | :---: |
| [cite_start]*(Pega aquí tu imagen de `Inicio de Sesión`)* [cite: 701] | [cite_start]*(Pega aquí tu imagen del `Inventario`)* [cite: 854] |

| Escanear Productos | Documentación PECOSA |
| :---: | :---: |
| [cite_start]*(Pega aquí tu imagen de `Escanear Nuevos Productos`)* [cite: 794] | [cite_start]*(Pega aquí tu imagen de `Documentación PECOSA`)* [cite: 928] |

| Dashboard |
| :---: |
| [cite_start]*(Pega aquí tu imagen del `Dashboard`)* [cite: 1020, 1048] |

## ✨ Características Principales

* [cite_start]**Gestión de Inventario (CRUD):** Registro, actualización y eliminación de productos[cite: 22, 1146].
* [cite_start]**Escaner de Productos:** Permite registrar productos nuevos escaneando su código patrimonial o ingresándolo manualmente[cite: 26, 34, 1157].
* [cite_start]**Gestión de Documentos PECOSA:** Sube y asocia archivos (PDF, Word, Excel, Imagen) que justifican el ingreso de productos[cite: 25, 53, 61, 1151].
* [cite_start]**Alertas de Antigüedad:** Notifica y resalta visualmente los productos con más de 10 años de antigüedad[cite: 24, 65, 358, 1147].
* [cite_start]**Reportes:** Exporta el inventario completo o los productos nuevos a formatos **Excel** y **PDF**[cite: 27, 73, 79, 1155].
* [cite_start]**Dashboard Interactivo:** Gráficos que muestran el estado del inventario por clasificación de antigüedad (Nuevos, Por Vencer, Antiguos)[cite: 1018, 1020].
* [cite_start]**Gestión de Usuarios:** Sistema de inicio de sesión con roles (Administrador y Usuario)[cite: 396, 1086].
* [cite_start]**Historial de Reposiciones:** Mantiene un registro histórico de los productos que han sido dados de baja[cite: 23, 70, 425].

## 🛠️ Tecnologías Utilizadas

* [cite_start]**Backend:** PHP [cite: 1110]
* [cite_start]**Base de Datos:** MySQL [cite: 1108]
* [cite_start]**Frontend:** HTML [cite: 1113][cite_start], CSS [cite: 1115][cite_start], Bootstrap [cite: 1116]
* [cite_start]**Entorno de Servidor Local:** XAMPP (Apache, MySQL, PHP) [cite: 9, 1102]
* [cite_start]**IDE:** Visual Studio Code [cite: 10]
* [cite_start]**Dashboarding:** Power BI (para el dashboard interactivo) [cite: 1018]

## 🚀 Instalación y Puesta en Marcha

Para ejecutar este proyecto en tu máquina local, sigue estos pasos:

1.  **Clonar el Repositorio:**
    ```bash
    git clone [https://github.com/tu-usuario/tu-repositorio.git](https://github.com/tu-usuario/tu-repositorio.git)
    ```

2.  **Instalar XAMPP:**
    [cite_start]Asegúrate de tener [XAMPP](https://www.apachefriends.org/es/index.html) (o un servidor local similar) instalado[cite: 9].

3.  **Mover el Proyecto:**
    Mueve la carpeta clonada del proyecto dentro de la carpeta `htdocs` de tu instalación de XAMPP. [cite_start]Según el manual de usuario, la carpeta debe llamarse **`realinventario`**[cite: 13].
    * Ruta de ejemplo: `C:\xampp\htdocs\realinventario`

4.  **Importar la Base de Datos:**
    * Inicia los módulos de Apache y MySQL en XAMPP.
    * Abre `phpMyAdmin` (usualmente en `http://localhost/phpmyadmin/`).
    * Crea una nueva base de datos (p.ej., `inventario_cmi`).
    * Ve a la pestaña "Importar", selecciona el archivo `.sql` del proyecto (debes incluirlo en tu repo) y haz clic en "Continuar".

5.  **Configurar la Conexión:**
    * (Si es necesario) Modifica el archivo de conexión de la base de datos en el proyecto (p.ej., `config.php` o `db.php`) con el nombre de tu base de datos, usuario (`root`) y contraseña (usualmente vacía por defecto en XAMPP).

6.  **Acceder al Sistema:**
    * [cite_start]Abre tu navegador (Google Chrome recomendado [cite: 7]) e ingresa a la URL:
    * [cite_start]`http://localhost/realinventario/login.php` [cite: 13]

## 🔑 Credenciales de Acceso

[cite_start]Puedes usar las siguientes credenciales de ejemplo (o las que hayas configurado en tu base de datos)[cite: 17, 18]:

* **Usuario:** `admin`
* **Contraseña:** `admin123`