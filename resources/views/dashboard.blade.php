<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Dashboard Operativo - KPIs -->
            <div class="mb-8 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">

                <!-- Tarjeta: Gacetas Publicadas -->
                <div class="kpi-card">
                    <div class="kpi-label">Gacetas Publicadas</div>
                    <div class="kpi-value">{{ $kpiGacetas }}</div>
                </div>

                <!-- Tarjeta: Solicitudes Atendidas -->
                <div class="kpi-card">
                    <div class="kpi-label">Solicitudes Atendidas</div>
                    <div class="kpi-value">{{ $kpiSolicitudesAtendidas }}</div>
                </div>

                <!-- Tarjeta: Instituciones Activas -->
                <div class="kpi-card">
                    <div class="kpi-label">Instituciones Activas</div>
                    <div class="kpi-value">{{ $kpiInstitucionesActivas }}</div>
                </div>

                <!-- Tarjeta: Total Instituciones -->
                <div class="kpi-card">
                    <div class="kpi-label">Total Instituciones</div>
                    <div class="kpi-value">{{ $kpiTotalInstituciones }}</div>
                </div>

                <!-- Tarjeta: POAs Activos -->
                <div class="kpi-card">
                    <div class="kpi-label">POAs ({{ date('Y') }})</div>
                    <div class="kpi-value">{{ $kpiPoasActuales }}</div>
                </div>

                <!-- Tarjeta: Préstamos Activos -->
                <div class="kpi-card">
                    <div class="kpi-label">Préstamos Físicos</div>
                    <div class="kpi-value">{{ $kpiPrestamosActivos }}</div>
                </div>

            </div>

            <!-- Contenedores de los Gráficos -->
            <div class="mb-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
                <div class="chart-card">
                    <h3>Tendencia de Solicitudes ({{ date('Y') }})</h3>
                    <div class="relative h-72">
                        <canvas id="chartSolicitudes"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3>Atenciones por Coordinación ({{ date('Y') }})</h3>
                    <div class="relative h-72">
                        <canvas id="chartAtenciones"></canvas>
                    </div>
                </div>
            </div>

            <!-- Importación de Chart.js (defer: no bloquea el parseo) -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Paleta corporativa: azul marino + neutros, con acento esmeralda.
                const corporate = {
                    navy: '#1e3a8a',
                    navySoft: 'rgba(30, 58, 138, 0.08)',
                    slate: '#334155',
                    steel: '#64748b',
                    muted: '#94a3b8',
                    line: '#e2e8f0',
                    accent: '#059669',
                    accentSoft: 'rgba(5, 150, 105, 0.10)'
                };

                const baseTooltip = {
                    backgroundColor: '#0f172a',
                    titleColor: '#ffffff',
                    bodyColor: '#e2e8f0',
                    borderColor: '#334155',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 6,
                    displayColors: true,
                    boxPadding: 4
                };

                Chart.defaults.font.family = "'Figtree', ui-sans-serif, system-ui, sans-serif";
                Chart.defaults.color = '#64748b';

                // --- Gráfico 1: Líneas (Tendencia de Solicitudes) ---
                const rawSolicitudes = @json($chartSolicitudesMes);
                const mesesLabel = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

                function prepararDatosChart(rawData) {
                    let arrayLimpio = Array(12).fill(0);
                    for (const [mes, total] of Object.entries(rawData)) {
                        let indiceMes = parseInt(mes, 10) - 1;
                        if(indiceMes >= 0 && indiceMes < 12) {
                            arrayLimpio[indiceMes] = total;
                        }
                    }
                    return arrayLimpio;
                }

                const dataSolicitudes = prepararDatosChart(rawSolicitudes);

                new Chart(document.getElementById('chartSolicitudes'), {
                    type: 'line',
                    data: {
                        labels: mesesLabel,
                        datasets: [{
                            label: 'Solicitudes',
                            data: dataSolicitudes,
                            borderColor: corporate.navy,
                            backgroundColor: corporate.navySoft,
                            pointBackgroundColor: corporate.navy,
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: baseTooltip
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { color: corporate.line }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, color: '#64748b' },
                                grid: { color: corporate.line },
                                border: { display: false }
                            }
                        }
                    }
                });

                // --- Gráfico 2: Dona (Atenciones por Coordinación) ---
                const labelsDona = @json($chartAtencionesLabels);
                const dataDona = @json($chartAtencionesData);

                new Chart(document.getElementById('chartAtenciones'), {
                    type: 'doughnut',
                    data: {
                        labels: labelsDona,
                        datasets: [{
                            data: dataDona,
                            backgroundColor: [
                                corporate.navy,
                                corporate.slate,
                                corporate.accent,
                                corporate.steel,
                                corporate.muted,
                                '#cbd5e1'
                            ],
                            borderColor: '#ffffff',
                            borderWidth: 2,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#475569',
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    boxWidth: 8,
                                    padding: 16,
                                    font: { size: 11 }
                                }
                            },
                            tooltip: baseTooltip
                        }
                    }
                });
            });
            </script>
        </div>
    </div>
</x-app-layout>
