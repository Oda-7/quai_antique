<?php
$reqAllergicListBd = $pdo->prepare('SELECT * FROM allergic ORDER BY allergic_name');
$reqAllergicListBd->execute();
$allergicListBd = $reqAllergicListBd->fetchAll();
