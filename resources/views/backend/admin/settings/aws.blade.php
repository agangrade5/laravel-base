<div class="card settings-card mb-4">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-cloud-arrow-up-fill fs-4 me-2 text-primary"></i>
        <div>
            <h5 class="mb-0 fw-bold">AWS Cloud Settings</h5>
            <small class="text-muted">Manage Amazon Web Services (AWS) S3 storage, SES mail, and SDK credentials.</small>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.aws') }}" id="aws-settings-form" class="row g-4">
            @csrf
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="aws_access_key_id">AWS Access Key ID</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="text" class="form-control font-mono" id="aws_access_key_id" name="aws_access_key_id" placeholder="AKIAIOSFODNN7EXAMPLE" value="{{ $awsData['aws_access_key_id'] ?? '' }}">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="aws_secret_access_key">AWS Secret Access Key</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control font-mono" id="aws_secret_access_key" name="aws_secret_access_key" placeholder="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY" value="{{ $awsData['aws_secret_access_key_decrypted'] ?? '' }}">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="aws_default_region">AWS Default Region</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                    <select class="form-select" id="aws_default_region" name="aws_default_region">
                        <option value="us-east-1" {{ ($awsData['aws_default_region'] ?? 'us-east-1') == 'us-east-1' ? 'selected' : '' }}>us-east-1 (N. Virginia)</option>
                        <option value="us-west-2" {{ ($awsData['aws_default_region'] ?? 'us-east-1') == 'us-west-2' ? 'selected' : '' }}>us-west-2 (Oregon)</option>
                        <option value="ap-south-1" {{ ($awsData['aws_default_region'] ?? 'us-east-1') == 'ap-south-1' ? 'selected' : '' }}>ap-south-1 (Mumbai)</option>
                        <option value="eu-central-1" {{ ($awsData['aws_default_region'] ?? 'us-east-1') == 'eu-central-1' ? 'selected' : '' }}>eu-central-1 (Frankfurt)</option>
                        <option value="ap-southeast-1" {{ ($awsData['aws_default_region'] ?? 'us-east-1') == 'ap-southeast-1' ? 'selected' : '' }}>ap-southeast-1 (Singapore)</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="aws_bucket">AWS S3 Bucket Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-folder-symlink"></i></span>
                    <input type="text" class="form-control" id="aws_bucket" name="aws_bucket" placeholder="my-bucket-name" value="{{ $awsData['aws_bucket'] ?? '' }}">
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4 py-2" id="aws-settings-btn">
                    <i class="bi bi-check-lg me-1"></i> Save AWS Settings
                </button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
    {!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/aws-settings.js')) !!}
@endpush
