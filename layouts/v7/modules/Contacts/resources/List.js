Vtiger_List_Js("Contacts_List_Js", {
    lastId: false,
}, {
    registerUpdateContactsEvent: function () {
        var self = this;
        var params = {
            module: 'Contacts',
            action: 'LiveUpdateAjax',
            mode: 'getLastId'
        }
        app.request.post({data: params}).then(
            function(err,data) {
                if (err === null) {
                    Contacts_List_Js.lastId = data.id;
                    setInterval(self.runUpdate, 10000);
                }
            }
        )
    },
    runUpdate: function() {
        var params = {
            module: 'Contacts',
            action: 'LiveUpdateAjax',
            mode: 'getContacts',
            record: Contacts_List_Js.lastId
        }
        app.request.post({data: params}).then(
            function(err,data) {
                data.forEach(function(entity) {
                    Contacts_List_Js.lastId = entity.id;
                });
                if(data.length > 0) {
                    var listInstance = new Vtiger_List_Js();
                    var paramsData = {'page': '1'};
                    listInstance.loadListViewRecords();
                }
            }
        );
    },
    registerEvents : function() {
        this._super();
        this.registerUpdateContactsEvent();
    }
});
