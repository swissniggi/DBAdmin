
/**
 * Stellt fest, ob ein Datenbankname eingegeben wurde
 * @param {String} name
 * @returns {Element.value|Boolean}
 */
function checkDbname(name) {
    var dbname = document.getElementById('dbname_' + name).value;
    
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
 * @param {String} name
 * @returns {boolean}
 */
function closeModalBox(name) {
    var modalbox = document.getElementById('modalbox_' + name);
    var items = document.getElementsByClassName('nosee');
    var forms = document.getElementsByClassName('dbform');
    
    modalbox.style.display = "none";
    for (var form in forms) {
        form.reset();
    }
    
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
    
    setHiddenField(Id, name);
    overload.style.display = 'flex';
    load.style.display = 'flex';
    name = name === 'delete' ? 'löschen' : 'exportieren';
    
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
    var hiddenfield = document.getElementById('hiddenfield_' + name).value;
    var load = document.getElementById('load');
    var overload = document.getElementById('overload');
    
    var dbname = checkDbname(name);
    
    if (dbname === hiddenfield) {
            alert('Der neue Datenbankname muss sich vom aktuellen Namen unterscheiden!');
            return false;
    } else if (!dbname) {
        return false;
    } else {
        overload.style.display = 'flex';
        load.style.display = 'flex';
        closeModalBox();
        name = name === 'duplicate' ? 'duplizieren' : 'umbenennen';
        return confirm('Datenbank wirklich ' + name + '?');
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
 * @param {int} Id
 * @param {String} name
 */
function setHiddenField(Id, name) {
    if (name === 'delete' || name === 'export') {
        name = name + '_' + Id;
    }
    var hiddenField = document.getElementById('hiddenfield_' + name);
    
    var id = 'td'+Id;
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
    if (Id !== 0) {
        setHiddenField(Id, name);
    }
    
    var modalbox = document.getElementById('modalbox_' + name);
    modalbox.style.display = "block";
    var button = document.getElementById(name);
    
    if (name === 'insert') {
        button.style = "display: inline-block";
    } else {
        var dbname = document.getElementById('dbname_' + name);
        dbname.style = "margin-top: 50px";
        dbname.focus();
        dbname.selectionStart += dbname.value.length;
        button.style = "margin-top: 50px";
    }
    
    var text = '';
    switch(name) {
        case 'create': text = 'Neue Datenbank erstellen'; break;
        case 'insert': text = 'Dump importieren'; break;
        case 'duplicate': text = 'Datenbank duplizieren'; break;
        case 'rename': text = 'Datenbank umbenennen'; break;
    }
    
    var bartext = document.getElementById('titlebar_' + name);
    bartext.innerHTML = text;
}