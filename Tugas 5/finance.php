<?php
declare(strict_types=1);
session_start();

// Menghubungkan kelas Transaction
require_once 'Transaction.php';

// Inisialisasi struktur data di sesi jika belum ada
if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 0.0;
}
if (!isset($_SESSION['history'])) {
    $_SESSION['history'] = [];
}

// Menghasilkan Token CSRF untuk keamanan formulir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$message = '';

// Memproses data jika ada metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validasi CSRF
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
        die('Aksi tidak sah: Token CSRF tidak valid.');
    }

    $rawType = $_POST['type'] ?? '';
    $rawAmount = $_POST['amount'] ?? '';

    // 2. Gunakan ekspresi match untuk mencocokkan dan memvalidasi jenis transaksi
    $validatedType = match ($rawType) {
        'deposit', 'withdraw' => $rawType,
        default => null,
    };

    // 3. Validasi jumlah transaksi sebagai angka desimal positif
    $isAmountValid = is_numeric($rawAmount) && (float)$rawAmount > 0;

    if ($validatedType === null) {
        $message = "Error: Jenis transaksi tidak valid.";
    } elseif (!$isAmountValid) {
        $message = "Error: Jumlah transaksi harus berupa angka desimal positif.";
    } else {
        // Data bersih, siap diproses
        $amount = (float)$rawAmount;
        $transactionId = uniqid('TXN-');

        $transaction = new Transaction($transactionId, $validatedType, $amount);
        
        // Memproses transaksi dan mengubah saldo sesi
        $isSuccess = $transaction->process($_SESSION['balance']);

        if ($isSuccess) {
            // Mencatat riwayat
            $_SESSION['history'][] = [
                'id' => $transaction->getId(),
                'type' => $transaction->getType(),
                'amount' => $transaction->getAmount(),
                'time' => date('Y-m-d H:i:s')
            ];
            $message = "Sukses: Transaksi {$validatedType} sebesar Rp " . number_format($amount, 2) . " berhasil diproses.";
        } else {
            $message = "Gagal: Saldo tidak mencukupi untuk melakukan penarikan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Keuangan</title>
    <style>
        body { 
            font-family: sans-serif; 
            margin: 0; background-color: #f4f4f9; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            padding: 1rem; 
        }
        .container { 
            width: 100%;
            max-width: 600px; 
            background: white; 
            padding: 2rem; 
            border-radius: 8px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
        }      
        .alert { 
            padding: 1rem; 
            margin-bottom: 1rem; 
            border-radius: 4px; 
            background-color: #e2e8f0; 
        }
        .balance { 
            font-size: 1.5rem; 
            font-weight: bold; 
            color: #2d3748; 
            margin-bottom: 1.5rem; 
        }
        form { 
            display: flex; 
            flex-direction: column; 
            gap: 1rem; 
            margin-bottom: 2rem; 
        }
        input, select, button { 
            padding: 0.5rem; 
            font-size: 1rem; 
        }
        button { 
            background-color: #3182ce; 
            color: white; 
            border: none; 
            cursor: pointer; 
            border-radius: 4px; 
        }
        button:hover { 
            background-color: #2b6cb0; 
        }
        table { 
            width: 100%; 
            min-width: 500px; 
            border-collapse: collapse; 
            margin-top: 1rem; 
        }
        th, td { 
            padding: 0.75rem; 
            border: 1px solid #cbd5e0; 
            text-align: left; 
        }
        th { 
            background-color: #edf2f7; 
        }
        .table-wrapper {
            width: 100%; 
            overflow-x: auto; 
            -webkit-overflow-scrolling: touch; 
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Manajemen Keuangan Sederhana</h2>
    
    <!-- Menerapkan htmlspecialchars untuk mencegah XSS pada output -->
    <div class="balance">
        Saldo Saat Ini: Rp <?= htmlspecialchars(number_format($_SESSION['balance'], 2), ENT_QUOTES, 'UTF-8') ?>
    </div>

    <?php if ($message): ?>
        <div class="alert">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form action="finance.php" method="POST">
        <!-- Input Token CSRF tersembunyi -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        
        <div>
            <label for="type">Jenis Transaksi:</label><br>
            <select name="type" id="type" required>
                <option value="deposit">Deposit (Setor)</option>
                <option value="withdraw">Withdraw (Tarik)</option>
            </select>
        </div>

        <div>
            <label for="amount">Jumlah (Desimal Positif):</label><br>
            <input type="number" step="0.01" min="0.01" name="amount" id="amount" required>
        </div>

        <button type="submit">Proses Transaksi</button>
    </form>

    <h3>Riwayat Transaksi</h3>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Waktu</th>
                    <th>Tipe</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($_SESSION['history'])): ?>
                    <tr><td colspan="4">Belum ada transaksi.</td></tr>
                <?php else: ?>
                    <!-- Menampilkan array history dengan perlindungan XSS penuh -->
                    <?php foreach (array_reverse($_SESSION['history']) as $tx): ?>
                        <tr>
                            <td><?= htmlspecialchars($tx['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($tx['time'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(ucfirst($tx['type']), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>Rp <?= htmlspecialchars(number_format($tx['amount'], 2), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>