
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
    } else if (confirm('Dump wirklich importieren?')) {
        var load = document.getElementById('load');
        var overload = document.getElementById('overload');

        closeModalBox('insert');
        overload.style.display = 'flex';
        load.style.display = 'flex';
        return true;
    } else {
        return false;
    }
}


/**
 * Überprüft ob ein Benutzername und ein Passwort eingegeben wurden
 * @returns {Boolean}
 */
function checkFields() {
    var username = document.getElementById('username');
    var password = document.getElementById('passwort');
    
    if (username.value === '' || password.value === '') {
        alert('Bitte fülle alle Felder aus!');
        return false;
    } else {
        return true;
    }
}


/**
 * Modal-Box schliessen und Styles zurücksetzen
 * @param {String} name
 * @param {Event} event
 * @returns {boolean}
 */
function closeModalBox(name, event) {
    var modalbox = document.getElementById('modalbox_' + name);
    var items = document.getElementsByClassName('nosee');
    
    modalbox.style.display = "none";
    
    for (var i = 0; i < items.length; i++) {
        items[i].removeAttribute('style');
        
    }
    
    // folgenden Code nur ausführen, wenn auf 'X' geclickt wurde
    if (typeof event !== 'undefined') {
        for (i = 0; i < items.length; i++) {
            if (items[i].type === 'text') {
                items[i].value = '';
            }
        }
        return false;
    }
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
    var insert = name === 'duplicate' ? 'duplizieren' : 'umbenennen';
    
    if (dbname === hiddenfield) {
        alert('Der neue Datenbankname muss sich vom aktuellen Namen unterscheiden!');
        return false;
    } else if (dbname && confirm('Datenbank wirklich ' + insert + '?')) {
        closeModalBox(name);
        overload.style.display = 'flex';
        load.style.display = 'flex';       
        return true;
    } else {
        return false;
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
 * Button-Event ausführen
 * @param {string} name
 * @returns {Boolean|Element.value}
 */
function executeEvent(name) {
    switch(name) {
        case 'create': return checkDbname(name); break;
        case 'insert': return checkDump(); break;
        case 'duplicate': 
        case 'rename': return confirmDuplicateOrRename(name); break;
    }
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
    
    if (name !== 'insert') {  
        var dbname = document.getElementById('dbname_' + name);
        var button = document.getElementById(name);
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