/* global this, kijs */

// --------------------------------------------------------------
// dbadmin.LoginWindow
// --------------------------------------------------------------

dbadmin.ActionWindow = class dbadmin_ActionWindow extends kijs.gui.Window {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super();

        this._formPanel = null;
        this._textField = null;

        // Config generieren
        config = Object.assign({}, this._createConfig(), config);

         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            facadeFnSave: { target: 'facadeFnSave', context: this._formPanel },
            data: { target: 'data', context: this._formPanel },
            value: { target: 'value', context: this._textField },
            rpc: { target: 'rpc', context: this._formPanel }
        });

        // Event-Weiterleitungen von this._formPanel
        this._eventForwardsAdd('afterSave', this._formPanel);

        // Config anwenden
        if (kijs.isObject(config)) {
            this.applyConfig(config, true);
        }
    }


    // --------------------------------------------------------------
    // GETTERS / SETTERS
    // --------------------------------------------------------------
    get facadeFnSave() { return this._formPanel.facadeFnSave; }
    set facadeFnSave(val) { this._formPanel.facadeFnSave = val; }
    
    get data() { return this._formPanel.data; }
    set data(val) {this._formPanel.data = val; }

    get rpc() { return this._formPanel.rpc; }
    set rpc(val) { this._formPanel.rpc = val; }
    
    get value() { return this._textField.value; }
    set value(val) {this._textField.value = val; }


    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------
    // PROTECTED
    // Config definieren
    _createConfig() {
        this._textField = this._createTextField();
        this._formPanel = this._createFormPanel();        

        const config = {
            width: 400,
            closable: true,
            maximizable: false,
            resizable: false,
            modal: true,
            defaults:{
                width: 380,
                height: 25,
                style:{
                    margin: '10px 5px 10px 5px'
                }
            },
            elements:[
                this._formPanel
            ]
        };
        
        return config;
    }

    // FormPanel definieren
    _createFormPanel() {
        return new kijs.gui.FormPanel({
            name: 'actionFormPanel',            
            elements:[
                this._textField
            ],
            footerStyle:{
                padding: '10px'
            },
            footerElements:[
                {
                    xtype: 'kijs.gui.Button',
                    name: 'btnAction',
                    iconChar: '&#xf00c',
                    isDefault: true,
                    caption: 'OK',
                    on:{
                        click: this._onBtnActionClick,
                        context: this
                    }
                }
            ]
        });
    }
    
    // Textfeld definieren
    // muss separat sein, damit das 'value' zugewiesen werden kann
    _createTextField() {
        return new kijs.gui.field.Text({
            width: 380,
            height: 25,
            labelWidth: 180,
            required: true,
            name: 'newDbName',
            label: 'Neuer Datenbankname',
            helpText: 'Erlaubte Zeichen:<br />-Kleinbuchstaben<br />-Underlines<br />Keine Umlaute!',
            style:{
                margin: '10px'
            }
        });
    }

    // LISTENERS
    _onBtnActionClick(e) {
        this._formPanel.save();
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
        this._formPanel = null;
        this._textField = null;

        // Basisklasse auch entladen
        super.destruct(true);
    }
};