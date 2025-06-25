<?php
session_start();
require_once __DIR__ . '/../src/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');

    if (!$username || !$password || !$password_confirm) {
        $error = 'Будь ласка, заповніть всі поля.';
    } elseif ($password !== $password_confirm) {
        $error = 'Паролі не співпадають.';
    } else {
        // Перевірка існування користувача
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmtCheck->execute([$username]);
        if ($stmtCheck->fetchColumn() > 0) {
            $error = 'Користувач з таким логіном вже існує.';
        } else {
            // Додаємо користувача
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            try {
                $stmt->execute([$username, $hash]);
                $_SESSION['user'] = [
                    'id' => $pdo->lastInsertId(),
                    'username' => $username
                ];
                $success = 'Реєстрація пройшла успішно! Ви автоматично увійшли.';
                // Через кілька секунд можна зробити редірект на головну
                header("Refresh:3; url=/");
            } catch (PDOException $e) {
                $error = 'Сталася помилка, спробуйте пізніше.';
                error_log('PDOException: ' . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8" />
    <title>Реєстрація</title>
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
        .success {
            color: #198754;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Реєстрація</h1>

    <?php if ($error): ?>
        <div class="message error"><?=htmlspecialchars($error)?></div>
    <?php elseif ($success): ?>
        <div class="message success"><?=htmlspecialchars($success)?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Логін" required value="<?=htmlspecialchars($_POST['username'] ?? '')?>" />
        <input type="password" name="password" placeholder="Пароль" required />
        <input type="password" name="password_confirm" placeholder="Підтвердження пароля" required />
        <button type="submit">Зареєструватися</button>
    </form>
    <div class="login-link">
        Вже маєте акаунт? <a href="login.php">Увійти</a>
    </div>
    <?php endif; ?>
</body>
</html>
