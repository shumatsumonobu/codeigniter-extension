import '~/mfa.css';
import selectAll from '~/shared/selectAll';
import Dialog from '~/shared/Dialog';
import Validation from '~/shared/Validation';
import Toast from '~/shared/Toast';
import UserApi from '~/api/UserApi';

let requestValidation;
let verifyValidation;

function initRequestForm() {
  requestValidation = new Validation(ref.requestRecoveryForm.get(0), {
    email: {
      validators: {
        notEmpty: {message: 'Email is required.'},
        emailAddress: {message: 'Enter a valid email address.'}
      }
    }
  });

  requestValidation.onValid(async () => {
    try {
      requestValidation.onIndicator();
      const formData = new FormData(requestValidation.form);
      const email = formData.get('email');

      const {data} = await userApi.mfaRequestRecovery(formData);
      requestValidation.offIndicator();

      if (!data || !data.success) {
        Toast.error('Failed to send recovery email.');
        return;
      }

      // Store email for verification step
      ref.recoveryEmail.val(email);

      // For demo purposes, show the token (REMOVE IN PRODUCTION!)
      if (data.demo_token) {
        Toast.info(`Demo token: ${data.demo_token}`, 10000);
      }

      // Show verify step
      ref.requestStep.hide();
      ref.verifyStep.show();
    } catch (err) {
      requestValidation.offIndicator();
      Dialog.unknownError();
      throw err;
    }
  });
}

function initVerifyForm() {
  verifyValidation = new Validation(ref.verifyRecoveryForm.get(0), {
    token: {
      validators: {
        notEmpty: {message: 'Recovery token is required.'},
        invalidToken: {message: 'Invalid or expired token.'}
      }
    }
  });

  verifyValidation.onValid(async () => {
    try {
      verifyValidation.onIndicator();
      const formData = new FormData(verifyValidation.form);

      const {data} = await userApi.mfaVerifyRecovery(formData);
      verifyValidation.offIndicator();

      if (!data || !data.success) {
        return void verifyValidation.setError('token', 'invalidToken');
      }

      // Show success step
      ref.verifyStep.hide();
      ref.successStep.show();
    } catch (err) {
      verifyValidation.offIndicator();
      Dialog.unknownError();
      throw err;
    }
  });
}

function initBackButton() {
  ref.backToRequest?.on('click', (e) => {
    e.preventDefault();
    ref.verifyStep.hide();
    ref.requestStep.show();
    ref.verifyRecoveryForm.get(0).reset();
  });
}

const userApi = new UserApi();
const ref = selectAll();

initRequestForm();
initVerifyForm();
initBackButton();
