/* global kijs, this */

// --------------------------------------------------------------
// dbadmin.LoginWindow
// --------------------------------------------------------------

dbadmin.SelectWindow = class dbadmin_SelectWindow extends kijs.gui.Window {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super();

        this._comboField = null;
        this._formPanel = null;

        // Config generieren
        config = Object.assign({}, this._createConfig(), config);

         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            facadeFnLoad: { target: 'facadeFnLoad', context: this._comboField },
            facadeFnSave: { target: 'facadeFnSave', context: this._formPanel },
            data: { target: 'data', context: this._formPanel },
            rpcComboField: { target: 'rpc', context: this._comboField },
            rpcFormPanel: { target: 'rpc', context: this._formPanel }
        });

        // Event-Weiterleitungen von this._formPanel
        this._formPanel.on('afterSave', function(e) {
            this.raiseEvent('afterSave', e);
        }, this );

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

    get facadeFnLoad() { return this._comboField.facadeFnLoad; }
    set facadeFnLoad(val) { this._ComboField.facadeFnLoad = val; }

    get data() { return this._formPanel.data; }
    set data(val) { this._formPanel.data = val; }

    get rpcFormPanel() { return this._formPanel.rpc; }
    set rpcFormPanel(val) { this._formPanel.rpc = val; }

    get rpcComboField() { return this._comboField.rpc; }
    set rpcComboField(val) { this._comboField.rpc = val; } 


    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------
    // PROTECTED
    // Config definieren
    _createConfig() {
        this._comboField = this._createCombo();
        this._formPanel = this._createFormPanel();

        const config = {
            iconChar: '&#xf019',
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

    // Combofeld definieren
    _createCombo() {
        return new kijs.gui.field.Combo({
            name: 'dumps',
            required: true,
            autoLoad: true,
            style:{
                margin: '10px'
            }
        });
    }

    // FormPanel definieren
    _createFormPanel() {
        return new kijs.gui.FormPanel({
            xtype: 'kijs.gui.FormPanel',
            name: 'actionFormPanel',
            defaults:{
                width: 380,
                height: 25,
                style:{
                    margin: '10px'
                }
            },
            elements:[
                this._comboField,
                {
                    xtype: 'kijs.gui.field.Checkbox',
                    name: 'delete',
                    caption: 'Dump nach import löschen'
                }
            ],
            footerStyle:{
                padding: '10px'
            },
            footerElements:[
                {
                    xtype: 'kijs.gui.Button',
                    name: 'btnImport',
                    iconChar: '&#xf00c',
                    isDefault: true,
                    width: 120,
                    height: 30,
                    caption: 'Importieren',
                    on:{
                        click: this._onBtnActionClick,
                        context: this
                    }
                }
            ]
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
        this._comboField = null;
        this._formPanel = null;

        // Basisklasse auch entladen
        super.destruct(true);
    }
};