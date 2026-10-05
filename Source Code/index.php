<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verity - Anti-Buddy Punching System</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --verity-yellow: #facc15;
            --verity-purple: #8b5cf6;
            --bg-obsidian: #09090b;
            --bg-sidebar: #121215;
            --bg-card: #18181b;
            --bg-elevated: #27272a;
            --border-color: #27272a;
            --border-highlight: #3f3f46;
            --text-primary: #f4f4f5;
            --text-muted: #a1a1aa;
            --status-danger: #f43f5e;
            --status-success: #10b981;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background-color: var(--bg-obsidian); color: var(--text-primary); min-height: 100vh; display: flex; overflow-x: hidden; }

        .toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 99999; display: flex; flex-direction: column; gap: 10px; }
        .toast { background-color: var(--bg-elevated); color: var(--text-primary); padding: 14px 20px; border-radius: 10px; border: 1px solid var(--border-highlight); font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .toast.success { border-left: 4px solid var(--status-success); }
        .toast.danger { border-left: 4px solid var(--status-danger); }

        .modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(4px); z-index: 10000; display: none; align-items: center; justify-content: center; padding: 20px; }
        .modal-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-top: 4px solid var(--verity-purple); border-radius: 16px; padding: 28px; width: 100%; max-width: 480px; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }

        .login-screen { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: var(--bg-obsidian); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-top: 4px solid var(--verity-yellow); border-radius: 16px; padding: 35px 30px; width: 100%; max-width: 400px; text-align: center; }

        .input-group { text-align: left; margin-bottom: 15px; }
        .input-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; }
        .input-group input, .input-group select { width: 100%; padding: 10px 12px; background-color: var(--bg-obsidian); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-size: 13px; }

        .app-container { display: none; width: 100vw; min-height: 100vh; }
        aside.sidebar { width: 260px; background-color: var(--bg-sidebar); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; justify-content: space-between; padding: 20px 15px; flex-shrink: 0; }
        .sidebar-brand { display: flex; align-items: center; gap: 12px; font-size: 22px; font-weight: 800; color: var(--verity-yellow); padding: 10px 10px 25px 10px; border-bottom: 1px solid var(--border-color); }

        .nav-menu { display: flex; flex-direction: column; gap: 6px; margin-top: 20px; flex: 1; }
        .nav-item { display: flex; align-items: center; gap: 14px; padding: 12px 16px; color: var(--text-muted); background: none; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; text-align: left; width: 100%; }
        .nav-item:hover { color: var(--text-primary); background-color: var(--bg-card); }
        .nav-item.active { color: #000000; background-color: var(--verity-yellow); }

        main.workspace { flex: 1; display: flex; flex-direction: column; overflow-y: auto; max-height: 100vh; }
        header.top-bar { height: 70px; background-color: var(--bg-obsidian); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 0 30px; position: sticky; top: 0; z-index: 10; }

        .search-wrapper { position: relative; width: 320px; }
        .search-wrapper input { width: 100%; padding: 8px 12px 8px 38px; background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); }
        .search-wrapper i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }

        .content-area { padding: 30px; max-width: 1300px; width: 100%; margin: 0 auto; }
        .page-view { display: none; }
        .page-view.active-view { display: block; }
        .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; }

        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .grid-charts { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 25px; }
        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; }
        .stat-value { font-size: 32px; font-weight: 800; margin: 8px 0; }

        .btn { background-color: var(--verity-yellow); color: #000000; padding: 9px 18px; border: none; border-radius: 8px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .btn-purple { background-color: var(--verity-purple); color: #ffffff; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }

        .strike-pill { padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 800; background-color: rgba(244, 63, 94, 0.15); color: var(--status-danger); }
        .strike-pill.safe { background-color: rgba(16, 185, 129, 0.15); color: var(--status-success); }

        .table-responsive { width: 100%; overflow-x: auto; border-radius: 12px; border: 1px solid var(--border-color); }
        table { width: 100%; border-collapse: separate; border-spacing: 0; background-color: var(--bg-card); }
        th, td { padding: 14px 18px; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 13px; }
        th { background-color: #0d0d10; color: var(--text-muted); font-size: 11px; text-transform: uppercase; }
    </style>
</head>
<body>

    <div id="toast-container" class="toast-container"></div>

    <!-- DYNAMIC MANUAL SCAN MODAL -->
    <div id="scan-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fa-solid fa-camera-retro" style="color: var(--verity-yellow);"></i> Manual Entry Terminal</h3>
                <button onclick="closeScanModal()" style="background:none; border:none; color: var(--text-muted); font-size:18px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form onsubmit="handleManualScanSubmit(event)">
                <div class="input-group">
                    <label>Select Employee</label>
                    <select id="scan-employee-id" required></select>
                </div>
                <div class="input-group">
                    <label>Station Terminal</label>
                    <select id="scan-station">
                        <option value="Main Pharmacy">Main Pharmacy</option>
                        <option value="ER Triage Desk">ER Triage Desk</option>
                        <option value="Security Gate">Security Gate</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Verification Method</label>
                    <select id="scan-method">
                        <option value="Biometric Facial Scan">Biometric Facial Scan</option>
                        <option value="Manual Override">Manual Override Passcode</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Scan Timestamp (Auto-Calculates Late Minutes)</label>
                    <input type="datetime-local" id="scan-datetime" required>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn" style="background-color: var(--bg-elevated); color: var(--text-primary); flex: 1;" onclick="closeScanModal()">Cancel</button>
                    <button type="submit" class="btn btn-purple" style="flex: 2; justify-content: center;"><i class="fa-solid fa-database"></i> Process Scan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- LOGIN OVERLAY -->
    <div id="login-screen" class="login-screen">
        <div class="login-card">
            <div style="font-size: 28px; font-weight: 800; color: var(--verity-yellow); margin-bottom: 15px;"><i class="fa-solid fa-face-smile"></i> VERITY</div>
            <h3>Supervisor Authorization</h3>
            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 20px;">Enter credentials to access MySQL database controls.</p>
            <form onsubmit="handleLogin(event)">
                <div class="input-group">
                    <label>Node ID</label>
                    <input type="text" id="node-id" placeholder="MGR-8802" required>
                </div>
                <div class="input-group">
                    <label>Passcode</label>
                    <input type="password" id="node-pass" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-purple" style="width: 100%; justify-content: center; margin-top: 10px;"><i class="fa-solid fa-lock"></i> Authorize Session</button>
            </form>
        </div>
    </div>

    <!-- MAIN APP SHELL -->
    <div id="app-container" class="app-container">
        <aside class="sidebar">
            <div>
                <div class="sidebar-brand"><i class="fa-solid fa-face-smile"></i> VERITY</div>
                <nav class="nav-menu">
                    <button class="nav-item active" onclick="switchPage('dashboard')"><i class="fa-solid fa-chart-pie"></i> Executive Overview</button>
                    <button class="nav-item" onclick="switchPage('audit-queue')"><i class="fa-solid fa-clock-rotate-left"></i> Vacant Time Queue</button>
                    <button class="nav-item" onclick="switchPage('strikes-payroll')"><i class="fa-solid fa-bolt-lightning"></i> Strikes & Payroll</button>
                    <button class="nav-item" onclick="switchPage('master-records')"><i class="fa-solid fa-database"></i> Master Logs</button>
                    <button class="nav-item" onclick="switchPage('audit-reports')"><i class="fa-solid fa-file-invoice-dollar"></i> Compliance Reports</button>
                </nav>
            </div>
            <div style="background-color: var(--bg-card); padding: 12px; border-radius: 10px; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background-color: var(--verity-purple); display: flex; align-items: center; justify-content: center; font-weight: 800;">MGR</div>
                    <div>
                        <div style="font-size: 13px; font-weight: 800;" id="user-display-node">Supervisor Node</div>
                        <div style="font-size: 11px; color: var(--text-muted);">MySQL Connected</div>
                    </div>
                </div>
                <button onclick="handleLogout()" style="background:none; border:none; color:var(--text-muted); cursor:pointer;"><i class="fa-solid fa-right-from-bracket"></i></button>
            </div>
        </aside>

        <main class="workspace">
            <header class="top-bar">
                <div class="search-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="global-search" placeholder="Search logs or employees..." oninput="filterTables()">
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="font-size: 12px; color: var(--text-muted);"><i class="fa-solid fa-circle" style="color: var(--status-success); font-size: 8px;"></i> Port 3307 Active</span>
                    <button class="btn btn-purple btn-sm" onclick="openScanModal()"><i class="fa-solid fa-plus"></i> Manual Scan</button>
                </div>
            </header>

            <div class="content-area">
                <!-- VIEW 1: DASHBOARD -->
                <section id="page-dashboard" class="page-view active-view">
                    <div class="page-header"><h2>Executive Dashboard</h2></div>
                    <div class="grid-3">
                        <div class="card"><span style="font-size:12px; color:var(--text-muted); font-weight:700;">SHIFT SCANS TODAY</span><div class="stat-value" id="stat-scans" style="color: var(--verity-yellow);">0</div></div>
                        <div class="card"><span style="font-size:12px; color:var(--text-muted); font-weight:700;">WEEKLY STRIKES ISSUED</span><div class="stat-value" id="stat-strikes" style="color: var(--status-danger);">0</div></div>
                        <div class="card"><span style="font-size:12px; color:var(--text-muted); font-weight:700;">PAYROLL DEDUCTION BALANCE</span><div class="stat-value" id="stat-payroll" style="color: var(--verity-purple);">₱0.00</div></div>
                    </div>
                    <div class="grid-charts">
                        <div class="card">
                            <h4 style="margin-bottom: 15px;"><i class="fa-solid fa-chart-bar" style="color: var(--verity-yellow);"></i> Peak Check-In Volume (Dynamic)</h4>
                            <div style="height: 200px;"><canvas id="chart-peak-hours"></canvas></div>
                        </div>
                        <div class="card">
                            <h4 style="margin-bottom: 15px;"><i class="fa-solid fa-chart-pie" style="color: var(--verity-purple);"></i> Anomaly Breakdown (Dynamic)</h4>
                            <div style="height: 200px;"><canvas id="chart-anomalies"></canvas></div>
                        </div>
                    </div>
                </section>

                <!-- VIEW 2: VACANT QUEUE -->
                <section id="page-audit-queue" class="page-view">
                    <div class="page-header"><h2>Vacant Time Queue</h2></div>
                    <div class="table-responsive">
                        <table>
                            <thead><tr><th>Time</th><th>Employee</th><th>Department</th><th>Anomaly</th><th>Strikes</th><th>Actions</th></tr></thead>
                            <tbody id="queue-tbody"></tbody>
                        </table>
                    </div>
                </section>

                <!-- VIEW 3: STRIKES & PAYROLL -->
                <section id="page-strikes-payroll" class="page-view">
                    <div class="page-header"><h2>Strikes & Payroll Adjustments</h2></div>
                    <div class="table-responsive">
                        <table>
                            <thead><tr><th>Employee</th><th>Department</th><th>Shift Start</th><th>Actual</th><th>Strikes</th><th>Net Deduction</th></tr></thead>
                            <tbody id="payroll-tbody"></tbody>
                        </table>
                    </div>
                </section>

                <!-- VIEW 4: MASTER LOGS -->
                <section id="page-master-records" class="page-view">
                    <div class="page-header"><h2>Master Verification Database</h2></div>
                    <div class="table-responsive">
                        <table>
                            <thead><tr><th>Log ID</th><th>Timestamp</th><th>Employee</th><th>Station</th><th>Method</th><th>Status</th></tr></thead>
                            <tbody id="master-tbody"></tbody>
                        </table>
                    </div>
                </section>

                <!-- VIEW 5: COMPLIANCE REPORTS -->
                <section id="page-audit-reports" class="page-view">
                    <div class="page-header"><h2>Compliance Audit Summary</h2></div>
                    <div class="table-responsive">
                        <table>
                            <thead><tr><th>Employee ID</th><th>Name</th><th>Strikes Penalty</th><th>Hours Short</th><th>Total Adjustment</th></tr></thead>
                            <tbody id="report-tbody"></tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

<script>
    let chartPeakInstance = null;
    let chartAnomalyInstance = null;

    async function fetchDatabaseData() {
        try {
            const response = await fetch('api.php');
            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
            return await response.json();
        } catch (err) {
            console.error('Fetch Error:', err);
            showToast('Database connection failed: ' + err.message, 'danger');
            return null;
        }
    }

    async function refreshUI() {
        const db = await fetchDatabaseData();
        if (!db) return;

        // Stats Card Safety Checks
        if (db.stats) {
            document.getElementById('stat-scans').innerText = db.stats.totalScans ?? 0;
            document.getElementById('stat-strikes').innerText = db.stats.weeklyStrikes ?? 0;
            document.getElementById('stat-payroll').innerText = '₱' + (Number(db.stats.payrollBalance) || 0).toFixed(2);
        }

        // Employee Dropdown
        const selectEmp = document.getElementById('scan-employee-id');
        if (selectEmp && db.employees) {
            selectEmp.innerHTML = db.employees.map(e => `<option value="${e.id}">${e.name} (Shift Start: ${e.shift_start})</option>`).join('');
        }

        // Vacant Queue
        const queueTbody = document.getElementById('queue-tbody');
        if (queueTbody) {
            queueTbody.innerHTML = (db.queue && db.queue.length) ? db.queue.map(item => `
                <tr>
                    <td>${item.time}</td>
                    <td>${item.empName} (${item.empId})</td>
                    <td>${item.dept}</td>
                    <td><span style="color:var(--status-danger); font-weight:700;">${item.anomaly}</span></td>
                    <td><span class="strike-pill">${item.strikes}</span></td>
                    <td><button class="btn btn-purple btn-sm" onclick="clearQueueItem(${item.id})">Clear</button></td>
                </tr>
            `).join('') : `<tr><td colspan="6" style="text-align:center; color:var(--text-muted);">Queue cleared</td></tr>`;
        }

        // Payroll Table
        const payrollTbody = document.getElementById('payroll-tbody');
        if (payrollTbody && db.employees) {
            payrollTbody.innerHTML = db.employees.map(e => {
                const shortHours = Math.max(0, Number(e.schedule_hours) - Number(e.actual_hours));
                const rate = Number(e.hourly_rate) || 150;
                const penalty = Number(e.strike_penalty) || 200;
                const deduction = (shortHours * rate) + (Number(e.strikes) * penalty);
                return `
                    <tr>
                        <td><strong>${e.name}</strong> (${e.id})</td>
                        <td>${e.department}</td>
                        <td>${e.shift_start}</td>
                        <td>${e.actual_hours} hrs</td>
                        <td><span class="strike-pill ${e.strikes == 0 ? 'safe' : ''}">${e.strikes}</span></td>
                        <td style="color: var(--status-danger); font-weight: 800;">-₱${deduction.toFixed(2)}</td>
                    </tr>
                `;
            }).join('');
        }

        // Master Logs
        const masterTbody = document.getElementById('master-tbody');
        if (masterTbody && db.masterLogs) {
            masterTbody.innerHTML = db.masterLogs.map(log => `
                <tr>
                    <td>${log.logId}</td>
                    <td>${log.timestamp}</td>
                    <td>${log.employee}</td>
                    <td>${log.station}</td>
                    <td>${log.method}</td>
                    <td><span style="color:${log.statusColor}; font-weight:bold;">${log.status}</span></td>
                </tr>
            `).join('');
        }

        // Reports Table
        const reportTbody = document.getElementById('report-tbody');
        if (reportTbody && db.employees) {
            reportTbody.innerHTML = db.employees.map(e => {
                const shortHours = Math.max(0, Number(e.schedule_hours) - Number(e.actual_hours));
                const rate = Number(e.hourly_rate) || 150;
                const penalty = Number(e.strike_penalty) || 200;
                const totalDeduction = (shortHours * rate) + (Number(e.strikes) * penalty);
                return `
                    <tr>
                        <td>${e.id}</td>
                        <td>${e.name}</td>
                        <td>₱${(Number(e.strikes) * penalty).toFixed(2)} (${e.strikes} strikes)</td>
                        <td>${shortHours.toFixed(1)} hrs (-₱${(shortHours * rate).toFixed(2)})</td>
                        <td style="color: var(--status-danger); font-weight:800;">-₱${totalDeduction.toFixed(2)}</td>
                    </tr>
                `;
            }).join('');
        }

        // Safe Chart Updates
        if (chartPeakInstance && db.chartPeakData) {
            chartPeakInstance.data.labels = db.chartPeakLabels;
            chartPeakInstance.data.datasets[0].data = db.chartPeakData;
            chartPeakInstance.update();
        }

        if (chartAnomalyInstance && db.chartAnomalyData) {
            chartAnomalyInstance.data.datasets[0].data = db.chartAnomalyData;
            chartAnomalyInstance.update();
        }
    }

    async function handleLogin(e) {
        e.preventDefault();
        const nodeId = document.getElementById('node-id').value;
        const password = document.getElementById('node-pass').value;

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'login', nodeId: nodeId, password: password })
            });

            // Parse response body safely
            const rawText = await response.text();
            let result;
            try {
                result = JSON.parse(rawText);
            } catch (pErr) {
                console.error("Non-JSON Server Response:", rawText);
                throw new Error("Server output invalid response text");
            }

            if (response.ok && result.success) {
                localStorage.setItem('verity_logged_in', 'true');
                localStorage.setItem('verity_user', JSON.stringify(result.user));

                document.getElementById('user-display-node').innerText = result.user.name;
                document.getElementById('login-screen').style.display = 'none';
                document.getElementById('app-container').style.display = 'flex';
                
                initCharts();
                await refreshUI();
                showToast(`Authorized: Welcome ${result.user.name}`, 'success');
            } else {
                showToast(result.error || 'Authorization failed', 'danger');
            }
        } catch (err) {
            console.error('Login Detailed Error:', err);
            showToast('Login Error: ' + err.message, 'danger');
        }
    }

    async function handleManualScanSubmit(e) {
        e.preventDefault();
        const dtInput = document.getElementById('scan-datetime').value;
        const sqlFormattedDate = dtInput ? dtInput.replace('T', ' ') + ':00' : '';

        const data = {
            empId: document.getElementById('scan-employee-id').value,
            station: document.getElementById('scan-station').value,
            method: document.getElementById('scan-method').value,
            scanTime: sqlFormattedDate
        };

        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        closeScanModal();

        if (result.isLate) {
            showToast(`Anomaly Detected: ${result.status}! Strike Issued.`, 'danger');
        } else {
            showToast(`Clean Scan Recorded (${result.status})`, 'success');
        }

        refreshUI();
    }

    async function clearQueueItem(id) {
        await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'clear_queue', id: id })
        });
        showToast('Queue item cleared', 'success');
        refreshUI();
    }

    function handleLogout() {
        localStorage.clear();
        document.getElementById('app-container').style.display = 'none';
        document.getElementById('login-screen').style.display = 'flex';
        document.getElementById('node-id').value = '';
        document.getElementById('node-pass').value = '';
    }

    function openScanModal() { 
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('scan-datetime').value = now.toISOString().slice(0, 16);
        document.getElementById('scan-modal').style.display = 'flex'; 
    }

    function closeScanModal() { document.getElementById('scan-modal').style.display = 'none'; }

    function switchPage(pageId) {
        document.querySelectorAll('.page-view').forEach(view => view.classList.remove('active-view'));
        document.querySelectorAll('.nav-item').forEach(btn => btn.classList.remove('active'));
        document.getElementById('page-' + pageId).classList.add('active-view');
        event.currentTarget.classList.add('active');
    }

    function filterTables() {
        const query = document.getElementById('global-search').value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
        });
    }

    function showToast(msg, type='success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `<span>${msg}</span>`;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }

    function initCharts() {
        if (typeof Chart === 'undefined') {
            console.warn("Chart.js missing or failed to load.");
            return;
        }

        const ctxPeakEl = document.getElementById('chart-peak-hours');
        if (ctxPeakEl && !chartPeakInstance) {
            chartPeakInstance = new Chart(ctxPeakEl.getContext('2d'), {
                type: 'bar',
                data: { labels: [], datasets: [{ label: 'Check-In Volume', data: [], backgroundColor: '#facc15' }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        const ctxAnomalyEl = document.getElementById('chart-anomalies');
        if (ctxAnomalyEl && !chartAnomalyInstance) {
            chartAnomalyInstance = new Chart(ctxAnomalyEl.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Verified', 'Late', 'Rapid', 'Other'],
                    datasets: [{ data: [0, 0, 0, 0], backgroundColor: ['#10b981', '#f43f5e', '#fbbf24', '#8b5cf6'] }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        if (localStorage.getItem('verity_logged_in') === 'true') {
            const user = JSON.parse(localStorage.getItem('verity_user') || '{}');
            if (user.name) {
                document.getElementById('user-display-node').innerText = user.name;
            }
            document.getElementById('login-screen').style.display = 'none';
            document.getElementById('app-container').style.display = 'flex';
            initCharts();
            refreshUI();
        }
    });
</script>
</body>
</html>