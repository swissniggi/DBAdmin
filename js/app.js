// --------------------------------------------------------------
// Facade
// --------------------------------------------------------------
kit = {};
kit.App = class kit_App {
        

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {

        // RPC-Instanz
        var rpcConfig = {};
        if (config.ajaxUrl) {
            rpcConfig.url = config.ajaxUrl;
        }
        this._rpc = new kijs.gui.Rpc(rpcConfig);
        
        this.viewport = null;
    }


    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------
    run() {
        let _this = this;
        
        // ViewPort erstellen
        this.viewport = new kijs.gui.ViewPort({
            cls: 'kijs-flexcolumn',            
            elements: [
                {
                    xtype: 'kijs.gui.Panel',
                    name: 'mainPanel',
                    caption: 'DBAdmin',
                    footerCaption: '&copy; by Nicolas Burgunder',
                    iconCls: 'icoWizard16',
                    cls: 'kijs-flexrow',
                    elements:[
                        {
                            xtype: 'kijs.gui.DataView',
                            selectType: 'single',
                            rpc: this._rpc, 
                            waitMaskTargetDomProperty: 'innerDom',
                            autoLoad: true,
                            facadeFnLoad: 'dbadmin.loadDbs',
                            style: {
                                flex: 1
                            },
                            innerStyle: {
                                padding: '10px',
                                overflowY: 'auto'
                            }
                        }
                    ],
                    headerElements:[
                        {
                            xtype: 'kijs.gui.Button',
                            name: 'btnCreate',
                            html: '<img src="img/create.PNG" style="width: 25px" alt="DB erstellen"></img>',
                            on:{
                                click: function(){
                                    _this.showCreateWindow();
                                }
                            }
                        },{
                            xtype: 'kijs.gui.Button',
                            name: 'btnLogout',
                            html: '<img src="img/logout.PNG" style="width: 25px" alt"Ausloggen"></img>',
                            on:{
                                click: function(){
                                    sessionStorage.removeItem('ID');
                                    _this._rpc.do('dbadmin.logout', null, 
                                    function(response) {
                                        if (response.data.success === 'true') {
                                            kijs.gui.CornerTipContainer.show('Info', 'Du wurdest erfolgreich ausgelogt', 'info');
                                            this.showLoginWindow();
                                            // DataView leeren
                                            this.viewport.elements[0].elements[0].load(null);
                                            
                                        } else {
                                            kijs.gui.MsgBox.error('Fehler', response.data);       
                                        }
                                    }, _this, false, this.parent, 'dom', false);
                                }
                            }
                        }
                    ],
                    footerElements:[
                        {
                            xtype: 'kijs.gui.Button',
                            name: 'btnDelete',
                            html: '<img src="img/trash.PNG" style="width: 25px" alt="DB löschen"></img>',
                            style:{
                                border: 'none'
                            }
                        },{
                            xtype: 'kijs.gui.Button',
                            name: 'btnImport',
                            html: '<img src="img/import.PNG" style="width: 25px" alt="Dump importieren"></img>',
                            style:{
                                border: 'none'
                            }
                        },{
                            xtype: 'kijs.gui.Button',
                            name: 'btnExport',
                            html: '<img src="img/export.PNG" style="width: 25px" alt="Dump exportieren"></img>',
                            style:{
                                border: 'none'
                            }
                        },{
                            xtype: 'kijs.gui.Button',
                            name: 'btnDuplicate',
                            html: '<img src="img/duplicate.PNG" style="width: 25px" alt="DB duplizieren"></img>',
                            style:{
                                border: 'none'
                            }
                        },{
                            xtype: 'kijs.gui.Button',
                            name: 'btnRename',
                            html: '<img src="img/edit.PNG" style="width: 25px" alt="DB umbenennen"></img>',
                            style:{
                                border: 'none'
                            }
                        }                     
                    ]
                }
            ]
        });
        this.viewport.render();
        if (!sessionStorage.getItem('ID')) {
            this.showLoginWindow();
        } else {
            this.viewport.elements[0].elements[0].load();
        }
    }
    
    
    showCreateWindow() {
        let _this = this;
        // Create-Window erstellen
        let createWindow = new dbadmin.Window({
            caption: 'neue Datenbank erstellen',
            closable: true,
            modal: true,
            width: 400,
            defaults:{
                width: 380,
                height: 25,
                style:{
                    margin: '10px'
                }
            },
            elements:[
                {
                    xtype: 'kijs.gui.field.Text',
                    labelWidth: 140,
                    required: true,
                    name: 'dbname',
                    label: 'Datenbankname'
                },{
                    xtype: 'kijs.gui.Button',
                    name: 'btnLogin',
                    width: 100,
                    height: 30,
                    caption: 'OK',
                    on:{
                        click: function(){
                            let dbname = this.parent.down('dbname');

                            if (dbname.validate()) {
                                _this._rpc.do('dbadmin.create', {dbname : dbname.value}, 
                                function(response) {
                                    if (response.data.success === 'true') {
                                        createWindow.destruct();
                                        kijs.gui.MsgBox.info('Info', 'Datenbank erfolgreich erstellt!');
                                        this.viewport.elements[0].elements[0].load();
                                    } else {
                                        kijs.gui.MsgBox.error('Fehler', response.data);       
                                    }
                                }, _this, false, this.parent, 'dom', false);
                            } else {
                                kijs.gui.MsgBox.warning('Warnung!', 'Bitte Datenbankname angeben!');
                            } 
                        }
                    }
                }
            ]
        });
        createWindow.show();
    }
    
    
    showLoginWindow() {
        let _this = this;
        // Window erstellen
        let loginWindow = new dbadmin.Window({
            caption: 'Login',
            modal: true,
            width: 300,
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
                },{
                    xtype: 'kijs.gui.Button',
                    name: 'btnLogin',
                    width: 100,
                    height: 30,
                    caption: 'Login',
                    on:{
                        click: function(){
                            let username = this.parent.down('username');
                            let password = this.parent.down('password');
                            
                            if (username.validate() && password.validate()) {
                                _this._rpc.do('dbadmin.login', {username : username.value, password : password.value}, 
                                function(response) {
                                    if (response.data.success === 'true') {
                                        loginWindow.destruct();
                                        this.setSessionId();
                                        this.viewport.elements[0].elements[0].load();
                                    } else {
                                        kijs.gui.MsgBox.error('Fehler', response.data);       
                                    }
                                }, _this, false, this.parent, 'dom', false);
                            } else {
                                kijs.gui.MsgBox.warning('Warnung!', 'Bitte alle Felder ausfüllen!');
                            }
                        }
                    }
                }
            ]
        });
        loginWindow.show();
    }
    
    
    setSessionId() {
        function s4() {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        }
        let Id = s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
        sessionStorage.setItem('ID', Id); 
    }
};