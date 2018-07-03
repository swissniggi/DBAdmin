/**
 * Überprüft ob ein Benutzername und ein Passwort eingegeben wurden
 * @returns {Boolean}
 */
function checkFields() {
    $username = document.getElementById('username');
    $password = document.getElementById('passwort');
    
    if ($username.value == '' || $password.value == '') {
        alert('Bitte fülle alle Felder aus!');
        return false;
    } else {
        return true;
    }
}


/**
 * Ändert die Hintergrundfarbe der ausgewählten Tabellenzelle
 * Schreibt den Namen der ausgewählten DB ins hiddenField
 * @param {integer} cellId
 * @returns {undefined}
 */
function changeColor(cellId) {
    var id = 'td'+cellId;
    var rows = document.getElementsByClassName('tablerows');
    var cells = document.getElementsByClassName('tablecells');
    
    for (var i = 0; i < rows.length; i++) {
        if (rows[i].id == id) {
            if (rows[i].style.backgroundColor !== 'lightpink') {
                rows[i].style.backgroundColor = 'lightpink';
                document.getElementById('hiddenfield').value = cells[i].innerHTML;
            } else {
                if (i%2 === 0) {
                    rows[i].style.backgroundColor = 'white';
                } else {
                    rows[i].style.backgroundColor = 'aliceblue';
                }
                document.getElementById('hiddenfield').value = '';
            }
        } else {
            if (i%2 === 0) {
                rows[i].style.backgroundColor = 'white';
            } else {
                rows[i].style.backgroundColor = 'aliceblue';
            }
        }
    }        
}


/**
 * Fragt nach, ob die Datenbank wirklich gelöscht werden soll
 * @returns {Boolean}
 */
function confirmDelete() {
    var load = document.getElementById('load');
    var overload = document.getElementById('overload');
    
    if (checkHiddenField()) {
        overload.style.display = 'block';
        load.style.display = 'block';
        return confirm('Willst du die Ausgewählte Datenbank wirklich löschen?');
    } else {
        return false;
    }
}


/**
 * Fragt nach, ob die Datenbank wirklich umbenannt werden soll
 * @returns {Boolean}
 */
function confirmRename() {
    var hiddenfield = document.getElementById('hiddenfield').value;
    var dbname = document.getElementById('dbname').value;
    var load = document.getElementById('load');
    var overload = document.getElementById('overload');
    
    if (checkHiddenField() && checkDbname()) {
        if (hiddenfield === dbname) {
            alert('Der neue Datenbankname muss sich vom aktuellen Namen unterscheiden!');
            return false;
        } else {
            overload.style.display = 'block';
            load.style.display = 'block';
            return confirm('Willst du die Datenbank wirklich umbenennen?');
        }
    } else {
        return false;
    }
}


/**
 * Stellt sicher, dass eine Datenbank zum Duplizieren
 * und ein neuer Datenbankname ausgewählt wird
 * @returns {Boolean}
 */
function confirmDuplicate() {
    var hiddenfield = document.getElementById('hiddenfield').value;
    var dbname = document.getElementById('dbname').value;
    var load = document.getElementById('load');
    var overload = document.getElementById('overload');
    
    
    if (checkHiddenField() && checkDbname()) {
        if (hiddenfield === dbname) {
            alert('Der neue Datenbankname muss sich vom aktuellen Namen unterscheiden!');
            return false;
        } else {
            overload.style.display = 'block';
            load.style.display = 'block';
            return true;
        }
    } else {
        return false;
    }
}


/**
 * Prüft, ob das Hiddenfield einen Wert enthält
 * --> true = Datenbank ausgewählt
 * @returns {Boolean}
 */
function checkHiddenField() {
    var hiddenField = document.getElementById('hiddenfield');
    
    if (hiddenField.value == '') {
        alert('Keine Datenbank ausgewählt.');
        return false;
    } else {
        return true;       
    }
}


/**
 * Prüft, ob ein Datenbankname eingegeben wurde.
 * @returns {Boolean}
 */
function checkDbname() {
    var dbname = document.getElementById('dbname');
    
    if (dbname.value == '') {
        alert('Kein Datenbankname gewählt.');
        return false;
    } else {
        return true;
    }
}


/**
 * Fragt nach, ob sich der Benutzer wirklich ausloggen möchte
 * @returns {Boolean}
 */
function confirmLogout() {
    return confirm('Willst du dich wirklich ausloggen?');
}


/**
 * Führt eine Check-Routine für 'Dump importieren' aus
 * @returns {Boolean}
 */
function checkDump() {
    var select = document.getElementById('select');
    var option = select.options[select.selectedIndex].value;
    
    if (option === '') {        
        alert('Kein Dump ausgewählt!');
        return false;
    } else {
        var load = document.getElementById('load');
        var overload = document.getElementById('overload');
        
        if (document.getElementById('hiddenfield').value !== '') {
            if (confirm('Willst du die Datenbank wirklich überschreiben?')) {
                overload.style.display = 'block';
                load.style.display = 'block';
                return true;
            } else {
                return false;
            }
        } else if (document.getElementById('dbname').value !== '') {
            overload.style.display = 'block';
            load.style.display = 'block';
            return true;
        } else {
            alert('Du musst eine Datenbank aus der Tabelle auswählen \noder einen Datenbanknamen eingeben!');
            return false;
        }
    }
}


