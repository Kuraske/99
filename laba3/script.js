let currentInput = '';
let previousInput = '';
let operator = '';
let expression = ''; 

function appendNumber(number) {
    currentInput += number;
    expression += number; 
    updateDisplay();


}

function clearDisplay() {
    currentInput = '';
    previousInput = '';
    operator = '';
    expression = ''; 

    updateDisplay();
    clearOperatorHighlight();
}

function setOperation(op) {
    if (currentInput === '') return;
    if (previousInput !== '') {
        calculateResult();
    }
    operator = op;
    previousInput = currentInput;
    currentInput = '';
    expression += ' ' + op + ' ';

    updateDisplay();
    highlightOperator(op);  
}

function calculateResult() {
    if (previousInput === '' || currentInput === '') return;
    let result;
    const prev = parseFloat(previousInput);
    const current = parseFloat(currentInput);

    switch (operator) {
        case '+':
            result = prev + current;
            break;
        case '-':

            result = prev - current;
            break;
        case '*':
            result = prev * current;
            break;
        case '/':
            if (current === 0) {
                alert('Division by zero is not allowed!');
                clearDisplay();
                return;
            }
            result = prev / current;
            break;
        default:

            return;
    }

    currentInput = result.toString();
    operator = '';
    previousInput = '';
    expression = result.toString(); 
    updateDisplay();
    clearOperatorHighlight();  
}

function updateDisplay() {
    document.getElementById('display').value = expression; 
}


function highlightOperator(op) {
    
    const operators = document.querySelectorAll('.operator');
    operators.forEach(button => button.classList.remove('active'));
    
    
    const operatorButton = document.querySelector(`.operator:nth-child(${getOperatorPosition(op)})`);
    if (operatorButton) {
        operatorButton.classList.add('active');
    }
}


function clearOperatorHighlight() {
    const operators = document.querySelectorAll('.operator');
    operators.forEach(button => button.classList.remove('active'));
}


function getOperatorPosition(op) {
    switch (op) {
        case '+': return 4;
        case '-': return 8;
        case '*': return 12;
        case '/': return 16;
        default: return 0;
    }
}
