$ErrorActionPreference = 'Stop'

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..')
$docsDir = Join-Path $projectRoot 'docs'
$output = Join-Path $docsDir 'Informe_Primer_Avance_RSU.docx'
$tempDir = Join-Path $env:TEMP ('rsu_docx_' + [guid]::NewGuid().ToString('N'))

function Escape-Xml([string] $value) {
    if ($null -eq $value) { return '' }

    $clean = [regex]::Replace($value, '[^\u0009\u000A\u000D\u0020-\uD7FF\uE000-\uFFFD]', '')
    return [System.Security.SecurityElement]::Escape($clean)
}

function Write-Utf8NoBom([string] $path, [string] $content) {
    $encoding = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllText($path, $content, $encoding)
}

function TextRun([string] $text, [string] $style = 'Normal') {
    $escaped = Escape-Xml $text
    $preserve = if ($text -match '^\s|\s$|  ') { ' xml:space="preserve"' } else { '' }

    if ($style -eq 'Code') {
        return "<w:r><w:rPr><w:rStyle w:val=`"CodeChar`"/></w:rPr><w:t$preserve>$escaped</w:t></w:r>"
    }

    if ($style -eq 'Bold') {
        return "<w:r><w:rPr><w:b/></w:rPr><w:t$preserve>$escaped</w:t></w:r>"
    }

    return "<w:r><w:t$preserve>$escaped</w:t></w:r>"
}

function Paragraph([string] $text, [string] $style = 'Normal') {
    $pStyle = if ($style -ne 'Normal' -and $style -ne 'Code') { "<w:pPr><w:pStyle w:val=`"$style`"/></w:pPr>" } else { '' }

    if ($style -eq 'Code') {
        $lines = $text -split "`r?`n"
        $runs = foreach ($line in $lines) {
            (TextRun $line 'Code') + '<w:br/>'
        }

        return "<w:p><w:pPr><w:pStyle w:val=`"CodeBlock`"/></w:pPr>$($runs -join '')</w:p>"
    }

    return "<w:p>$pStyle$(TextRun $text)</w:p>"
}

function Bullet([string] $text) {
    return "<w:p><w:pPr><w:pStyle w:val=`"ListParagraph`"/><w:numPr><w:ilvl w:val=`"0`"/><w:numId w:val=`"1`"/></w:numPr></w:pPr>$(TextRun $text)</w:p>"
}

function Table($headers, $rows) {
    $table = @"
<w:tbl>
<w:tblPr>
<w:tblStyle w:val="TableGrid"/>
<w:tblW w:w="0" w:type="auto"/>
<w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/>
</w:tblPr>
"@

    $table += '<w:tr>'
    foreach ($header in $headers) {
        $table += "<w:tc><w:tcPr><w:shd w:fill=`"D9EAF7`"/></w:tcPr><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>$(Escape-Xml $header)</w:t></w:r></w:p></w:tc>"
    }
    $table += '</w:tr>'

    foreach ($row in $rows) {
        $table += '<w:tr>'
        foreach ($cell in $row) {
            $table += "<w:tc><w:p>$(TextRun ([string] $cell))</w:p></w:tc>"
        }
        $table += '</w:tr>'
    }

    $table += '</w:tbl>'
    return $table
}

function Read-ProjectFile([string] $relativePath) {
    $path = Join-Path $projectRoot $relativePath
    return (Get-Content -LiteralPath $path -Raw).Trim()
}

New-Item -ItemType Directory -Force -Path $docsDir | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $tempDir '_rels') | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $tempDir 'docProps') | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $tempDir 'word/_rels') | Out-Null

$body = New-Object System.Collections.Generic.List[string]

$body.Add((Paragraph 'Informe Tecnico del Primer Avance' 'Title'))
$body.Add((Paragraph 'Aplicacion Web para el Proyecto de Reciclaje del area de RSU' 'Subtitle'))
$body.Add((Paragraph 'Escuela de Ingenieria de Sistemas y Computacion - USAT' 'Subtitle'))
$body.Add((Paragraph 'Curso: Topicos Avanzados en Desarrollo de Software' 'Subtitle'))
$body.Add((Paragraph 'Framework: Laravel 10 | Base de datos: MySQL/MariaDB XAMPP' 'Subtitle'))
$body.Add((Paragraph ('Fecha de elaboracion: ' + (Get-Date -Format 'dd/MM/yyyy')) 'Subtitle'))

$body.Add((Paragraph '1. Resumen Ejecutivo' 'Heading1'))
$body.Add((Paragraph 'El presente informe documenta el Primer Avance de la aplicacion web orientada al proyecto de reciclaje del area de Responsabilidad Social Universitaria. El avance implementa la base funcional del sistema: autenticacion, dashboard administrativo y los CRUD iniciales del modulo Gestion de Vehiculos.'))
$body.Add((Paragraph 'La solucion fue desarrollada con Laravel 10, Laravel AdminLTE para el panel administrativo, Jetstream/Fortify para autenticacion y Laravel Collective para formularios. La persistencia se configuro en MySQL/MariaDB de XAMPP para que los datos sean revisables desde phpMyAdmin.'))

$body.Add((Paragraph '2. Alcance del Primer Avance' 'Heading1'))
$body.Add((Bullet 'Inicio de sesion con usuario precargado.'))
$body.Add((Bullet 'Interfaz administrativa con menu lateral AdminLTE.'))
$body.Add((Bullet 'Dashboard inicial con informacion general del sistema.'))
$body.Add((Bullet 'CRUD de colores de vehiculos.'))
$body.Add((Bullet 'CRUD de marcas de vehiculos.'))
$body.Add((Bullet 'CRUD de modelos asociados a marcas.'))
$body.Add((Bullet 'CRUD de tipos de vehiculos.'))
$body.Add((Bullet 'Base de datos MySQL visible desde phpMyAdmin.'))
$body.Add((Bullet 'Validaciones separadas en Form Requests.'))
$body.Add((Bullet 'Mensajes de autenticacion y validacion en espanol.'))

$body.Add((Paragraph '3. Tecnologias y Herramientas' 'Heading1'))
$body.Add((Table @('Herramienta', 'Uso en el proyecto') @(
    @('Laravel 10', 'Framework principal de desarrollo backend'),
    @('PHP 8.2 XAMPP', 'Runtime de ejecucion local'),
    @('MySQL/MariaDB', 'Motor de base de datos'),
    @('phpMyAdmin', 'Administracion visual de base de datos'),
    @('Laravel AdminLTE', 'Plantilla de panel administrativo'),
    @('Jetstream/Fortify', 'Autenticacion e inicio de sesion'),
    @('Laravel Collective', 'Construccion de formularios Blade'),
    @('Blade', 'Motor de plantillas de Laravel')
)))

$body.Add((Paragraph '4. Configuracion de Base de Datos' 'Heading1'))
$body.Add((Paragraph 'El proyecto fue configurado para trabajar con MySQL/MariaDB de XAMPP. La base creada se llama rsu_proyectofinal y puede visualizarse desde phpMyAdmin.'))
$body.Add((Paragraph 'Configuracion principal en .env:' 'Heading2'))
$body.Add((Paragraph @'
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rsu_proyectofinal
DB_USERNAME=root
DB_PASSWORD=
'@ 'Code'))
$body.Add((Paragraph 'Tablas principales creadas:' 'Heading2'))
$body.Add((Bullet 'users'))
$body.Add((Bullet 'vehicle_colors'))
$body.Add((Bullet 'brands'))
$body.Add((Bullet 'brand_models'))
$body.Add((Bullet 'vehicle_types'))
$body.Add((Bullet 'sessions'))

$body.Add((Paragraph '5. Acceso al Sistema' 'Heading1'))
$body.Add((Paragraph 'URL local de acceso:' 'Heading2'))
$body.Add((Paragraph 'http://127.0.0.1:8000/login' 'Code'))
$body.Add((Paragraph 'Usuario inicial:' 'Heading2'))
$body.Add((Paragraph @'
Correo: juliaan.arm@gmail.com
Contrasena: julian123
Nombre: Julian Armas
'@ 'Code'))
$body.Add((Paragraph 'El registro publico de usuarios fue omitido para este avance. El usuario se crea mediante DatabaseSeeder para asegurar una revision rapida y reproducible.'))

$body.Add((Paragraph '6. Arquitectura Aplicada' 'Heading1'))
$body.Add((Paragraph 'La implementacion respeta MVC: las rutas declaran el acceso, los controladores coordinan las acciones, los Form Requests validan entradas, los modelos representan tablas y relaciones, y las vistas Blade renderizan la interfaz.'))
$body.Add((Paragraph 'Rutas protegidas y organizadas:' 'Heading2'))
$body.Add((Paragraph (Read-ProjectFile 'routes/web.php') 'Code'))

$body.Add((Paragraph '7. Buenas Practicas Aplicadas' 'Heading1'))
$body.Add((Bullet 'Uso de Route::resource para CRUDs.'))
$body.Add((Bullet 'Agrupacion de rutas con middleware auth.'))
$body.Add((Bullet 'Validaciones en Form Requests dedicadas.'))
$body.Add((Bullet 'Controladores delgados y faciles de leer.'))
$body.Add((Bullet 'Modelos con fillable y relaciones Eloquent.'))
$body.Add((Bullet 'Vistas organizadas por modulo y modales separados en parciales.'))
$body.Add((Bullet 'Seeders para reconstruir datos iniciales.'))
$body.Add((Bullet 'Traducciones en lang/es para mensajes de autenticacion y validacion.'))

$body.Add((Paragraph '8. Modulo Gestion de Vehiculos' 'Heading1'))
$body.Add((Paragraph 'El menu Gestion de Vehiculos contiene las opciones necesarias para este avance: Colores, Marcas, Modelos y Tipo de Vehiculos. Cada seccion cuenta con listado, busqueda, paginacion, registro, edicion y eliminacion.'))

$body.Add((Paragraph '8.1 CRUD de Colores' 'Heading2'))
$body.Add((Paragraph 'Tabla: vehicle_colors. Permite registrar colores con codigo hexadecimal RGB, una descripcion opcional y vista previa visual.'))
$body.Add((Table @('Campo', 'Descripcion', 'Validacion') @(
    @('name', 'Nombre del color', 'Obligatorio, texto, maximo 80 caracteres'),
    @('code', 'Codigo hexadecimal RGB', 'Obligatorio, unico, formato #RRGGBB'),
    @('description', 'Descripcion opcional', 'Texto, maximo 255 caracteres')
)))
$body.Add((Paragraph 'Form Request de colores:' 'Heading3'))
$body.Add((Paragraph (Read-ProjectFile 'app/Http/Requests/StoreVehicleColorRequest.php') 'Code'))
$body.Add((Paragraph 'Controlador de colores:' 'Heading3'))
$body.Add((Paragraph (Read-ProjectFile 'app/Http/Controllers/VehicleColorController.php') 'Code'))

$body.Add((Paragraph '8.2 CRUD de Marcas' 'Heading2'))
$body.Add((Paragraph 'Tabla: brands. Permite registrar marcas, descripcion y logo opcional. Tambien se evita eliminar una marca si tiene modelos asociados.'))
$body.Add((Table @('Campo', 'Descripcion', 'Validacion') @(
    @('name', 'Nombre de la marca', 'Obligatorio, unico, maximo 100 caracteres'),
    @('description', 'Descripcion opcional', 'Texto, maximo 255 caracteres'),
    @('logo_path', 'Ruta del logo almacenado', 'Se carga desde formulario como imagen opcional')
)))
$body.Add((Paragraph 'Controlador de marcas:' 'Heading3'))
$body.Add((Paragraph (Read-ProjectFile 'app/Http/Controllers/BrandController.php') 'Code'))

$body.Add((Paragraph '8.3 CRUD de Modelos' 'Heading2'))
$body.Add((Paragraph 'Tabla: brand_models. Cada modelo pertenece a una marca y cuenta con codigo unico.'))
$body.Add((Table @('Campo', 'Descripcion', 'Validacion') @(
    @('brand_id', 'Marca asociada', 'Obligatorio, debe existir en brands'),
    @('name', 'Nombre del modelo', 'Obligatorio, unico por marca'),
    @('code', 'Codigo del modelo', 'Obligatorio y unico'),
    @('description', 'Descripcion opcional', 'Texto, maximo 255 caracteres')
)))
$body.Add((Paragraph 'Modelo Eloquent con relacion:' 'Heading3'))
$body.Add((Paragraph (Read-ProjectFile 'app/Models/BrandModel.php') 'Code'))

$body.Add((Paragraph '8.4 CRUD de Tipos de Vehiculos' 'Heading2'))
$body.Add((Paragraph 'Tabla: vehicle_types. Registra las categorias base de vehiculos utilizadas luego por el modulo de vehiculos completo.'))
$body.Add((Table @('Campo', 'Descripcion', 'Validacion') @(
    @('name', 'Nombre del tipo', 'Obligatorio, unico, maximo 100 caracteres'),
    @('description', 'Descripcion opcional', 'Texto, maximo 255 caracteres')
)))
$body.Add((Paragraph 'Controlador de tipos de vehiculos:' 'Heading3'))
$body.Add((Paragraph (Read-ProjectFile 'app/Http/Controllers/VehicleTypeController.php') 'Code'))

$body.Add((Paragraph '9. Migraciones Principales' 'Heading1'))
$body.Add((Paragraph 'Las migraciones definen el esquema de la base de datos. A continuacion se muestran las tablas propias del avance.'))
$body.Add((Paragraph 'Migracion vehicle_colors:' 'Heading2'))
$body.Add((Paragraph (Read-ProjectFile 'database/migrations/2026_05_30_080540_create_vehicle_colors_table.php') 'Code'))
$body.Add((Paragraph 'Migracion brands:' 'Heading2'))
$body.Add((Paragraph (Read-ProjectFile 'database/migrations/2026_05_30_080540_create_brands_table.php') 'Code'))
$body.Add((Paragraph 'Migracion brand_models:' 'Heading2'))
$body.Add((Paragraph (Read-ProjectFile 'database/migrations/2026_05_30_080541_create_brand_models_table.php') 'Code'))
$body.Add((Paragraph 'Migracion vehicle_types:' 'Heading2'))
$body.Add((Paragraph (Read-ProjectFile 'database/migrations/2026_05_30_080540_create_vehicle_types_table.php') 'Code'))

$body.Add((Paragraph '10. Datos Iniciales' 'Heading1'))
$body.Add((Paragraph 'El archivo DatabaseSeeder crea el usuario de revision y datos iniciales para colores, marcas, modelos y tipos de vehiculos. Esto permite reconstruir el entorno rapidamente con migrate:fresh --seed.'))
$body.Add((Paragraph (Read-ProjectFile 'database/seeders/DatabaseSeeder.php') 'Code'))

$body.Add((Paragraph '11. Traduccion de Mensajes' 'Heading1'))
$body.Add((Paragraph 'Se configuro el idioma español en config/app.php y se agregaron archivos en lang/es. Con ello el mensaje de credenciales incorrectas se muestra en español.'))
$body.Add((Paragraph 'Archivo lang/es/auth.php:' 'Heading2'))
$body.Add((Paragraph (Read-ProjectFile 'lang/es/auth.php') 'Code'))

$body.Add((Paragraph '12. Interfaz y Recursos Visuales' 'Heading1'))
$body.Add((Paragraph 'La interfaz usa AdminLTE, estilos personalizados y recursos graficos del proyecto. El logo institucional se muestra en login y barra lateral; la imagen muni.jpg se utiliza como imagen representativa en el dashboard.'))
$body.Add((Bullet 'Logo: public/images/municipalidad-jlo.png'))
$body.Add((Bullet 'Imagen dashboard: public/images/muni.jpg'))
$body.Add((Bullet 'Estilos personalizados: public/css/rsu.css'))

$body.Add((Paragraph '13. Comandos de Ejecucion' 'Heading1'))
$body.Add((Paragraph 'Levantar servidor local:' 'Heading2'))
$body.Add((Paragraph 'C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000' 'Code'))
$body.Add((Paragraph 'Migrar y cargar seeders:' 'Heading2'))
$body.Add((Paragraph 'C:\xampp\php\php.exe artisan migrate:fresh --seed' 'Code'))
$body.Add((Paragraph 'Limpiar cache de configuracion:' 'Heading2'))
$body.Add((Paragraph 'C:\xampp\php\php.exe artisan config:clear' 'Code'))
$body.Add((Paragraph 'Ejecutar pruebas:' 'Heading2'))
$body.Add((Paragraph 'C:\xampp\php\php.exe artisan test' 'Code'))
$body.Add((Paragraph 'Ver rutas:' 'Heading2'))
$body.Add((Paragraph 'C:\xampp\php\php.exe artisan route:list --except-vendor' 'Code'))

$body.Add((Paragraph '14. Verificacion Realizada' 'Heading1'))
$body.Add((Bullet 'La aplicacion responde en http://127.0.0.1:8000/login.'))
$body.Add((Bullet 'El usuario Julian Armas puede iniciar sesion.'))
$body.Add((Bullet 'La base rsu_proyectofinal se encuentra en MySQL/MariaDB y puede verse desde phpMyAdmin.'))
$body.Add((Bullet 'Los CRUD del avance cargan y guardan datos correctamente.'))
$body.Add((Bullet 'El mensaje de credenciales incorrectas se visualiza en español.'))
$body.Add((Bullet 'Las pruebas automatizadas pasan correctamente.'))

$body.Add((Paragraph '15. Pendientes para Siguientes Avances' 'Heading1'))
$body.Add((Bullet 'CRUD completo de vehiculos con placa, codigo, año, capacidad e imagenes.'))
$body.Add((Bullet 'Gestion de personal y tipos de personal.'))
$body.Add((Bullet 'Contratos, vacaciones y asistencias.'))
$body.Add((Bullet 'Gestion de zonas y rutas.'))
$body.Add((Bullet 'Turnos y programacion de recorridos.'))
$body.Add((Bullet 'Validaciones avanzadas de disponibilidad de personal y vehiculos.'))

$body.Add((Paragraph '16. Conclusion' 'Heading1'))
$body.Add((Paragraph 'El Primer Avance establece una base tecnica ordenada y mantenible para continuar el desarrollo del sistema. Se implementaron autenticacion, estructura visual, base de datos MySQL y los CRUD iniciales de Gestion de Vehiculos, siguiendo buenas practicas de Laravel como rutas resource, Form Requests, MVC, modelos con relaciones y controladores simples.'))

$documentXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas"
xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math"
xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing"
xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
xmlns:w10="urn:schemas-microsoft-com:office:word"
xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml"
xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup"
xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk"
xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml"
xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape"
mc:Ignorable="w14 wp14">
<w:body>
$($body -join "`n")
<w:sectPr>
<w:pgSz w:w="12240" w:h="15840"/>
<w:pgMar w:top="1080" w:right="1080" w:bottom="1080" w:left="1080" w:header="720" w:footer="720" w:gutter="0"/>
</w:sectPr>
</w:body>
</w:document>
"@

$stylesXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:qFormat/><w:rPr><w:rFonts w:ascii="Aptos" w:hAnsi="Aptos"/><w:sz w:val="22"/></w:rPr><w:pPr><w:spacing w:after="160" w:line="276" w:lineRule="auto"/></w:pPr></w:style>
<w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:basedOn w:val="Normal"/><w:qFormat/><w:rPr><w:b/><w:color w:val="0B3D62"/><w:sz w:val="40"/></w:rPr><w:pPr><w:jc w:val="center"/><w:spacing w:after="260"/></w:pPr></w:style>
<w:style w:type="paragraph" w:styleId="Subtitle"><w:name w:val="Subtitle"/><w:basedOn w:val="Normal"/><w:qFormat/><w:rPr><w:color w:val="4B5563"/><w:sz w:val="24"/></w:rPr><w:pPr><w:jc w:val="center"/><w:spacing w:after="120"/></w:pPr></w:style>
<w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:qFormat/><w:rPr><w:b/><w:color w:val="005B8F"/><w:sz w:val="30"/></w:rPr><w:pPr><w:spacing w:before="300" w:after="160"/></w:pPr></w:style>
<w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:qFormat/><w:rPr><w:b/><w:color w:val="0F5F7F"/><w:sz w:val="26"/></w:rPr><w:pPr><w:spacing w:before="220" w:after="120"/></w:pPr></w:style>
<w:style w:type="paragraph" w:styleId="Heading3"><w:name w:val="heading 3"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:qFormat/><w:rPr><w:b/><w:color w:val="374151"/><w:sz w:val="23"/></w:rPr><w:pPr><w:spacing w:before="160" w:after="100"/></w:pPr></w:style>
<w:style w:type="paragraph" w:styleId="ListParagraph"><w:name w:val="List Paragraph"/><w:basedOn w:val="Normal"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr></w:style>
<w:style w:type="paragraph" w:styleId="CodeBlock"><w:name w:val="Code Block"/><w:basedOn w:val="Normal"/><w:pPr><w:shd w:fill="F3F4F6"/><w:spacing w:before="80" w:after="160"/></w:pPr><w:rPr><w:rFonts w:ascii="Consolas" w:hAnsi="Consolas"/><w:sz w:val="18"/></w:rPr></w:style>
<w:style w:type="character" w:styleId="CodeChar"><w:name w:val="Code Char"/><w:rPr><w:rFonts w:ascii="Consolas" w:hAnsi="Consolas"/><w:sz w:val="18"/></w:rPr></w:style>
<w:style w:type="table" w:styleId="TableGrid"><w:name w:val="Table Grid"/><w:tblPr><w:tblBorders><w:top w:val="single" w:sz="4" w:color="AAB7C4"/><w:left w:val="single" w:sz="4" w:color="AAB7C4"/><w:bottom w:val="single" w:sz="4" w:color="AAB7C4"/><w:right w:val="single" w:sz="4" w:color="AAB7C4"/><w:insideH w:val="single" w:sz="4" w:color="AAB7C4"/><w:insideV w:val="single" w:sz="4" w:color="AAB7C4"/></w:tblBorders></w:tblPr></w:style>
</w:styles>
"@

$numberingXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:abstractNum w:abstractNumId="0">
<w:multiLevelType w:val="hybridMultilevel"/>
<w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="•"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr></w:lvl>
</w:abstractNum>
<w:num w:numId="1"><w:abstractNumId w:val="0"/></w:num>
</w:numbering>
"@

$contentTypesXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
<Override PartName="/word/numbering.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml"/>
<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
"@

$relsXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
"@

$documentRelsXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/>
</Relationships>
"@

$coreXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<dc:title>Informe Tecnico del Primer Avance RSU</dc:title>
<dc:creator>Proyecto RSU - USAT</dc:creator>
<cp:lastModifiedBy>Codex</cp:lastModifiedBy>
<dcterms:created xsi:type="dcterms:W3CDTF">$(Get-Date -Format s)Z</dcterms:created>
<dcterms:modified xsi:type="dcterms:W3CDTF">$(Get-Date -Format s)Z</dcterms:modified>
</cp:coreProperties>
"@

$appXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
<Application>Microsoft Word</Application>
</Properties>
"@

Write-Utf8NoBom (Join-Path $tempDir '[Content_Types].xml') $contentTypesXml
Write-Utf8NoBom (Join-Path $tempDir '_rels/.rels') $relsXml
Write-Utf8NoBom (Join-Path $tempDir 'docProps/core.xml') $coreXml
Write-Utf8NoBom (Join-Path $tempDir 'docProps/app.xml') $appXml
Write-Utf8NoBom (Join-Path $tempDir 'word/document.xml') $documentXml
Write-Utf8NoBom (Join-Path $tempDir 'word/styles.xml') $stylesXml
Write-Utf8NoBom (Join-Path $tempDir 'word/numbering.xml') $numberingXml
Write-Utf8NoBom (Join-Path $tempDir 'word/_rels/document.xml.rels') $documentRelsXml

Get-ChildItem -LiteralPath $tempDir -Recurse -Filter *.xml | ForEach-Object {
    [xml] (Get-Content -LiteralPath $_.FullName -Raw) | Out-Null
}

if (Test-Path $output) {
    Remove-Item -LiteralPath $output -Force
}

Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($tempDir, $output)
Remove-Item -LiteralPath $tempDir -Recurse -Force

Write-Host "Documento generado: $output"
