<?php

function getComputerChoice($choices) {
    return array_rand($choices);
}

function isValidChoice($input) {
    return is_numeric($input) && in_array($input, [0, 1, 2]);
}

function playRockPaperScissors() {
    $choices = ['камінь', 'ножиці', 'папір'];
    $userScore = 0;
    $computerScore = 0;

    for ($round = 1; $round <= 3; $round++) {
        echo "\nРаунд {$round}. Обери:\n";
        echo "[0] камінь\n[1] ножиці\n[2] папір\n-> ";

        $userChoice = trim(fgets(STDIN));

        if (!isValidChoice($userChoice)) {
            echo "Невірний вибір. Спробуйте ще раз.\n";
            $round--;
            continue;
        }

        $computerChoice = getComputerChoice($choices);
        echo "Я вибрав: " . $choices[$computerChoice] . "\n";

        if ($userChoice == $computerChoice) {
            echo "Нічия!\n";
        } elseif (
            ($userChoice == 0 && $computerChoice == 1) ||
            ($userChoice == 1 && $computerChoice == 2) ||
            ($userChoice == 2 && $computerChoice == 0)
        ) {
            echo "Ти виграв цей раунд!\n";
            $userScore++;
        } else {
            echo "Я виграв цей раунд!\n";
            $computerScore++;
        }
    }

    echo "\nФінальний рахунок:\n";
    echo "Ти: {$userScore}\n";
    echo "Комп'ютер: {$computerScore}\n";
    echo ($userScore > $computerScore ? "Ти переміг!" : ($userScore < $computerScore ? "Я переміг!" : "Нічия!")) . "\n";
}

// Запуск гри
playRockPaperScissors();

?>