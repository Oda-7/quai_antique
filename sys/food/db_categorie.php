<?php
// Fetch all categorie
$reqCategorieList = $pdo->prepare('SELECT * FROM categorie ');
$reqCategorieList->execute();
$categorieList = $reqCategorieList->fetchAll();
