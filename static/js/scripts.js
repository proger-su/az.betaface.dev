var betafaceAuth = {
    $form: null,
    $email: null,
    init: function () {
        this.webcam.parent = this;
        this.$form = jQuery('#betaface-auth');
        this.$email = jQuery('.betaface-auth-email', this.$form);
        this.$nonce = jQuery('#betaface-auth-nonce', this.$form);

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
                _this.close();
            });

            this.$btnRegister.on('click', function (event) {
                event.preventDefault();
                _this.parent.register();
            });

            this.$btnLogin.on('click', function (event) {
                event.preventDefault();
                _this.parent.login();
            });

        },
        close: function () {
            this.$wrap.css('display', 'none');
            Webcam.reset();
        },
        attach: function () {
            if (!this.$screen.length) {
                swal('Oh noes!', 'No DOM element found!', 'error');
                return;
            }

            if (!this.parent.validateEmail()) {
                return;
            }

            this.$wrap.css('display', 'block');

            Webcam.set({
                width: 900,
                height: 500,
                image_format: 'jpeg',
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
    validateEmail: function () {
        var email = this.$email.val();
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if (re.test(String(email).toLowerCase())) {
            return true;
        }
        swal('Oh noes!', 'Email is empty or incorrect!', 'error');
        return false;
    },
    register: function () {
        if (!this.validateEmail()) {
            return;
        }

        var _this = this;

        Webcam.snap(function (photo) {
            jQuery.ajax({
                url: betafaceAuthConfig.ajaxUrl,
                dataType: 'json',
                type: 'POST',
                data: {
                    action: betafaceAuthConfig.actions.register,
                    nonce: _this.$nonce.val(),
                    email: _this.$email.val(),
                    photo: photo
                },
                beforeSend: function () {
                    _this.webcam.$wrap.addClass('loading');
                },
                success: function (response) {
                    if (!response.success) {
                        swal('Oh noes!', response.data, 'error');
                        _this.webcam.close();
                        return;
                    }

                    location.reload();
                },
                error: function (jqXHR, textStatus) {
                    swal('Oh noes!', textStatus, 'error');
                },
                complete: function () {
                    _this.webcam.$wrap.removeClass('loading');
                }
            });
        });
    },
    login: function () {
        if (!this.validateEmail()) {
            return;
        }

        var _this = this;

        Webcam.snap(function (photo) {
            jQuery.ajax({
                url: betafaceAuthConfig.ajaxUrl,
                dataType: 'json',
                type: 'POST',
                data: {
                    action: betafaceAuthConfig.actions.login,
                    nonce: _this.$nonce.val(),
                    email: _this.$email.val(),
                    photo: photo
                },
                beforeSend: function () {
                    _this.webcam.$wrap.addClass('loading');
                },
                success: function (response) {
                    if (!response.success) {
                        swal('Oh noes!', response.data, 'error');
                        _this.webcam.close();
                        return;
                    }

                    location.reload();
                },
                error: function (jqXHR, textStatus) {
                    swal('Oh noes!', textStatus, 'error');
                },
                complete: function () {
                    _this.webcam.$wrap.removeClass('loading');
                }
            });
        });
    },
};

jQuery(document).ready(function () {
    betafaceAuth.init();
});