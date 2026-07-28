<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Dashboard Operativo - KPIs -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                
                <!-- Tarjeta: Gacetas Publicadas -->
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
                    <div class="text-sm text-slate-500 font-bold uppercase tracking-wide">Gacetas Publicadas</div>
                    <div class="text-3xl font-extrabold text-slate-800 mt-2">{{ $kpiGacetas }}</div>
                </div>
                
                <!-- Tarjeta: Solicitudes Atendidas -->
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
                    <div class="text-sm text-slate-500 font-bold uppercase tracking-wide">Solicitudes Atendidas</div>
                    <div class="text-3xl font-extrabold text-slate-800 mt-2">{{ $kpiSolicitudesAtendidas }}</div>
                </div>
                
                <!-- Tarjeta: Instituciones Activas -->
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-purple-500">
                    <div class="text-sm text-slate-500 font-bold uppercase tracking-wide">Instituciones Activas</div>
                    <div class="text-3xl font-extrabold text-slate-800 mt-2">{{ $kpiInstitucionesActivas }}</div>
                </div>
                
                <!-- Tarjeta: Total Instituciones -->
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-amber-500">
                    <div class="text-sm text-slate-500 font-bold uppercase tracking-wide">Total Instituciones</div>
                    <div class="text-3xl font-extrabold text-slate-800 mt-2">{{ $kpiTotalInstituciones }}</div>
                </div>
                
                <!-- Tarjeta: POAs Activos -->
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-red-500">
                    <div class="text-sm text-slate-500 font-bold uppercase tracking-wide">POAs ({{ date('Y') }})</div>
                    <div class="text-3xl font-extrabold text-slate-800 mt-2">{{ $kpiPoasActuales }}</div>
                </div>

                <!-- Tarjeta: Préstamos Activos -->
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-orange-500">
                    <div class="text-sm text-slate-500 font-bold uppercase tracking-wide">Préstamos Físicos</div>
                    <div class="text-3xl font-extrabold text-slate-800 mt-2">{{ $kpiPrestamosActivos }}</div>
                </div>
                
            </div>

            <!-- Contenedores de los Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-bold text-slate-700 mb-4">Tendencia de Solicitudes ({{ date('Y') }})</h3>
                    <canvas id="chartSolicitudes"></canvas>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-bold text-slate-700 mb-4">Atenciones por Coordinación ({{ date('Y') }})</h3>
                    <canvas id="chartAtenciones"></canvas>
                </div>
            </div>

            <!-- Importación de Chart.js -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
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
                            borderColor: '#10b981', 
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: { 
                            y: { beginAtZero: true, ticks: { precision: 0 } }
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
                            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            });
            </script>
        </div>
    </div>
</x-app-layout>