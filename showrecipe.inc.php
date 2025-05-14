<?php include 'config.php'; ?>
<div id="main">
<div id='preview'><?php


$recipeid = (int)$_GET['id'];
$safe_recipeid = htmlentities($recipeid, ENT_QUOTES, 'UTF-8');

// Récupération de la recette (PDO, requête préparée)
$stmt = $pdo->prepare("SELECT title,poster,shortdesc,ingredients,directions FROM recipes WHERE recipeid = :recipeid");
$stmt->bindParam(':recipeid', $recipeid, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    die('No records retrieved');
}
$title = $row['title'];
$poster = $row['poster'];
$shortdesc = $row['shortdesc'];
$ingredients = nl2br($row['ingredients']);
$directions = nl2br($row['directions']);

echo "<h2>$title</h2>\n";

echo "by $poster <br><br>\n";
echo $shortdesc . "<br><br>\n";
echo "<h3>Ingredients:</h3>\n";
echo $ingredients . "<br><br>\n";

echo "<h3>Directions:</h3>\n";
echo $directions . "\n";
echo "<br><br>\n";

$stmt = $pdo->prepare("SELECT COUNT(commentid) FROM comments WHERE recipeid = :recipeid");
$stmt->bindParam(':recipeid', $recipeid, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_NUM);

if ($row[0] == 0)
{
   echo "No comments posted yet.&nbsp;&nbsp;\n";
   echo "<a href=\"index.php?content=newcomment&id=$safe_recipeid\">Add a comment</a>\n";
   echo "&nbsp;&nbsp;&nbsp;<a href=\"print.php?id=$safe_recipeid\" target=\"_blank\">Print recipe</a>\n";
   echo "<hr>\n";
} else
{
   $totrecords = $row[0];
   echo $row[0] . "\n";
   echo "&nbsp;comments posted.&nbsp;&nbsp;\n";
   echo "<a href=\"index.php?content=newcomment&id=$safe_recipeid\">Add a comment</a>\n";
   echo "&nbsp;&nbsp;&nbsp;<a href=\"print.php?id=$safe_recipeid\" target=\"_blank\">Print recipe</a>\n";
   echo "<hr>\n";
   echo "<h2>Comments:</h2>\n";

   if (!isset($_GET['page']))
      $thispage = 1;
   else
      $thispage = $_GET['page'];

   $recordsperpage = 5;
   $offset = ($thispage - 1) * $recordsperpage;
   $totpages = ceil($totrecords / $recordsperpage);

   $stmt = $pdo->prepare("SELECT date,poster,comment FROM comments WHERE recipeid = :recipeid ORDER BY commentid DESC LIMIT :offset, :recordsperpage");
   $stmt->bindParam(':recipeid', $recipeid, PDO::PARAM_INT);
   $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
   $stmt->bindParam(':recordsperpage', $recordsperpage, PDO::PARAM_INT);
   $stmt->execute();
   while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
       $date = $row['date'];
       $poster = $row['poster'];
       $comment = nl2br($row['comment']);

       echo $date . " - posted by " . $poster . "<br>\n";
       echo $comment . "<br><br>\n";
   }


   if ($thispage > 1)
   {
      $page = $thispage - 1;
      $prevpage = "<a href=\"index.php?content=showrecipe&id=$recipeid&page=$page\">Previous</a> ";
   } else
   {
   if ($totpages > 1)
   { 
      $bar = '';
    for ($page = 1; $page <= $totpages; $page++) {
        $safe_page = htmlentities($page, ENT_QUOTES, 'UTF-8');
        if ($page == $thispage) {
            $bar .= " $safe_page ";
        } else {
            $bar .= " <a href=\"index.php?content=showrecipe&id=$safe_recipeid&page=$safe_page\">$safe_page</a> ";
        }
    }

    $safe_prev = htmlentities($thispage - 1, ENT_QUOTES, 'UTF-8');
    $safe_next = htmlentities($thispage + 1, ENT_QUOTES, 'UTF-8');
    $prevpage = ($thispage > 1) ? "<a href=\"index.php?content=showrecipe&id=$safe_recipeid&page=$safe_prev\">Previous</a> " : "Previous";
    $nextpage = ($thispage < $totpages) ? " <a href=\"index.php?content=showrecipe&id=$safe_recipeid&page=$safe_next\">Next</a>" : "Next";

    echo "GoTo: " . $prevpage . $bar . $nextpage;
}
?></div>
</div>