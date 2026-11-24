<?php

use Illuminate\Database\Seeder;
use App\Salud\SaludArchivo;
use App\Salud\SaludEstablecimiento;
use App\Salud\SaludTelefono;
use App\Salud\SaludHorario;
use App\Salud\SaludServicio;
use App\Salud\SaludSeccionDetalle;
use App\Salud\SaludCronogramaFila;

/**
 * Seed idempotente de los 6 establecimientos de salud del Municipio de Llallagua.
 * Fuente: directorio-salud-llallagua.html (const C y const DT), gestión 2026.
 *
 * Campos marcados PENDIENTE quedan null/vacíos según indicado en el prompt.
 * Ejecutar: php artisan db:seed --class=SaludEstablecimientosSeeder
 */
class SaludEstablecimientosSeeder extends Seeder
{
    public function run()
    {
        $fotosPorSlug = [
            'hospital-madre-obrera' => [
                'clave'           => 'server/salud/archivos/2e2QWHpfCmvfQ9Pev14A6bP7CZweOh01z4TcR8AX.png',
                'nombre_original' => 'segundonivel.png',
                'mime'            => 'image/png',
                'tamano'          => 263122,
                'ancho'           => 611,
                'alto'            => 200,
            ],
            'csa-madre-obrera' => [
                'clave'           => 'server/salud/archivos/m0HKiFJ8YQRmQfSjbnGmfBEeh4s1wF0lqKElF6Jr.jpg',
                'nombre_original' => 'centro.jpg',
                'mime'            => 'image/jpeg',
                'tamano'          => 232204,
                'ancho'           => 1280,
                'alto'            => 960,
            ],
            'csa-sakamarka' => [
                'clave'           => 'server/salud/archivos/KU5vU0zw6cKMV3zQHZy9xZrEmsWbE7Xw4ElfjAVu.jpg',
                'nombre_original' => 'fachada-csa-sakamarka.jpg',
                'mime'            => 'image/jpeg',
                'tamano'          => 226585,
                'ancho'           => 1200,
                'alto'            => 1600,
            ],
            'csa-catavi' => [
                'clave'           => 'server/salud/archivos/wktK7co56I0eA0nWrMjBcol5Q7LJzSirgU5blrGq.jpg',
                'nombre_original' => 'fachada-csa-catavi.jpg',
                'mime'            => 'image/jpeg',
                'tamano'          => 97987,
                'ancho'           => 681,
                'alto'            => 306,
            ],
            'csa-siglo-xx' => [
                'clave'           => 'server/salud/archivos/nBBxh3sU6I4UU63xoqvkEZiDxx9egmh0pHqzFi8Z.jpg',
                'nombre_original' => 'fachada-csa-siglo-xx.jpg',
                'mime'            => 'image/jpeg',
                'tamano'          => 209840,
                'ancho'           => 1600,
                'alto'            => 1204,
            ],
            'centro-rehabilitacion-llallagua' => [
                'clave'           => 'server/salud/archivos/W6Sh8BfbsgK2LMhZDkKz4CRql6YVncWUIyO72mDD.png',
                'nombre_original' => 'rehabilitacion.png',
                'mime'            => 'image/png',
                'tamano'          => 2514162,
                'ancho'           => 1350,
                'alto'            => 1032,
            ],
        ];

        $establecimientos = $this->datos();

        foreach ($establecimientos as $data) {
            $fotoId = null;
            if (isset($fotosPorSlug[$data['slug']])) {
                $fMeta = $fotosPorSlug[$data['slug']];
                $archivo = SaludArchivo::firstOrCreate(
                    ['clave' => $fMeta['clave']],
                    $fMeta
                );
                $fotoId = $archivo->id;
            }

            // ── Establecimiento ──────────────────────────────────────
            $atributos = [
                'nombre'       => $data['nombre'],
                'tipo'         => $data['tipo'],
                'nivel'        => $data['nivel'] ?? null,
                'zona'         => $data['zona'],
                'direccion'    => $data['direccion'],
                'latitud'      => $data['latitud'] ?? null,
                'longitud'     => $data['longitud'] ?? null,
                'responsable'  => $data['responsable'],
                'descripcion'  => $data['descripcion'] ?? null,
                'atiende_24h'  => $data['atiende_24h'] ?? false,
                'activo'       => true,
            ];
            if ($fotoId) {
                $atributos['foto_fachada_id'] = $fotoId;
            }

            $est = SaludEstablecimiento::updateOrCreate(
                ['slug' => $data['slug']],
                $atributos
            );

            // ── Teléfonos ────────────────────────────────────────────
            $est->telefonos()->delete();
            foreach ($data['telefonos'] ?? [] as $i => $tel) {
                SaludTelefono::create([
                    'establecimiento_id' => $est->id,
                    'numero'  => $tel['numero'],
                    'etiqueta'=> $tel['etiqueta'] ?? 'fijo',
                    'orden'   => $i,
                ]);
            }

            // ── Horarios ─────────────────────────────────────────────
            $est->horarios()->delete();
            foreach ($data['horarios'] ?? [] as $h) {
                SaludHorario::create([
                    'establecimiento_id' => $est->id,
                    'dias'       => $h['dias'],
                    'hora_inicio'=> $h['inicio'],
                    'hora_fin'   => $h['fin'],
                    'nota'       => $h['nota'] ?? null,
                ]);
            }

            // ── Servicios ────────────────────────────────────────────
            $est->servicios()->delete();
            foreach ($data['servicios'] ?? [] as $i => $sv) {
                SaludServicio::create([
                    'establecimiento_id' => $est->id,
                    'nombre'      => $sv['nombre'],
                    'categoria'   => $sv['categoria'] ?? 'otro',
                    'descripcion' => $sv['descripcion'] ?? null,
                    'orden'       => $i,
                ]);
            }

            // ── Secciones de detalle ─────────────────────────────────
            $est->secciones()->delete();
            foreach ($data['secciones'] ?? [] as $i => $sec) {
                SaludSeccionDetalle::create([
                    'establecimiento_id' => $est->id,
                    'titulo' => $sec['titulo'],
                    'texto'  => $sec['texto'] ?? null,
                    'items'  => $sec['items'] ?? null,
                    'orden'  => $i,
                ]);
            }

            // ── Cronograma en tabla (filas) ───────────────────────────
            $est->cronogramasFilas()->delete();
            foreach ($data['cronograma_filas'] ?? [] as $i => $fila) {
                SaludCronogramaFila::create([
                    'establecimiento_id' => $est->id,
                    'dia_o_fecha'  => $fila[0] ?? null,
                    'servicio'     => $fila[1] ?? null,
                    'profesional'  => $fila[2] ?? null,
                    'horario'      => $fila[3] ?? null,
                    'observaciones'=> $fila[4] ?? null,
                    'orden'        => $i,
                ]);
            }
        }
    }

    // ════════════════════════════════════════════════════════════════
    // DATOS INICIALES
    // ════════════════════════════════════════════════════════════════

    private function datos(): array
    {
        // Días de la semana: 1=Lun, 2=Mar, 3=Mié, 4=Jue, 5=Vie, 6=Sáb, 7=Dom
        $lv = [1, 2, 3, 4, 5]; // lunes a viernes

        return [

            // ────────────────────────────────────────────────────────
            // 1. Hospital de Segundo Nivel "Madre Obrera"
            // ────────────────────────────────────────────────────────
            [
                'slug'        => 'hospital-madre-obrera',
                'nombre'      => 'Hospital de Segundo Nivel "Madre Obrera"',
                'tipo'        => 'hospital',
                'nivel'       => '2.º nivel',
                'zona'        => 'Viluyo',
                'direccion'   => 'Carretera Llallagua–Uncía (diagonal Jaime Mendoza), zona Viluyo',
                'latitud'     => null, // PENDIENTE: confirmar coordenadas precisas
                'longitud'    => null,
                'responsable' => 'Dr. David Richard Velasco Coca, Director Ejecutivo',
                'atiende_24h' => true,
                'telefonos'   => [
                    ['numero' => '(02) 5024-4271', 'etiqueta' => 'fijo'],
                ],
                // PENDIENTE: Días de atención de consulta (solo Emergencias confirmadas 24h/7d)
                'horarios' => [
                    ['dias' => $lv, 'inicio' => '08:30', 'fin' => '12:30', 'nota' => 'Consulta mañana'],
                    ['dias' => $lv, 'inicio' => '14:30', 'fin' => '18:30', 'nota' => 'Consulta tarde'],
                ],
                'servicios' => [
                    ['nombre' => 'Emergencias y urgencias',          'categoria' => 'emergencias'],
                    ['nombre' => 'Laboratorio',                       'categoria' => 'laboratorio'],
                    ['nombre' => 'Medicina interna',                  'categoria' => 'medicina_general'],
                    ['nombre' => 'Pediatría y neonatología',          'categoria' => 'especialidades'],
                    ['nombre' => 'Traumatología y ortopedia',         'categoria' => 'especialidades'],
                    ['nombre' => 'Cirugía general',                   'categoria' => 'especialidades'],
                    ['nombre' => 'Ginecología y obstetricia',         'categoria' => 'especialidades'],
                    ['nombre' => 'Terapia intensiva',                 'categoria' => 'especialidades'],
                    ['nombre' => 'Gastroenterología',                 'categoria' => 'especialidades'],
                    ['nombre' => 'Neumología',                        'categoria' => 'especialidades'],
                    ['nombre' => 'Nutrición',                         'categoria' => 'especialidades'],
                    ['nombre' => 'Odontología',                       'categoria' => 'odontologia'],
                    ['nombre' => 'Anestesiología',                    'categoria' => 'especialidades'],
                    ['nombre' => 'Fisioterapia y kinesiología',       'categoria' => 'fisioterapia'],
                    ['nombre' => 'Imagenología – ecografía',          'categoria' => 'imagenologia'],
                    ['nombre' => 'Imagenología – rayos X',            'categoria' => 'imagenologia'],
                    ['nombre' => 'Enfermería',                        'categoria' => 'enfermeria'],
                    ['nombre' => 'Telesalud',                         'categoria' => 'telesalud'],
                ],
                'secciones' => [
                    [
                        'titulo' => 'Médicos por especialidad',
                        'items'  => [
                            'Ginecología: Acha, Salazar, Ramirez, Cussi, Angulo, Pérez, Sánchez, Araoz',
                            'Pediatría: Suárez, Tarqui, Mamani, Apaz, Andia, Tola',
                            'Traumatología: Alas, Veramendi, Zepita',
                            'Anestesiología: Tinta, Virhuez, Arce, Calustro, Ortuño, Zenteno',
                            'Cirugía general: Iriarte, Gambarte, Chile, Cordero, Quispe, Ugarte',
                            'Medicina interna: Flores, Jiménez, Callisaya, Conde, Torrico, Denver',
                            'Gastroenterología: Dra. Quiroga',
                            'Emergencias (médicos de turno): Gallardo, Moya, Mena, Aguilar, Cepeda, Cruz, Velásquez, Serrudo',
                        ],
                    ],
                    [
                        'titulo' => 'Requisitos para la atención',
                        'items'  => [
                            'Particulares: solo carnet de identidad',
                        ],
                    ],
                    [
                        'titulo' => 'Afiliación al SUS',
                        'items'  => [
                            'Acudir primero al centro de salud de primer nivel más cercano a su domicilio (puerta de entrada al SUS)',
                            'Boleta de referencia del centro de salud, boleta de reconsulta o epicrisis',
                        ],
                    ],
                ],
            ],

            // ────────────────────────────────────────────────────────
            // 2. CSA Madre Obrera
            // ────────────────────────────────────────────────────────
            [
                'slug'        => 'csa-madre-obrera',
                'nombre'      => 'Centro de Salud Ambulatorio Madre Obrera',
                'tipo'        => 'centro_salud',
                'nivel'       => '1.er nivel',
                'zona'        => 'Zona 2, Plaza de Armas',
                'direccion'   => 'Av. 10 de Noviembre, Zona 2 (Plaza de Armas), a 1 km de la ciudad',
                'latitud'     => -18.4232015,
                'longitud'    => -66.5867663,
                'responsable' => 'Dra. Jenny Espejo Flores',
                'atiende_24h' => false,
                'telefonos'   => [], // PENDIENTE: sin teléfono institucional registrado
                // PENDIENTE: Días de atención no confirmados
                'horarios' => [
                    ['dias' => $lv, 'inicio' => '08:00', 'fin' => '20:00', 'nota' => null],
                ],
                'servicios' => [
                    ['nombre' => 'Programa de Tuberculosis',         'categoria' => 'programas'],
                    ['nombre' => 'Programa de la Mujer',             'categoria' => 'programas'],
                    ['nombre' => 'ITS / VIH-SIDA',                  'categoria' => 'programas'],
                    ['nombre' => 'Bono Juana Azurduy',               'categoria' => 'programas'],
                    ['nombre' => 'PAI (vacunas)',                    'categoria' => 'vacunacion'],
                    ['nombre' => 'Crecimiento y desarrollo (CCD)',   'categoria' => 'programas'],
                    ['nombre' => 'DIT',                              'categoria' => 'programas'],
                    ['nombre' => 'Lactancia materna',                'categoria' => 'programas'],
                    ['nombre' => 'Zoonosis',                         'categoria' => 'programas'],
                    ['nombre' => 'Adolescentes',                     'categoria' => 'programas'],
                    ['nombre' => 'Educación para la vida',           'categoria' => 'programas'],
                    ['nombre' => 'Escuelas saludables',              'categoria' => 'programas'],
                    ['nombre' => 'Triaje y emergencias',             'categoria' => 'emergencias'],
                ],
                'secciones' => [
                    [
                        'titulo' => 'Requisitos para la atención',
                        'items'  => ['Carnet de identidad y/o carnet del SUS'],
                    ],
                    [
                        'titulo' => 'Afiliación al SUS',
                        'items'  => [
                            'No tener otro seguro de salud (CNS, Caja Petrolera, etc.)',
                            'Cédula de identidad',
                        ],
                    ],
                ],
            ],

            // ────────────────────────────────────────────────────────
            // 3. CSA Sakamarka
            // ────────────────────────────────────────────────────────
            [
                'slug'        => 'csa-sakamarka',
                'nombre'      => 'Centro de Salud Ambulatorio Sakamarka',
                'tipo'        => 'centro_salud',
                'nivel'       => '1.er nivel',
                'zona'        => 'Distrito de Sakamarka, Zona 11',
                'direccion'   => 'Zona 11, calle 2 de Agosto, entre Av. Ejército y San Jorge',
                'latitud'     => -18.4267793,
                'longitud'    => -66.5680393,
                'responsable' => 'Dr. Elmer Oporto Cordel',
                'atiende_24h' => false,
                'telefonos'   => [],
                'horarios'    => [
                    ['dias' => $lv, 'inicio' => '08:30', 'fin' => '12:30', 'nota' => 'Horario habitual (mañana)'],
                    ['dias' => $lv, 'inicio' => '14:30', 'fin' => '18:30', 'nota' => 'Horario habitual (tarde)'],
                    ['dias' => $lv, 'inicio' => '08:30', 'fin' => '16:30', 'nota' => 'Horario continuo con instructiva'],
                ],
                'servicios' => [
                    ['nombre' => 'Medicina general',                              'categoria' => 'medicina_general'],
                    ['nombre' => 'Odontología general',                           'categoria' => 'odontologia'],
                    ['nombre' => 'Farmacia',                                      'categoria' => 'farmacia'],
                    ['nombre' => 'Aplicación de vacunas',                         'categoria' => 'vacunacion'],
                    ['nombre' => 'Aplicación de sueros, inyectables y curaciones','categoria' => 'enfermeria'],
                ],
                'secciones' => [
                    [
                        'titulo' => 'Días y horario de atención por servicio',
                        'items'  => [
                            'Consultorio médico 2, odontología, farmacia y enfermería: lunes a viernes de 08:30 a 12:30 (turno mañana) y de 14:30 a 16:30 (turno tarde).',
                            'Consultorio médico 1: lunes a viernes de 09:00 a 12:00 (mañana) y turno tarde desde las 14:30.',
                        ],
                    ],
                    [
                        'titulo' => 'Requisitos para recibir la atención',
                        'items'  => [
                            'Cédula de identidad y estar registrado en el SUS (el registro debe pertenecer al municipio de Llallagua).',
                            'Menores de 4 meses: portar únicamente su cartón de recién nacido.',
                            'Pacientes con otro tipo de seguro: portar cédula de identidad y realizar la cancelación del servicio.',
                            'Por emergencia y por única vez se puede atender a pacientes con SUS de otro municipio.',
                        ],
                    ],
                    [
                        'titulo' => 'Afiliación al SUS y programas de salud',
                        'items'  => [
                            'Cédula de identidad vigente.',
                            'No contar con otro tipo de seguro de salud (Caja Nacional de Salud, Caja Bancaria, Seguro Universitario, etc.).',
                        ],
                    ],
                    [
                        'titulo' => 'Actividades y campañas de salud',
                        'texto'  => 'Las actividades y campañas fuera del establecimiento dependen de las instructivas de la Red de Salud Municipal SAFCI – Llallagua, SEDES Potosí, Red de Salud Uncía y el planificador anual.',
                        'items'  => [
                            'Población dirigida: toda la población en general y grupos específicos según el programa a cargo (ej. Programa de Adolescentes, Programa de Lactancia Materna, etc.).',
                            'Requisitos de participación: sin requisitos, solo asistir a la actividad (en unidades educativas participan estudiantes, plantel docente y, con autorización de la dirección, padres de familia).',
                            'Responsables: los responsables municipales de cada programa y, en ferias de salud distritales, el responsable del centro de salud junto con todo el personal.',
                        ],
                    ],
                ],
            ],

            // ────────────────────────────────────────────────────────
            // 4. CSA Catavi
            // ────────────────────────────────────────────────────────
            [
                'slug'        => 'csa-catavi',
                'nombre'      => 'Centro de Salud Ambulatorio Catavi',
                'tipo'        => 'centro_salud',
                'nivel'       => '1.er nivel',
                'zona'        => 'Catavi, Zona Central',
                'direccion'   => 'Zona Central, Av. Bioquímica, frente a la U.E. Ayacucho',
                'latitud'     => -18.41353,
                'longitud'    => -66.56387,
                'responsable' => 'Dra. Norma Flores Apaza',
                'atiende_24h' => false,
                'telefonos'   => [
                    ['numero' => '2582-2490', 'etiqueta' => 'fijo'],
                ],
                'horarios' => [
                    ['dias' => $lv, 'inicio' => '08:00', 'fin' => '20:00', 'nota' => 'Medicina general (12 hrs con turnos)'],
                    ['dias' => $lv, 'inicio' => '08:00', 'fin' => '14:00', 'nota' => 'Odontología (turno mañana)'],
                    ['dias' => $lv, 'inicio' => '14:00', 'fin' => '20:00', 'nota' => 'Odontología (turno tarde)'],
                ],
                'servicios' => [
                    ['nombre' => 'Medicina general',                    'categoria' => 'medicina_general'],
                    ['nombre' => 'Odontología',                         'categoria' => 'odontologia'],
                    ['nombre' => 'Enfermería',                          'categoria' => 'enfermeria'],
                    ['nombre' => 'Farmacia',                            'categoria' => 'farmacia'],
                    ['nombre' => 'Admisión, información y estadística', 'categoria' => 'otro'],
                    ['nombre' => 'Programa de adolescentes',            'categoria' => 'programas'],
                    ['nombre' => 'Programa de tuberculosis',            'categoria' => 'programas'],
                    ['nombre' => 'Atención a la mujer en edad fértil',  'categoria' => 'programas'],
                    ['nombre' => 'Violencia',                           'categoria' => 'programas'],
                    ['nombre' => 'Enfermedades no transmisibles',       'categoria' => 'programas'],
                    ['nombre' => 'Promoción de la salud',               'categoria' => 'programas'],
                    ['nombre' => 'Zoonosis',                            'categoria' => 'programas'],
                    ['nombre' => 'VIH / ITS',                           'categoria' => 'programas'],
                    ['nombre' => 'PAI (vacunas)',                       'categoria' => 'vacunacion'],
                    ['nombre' => 'Crecimiento y desarrollo (CCD)',      'categoria' => 'programas'],
                ],
                'secciones' => [
                    [
                        'titulo' => 'Requisitos para recibir la atención',
                        'items'  => [
                            'Carnet del SUS y carnet de identidad.',
                            'El paciente debe contar con adscripción en el municipio de Llallagua.',
                        ],
                    ],
                    [
                        'titulo' => 'Información y requisitos para el Seguro Único de Salud (SUS)',
                        'items'  => [
                            'Contar con el carnet de identidad.',
                            'Boleta de agua, luz o gas.',
                        ],
                    ],
                    [
                        'titulo' => 'Campañas y actividades',
                        'texto'  => 'El Centro de Salud Ambulatorio Catavi elabora el cronograma mensual por servicios donde se detallan los días, turnos y fechas de atención.',
                    ],
                ],
            ],

            // ────────────────────────────────────────────────────────
            // 5. CSA Siglo XX
            // ────────────────────────────────────────────────────────
            [
                'slug'        => 'csa-siglo-xx',
                'nombre'      => 'Centro de Salud Ambulatorio Siglo XX',
                'tipo'        => 'centro_salud',
                'nivel'       => '1.er nivel',
                'zona'        => 'Localidad Siglo XX, Campamento 6 B',
                'direccion'   => 'Campamento 6 B, Calle Campero s/n',
                'latitud'     => -18.421879,
                'longitud'    => -66.589474,
                'responsable' => 'Dr. Gustavo Vasquez Quiñones',
                'atiende_24h' => false,
                'telefonos'   => [
                    ['numero' => '2-582-0058', 'etiqueta' => 'fijo'],
                ],
                'horarios' => [
                    ['dias' => $lv, 'inicio' => '08:00', 'fin' => '12:00', 'nota' => 'Turno mañana'],
                    ['dias' => $lv, 'inicio' => '14:00', 'fin' => '18:00', 'nota' => 'Turno tarde'],
                ],
                'servicios' => [
                    ['nombre' => 'Servicio de Medicina',                                           'categoria' => 'medicina_general'],
                    ['nombre' => 'Servicio de Odontología',                                        'categoria' => 'odontologia'],
                    ['nombre' => 'Servicio de Enfermería (CCDD, PAI, DIT, Enfermería, Tuberculosis)', 'categoria' => 'enfermeria'],
                    ['nombre' => 'Servicio de Farmacia',                                           'categoria' => 'farmacia'],
                    ['nombre' => 'Servicio de Estadística, Admisiones e Informática',              'categoria' => 'otro'],
                ],
                'secciones' => [
                    [
                        'titulo' => 'Requisitos para recibir la atención',
                        'items'  => [
                            'La atención se realiza sin discriminación alguna a todo paciente que acude al establecimiento, tenga seguro a corto plazo o no.',
                            'Carnet de identidad.',
                            'Formulario del SUS.',
                        ],
                    ],
                    [
                        'titulo' => 'Información y requisitos para el Seguro Universal de Salud (SUS)',
                        'items'  => [
                            'Fotocopia de C.I.',
                            'Fotocopia de boleta de luz o agua (según normativa, el paciente debe afiliarse en el centro de salud más cercano a su domicilio, sin limitar la atención en los diferentes centros de salud del municipio).',
                        ],
                    ],
                    [
                        'titulo' => 'Actividades y campañas de salud',
                        'texto'  => 'Las actividades y campañas se realizan según la planificación de los diferentes programas de la Red Municipal SAFCI Llallagua durante toda la gestión.',
                    ],
                ],
            ],

            // ────────────────────────────────────────────────────────
            // 6. Centro de Rehabilitación Llallagua
            // ────────────────────────────────────────────────────────
            [
                'slug'        => 'centro-rehabilitacion-llallagua',
                'nombre'      => 'Centro de Rehabilitación Llallagua',
                'tipo'        => 'rehabilitacion',
                'nivel'       => 'GAM Llallagua',
                'zona'        => 'Distrito Catavi, Zona 12 de Febrero',
                'direccion'   => 'Calle 12 de Febrero, Distrito Catavi',
                'latitud'     => null,
                'longitud'    => null,
                'responsable' => 'Lic. Hugo Quispe Mamani',
                'descripcion' => 'El Centro de Rehabilitación Llallagua cuenta con un equipo multidisciplinario encargado de brindar atención integral a personas con discapacidad y población en general, mediante servicios de promoción, prevención, evaluación, tratamiento y rehabilitación.',
                'atiende_24h' => false,
                'telefonos'   => [
                    ['numero' => '79310691', 'etiqueta' => 'celular'],
                    ['numero' => '72213455', 'etiqueta' => 'celular'],
                ],
                'horarios' => [
                    ['dias' => $lv, 'inicio' => '08:30', 'fin' => '12:30', 'nota' => 'Turno de la mañana'],
                    ['dias' => $lv, 'inicio' => '14:00', 'fin' => '18:30', 'nota' => 'Turno de la tarde'],
                ],
                'servicios' => [
                    [
                        'nombre'      => 'Medicina',
                        'categoria'   => 'medicina_general',
                        'descripcion' => implode("\n", [
                            'Responsable: Dra. Wendy Nadeska Paco.',
                            '• Evaluación y valoración médica integral.',
                            '• Consulta médica general.',
                            '• Valoración del estado de salud de las personas con discapacidad.',
                            '• Identificación de necesidades de rehabilitación.',
                            '• Seguimiento y control médico.',
                            '• Orientación sobre prevención de enfermedades y complicaciones.',
                            '• Derivación a establecimientos de mayor complejidad cuando corresponda.',
                        ]),
                    ],
                    [
                        'nombre'      => 'Enfermería',
                        'categoria'   => 'enfermeria',
                        'descripcion' => implode("\n", [
                            'Responsable: Lic. Ana Espinoza.',
                            '• Control de signos vitales.',
                            '• Control de presión arterial, frecuencia cardíaca, frecuencia respiratoria y temperatura.',
                            '• Atención y orientación de enfermería.',
                            '• Promoción de hábitos saludables.',
                            '• Educación para la prevención de enfermedades.',
                            '• Apoyo en actividades de promoción y prevención de la salud.',
                            '• Seguimiento y orientación a pacientes y familiares.',
                        ]),
                    ],
                    [
                        'nombre'      => 'Fisioterapia',
                        'categoria'   => 'fisioterapia',
                        'descripcion' => implode("\n", [
                            'Responsables: Lic. Jeaniz Felipez, Lic. Brisan Montaño, Lic. Mariela Machaca y Lic. Hugo Quispe.',
                            '• Evaluación y valoración fisioterapéutica.',
                            '• Elaboración de planes individualizados de tratamiento.',
                            '• Rehabilitación de alteraciones musculoesqueléticas.',
                            '• Tratamiento de lesiones neurológicas.',
                            '• Rehabilitación de personas con discapacidad física.',
                            '• Terapia para mejorar la movilidad articular, fuerza muscular y equilibrio.',
                            '• Reeducación de la marcha y entrenamiento funcional.',
                            '• Ejercicios terapéuticos y técnicas de rehabilitación.',
                            '• Prevención de complicaciones secundarias a la discapacidad.',
                            '• Educación al paciente y familiares sobre ejercicios domiciliarios.',
                        ]),
                    ],
                    [
                        'nombre'      => 'Fonoaudiología',
                        'categoria'   => 'fonoaudiologia',
                        'descripcion' => implode("\n", [
                            'Responsable: Lic. Xiomara Benavides.',
                            '• Evaluación del lenguaje y la comunicación.',
                            '• Evaluación de alteraciones del habla.',
                            '• Intervención en dificultades del desarrollo del lenguaje.',
                            '• Estimulación del lenguaje y la comunicación.',
                            '• Rehabilitación de alteraciones de la articulación y pronunciación.',
                            '• Intervención en dificultades de la comunicación asociadas a trastornos del neurodesarrollo.',
                            '• Orientación a padres y familiares.',
                            '• Seguimiento de la evolución de los pacientes.',
                        ]),
                    ],
                    [
                        'nombre'      => 'Trabajo social',
                        'categoria'   => 'trabajo_social',
                        'descripcion' => implode("\n", [
                            'Responsable: Lic. Jhemina Cruz.',
                            '• Evaluación sociofamiliar de personas con discapacidad.',
                            '• Orientación sobre derechos y beneficios de las personas con discapacidad.',
                            '• Información sobre trámites y documentación relacionada con discapacidad.',
                            '• Orientación sobre el acceso al Sistema Único de Salud (SUS).',
                            '• Coordinación con instituciones y organizaciones sociales.',
                            '• Seguimiento de casos sociales.',
                            '• Visitas domiciliarias.',
                            '• Promoción de la inclusión familiar, educativa y comunitaria.',
                            '• Sensibilización sobre los derechos de las personas con discapacidad.',
                        ]),
                    ],
                ],
                'cronograma_filas' => [
                    ['Lunes a viernes', 'Medicina',       'Dra. Wendy Nadeska Paco', '09:00–12:00 / 14:00–17:00', 'Consultorio médico'],
                    ['Lunes a viernes', 'Enfermería',     'Lic. Ana Espinoza',       '09:00–12:00 / 14:00–17:00', 'Área de enfermería'],
                    ['Lunes a viernes', 'Fisioterapia',   'Lic. Jeaniz Felipez',     '09:00–12:00 / 14:00–17:00', 'Área de fisioterapia'],
                    ['Lunes a viernes', 'Fisioterapia',   'Lic. Brisan Montaño',     '08:30–12:30 / 14:30–18:30', 'Área de fisioterapia'],
                    ['Lunes a viernes', 'Fisioterapia',   'Lic. Mariela Machaca',    '09:00–12:00 / 14:00–17:00', 'Área de fisioterapia'],
                    ['Lunes a viernes', 'Fisioterapia',   'Lic. Hugo Quispe',        '09:00–12:00 / 14:00–17:00', 'Área de fisioterapia'],
                    ['Lunes a viernes', 'Trabajo Social', 'Lic. Jhemina Cruz',       '09:00–12:00 / 14:00–17:00', 'Área de trabajo social'],
                    ['Lunes a viernes', 'Fonoaudiología', 'Lic. Xiomara Benavides',  '14:30–18:30',               'Área de fonoaudiología'],
                ],
                'secciones' => [
                    [
                        'titulo' => 'Requisitos para recibir atención',
                        'items'  => [
                            'Cédula de identidad del paciente.',
                            'Carnet del Sistema Único de Salud (SUS), cuando corresponda.',
                            'Referencia médica.',
                            'Estudios complementarios anteriores (radiografías, ecografías, tomografías, laboratorios u otros), cuando corresponda.',
                            'Informes médicos y antecedentes de tratamientos previos.',
                            'En caso de menores de edad, acudir acompañado de su padre, madre o tutor.',
                            'Documentación relacionada con discapacidad, si corresponde.',
                            'La atención estará sujeta a la valoración del profesional y a los procedimientos establecidos por el establecimiento de salud.',
                        ],
                    ],
                    [
                        'titulo' => 'Información sobre el Seguro Universal de Salud (SUS) y programas de salud',
                        'texto'  => 'El Centro de Rehabilitación Llallagua orienta a la población sobre el acceso a los servicios de salud y los beneficios del Sistema Único de Salud (SUS), conforme a la normativa vigente:',
                        'items'  => [
                            'Requisitos para acceder al SUS.',
                            'Derechos de los usuarios de los servicios de salud.',
                            'Atención integral a personas con discapacidad.',
                            'Prevención y promoción de la salud.',
                            'Programas de salud vigentes.',
                            'Referencia y contrarreferencia cuando corresponda.',
                        ],
                    ],
                    [
                        'titulo' => 'Actividades y campañas de salud',
                        'texto'  => 'El Centro de Rehabilitación Llallagua desarrolla actividades de promoción, prevención, sensibilización e inclusión social, dirigidas a personas con discapacidad, familias y población en general:',
                        'items'  => [
                            'Campañas de sensibilización sobre los derechos de las personas con discapacidad.',
                            'Jornadas de prevención de la discapacidad.',
                            'Actividades de promoción de la salud y prevención de enfermedades.',
                            'Charlas educativas sobre rehabilitación y cuidado de la salud.',
                            'Actividades de sensibilización sobre el buen trato e inclusión social.',
                            'Caminatas y ferias de sensibilización por fechas conmemorativas relacionadas con la salud.',
                            'Campañas de prevención y cuidado de la salud visual.',
                            'Visitas domiciliarias y seguimiento a personas con discapacidad.',
                            'Actividades de orientación a familiares y cuidadores.',
                            'Coordinación interinstitucional para fortalecer la rehabilitación y la inclusión comunitaria.',
                        ],
                    ],
                    [
                        'titulo' => 'Observaciones generales',
                        'texto'  => 'El Centro de Rehabilitación Llallagua desarrolla sus actividades mediante un equipo multidisciplinario, brindando servicios orientados a mejorar la calidad de vida, autonomía funcional, inclusión y participación social de las personas con discapacidad y población en general. Asimismo, se continúa fortaleciendo la coordinación con el Gobierno Autónomo Municipal de Llallagua, establecimientos de salud, organizaciones sociales e instituciones públicas y privadas, con la finalidad de ampliar el acceso a los servicios de rehabilitación.',
                    ],
                ],
            ],
        ];
    }
}
