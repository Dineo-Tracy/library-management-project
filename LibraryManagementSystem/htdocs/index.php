<?php
class Book {
    private $title;
    private $author;
    private $isbn;
    private $genre;

    public function setBookDetails($title, $author, $isbn, $genre) {
        $this->title = htmlspecialchars($title);
        $this->author = htmlspecialchars($author);
        $this->isbn = htmlspecialchars($isbn);
        $this->genre = htmlspecialchars($genre);
    }

    public function getBookDetails() {
        return [
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'genre' => $this->genre
        ];
    }
}

function establishDBConnection() {
    try {
        $dsn = 'mysql:host=localhost;dbname=library_db';
        $username = 'root';
        $password = ''; 
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        return new PDO($dsn, $username, $password, $options);
    } catch (PDOException $e) {
        die('Failed to connect: ' . $e->getMessage());
    }
}

function insertBook($book) {
    $db = establishDBConnection();
    $sql = "INSERT INTO books (title, author, isbn, genre) VALUES (:title, :author, :isbn, :genre)";
    try {
        $stmt = $db->prepare($sql);
        $details = $book->getBookDetails();
        $stmt->bindParam(':title', $details['title']);
        $stmt->bindParam(':author', $details['author']);
        $stmt->bindParam(':isbn', $details['isbn']);
        $stmt->bindParam(':genre', $details['genre']);
        $stmt->execute();
        echo "<p class='success'>Book successfully added!</p>";
    } catch (PDOException $e) {
        echo "<p class='error'>Error adding book: " . $e->getMessage() . "</p>";
    }
}

function displayAllBooks() {
    $db = establishDBConnection();
    $sql = "SELECT * FROM books ORDER BY title ASC";
    try {
        $stmt = $db->query($sql);
        $books = $stmt->fetchAll();
        if (count($books) > 0) {
            echo "<table class='book-table'>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Genre</th>
                        </tr>
                    </thead>
                    <tbody>";
            foreach ($books as $book) {
                echo "<tr>
                        <td>{$book['title']}</td>
                        <td>{$book['author']}</td>
                        <td>{$book['isbn']}</td>
                        <td>{$book['genre']}</td>
                      </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No books available in the library.</p>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Error displaying books: " . $e->getMessage() . "</p>";
    }
}

function findBookByTitle($title) {
    $db = establishDBConnection();
    $sql = "SELECT * FROM books WHERE title LIKE :title ORDER BY title ASC";
    try {
        $stmt = $db->prepare($sql);
        $searchTerm = "%" . $title . "%";
        $stmt->bindParam(':title', $searchTerm);
        $stmt->execute();
        $books = $stmt->fetchAll();
        if (count($books) > 0) {
            echo "<table class='book-table'>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Genre</th>
                        </tr>
                    </thead>
                    <tbody>";
            foreach ($books as $book) {
                echo "<tr>
                        <td>{$book['title']}</td>
                        <td>{$book['author']}</td>
                        <td>{$book['isbn']}</td>
                        <td>{$book['genre']}</td>
                      </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No books matched your search.</p>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Error searching for book: " . $e->getMessage() . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_book'])) {
        $book = new Book();
        $book->setBookDetails($_POST['book_title'], $_POST['book_author'], $_POST['book_isbn'], $_POST['book_genre']);
        insertBook($book);
    } elseif (isset($_POST['search_book'])) {
        findBookByTitle($_POST['search_title']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .book-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
        }
        .book-table th, .book-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .book-table th {
            background-color: #f8f8f8;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }
        input[type="submit"] {
            background-color: #5cb85c;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #4cae4c;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Library Management System</h1>

    <h2>Add a New Book</h2>
    <form method="POST" action="">
        <label for="book_title">Title:</label>
        <input type="text" id="book_title" name="book_title" required>

        <label for="book_author">Author:</label>
        <input type="text" id="book_author" name="book_author" required>

        <label for="book_isbn">ISBN:</label>
        <input type="text" id="book_isbn" name="book_isbn" required>

        <label for="book_genre">Genre:</label>
        <input type="text" id="book_genre" name="book_genre" required>

        <input type="submit" name="add_book" value="Add Book">
    </form>

    <h2>Search for a Book</h2>
    <form method="POST" action="">
        <label for="search_title">Enter title to search:</label>
        <input type="text" id="search_title" name="search_title" required>
        <input type="submit" name="search_book" value="Search">
    </form>

    <h2>All Books in Library</h2>
    <?php displayAllBooks(); ?>
</body>
</html>
```


