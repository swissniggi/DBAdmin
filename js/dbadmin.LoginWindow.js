
// --------------------------------------------------------------
// dbadmin.LoginWindow
// --------------------------------------------------------------

dbadmin.LoginWindow = class dbadmin_LoginWindow extends kijs.gui.Window {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super();
        
        this._formPanel = null;
        
        // Config generieren
        config = Object.assign({}, this._createConfig(), config);
        
         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            facadeFnSave: { target: 'facadeFnSave', context: this._formPanel },
            rpc: { target: 'rpc', context: this._formPanel }
        });
        
        // Event-Weiterleitungen von this._formPanel
        //this._eventForwardsAdd('afterSave', this._formPanel);
        
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
    
    get rpc() { return this._formPanel.rpc; }
    set rpc(val) { this._formPanel.rpc = val; }
    
    
    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------
    // PROTECTED
    // Config definieren
    _createConfig() {
        this._formPanel = this._createFormPanel();
        
        const config = {
            caption: 'Login',
            iconChar: '&#xf023',
            width: 300,
            closable: false,
            maximizable: false,
            resizable: false,
            elements:[
                this._formPanel
            ]
        };
        
        return config;
    }
    
    // FormPanel definieren
    _createFormPanel() {
        return new kijs.gui.FormPanel({
            xtype: 'kijs.gui.FormPanel',
            name: 'loginFormPanel',
            defaults:{
                width: 280,
                height: 25,
                style:{
                    margin: '10px'
                }
            },
            elements:[
                {
                    xtype: 'kijs.gui.field.Text',
                    labelWidth: 80,
                    required: true,                    
                    name: 'username',
                    label: 'Benutzer'
                },{
                    xtype: 'kijs.gui.field.Text',
                    labelWidth: 80,
                    required: true,
                    name: 'password',
                    label: 'Passwort'
                }
            ],
            footerStyle:{
                padding: '10px'
            },
            footerElements:[
                {
                    xtype: 'kijs.gui.Button',
                    name: 'btnLogin',
                    iconChar: '&#xf00c',
                    isDefault: true,
                    width: 100,
                    height: 30,
                    caption: 'Login',
                    on:{
                        click: this._onBtnLoginClick,
                        context: this
                    }
                }
            ]
        });
    }
    
    // LISTENERS
    _onBtnLoginClick(e) {
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
        
        // Basisklasse auch entladen
        super.destruct(true);
    }
};