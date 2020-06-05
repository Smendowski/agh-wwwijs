<?php
if (!defined('IN_INDEX')) { exit("Nie można uruchomić tego pliku bezpośrednio."); }

?>

<div class="card mb-3">
    <div class="card-header">
        Przykład: dodanie danych do tabeli za pomocą metody POST
    </div>
    <div class="card-body">



        <?php
        // PUNKT 13 LABORATORIUM -  Dodawanie Opinii
        if (isset($_POST['opinion'])) {
            $opinion = $_POST['opinion'];
            if (mb_strlen($opinion) >= 5 && mb_strlen($opinion) <= 200) {
                $recaptcha = new \ReCaptcha\ReCaptcha($config['recaptcha_private']);
                $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
                if ($resp->isSuccess()) {
                    $stmt = $dbh->prepare("INSERT INTO guest_book (opinion, ip, created) VALUES (:opinion, :ip, NOW())");
                    $stmt->execute([':opinion' => $opinion, ':ip' => $_SERVER['REMOTE_ADDR']]);
                    print '<p style="font-weight: bold; color: green;">Dane zostały dodane do bazy.</p>';
                } else {
                    print '<p style="font-weight: bold; color: red;">reCAPTCHA rozwiązana niepoprawnie.</p>';
                }
            } else {
                print '<p style="font-weight: bold; color: red;">Podane dane są nieprawidłowe.</p>';
            }
        }
        // PUNKT 13 LABORATORIUM


        // Punkt 14 Laboratorium
        if (isset($_GET['delete'])) {
            $delete = $_GET['delete'];
            unset($_GET['delete']);
            $stmt = $dbh->prepare("DELETE FROM guest_book WHERE id = :id AND ip = :ip;");
            $stmt->execute([':id' => $delete, ':ip' => $_SERVER['REMOTE_ADDR']]);
        }
        ?>


        <!-- PUNKT 13 LABORATORIUM
        zmienić page na guest book!-->
        <form action="/guest_book" method="POST">
            <input type="text" name="opinion">
            <div class="g-recaptcha" data-sitekey="<?php print $config['recaptcha_public']?>"></div>
            <input type="submit" value="Dodaj">
        </form>



        <table class="table table-striped mt-3" id="moja-tabelka">
            <thead>
            <tr id="wiersz-naglowka">
                <th scope="col">ID</th>
                <th scope="col">Opinia</th>
            </tr>
            </thead>
            <tbody>

            <?php
            // PUNKT 13 -  wyświetlanie z bazy danych
            // DODAC ip
            $stmt = $dbh->prepare("SELECT id, opinion, ip FROM guest_book");
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                print '
                <tr>
                  <td>' . intval($row['id']) . '</td>
                  <td>' . htmlspecialchars($row['opinion'], ENT_QUOTES | ENT_HTML401, 'UTF-8') . '</td>';
				  if ($row['ip'] == $_SERVER['REMOTE_ADDR']) {
					  print '<td>' . '<button id="przycisk"><a href="/guest_book/delete/' . $row['id'] . '">Usuń</a></button>' . '</td>';
				  } else {
					  print '<td>' . '</td>';
				  }
                print '</tr>';


            }
            ?>
            </tbody>
        </table>
    </div>
