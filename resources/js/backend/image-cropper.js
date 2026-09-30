document.addEventListener('DOMContentLoaded', () => {

    const avatarInput = document.getElementById('avatar-file-input');
    const cropperImage = document.getElementById('cropper-image');
    const cropProfileModal = document.getElementById('crop-profile-modal');
    const cropImageBtn = document.getElementById('crop-image-btn');

    const avatarPreview = document.getElementById('avatar-preview');

    let cropper = null;
    let selectedImageUrl = null;

    if (
        !avatarInput ||
        !cropperImage ||
        !cropProfileModal ||
        !cropImageBtn
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Select Image
    |--------------------------------------------------------------------------
    */

    avatarInput.addEventListener('change', function (event) {

        const file = event.target.files[0];

        if (!file) {
            return;
        }

        /*
        |----------------------------------------------------------------------
        | Validate File Type
        |----------------------------------------------------------------------
        */

        if (!file.type.startsWith('image/')) {

            if (window.toastr) {
                toastr.error('Please select a valid image.');
            }

            avatarInput.value = '';

            return;
        }

        /*
        |----------------------------------------------------------------------
        | Validate File Size
        |----------------------------------------------------------------------
        */

        if (file.size > 2 * 1024 * 1024) {

            if (window.toastr) {
                toastr.error('Image size must not exceed 2MB.');
            }

            avatarInput.value = '';

            return;
        }

        /*
        |----------------------------------------------------------------------
        | Create Object URL
        |----------------------------------------------------------------------
        */

        if (selectedImageUrl) {
            URL.revokeObjectURL(selectedImageUrl);
        }

        selectedImageUrl = URL.createObjectURL(file);

        cropperImage.src = selectedImageUrl;

        /*
        |----------------------------------------------------------------------
        | Open Bootstrap Modal
        |----------------------------------------------------------------------
        */

        const modal = bootstrap.Modal.getOrCreateInstance(
            cropProfileModal
        );

        modal.show();
    });


    /*
    |--------------------------------------------------------------------------
    | Initialize Cropper AFTER Modal Is Visible
    |--------------------------------------------------------------------------
    */

    cropProfileModal.addEventListener('shown.bs.modal', function () {

        if (cropper) {
            cropper.destroy();
            cropper = null;
        }

        cropper = new Cropper(cropperImage, {

            aspectRatio: 1,

            viewMode: 1,

            dragMode: 'move',

            autoCropArea: 0.9,

            responsive: true,

            restore: false,

            guides: true,

            center: true,

            highlight: false,

            cropBoxMovable: true,

            cropBoxResizable: true,

            toggleDragModeOnDblclick: false,

        });
    });


    /*
    |--------------------------------------------------------------------------
    | Crop Image
    |--------------------------------------------------------------------------
    */

    cropImageBtn.addEventListener('click', function () {

        if (!cropper) {
            return;
        }

        const canvas = cropper.getCroppedCanvas({

            width: 400,

            height: 400,

            imageSmoothingEnabled: true,

            imageSmoothingQuality: 'high',

        });

        if (!canvas) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Update Preview
        |--------------------------------------------------------------------------
        */

        const croppedImage = canvas.toDataURL(
            'image/webp',
            0.85
        );

        avatarPreview.src = croppedImage;

        /*
        |--------------------------------------------------------------------------
        | Convert Canvas To File
        |--------------------------------------------------------------------------
        */

        canvas.toBlob(function (blob) {

            if (!blob) {

                if (window.toastr) {
                    toastr.error(
                        'Unable to process cropped image.'
                    );
                }

                return;
            }

            const croppedFile = new File(
                [blob],
                'avatar.webp',
                {
                    type: 'image/webp',
                    lastModified: Date.now(),
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Put Cropped File Into File Input
            |--------------------------------------------------------------------------
            */

            const dataTransfer = new DataTransfer();

            dataTransfer.items.add(croppedFile);

            avatarInput.files = dataTransfer.files;

            /*
            |--------------------------------------------------------------------------
            | Reset Remove Flag
            |--------------------------------------------------------------------------
            */

            const removeProfileImage = document.getElementById(
                'remove-profile-image'
            );

            if (removeProfileImage) {
                removeProfileImage.value = '0';
            }

            /*
            |--------------------------------------------------------------------------
            | Close Modal
            |--------------------------------------------------------------------------
            */

            const modal = bootstrap.Modal.getInstance(
                cropProfileModal
            );

            if (modal) {
                modal.hide();
            }

            if (window.toastr) {
                toastr.success(
                    'Profile picture cropped successfully.'
                );
            }

        }, 'image/webp', 0.85);
    });


    /*
    |--------------------------------------------------------------------------
    | Destroy Cropper When Modal Closes
    |--------------------------------------------------------------------------
    */

    cropProfileModal.addEventListener('hidden.bs.modal', function () {

        if (cropper) {
            cropper.destroy();
            cropper = null;
        }

        cropperImage.removeAttribute('src');

        if (selectedImageUrl) {
            URL.revokeObjectURL(selectedImageUrl);
            selectedImageUrl = null;
        }
    });
});
