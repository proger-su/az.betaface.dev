var betafaceAuth = {

    $form: null,
    init: function () {
        this.webcam.parent = this;
        this.$form = jQuery('#betaface-auth');

        if (!this.$form.length) {
            return;
        }

        this.webcam.init();
        this.addEventListeners();
    },
    addEventListeners: function () {
        var _this = this;
        this.$form.on('submit', function (event) {
            event.preventDefault();
            _this.webcam.attach();

//                _this.webcam.get();
//                _this.runAuth(jQuery(this));
        });
    },
    webcam: {
        parent: null,
        $screen: null,
        $wrap: null,
        $btnRun: null,
        $btnReset: null,
        init: function () {
            this.$wrap = jQuery('#betaface-auth-screen-wrap');
            this.$screen = jQuery('#betaface-auth-screen', this.$wrap);
            this.$btnRun = jQuery('.buttons .run', this.$wrap);
            this.$btnReset = jQuery('.buttons .run', this.$wrap);
        },
        addEventListeners: function () {
            this.$btnReset.on('click', function (event) {
                event.preventDefault();
                this.$wrap.css('display', 'none');
                Webcam.reset();
            });
        },
        attach: function () {
            console.log(1);
            if (!this.$screen.length) {
                return;
            }

            this.$wrap.css('display', 'block');

            Webcam.set({
                width: 900,
                height: 500,
                image_format: 'jpg',
                jpeg_quality: 90
            });

            Webcam.attach('#betaface-auth-screen');
        },
        get: function () {
            Webcam.snap(function (data_uri) {
                console.log(data_uri);
            });
        },

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