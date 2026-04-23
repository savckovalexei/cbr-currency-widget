<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Курсы валют ЦБ РФ</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .widget-card {
            max-width: 500px;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .widget-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .rate-value {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .change-badge {
            font-size: 0.9rem;
            padding: 4px 8px;
            border-radius: 6px;
        }
        
        .trend-up {
            color: #198754;
            background-color: #d1e7dd;
        }
        
        .trend-down {
            color: #dc3545;
            background-color: #f8d7da;
        }
        
        .trend-neutral {
            color: #6c757d;
            background-color: #e9ecef;
        }
        
        .update-info {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        
        .status-live {
            background-color: #198754;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s;
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <!-- Виджет -->
                <div class="card widget-card shadow">
                    <!-- Заголовок -->
                    <div class="widget-header text-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-currency-exchange me-2"></i>
                                Курсы валют ЦБ РФ
                            </h5>
                            <span class="badge bg-light text-dark">
                                <span class="status-indicator status-live"></span>
                                Live
                            </span>
                        </div>
                    </div>
                    
                    <!-- Тело виджета -->
                    <div class="card-body p-0">
                        <div id="rates-container" class="p-3">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Загрузка...</span>
                                </div>
                                <p class="mt-2 text-muted">Загрузка курсов...</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Подвал -->
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="update-info">
                                <i class="bi bi-clock me-1"></i>
                                Обновление через: 
                                <span id="countdown" class="fw-bold">60</span> сек
                            </div>
                            <a href="/admin/settings" 
                               class="btn btn-outline-secondary btn-sm" 
                               title="Настройки">Настройки
                                <i class="bi bi-gear"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Информация об ошибке (скрыта по умолчанию) -->
                <div id="error-alert" 
                     class="alert alert-danger mt-3 d-none fade show" 
                     role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="error-message"></span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let updateInterval = 60;
        let countdownValue = 60;
        let countdownTimer = null;
        let updateTimer = null;

        // Загрузка настроек
        async function fetchSettings() {
            try {
                const response = await fetch('/api/settings');
                if (!response.ok) throw new Error('Ошибка загрузки настроек');
                
                const data = await response.json();
                updateInterval = data.update_interval || 60;
                countdownValue = updateInterval;
                updateCountdown();
            } catch (e) {
                console.error('Ошибка загрузки настроек:', e);
                showError('Не удалось загрузить настройки');
            }
        }

        // Загрузка курсов
        async function loadRates() {
            try {
                const response = await fetch('/api/rates');
                if (!response.ok) throw new Error('Ошибка загрузки курсов');
                
                const rates = await response.json();
                renderRates(rates);
                hideError();
                resetCountdown();
            } catch (e) {
                console.error('Ошибка загрузки курсов:', e);
                showError('Не удалось загрузить курсы валют');
            }
        }

        // Отрисовка таблицы с курсами
        function renderRates(rates) {
            const container = document.getElementById('rates-container');
            
            if (!rates || rates.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                        <p class="mt-2 text-muted">Нет данных для отображения</p>
                    </div>
                `;
                return;
            }

            let html = `
                <div class="table-responsive fade-in">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Валюта</th>
                                <th class="text-end">Курс (₽)</th>
                                <th class="text-end">Изменение</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            rates.forEach(rate => {
                const trendClass = rate.trend === 'up' ? 'trend-up' : 
                                  (rate.trend === 'down' ? 'trend-down' : 'trend-neutral');
                
                const arrowIcon = rate.trend === 'up' ? 'bi-arrow-up' : 
                                 (rate.trend === 'down' ? 'bi-arrow-down' : 'bi-dash');
                
                const changePrefix = rate.change > 0 ? '+' : '';
                const changeText = rate.change !== null ? 
                    `${changePrefix}${rate.change.toFixed(4)}` : '—';

                html += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-2">${rate.char_code}</span>
                                <span class="text-muted small">${rate.name}</span>
                            </div>
                        </td>
                        <td class="text-end rate-value">${rate.value}</td>
                        <td class="text-end">
                            <span class="change-badge ${trendClass}">
                                <i class="bi ${arrowIcon} me-1"></i>
                                ${changeText}
                            </span>
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            container.innerHTML = html;
        }

        // Таймер обратного отсчета
        function updateCountdown() {
            document.getElementById('countdown').textContent = countdownValue;
        }

        function resetCountdown() {
            countdownValue = updateInterval;
            updateCountdown();
            
            if (countdownTimer) clearInterval(countdownTimer);
            
            countdownTimer = setInterval(() => {
                countdownValue--;
                updateCountdown();
                
                if (countdownValue <= 0) {
                    loadRates();
                }
            }, 1000);
        }

        function scheduleUpdate() {
            if (updateTimer) clearTimeout(updateTimer);
            
            updateTimer = setTimeout(() => {
                loadRates();
            }, updateInterval * 1000);
        }

        // Обработка ошибок
        function showError(message) {
            const alert = document.getElementById('error-alert');
            document.getElementById('error-message').textContent = message;
            alert.classList.remove('d-none');
        }

        function hideError() {
            const alert = document.getElementById('error-alert');
            alert.classList.add('d-none');
        }

        // Инициализация
        (async function init() {
            await fetchSettings();
            await loadRates();
            resetCountdown();
        })();
    </script>
</body>
</html>