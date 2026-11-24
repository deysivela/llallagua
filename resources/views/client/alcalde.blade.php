@extends('client.layouts.index')
@section('title', 'Alcalde Municipal | GAM Llallagua')
@section('content')
    <section class="gam-inner-section gam-alcalde-page">
        <div class="container">
            <header class="gam-alcalde-head gam-alcalde-head--center" data-aos="fade-up" data-aos-duration="700">
                <h1 class="gam-heading">Alcalde Municipal</h1>
                <p class="gam-lead">Autoridad ejecutiva del Gobierno Autónomo Municipal de Llallagua</p>
            </header>

            @forelse($employees as $employee)
                @php
                    $nombreBase = trim($employee->name . ' ' . $employee->last_name);
                    $nombreCompleto = stripos($nombreBase, 'M.Sc') === 0 ? $nombreBase : 'M.Sc. ' . $nombreBase;
                @endphp
                <article class="gam-alcalde" aria-labelledby="alcalde-nombre">
                    <div class="gam-alcalde-hero" data-aos="fade-up" data-aos-duration="800">
                        <figure class="gam-alcalde-portrait">
                            <img src="{{ asset($employee->photo) }}"
                                alt="Retrato de {{ $nombreCompleto }}, Alcalde Municipal de Llallagua"
                                width="420" height="520" loading="eager">
                        </figure>

                        <div class="gam-alcalde-identity">
                            <span class="gam-alcalde-kicker">Perfil Biográfico e Institucional</span>
                            <h2 class="gam-alcalde-name" id="alcalde-nombre">{{ $nombreCompleto }}</h2>
                            @if (!empty($employee->charge_employee))
                                <p class="gam-alcalde-role">{{ $employee->charge_employee }}</p>
                            @endif
                            <p class="gam-alcalde-meta">
                                Nacido el 28 de diciembre de 1974 en el histórico distrito minero de Llallagua,
                                provincia Rafael Bustillo del departamento de Potosí, Bolivia. Educador de vocación,
                                gestor educativo y escritor, cuenta con una sólida formación posgradual y una
                                destacada trayectoria como director institucionalizado durante las gestiones 2009 al 2026,
                                dedicada al fortalecimiento de la educación, los valores y el desarrollo humano del municipio.
                            </p>

                            <dl class="gam-alcalde-facts">
                                <div class="gam-alcalde-fact">
                                    <dt>
                                        <i class="icon-Calendar-4" aria-hidden="true"></i>
                                        Nacimiento y origen
                                    </dt>
                                    <dd>28 de diciembre de 1974 · Distrito minero de Llallagua, Potosí – Bolivia</dd>
                                </div>
                                <div class="gam-alcalde-fact">
                                    <dt>
                                        <i class="icon-Administrator" aria-hidden="true"></i>
                                        Grado académico
                                    </dt>
                                    <dd>Magíster en Educación Sociocomunitaria (M.Sc.)</dd>
                                </div>
                                <div class="gam-alcalde-fact">
                                    <dt>
                                        <i class="ti-shield" aria-hidden="true"></i>
                                        Gestión directiva
                                    </dt>
                                    <dd>Director institucionalizado (Gestiones 2009 – 2026)</dd>
                                </div>
                                @if ($employee->address)
                                    <div class="gam-alcalde-fact">
                                        <dt>
                                            <i class="icon-Map-Marker2" aria-hidden="true"></i>
                                            Dirección
                                        </dt>
                                        <dd>{{ $employee->address }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <div class="gam-alcalde-bio-grid" data-aos="fade-up" data-aos-duration="850">
                        <section class="gam-alcalde-card" aria-labelledby="bio-formacion-superior">
                            <header class="gam-alcalde-card-head">
                                <span class="gam-alcalde-card-icon" aria-hidden="true">
                                    <i class="icon-Folder-Bookmark"></i>
                                </span>
                                <div>
                                    <span class="gam-alcalde-card-eyebrow">Trayectoria Académica</span>
                                    <h3 class="gam-alcalde-card-title" id="bio-formacion-superior">Formación Superior y Posgrado</h3>
                                </div>
                            </header>
                            <ul class="gam-alcalde-timeline">
                                <li>
                                    <strong>Magíster en Educación Sociocomunitaria</strong>
                                    <span>Universidad Pedagógica de Sucre</span>
                                </li>
                                <li>
                                    <strong>Especialista en Diseño Curricular</strong>
                                    <span>Universidad Nacional “Siglo XX”</span>
                                </li>
                                <li>
                                    <strong>Licenciatura en Ciencias de la Educación</strong>
                                    <span>Universidad Salesiana de Bolivia</span>
                                </li>
                                <li>
                                    <strong>Maestro titulado en Religión, Ética y Moral</strong>
                                    <span>Instituto Normal Superior Católico “Sedes Sapientiae”</span>
                                </li>
                            </ul>
                        </section>

                        <section class="gam-alcalde-card" aria-labelledby="bio-estudios-gestion">
                            <header class="gam-alcalde-card-head">
                                <span class="gam-alcalde-card-icon" aria-hidden="true">
                                    <i class="ti-flag-alt"></i>
                                </span>
                                <div>
                                    <span class="gam-alcalde-card-eyebrow">Raíces y Servicio</span>
                                    <h3 class="gam-alcalde-card-title" id="bio-estudios-gestion">Formación Escolar y Carrera Directiva</h3>
                                </div>
                            </header>
                            <div class="gam-alcalde-card-body">
                                <div class="gam-alcalde-highlight">
                                    <span class="gam-alcalde-badge">2009 – 2026</span>
                                    <p>
                                        Se desempeñó como <strong>Director Institucionalizado</strong> durante las gestiones
                                        2009 al 2026, liderando proyectos educativos e institucionales al servicio de la niñez
                                        y juventud de Llallagua.
                                    </p>
                                </div>
                                <h4 class="gam-alcalde-subtitle">Unidades educativas donde cursó sus estudios</h4>
                                <ul class="gam-alcalde-tags">
                                    <li>U.E. Mons. Cleto Loayza</li>
                                    <li>U.E. 3 Potosí</li>
                                    <li>Ciclo Intermedio Bolivia</li>
                                    <li>Colegio Técnico Humanístico 1ro de Mayo</li>
                                </ul>
                            </div>
                        </section>

                        <section class="gam-alcalde-card" aria-labelledby="bio-reconocimientos">
                            <header class="gam-alcalde-card-head">
                                <span class="gam-alcalde-card-icon" aria-hidden="true">
                                    <i class="icon-Bookmark"></i>
                                </span>
                                <div>
                                    <span class="gam-alcalde-card-eyebrow">Mérito Educativo</span>
                                    <h3 class="gam-alcalde-card-title" id="bio-reconocimientos">Reconocimientos y Distinciones</h3>
                                </div>
                            </header>
                            <ul class="gam-alcalde-timeline">
                                <li>
                                    <strong>Cámara de Diputados y Cámara de Senadores</strong>
                                    <span>Reconocimiento legislativo al Mérito Educativo por su aporte a la educación boliviana.</span>
                                </li>
                                <li>
                                    <strong>Gobierno Autónomo Municipal de Llallagua</strong>
                                    <span>Distinción institucional por su vocación de servicio y contribución al desarrollo educativo local.</span>
                                </li>
                                <li>
                                    <strong>Dirección Distrital de Educación de Llallagua</strong>
                                    <span>Reconocimiento a su trayectoria docente, directiva y producción pedagógica.</span>
                                </li>
                            </ul>
                        </section>

                        <section class="gam-alcalde-card" aria-labelledby="bio-publicaciones">
                            <header class="gam-alcalde-card-head">
                                <span class="gam-alcalde-card-icon" aria-hidden="true">
                                    <i class="icon-File-ClipboardFileText"></i>
                                </span>
                                <div>
                                    <span class="gam-alcalde-card-eyebrow">Producción Intelectual</span>
                                    <h3 class="gam-alcalde-card-title" id="bio-publicaciones">Publicaciones y Obras</h3>
                                </div>
                            </header>
                            <ul class="gam-alcalde-pubs">
                                <li>
                                    <span class="gam-alcalde-pub-year">Jul. 2006</span>
                                    <div>
                                        <strong>Periódico “Caminando Juntos”</strong>
                                        <span>Diócesis de Potosí</span>
                                    </div>
                                </li>
                                <li>
                                    <span class="gam-alcalde-pub-year">2009</span>
                                    <div>
                                        <strong>Boletín “Sumaq Kawsay”</strong>
                                        <span>Publicación cultural y educativa</span>
                                    </div>
                                </li>
                                <li>
                                    <span class="gam-alcalde-pub-year">2011</span>
                                    <div>
                                        <strong>Texto “Mis Valores”</strong>
                                        <span>Dirección Distrital de Educación de Llallagua</span>
                                    </div>
                                </li>
                                <li>
                                    <span class="gam-alcalde-pub-year">2025</span>
                                    <div>
                                        <strong>Antología Literaria “Bicentenario de Bolivia 2025”</strong>
                                        <span>Obra colectiva conmemorativa</span>
                                    </div>
                                </li>
                                <li>
                                    <span class="gam-alcalde-pub-year">2025</span>
                                    <div>
                                        <strong>Cuentos — Unidad Educativa “Libertad”</strong>
                                        <span>Producción narrativa y pedagógica</span>
                                    </div>
                                </li>
                            </ul>
                        </section>
                    </div>

                </article>
            @empty
                <div class="gam-empty-state" data-aos="fade-up" data-aos-duration="800">
                    <i class="icon-Administrator" aria-hidden="true"></i>
                    <h2 class="gam-heading-sm">Información no disponible</h2>
                    <p>Pronto publicaremos los datos de la autoridad ejecutiva del municipio.</p>
                </div>
            @endforelse

            <aside class="gam-alcalde-links" aria-label="Secciones relacionadas" data-aos="fade-up"
                data-aos-duration="900">
                <span class="gam-side-label">También puede consultar</span>
                <div class="gam-alcalde-links-row">
                    <a class="gam-alcalde-link" href="{{ route('autoridades') }}">
                        <i class="icon-Business-Mens" aria-hidden="true"></i>
                        <span>
                            <strong>Autoridades</strong>
                            <em>Nómina municipal</em>
                        </span>
                        <i class="ti-arrow-right" aria-hidden="true"></i>
                    </a>
                    <a class="gam-alcalde-link" href="{{ route('proyectos') }}">
                        <i class="icon-Edit-Map" aria-hidden="true"></i>
                        <span>
                            <strong>Proyectos</strong>
                            <em>Obras en ejecución</em>
                        </span>
                        <i class="ti-arrow-right" aria-hidden="true"></i>
                    </a>
                    <a class="gam-alcalde-link" href="{{ route('transparencia') }}">
                        <i class="icon-File-Search" aria-hidden="true"></i>
                        <span>
                            <strong>Transparencia</strong>
                            <em>Rendición de cuentas</em>
                        </span>
                        <i class="ti-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </aside>

        </div>
    </section>
@endsection
