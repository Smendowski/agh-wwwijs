<?php

if (isset($_GET['show']) && intval($_GET['show']) > 0) {

    $id = intval($_GET['show']);

    // podstrona /articles_list/show/<id>,
    // tutaj wyswietlamy tytul i tresc artykulu, ktorego ID mamy w zmiennej $id

    print'<a href="/articles_list">Powrót do poprzedniej strony</a> 
    </br>
    </br>';

    $stmt = $dbh->prepare("SELECT * FROM articles WHERE id=:id");
    $stmt->execute([':id'=>$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if($row['title']!=null and $row['content']!=null)
    {
        print'
    <div class="card mb-3">
        <div class="card-header"> '.$row['title'].' </div>
        <div class="card-body"> '.$row['content'].'</div>
    </div>
                
    ';
    }
    else
    {
        print '<span style="color: red;">Brak wpisów</span>';
    }


} elseif (isset($_GET['edit']) && intval($_GET['edit']) > 0) {

    if(isset($_SESSION['id']))
    {
        $id = intval($_GET['edit']);

        if (isset($_POST['title']) && isset($_POST['content'])) {

            // tutaj zapisujemy zmiany w artykule $id, zakladajac, ze w formularzu edycji
            // dla tytulu i tresci nadano atrybuty name="title" oraz name="content",
            // przed zapisem nalezy upewnic sie, ze zalogowany uzytkownik jest autorem artykulu
            $stmt = $dbh->prepare("SELECT * FROM articles WHERE id=:id");
            $stmt->execute([':id'=>$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_id=$row['user_id'];

            if(isset($_POST['articleEdit']))
            {

                $title=$_POST['title'];
                $content=$_POST['content'];

                if($_SESSION['id']==$user_id)
                {
                    if(mb_strlen($title)>0 and mb_strlen($content)>0)
                    {
                        $stmt = $dbh->prepare("UPDATE articles SET title = :title, content = :content WHERE id = :id AND user_id = :user_id");
                        $stmt->execute([':user_id'=>$user_id,':title'=>$title,':content'=>$content,':id'=>$id]);
                    }
                    else
                    {
                        print '<span style="color: red;">Wpis nie może zostać pusty</span>';
                    }
                }
                else
                {
                    print '<span style="color: red;">Brak uprawnień do zmiany tego wpisu</span>
                 </br> </br>';
                }

            }


        }


        // podstrona /articles_list/edit/<id>,
        // tutaj wyswietlamy formularz edycji artykulu, ktorego ID mamy w zmiennej $id
        print'<a href="/articles_list">Powrót do poprzedniej strony</a> 
    </br>
    </br>';
        

        $stmt = $dbh->prepare("SELECT * FROM articles WHERE id=:id");
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id=$row['user_id'];
        if($row['title']!=null and $row['content']!=null)
        {
            $titleData=htmlspecialchars($row['title'],ENT_QUOTES | ENT_HTML401, 'UTF-8');
            print'<form action="/articles_list/edit/'.$id.'" method="POST">
    <input type="textarea" name="title" value='.$titleData.' style="width:720px">
    </br>
    </br>
    <textarea class="art-editor" name="content" style="height: 300px; width: 720px">' .  htmlspecialchars($row['content'],ENT_QUOTES | ENT_HTML401, 'UTF-8') . '</textarea>
    </br>
    <input type="submit" name="articleEdit" value="Edytuj">
    </form> ';
        }
        else
        {
            print '<span style="color: red;">Nie ma tu czego edytować</span>';
        }


    }
    else{
        print '<span style="color: red;">Niezalogowanym wstepn wzbroniony</span>';
    }







} else {



    if (isset($_GET['delete']) && intval($_GET['delete']) > 0) {
        if(isset($_SESSION['id'])==true)
        {
            $id = intval($_GET['delete']);

            // tutaj usuwamy artykul, ktorego ID mamy w zmiennej $id,
            // przed usunieciem nalezy upewnic sie, ze zalogowany uzytkownik jest autorem artykulu
            $stmt = $dbh->prepare("SELECT * FROM articles WHERE id=:id");
            $stmt->execute([':id'=>$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_id=$row['user_id'];
            if($row['title']!=null)
            {
                if($_SESSION['id']==$row['user_id'])
                {
                    $stmt = $dbh->prepare(" DELETE FROM articles WHERE id = :id AND user_id = :user_id");
                    $stmt->execute(['id' => $id,'user_id'=>$user_id]);

                }
                else
                {
                    print '<span style="color: red;">Brak uprawnień.</span>';
                }
            }

        }
        else{ print '<span style="color: red;">Nic z tego</span>';}
    }

    // podstrona /articles_list,
    // tutaj wyswietlamy listę wszystkich artykulow
    $stmt = $dbh->prepare("SELECT * FROM articles ORDER BY id DESC");
    $stmt->execute();

    print'
            <table class="table table-striped">
            <thead>
            <tr id="wiersz-naglowka">
                <th scope="col">Tytuł artykułu</th>
                <th scope="col">Opcja 1</th>
                <th scope="col">Opcja 2</th>
            </tr>
            </thead>
            <tbody>';

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        if(isset($_SESSION['id']) && $row['user_id']==$_SESSION['id'])
        {

            print'
                <tr>
                    <td><a style="font-size:20px;" href="/articles_list/show/'. $row['id'] .' "> '.$row['title'].' </a></td>
                    <td><button><a href="/articles_list/edit/'. $row['id'] .' "> Edytuj </a></button></td>
                    <td><button><a href="/articles_list/delete/'. $row['id'] .' "> Usun </a></button></td>

                     
                     
                </tr>';
        }
        else
        {
            print'
                    <tr>
                        <td><a style="font-size:20px;" href="/articles_list/show/'. $row['id'] .' "> '.$row['title'].' </a></td>
                    </tr>
                ';
        }
    }
    print'</tbody>
            </table>';

}

