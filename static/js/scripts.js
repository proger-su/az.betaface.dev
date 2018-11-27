var betafaceAuth = {
    $form: null,
    init: function () {
        this.webcam.parent = this;
        this.$form = jQuery('#betaface-auth');
        this.webcam.init();
        this.addEventListeners();
    },
    addEventListeners: function () {
        var _this = this;
            this.$form.on('submit', function (event) {
                event.preventDefault();

                _this.webcam.get();

//                _this.runAuth(jQuery(this));
            });
    },
    webcam: {
        parent: null,
        init: function () {
            jQuery('<div id="betaface-auth-screen" />').insertAfter(this.parent.$form);
            Webcam.set({
                width: 900,
                height: 400,
                image_format: 'jpg',
                jpeg_quality: 90
            });

            Webcam.attach('#betaface-auth-screen');
        },
        get: function () {
            Webcam.snap(function (data_uri) {
                console.log(data_uri);
            });
        }
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