<div
    class="modal fade"
    id="crop-profile-modal"
    tabindex="-1"
    aria-labelledby="cropProfileModalLabel"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropProfileModalLabel">
                    Crop Profile Picture
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>
            <div class="modal-body p-3">

                <div class="cropper-wrapper">
                    <img
                        id="cropper-image"
                        src=""
                        alt="Crop Profile Picture"
                    >
                </div>
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    class="btn btn-primary"
                    id="crop-image-btn"
                >
                    <i class="bi bi-crop me-1"></i>
                    Crop & Continue
                </button>
            </div>

        </div>
    </div>
</div>
@push('scripts')
{!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/image-cropper.js')) !!}
@endpush
