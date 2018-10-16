/* global kijs */

// --------------------------------------------------------------
// dbadmin.DatabaseView
// --------------------------------------------------------------

dbadmin.DatabaseView = class dbadmin_DatabaseView extends kijs.gui.DataView {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super();

        // Config generieren
        config = Object.assign({}, this._createConfig(), config);

        // Config anwenden
        if (kijs.isObject(config)) {
            this.applyConfig(config, true);
        }
    }

    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------
    // PUBLIC
    // overwrite
    createElement(dataRow, index) {
        let html = '';

        html += '<div>';
        if (!dataRow['isDefault']) {            
            html += ' <span class="dbadmin-icon">&#xf1c0</span>';
        } else {
            html += ' <span class="dbadmin-defaulticon">&#xf1c0</span>';
        }
        html += ' <span class="value">' + dataRow['Datenbankname'] + '</span>';
        html += '</div>';
        html += '<div>';
        html += ' <span class="dbadmin-label">Importiert: ' + dataRow['Importdatum'] + '</span>';
        html += '</div>';
        html += '<div>';
        html += ' <span class="dbadmin-label">Geändert: ' + dataRow['Änderungsdatum'] + '</span>';
        html += '</div>';
        html += '<div>';
        html += ' <span class="dbadmin-label">Tabellen: ' + dataRow['AnzahlTabellen'] + '</span>';
        html += '</div>';
        html += '<div>';
        html += ' <span class="dbadmin-label">Grösse: ' + dataRow['DatenbankGrösse'] + '</span>';
        html += '</div>';
        
        let el = new kijs.gui.DataViewElement({
            dataRow: dataRow,
            html: html
        });
        
        if (dataRow['isDefault']) {
            el._dom.clsAdd('dbadmin-defaultdb');
        }
        return el;
    }

    // PROTECTED
    // Config definieren
    _createConfig() {
        const config = {
            name: 'dvDatabases',
            selectType: 'single',
            waitMaskTargetDomProperty: 'innerDom',
            autoLoad: false,
            facadeFnLoad: 'dbadmin.loadDbs',
            innerStyle:{
                padding: '10px',
                overflowY: 'auto'
            }
        };

        return config;
    }


    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct(preventDestructEvent) {
        // Event auslösen.
        if (!preventDestructEvent) {
            this.raiseEvent('destruct');
        }

        // Elemente/DOM-Objekte entladen

        // Variablen (Objekte/Arrays) leeren

        // Basisklasse auch entladen
        super.destruct(true);
    }
};