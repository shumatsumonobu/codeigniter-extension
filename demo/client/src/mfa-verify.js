import '~/mfa.css';
import selectAll from '~/shared/selectAll';
import Dialog from '~/shared/Dialog';
import Validation from '~/shared/Validation';
import Toast from '~/shared/Toast';
import UserApi from '~/api/UserApi';

function initValidation() {
  validation = new Validation(ref.mfaVerifyForm.get(0), {
    code: {
      validators: {
        notEmpty: {message: 'Code is required.'},
        invalidCode: {message: 'Invalid code. Please try again.'}
      }
    }
  });
}

function initForm() {
  validation.onValid(async () => {
    try {
      validation.onIndicator();
      const formData = new FormData(validation.form);
      // Normalize the code (remove dashes/spaces for backup codes)
      formData.set('code', formData.get('code').replace(/[\s\-]/g, ''));

      const {data} = await userApi.mfaVerifyLogin(formData);
      validation.offIndicator();

      if (!data || !data.success) {
        return void validation.setError('code', 'invalidCode');
      }

      // Show warning if backup codes are low
      if (data.backup_codes_remaining !== undefined && data.backup_codes_remaining < 3) {
        Toast.warning(`You only have ${data.backup_codes_remaining} backup codes remaining.`);
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
