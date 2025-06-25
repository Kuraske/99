<?php
session_start();
require_once __DIR__ . '/../src/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$username || !$password) {
        $error = 'Будь ласка, введіть логін та пароль.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username']
            ];
            header('Location: /'); // Редірект на головну сторінку після входу
            exit;
        } else {
            $error = 'Невірний логін або пароль.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8" />
    <title>Увійти</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 40px auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        input[type="text"], input[type="password"] {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #28a745;
            border: none;
            color: white;
            padding: 12px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            text-align: center;
            margin: 15px 0;
        }
        .error {
            color: #d93025;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Авторизація</h1>

    <?php if ($error): ?>
        <div class="message error"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="text" name="username" placeholder="Логін" required value="<?=htmlspecialchars($_POST['username'] ?? '')?>" />
        <input type="password" name="password" placeholder="Пароль" required />
        <button type="submit">Увійти</button>
    </form>

    <div class="register-link">
        Ще немає акаунта? <a href="register.php">Зареєструватися</a>
    </div>
</body>
</html>
