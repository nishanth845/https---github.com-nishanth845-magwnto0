define([
    'uiComponent',
    'jquery',
    'mage/mage',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'    
    
], function (Component, $, storage) {

    return Component.extend({

        initialize: function (main) {
            var myAjaxUrl = main.AjaxUrl;
            var showOrg = main.showOrgPopup;
            var showtermsPopup = main.termsPopup;
            
            if(showtermsPopup == 1){
                openTermsPopup();
            }
        },
    });
});