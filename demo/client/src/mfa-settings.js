import '~/mfa.css';
import selectAll from '~/shared/selectAll';
import Dialog from '~/shared/Dialog';
import Validation from '~/shared/Validation';
import Toast from '~/shared/Toast';
import UserApi from '~/api/UserApi';

let disableValidation;
let regenerateValidation;

function initDisableForm() {
  if (!ref.disableMfaForm || !ref.disableMfaForm.length) {
    console.log('disableMfaForm not found');
    return;
  }

  console.log('initDisableForm: form found');

  ref.disableMfaForm.on('submit', async (e) => {
    e.preventDefault();
    console.log('Form submitted');

    const code = ref.disableMfaForm.find('input[name="code"]').val();
    if (!code) {
      alert('Please enter a code');
      return;
    }

    const confirmed = await Dialog.confirm(
      'Disable Two-Factor Authentication',
      'Are you sure you want to disable 2FA? This will make your account less secure.',
      {confirmText: 'Disable 2FA', confirmClass: 'btn-danger'}
    );

    if (!confirmed) return;

    try {
      const formData = new FormData(ref.disableMfaForm.get(0));
      console.log('Sending request with code:', code);

      const {data} = await userApi.mfaDisable(formData);
      console.log('Response:', data);

      if (!data || !data.success) {
        alert('Invalid code. Please try again.');
        return;
      }

      Toast.success('Two-factor authentication disabled.');
      setTimeout(() => location.reload(), 1500);
    } catch (err) {
      console.error('Error:', err);
      Dialog.unknownError();
    }
  });
}

function initRegenerateForm() {
  if (!ref.regenerateCodes.length) return;

  const modal = new bootstrap.Modal(document.getElementById('regenerateCodesModal'));

  ref.regenerateCodes.on('click', () => modal.show());

  regenerateValidation = new Validation(ref.regenerateCodesForm.get(0), {
    code: {
      validators: {
        notEmpty: {message: 'Code is required.'},
        invalidCode: {message: 'Invalid code. Please try again.'}
      }
    }
  });

  regenerateValidation.onValid(async () => {
    try {
      regenerateValidation.onIndicator();
      const formData = new FormData(regenerateValidation.form);

      const {data} = await userApi.mfaRegenerateBackupCodes(formData);
      regenerateValidation.offIndicator();

      if (!data || !data.success) {
        return void regenerateValidation.setError('code', 'invalidCode');
      }

      modal.hide();
      showNewCodes(data.backup_codes);
    } catch (err) {
      regenerateValidation.offIndicator();
      Dialog.unknownError();
      throw err;
    }
  });
}

function showNewCodes(codes) {
  const html = codes.map(code =>
    `<div class="col-6"><code class="fs-5 fw-bold">${code}</code></div>`
  ).join('');
  ref.newBackupCodes.html(html);

  const modal = new bootstrap.Modal(document.getElementById('newCodesModal'));
  modal.show();

  // Download new codes
  ref.downloadNewCodes?.on('click', () => {
    const codesText = codes.join('\n');
    const blob = new Blob([`CodeIgniter Demo Backup Codes\n${'='.repeat(30)}\n\n${codesText}\n\nEach code can only be used once.`], {type: 'text/plain'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'backup-codes.txt';
    a.click();
    URL.revokeObjectURL(url);
  });

  // Copy new codes
  ref.copyNewCodes?.on('click', () => {
    navigator.clipboard.writeText(codes.join('\n'));
    Toast.success('Backup codes copied to clipboard!');
  });

  // Reload page when modal is closed
  document.getElementById('newCodesModal').addEventListener('hidden.bs.modal', () => {
    location.reload();
  }, {once: true});
}

const userApi = new UserApi();
const ref = selectAll();

initDisableForm();
initRegenerateForm();
