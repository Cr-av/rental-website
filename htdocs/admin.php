<?php
include("includes/db.php");
include("includes/auth.php");

if (!isAdmin()) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        // Usuwanie
        $delete_id = intval($_POST['delete_id']);
        $stmt = $conn->prepare("DELETE FROM media WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
    } elseif (isset($_POST['tytul'], $_POST['typ'], $_POST['link'])) {
        // Dodawanie
        $tytul = $_POST['tytul'];
        $typ = $_POST['typ'];
        $link = $_POST['link'];
        $dostepnosc = isset($_POST['dostepnosc']) ? intval($_POST['dostepnosc']) : 1;

        $stmt = $conn->prepare("INSERT INTO media (tytul, typ, link, dostepnosc) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $tytul, $typ, $link, $dostepnosc);
        $stmt->execute();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Panel admina</title>
</head>
<body>
<h2>Panel administratora</h2>
<p><a href="index.php">Strona główna</a> | <a href="logout.php">Wyloguj</a></p>

<h3>Dodaj książkę lub film</h3>
<form method="post">
    Tytuł: <input type="text" name="tytul" required><br>
    Typ: 
    <select name="typ">
        <option value="ksiazka">Książka</option>
        <option value="film">Film</option>
    </select><br>
    Link do zasobu (np. iframe, YouTube, itp.): <input type="text" name="link"><br>
    Dostępność: 
    <select name="dostepnosc">
        <option value="1" selected>Dostępne</option>
        <option value="0">Wypożyczone</option>
    </select><br>
    <input type="submit" value="Dodaj">
</form>

<h3>Lista pozycji</h3>
<ul>
<?php
$res = $conn->query("SELECT * FROM media ORDER BY tytul");
while ($m = $res->fetch_assoc()) {
    $id = intval($m['id']);
    $tytul = htmlspecialchars($m['tytul']);
    $typ = htmlspecialchars($m['typ']);
    $dostepnosc = $m['dostepnosc'] ? "Dostępne" : "Wypożyczone";

    echo "<li><strong>$tytul</strong> ($typ) – $dostepnosc ";
    echo "<form method='post' style='display:inline;' onsubmit=\"return confirm('Na pewno chcesz usunąć?');\">
            <input type='hidden' name='delete_id' value='$id'>
            <input type='submit' value='Usuń'>
          </form></li>";
}
?>
</ul>
</body>
</html>





