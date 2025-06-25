<?php
session_start();
require_once __DIR__ . '/../src/db.php';

function currentUserId() {
    return $_SESSION['user']['id'] ?? null;
}

$user_id = currentUserId();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!$user_id) {
        header('HTTP/1.1 403 Forbidden');
        exit('Ви повинні бути авторизовані.');
    }

    if ($_POST['action'] === 'add_message' && !empty(trim($_POST['text']))) {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, text, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, trim($_POST['text'])]);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($_POST['action'] === 'like' && !empty($_POST['message_id'])) {
        $message_id = (int)$_POST['message_id'];
        $stmt = $pdo->prepare("SELECT user_id FROM messages WHERE id = ?");
        $stmt->execute([$message_id]);
        $msg_owner = $stmt->fetchColumn();

        if ($msg_owner && $msg_owner != $user_id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE user_id = ? AND message_id = ?");
            $stmt->execute([$user_id, $message_id]);
            if ($stmt->fetchColumn() == 0) {
                $pdo->prepare("INSERT INTO likes (user_id, message_id, created_at) VALUES (?, ?, NOW())")->execute([$user_id, $message_id]);
            }
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($_POST['action'] === 'delete' && !empty($_POST['message_id'])) {
        $message_id = (int)$_POST['message_id'];

        $stmt = $pdo->prepare("SELECT created_at FROM messages WHERE id = ? AND user_id = ?");
        $stmt->execute([$message_id, $user_id]);
        $created_at = $stmt->fetchColumn();

        if ($created_at && (time() - strtotime($created_at)) < 86400) {
            $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([$message_id]);
            $pdo->prepare("DELETE FROM likes WHERE message_id = ?")->execute([$message_id]);
        }

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Отримуємо всі повідомлення з кількістю лайків
$stmt = $pdo->query("
    SELECT m.*, u.username, 
           (SELECT COUNT(*) FROM likes l WHERE l.message_id = m.id) AS likes_count
    FROM messages m
    JOIN users u ON m.user_id = u.id
    ORDER BY m.created_at DESC
");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Отримуємо ID повідомлень, які лайкнув поточний користувач
$liked_message_ids = [];
if ($user_id) {
    $stmt = $pdo->prepare("SELECT message_id FROM likes WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $liked_message_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8" />
    <title>Повідомлення</title>
    <style>
        /* (стилі такі ж як раніше, можна скопіювати з попереднього коду) */
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 40px auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            color: #333;
        }
        h1 {
            text-align: center;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logout-button {
            background-color: #dc3545;
            border: none;
            color: white;
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-button:hover {
            background-color: #b02a37;
        }
        form textarea {
            width: 100%;
            min-height: 80px;
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #ccc;
            resize: vertical;
        }
        form button {
            background-color: #28a745;
            border: none;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        form button:hover {
            background-color: #218838;
        }
        .message {
            background: white;
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .message strong {
            font-size: 18px;
        }
        .message small {
            color: #666;
        }
        .actions {
            margin-top: 10px;
        }
        .actions form {
            display: inline-block;
            margin-right: 10px;
        }
        .actions button {
            background-color: #007bff;
            border: none;
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            color: white;
        }
        .actions button:hover {
            background-color: #0056b3;
        }
        .actions .delete-button {
            background-color: #dc3545;
        }
        .actions .delete-button:hover {
            background-color: #b02a37;
        }
        a.login-link {
            display: block;
            text-align: center;
            margin-top: 30px;
            font-size: 16px;
            color: #007bff;
            text-decoration: none;
        }
        a.login-link:hover {
            text-decoration: underline;
        }
        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>
        Список повідомлень
        <?php if ($user_id): ?>
            <a href="?logout=1" class="logout-button">Вийти</a>
        <?php endif; ?>
    </h1>

    <?php if ($user_id): ?>
        <form method="POST">
            <textarea name="text" required placeholder="Ваше повідомлення"></textarea>
            <input type="hidden" name="action" value="add_message">
            <button type="submit">Додати повідомлення</button>
        </form>
        <hr>
    <?php else: ?>
        <p style="text-align:center;">
            <a class="login-link" href="/login.php">Увійти</a>, щоб додавати повідомлення, лайкати та видаляти свої.
        </p>
    <?php endif; ?>

    <?php foreach ($messages as $msg): ?>
        <div class="message">
            <p><strong><?= htmlspecialchars($msg['username']) ?>:</strong> <?= htmlspecialchars($msg['text']) ?></p>
            <small><?= $msg['created_at'] ?></small><br>
            <small>Лайків: <?= $msg['likes_count'] ?></small>

            <div class="actions">
                <?php 
                // Показуємо кнопку лайку, якщо:
                // - користувач залогінений
                // - це не його повідомлення
                // - і він ще не лайкнув це повідомлення
                if ($user_id && $msg['user_id'] != $user_id && !in_array($msg['id'], $liked_message_ids)): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="like">
                        <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                        <button type="submit">Лайкнути</button>
                    </form>
                <?php endif; ?>

                <?php if ($user_id == $msg['user_id'] && (time() - strtotime($msg['created_at'])) < 86400): ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Видалити повідомлення?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                        <button type="submit" class="delete-button">Видалити</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</body>
</html>
