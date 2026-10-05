<?php
header('Content-Type: application/json');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        // 1. Dynamic Stats
        $scansCount = $pdo->query("SELECT COUNT(*) FROM scan_logs WHERE DATE(timestamp) = CURDATE()")->fetchColumn() ?: 0;
        $strikesCount = $pdo->query("SELECT SUM(strikes) FROM employees")->fetchColumn() ?: 0;

        $employees = $pdo->query("SELECT * FROM employees")->fetchAll() ?: [];
        $masterLogs = $pdo->query("SELECT log_id AS logId, DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i:%s') AS timestamp, employee, station, method, status, status_color AS statusColor FROM scan_logs ORDER BY id DESC LIMIT 50")->fetchAll() ?: [];
        $queue = $pdo->query("SELECT id, time, emp_name AS empName, emp_id AS empId, dept, anomaly, strikes FROM vacant_queue ORDER BY id DESC")->fetchAll() ?: [];

        // 2. Dynamic Payroll Calculations
        $payrollBalance = 0;
        foreach ($employees as $emp) {
            $shortHours = max(0, $emp['schedule_hours'] - $emp['actual_hours']);
            $rate = $emp['hourly_rate'] ?? 150.00;
            $penalty = $emp['strike_penalty'] ?? 200.00;
            $deduction = ($shortHours * $rate) + ($emp['strikes'] * $penalty);
            $payrollBalance += $deduction;
        }

        // 3. Dynamic Peak Hours Chart (Aggregated by Hour today: 6 AM - 6 PM)
		$peakHoursQuery = $pdo->query("
			SELECT HOUR(timestamp) as hr, COUNT(*) as total 
			FROM scan_logs 
			WHERE DATE(timestamp) = CURDATE() 
			GROUP BY HOUR(timestamp) 
			ORDER BY hr ASC
		")->fetchAll();

		$peakLabels = [
			'06:00 AM', '07:00 AM', '08:00 AM', '09:00 AM', '10:00 AM', '11:00 AM', 
			'12:00 PM', '01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM', '05:00 PM', '06:00 PM'
		];

		$peakDataMap = [
			6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 
			12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0, 17 => 0, 18 => 0
		];

		foreach ($peakHoursQuery as $row) {
			$h = (int)$row['hr'];
			if (array_key_exists($h, $peakDataMap)) {
        $peakDataMap[$h] = (int)$row['total'];
			}
		}
		$chartPeakData = array_values($peakDataMap);

        // 4. Dynamic Anomaly Chart (Aggregated by Status)
        $verifiedCount = $pdo->query("SELECT COUNT(*) FROM scan_logs WHERE status = 'Verified'")->fetchColumn() ?: 0;
        $lateCount = $pdo->query("SELECT COUNT(*) FROM scan_logs WHERE status LIKE 'Late%'")->fetchColumn() ?: 0;
        $rapidCount = $pdo->query("SELECT COUNT(*) FROM scan_logs WHERE status LIKE 'Rapid%'")->fetchColumn() ?: 0;
        $otherCount = $pdo->query("SELECT COUNT(*) FROM scan_logs WHERE status NOT LIKE 'Late%' AND status NOT LIKE 'Rapid%' AND status != 'Verified'")->fetchColumn() ?: 0;

        echo json_encode([
            'stats' => [
                'totalScans' => (int)$scansCount,
                'weeklyStrikes' => (int)$strikesCount,
                'payrollBalance' => (float)$payrollBalance
            ],
            'chartPeakLabels' => $peakLabels,
            'chartPeakData' => $chartPeakData,
            'chartAnomalyData' => [(int)$verifiedCount, (int)$lateCount, (int)$rapidCount, (int)$otherCount],
            'employees' => $employees,
            'queue' => $queue,
            'masterLogs' => $masterLogs
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $action = $input['action'] ?? '';

        // LOGIN VALIDATION
        if ($action === 'login') {
            $nodeId = trim($input['nodeId'] ?? '');
            $password = trim($input['password'] ?? '');

            $stmt = $pdo->prepare("SELECT * FROM users WHERE node_id = ?");
            $stmt->execute([$nodeId]);
            $user = $stmt->fetch();

            if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Authorization successful',
                    'user' => ['nodeId' => $user['node_id'], 'name' => $user['name'], 'role' => $user['role']]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid Node ID or Passcode']);
            }
            exit();
        }

        // CLEAR QUEUE ITEM
        if ($action === 'clear_queue') {
            $stmt = $pdo->prepare("DELETE FROM vacant_queue WHERE id = ?");
            $stmt->execute([$input['id']]);
            echo json_encode(['success' => true, 'message' => 'Queue item cleared']);
            exit();
        }

        // DYNAMIC SCAN PROCESSING
        $empId = $input['empId'] ?? '';
        $station = $input['station'] ?? 'Main Terminal';
        $methodType = $input['method'] ?? 'Biometric Facial Scan';
        $scanTimestamp = !empty($input['scanTime']) ? $input['scanTime'] : date('Y-m-d H:i:s');

        // Fetch employee shift start time
        $empStmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
        $empStmt->execute([$empId]);
        $emp = $empStmt->fetch();

        if (!$emp) {
            http_response_code(404);
            echo json_encode(['error' => 'Employee not found']);
            exit();
        }

        $fullEmpString = $emp['name'] . " (" . $emp['id'] . ")";
        $logId = 'LOG-' . rand(1000, 9999);

        // Calculate late time dynamically: Scan Time vs Shift Start Time
        $scanTimeUnix = strtotime($scanTimestamp);
        $shiftStartUnix = strtotime(date('Y-m-d', $scanTimeUnix) . ' ' . $emp['shift_start']);

        $status = 'Verified';
        $statusColor = '#10b981';
        $isLate = false;

        if ($scanTimeUnix > $shiftStartUnix) {
            $minutesLate = (int)round(($scanTimeUnix - $shiftStartUnix) / 60);
            if ($minutesLate > 0) {
                $status = "Late (+" . $minutesLate . "m)";
                $statusColor = '#f43f5e';
                $isLate = true;
            }
        }

        // Save scan log
        $insertStmt = $pdo->prepare("INSERT INTO scan_logs (log_id, timestamp, employee, station, method, status, status_color) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->execute([$logId, $scanTimestamp, $fullEmpString, $station, $methodType, $status, $statusColor]);

        // If late, dynamically update strikes and add to vacant queue
        if ($isLate) {
            $updateEmp = $pdo->prepare("UPDATE employees SET strikes = strikes + 1 WHERE id = ?");
            $updateEmp->execute([$empId]);

            $newStrikes = $emp['strikes'] + 1;
            $formattedTime = date('h:i A', $scanTimeUnix);

            $insertQueue = $pdo->prepare("INSERT INTO vacant_queue (time, emp_name, emp_id, dept, anomaly, strikes) VALUES (?, ?, ?, ?, ?, ?)");
            $insertQueue->execute([$formattedTime, $emp['name'], $emp['id'], $emp['department'], $status, $newStrikes]);
        }

        echo json_encode([
            'success' => true, 
            'message' => 'Scan processed dynamically', 
            'status' => $status,
            'isLate' => $isLate
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>