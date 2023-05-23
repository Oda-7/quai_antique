<?php

//reglé l'erreur de suppression de role


if (isset($_POST['delete_role'])) {
   $listNameRole = array();
   if (!empty($_POST['checkbox_delete_roles'])) {
      foreach ($_POST['checkbox_delete_roles'] as $roleDelete => $value) {
         $reqNameRole = $pdo->prepare('SELECT * FROM role WHERE role_id = ?');
         $reqNameRole->execute([$value]);
         $nameRole = $reqNameRole->fetch();
         array_push($listNameRole, $nameRole->role_name);

         $reqDelete = $pdo->prepare('DELETE FROM role WHERE role_id = ?');
         $reqDelete->execute([$value]);
         $errors['role_delete'] .= 'Role supprimé :' . $listNameRole[$roleDelete] . '<br>';
      }
   } else {
      $errors['role_checkbox'] = "Vous n'avez pas cocher de role a supprimer";
   }
}

if (!empty($_POST['add_role'])) {
   $reqAddRole = $pdo->prepare('INSERT INTO role SET role_name = ?');
   $reqAddRole->execute([ucfirst($_POST['role_name'])]);
   $_SESSION['flash']['success'] = 'Le role a été ajouté';
}

if (isset($_POST['modify_role'])) {
   $reqUpdateRole = $pdo->prepare('UPDATE role SET role_name = ? WHERE role_id = ?');
   $reqUpdateRole->execute([ucfirst($_POST['role_name_modify']), $_GET['role']]);
   $_SESSION['flash']['success'] = 'Le role a été modifié';
}
