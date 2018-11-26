var betafaceAuth = {
    $forms: null,
    init: function () {
        this.$forms = jQuery('form.betaface-auth');
        this.addEventListeners();
    },
    addEventListeners: function () {
        var _this = this;
        this.$forms.each(function () {
            var $self = jQuery(this);
            $self.on('submit', function (event) {
                event.preventDefault();
                _this.runAuth(jQuery(this));
            });
        });
    },
    runAuth: function ($self) {
        var $nonce = $self.find('#betaface-auth-nonce');
        var $email = $self.find('.betaface-auth-email');
        jQuery.ajax({
            url: betafaceAuthConfig.ajaxUrl,
            dataType: 'json',
            type: 'POST',
            data: {
                action: betafaceAuthConfig.action,
                nonce: $nonce.val(),
                email: $email.val(),
            },
            beforeSend: function () {
            },
            success: function (response) {
                if (!response.success) {
                    alert(response.data.message);
                    return;
                }

            },
            error: function (jqXHR, textStatus) {
            },
            complete: function () {
            }
        });
    }
};

jQuery(document).ready(function () {
    betafaceAuth.init();
});