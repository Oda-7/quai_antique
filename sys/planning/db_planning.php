<?php
// Fetch all planning
$planning = $pdo->prepare('SELECT * FROM planning');
$planning->execute();
$dayTime = $planning->fetchAll();
