var betafaceAuth = {
    $forms: null,
    init = function () {
        this.$forms = jQuery('form.betaface-auth');
    },
    addEventListeners = function () {
        this.$forms.each(function () {
            var $self = jQuery(this);
            $self.on('submit', function (event) {
                event.preventDefault();
            })
        });
    }
};

jQuery(document).ready(function () {
    betafaceAuth.init();
});