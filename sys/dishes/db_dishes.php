<?php 
$reqDishesList = $pdo->prepare('SELECT * FROM dishes ORDER BY dishes_name');
$reqDishesList->execute();
$dishesList = $reqDishesList->fetchAll();
