import '~/login.css';
import selectAll from '~/shared/selectAll';
import Dialog from '~/shared/Dialog';
import Validation from '~/shared/Validation';
import UserApi from '~/api/UserApi';

function initValidation() {
  validation = new Validation(ref.loginForm.get(0), {
    email: {
      validators: {
        notEmpty: {message: 'Email is required.'},
        emailAddress: {message: 'Enter your email correctly.'},
        userNotFound: {message: 'Account not found.'}
      }
    },
    password: {
      validators: {
        notEmpty: {message: 'Password is required.'}
      }
    }
  });
}

function initForm() {
  validation.onValid(async () => {
    try {
      validation.onIndicator();
      const {data} = await userApi.login(new FormData(validation.form));
      validation.offIndicator();

      // Handle new response format
      if (!data || !data.success)
        return void validation.setError('email', 'userNotFound');

      // Check if MFA verification is required (MFA enabled)
      if (data.mfa_required) {
        location.href = '/users/mfa-verify';
        return;
      }

      // Check if MFA setup should be prompted (MFA not enabled yet)
      if (data.mfa_setup_prompt) {
        location.href = '/users/mfa-setup';
        return;
      }

      location.href = '/';
    } catch (err) {
      validation.offIndicator();
      Dialog.unknownError();
      throw err;
    }
  });
}

const userApi = new UserApi();
const ref = selectAll();
let validation;
initValidation();
initForm();