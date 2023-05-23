<?php
// FetchAll sub_categorie

$reqListSubCategorie = $pdo->prepare('SELECT * FROM sub_categorie ORDER BY sub_categorie_name');
$reqListSubCategorie->execute();
$listSubCategorie = $reqListSubCategorie->fetchAll();
