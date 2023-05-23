<?php
$reqVerifyCategorie = $pdo->prepare('SELECT COUNT(categorie_name) AS numberCategorie FROM categorie');
$reqVerifyCategorie->execute();
$verifyCategorie = $reqVerifyCategorie->fetch();

$insertCategorie = [
   "Amuses Bouches",
   "Entrées",
   "Plats",
   "Fromages",
   "Desserts",
   "Vins",
];

if ($verifyCategorie->numberCategorie < 1) {
   foreach ($insertCategorie as $categorie) {
      $reqInsertCategories = $pdo->prepare("INSERT INTO categorie SET categorie_name = ?");
      $reqInsertCategories->execute([$categorie]);
   }
}

// Sub Categorie

if (isset($_POST['categorie_add_validate'])) {
   if (empty($_POST['categorie_name'])) {
      $errors['empty_categorie_name'] = "Vous n'avez pas donné de nom à la catégorie";
   } else {
      $reqVerifyCategorieInsert = $pdo->prepare('SELECT * FROM sub_categorie WHERE sub_categorie_name = ?');
      $reqVerifyCategorieInsert->execute([ucfirst($_POST['categorie_name'])]);
      $verifyCategorieInsert = $reqVerifyCategorieInsert->fetch();
      if (!$verifyCategorieInsert) {
         $reqInsertCategorie = $pdo->prepare('INSERT INTO sub_categorie SET sub_categorie_name = ?, categorie_id = ?');
         $reqInsertCategorie->execute([ucfirst($_POST['categorie_name']), $_POST['categorie_select']]);
         $_SESSION['flash']['success'] = 'La categorie a été ajoutée';
      } else {
         $errors['add_categorie'] = "La catégorie existe déja";
      }
   }
}

if (isset($_POST['modify_categorie'])) {
   $reqModifyCategorie = $pdo->prepare('UPDATE sub_categorie SET sub_categorie_name = ?, categorie_id = ? WHERE sub_categorie_id = ?');
   $reqModifyCategorie->execute([ucfirst($_POST['categorie_name']), $_POST['modify_categorie_id'], $_GET['categorie']]);
   // header('location: panel.php');
   $urlLogin = "panel.php";
   echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
   // unset($_GET['categorie']);
}

if (!empty($_POST['checkbox_categorie_delete'])) {
   $listNameCategorie = array();
   foreach ($_POST['checkbox_categorie_delete'] as $categorieDelete) {
      $reqNameCategorie = $pdo->prepare('SELECT * FROM sub_categorie WHERE sub_categorie_id = ?');
      $reqNameCategorie->execute([$categorieDelete]);
      $nameCategorie = $reqNameCategorie->fetch();
      array_push($listNameCategorie, $nameCategorie->sub_categorie_name);
   }
}

if (isset($_POST['delete_categorie'])) {
   if (empty($_POST['checkbox_categorie_delete'])) {
      $errors['checkbox_categorie'] = "Vous n'avez pas choisit de catégorie(s) a supprimer";
   } else {
      $i = 0;
      foreach ($_POST['checkbox_categorie_delete'] as $categorieDelete) {
         $reqVerifyDishesCategorie = $pdo->prepare('SELECT * FROM dishes WHERE sub_categorie_id = ?');
         $reqVerifyDishesCategorie->execute([$categorieDelete]);
         $verifyDishesCategorie = $reqVerifyDishesCategorie->fetch();
         if (!$verifyDishesCategorie) {
            $reqDeleteCategorie = $pdo->prepare('DELETE FROM sub_categorie WHERE sub_categorie_id = ?');
            $reqDeleteCategorie->execute([$categorieDelete]);
            $errors['categorie_delete'] .= 'Catégorie supprimé : ' . $listNameCategorie[$i] . '<br>';
         } else {
            $errors['no_delete_categorie'] = "Vous ne pouvez pas supprimer la catégorie " . $listNameCategorie[$i] . " car elle contient des plats";
         }

         $i++;
      }
   }
}
