$ErrorActionPreference = 'Stop'

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..')
$outputDir = Join-Path $projectRoot 'documentacion'
$tempDir = Join-Path $env:TEMP ('rsu_avance03_docx_' + [guid]::NewGuid().ToString('N'))
$outputFile = Join-Path $outputDir 'Informe_Avance_03_RSU.docx'

function Escape-Xml {
    param([AllowNull()][string] $Text)

    if ($null -eq $Text) {
        return ''
    }

    return [System.Security.SecurityElement]::Escape($Text)
}

function Run {
    param(
        [string] $Text,
        [string] $Style = '',
        [bool] $Bold = $false,
        [string] $Color = '',
        [string] $Size = '',
        [bool] $Preserve = $false
    )

    $properties = ''
    if ($Bold) { $properties += '<w:b/>' }
    if ($Color) { $properties += "<w:color w:val=`"$Color`"/>" }
    if ($Size) { $properties += "<w:sz w:val=`"$Size`"/><w:szCs w:val=`"$Size`"/>" }
    if ($Style -eq 'Code') { $properties += '<w:rFonts w:ascii="Consolas" w:hAnsi="Consolas"/>' }

    $runProperties = if ($properties) { "<w:rPr>$properties</w:rPr>" } else { '' }
    $space = if ($Preserve) { ' xml:space="preserve"' } else { '' }

    return "<w:r>$runProperties<w:t$space>$(Escape-Xml $Text)</w:t></w:r>"
}

function Paragraph {
    param(
        [string] $Text = '',
        [string] $Style = '',
        [bool] $Bold = $false,
        [string] $Color = '',
        [string] $Size = ''
    )

    $paragraphStyle = if ($Style) { "<w:pPr><w:pStyle w:val=`"$Style`"/></w:pPr>" } else { '' }
    return "<w:p>$paragraphStyle$(Run -Text $Text -Style $Style -Bold $Bold -Color $Color -Size $Size)</w:p>"
}

function Bullet {
    param([string] $Text)

    return "<w:p><w:pPr><w:pStyle w:val=`"ListParagraph`"/></w:pPr>$(Run -Text "- $Text")</w:p>"
}

function CodeBlock {
    param([string] $Code)

    $items = @()
    foreach ($line in ($Code -split "`r?`n")) {
        $items += "<w:p><w:pPr><w:pStyle w:val=`"Code`"/></w:pPr>$(Run -Text $line -Style 'Code' -Preserve $true)</w:p>"
    }

    return ($items -join "`n")
}

function Table {
    param([string[][]] $Rows)

    $xml = @()
    $xml += '<w:tbl>'
    $xml += '<w:tblPr><w:tblW w:w="5000" w:type="pct"/><w:tblBorders><w:top w:val="single" w:sz="6" w:color="D9DEE8"/><w:left w:val="single" w:sz="6" w:color="D9DEE8"/><w:bottom w:val="single" w:sz="6" w:color="D9DEE8"/><w:right w:val="single" w:sz="6" w:color="D9DEE8"/><w:insideH w:val="single" w:sz="6" w:color="D9DEE8"/><w:insideV w:val="single" w:sz="6" w:color="D9DEE8"/></w:tblBorders></w:tblPr>'

    for ($i = 0; $i -lt $Rows.Length; $i++) {
        $xml += '<w:tr>'
        foreach ($cell in $Rows[$i]) {
            $fill = if ($i -eq 0) { '<w:shd w:fill="0E3C67"/>' } else { '' }
            $color = if ($i -eq 0) { 'FFFFFF' } else { '1F2937' }
            $bold = $i -eq 0
            $xml += "<w:tc><w:tcPr><w:tcW w:w=`"2400`" w:type=`"dxa`"/>$fill</w:tcPr><w:p>$(Run -Text $cell -Bold $bold -Color $color)</w:p></w:tc>"
        }
        $xml += '</w:tr>'
    }

    $xml += '</w:tbl>'
    return ($xml -join "`n")
}

function Ensure-Directory {
    param([string] $Path)

    if (-not (Test-Path $Path)) {
        New-Item -ItemType Directory -Path $Path | Out-Null
    }
}

Ensure-Directory $outputDir
Ensure-Directory $tempDir
Ensure-Directory (Join-Path $tempDir '_rels')
Ensure-Directory (Join-Path $tempDir 'docProps')
Ensure-Directory (Join-Path $tempDir 'word')

$body = @()
$body += Paragraph 'Informe Tecnico - Avance 03' 'Title'
$body += Paragraph 'Sistema web de reciclaje RSU - Escuela de Ingenieria de Sistemas y Computacion USAT' 'Subtitle'
$body += Paragraph 'Curso: Topicos Avanzados en Desarrollo de Software'
$body += Paragraph 'Framework: Laravel 10, Laravel AdminLTE, JetStream, Laravel Collective, MySQL/XAMPP'
$body += Paragraph 'Fecha de elaboracion: 06/06/2026'
$body += Paragraph ''

$body += Paragraph '1. Resumen Ejecutivo' 'Heading1'
$body += Paragraph 'En el Avance 03 se desarrollaron los modulos de asistencias, vacaciones y turnos/programacion para el sistema RSU. El objetivo fue completar la gestion operativa del personal, permitiendo registrar entradas y salidas, administrar solicitudes de vacaciones con reglas de negocio y mantener los turnos usados por el sistema.'
$body += Paragraph 'La implementacion mantiene el patron MVC de Laravel, usa rutas resource para CRUD, Form Requests para validacion, modelos Eloquent con relaciones claras, vistas Blade basadas en AdminLTE y operaciones AJAX coherentes con los avances anteriores.'

$body += Paragraph '2. Alcance del Avance 03' 'Heading1'
$body += Bullet 'Modulo de Asistencias: listado, filtros por fecha y empleado, registro en modal, edicion y eliminacion.'
$body += Bullet 'Modulo de Vacaciones: listado, nueva solicitud, aprobacion, rechazo, cancelacion y descuento de saldo anual.'
$body += Bullet 'Modulo de Turnos: CRUD de turnos con hora de inicio, hora de termino y descripcion.'
$body += Bullet 'Datos de prueba para revision rapida en fechas cercanas a junio y julio de 2026.'
$body += Bullet 'Integracion con el menu lateral de AdminLTE dentro de Gestion de personal y Programacion.'

$body += Paragraph '3. Modulos Implementados' 'Heading1'
$body += Paragraph '3.1 Asistencias' 'Heading2'
$body += Paragraph 'El modulo de asistencias permite registrar la asistencia del personal operativo. Incluye filtros por fecha de inicio, fecha de fin y busqueda por DNI, nombres o apellidos. En el formulario se selecciona al empleado, la fecha, la hora, el tipo de registro, el estado, el turno y notas opcionales.'
$body += Bullet 'Tipos principales: Entrada y Salida.'
$body += Bullet 'Estados principales: Presente, Tardanza, Falta y Justificado.'
$body += Bullet 'El primer registro del dia para un empleado debe ser Entrada.'
$body += Bullet 'Se evita duplicar el mismo tipo de asistencia para el mismo empleado y fecha.'
$body += Bullet 'El turno puede asignarse automaticamente segun la hora registrada.'

$body += Paragraph '3.2 Vacaciones' 'Heading2'
$body += Paragraph 'El modulo de vacaciones permite registrar solicitudes, validar elegibilidad del personal, evitar cruces de fechas y aprobar o rechazar solicitudes. La regla mas importante del avance se implemento al aprobar: los dias solicitados se descuentan del saldo disponible del empleado en el año correspondiente.'
$body += Bullet 'Solo personal activo con contrato Permanente o Nombrado puede solicitar vacaciones.'
$body += Bullet 'No se permiten solicitudes superpuestas con vacaciones aprobadas o pendientes.'
$body += Bullet 'Al aprobar se descuenta el saldo anual en la tabla vacation_balances.'
$body += Bullet 'Las solicitudes aprobadas ya no se eliminan; las pendientes pueden cancelarse.'

$body += Paragraph '3.3 Turnos' 'Heading2'
$body += Paragraph 'El modulo de turnos permite administrar los horarios usados para clasificar registros de asistencia. Soporta turnos normales y turnos que cruzan medianoche, como Madrugada de 22:00 a 06:00.'

$body += Paragraph '4. Base de Datos' 'Heading1'
$body += Table @(
    @('Tabla', 'Descripcion', 'Campos principales'),
    @('attendances', 'Registros de asistencia del personal.', 'employee_id, shift_id, attendance_date, attendance_time, type, status, notes'),
    @('vacation_requests', 'Solicitudes de vacaciones.', 'employee_id, vacation_balance_id, requested_at, start_date, end_date, days_requested, remaining_days, status'),
    @('vacation_balances', 'Saldo anual de vacaciones por empleado.', 'employee_id, year, total_days, used_days, available_days'),
    @('shifts', 'Turnos de trabajo.', 'name, start_time, end_time, description')
)

$body += Paragraph '5. Rutas Implementadas' 'Heading1'
$body += Paragraph 'Las rutas se agruparon bajo middleware auth y se usaron recursos REST cuando correspondia, manteniendo rutas limpias y mantenibles.'
$body += CodeBlock @'
Route::resource('asistencias', AttendanceController::class)
    ->parameters(['asistencias' => 'attendance']);

Route::put('vacaciones/{vacation}/aprobar', [VacationRequestController::class, 'approve'])
    ->name('vacations.approve');

Route::put('vacaciones/{vacation}/rechazar', [VacationRequestController::class, 'reject'])
    ->name('vacations.reject');

Route::resource('vacaciones', VacationRequestController::class)
    ->parameters(['vacaciones' => 'vacation']);

Route::resource('turnos', ShiftController::class)
    ->parameters(['turnos' => 'shift']);
'@

$body += Paragraph '6. Reglas de Negocio Principales' 'Heading1'
$body += Table @(
    @('Modulo', 'Regla', 'Resultado'),
    @('Asistencias', 'El primer registro del dia debe ser Entrada.', 'Evita salidas sin una entrada previa.'),
    @('Asistencias', 'No repetir tipo de registro por empleado y fecha.', 'Evita duplicidad de entradas o salidas.'),
    @('Vacaciones', 'Solo contrato Permanente o Nombrado activo.', 'Evita solicitudes de personal no elegible.'),
    @('Vacaciones', 'No cruzar fechas con solicitudes pendientes o aprobadas.', 'Mantiene disponibilidad coherente.'),
    @('Vacaciones', 'Al aprobar se descuentan dias del saldo anual.', 'Actualiza used_days y available_days.'),
    @('Turnos', 'Validar hora de inicio y termino.', 'Permite clasificar asistencias por horario.')
)

$body += Paragraph '7. Codigo Relevante' 'Heading1'
$body += Paragraph '7.1 Descuento de vacaciones al aprobar' 'Heading2'
$body += CodeBlock @'
public function approve(Request $request, VacationRequest $vacation)
{
    if ($vacation->status !== VacationRequest::STATUS_PENDING) {
        return $this->errorResponse($request, 'vacations.index', 'Solo se pueden aprobar solicitudes pendientes.');
    }

    $message = DB::transaction(function () use ($vacation) {
        $balance = VacationBalance::where('employee_id', $vacation->employee_id)
            ->where('year', $vacation->start_date->year)
            ->lockForUpdate()
            ->first();

        if (! $balance) {
            $balance = VacationBalance::create([
                'employee_id' => $vacation->employee_id,
                'year' => $vacation->start_date->year,
                'total_days' => VacationBalance::DEFAULT_ANNUAL_DAYS,
                'used_days' => 0,
                'available_days' => VacationBalance::DEFAULT_ANNUAL_DAYS,
            ]);
        }

        if (! $balance->canUse($vacation->days_requested)) {
            return 'No se puede aprobar. El personal solo tiene '.$balance->available_days.' dias disponibles.';
        }

        $balance->discount($vacation->days_requested);

        $vacation->update([
            'vacation_balance_id' => $balance->id,
            'remaining_days' => $balance->available_days,
            'status' => VacationRequest::STATUS_APPROVED,
        ]);

        return null;
    });
}
'@

$body += Paragraph '7.2 Modelo VacationBalance' 'Heading2'
$body += CodeBlock @'
class VacationBalance extends Model
{
    public const DEFAULT_ANNUAL_DAYS = 30;

    protected $fillable = [
        'employee_id',
        'year',
        'total_days',
        'used_days',
        'available_days',
    ];

    public function canUse(int $days): bool
    {
        return $this->available_days >= $days;
    }

    public function discount(int $days): void
    {
        $this->forceFill([
            'used_days' => $this->used_days + $days,
            'available_days' => $this->available_days - $days,
        ])->save();
    }
}
'@

$body += Paragraph '7.3 Validacion de vacaciones' 'Heading2'
$body += CodeBlock @'
$validator->after(function ($validator) {
    $employee = Employee::with('contracts')->find($this->employee_id);

    $hasEligibleContract = $employee?->contracts()
        ->where('is_active', true)
        ->whereIn('contract_type', [Contract::TYPE_PERMANENT, Contract::TYPE_NAMED])
        ->exists();

    if (! $hasEligibleContract) {
        $validator->errors()->add(
            'employee_id',
            'Solo personal con contrato permanente o nombrado activo puede solicitar vacaciones.'
        );
    }
});
'@

$body += Paragraph '7.4 Asignacion automatica de turno por hora' 'Heading2'
$body += CodeBlock @'
private function shiftForTime(string $time): ?Shift
{
    return Shift::orderBy('start_time')->get()->first(function (Shift $shift) use ($time) {
        if ($shift->start_time <= $shift->end_time) {
            return $time >= $shift->start_time && $time < $shift->end_time;
        }

        return $time >= $shift->start_time || $time < $shift->end_time;
    }) ?? Shift::orderBy('start_time')->first();
}
'@

$body += Paragraph '8. Datos de Prueba para Revision Rapida' 'Heading1'
$body += Paragraph 'Se agregaron datos cercanos a la fecha de trabajo del proyecto para que la revision sea rapida en pantalla.'
$body += Table @(
    @('Modulo', 'Personal', 'Fecha / Periodo', 'Estado'),
    @('Contrato', 'Diego Campos Nunez', 'Inicio: 15/05/2026', 'Permanente activo'),
    @('Contrato', 'Valeria Flores Cardenas', '01/06/2026 - 31/08/2026', 'Temporal activo'),
    @('Vacaciones', 'Esteban Rojas Salazar', '03/06/2026 - 06/06/2026, 4 dias', 'Aprobado, quedan 26 dias'),
    @('Vacaciones', 'Lucia Benites Paredes', '10/06/2026 - 14/06/2026, 5 dias', 'Pendiente'),
    @('Vacaciones', 'Mariana Torres Vega', '24/06/2026 - 26/06/2026, 3 dias', 'Aprobado, quedan 27 dias'),
    @('Vacaciones', 'Diego Campos Nunez', '06/07/2026 - 11/07/2026, 6 dias', 'Pendiente')
)

$body += Paragraph '9. Buenas Practicas Aplicadas' 'Heading1'
$body += Bullet 'Uso de Route::resource para CRUDs completos.'
$body += Bullet 'Controladores enfocados en coordinar la peticion y respuesta.'
$body += Bullet 'Validaciones en Form Requests dedicadas.'
$body += Bullet 'Relaciones Eloquent entre Employee, Attendance, Shift, VacationRequest y VacationBalance.'
$body += Bullet 'Transacciones en aprobacion de vacaciones para proteger el saldo anual.'
$body += Bullet 'Vistas Blade organizadas por modulo y parciales reutilizables para formularios/modales.'
$body += Bullet 'Datos semilla consistentes para revision y demostracion.'

$body += Paragraph '10. Verificacion' 'Heading1'
$body += Paragraph 'Se ejecutaron validaciones de sintaxis, pruebas automatizadas, cache de vistas y formato de codigo.'
$body += CodeBlock @'
php artisan migrate
php artisan db:seed
php artisan test
php artisan view:cache
php vendor/bin/pint --dirty
'@
$body += Paragraph 'Resultado: las pruebas base pasaron correctamente y el seeder cargo los datos de revision en MySQL.'

$body += Paragraph '11. Conclusiones' 'Heading1'
$body += Paragraph 'El Avance 03 queda implementado con los modulos solicitados para asistencias, vacaciones y turnos. La regla de descuento de vacaciones se implemento de forma persistente y verificable en la base de datos mediante vacation_balances. La solucion conserva la estructura Laravel trabajada durante el curso y se mantiene preparada para seguir creciendo en los siguientes avances.'

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
      <w:pgSz w:w="11906" w:h="16838"/>
      <w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134" w:header="708" w:footer="708" w:gutter="0"/>
    </w:sectPr>
  </w:body>
</w:document>
"@

$stylesXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:style w:type="paragraph" w:default="1" w:styleId="Normal">
    <w:name w:val="Normal"/>
    <w:qFormat/>
    <w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:sz w:val="22"/></w:rPr>
    <w:pPr><w:spacing w:after="120" w:line="276" w:lineRule="auto"/></w:pPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Title">
    <w:name w:val="Title"/>
    <w:basedOn w:val="Normal"/>
    <w:qFormat/>
    <w:pPr><w:spacing w:after="240"/></w:pPr>
    <w:rPr><w:b/><w:color w:val="0E3C67"/><w:sz w:val="40"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Subtitle">
    <w:name w:val="Subtitle"/>
    <w:basedOn w:val="Normal"/>
    <w:qFormat/>
    <w:rPr><w:color w:val="4B5563"/><w:sz w:val="24"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading1">
    <w:name w:val="heading 1"/>
    <w:basedOn w:val="Normal"/>
    <w:qFormat/>
    <w:pPr><w:spacing w:before="360" w:after="160"/></w:pPr>
    <w:rPr><w:b/><w:color w:val="0E3C67"/><w:sz w:val="30"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading2">
    <w:name w:val="heading 2"/>
    <w:basedOn w:val="Normal"/>
    <w:qFormat/>
    <w:pPr><w:spacing w:before="240" w:after="120"/></w:pPr>
    <w:rPr><w:b/><w:color w:val="0B6FA4"/><w:sz w:val="25"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="ListParagraph">
    <w:name w:val="List Paragraph"/>
    <w:basedOn w:val="Normal"/>
    <w:qFormat/>
    <w:pPr><w:ind w:left="360"/></w:pPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Code">
    <w:name w:val="Code"/>
    <w:basedOn w:val="Normal"/>
    <w:qFormat/>
    <w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr>
    <w:rPr><w:rFonts w:ascii="Consolas" w:hAnsi="Consolas"/><w:sz w:val="18"/><w:color w:val="111827"/></w:rPr>
  </w:style>
</w:styles>
'@

$contentTypesXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
'@

$relsXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
'@

$documentRelsXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
'@

$coreXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:dcterms="http://purl.org/dc/terms/"
    xmlns:dcmitype="http://purl.org/dc/dcmitype/"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>Informe Tecnico - Avance 03 RSU</dc:title>
  <dc:subject>Sistema web RSU</dc:subject>
  <dc:creator>Codex</dc:creator>
  <cp:lastModifiedBy>Codex</cp:lastModifiedBy>
</cp:coreProperties>
'@

$appXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"
    xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>Microsoft Office Word</Application>
</Properties>
'@

Set-Content -LiteralPath (Join-Path $tempDir '[Content_Types].xml') -Value $contentTypesXml -Encoding UTF8
Set-Content -LiteralPath (Join-Path $tempDir '_rels\.rels') -Value $relsXml -Encoding UTF8
Set-Content -LiteralPath (Join-Path $tempDir 'docProps\core.xml') -Value $coreXml -Encoding UTF8
Set-Content -LiteralPath (Join-Path $tempDir 'docProps\app.xml') -Value $appXml -Encoding UTF8
Set-Content -LiteralPath (Join-Path $tempDir 'word\document.xml') -Value $documentXml -Encoding UTF8
Set-Content -LiteralPath (Join-Path $tempDir 'word\styles.xml') -Value $stylesXml -Encoding UTF8
Ensure-Directory (Join-Path $tempDir 'word\_rels')
Set-Content -LiteralPath (Join-Path $tempDir 'word\_rels\document.xml.rels') -Value $documentRelsXml -Encoding UTF8

if (Test-Path $outputFile) {
    Remove-Item -LiteralPath $outputFile -Force
}

Add-Type -AssemblyName System.IO.Compression.FileSystem
Add-Type -AssemblyName System.IO.Compression
$zip = [System.IO.Compression.ZipFile]::Open($outputFile, [System.IO.Compression.ZipArchiveMode]::Create)
$entries = @(
    @('[Content_Types].xml', '[Content_Types].xml'),
    @('_rels/.rels', '_rels\.rels'),
    @('docProps/core.xml', 'docProps\core.xml'),
    @('docProps/app.xml', 'docProps\app.xml'),
    @('word/document.xml', 'word\document.xml'),
    @('word/styles.xml', 'word\styles.xml'),
    @('word/_rels/document.xml.rels', 'word\_rels\document.xml.rels')
)

foreach ($entry in $entries) {
    [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
        $zip,
        (Join-Path $tempDir $entry[1]),
        $entry[0],
        [System.IO.Compression.CompressionLevel]::Optimal
    ) | Out-Null
}

$zip.Dispose()
Remove-Item -LiteralPath $tempDir -Recurse -Force

Write-Output $outputFile
