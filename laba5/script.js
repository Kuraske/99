// Клас User
class User {
    constructor(id, name, email) {
        this.id = id;
        this.name = name;
        this.email = email;
    }
}

// Клас Librarian (Бібліотекар)
class Librarian extends User {
    constructor(id, name, email, salary) {
        super(id, name, email);
        this.salary = salary;
    }

    addBook(book, library) {
        library.addBook(book);
    }

    removeBook(book, library) {
        library.removeBook(book);
    }
}

// Клас Reader (Читач)
class Reader extends User {
    constructor(id, name, email, membershipId) {
        super(id, name, email);
        this.membershipId = membershipId;
    }

    borrowBook(book, library) {
        if (book.isAvailable) {
            let dueDate = new Date();
            dueDate.setDate(dueDate.getDate() + 14); // Термін повернення книги - 14 днів
            let loan = new Loan(book, this, dueDate);
            library.addLoan(loan);
            book.borrow();
            alert(`${this.name} позичив книгу: ${book.title}. Термін повернення: ${dueDate.toLocaleDateString()}`);
            renderBooks();
        } else {
            alert("Ця книга вже в користуванні!");
        }
    }

    returnBook(book, library) {
        const loan = library.findLoan(book, this);
        if (loan) {
            const today = new Date();
            const overdueDays = Math.floor((today - loan.dueDate) / (1000 * 60 * 60 * 24));
            if (overdueDays > 0) {
                alert(`Книга прострочена на ${overdueDays} днів!`);
            } else {
                alert(`Ви повернули книгу вчасно!`);
            }
            book.returnBook();
            library.removeLoan(loan);
            alert(`${this.name} повернув книгу: ${book.title}`);
            renderBooks();
        } else {
            alert("Ви не позичали цю книгу!");
        }
    }
}

// Клас Book
class Book {
    constructor(title, author) {
        this.title = title;
        this.author = author;
        this.isAvailable = true;
    }

    borrow() {
        this.isAvailable = false;
    }

    returnBook() {
        this.isAvailable = true;
    }
}

// Клас Loan
class Loan {
    constructor(book, reader, dueDate) {
        this.book = book;
        this.reader = reader;
        this.dueDate = dueDate;
    }
}

// Клас Library
class Library {
    constructor() {
        this.books = [];
        this.users = [];
        this.loans = [];
    }

    addBook(book) {
        this.books.push(book);
    }

    removeBook(book) {
        this.books = this.books.filter(b => b !== book);
    }

    addUser(user) {
        this.users.push(user);
    }

    addLoan(loan) {
        this.loans.push(loan);
    }

    removeLoan(loan) {
        this.loans = this.loans.filter(l => l !== loan);
    }

    findLoan(book, reader) {
        return this.loans.find(loan => loan.book === book && loan.reader === reader);
    }

    getOverdueLoans() {
        const today = new Date();
        return this.loans.filter(loan => loan.dueDate < today);
    }

    getAvailableBooks() {
        return this.books.filter(book => book.isAvailable);
    }
}

// Створення бібліотеки
const library = new Library();

// Додавання бібліотекаря
const librarian = new Librarian(1, "Іван Іванов", "ivan@example.com", 5000);
library.addUser(librarian);

// Додавання книг
const book1 = new Book("Книга 1", "Автор 1");
const book2 = new Book("Книга 2", "Автор 2");
const book3 = new Book("Книга 3", "Автор 3");

library.addBook(book1);
library.addBook(book2);
library.addBook(book3);

// Створення об'єкта читача
let currentReader = null;

// Функція рендерингу книг
function renderBooks() {
    const bookList = document.getElementById("bookList");
    const bookSelect = document.getElementById("bookSelect");
    const bookSelectReader = document.getElementById("bookSelectReader");
    const overdueList = document.getElementById("overdueList");

    bookList.innerHTML = "";
    bookSelect.innerHTML = '<option value="">Виберіть книгу</option>';
    bookSelectReader.innerHTML = '<option value="">Виберіть книгу</option>';
    overdueList.innerHTML = "";

    library.books.forEach(book => {
        const li = document.createElement("li");
        li.textContent = `${book.title} - ${book.author} (${book.isAvailable ? "Доступна" : "Взята"})`;
        bookList.appendChild(li);

        const option = document.createElement("option");
        option.value = book.title;
        option.textContent = book.title;
        bookSelect.appendChild(option);
        bookSelectReader.appendChild(option.cloneNode(true));
    });

    // Відображення прострочених позик
    const overdueLoans = library.getOverdueLoans();
    overdueLoans.forEach(loan => {
        const li = document.createElement("li");
        li.textContent = `${loan.book.title} позичена ${loan.reader.name}, термін повернення: ${loan.dueDate.toLocaleDateString()}`;
        overdueList.appendChild(li);
    });
}

// Додавання книги
function addBook() {
    const title = document.getElementById("bookTitle").value;
    const author = document.getElementById("bookAuthor").value;
    if (title && author) {
        const newBook = new Book(title, author);
        librarian.addBook(newBook, library);
        renderBooks();
    }
}

// Видалення книги
function removeBook() {
    const title = document.getElementById("bookSelect").value;
    const book = library.books.find(b => b.title === title);
    if (book) {
        librarian.removeBook(book, library);
        renderBooks();
    }
}

// Позика книги
function borrowBook() {
    const bookTitle = document.getElementById("bookSelectReader").value;
    const readerName = document.getElementById("readerName").value;
    if (!currentReader) {
        currentReader = new Reader(Date.now(), readerName, `${readerName}@example.com`, Date.now());
        library.addUser(currentReader);
    }
    const book = library.books.find(b => b.title === bookTitle);
    if (book) {
        currentReader.borrowBook(book, library);
        renderBooks();
    }
}

// Повернення книги
function returnBook() {
    const bookTitle = document.getElementById("bookSelectReader").value;
    const book = library.books.find(b => b.title === bookTitle);
    if (book) {
        currentReader.returnBook(book, library);
    }
}

// Виконання рендерингу книг
renderBooks();
