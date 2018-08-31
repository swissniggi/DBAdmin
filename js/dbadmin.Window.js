/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

dbadmin_Window = class dbadmin_gui_Window extends kijs.gui.Window {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super();
                
        this._focusDelay = 200;    // Delay zwischen dem rendern und dem setzen vom Fokus
        this._resizeDelay = 200;    // min. Delay zwischen zwei Resize-Events
        
        this._targetX = null;           // Zielelement (kijs.gui.Dom) oder Body (HTMLElement)
        this._targetDomProperty = 'dom'; // Dom-Eigenschaft im Zielelement (String) (Spielt bei Body als target keine Rolle)
        
        this._dom.clsAdd('kijs-window');
        
        // Standard-config-Eigenschaften mergen
        config = Object.assign({}, {
            draggable: true,
            target: document.body,
            
            // defaults overwrite kijs.gui.Panel
            closable: false,
            maximizable: false,
            resizable: false,
            shadow: true
        }, config);
        
        // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            draggable: { target: 'draggable' },
            focusDelay: true,
            modal: { target: 'modal' },     // Soll das Fenster modal geöffnet werden (alles Andere wird mit einer halbtransparenten Maske verdeckt)?
            resizeDelay: true,
            target: { target: 'target' },
            targetDomProperty: true
        });
        
        // Listeners
        this.on('mouseDown', this._onMouseDown, this);
        
        // Config anwenden
        if (kijs.isObject(config)) {
            this.applyConfig(config, true);
        }
    }
};