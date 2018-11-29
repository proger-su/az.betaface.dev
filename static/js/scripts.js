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

            _this.webcam.attach(jQuery(this));

//                _this.webcam.get();
//                _this.runAuth(jQuery(this));
        });
    },
    webcam: {
        parent: null,
        $screen: null,
        $wrap: null,
        $btnLogin: null,
        $btnClose: null,
        $btnRegister: null,
        init: function () {
            this.$wrap = jQuery('#betaface-auth-screen-wrap');
            this.$screen = jQuery('#betaface-auth-screen', this.$wrap);
            this.$btnLogin = jQuery('.buttons .login', this.$wrap);
            this.$btnClose = jQuery('.buttons .close', this.$wrap);
            this.$btnRegister = jQuery('.buttons .register', this.$wrap);

            this.addEventListeners();
        },
        addEventListeners: function () {
            var _this = this;
            this.$btnClose.on('click', function (event) {
                event.preventDefault();
                _this.$wrap.css('display', 'none');
                Webcam.reset();
            });
        },
        attach: function ($form) {
            if (!this.$screen.length) {
                alert('No DOM element found!')
                return;
            }

            var email = $form.find('.betaface-auth-email').val();

            if(!this.validateEmail(email)){
                alert('Email is incorrect!')
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
        validateEmail: function (email) {
            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
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