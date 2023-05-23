<?php

// récuperer l'id de la personne qui réserve pour lui envoyer un mail de confirmation
$dateNow = new DateTime();
$dateCurrent = $dateNow->format('Y-m-d');
$dateMax = new DateTimeImmutable("+20 day");
$dateEnd = $dateMax->format('Y-m-d');

$listDateInsert = array();

for ($d = 0; $d < 21; $d++) {
   if ($d != 0 && $d != 21) {
      $dateInterval[$d] = new DateTimeImmutable("+" . $d . " day");
      $listDateInsert[$d] = [[$dateInterval[$d]->format('Y-m-d'), 1], [$dateInterval[$d]->format('Y-m-d'), 2]];
   } elseif ($d != 21) {
      $listDateInsert[$d] = [[$dateCurrent, 1], [$dateCurrent, 2]];
   } else {
      $listDateInsert[$d] = [[$dateEnd, 1], [$dateEnd, 2]];
   }
}

if (!empty($listDateInsert)) {
   // insertion des reservations et des dates en base de donnée
   $reqVerifyExistDateCalendar = $pdo->prepare('SELECT * FROM calendar WHERE date_calendar = ? AND part_day = ?');
   $reqInsertCalendar = $pdo->prepare('INSERT INTO calendar SET date_calendar = ?, part_day = ?, number_reservation = ?');

   foreach ($listDateInsert as $d => $dateInsert) {
      $numberReservation = 80;
      $reqVerifyExistDateCalendar->execute([$dateInsert[0][0], $dateInsert[0][1]]);
      $firstVerifyExist = $reqVerifyExistDateCalendar->fetch();

      $reqVerifyExistDateCalendar->execute([$dateInsert[1][0], $dateInsert[1][1]]);
      $secondVerify = $reqVerifyExistDateCalendar->fetch();
      if (!$firstVerifyExist) {
         $reqInsertCalendar->execute([$dateInsert[0][0], $dateInsert[0][1], $numberReservation]);
      }
      if (!$secondVerify) {
         $reqInsertCalendar->execute([$dateInsert[1][0], $dateInsert[1][1], $numberReservation]);
      }
   }
}

$reqSelectProfil = $pdo->prepare('SELECT * FROM profil WHERE profil_id = ?');
$reqSelectProfil->execute([$_SESSION['auth']->user_id]);
$selectProfil = $reqSelectProfil->fetch();
?>

<div class="modal fade" id="modal_calendar" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content" style="background-color: #f5cb5c;">
         <div class="modal-body">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <form method="get" class=" d-flex flex-column ">
               <label class="mb-3 mx-auto">
                  Choisissez une date de réservation
               </label>
               <div class="d-flex gap-2 justify-content-center">
                  <input class="form-control" type="date" id="calendar" name="calendar" max="<?= $dateEnd ?>" min="<?= $dateCurrent ?>">
                  <input class="btn" id="validate_calendar" type="button" value="Valider" style="background-color: #333533 ;color: #e8eddf;">
               </div>
            </form>


            <div id="display_button" class="d-none flex-column flex-wrap justify-content-center gap-3 align-items-center">
               <div id="first_part" class="d-flex flex-column align-items-center">
                  <h3 class="mt-2">Repas du midi</h3>

                  <div class="my-2" id="part_1">
                     <button style="background-color: #333533 ;color: #e8eddf;" class="btn" id="button_calendar_part1" name="button_calendar" value="11h">11h</button>
                     <button style="background-color: #333533 ;color: #e8eddf;" class="btn" id="button_calendar_part1" name="button_calendar" value="12h">12h</button>
                     <button style="background-color: #333533 ;color: #e8eddf;" class="btn" id="button_calendar_part1" name="button_calendar" value="13h">13h</button>
                  </div>
               </div>

               <div id="second_part" class="d-flex flex-column align-items-center">
                  <h3 class="mt-2">Repas du soir</h3>

                  <div class="my-2" id="part_2">
                     <button style="background-color: #333533 ;color: #e8eddf;" class="btn" id="button_calendar_part2" name="button_calendar" value="18h">18h</button>
                     <button style="background-color: #333533 ;color: #e8eddf;" class="btn" id="button_calendar_part2" name="button_calendar" value="19h">19h</button>
                     <button style="background-color: #333533 ;color: #e8eddf;" class="btn" id="button_calendar_part2" name="button_calendar" value="20h">20h</button>
                  </div>
               </div>
            </div>

            <script>
               const emailSession = "<?= $_SESSION['auth']->user_email ?>";
               const displayCalendar = document.getElementById('display_button')
               const validateCalendar = document.getElementById('validate_calendar')
               const inputDate = document.getElementById('calendar')


               // function returnNumber
               const firstPart = document.getElementById('first_part')
               const secondPart = document.getElementById('second_part')
               const buttonCalendarPart1 = document.querySelectorAll('#button_calendar_part1')
               const buttonCalendarPart2 = document.querySelectorAll('#button_calendar_part2')


               let numberPart1 = document.createElement('p')
               numberPart1.id = "number_reservation1"
               let numberPart2 = document.createElement('p')
               numberPart2.id = "number_reservation2"

               validateCalendar.addEventListener('click', (event) => {
                  const inputCalendar = inputDate.value
                  displayCalendar.classList.replace('d-none', 'd-flex')

                  returnNumberReservationPart1(inputCalendar)
                  returnNumberReservationPart2(inputCalendar)
               })

               // function
               function returnNumberReservationPart1(dateCalendar) {

                  partSelect1 = 1
                  xhrPart1 = new XMLHttpRequest()
                  if (dateCalendar != "") {
                     xhrPart1.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                           let test = xhrPart1.responseText
                           document.getElementById('number_reservation1').textContent = "Nombre de repas restant : " + test

                           const inputGet1 = document.createElement('input')
                           inputGet1.type = "hidden",
                              inputGet1.value = test,
                              inputGet1.id = "input_get_number1"

                           const firstPart = document.getElementById('first_part')
                           firstPart.append(inputGet1)

                        }
                     }
                     xhrPart1.open("POST", "./sys/planning/calendar/calendar_get.php", true)
                     xhrPart1.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                     xhrPart1.send("date=" + dateCalendar + "&part=" + partSelect1)
                  }
                  //const numberReservation1 = test

                  firstPart.insertAdjacentElement('beforeend', numberPart1)
               }

               function returnNumberReservationPart2(dateCalendar) {
                  partSelect2 = 2
                  // var test
                  // let numberReservation2;
                  xhrPart2 = new XMLHttpRequest()
                  if (dateCalendar != "") {
                     xhrPart2.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                           test = xhrPart2.responseText
                           document.getElementById('number_reservation2').textContent = "Nombre de repas restant : " + test

                           const inputGet2 = document.createElement('input')
                           inputGet2.type = "hidden",
                              inputGet2.value = test,
                              inputGet2.id = "input_get_number2"

                           const secondPart = document.getElementById('second_part')
                           secondPart.append(inputGet2)
                        }
                     }
                     xhrPart2.open("POST", "./sys/planning/calendar/calendar_get.php", true)
                     xhrPart2.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                     xhrPart2.send("date=" + dateCalendar + "&part=" + partSelect2)
                  }
                  secondPart.insertAdjacentElement('beforeend', numberPart2)
               }


               // string for User
               function insertData1(int, dateSelect, partSelect, emailUser, hoursSelect) {
                  xhr = new XMLHttpRequest()
                  if (int != "") {
                     xhr.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                           let test = xhr.responseText
                           document.getElementById('number_reservation1').textContent = "Nombre de repas restant : " + test
                        }
                     }
                     xhr.open("POST", "./sys/planning/calendar/calendar_post.php", true)
                     xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
                     xhr.send("number_reservation=" + int + "&date=" + dateSelect + "&part=" + partSelect + "&email=" + emailUser + "&hours=" + hoursSelect)
                  }
               }

               function insertData2(int, dateSelect, partSelect, emailUser, hoursSelect) {
                  xhr2 = new XMLHttpRequest()
                  if (int != "") {
                     xhr2.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                           let test = xhr2.responseText
                           document.getElementById('number_reservation2').textContent = "Nombre de repas restant : " + test
                        }
                     }
                     xhr2.open("POST", "./sys/planning/calendar/calendar_post.php", true)
                     xhr2.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
                     xhr2.send("number_reservation=" + int + "&date=" + dateSelect + "&part=" + partSelect + "&email=" + emailUser + "&hours=" + hoursSelect)
                  }

               }

               let dateUpdateBd = window.location.search.split('=')
               let verifyUser = document.createElement('p')
               verifyUser.className = "text-center mt-3"


               let inputNumber = document.createElement('input')
               inputNumber.type = "number"
               inputNumber.max = 25
               inputNumber.id = "number_reservation"
               inputNumber.className = "form-control text-center"

               const label = document.createElement('label')
               // label.textContent = "Veuillez rentrer le nombre de personne(s) :"
               const button = document.createElement('button')
               button.textContent = "Valider"
               button.style = 'background-color: #333533 ;color: #e8eddf;'
               button.className = 'btn'
               const button2 = document.createElement('button')
               button2.textContent = "Valider"
               button2.value = 2
               button2.style = 'background-color: #333533 ;color: #e8eddf;'
               button2.className = 'btn'


               const hours = document.createElement('input')
               hours.type = "hidden"
               hours.id = "hours_input"

               if (buttonCalendarPart1) {
                  buttonCalendarPart1.forEach(element => {
                     element.addEventListener('click', (event) => {

                        verifyUser.innerHTML = "Pour confirmer la réservation de " + event.currentTarget.value + "<br>Veuillez rentrer le nombre de personne(s) :"
                        hours.value = event.currentTarget.value

                        displayCalendar.insertAdjacentElement('afterbegin', hours)
                        button2.remove()
                        if (inputNumber) {
                           inputNumber.value = <?= $selectProfil->profil_default_reservation ?>
                        }
                        displayCalendar.insertAdjacentElement('afterbegin', button)
                        displayCalendar.insertAdjacentElement('afterbegin', inputNumber)
                        displayCalendar.insertAdjacentElement('afterbegin', verifyUser)
                     })
                  })
               }

               if (buttonCalendarPart2) {
                  buttonCalendarPart2.forEach(element => {
                     element.addEventListener('click', (event) => {
                        verifyUser.innerHTML = "Pour confirmer la réservation de " + event.currentTarget.value + "<br>Veuillez rentrer le nombre de personne(s) :"
                        hours.value = event.currentTarget.value

                        displayCalendar.insertAdjacentElement('afterbegin', hours)
                        button.remove()
                        if (inputNumber) {
                           inputNumber.value = <?= $selectProfil->profil_default_reservation ?>
                        }
                        displayCalendar.insertAdjacentElement('afterbegin', button2)
                        displayCalendar.insertAdjacentElement('afterbegin', inputNumber)
                        displayCalendar.insertAdjacentElement('afterbegin', verifyUser)

                     })
                  })
               }
               const partOne = document.getElementById('part_1')
               const partTwo = document.getElementById('part_2')


               button.addEventListener('click', (event) => {
                  const numberReservation1 = document.getElementById('input_get_number1').value
                  part1 = 1
                  if (inputNumber.value > numberReservation1) {
                     alert('Vous ne pouvez pas réserver plus de personne que le nombre de réservation possible !')
                  } else if (inputNumber.value > 25) {
                     alert('Vous ne pouvez pas réserver pour plus de 25 personne !')
                  } else {
                     const inputHours = document.getElementById('hours_input')
                     insertData1(inputNumber.value, inputDate.value, part1, emailSession, inputHours.value)

                     displayCalendar.removeChild(button)
                     displayCalendar.removeChild(inputNumber)
                     partOne.remove()
                     partTwo.remove()
                     // displayCalendar.remove(buttonCalendarPart1)
                     // displayCalendar.remove(buttonCalendarPart2)

                     const pSucces = document.createElement('p')
                     pSucces.innerHTML = "Votre réservation a bien était enregistré"
                     pSucces.className = "text-center alert alert-success"
                     displayCalendar.append(pSucces)
                  }
               })

               button2.addEventListener('click', (event) => {
                  const numberReservation2 = document.getElementById('input_get_number2').value
                  part2 = 2
                  if (inputNumber.value > numberReservation2) {
                     alert('Vous ne pouvez pas réserver pour plus de personne que le nombre de réservation possible !')
                  } else if (inputNumber.value > 25) {
                     alert('Vous ne pouvez pas réserver pour plus de 25 personne !')
                  } else {
                     const inputHours = document.getElementById('hours_input')
                     insertData2(inputNumber.value, inputDate.value, part2, emailSession, inputHours)

                     displayCalendar.removeChild(button2)
                     displayCalendar.removeChild(inputNumber)
                     partOne.remove()
                     partTwo.remove()

                     const pSucces = document.createElement('p')
                     pSucces.innerHTML = "Votre réservation a bien était enregistré"
                     pSucces.className = "text-center alert alert-success"
                     displayCalendar.append(pSucces)
                  }
               })
            </script>
            <?php // endif; 
            ?>
         </div>
      </div>
   </div>
</div>