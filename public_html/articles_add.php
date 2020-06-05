<form action="/articles_add" method="POST">
    <input type="textarea" name="title" placeholder="Tytuł artykułu" style="width:720px">
    </br>
    </br>
    <textarea class="art-editor" name="content" style="height: 300px; width: 720px"></textarea>
    </br>
    <input type="submit" name="articleAdd" value="Dodaj">
</form>


<?php
if(isset($_POST['articleAdd']))
{

    $user_id=$_SESSION['id'];
    $title=$_POST['title'];
    $content=$_POST['content'];

    if(mb_strlen($title)>0 and mb_strlen($content)>0)
    {
        $stmt = $dbh->prepare("INSERT INTO articles (user_id, title, content, created) VALUES (:user_id, :title, :content, NOW())");
        $stmt->execute(['user_id'=>$user_id,'title'=>$title,'content'=>$content]);
    }
    else
    {
        print '</br></br><span style="color: red;">Wpis nie może być pusty</span>';
    }
}

?>

