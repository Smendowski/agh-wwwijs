<?php
if (!defined('IN_INDEX')) { exit("Nie można uruchomić tego pliku bezpośrednio."); }

?>

<div class="card mb-3">
    <div class="card-header">
        Rejestracja użytkownika
    </div>
    <div class="card-body">
        <form action="/register" method="POST">
            <label>E-mail<br><input type="text" name="email"/></label><br>
            <label>Hasło<br><input type="password" name="password"/></label><br>
            <div class="g-recaptcha" data-sitekey="<?php print $config['recaptcha_public']?>"></div><br>
            <input type="submit" value="Załóż konto"/>
        </form>
    </div>

<?php
if(isset($_POST['g-recaptcha-response']))
{
    $recaptcha = new \ReCaptcha\ReCaptcha($config['recaptcha_private']);
    $resp = $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
        ->verify($_POST['g-recaptcha-response'],$_SERVER['REMOTE_ADDR']);
    if($resp->isSuccess())
    {
        if(isset($_POST['email']))
        {
            $flag= true;
            $email = $_POST['email'];
            if(!(preg_match('/^[a-zA-Z0-9\-\_\.]+\@[a-zA-Z0-9\-\_\.]+\.[a-zA-Z]{2,5}$/D', $email)))
            {
                $flag = false;
                $_SESSION['e_email'] = 'Niepoprawny format adresu e-mail';
            }
            $plainpassword = $_POST['password'];
            $password = password_hash($plainpassword, PASSWORD_DEFAULT);
            if($flag){
                try {
                    $stmt = $dbh->prepare('
							INSERT INTO users (id, email, password, created) VALUES (null, :email, :password, NOW())');
                    $stmt->execute([':email' => $email, ':password' => $password]);
                    print '<span style="color: green;">Konto zostało założone.</span>';
                } catch (PDOException $e) {
                    print '<span style="color: red;">Podany adres email jest już zajęty.</span>';
                }
            }
        }
    }
    else
    {
        $errors=$resp->getErrorCodes();
        print '<p style="font-weight: bold; color: red;">Źle rozwiązana reCaptcha</p>';
    }
}
?>