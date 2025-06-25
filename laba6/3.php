<?php

function calculate($num1, $operator, $num2) {
    $operations = [
        '+' => fn($a, $b) => $a + $b,
        '-' => fn($a, $b) => $a - $b,
        '*' => fn($a, $b) => $a * $b,
        '/' => fn($a, $b) => $b != 0 ? $a / $b : "Ділення на нуль!",
        '**' => fn($a, $b) => pow($a, $b),
        '%' => fn($a, $b) => $b != 0 ? $a % $b : "Ділення на нуль!"
    ];

    if (!isset($operations[$operator])) {
        return "Невідомий оператор";
    }

    return $operations[$operator]($num1, $num2);
}

function runCalculator() {
    echo "Введіть вираз (напр. 5 + 3): ";
    $input = trim(fgets(STDIN));

    if (preg_match('/^(-?\d+\.?\d*)\s*([\+\-\*\/\%]|\*\*)\s*(-?\d+\.?\d*)$/', $input, $matches)) {
        $num1 = floatval($matches[1]);
        $operator = $matches[2];
        $num2 = floatval($matches[3]);

        $result = calculate($num1, $operator, $num2);
        echo "Результат: " . $result . "\n";
    } else {
        echo "Невірний формат виразу!\n";
    }
}

runCalculator();

?>