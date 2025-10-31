# Sistema de Inventario - CMI José Carlos Mariátegui

Desarrollado por: **Jorge Eduardo Mendoza Pacheco**
Correo: **jorge@hotmail.com** | Teléfono: **+51 948051742**

Este proyecto es un Sistema de Inventario web desarrollado como parte de las Prácticas Pre-Profesionales II de la carrera de Ingeniería de Sistemas en la Universidad Autónoma del Perú.

## 🎯 Problema Solucionado

El Centro Materno Infantil José Carlos Mariátegui gestionaba su inventario de forma manual usando hojas de cálculo en Excel. Este método generaba problemas críticos:

* Retrasos en el control de inventario.
* Errores frecuentes en los registros (duplicados, omisiones).
* Desabastecimiento de productos críticos.
* Falta de información en tiempo real para la toma de decisiones.
* Ausencia de trazabilidad con documentos (PECOSA).

## 💡 Solución Implementada

Se implementó un sistema de inventario digital y automatizado que utiliza tecnología de escaneo de códigos de barras para optimizar la gestión logística. El sistema centraliza la información, reduce los errores humanos en un 87% y disminuye el tiempo de gestión en un 75%.


## ✨ Características Principales

* **Gestión de Inventario (CRUD):** Registro, actualización y eliminación de productos.
* **Escaner de Productos:** Permite registrar productos nuevos escaneando su código patrimonial o ingresándolo manualmente.
* **Gestión de Documentos PECOSA:** Sube y asocia archivos (PDF, Word, Excel, Imagen) que justifican el ingreso de productos.
* **Alertas de Antigüedad:** Notifica y resalta visualmente los productos con más de 10 años de antigüedad.
* **Reportes:** Exporta el inventario completo o los productos nuevos a formatos **Excel** y **PDF**.
* **Dashboard Interactivo:** Gráficos que muestran el estado del inventario por clasificación de antigüedad (Nuevos, Por Vencer, Antiguos).
* **Gestión de Usuarios:** Sistema de inicio de sesión con roles (Administrador y Usuario).
* **Historial de Reposiciones:** Mantiene un registro histórico de los productos que han sido dados de baja.

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP
* **Base de Datos:** MySQL
* **Frontend:** HTML, CSS, Bootstrap
* **Entorno de Servidor Local:** XAMPP (Apache, MySQL, PHP)
* **IDE:** Visual Studio Code
* **Dashboarding:** Power BI (para el dashboard interactivo)

## 🚀 Instalación y Puesta en Marcha

Para ejecutar este proyecto en tu máquina local, sigue estos pasos:

1.  **Clonar el Repositorio:**
    ```bash
    git clone [https://github.com/jorgemendozapachecoo-coder/PROYECTO-INVENTARIO-ESCANER-LOGISTICA-HJCM.git](https://github.com/jorgemendozapachecoo-coder/PROYECTO-INVENTARIO-ESCANER-LOGISTICA-HJCM.git)
    ```

2.  **Instalar XAMPP:**
    Asegúrate de tener [XAMPP](https://www.apachefriends.org/es/index.html) (o un servidor local similar) instalado.

3.  **Mover el Proyecto:** Mueve la carpeta clonada del proyecto dentro de la carpeta `htdocs` de tu instalación de XAMPP. Según el manual de usuario, la carpeta debe llamarse `realinventario`.
    * Ruta de ejemplo: `C:\xampp\htdocs\realinventario`

4.  **Importar la Base de Datos:**
    * Inicia los módulos de Apache y MySQL en XAMPP.
    * Abre `phpMyAdmin` (usualmente en `http://localhost/phpmyadmin/`).
    * Crea una nueva base de datos (p.ej., `inventario_cmi`).
    * Ve a la pestaña "Importar", selecciona el archivo `.sql` del proyecto (debes incluirlo en tu repo) y haz clic en "Continuar".

5.  **Configurar la Conexión:**
    * (Si es necesario) Modifica el archivo de conexión de la base de datos en el proyecto (p.ej., `db.php`) con el nombre de tu base de datos, usuario (`root`) y contraseña (usualmente vacía por defecto en XAMPP).

6.  **Acceder al Sistema:**
    * Abre tu navegador (Google Chrome recomendado) e ingresa a la URL:
    * `http://localhost/realinventario/login.php`

## 🔑 Credenciales de Acceso

Puedes usar las siguientes credenciales de ejemplo (o las que hayas configurado en tu base de datos):

* **Usuario:** `admin`
* **Contraseña:** `admin123`
