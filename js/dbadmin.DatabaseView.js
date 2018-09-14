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
        html += ' <span class="dbadmin-icon">&#xf1c0</span>';
        html += ' <span class="value">' + dataRow['Datenbankname'] + '</span>';
        html += '</div>';
        html += '<div>';
        html += ' <span class="dbadmin-label">Importdatum: ' + dataRow['Importdatum'] + '</span>';
        html += '</div>';
        html += '<div>';
        html += ' <span class="dbadmin-label">Änderungsdatum: ' + dataRow['Änderungsdatum'] + '</span>';
        html += '</div>';
        html += '<div>';
        html += ' <span class="dbadmin-label">Anzahl Tabellen: ' + dataRow['AnzahlTabellen'] + '</span>';
        html += '</div>';
        html += '<div>';
        html += ' <span class="dbadmin-label">Datenbankgrösse: ' + dataRow['DatenbankGrösse'] + '</span>';
        html += '</div>';
        
        return new kijs.gui.DataViewElement({
            dataRow: dataRow,
            html: html
        });
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
            style:{
                width: '100%'
            },
            innerStyle:{
                padding: '10px',
                overflowY: 'auto',
                flex: 'initial'
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