// --------------------------------------------------------------
// Facade
// --------------------------------------------------------------
kit = {};
kit.App = class kit_App {
        

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        
        this._actionWindow = null;
        this._databaseView = null;
        this._loginWindow = null;
        this._selectWindow = null,
        this._viewport = null;       
        
        // RPC-Instanz
        var rpcConfig = {};
        if (config.ajaxUrl) {
            rpcConfig.url = config.ajaxUrl;
        }
        this._rpc = new kijs.gui.Rpc(rpcConfig);
        
        
    }


    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------
    run() {        
        this._databaseView = new dbadmin.DatabaseView({
            rpc: this._rpc
        });
        
        // ViewPort erstellen
        this._viewport = new kijs.gui.ViewPort({
            cls: 'kijs-flexcolumn',          
            elements: [
                {
                    xtype: 'kijs.gui.Panel',
                    name: 'mainPanel',
                    caption: 'DBAdmin',
                    footerCaption: '&copy; by Nicolas Burgunder',
                    iconCls: 'icoWizard16',
                    cls: 'kijs-flexrow',
                    style:{
                        flex: 1
                    },
                    elements:[
                            this._databaseView
                    ],
                    headerElements:[
                        {
                            xtype: 'kijs.gui.Button',
                            name: 'btnCreate',
                            iconChar: '&#xf067',
                            on:{
                                click: function(){
                                    this.showActionWindow('create');
                                },
                                context: this
                            }
                        }
                    ],
                    headerBarElements:[
                        {
                            xtype: 'kijs.gui.Button',
                            name: 'btnLogout',
                            iconChar: '&#xf011',
                            on:{
                                click: function(){
                                    localStorage.removeItem('ID');
                                    this._rpc.do('dbadmin.logout', null, 
                                    function() {
                                        kijs.gui.CornerTipContainer.show('Info', 'Du wurdest erfolgreich ausgelogt', 'info');
                                        this.showLoginWindow();
                                        // DataView leeren
                                        this._viewport.down('dvDatabases').load(null);
                                    }, this, false, this._viewport, 'dom', false);
                                },
                                context: this
                            }
                        }
                    ],
                    footerElements:[
                        {
                            xtype: 'kijs.gui.Button',
                            name: 'btnDelete',
                            iconChar: '&#xf1f8',
                            style:{
                                border: 'none'
                            },
                            on:{
                                click: function(){
                                    if (this._viewport.down('dvDatabases').getSelected() === null) {
                                        kijs.gui.MsgBox.alert('Achtung','Keine Datenbank ausgewählt!');
                                    } else {
                                        this._rpc.do('dbadmin.delete', this._viewport.down('dvDatabases').getSelected().dataRow, 
                                        function(response) {
                                            if (response.data.success === 'true') {
                                                kijs.gui.CornerTipContainer.show('Info', 'Datenbank erfolgreich gelöscht.', 'info');
                                                this._viewport.down('dvDatabases').load();             
                                            } else {
                                                kijs.gui.MsgBox.error('Fehler', response.errorMsg);       
                                            }
                                        }, this, false, this._viewport, 'dom', false);
                                    }
                                },
                                context: this
                            }
                        },{
                            xtype: 'kijs.gui.Button',
                            name: 'btnImport',
                            iconChar: '&#xf019',
                            style:{
                                border: 'none'
                            },
                            on:{
                                click: function(){
                                    if (this._viewport.down('dvDatabases').getSelected() === null) {
                                        kijs.gui.MsgBox.alert('Achtung','Keine Datenbank ausgewählt!');
                                    } else {
                                        this.showSelectWindow();
                                    }
                                },
                                context: this
                            }
                        },{
                            xtype: 'kijs.gui.Button',
                            name: 'btnExport',
                            iconChar: '&#xf093',
                            style:{
                                border: 'none'
                            },
                            on:{
                                click: function(){
                                    if (this._viewport.down('dvDatabases').getSelected() === null) {
                                        kijs.gui.MsgBox.alert('Achtung','Keine Datenbank ausgewählt!');
                                    } else {
                                        this._rpc.do('dbadmin.export', this._viewport.down('dvDatabases').getSelected().dataRow, 
                                        function(response) {
                                            if (response.data.success === 'true') {
                                                kijs.gui.CornerTipContainer.show('Info', 'Datenbank erfolgreich exportiert.', 'info');
                                                this._viewport.down('dvDatabases').load();             
                                            } else {
                                                kijs.gui.MsgBox.error('Fehler', response.errorMsg);       
                                            }
                                        }, this, false, this._viewport, 'dom', false);
                                    }
                                },
                                context: this
                            }
                        },{
                            xtype: 'kijs.gui.Button',
                            name: 'btnDuplicate',
                            iconChar: '&#xf0c5',
                            style:{                                
                                border: 'none'
                            },
                            on:{
                                click: function(){
                                    if (this._viewport.down('dvDatabases').getSelected() === null) {
                                        kijs.gui.MsgBox.alert('Achtung','Keine Datenbank ausgewählt!');
                                    } else {
                                        this.showActionWindow('duplicate');
                                    }
                                },
                                context: this
                            }
                        },{
                            xtype: 'kijs.gui.Button',
                            name: 'btnRename',
                            iconChar: '&#xf044',
                            style:{
                                border: 'none'
                            },
                            on:{
                                click: function(){
                                    if (this._viewport.down('dvDatabases').getSelected() === null) {
                                        kijs.gui.MsgBox.alert('Achtung','Keine Datenbank ausgewählt!');
                                    } else {
                                        this.showActionWindow('rename');
                                    }
                                },
                                context: this
                            }
                        }                     
                    ]
                }
            ]
        });
        this._viewport.render();
        
        if (!localStorage.getItem('ID')) {
            this.showLoginWindow();
        } else {
            this._viewport.down('dvDatabases').load();
        }
    }
    
    
    showActionWindow(action) {
        let caption = '';
        let iconChar = '';
        
        switch(action) {
            case 'create': caption = 'neue Datenbank erstellen'; iconChar = '&#xf067'; break;
            case 'duplicate': caption = 'Datenbank duplizieren'; iconChar = '&#xf0c5'; break;
            case 'rename': caption = 'Datenbank umbenennen'; iconChar = '&#xf044'; break;
        }
        // Create-Window erstellen
        this._actionWindow = new dbadmin.ActionWindow({
            caption: caption,
            iconChar: iconChar,
            value: action === 'create' ? 'create' : this._viewport.down('dvDatabases').getSelected().dataRow['Datenbankname'],
            rpc: this._rpc,
            facadeFnSave: 'dbadmin.'+action,
            on:{
                afterSave: this._onActionWindowAfterSave,
                context: this
            }     
        });
        this._actionWindow.show();
    }
    
    
    showLoginWindow() {
        // Window erstellen
        this._loginWindow = new dbadmin.LoginWindow({
            rpc: this._rpc,
            facadeFnSave: 'dbadmin.login',
            on:{
                afterSave: this._onLoginWindowAfterSave,
                context: this
            }
        });
        this._loginWindow.show();
    }
    
    
    showSelectWindow() {
        console.log(this._viewport.down('dvDatabases').getSelected());
        // Window erstellen
        this._selectWindow = new dbadmin.SelectWindow({
            caption: 'Dumps',            
            rpcFormPanel: this._rpc,
            rpcComboField: this._rpc,
            facadeFnSave: 'dbadmin.import',
            facadeFnLoad: 'dbadmin.loadDumps',
            value: this._viewport.down('dvDatabases').getSelected().dataRow['Datenbankname'],
            on:{
                afterSave: this._onSelectWindowAfterSave,
                context: this
            }
        });
        this._selectWindow.show();
    }
    
    
    _setSessionId() {
        function s4() {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        }
        let Id = s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
        localStorage.setItem('ID', Id); 
    }
    
    
    // LISTENERS
    _onActionWindowAfterSave(e) {
        let txt = '';
        switch(e.raiseElement.facadeFnSave) {
            case 'dbadmin.create': txt = 'erstellt.'; break;
            case 'dbadmin.duplicate': txt = 'dupliziert.'; break
            case 'dbadmin.rename': txt = 'umbenannt.'; break;
        }
        this._viewport.down('dvDatabases').load();
        console.log(e.raiseElement.facadeFnSave);
        this._actionWindow.destruct();
        kijs.gui.CornerTipContainer.show('Info', 'Datenbank erfolgreich '+txt, 'info');
    }
       
    _onLoginWindowAfterSave(e) {
        this._viewport.down('dvDatabases').load();
        this._setSessionId();       
        this._loginWindow.destruct();
    }
    
    _onSelectWindowAfterSave(e) {
        this._viewport.down('dvDatabases').load();
        this._selectWindow.destruct();
        kijs.gui.CornerTipContainer.show('Info', 'Dump erfolgreich importiert.', 'info');
    }
};