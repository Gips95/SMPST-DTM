<?php
session_start();
include 'panel.php';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Dashboard de Estadísticas</title>
    <link href="styles/bootstrap.css" rel="stylesheet">

    <script src="js/chart.js"></script>
    <style>
        .main-content {
            flex: 1;
            padding: 20px;
            transition: margin-left 0.3s;
            overflow-x: hidden;
        }
        .card-stat {
            transition: transform 0.3s;
            cursor: pointer;
        }

        .card-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .chart-container {
            height: 400px;
            position: relative;
        }

        /* ─────────────────────────────────────────
   Tipografía y colores globales
───────────────────────────────────────── */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        h5 {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* ─────────────────────────────────────────
   Contenedor de filtros
───────────────────────────────────────── */
        .row.mb-4 {
            align-items: flex-end;
        }

        .form-select {
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .btn-primary {
            border-radius: 8px;
            padding: 0.6rem 1.4rem;
        }

        /* ─────────────────────────────────────────
   Tarjetas resumen
───────────────────────────────────────── */
        .card-stat {
            border: none;
            border-radius: 12px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card-stat .card-body {
            padding: 1.5rem;
            text-align: center;
        }

        .card-stat h2 {
            font-size: 2.5rem;
            margin: 0.5rem 0 0;
        }

        .card-stat.bg-primary {
            background: linear-gradient(135deg, #4e73df, #224abe);
        }

        .card-stat.bg-success {
            background: linear-gradient(135deg, #1cc88a, #17a673);
        }

        .card-stat.bg-info {
            background: linear-gradient(135deg, #36b9cc, #2c9faf);
        }

        /* Hover */
        .card-stat:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        /* ─────────────────────────────────────────
   Gráficos
───────────────────────────────────────── */
        .chart-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .chart-container canvas {
            padding: 1rem;
        }

        /* ─────────────────────────────────────────
   Responsive tweaks
───────────────────────────────────────── */
        @media (max-width: 768px) {
            .card-stat h2 {
                font-size: 2rem;
            }

            .chart-container {
                height: 300px;
            }
        }

        /* ─────────────────────────────────────────
   Botones y controles
───────────────────────────────────────── */
        .btn-custom {
            border-radius: 8px;
            padding: 0.6rem 1.4rem;
            transition: all 0.3s ease;
            font-weight: 500;
            letter-spacing: 0.5px;
            min-width: 180px;
            margin: 0 5px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Contenedor de botones */
        .buttons-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
    </style>
</head>

<body>
<div class="main-content" id="mainContent">
        <div class="container-fluid py-4">
            <!-- Filtros -->
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
                    <select id="fechaInicio" class="form-select btn-custom">
                        <option value="">-- Selecciona Año --</option>
                        <!-- Se llenará por JS -->
                    </select>
                </div>

                <div class="col-auto">
                    <button id="btnActualizar" class="btn btn-primary btn-custom">
                        <i class="fas fa-sync-alt me-2"></i>Actualizar Datos
                    </button>
                </div>

                
            </div>

        </div>

        <!-- Tarjetas resumen -->
        <div class="row" id="mainStats"></div>

        <!-- Gráficos principales -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body chart-container">
                        <canvas id="chartLines"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body chart-container">
                        <canvas id="chartTimeline"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de archivos -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartFiles"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
    <script>
        class StatsDashboard {
            constructor() {
                this.charts = {};
                this.init();
            }

            init() {
                this.loadData();
                document.getElementById('btnActualizar')
                    .addEventListener('click', () => this.loadData());
            }

            async loadData() {
                try {
                    // Si no selecciona nada, tomamos hoy:
                    const raw = document.getElementById('fechaInicio').value;
                    const filters = {
                        fecha_inicio: raw || new Date().toISOString().slice(0, 10) // “YYYY-MM-DD”
                    };

                    const [main, lines, timeline, files] = await Promise.all([
                        this.fetchData('main', filters),
                        this.fetchData('lines', filters),
                        this.fetchData('timeline', filters),
                        this.fetchData('files', filters)
                    ]);

                    this.updateMainCards(main);
                    this.renderChart('lines', lines);
                    this.renderChart('timeline', timeline);
                    this.renderChart('files', files);

                } catch (error) {
                    console.error('Error detallado:', error);
                    alert('Error al cargar datos: ' + error.message);
                }
            }

            async fetchData(type, filters = {}) {
                const params = new URLSearchParams({
                    type,
                    filters: JSON.stringify(filters)
                });

                const response = await fetch(`endpoints/reportes.php?${params}`);
                const text = await response.text();
                console.log(`[${type}] respuesta cruda:`, text); // **Depuración**

                const data = JSON.parse(text);
                if (data.status !== 'success') {
                    console.error(`[${type}] payload JSON parseado:`, data);
                    throw new Error(data.message);
                }
                return data.data;
            }

            updateMainCards(data) {
                // Helper para formatear bytes en unidades legibles
                const formatBytes = (bytes) => {
                    if (bytes >= 1024 ** 3) {
                        return (bytes / 1024 ** 3).toFixed(2) + ' GB';
                    } else if (bytes >= 1024 ** 2) {
                        return (bytes / 1024 ** 2).toFixed(2) + ' MB';
                    } else if (bytes >= 1024) {
                        return (bytes / 1024).toFixed(2) + ' KB';
                    } else {
                        return bytes + ' bytes';
                    }
                };

                // Aplicamos el formateo
                const espacioStr = formatBytes(data.espacio_total);

                const statsHtml = `
        <div class="col-md-4 mb-4">
            <div class="card card-stat bg-primary text-white">
                <div class="card-body">
                    <h5>Proyectos Totales</h5>
                    <h2>${data.total_proyectos}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card card-stat bg-success text-white">
                <div class="card-body">
                    <h5>Líneas de Investigación</h5>
                    <h2>${data.lineas_unicas}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card card-stat bg-info text-white">
                <div class="card-body">
                    <h5>Espacio en Archivos</h5>
                    <h2>${espacioStr}</h2>
                </div>
            </div>
        </div>
    `;
                document.getElementById('mainStats').innerHTML = statsHtml;
            }
            renderChart(type, data) {
                if (this.charts[type]) this.charts[type].destroy();

                const ctx = document.getElementById(
                    `chart${type.charAt(0).toUpperCase() + type.slice(1)}`
                );

                // SELECCIÓN DE TIPO DE GRÁFICO
                let chartType;
                if (type === 'lines') chartType = 'doughnut';
                else if (type === 'files') chartType = 'bar'; // ← antes era 'line'
                else chartType = 'line';

                this.charts[type] = new Chart(ctx, {
                    type: chartType,
                    data: this.prepareChartData(type, data),
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: this.getChartTitle(type)
                            }
                        }
                    }
                });
            }

            prepareChartData(type, data) {
                const configs = {
                    lines: {
                        labels: data.map(item => item.linea_investigacion),
                        datasets: [{
                            data: data.map(item => Number(item.total)), // ← Number()
                            backgroundColor: ['#4dc9f6', '#f67019', '#f53794', '#537bc4', '#acc236']
                        }]
                    },
                    timeline: {
                        labels: data.map(item => item.periodo),
                        datasets: [{
                            label: 'Proyectos por Mes',
                            data: data.map(item => Number(item.total_proyectos)),
                            borderColor: '#3e95cd',
                            fill: false
                        }]
                    },
                    files: {
                        labels: data.map(item => item.tipo),
                        datasets: [{
                            label: 'Archivos por Tipo',
                            data: data.map(item => Number(item.cantidad)), // ← Number()
                            backgroundColor: '#3cba9f'
                        }]
                    }
                };
                return configs[type];
            }

            getChartTitle(type) {
                const titles = {
                    lines: 'Distribución por Línea de Investigación',
                    timeline: 'Evolución Temporal de Proyectos',
                    files: 'Archivos por Tipo'
                };
                return titles[type];
            }
        }

        // Inicialización
        document.addEventListener('DOMContentLoaded', () => new StatsDashboard());
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('fechaInicio');
            const currentYear = new Date().getFullYear();
            const startYear = 2000; // o el que quieras

            // Insertamos opciones del currentYear hacia atrás
            for (let y = currentYear; y >= startYear; y--) {
                const opt = document.createElement('option');
                opt.value = `${y}-01-01`; // para enviar al backend como “YYYY-01-01”
                opt.textContent = y;
                select.appendChild(opt);
            }
        });
    </script>

</body>
</html>