import '~/mfa.css';
import selectAll from '~/shared/selectAll';
import Dialog from '~/shared/Dialog';
import Validation from '~/shared/Validation';
import Toast from '~/shared/Toast';
import UserApi from '~/api/UserApi';

let stepper;
let validation;
let setupData = null;

function initStepper() {
  const stepperEl = ref.mfaStepper.get(0);
  stepper = new KTStepper(stepperEl);

  stepper.on('kt.stepper.next', async (stepper) => {
    const currentStep = stepper.getCurrentStepIndex();

    if (currentStep === 1) {
      // Moving from step 1 to step 2 - just continue
      stepper.goNext();
    } else if (currentStep === 2) {
      // Verify the code before moving to step 3
      if (await verifyCode()) {
        stepper.goNext();
      }
    }
  });

  stepper.on('kt.stepper.previous', (stepper) => {
    stepper.goPrevious();
  });
}

function initValidation() {
  validation = new Validation(ref.mfaSetupForm.get(0), {
    code: {
      validators: {
        notEmpty: {message: 'Code is required.'},
        regexp: {regexp: /^\d{6}$/, message: 'Enter a 6-digit code.'},
        invalidCode: {message: 'Invalid code. Please try again.'}
      }
    }
  });

  // Handle form submission on final step
  validation.onValid(async () => {
    try {
      validation.onIndicator();
      // Setup is already complete at this point, just redirect
      validation.offIndicator();
      Toast.success('Two-factor authentication enabled!');
      setTimeout(() => {
        location.href = '/users/personal';
      }, 1500);
    } catch (err) {
      validation.offIndicator();
      Dialog.unknownError();
      throw err;
    }
  });
}

function loadSetup() {
  // Data is already rendered from server-side template
  // Just populate setupData from DOM for copy/download functions
  setupData = {
    secret: ref.setupSecret.val() || ref.secret.text().trim(),
    backup_codes: (ref.backupCodesText.val() || '').trim().split('\n').filter(c => c)
  };
}

async function verifyCode() {
  const code = ref.mfaSetupForm.find('input[name="code"]').val();

  if (!code || !/^\d{6}$/.test(code)) {
    validation.setError('code', 'regexp');
    return false;
  }

  try {
    validation.onIndicator();
    const formData = new FormData();
    formData.append('code', code);

    const {data} = await userApi.mfaVerifySetup(formData);
    validation.offIndicator();

    if (!data || !data.success) {
      validation.setError('code', 'invalidCode');
      return false;
    }

    return true;
  } catch (err) {
    validation.offIndicator();
    Dialog.unknownError();
    throw err;
  }
}

function initCopyButtons() {
  // Copy secret
  ref.copySecret.on('click', () => {
    navigator.clipboard.writeText(setupData?.secret || '');
    Toast.success('Secret copied to clipboard!');
  });

  // Copy all backup codes
  ref.copyCodes.on('click', () => {
    const codes = setupData?.backup_codes?.join('\n') || '';
    navigator.clipboard.writeText(codes);
    Toast.success('Backup codes copied to clipboard!');
  });

  // Download backup codes
  ref.downloadCodes.on('click', () => {
    const codes = setupData?.backup_codes?.join('\n') || '';
    const blob = new Blob([`CodeIgniter Demo Backup Codes\n${'='.repeat(30)}\n\n${codes}\n\nEach code can only be used once.\nStore these codes in a safe place.`], {type: 'text/plain'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'backup-codes.txt';
    a.click();
    URL.revokeObjectURL(url);
  });

  // Print backup codes
  if (ref.printCodes.length) {
    ref.printCodes.on('click', () => {
      const codes = setupData?.backup_codes?.join('\n') || '';
      const printWindow = window.open('', '_blank');
      printWindow.document.write(`
        <html>
        <head><title>Backup Codes</title></head>
        <body style="font-family: monospace; padding: 20px;">
          <h2>CodeIgniter Demo - Backup Codes</h2>
          <p>Store these codes in a safe place. Each code can only be used once.</p>
          <pre style="font-size: 18px; line-height: 2;">${codes}</pre>
          <script>window.print(); window.close();</script>
        </body>
        </html>
      `);
    });
  }
}

function initSkipButton() {
  // Skip MFA setup - user can do it later
  if (ref.skipSetup.length) {
    ref.skipSetup.on('click', () => {
      Dialog.confirm('Skip MFA Setup?',
        'You can enable two-factor authentication later from your profile settings.',
        () => {
          location.href = '/';
        }
      );
    });
  }
}

const userApi = new UserApi();
const ref = selectAll();

initStepper();
initValidation();
initCopyButtons();
initSkipButton();
loadSetup();
