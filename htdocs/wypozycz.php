<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['media_id'], $_POST['dni'])) {
    $user_id  = $_SESSION['user_id'];
    $media_id = (int)$_POST['media_id'];
    $dni      = (int)$_POST['dni'];

    if ($dni <= 0) {
        die("Nieprawidłowy okres wypożyczenia.");
    }

    // Obliczenie dat
    $data_wyp = date('Y-m-d H:i:s');
    $data_zwr = date('Y-m-d H:i:s', strtotime("+$dni days"));

    // Ustalmy stałą stawkę np. 2.00 zł za dzień
    $stawka_dzienna = 2.00;
    $oplata = $dni * $stawka_dzienna;

    // Przygotowanie zapytania
    $stmt = $conn->prepare("
        INSERT INTO wypozyczenia (user_id, media_id, data_wypozyczenia, data_zwrotu, status, oplata)
        VALUES (?, ?, ?, ?, 'wypozyczone', ?)
    ");

    if (!$stmt) {
        die("Błąd zapytania: " . $conn->error);
    }

    // Parametry: i - int, s - string, d - decimal
    $stmt->bind_param("iissd", $user_id, $media_id, $data_wyp, $data_zwr, $oplata);

    if ($stmt->execute()) {
        // Zmniejsz dostępność media (dostepnosc = 0)
        $conn->query("UPDATE media SET dostepnosc = 0 WHERE id = $media_id");

        header("Location: dashboard.php");
        exit;
    } else {
        die("Nie udało się wypożyczyć pozycji. Błąd: " . $stmt->error);
    }
} else {
    header("Location: dashboard.php");
    exit;
}





