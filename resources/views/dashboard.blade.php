<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Tarjeta Usuarios Totales -->
                <div
                    class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-slate-500 text-sm font-medium uppercase tracking-wider">Usuarios Activos</h3>
                        <p class="text-3xl font-bold text-slate-900 mt-2">{{ $totalUsers }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full text-blue-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                </div>

                <!-- Tarjeta Roles Registrados -->
                <div
                    class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-slate-500 text-sm font-medium uppercase tracking-wider">Roles Registrados</h3>
                        <p class="text-3xl font-bold text-slate-900 mt-2">{{ $totalRoles }}</p>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-full text-indigo-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                            </path>
                        </svg>
                    </div>
                </div>

                <!-- Tarjeta Instituciones Totales -->
                <div
                    class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-slate-500 text-sm font-medium uppercase tracking-wider">Instituciones</h3>
                        <p class="text-3xl font-bold text-slate-900 mt-2">{{ $totalInstitutions }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full text-purple-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                </div>

                <!-- Tarjeta Documentos/Casos Totales -->
                <div
                    class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-slate-500 text-sm font-medium uppercase tracking-wider">Doc / Casos Totales</h3>
                        <p class="text-3xl font-bold text-slate-900 mt-2">1,248</p>
                    </div>
                    <div class="p-3 bg-teal-100 rounded-full text-teal-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Tarjeta POA General -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 mb-6">
                <h3 class="text-lg font-medium text-slate-800 mb-4 border-b pb-2">Avance Plan Operativo Anual (POA General {{ date('Y') }})</h3>
                
                @if(isset($poasAnuales) && $poasAnuales->count() > 0)
                    @php
                        $totalMeta = 0;
                        $totalEjecutada = 0;
                        foreach($poasAnuales as $poa) {
                            foreach($poa->actividades as $act) {
                                foreach($act->metasTrimestrales as $meta) {
                                    $totalMeta += $meta->meta_actual;
                                    $totalEjecutada += $meta->ejecutada;
                                }
                            }
                        }
                        $porcentaje = $totalMeta > 0 ? min(100, round(($totalEjecutada / $totalMeta) * 100)) : 0;
                    @endphp
                    
                    <div class="mb-2 flex justify-between text-sm mt-4">
                        <span class="text-slate-600 font-medium">Progreso Anual</span>
                        <span class="text-slate-800 font-bold">{{ $porcentaje }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-4 mb-2">
                        <div class="bg-blue-600 h-4 rounded-full" style="width: {{ $porcentaje }}%"></div>
                    </div>
                    <div class="text-xs text-slate-500 flex justify-between">
                        <span>Actividades Ejecutadas: {{ $totalEjecutada }}</span>
                        <span>Meta Programada: {{ $totalMeta }}</span>
                    </div>
                @else
                    <p class="text-slate-500 text-sm py-4 italic text-center">No hay Plan Operativo activo para el año en curso.</p>
                @endif
            </div>

            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 relative mb-6">

                <!-- Gráfico de Usuarios -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                    <h3 class="text-lg font-medium text-slate-800 mb-4 border-b pb-2">Distribución de Usuarios por Rol
                    </h3>
                    <div class="relative h-64 w-full">
                        <canvas id="rolesChart"></canvas>
                    </div>
                </div>

                <!-- Gráfico de Resumen (Totales) -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                    <h3 class="text-lg font-medium text-slate-800 mb-4 border-b pb-2">Proporción del Sistema</h3>
                    <div class="relative h-64 w-full">
                        <canvas id="summaryChart"></canvas>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Chart.js Local -->
    <script src="{{ asset('assets/vendor/js/chart.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Datos inyectados desde Laravel
            const rolesData = @json($rolesDistribution);
            const totalUsers = {{ $totalUsers }};
            const totalRoles = {{ $totalRoles }};

            const roleNames = rolesData.map(r => r.name);
            const roleCounts = rolesData.map(r => r.count);

            // Chart 1: Distribución por Roles (Bar Chart)
            const ctxRoles = document.getElementById('rolesChart').getContext('2d');
            new Chart(ctxRoles, {
                type: 'bar',
                data: {
                    labels: roleNames,
                    datasets: [{
                        label: 'Cantidad de Usuarios',
                        data: roleCounts,
                        backgroundColor: ['#0f172a', '#1e293b', '#334155', '#475569'], // Corporate blues/slates
                        borderColor: '#ffffff',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            // Chart 2: Proporción del Sistema (Pie Chart o Doughnut)
            const ctxSummary = document.getElementById('summaryChart').getContext('2d');
            new Chart(ctxSummary, {
                type: 'doughnut',
                data: {
                    labels: ['Usuarios Totales', 'Roles Creados'],
                    datasets: [{
                        data: [totalUsers, totalRoles],
                        backgroundColor: [
                            '#1e293b', // slate-800
                            '#cbd5e1'  // slate-300
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    </script>
</x-app-layout>