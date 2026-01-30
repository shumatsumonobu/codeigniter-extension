import Api from '~/shared/Api';

export default class extends Api {
  constructor() {
    super('/users');
  }

  async login(formData) {
    return this.client.post('login', formData);
  }

  async logout() {
    location.assign('/api/users/logout');
  }

  async createUser(formData) {
    return this.client.post('/', formData);
  }

  async getUser(userId) {
    return this.client.get(`/${userId}`);
  }

  async updateUser(userId, formData) {
    return this.client.put(`/${userId}`, formData);
  }

  async deleteUser(userId) {
    return this.client.delete(`/${userId}`);
  }

  async updateProfile(formData) {
    return this.client.put('/profile', formData);
  }

  // MFA Methods
  async mfaStatus() {
    return this.client.get('/mfa-status');
  }

  async mfaSetup() {
    return this.client.post('/mfa-setup');
  }

  async mfaVerifySetup(formData) {
    return this.client.post('/mfa-verify-setup', formData);
  }

  async mfaVerifyLogin(formData) {
    return this.client.post('/mfa-verify-login', formData);
  }

  async mfaDisable(formData) {
    return this.client.post('/mfa-disable', formData);
  }

  async mfaRegenerateBackupCodes(formData) {
    return this.client.post('/mfa-regenerate-backup-codes', formData);
  }

  async mfaRequestRecovery(formData) {
    return this.client.post('/mfa-request-recovery', formData);
  }

  async mfaVerifyRecovery(formData) {
    return this.client.post('/mfa-verify-recovery', formData);
  }
}