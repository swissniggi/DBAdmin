
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
    // GETTERS / SETTERS
    // --------------------------------------------------------------
    
    
    
    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------
    // PUBLIC
    createElement(dataRow, index) {
        let icon = '&#xf1c0';
        let html = '';

        html += '<div class="dataViewDiv">';
        html += ' <span class="dbadmin-icon">' + icon + '</span>';
        html += '</div>';

        kijs.Object.each(dataRow, function(key, val) {
            if (key === 'Datenbankname') {
                html += '<div>';
                html += ' <span class="value">' + val + '</span>';
                html += '</div>';
            } else {
                html += '<div>';
                html += ' <span class="dbadmin-label">' + key + ': ' + val + '</span>';
                html += '</div>';
            }
            
        }, this);
        
        return new kijs.gui.DataViewElement({
            dataRow: dataRow,
            html: html
        });
    }
    
    // PROTECTED
    _createConfig() {
        
        const config = {
            name: 'dvDatabases',
            selectType: 'single',
            waitMaskTargetDomProperty: 'innerDom',
            autoLoad: true,
            facadeFnLoad: 'dbadmin.loadDbs',
            innerStyle: {
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
};/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


