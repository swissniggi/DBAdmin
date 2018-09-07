// --------------------------------------------------------------
// Facade
// --------------------------------------------------------------
dbadmin.App = class dbadmin_App {
        

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
    /**
     * App ausführen
     * @returns {undefined}
     */
    run() {
        this._databaseView = new dbadmin.DatabaseView({
            rpc: this._rpc
        });

        let buttons = ({
            btnCreate : true,
            btnDelete : true,
            btnImport : true,
            btnExport : true,
            btnDuplicate : true,
            btnRename : true
        });
        let mainPanel = new dbadmin.MainPanel();
        let panel = mainPanel.create(this, buttons, this._databaseView);

        // ViewPort erstellen
        this._viewport = new kijs.gui.ViewPort({
            cls: 'kijs-flexcolumn',
            elements: [
                panel
            ]
        });
        this._viewport.render();

        // bei Start der Anwendung Login-Fenster anzeigen
        if (!localStorage.getItem('ID')) {
            this.showLoginWindow();
        } else {
            this._viewport.down('dvDatabases').load();
        }
    }


    /**
     * Action-Fenster erstellen
     * @param {string} action
     * @returns {undefined}
     */
    showActionWindow(action) {
        let caption = '';
        let iconChar = '';

        switch(action) {
            case 'create': caption = 'Neue Datenbank erstellen'; iconChar = '&#xf067'; break;
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
        // bei Benutzern mit Root-Rechten ist 'User' ein leerer String
        this._actionWindow.down('newDbname').value = localStorage.getItem('User');
    }


    /**
     * Login-Fenster erstellen
     * @returns {undefined}
     */
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


    /**
     * Select-Fenster erstellen
     * @returns {undefined}
     */
    showSelectWindow() {
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


    /**
     * Session-ID erstellen
     * @returns {undefined}
     */
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
        let username = this._loginWindow.down('username').value;

        if (username.includes('_')) {
            localStorage.setItem('User', username);
        }
        this._loginWindow.destruct();
    }

    _onSelectWindowAfterSave(e) {
        this._viewport.down('dvDatabases').load();
        this._selectWindow.destruct();
        kijs.gui.CornerTipContainer.show('Info', 'Dump erfolgreich importiert.', 'info');
    }
};