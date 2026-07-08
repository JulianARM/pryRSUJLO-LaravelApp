$ErrorActionPreference = 'Stop'

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..')
$outputDir = Join-Path $projectRoot 'documentacion'
$tempDir = Join-Path $env:TEMP ('rsu_avances_01_04_docx_' + [guid]::NewGuid().ToString('N'))
$outputFile = Join-Path $outputDir 'Informe_Completo_Avances_01_04_RSU.docx'
$createdAt = '09/06/2026'

function Escape-Xml {
    param([AllowNull()][string] $Text)

    if ($null -eq $Text) {
        return ''
    }

    return [System.Security.SecurityElement]::Escape($Text)
}

function Write-Utf8NoBom {
    param(
        [string] $Path,
        [string] $Content
    )

    $encoding = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllText($Path, $Content, $encoding)
}

function Run {
    param(
        [AllowNull()][string] $Text,
        [string] $Style = '',
        [bool] $Bold = $false,
        [string] $Color = '',
        [string] $Size = '',
        [bool] $Italic = $false,
        [bool] $Preserve = $false
    )

    $properties = ''
    if ($Bold) { $properties += '<w:b/>' }
    if ($Italic) { $properties += '<w:i/>' }
    if ($Color) { $properties += "<w:color w:val=`"$Color`"/>" }
    if ($Size) { $properties += "<w:sz w:val=`"$Size`"/><w:szCs w:val=`"$Size`"/>" }
    if ($Style -eq 'Code') { $properties += '<w:rFonts w:ascii="Consolas" w:hAnsi="Consolas"/>' }

    $runProperties = if ($properties) { "<w:rPr>$properties</w:rPr>" } else { '' }
    $space = if ($Preserve) { ' xml:space="preserve"' } else { '' }

    return "<w:r>$runProperties<w:t$space>$(Escape-Xml $Text)</w:t></w:r>"
}

function Paragraph {
    param(
        [AllowNull()][string] $Text = '',
        [string] $Style = '',
        [bool] $Bold = $false,
        [string] $Color = '',
        [string] $Size = '',
        [bool] $Italic = $false
    )

    $paragraphStyle = if ($Style) { "<w:pPr><w:pStyle w:val=`"$Style`"/></w:pPr>" } else { '' }
    return "<w:p>$paragraphStyle$(Run -Text $Text -Style $Style -Bold $Bold -Color $Color -Size $Size -Italic $Italic)</w:p>"
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
    param([object[]] $Rows)

    $xml = @()
    $xml += '<w:tbl>'
    $xml += '<w:tblPr><w:tblW w:w="5000" w:type="pct"/><w:tblBorders><w:top w:val="single" w:sz="6" w:color="D9DEE8"/><w:left w:val="single" w:sz="6" w:color="D9DEE8"/><w:bottom w:val="single" w:sz="6" w:color="D9DEE8"/><w:right w:val="single" w:sz="6" w:color="D9DEE8"/><w:insideH w:val="single" w:sz="6" w:color="D9DEE8"/><w:insideV w:val="single" w:sz="6" w:color="D9DEE8"/></w:tblBorders></w:tblPr>'

    for ($i = 0; $i -lt $Rows.Length; $i++) {
        $xml += '<w:tr>'
        foreach ($cell in $Rows[$i]) {
            $fill = if ($i -eq 0) { '<w:shd w:fill="0E3C67"/>' } else { '' }
            $color = if ($i -eq 0) { 'FFFFFF' } else { '1F2937' }
            $bold = $i -eq 0
            $xml += "<w:tc><w:tcPr><w:tcW w:w=`"2400`" w:type=`"dxa`"/>$fill</w:tcPr><w:p>$(Run -Text ([string] $cell) -Bold $bold -Color $color)</w:p></w:tc>"
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
Ensure-Directory (Join-Path $tempDir 'word\_rels')

$body = @()
$body += Paragraph 'Informe tecnico completo' 'Title'
$body += Paragraph 'Sistema web RSU - Proyecto de reciclaje para la Escuela de Ingenieria de Sistemas y Computacion USAT' 'Subtitle'
$body += Paragraph 'Curso: Topicos Avanzados en Desarrollo de Software'
$body += Paragraph 'Framework y herramientas: Laravel 10, Laravel AdminLTE, JetStream, Laravel Collective, MySQL/XAMPP, AJAX, Leaflet/OpenStreetMap'
$body += Paragraph "Fecha de elaboracion: $createdAt"
$body += Paragraph ''

$body += Paragraph 'Contenido' 'Heading1'
$body += Bullet 'Resumen general del sistema.'
$body += Bullet 'Arquitectura tecnica y buenas practicas aplicadas.'
$body += Bullet 'Detalle funcional por Avance 01, Avance 02, Avance 03 y Avance 04.'
$body += Bullet 'Base de datos, modelos, rutas, controladores, requests, vistas y JavaScript.'
$body += Bullet 'Fragmentos de codigo representativos.'
$body += Bullet 'Pruebas, ejecucion del proyecto y recomendaciones.'

$body += Paragraph '1. Resumen general' 'Heading1'
$body += Paragraph 'El proyecto rsu-proyectofinal implementa una aplicacion web para apoyar la gestion de residuos solidos urbanos vinculada al proyecto de reciclaje del area RSU. El sistema se desarrollo como entregable progresivo del curso, organizando la solucion por modulos funcionales y manteniendo una estructura Laravel clara.'
$body += Paragraph 'La aplicacion permite autenticar usuarios, administrar catalogos vehiculares, registrar vehiculos e imagenes, gestionar personal y contratos, controlar asistencias, solicitudes de vacaciones, turnos y zonas geograficas con perimetros trazados en mapa.'

$body += Paragraph '2. Datos generales del proyecto' 'Heading1'
$body += Table @(
    @('Concepto', 'Detalle'),
    @('Nombre del proyecto', 'rsu-proyectofinal'),
    @('Institucion', 'Universidad Catolica Santo Toribio de Mogrovejo - USAT'),
    @('Escuela', 'Ingenieria de Sistemas y Computacion'),
    @('Curso', 'Topicos Avanzados en Desarrollo de Software'),
    @('Backend', 'Laravel 10'),
    @('Interfaz', 'Laravel AdminLTE, Blade, Bootstrap y JavaScript'),
    @('Autenticacion', 'JetStream'),
    @('Formularios', 'Laravel Collective'),
    @('Base de datos', 'MySQL en XAMPP'),
    @('Mapa', 'Leaflet con OpenStreetMap'),
    @('Usuario de prueba', 'Julian Armas - juliaan.arm@gmail.com - julian123')
)

$body += Paragraph '3. Arquitectura aplicada' 'Heading1'
$body += Paragraph 'La solucion respeta la arquitectura MVC de Laravel. Los modelos representan tablas y relaciones, los controladores coordinan peticiones y respuestas, los Form Requests concentran validaciones y las vistas Blade renderizan la interfaz con la menor logica posible.'
$body += Bullet 'Rutas protegidas con middleware auth.'
$body += Bullet 'CRUDs declarados con Route::resource cuando corresponden.'
$body += Bullet 'Validaciones separadas en Store...Request y Update...Request.'
$body += Bullet 'Respuestas AJAX y tradicionales unificadas mediante el trait RespondsToCrudRequests.'
$body += Bullet 'Componentizacion practica mediante parciales Blade para formularios y modales.'
$body += Bullet 'Operaciones criticas envueltas en transacciones, por ejemplo aprobacion de vacaciones y guardado de coordenadas.'

$body += Paragraph '4. Estructura principal del codigo' 'Heading1'
$body += Table @(
    @('Ruta', 'Proposito'),
    @('app/Models', 'Modelos Eloquent: Vehicle, Employee, Contract, Attendance, VacationRequest, Zone, etc.'),
    @('app/Http/Controllers', 'Controladores por modulo.'),
    @('app/Http/Requests', 'Validaciones de formularios de creacion y actualizacion.'),
    @('resources/views', 'Vistas Blade organizadas por modulo.'),
    @('public/js/rsu-crud.js', 'AJAX comun, formularios, Select2, validaciones dinamicas y flash messages.'),
    @('public/js/rsu-zones.js', 'Logica de mapas, perimetros, coordenadas manuales y visualizacion de zonas.'),
    @('database/migrations', 'Estructura de tablas del sistema.'),
    @('database/seeders', 'Datos iniciales y datos de revision.'),
    @('tests/Feature', 'Pruebas automaticas de reglas clave.')
)

$body += Paragraph '5. Rutas principales' 'Heading1'
$body += Paragraph 'El archivo routes/web.php agrupa todo bajo auth. Esto evita acceso no autenticado a modulos internos y mantiene nombres de rutas coherentes.'
$body += CodeBlock @'
Route::middleware('auth')->group(function () {
    Route::resource('colores', VehicleColorController::class)
        ->parameters(['colores' => 'vehicle_color'])
        ->names('vehicle-colors')
        ->except(['show', 'create', 'edit']);

    Route::resource('vehiculos', VehicleController::class)
        ->parameters(['vehiculos' => 'vehicle'])
        ->names('vehicles')
        ->except(['show', 'create', 'edit']);

    Route::resource('personal', EmployeeController::class)
        ->parameters(['personal' => 'employee'])
        ->names('employees')
        ->except(['show', 'create', 'edit']);

    Route::resource('asistencias', AttendanceController::class)
        ->parameters(['asistencias' => 'attendance'])
        ->names('attendances')
        ->except(['show', 'create', 'edit']);

    Route::post('zonas/{zone}/coordenadas', [ZoneController::class, 'storeCoordinates'])
        ->name('zones.coordinates.store');

    Route::resource('zonas', ZoneController::class)
        ->parameters(['zonas' => 'zone'])
        ->names('zones')
        ->except(['create', 'edit']);
});
'@

$body += Paragraph '6. Trait para respuestas CRUD' 'Heading1'
$body += Paragraph 'Para que los CRUD funcionen tanto con recarga normal como con AJAX se creo un trait reutilizable. Esto evita repetir respuestas JSON o redirects en cada controlador.'
$body += CodeBlock @'
trait RespondsToCrudRequests
{
    protected function successResponse(Request $request, string $route, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route($route)->with('success', $message);
    }

    protected function errorResponse(Request $request, string $route, string $message, int $status = 422): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return redirect()->route($route)->with('error', $message);
    }
}
'@

$body += Paragraph '7. Avance 01 - Base inicial y catalogos vehiculares' 'Heading1'
$body += Paragraph 'El primer avance establecio la aplicacion Laravel con autenticacion, tema AdminLTE, menu lateral, dashboard y catalogos iniciales relacionados con vehiculos.'
$body += Paragraph '7.1 Funcionalidades' 'Heading2'
$body += Bullet 'Instalacion y configuracion de Laravel 10.'
$body += Bullet 'Integracion de JetStream para inicio de sesion.'
$body += Bullet 'Integracion de Laravel AdminLTE para layout administrativo.'
$body += Bullet 'Configuracion de Laravel Collective para formularios Blade.'
$body += Bullet 'Usuario de prueba Julian Armas.'
$body += Bullet 'Dashboard inicial con identidad municipal y resumen visual.'
$body += Bullet 'CRUD AJAX para colores, marcas, modelos y tipos de vehiculo.'
$body += Bullet 'Correccion de mensajes de login al espanol.'
$body += Bullet 'Cambio de SQLite a MySQL/XAMPP.'

$body += Paragraph '7.2 Tablas del Avance 01' 'Heading2'
$body += Table @(
    @('Tabla', 'Descripcion', 'Campos principales'),
    @('users', 'Usuarios autenticados por JetStream.', 'name, email, password'),
    @('vehicle_colors', 'Catalogo de colores.', 'name, code, description'),
    @('brands', 'Catalogo de marcas.', 'name, description, logo'),
    @('brand_models', 'Modelos asociados a marcas.', 'name, code, brand_id, description'),
    @('vehicle_types', 'Tipos de vehiculo.', 'name, description')
)

$body += Paragraph '7.3 Ejemplo de modelo y request' 'Heading2'
$body += CodeBlock @'
class VehicleColor extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
    ];
}

class StoreVehicleColorRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80', 'unique:vehicle_colors,name'],
            'code' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
'@

$body += Paragraph '7.4 AJAX en CRUDs' 'Heading2'
$body += Paragraph 'Los formularios con clase js-ajax-form se envian con fetch. Si la respuesta es correcta, se cierra el modal, se refresca el contenedor del CRUD y se muestra un mensaje visual tipo Bootstrap.'
$body += CodeBlock @'
const response = await fetch(form.action, {
    method: form.method || 'POST',
    body: new FormData(form),
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

await closeOpenModal(form);
await refreshCrudContainer();
showFlash(message, 'success');
'@

$body += Paragraph '8. Avance 02 - Vehiculos, personal y contratos' 'Heading1'
$body += Paragraph 'El segundo avance amplio el sistema con gestion operativa: vehiculos con imagenes, tipos de personal, registro de personal y contratos.'
$body += Paragraph '8.1 Funcionalidades' 'Heading2'
$body += Bullet 'CRUD de vehiculos con marca, modelo, tipo, color, capacidades y estado.'
$body += Bullet 'Gestion de imagenes de vehiculo, carga multiple y seleccion de imagen principal.'
$body += Bullet 'CRUD de tipos de personal.'
$body += Bullet 'CRUD de personal con foto, DNI, nombres, apellidos, fecha de nacimiento, telefono, email, estado, direccion y contrasena.'
$body += Bullet 'Licencia de conducir obligatoria para tipo de personal Conductor.'
$body += Bullet 'La contrasena no se altera al editar si se deja vacia.'
$body += Bullet 'CRUD de contratos con busqueda de personal mediante Select2.'
$body += Bullet 'Regla: un empleado no puede tener dos contratos activos.'
$body += Bullet 'Regla: si el contrato vencio, deben pasar dos meses para recontratar.'

$body += Paragraph '8.2 Tablas del Avance 02' 'Heading2'
$body += Table @(
    @('Tabla', 'Descripcion', 'Campos principales'),
    @('vehicles', 'Vehiculos de recoleccion o apoyo.', 'name, code, plate, year, capacities, brand_id, model_id, type_id, color_id'),
    @('vehicle_images', 'Imagenes asociadas a vehiculos.', 'vehicle_id, image, is_profile'),
    @('staff_types', 'Tipos de personal.', 'name, description'),
    @('employees', 'Personal operativo.', 'dni, names, surnames, birthdate, license, address, email, password, staff_type_id'),
    @('contracts', 'Contratos del personal.', 'employee_id, contract_type, start_date, end_date, salary, position, is_active')
)

$body += Paragraph '8.3 Modelo Employee' 'Heading2'
$body += CodeBlock @'
class Employee extends Model
{
    protected $fillable = [
        'dni', 'names', 'surnames', 'birthdate', 'phone', 'email',
        'password', 'license', 'address', 'status', 'photo', 'staff_type_id',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->names} {$this->surnames}";
    }
}
'@

$body += Paragraph '8.4 Validacion de conductor con licencia' 'Heading2'
$body += CodeBlock @'
$validator->after(function (Validator $validator) {
    $staffType = StaffType::find($this->staff_type_id);

    if ($staffType && str($staffType->name)->lower()->contains('conductor') && blank($this->license)) {
        $validator->errors()->add('license', 'La licencia de conducir es obligatoria para conductores.');
    }
});
'@

$body += Paragraph '8.5 Control de contrasena en edicion' 'Heading2'
$body += Paragraph 'En actualizacion de personal la contrasena solo se cambia cuando el campo llega con valor. Esto evita sobrescribir la clave existente con un valor vacio.'
$body += CodeBlock @'
$data = $request->validated();

if (blank($request->password)) {
    unset($data['password']);
} else {
    $data['password'] = Hash::make($request->password);
}

$employee->update($data);
'@

$body += Paragraph '8.6 Busqueda Select2 para contratos' 'Heading2'
$body += Paragraph 'Para no depender de listas largas, contratos usa un endpoint de busqueda que devuelve resultados compatibles con Select2.'
$body += CodeBlock @'
public function searchEmployees(Request $request): JsonResponse
{
    $term = $request->input('q');

    $employees = Employee::query()
        ->when($term, fn ($query) => $query
            ->where('dni', 'like', "%{$term}%")
            ->orWhere('names', 'like', "%{$term}%")
            ->orWhere('surnames', 'like', "%{$term}%"))
        ->limit(20)
        ->get();

    return response()->json([
        'results' => $employees->map(fn ($employee) => [
            'id' => $employee->id,
            'text' => "{$employee->full_name} - {$employee->dni}",
        ]),
    ]);
}
'@

$body += Paragraph '9. Avance 03 - Asistencias, vacaciones y turnos' 'Heading1'
$body += Paragraph 'El tercer avance completo la gestion operativa del personal. Se implementaron asistencias, vacaciones, saldo anual y turnos.'
$body += Paragraph '9.1 Asistencias' 'Heading2'
$body += Bullet 'Listado con filtros por fecha y busqueda de empleado.'
$body += Bullet 'Registro en modal con empleado, fecha, hora, turno automatico, tipo automatico, estado y notas.'
$body += Bullet 'El turno se calcula segun la hora.'
$body += Bullet 'El tipo se calcula para evitar error humano: primer registro Entrada, siguiente Salida.'
$body += Bullet 'Se evita duplicidad logica por empleado, fecha y tipo.'

$body += Paragraph '9.2 Codigo de asistencia automatica' 'Heading2'
$body += CodeBlock @'
private function typeForEmployeeDate(int $employeeId, string $date, ?Attendance $ignore = null): string
{
    $query = Attendance::where('employee_id', $employeeId)
        ->whereDate('attendance_date', $date);

    if ($ignore) {
        $query->whereKeyNot($ignore->id);
    }

    return $query->count() % 2 === 0
        ? Attendance::TYPE_ENTRY
        : Attendance::TYPE_EXIT;
}

private function shiftForTime(string $time): ?Shift
{
    return Shift::orderBy('start_time')->get()->first(function (Shift $shift) use ($time) {
        if ($shift->start_time <= $shift->end_time) {
            return $time >= $shift->start_time && $time < $shift->end_time;
        }

        return $time >= $shift->start_time || $time < $shift->end_time;
    });
}
'@

$body += Paragraph '9.3 Vacaciones' 'Heading2'
$body += Bullet 'Solo personal activo con contrato permanente o nombrado puede solicitar vacaciones.'
$body += Bullet 'El formulario muestra dias disponibles del personal seleccionado.'
$body += Bullet 'No permite registrar una solicitud si los dias solicitados superan el saldo.'
$body += Bullet 'No permite solicitudes cruzadas con otras pendientes o aprobadas.'
$body += Bullet 'Al aprobar se descuenta el saldo anual.'
$body += Bullet 'Al eliminar una solicitud aprobada se restauran los dias descontados.'
$body += Bullet 'La aprobacion usa confirmacion visual dentro de la interfaz, no alert nativo del navegador.'

$body += Paragraph '9.4 Tablas del Avance 03' 'Heading2'
$body += Table @(
    @('Tabla', 'Descripcion', 'Campos principales'),
    @('shifts', 'Turnos de trabajo.', 'name, start_time, end_time, description'),
    @('attendances', 'Registros de asistencia.', 'employee_id, shift_id, attendance_date, attendance_time, type, status, notes'),
    @('vacation_requests', 'Solicitudes de vacaciones.', 'employee_id, vacation_balance_id, requested_at, start_date, end_date, days_requested, remaining_days, status'),
    @('vacation_balances', 'Saldo anual de vacaciones.', 'employee_id, year, total_days, used_days, available_days')
)

$body += Paragraph '9.5 Descuento de vacaciones al aprobar' 'Heading2'
$body += CodeBlock @'
$message = DB::transaction(function () use ($vacation) {
    $balance = VacationBalance::where('employee_id', $vacation->employee_id)
        ->where('year', $vacation->start_date->year)
        ->lockForUpdate()
        ->firstOrCreate([
            'employee_id' => $vacation->employee_id,
            'year' => $vacation->start_date->year,
        ], [
            'total_days' => VacationBalance::DEFAULT_ANNUAL_DAYS,
            'used_days' => 0,
            'available_days' => VacationBalance::DEFAULT_ANNUAL_DAYS,
        ]);

    if (! $balance->canUse($vacation->days_requested)) {
        return 'No se puede aprobar. El personal no tiene dias suficientes.';
    }

    $balance->discount($vacation->days_requested);

    $vacation->update([
        'vacation_balance_id' => $balance->id,
        'remaining_days' => $balance->available_days,
        'status' => VacationRequest::STATUS_APPROVED,
    ]);
});
'@

$body += Paragraph '9.6 Modelo VacationBalance' 'Heading2'
$body += CodeBlock @'
class VacationBalance extends Model
{
    public const DEFAULT_ANNUAL_DAYS = 30;

    protected $fillable = [
        'employee_id', 'year', 'total_days', 'used_days', 'available_days',
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

    public function restoreDays(int $days): void
    {
        $used = max(0, $this->used_days - $days);

        $this->forceFill([
            'used_days' => $used,
            'available_days' => max(0, $this->total_days - $used),
        ])->save();
    }
}
'@

$body += Paragraph '10. Avance 04 - Zonas, coordenadas y mapas' 'Heading1'
$body += Paragraph 'El cuarto avance implemento el modulo de zonas. Permite registrar datos de una zona, trazar su perimetro en un mapa, registrar coordenadas manualmente, arrastrar puntos, visualizar zonas creadas y consultar todas las zonas en un mapa general.'
$body += Paragraph '10.1 Funcionalidades' 'Heading2'
$body += Bullet 'CRUD de zonas con departamento, provincia, distrito, descripcion, residuos promedio y estado.'
$body += Bullet 'Listado de zonas con botones para visualizar mapa, editar perimetro, editar datos y eliminar.'
$body += Bullet 'Vista Perimetro de la Zona para trazar puntos en el mapa.'
$body += Bullet 'Registro manual de latitud y longitud.'
$body += Bullet 'Arrastre de puntos del perimetro para corregir coordenadas.'
$body += Bullet 'Visualizacion de otras zonas como referencia con color visible y borde diferenciado.'
$body += Bullet 'Boton Mapa de Zonas para visualizar todas las zonas.'
$body += Bullet 'Seleccion directa de zonas desde el mapa: al hacer clic se carga la informacion lateral.'
$body += Bullet 'Selector para cambiar entre Todas las zonas y una zona especifica.'

$body += Paragraph '10.2 Tablas del Avance 04' 'Heading2'
$body += Table @(
    @('Tabla', 'Descripcion', 'Campos principales'),
    @('zones', 'Datos generales de zonas.', 'name, department, province, district, description, average_waste_kg, is_active'),
    @('zone_coordinates', 'Puntos del poligono de una zona.', 'zone_id, latitude, longitude, sort_order')
)

$body += Paragraph '10.3 Modelo Zone' 'Heading2'
$body += CodeBlock @'
class Zone extends Model
{
    protected $fillable = [
        'name', 'department', 'province', 'district',
        'description', 'average_waste_kg', 'is_active',
    ];

    protected $casts = [
        'average_waste_kg' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function coordinates(): HasMany
    {
        return $this->hasMany(ZoneCoordinate::class)->orderBy('sort_order');
    }

    public function getLocationLabelAttribute(): string
    {
        return "{$this->department} / {$this->province} / {$this->district}";
    }
}
'@

$body += Paragraph '10.4 Validacion de coordenadas' 'Heading2'
$body += CodeBlock @'
public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $validator) {
        $coordinates = $this->coordinates();

        if (count($coordinates) < 3) {
            $validator->errors()->add('coordinates', 'Debe registrar al menos 3 coordenadas.');
            return;
        }

        foreach ($coordinates as $coordinate) {
            if (! isset($coordinate['lat'], $coordinate['lng'])
                || ! is_numeric($coordinate['lat'])
                || ! is_numeric($coordinate['lng'])
                || $coordinate['lat'] < -90
                || $coordinate['lat'] > 90
                || $coordinate['lng'] < -180
                || $coordinate['lng'] > 180) {
                $validator->errors()->add('coordinates', 'Las coordenadas ingresadas no son validas.');
                return;
            }
        }
    });
}
'@

$body += Paragraph '10.5 Guardado transaccional del perimetro' 'Heading2'
$body += CodeBlock @'
public function storeCoordinates(StoreZoneCoordinatesRequest $request, Zone $zone)
{
    DB::transaction(function () use ($request, $zone) {
        $zone->coordinates()->delete();

        foreach ($request->coordinates() as $index => $coordinate) {
            $zone->coordinates()->create([
                'latitude' => $coordinate['lat'],
                'longitude' => $coordinate['lng'],
                'sort_order' => $index + 1,
            ]);
        }
    });

    return response()->json([
        'message' => 'Perimetro de la zona actualizado correctamente.',
    ]);
}
'@

$body += Paragraph '10.6 JavaScript de perimetro' 'Heading2'
$body += Paragraph 'El archivo public/js/rsu-zones.js contiene la logica de Leaflet. El mapa permite agregar puntos con clic, agregarlos manualmente, quitar puntos, limpiar, usar ubicacion y arrastrar marcadores.'
$body += CodeBlock @'
window.L.marker([coordinate.lat, coordinate.lng], {
    draggable: true,
    title: `Punto ${index + 1}`,
}).addTo(currentLayer).bindTooltip(`Punto ${index + 1}`, {
    permanent: true,
    direction: 'top',
}).on('dragend', (event) => {
    const latLng = event.target.getLatLng();

    coordinates[index] = {
        lat: Number(latLng.lat.toFixed(7)),
        lng: Number(latLng.lng.toFixed(7)),
    };

    render({ fitBounds: false });
});
'@

$body += Paragraph '10.7 Visualizacion de todas las zonas' 'Heading2'
$body += Paragraph 'El boton Mapa de Zonas abre un modal con todas las zonas. El usuario puede hacer clic en un poligono para cargar su informacion; el mapa mantiene visibles las demas zonas para navegar visualmente.'
$body += CodeBlock @'
polygon.on('click', () => selectOverviewZone(item.id));

function selectOverviewZone(zoneId) {
    element.dataset.selectedZoneId = String(zoneId);

    if (select) {
        select.value = String(zoneId);
    }

    renderSelectedZone();
}
'@

$body += Paragraph '10.8 Por que Leaflet/OpenStreetMap' 'Heading2'
$body += Paragraph 'Se eligio Leaflet con OpenStreetMap porque no exige API Key, facturacion ni configuracion de Google Cloud. Para el avance academico permite registrar y visualizar poligonos con coordenadas reales sin depender de credenciales externas. El mapa base sirve como referencia; la informacion critica del sistema son las coordenadas guardadas en MySQL.'
$body += Paragraph 'Si el proyecto requiere mas adelante busqueda avanzada de direcciones, rutas comerciales, trafico o vista satelital especifica, podria evaluarse Google Maps. Para el alcance actual, OpenStreetMap reduce friccion y facilita la revision del docente.'

$body += Paragraph '11. Base de datos consolidada' 'Heading1'
$body += Table @(
    @('Modulo', 'Tablas'),
    @('Autenticacion', 'users, sessions, password_reset_tokens, personal_access_tokens'),
    @('Catalogos vehiculares', 'vehicle_colors, brands, brand_models, vehicle_types'),
    @('Vehiculos', 'vehicles, vehicle_images'),
    @('Personal', 'staff_types, employees, contracts'),
    @('Asistencias', 'shifts, attendances'),
    @('Vacaciones', 'vacation_requests, vacation_balances'),
    @('Zonas', 'zones, zone_coordinates')
)

$body += Paragraph '12. Relaciones principales' 'Heading1'
$body += Table @(
    @('Relacion', 'Descripcion'),
    @('Brand hasMany BrandModel', 'Una marca puede tener varios modelos.'),
    @('Vehicle belongsTo Brand, BrandModel, VehicleType, VehicleColor', 'El vehiculo se clasifica por catalogos.'),
    @('Vehicle hasMany VehicleImage', 'Un vehiculo puede tener varias imagenes.'),
    @('StaffType hasMany Employee', 'Un tipo de personal agrupa empleados.'),
    @('Employee hasMany Contract', 'Un empleado puede tener historial de contratos.'),
    @('Employee hasMany Attendance', 'Un empleado tiene registros de asistencia.'),
    @('Shift hasMany Attendance', 'Un turno agrupa asistencias por horario.'),
    @('Employee hasMany VacationRequest y VacationBalance', 'Vacaciones y saldo anual se vinculan al personal.'),
    @('Zone hasMany ZoneCoordinate', 'Una zona esta compuesta por puntos ordenados.')
)

$body += Paragraph '13. Interfaz y experiencia de usuario' 'Heading1'
$body += Bullet 'Layout administrativo con AdminLTE y menu lateral por modulos.'
$body += Bullet 'Modales para altas y ediciones, evitando cambios de pagina innecesarios.'
$body += Bullet 'Mensajes visuales tipo Bootstrap para exito, error y confirmaciones.'
$body += Bullet 'Select2 para busquedas de personal en contratos, asistencias y vacaciones.'
$body += Bullet 'Color picker corregido para mostrar el color seleccionado.'
$body += Bullet 'Tablas con acciones compactas: editar, eliminar, imagenes, mapa o perimetro.'
$body += Bullet 'Mapa interactivo para trazado y consulta de zonas.'

$body += Paragraph '14. Codigo AJAX comun' 'Heading1'
$body += Paragraph 'El archivo public/js/rsu-crud.js centraliza el envio AJAX, validaciones dinamicas, Select2, refresco del contenedor y mensajes.'
$body += CodeBlock @'
document.addEventListener('submit', async function (event) {
    const form = event.target.closest('.js-ajax-form');

    if (!form) {
        return;
    }

    event.preventDefault();

    if (form.dataset.confirm && form.dataset.confirmed !== 'true') {
        showInlineConfirm(form, form.dataset.confirm);
        return;
    }

    const response = await fetch(form.action, {
        method: form.method || 'POST',
        body: new FormData(form),
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
});
'@

$body += Paragraph '15. Confirmaciones visuales' 'Heading1'
$body += Paragraph 'Se reemplazo window.confirm por confirmaciones internas dentro del area js-flash-messages. Esto mejora consistencia visual y evita cuadros nativos del navegador.'
$body += CodeBlock @'
function showInlineConfirm(form, message) {
    const flash = document.querySelector('.js-flash-messages');
    pendingConfirmForm = form;

    flash.innerHTML = `
        <div class="alert alert-warning fade show js-inline-confirm" role="alert">
            ${escapeHtml(message)}
            <button type="button" class="btn btn-sm btn-outline-secondary js-confirm-cancel">No</button>
            <button type="button" class="btn btn-sm btn-success js-confirm-accept">Si, continuar</button>
        </div>
    `;
}
'@

$body += Paragraph '16. Pruebas automatizadas' 'Heading1'
$body += Table @(
    @('Prueba', 'Proposito'),
    @('AttendanceAutomationTest', 'Verifica asignacion automatica del tipo de asistencia.'),
    @('EmployeeSearchTest', 'Verifica busqueda de empleados para Select2.'),
    @('VacationApprovalTest', 'Verifica descuento y restauracion de dias de vacaciones.'),
    @('VacationIndexTest', 'Verifica columna de dias restantes.'),
    @('ZoneModuleTest', 'Verifica acceso a zonas y guardado de coordenadas.')
)
$body += Paragraph 'Ultima verificacion ejecutada durante el desarrollo: 11 pruebas pasaron con 56 aserciones.'
$body += CodeBlock @'
php artisan test
php artisan view:cache
php vendor/bin/pint --dirty
node --check public/js/rsu-crud.js
node --check public/js/rsu-zones.js
'@

$body += Paragraph '17. Ejecucion del proyecto' 'Heading1'
$body += Paragraph 'Para ejecutar el proyecto en una maquina local con XAMPP:'
$body += CodeBlock @'
cd "E:\USAT\2026-I\Topicos Avanzados\Proyecto Final RSU\RSU VS\rsu-proyectofinal"

C:\xampp\php\php.exe artisan migrate --seed
C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000
'@
$body += Paragraph 'URL de acceso: http://127.0.0.1:8000/login'
$body += Paragraph 'Credenciales: juliaan.arm@gmail.com / julian123'

$body += Paragraph '18. Configuracion de base de datos' 'Heading1'
$body += Paragraph 'El sistema trabaja con MySQL de XAMPP. La base puede visualizarse en phpMyAdmin o por consola con mysql.exe.'
$body += CodeBlock @'
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rsu_proyectofinal
DB_USERNAME=root
DB_PASSWORD=
'@

$body += Paragraph '19. Buenas practicas destacadas' 'Heading1'
$body += Bullet 'Controladores pequenos y orientados a coordinar flujo.'
$body += Bullet 'Validaciones fuera del controlador mediante Form Requests.'
$body += Bullet 'Uso de Eloquent y relaciones en lugar de consultas sueltas repetidas.'
$body += Bullet 'Transacciones para operaciones sensibles.'
$body += Bullet 'Vistas Blade separadas por modulo y parciales.'
$body += Bullet 'JavaScript modular para comportamiento comun y mapas.'
$body += Bullet 'Nombres de rutas claros con names y parameters.'
$body += Bullet 'Pruebas Feature para reglas de negocio criticas.'

$body += Paragraph '20. Estado actual por avance' 'Heading1'
$body += Table @(
    @('Avance', 'Estado', 'Resumen'),
    @('Avance 01', 'Completado', 'Base Laravel, autenticacion, AdminLTE, dashboard y catalogos vehiculares.'),
    @('Avance 02', 'Completado', 'Vehiculos, imagenes, tipos de personal, personal y contratos.'),
    @('Avance 03', 'Completado', 'Asistencias automaticas, vacaciones con saldo, turnos y pruebas.'),
    @('Avance 04', 'Completado', 'Zonas, coordenadas, perimetros, mapa general y visualizacion interactiva.')
)

$body += Paragraph '21. Recomendaciones para siguientes avances' 'Heading1'
$body += Bullet 'Mantener Form Requests por cada nuevo formulario importante.'
$body += Bullet 'Agregar pruebas cuando una regla de negocio afecte contratos, vacaciones, rutas o zonas.'
$body += Bullet 'Evitar mezclar logica de negocio en Blade.'
$body += Bullet 'Si crece la logica de mapas o rutas, extraer servicios dedicados.'
$body += Bullet 'Evaluar Google Maps solo si se requieren busqueda de direcciones, rutas con trafico o servicios especificos de Google.'
$body += Bullet 'Agregar roles y permisos si el sistema debe separar administradores, operadores y supervisores.'

$body += Paragraph '22. Conclusion' 'Heading1'
$body += Paragraph 'Hasta el Avance 04 el proyecto cuenta con una base funcional y ordenada para la gestion del sistema RSU. La aplicacion cubre catalogos, vehiculos, personal, contratos, asistencias, vacaciones, turnos y zonas geograficas. La implementacion sigue buenas practicas de Laravel, usa MySQL, mantiene una interfaz consistente con AdminLTE y cuenta con pruebas para reglas criticas.'

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
    <w:rPr><w:b/><w:color w:val="0E3C67"/><w:sz w:val="42"/></w:rPr>
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

$coreXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:dcterms="http://purl.org/dc/terms/"
    xmlns:dcmitype="http://purl.org/dc/dcmitype/"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>Informe Completo Avances 01 al 04 RSU</dc:title>
  <dc:subject>Sistema web RSU Laravel</dc:subject>
  <dc:creator>Codex</dc:creator>
  <cp:lastModifiedBy>Codex</cp:lastModifiedBy>
</cp:coreProperties>
"@

$appXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"
    xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>Microsoft Office Word</Application>
</Properties>
'@

Write-Utf8NoBom -Path (Join-Path $tempDir '[Content_Types].xml') -Content $contentTypesXml
Write-Utf8NoBom -Path (Join-Path $tempDir '_rels\.rels') -Content $relsXml
Write-Utf8NoBom -Path (Join-Path $tempDir 'docProps\core.xml') -Content $coreXml
Write-Utf8NoBom -Path (Join-Path $tempDir 'docProps\app.xml') -Content $appXml
Write-Utf8NoBom -Path (Join-Path $tempDir 'word\document.xml') -Content $documentXml
Write-Utf8NoBom -Path (Join-Path $tempDir 'word\styles.xml') -Content $stylesXml
Write-Utf8NoBom -Path (Join-Path $tempDir 'word\_rels\document.xml.rels') -Content $documentRelsXml

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
