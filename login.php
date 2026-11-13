<?php
require_once 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identitas = trim($_POST['identitas'] ?? '');
    $password  = $_POST['password'] ?? '';

    if (empty($identitas) || empty($password)) {
        $error = "Username/Email dan password wajib diisi.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, username, password, nama_lengkap, role, is_active FROM users WHERE username = ? OR email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ss", $identitas, $identitas);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if ((int)$row['is_active'] !== 1) {
                $error = "Akun Anda sedang dinonaktifkan.";
            } elseif (password_verify($password, $row['password'])) {
                // Regenerasi session ID untuk keamanan
                session_regenerate_id(true);

                $_SESSION['user_id']      = $row['id'];
                $_SESSION['username']     = $row['username'];
                $_SESSION['nama_lengkap'] = $row['nama_lengkap'] ?? $row['username'];
                $_SESSION['role']         = $row['role'];

                // Update kolom last_login
                $update = mysqli_prepare($conn, "UPDATE users SET last_login = NOW() WHERE id = ?");
                mysqli_stmt_bind_param($update, "i", $row['id']);
                mysqli_stmt_execute($update);
                mysqli_stmt_close($update);

                header("Location: index.php");
                exit;
            } else {
                $error = "Username/Email atau password salah.";
            }
        } else {
            $error = "Username/Email atau password salah.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Habit Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold text-slate-800 mb-6 text-center">Login Habit Tracker</h1>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Username atau Email</label>
                <input type="text" name="identitas" required value="<?= htmlspecialchars($_POST['identitas'] ?? '') ?>" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg transition">Masuk</button>
        </form>

        <p class="text-sm text-center text-slate-600 mt-4">
            Belum punya akun? <a href="register.php" class="text-emerald-600 font-semibold hover:underline">Daftar sekarang</a>
        </p>
    </div>
</body>
</html>