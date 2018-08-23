/** Sicherstellen, dass beim (neu)laden der Seite
 * die ModalBox auf default gesetzt wird
 */ 
window.onload = closeModalBox();

/**
 * Stellt fest, ob ein Datenbankname eingegeben wurde
 * @param {int} version
 * @returns {Element.value|Boolean}
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

        closeModalBox();
        overload.style.display = 'flex';
        load.style.display = 'flex';
        return true;
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
 * Modal-Box schliessen und Styles zurücksetzen
 * @returns {boolean}
 */
function closeModalBox() {
    var modalbox = document.getElementById('modalbox');
    var modalbox2 = document.getElementById('modalbox2');
    var items = document.getElementsByClassName('nosee');
    var form = document.getElementById('dbform');
    
    modalbox.style.display = "none";
    modalbox2.style.display = "none";
    form.reset();
    
    for (var i = 0; i < items.length; i++) {
        items[i].removeAttribute('style');
    }
    return false;
}


/**
 * Fragt nach, ob die Datenbank wirklich gelöscht/exportiert werden soll
 * @param {int} Id
 * @param {String} name
 * @returns {Boolean}
 */
function confirmDeleteOrExport(Id, name) {
    var load = document.getElementById('load');
    var overload = document.getElementById('overload');
    
    setHiddenField(Id);
    overload.style.display = 'flex';
    load.style.display = 'flex';
    
    if (confirm('Datenbank wirklich ' + name + '?')) {
        return true;
    } else {
        overload.style.display = 'none';
        load.style.display = 'none';
        return false;
    }
}


/**
 * Stellt sicher, dass eine Datenbank zum Duplizieren/Umbenennen
 * und ein neuer Datenbankname ausgewählt wird
 * @param {String} name
 * @returns {Boolean}
 */
function confirmDuplicateOrRename(name) {
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
        return confit ('Datenbank wirklich ' + name + '?');
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
 * Ausgewählte Datenbank in Hiddenfield schreiben
 * --> true = Datenbank ausgewählt
 * @param {int} cellId
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
 * Modalbox anzeigen
 * @param {int} Id
 * @param {String} name
 */
function showModalBox(Id, name) {
    setHiddenField(Id);
    var modalbox = document.getElementById('modalbox2');
    modalbox.style.display = "block";
    var button = document.getElementById(name);
    
    if (name === 'insert') {
        var select = document.getElementById('select');
        var checkbox = document.getElementById('checkboxlabel');
        select.style.display = "inline-block";
        checkbox.style.display = "inline-block";
        button.style.display = "inline-block";
    } else {
        var dbname = document.getElementById('dbname2');
        dbname.style = "display: inline-block; margin-top: 50px";
        dbname.focus();
        dbname.selectionStart += dbname.value.length;
        button.style = "margin-top: 50px; display: inline-block";
    }    
    
    var text = '';
    switch(name) {
        case 'insert': text = 'Dump importieren'; break;
        case 'duplicate': text = 'Datenbank duplizieren'; break;
        case 'rename': text = 'Datenbank umbenennen'; break;
    }
    
    var bartext = document.getElementById('titlebar2');
    bartext.innerHTML = text;
}


/**
 * Pop-Up zur Eingabe des Datenbanknamens anzeigen
 * Version: erstellen
 */
function showNameField() {
    var modalbox = document.getElementById('modalbox');    
    var name = document.getElementById('dbname');
    var button = document.getElementById('create');
    var bartext = document.getElementById('titlebar');
    
    modalbox.style.display = "block";
    name.style = "display: inline-block; margin-top: 50px";    
    button.style = "margin-top: 50px; display: inline-block";
    
    bartext.innerHTML = 'Neue Datenbank erstellen';
    
    name.focus();
    name.selectionStart += name.value.length;
}