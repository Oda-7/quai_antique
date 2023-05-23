<?php

if (isset($_POST['submit_add_validate'])) {
   if (empty($_POST['allergic_name']) || empty($_POST['allergic_food'])) {
      $errors['formulaire'] = "Un champ du formulaire n'est pas remplie";
   } else {
      $reqInsertAllergic = $pdo->prepare('INSERT INTO allergic SET allergic_name = ?, allergic_food = ?');
      $reqInsertAllergic->execute([$_POST['allergic_name'], $_POST['allergic_food']]);
   }
}

if (isset($_POST['submit_update'])) {
   if (empty($_POST['allergic_name']) || empty($_POST['allergic_food'])) {
      $errors['allergic_empty'] = "Vous devez remplir les 2 champs pour ajouter un allergènes";
   } else {
      $reqUpdate = $pdo->prepare('UPDATE allergic SET allergic_name = ?, allergic_food = ? WHERE allergic_id = ' . $allergicUpdate->allergic_id);
      $reqUpdate->execute([$_POST['allergic_name'], $_POST['allergic_food']]);
   }
   // unset($_GET['id']);
   // header('location: ./panel.php');
   $urlLogin = "panel.php";
   echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
}

if (isset($_POST['cancel_allergic'])) {
   unset($_GET['id']);
}

if (isset($_POST['submit_delete'])) {
   if (!empty($_POST['checkbox_delete'])) {
      for ($i = 0; $i < count($_POST['checkbox_delete']); $i++) {
         $reqNameAllergic = $pdo->prepare('SELECT * FROM allergic WHERE allergic_id = ?');
         $reqNameAllergic->execute([$_POST['checkbox_delete'][$i]]);
         $nameAllergic = $reqNameAllergic->fetch();

         $reqDelete = $pdo->prepare('DELETE FROM allergic WHERE allergic_id = ?');
         $reqDelete->execute([$_POST['checkbox_delete'][$i]]);

         $errors['delete_allergic'] .= "Vous avez supprimé " . $nameAllergic->allergic_name . '<br>';
      }
   } else {
      $errors['selected_delete'] = "Vous n'avez séléctionné aucune allergie(s) à supprimer";
   }
}

$reqInsertOrNotAllergic = $pdo->prepare('SELECT COUNT(allergic_name) 
   AS number_allergic
   FROM allergic');
$reqInsertOrNotAllergic->execute();
$numberAllergic = $reqInsertOrNotAllergic->fetch();

$allergicList = [
   "Gluten" => "Pain, chapelure, gateaux, céréales (seigle, blé, avoine, orge, épeautre, kamut ou leurs souches hybridées) et produits à base de ces céréales)",
   "Poisson" => "Poissons entiers ou découpés, soupe et oeufs de poissons, fonds de sauce, sauce Worcestershire, tapenade, surimi, sushi",
   "Lait" => "Lait, beurre, crème fraiche, yaourt, fromage blanc, petit suisses, fromages, crème dessert, flan, sauce Béchamel",
   "Moutarde" => "Toutes les moutardes",
   "Sulfites" => "Vins, vinaigres, fruits, secs, certains légumes sous vide, en conserve ou surgelés, crevettes, cornichons, épices",
   "Crustacés" => "Crabe, crevettes, écrevisses, chaire de crustacés, fonds de sauce, fumets, bisque",
   "Arachides" => "Cacahuètes, Huile d'arachide, beurre de cacahuètes",
   "Fruits à coque" => "Noix entières, concassées, huile arachide, nougat, pates à tartiner, fromage aux noix",
   "Lupin" => "Farine, flocons, graines, germes de lupin, Pain, chapelure, produits de boulangerie et de patisserie",
   "Oeufs" => "Oeufs Entiers, blancs, jaunes, de poule, de caille, Mayonnaise, pâtes aux oeufs, gâteaux, dessert à la crème, meringues",
   "Soja" => "Graines de soja, germes de soja, farine de soja, lait de soja, sauce au soja",
   "Céleri" => "Céleri rave, boule, branche, sel de céleri, bouillon, fonds de sauce, mélanges d'épices",
   "Sésame" => "Graines de sésame, pâte de sésame, pain spéciaux, houmous",
   "Mollusques" => "Huiles, moules, coques, coquilles Saint Jacques, escargots, bulots, calamars, chair de mollusques, cocktail de fruits de mer"
];

if ($numberAllergic->number_allergic < 1) {
   foreach ($allergicList as $allergic => $details) {
      $reqInsertAllergic = $pdo->prepare('INSERT INTO allergic SET allergic_name = ?, allergic_food = ?');
      $reqInsertAllergic->execute([$allergic, $details]);
   }
}


function requestAllergic()
{
   include './sys/db.php';

   $reqAllergic = $pdo->prepare('SELECT * FROM allergic ORDER BY allergic_name');
   $reqAllergic->execute();
   $allergicTable = $reqAllergic->fetchAll();

   foreach ($allergicTable as $a => $key) {
      echo '<div class="d-flex flex-column">
         <b> ' . $key->allergic_name . '</b>
         <p class="mb-0">' . $key->allergic_food . '</p>
            <div>
               <a type="button" href="./panel.php?id=' . $key->allergic_id . '"><img src="./svg/sticky.svg" alt="Modifier"></a>
               <input type="checkbox" name="checkbox_delete[]" value="' . $key->allergic_id . '">
            </div>
         </div>';
   }
}
