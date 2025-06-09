<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$imie    = $_SESSION['imie'];

// --- Obsługa podglądu wypożyczonego media ---
$view_error = '';
$view_title = '';
$view_embed = '';

if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $view_id = (int)$_GET['view'];

    $stmt = $conn->prepare("
        SELECT m.tytul, m.link
        FROM wypozyczenia w
        JOIN media m ON w.media_id = m.id
        WHERE w.id = ? AND w.user_id = ? AND w.status = 'wypozyczone'
    ");
    $stmt->bind_param('ii', $view_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $view_title = htmlspecialchars($row['tytul']);
        $link = htmlspecialchars($row['link']);
        $extension = strtolower(pathinfo($link, PATHINFO_EXTENSION));

        if ($extension === 'mp4') {
            $view_embed = "
                <video controls autoplay style='width: 100%; max-width: 100%; height: auto; display: block; margin: 0 auto;'>
                    <source src=\"$link\" type=\"video/mp4\">
                    Twoja przeglądarka nie wspiera odtwarzania wideo.
                </video>
            ";
        } elseif ($extension === 'pdf') {
            $view_embed = "
                <iframe src=\"$link\" width=\"100%\" height=\"800px\" style='border: none;'></iframe>
            ";
        } else {
            $view_embed = "Nieobsługiwany format pliku: .$extension";
        }
    } else {
        $view_error = 'Brak dostępu do tego wypożyczenia.';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel użytkownika</title>
    <link rel="stylesheet" href="css/style_dash.css">
</head>
<body>
    <header>
        <h2>Witaj, <?= htmlspecialchars($imie) ?>!</h2>
        <nav>
            <a href="index.php">Strona główna</a>
            <?php if (isset($_SESSION['rola']) && $_SESSION['rola'] === 'admin'): ?>
                <a href="admin.php">Panel administratora</a>
            <?php endif; ?>
            <a href="logout.php">Wyloguj</a>
        </nav>
    </header>

    <main>
        <?php if ($view_embed || $view_error): ?>
            <section class="card viewer">
                <h3>Podgląd: <?= $view_title ?: '' ?></h3>
                <?php if ($view_error): ?>
                    <p class="error"><?= $view_error ?></p>
                <?php else: ?>
                    <?= $view_embed ?>
                <?php endif; ?>
                <p><a href="dashboard.php" class="btn">Wróć do panelu</a></p>
            </section>
        <?php endif; ?>

        <section class="card">
            <h3>Dostępne książki i filmy</h3>
            <ul>
                <?php
                $result = $conn->query("SELECT * FROM media WHERE dostepnosc = 1");

                $opisy = [
                    'Matrix' => 'Kultowy film sci-fi o symulowanej rzeczywistości. (FILM)',
                    'Lalka' => 'Powieść Bolesława Prusa o miłości i społeczeństwie. (KSIĄŻKA)',
                    'Incepcja' => 'Film o snach wewnątrz snów. (FILM)',
                    'Chłopi' => 'Epopeja o życiu wsi polskiej. (KSIĄŻKA)',
                    '1984' => 'Wizja państwa totalitarnego autorstwa George’a Orwella. (KSIĄŻKA)',
                    'W pustyni i w puszczy' => 'Przygody dzieci w Afryce. (KSIĄŻKA)',
                    'Forrest Gump' => 'Historia prostego człowieka, który staje się świadkiem przełomowych wydarzeń w historii USA. (FILM)',
                    'Pan Tadeusz' => 'Epopeja narodowa Adama Mickiewicza opowiadająca o szlacheckiej Polsce i sporach rodowych. (KSIĄŻKA)',
                ];

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $tytul = htmlspecialchars($row['tytul']);
                        $typ   = htmlspecialchars($row['typ']);
                        $id    = (int)$row['id'];

                        $baseTitle = $tytul;
                        $imgPath = "img/" . $baseTitle . ".jpg";
                        $opis = $opisy[$baseTitle] ?? 'Brak opisu.';

                        echo "
                        <li style='display: flex; align-items: center; gap: 20px; margin-bottom: 15px;'>
                            <img src='$imgPath' alt='Okładka $baseTitle' style='width: 80px; height: auto; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.2);'>
                            <div style='flex-grow: 1;'>
                                <strong>$tytul</strong><br>
                                <small>$opis</small>
                            </div>
                            <form action='wypozycz.php' method='post' style='display:inline;'>
                                <input type='hidden' name='media_id' value='$id'>
                                <select name='dni' class='select-dni' style='margin-right: 10px;'>
                                    <option value='1'>1 dzień</option>
                                    <option value='3'>3 dni</option>
                                    <option value='7'>7 dni</option>
                                    <option value='14'>14 dni</option>
                                </select>
                                <button type='submit' class='btn'>Wypożycz</button>
                            </form>
                        </li>";
                    }
                } else {
                    echo "<li>Brak dostępnych pozycji.</li>";
                }
                ?>
            </ul>
        </section>

        <section class="card">
            <h3>Twoje wypożyczenia</h3>
            <?php
            $stmt = $conn->prepare("
                SELECT m.tytul, w.data_wypozyczenia, w.data_zwrotu, w.oplata, w.status, w.id AS wypo_id
                FROM wypozyczenia w
                JOIN media m ON w.media_id = m.id
                WHERE w.user_id = ?
            ");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            ?>

            <?php if ($res && $res->num_rows > 0): ?>
                <ul>
                    <?php while ($row = $res->fetch_assoc()):
                        $tyt = htmlspecialchars($row['tytul']);
                        $dat = htmlspecialchars($row['data_wypozyczenia']);
                        $zwr = htmlspecialchars($row['data_zwrotu']);
                        $opl = htmlspecialchars($row['oplata']);
                        $sts = htmlspecialchars($row['status']);
                        $wid = (int)$row['wypo_id'];
                    ?>
                        <li>
                            <?= "$tyt | Wypożyczono: $dat | Zwrot do: $zwr | Opłata: $opl zł | Status: $sts" ?>
                            <?php if ($sts === 'wypozyczone'): ?>
                                <a class="btn" href="zwroc.php?id=<?= $wid ?>">Zwróć</a>
                                <a class="btn" href="dashboard.php?view=<?= $wid ?>">Zobacz</a>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Nie masz żadnych wypożyczeń.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Biblioteka Online</p>
    </footer>
</body>
</html>











