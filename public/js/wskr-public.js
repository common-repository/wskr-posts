window.wskr = window.wskr || {};

wskr.LoginForm = (function($) {
  const selectors = {
    form: '#WSKRLoginForm',
    rememberMe: '#RemeberMe',
    btnContinue: '#Continue',
    btnRegister: '#Register',
    btnForgotPassword: '#ForgotPassword',
    email: '#FormFieldEmail',
    password: '#FormFieldPassword',
    formGroupEmail: '.form-group-email',
    formGroupPassword: '.form-group-password',
    errorMsg: '.error-msg',
  };
  const emailRegex = new RegExp('^[A-Z0-9._%+-]+@[A-Z0-9.-]+\\.[A-Z]{2,}$', 'i');

  function LoginForm() {
    if (!$(selectors.form).length)
      return;

    this.rememberMe = false;
    this.isSubmitted = false;
    this.isLoading = false;

    this.$form = $(selectors.form);
    this.$email = this.$form.find(selectors.email);
    this.$password = this.$form.find(selectors.password);
    this.$formGroupEmail = this.$form.find(selectors.formGroupEmail);
    this.$formGroupPassword = this.$form.find(selectors.formGroupPassword);
    this.$rememberMe = this.$form.find(selectors.rememberMe);
    this.$btnContinue = this.$form.find(selectors.btnContinue);
    this.$btnRegister = this.$form.find(selectors.btnRegister);
    this.$btnForgotPassword = this.$form.find(selectors.btnForgotPassword);

    this.$formGroupEmail.on('keyup', this.handleEmailChange.bind(this));
    this.$formGroupPassword.on('keyup', this.handlePasswordChange.bind(this));
    this.$rememberMe.on('change', this.handleRememberMeChange.bind(this));
    this.$btnContinue.on('click', this.handleLogin.bind(this));
    this.$btnRegister.on('click', this.handleRegister.bind(this));
    this.$btnForgotPassword.on('click', this.handleForgotPassword.bind(this));
  }

  LoginForm.prototype = $.extend({}, LoginForm.prototype, {
    handleEmailChange: function(e) {
      const email = $(e.target).val();
      this.validateEmail(email);
    },

    handlePasswordChange: function(e) {
      const password = $(e.target).val();
      this.validatePassword(password);
    },

    handleRememberMeChange: function(e) {
      this.rememberMe = e.target.checked;
    },

    handleLogin: function(e) {
      if (this.isLoading)
        return;

      const email = this.$email.val();
      const password = this.$password.val();

      this.isSubmitted = true;

      const isEmailValid = this.validateEmail(email);
      const isPasswordValid = this.validatePassword(password);

      if (!isEmailValid || !isPasswordValid)
        return;

      this.setLoading(true);
      this.loginWithWSKR({ email, password })
        .then(response => {
          if (response && response.token) {
            this.storeAuthToken(response.token, this.rememberMe);
            this.loginToWordpress(response.token);
          } else {
            this.showMessage('An unknown error has occurred.', 'error');
            this.setLoading(false);
          }
        })
        .catch(error => {
          if (error.responseJSON && error.responseJSON.message) {
            this.showMessage(`${error.responseJSON.message}`, 'error');
          } else {
            this.showMessage('An unknown error has occurred.', 'error');
          }
          this.setLoading(false);
        });
    },

    handleRegister: function(e) {
      this.redirectToExternal(e)
    },

    handleForgotPassword: function(e) {
      this.redirectToExternal(e)
    },

    validateEmail: function(email) {
      const isEmpty = email === '';
      const isValid = (typeof email === 'string' && email.length > 0 && email.length < 255 && emailRegex.test(email));
      if (isValid) {
        this.$formGroupEmail.removeClass('has-error');
      } else {
        this.$formGroupEmail.addClass('has-error');
      }

      if (isEmpty) {
        this.$formGroupEmail.find(selectors.errorMsg).html('Please enter email address.');
        this.$formGroupEmail.find(selectors.errorMsg).show();
      } else if (!isValid) {
        this.$formGroupEmail.find(selectors.errorMsg).html('Invalid email address.');
        this.$formGroupEmail.find(selectors.errorMsg).show();
      } else {
        this.$formGroupEmail.find(selectors.errorMsg).html('');
        this.$formGroupEmail.find(selectors.errorMsg).hide();
      }

      if (!this.isSubmitted) {
        this.$formGroupEmail.removeClass('has-error');
        this.$formGroupEmail.find(selectors.errorMsg).html('');
        this.$formGroupEmail.find(selectors.errorMsg).hide();
      }

      return isValid;
    },

    validatePassword: function(password) {
      const isEmpty = password === '';
      if (!isEmpty) {
        this.$formGroupPassword.removeClass('has-error');
      } else {
        this.$formGroupPassword.addClass('has-error');
      }

      if (isEmpty) {
        this.$formGroupPassword.find(selectors.errorMsg).html('Please enter a password.');
        this.$formGroupPassword.find(selectors.errorMsg).show();
      } else {
        this.$formGroupPassword.find(selectors.errorMsg).html('');
        this.$formGroupPassword.find(selectors.errorMsg).hide();
      }

      if (!this.isSubmitted) {
        this.$formGroupPassword.removeClass('has-error');
        this.$formGroupPassword.find(selectors.errorMsg).html('');
        this.$formGroupPassword.find(selectors.errorMsg).hide();
      }

      return !isEmpty;
    },

    loginWithWSKR: function(payload) {
      return new Promise((resolve, reject) => {
        const { email, password } = payload;
        const url = wskrPublicAjax.api_base + "wskr/authorise";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            email        :  email,
            password     :  password,
          }),
          beforeSend : function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wskrPublicAjax.nonce);
          }
        }).done(function (data) {
          resolve(data);
        }).fail(function (error) {
          reject(error);
        });
      });
    },

    loginToWordpress: async function() {
      const { username, password, error } = await this.fetchWordpressCredential();
      if (error) {
        this.showMessage(`WordPress service unavailable.`, 'error');
        this.setLoading(false);
        return;
      }

      this.autoLogin({username, password})
          .then(response => {
            if (response && response.isLoggedIn) {
              this.showMessage('Login was successfully completed.', 'success');
              this.redirectToConfirm();
            } else {
              this.showMessage(`WordPress service unavailable.`, 'error');
            }
          })
          .catch(err => {
            if (err.responseJSON && err.responseJSON.message) {
              this.showMessage(`${err.responseJSON.message}`, 'error');
            } else {
              this.showMessage('An unknown error has occurred.', 'error');
            }
          })
          .finally(() => {
            this.setLoading(false);
          });
    },

    fetchWordpressCredential: function() {
      return new Promise((resolve, reject) => {
        const url = wskrPublicAjax.api_base + "wskr/wordpress";
        $.ajax({
          url        : url,
          method     : 'GET',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          beforeSend : function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wskrPublicAjax.nonce);
          }
        }).done(function (data) {
          resolve(data);
        }).fail(function (error) {
          reject(error);
        });
      });
    },

    autoLogin: function(payload) {
      return new Promise((resolve, reject) => {
        const { username, password } = payload;
        const url = wskrPublicAjax.api_base + "wskr/login";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            username     :  username,
            password     :  password,
          }),
          beforeSend : function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wskrPublicAjax.nonce);
          }
        }).done(function (data) {
          resolve(data);
        }).fail(function (error) {
          reject(error);
        });
      });
    },

    redirectToConfirm: function() {
      const redirectTo = this.$form.data('redirect-to');
      if (!redirectTo) return;
      window.location = redirectTo;
    },

    redirectToExternal: function(event) {
      const url = $(event.target).data('url');
      Snackbar.show({
        text: 'You are about to be redirected to the WSKR website.<br />' +
              'Do you wish to continue?',
        textColor: '#FFFFFF',
        backgroundColor: '#2196F3',
        actionTextColor: '#FFFFFF',
        actionText: 'Cancel',
        showSecondButton: true,
        secondButtonText: 'OK',
        secondButtonTextColor: '#FFFFFF',
        duration: 2000000000,
        url: url,
        customClass: 'confirm-modal'
      });
    },

    storeAuthToken: function(token, rememberMe) {
      if (rememberMe) {
        Cookies.set('wskr_auth_token', token, { expires: 14 });
      } else {
        Cookies.set('wskr_auth_token', token);
      }
    },

    setLoading: function(loading) {
      if (loading) {
        this.isLoading = true;
        this.$btnContinue.addClass('loading');
      } else {
        this.isLoading = false;
        this.$btnContinue.removeClass('loading');
      }
    },

    showMessage: function(text, status = 'normal') {
      if (!text || text === '')
        return;

      const normal = {
        textColor: '#FFFFFF',
        backgroundColor: '#2196F3',
        actionTextColor: '#FFFFFF'
      };

      const success = {
        textColor: '#FFFFFF',
        backgroundColor: '#4CAF50',
        actionTextColor: '#FFFFFF'
      };

      const warning = {
        textColor: '#1d1f21',
        backgroundColor: '#F9EE98',
        actionTextColor: '#1d1f21'
      };

      const error = {
        textColor: '#FFFFFF',
        backgroundColor: '#F66496',
        actionTextColor: '#FFFFFF'
      };

      let theme = '';
      switch (status) {
        case 'normal':
          theme = normal;
          break;
        case 'success':
          theme = success;
          break;
        case 'warning':
          theme = warning;
          break;
        case 'error':
          theme = error;
          break;
        default:
          theme = normal;
          break;
      }

      Snackbar.show({
        pos: 'bottom-center',
        text: text,
        textColor: theme.textColor,
        backgroundColor: theme.backgroundColor,
        actionTextColor: theme.actionTextColor,
      });
    }
  });

  return LoginForm;
})(jQuery);

wskr.ConfirmationForm = (function($) {
  const selectors = {
    form: '#WSKRConfirmationForm',
    btnCancel: '#Cancel',
    btnContinue: '#Continue',
    errorMsg: '.error-msg',
  };

  function ConfirmationForm() {
    if (!$(selectors.form).length)
      return;

    this.isLoading = false;

    this.$form = $(selectors.form);
    this.$btnCancel = this.$form.find(selectors.btnCancel);
    this.$btnContinue = this.$form.find(selectors.btnContinue);

    this.$btnContinue.on('click', this.handleConfirmation.bind(this));
  }

  ConfirmationForm.prototype = $.extend({}, ConfirmationForm.prototype, {
    handleConfirmation: function(e) {
      if (this.isLoading)
        return;

      const redirectTo = this.$form.data('redirect-to');
      const contentUrl = this.$form.data('content-url');
      const tokenValue = this.$form.data('token-value');

      if (tokenValue < 0) {
        this.showMessage('There is a problem with your login. Please logout and try again.', 'error'); // v1.2.2
        window.location = contentUrl;
        return;
      }

      this.setLoading(true);
      this.payWithWSKR({ contentUrl, tokenValue, redirectTo })
          .then(response => {
            console.log('response : ', response);
            if (response && response.code === 200) {
              this.showMessage('Your payment has been successful.', 'success'); // v1.2.2
              window.location = contentUrl;
            }

            if (response && response.code === 302) {
              this.redirectToPayment();
            }

            if (response && response.code === 400) {
              this.showMessage(`Invalid request.`, 'error');
            }

            if (response && response.code === 401) {
              this.showMessage('You need to log in to continue.', 'error'); // v1.2.2
              this.removeAuthToken();
              window.location = contentUrl;
            }
          })
          .catch(error => {
            if (error.responseJSON && error.responseJSON.message) {
              this.showMessage(`${error.responseJSON.message}`, 'error');
              if (error.responseJSON.error === 'login-required') {
                window.location = contentUrl;
              }
            } else {
              this.showMessage('An unknown error has occurred.', 'error');
            }
          })
          .finally(() => {
            this.setLoading(false);
          });
    },

    payWithWSKR: function(payload) {
      return new Promise((resolve, reject) => {
        const { contentUrl, tokenValue, redirectTo } = payload;
        const url = wskrPublicAjax.api_base + "wskr/pay";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            contentUrl      :  contentUrl,
            tokenValue      :  tokenValue,
            redirectTo      :  redirectTo,
          }),
          beforeSend : function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wskrPublicAjax.nonce);
          }
        }).done(function (data) {
          resolve(data);
        }).fail(function (error) {
          reject(error);
        });
      });
    },

    redirectToConfirm: function() {
      const redirectTo = this.$form.data('redirect-to');
      if (!redirectTo) return;
      window.location = redirectTo;
    },

    redirectToPayment: function() {
      const returnUrl = this.$form.data('return-url');
      const url = 'https://my.wskr.ie/transaction/addfunds?returnurl=' + returnUrl;

      Snackbar.show({
        text: 'You have insufficient WSKR Tokens to pay for this content.<br />' +
          'Do you wish to purchase additional tokens?.<br />' +
          '(Clicking continue will redirect you to the WSKR website)',
        textColor: '#FFFFFF',
        backgroundColor: '#2196F3',
        actionTextColor: '#FFFFFF',
        actionText: 'Cancel',
        showSecondButton: true,
        secondButtonText: 'Continue',
        secondButtonTextColor: '#FFFFFF',
        duration: 2000000000,
        url: url,
        customClass: 'confirm-modal additional-funds'
      });
    },

    removeAuthToken: function() {
      Cookies.remove('wskr_auth_token');
    },

    setLoading: function(loading) {
      if (loading) {
        this.isLoading = true;
        this.$btnContinue.addClass('loading');
      } else {
        this.isLoading = false;
        this.$btnContinue.removeClass('loading');
      }
    },

    showMessage: function(text, status = 'normal') {
      if (!text || text === '')
        return;

      const normal = {
        textColor: '#FFFFFF',
        backgroundColor: '#2196F3',
        actionTextColor: '#FFFFFF'
      };

      const success = {
        textColor: '#FFFFFF',
        backgroundColor: '#4CAF50',
        actionTextColor: '#FFFFFF'
      };

      const warning = {
        textColor: '#1d1f21',
        backgroundColor: '#F9EE98',
        actionTextColor: '#1d1f21'
      };

      const error = {
        textColor: '#FFFFFF',
        backgroundColor: '#F66496',
        actionTextColor: '#FFFFFF'
      };

      let theme = '';
      switch (status) {
        case 'normal':
          theme = normal;
          break;
        case 'success':
          theme = success;
          break;
        case 'warning':
          theme = warning;
          break;
        case 'error':
          theme = error;
          break;
        default:
          theme = normal;
          break;
      }

      Snackbar.show({
        pos: 'bottom-center',
        text: text,
        textColor: theme.textColor,
        backgroundColor: theme.backgroundColor,
        actionTextColor: theme.actionTextColor,
      });
    }
  });

  return ConfirmationForm;
})(jQuery);

(function( $ ) {
  'use strict';

  /**
   * All of the code for your public-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
	 *
	 * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
	 *
	 * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */
  jQuery(document).ready(function($){
    $('#wskr_email').on('input', function(){
      if($(this).val() != ''){
        $(this).removeClass('empty_fields');
      }
    });
    $('#wskr_password').on('input', function(){
      if($(this).val() != ''){
        $(this).removeClass('empty_fields');
      }
    });
  });

  $(document).ready(function() {

    new wskr.LoginForm();
    new wskr.ConfirmationForm();

  });

})( jQuery );
