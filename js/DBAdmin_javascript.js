
/**
 * Prüft, ob ein Datenbankname eingegeben wurde.
 * @param (int) version
 * @returns {Boolean|Element.value}
 */
function checkDbname(version) {
    if (version === 1) {
        var dbname = document.getElementById('dbname').value;
    } else {
        var dbname = document.getElementById('dbname2').value;
    }
    
    if (dbname === '') {
        alert('Kein Datenbankname gewählt.');
        return false;
    } else {
        return dbname;
    }
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
        var dbname = document.getElementById('dbname').value;
        
        if (dbname !== '') {
            closeModalBox();
            overload.style.display = 'flex';
            load.style.display = 'flex';
            return true;
        } else {
            alert("Kein Datenbankname eingegeben!");
            return false;
        }
    }
}


/**
 * Überprüft ob ein Benutzername und ein Passwort eingegeben wurden
 * @returns {Boolean}
 */
function checkFields() {
    $username = document.getElementById('username');
    $password = document.getElementById('passwort');
    
    if ($username.value === '' || $password.value === '') {
        alert('Bitte fülle alle Felder aus!');
        return false;
    } else {
        return true;
    }
}


/**
 * Ausgewählte Datenbank in Hiddenfiel schreiben
 * --> true = Datenbank ausgewählt
 * @param {int} cellId
 * @returns {Boolean}
 */
function setHiddenField(cellId) {
    var hiddenField = document.getElementById('hiddenfield');
    
    var id = 'td'+cellId;
    var rows = document.getElementsByClassName('tablerows');
    var cells = document.getElementsByClassName('tablecells');
    
    for (var i = 0; i < rows.length; i++) {
        if (rows[i].id === id) {
            hiddenField.value = cells[i].innerHTML;
            break;
        }
    }
}


/**
 * Fragt nach, ob die Datenbank wirklich gelöscht werden soll
 * @param {int} Id
 * @returns {Boolean}
 */
function confirmDelete(Id) {
    var load = document.getElementById('load');
    var overload = document.getElementById('overload');
    
    setHiddenField(Id);
    overload.style.display = 'flex';
    load.style.display = 'flex';
    if (confirm('Willst du die Ausgewählte Datenbank wirklich löschen?')) {
        return true;
    } else {
        overload.style.display = 'none';
        load.style.display = 'none';
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
    var load = document.getElementById('load');
    var overload = document.getElementById('overload');
    
    var dbname = checkDbname(2);
    
    if (dbname === hiddenfield) {
            alert('Der neue Datenbankname muss sich vom aktuellen Namen unterscheiden!');
            return false;
    } else if (!dbname) {
        return false;
    } else {
        overload.style.display = 'flex';
        load.style.display = 'flex';
        closeModalBox();
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
 * Fragt nach, ob die Datenbank wirklich umbenannt werden soll
 * @returns {Boolean}
 */
function confirmRename() {
    var hiddenfield = document.getElementById('hiddenfield').value;
    var load = document.getElementById('load');
    var overload = document.getElementById('overload');
    
    var dbname = checkDbname(2);
    
    if (dbname === hiddenfield) {
            alert('Der neue Datenbankname muss sich vom aktuellen Namen unterscheiden!');
            return false;
    } else if (!dbname) {
        return false;
    } else {
        overload.style.display = 'flex';
        load.style.display = 'flex';
        closeModalBox();
        return confirm('Willst du die Datenbank wirklich umbenennen?');
    }
}


/**
 * Pop-Up zur Eingabe des Datenbanknamens anzeigen
 * Version: duplizieren
 * @param {int} Id
 */
function showDuplicate(Id) {
    setHiddenField(Id);
    var modalbox2 = document.getElementById('modalbox2');
    var duplicate = document.getElementById('duplicate');
    var name = document.getElementById('dbname2');
    modalbox2.style.display = "block";
    name.style = "margin-top: 50px";
    duplicate.style = "margin-top: 50px; display: inline-block";
}


/**
 * Pop-Up zur Eingabe des Datenbanknamens anzeigen
 * Version: importieren
 */
function showDumps() {
    var modalbox = document.getElementById('modalbox');
    var insert = document.getElementById('insert');
    var select = document.getElementById('select');
    var checkbox = document.getElementById('checkboxlabel');
    insert.style.display = "inline-block";
    modalbox.style.display = "block";
    select.style.display = "block";
    checkbox.style.display = "inline-block";
}


/**
 * Pop-Up zur Eingabe des Datenbanknamens anzeigen
 * Version: erstellen
 */
function showNameField() {
    var modalbox = document.getElementById('modalbox');
    var create = document.getElementById('create');
    var name = document.getElementById('dbname');
    modalbox.style.display = "block";
    name.style = "margin-top: 50px";
    create.style = "margin-top: 50px; display: inline-block";
}


/**
 * Pop-Up zur Eingabe des Datenbanknamens anzeigen
 * Version: umbenennen
 * @param {int} Id
 */
function showRename(Id) {
    setHiddenField(Id);
    var modalbox2 = document.getElementById('modalbox2');
    var rename = document.getElementById('rename');
    var name = document.getElementById('dbname2');
    modalbox2.style.display = "block";
    name.style = "margin-top: 50px";
    rename.style = "margin-top: 50px; display: inline-block";
}


/**
 * Modal-Box schliessen und Styles zurücksetzen
 * @returns {boolean}
 */
function closeModalBox() {
    var modalbox = document.getElementById('modalbox');
    var modalbox2 = document.getElementById('modalbox2');
    var items = document.getElementsByClassName('nosee');
    
    modalbox.style.display = "none";
    modalbox2.style.display = "none";
    
    for (var i = 0; i < items.length; i++) {
        items[i].removeAttribute('style');
    }
    return false;
};