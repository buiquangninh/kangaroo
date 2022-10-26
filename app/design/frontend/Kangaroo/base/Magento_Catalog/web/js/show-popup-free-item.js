require([
    "jquery",
    "Magento_Ui/js/modal/modal",
    "mage/translate"
], function($, modal, $t) {
    var options = {
        type: 'popup',
        responsive: true,
        title: $t('Gifts when purchased with the device'),
        modalClass: 'popup-free-item',
        buttons: [{
            text: $t('Close'),
            class: '',
            click: function () {
                this.closeModal();
            }
        }]
    };

    var popup = modal(options, $('#modalFreeItem'));
    $("#show-popup").click(function() {
        $('#modalFreeItem').modal('openModal');
    });

    $("#show-popup-link").click(function() {
        $('#modalFreeItem').modal('openModal');
    });

    $("#show-popup-mobile").click(function() {
        $('#modalFreeItem').modal('openModal');
    });
});
