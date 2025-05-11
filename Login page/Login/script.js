// Password Toggle Functionality
document.addEventListener("DOMContentLoaded", function() {
    const passwordInput = document.getElementById("password");
    const togglePassword = document.querySelector('.toggle-password');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            const eyeOpen = this.querySelector('.eye-open');
            const eyeClosed = this.querySelector('.eye-closed');
            if (isHidden) {
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'inline';
            } else {
                eyeOpen.style.display = 'inline';
                eyeClosed.style.display = 'none';
            }
        });
    }
});

// 2FA Modal Handling
document.body.style.overflow = 'hidden';
document.getElementById('close-2fa-modal').onclick = function() {
    document.getElementById('twofa-modal').style.display = 'none';
    document.body.style.overflow = '';
};

// 2FA ID Scanner Implementation
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const elements = {
        cameraPreview: document.getElementById('camera-preview'),
        preview: document.getElementById('preview'),
        scanBtn: document.getElementById('scanBtn'),
        captureBtn: document.getElementById('captureBtn'),
        closeCameraBtn: document.getElementById('closeCameraBtn'),
        openCameraBtn: document.getElementById('openCameraBtn'),
        fileInput: document.getElementById('fileInput'),
        ocrStatus: document.getElementById('ocr-status'),
        cameraError: document.getElementById('camera-error')
    };

    let stream = null;
    let imageData = null;

    // Camera Functions
    const cameraFunctions = {
        openCamera() {
            if (!this.checkCameraSupport()) return;

            navigator.mediaDevices.getUserMedia({ video: true })
                .then(streamObj => {
                    stream = streamObj;
                    elements.cameraPreview.srcObject = stream;
                    this.updateCameraUI(true);
                    hideError();
                })
                .catch(err => this.handleCameraError(err));
        },

        checkCameraSupport() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showError("Camera not supported in this browser. Please use a supported browser or upload a photo instead.");
                return false;
            }
            if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                showError("Camera access requires HTTPS or localhost. Please use the file upload option below.");
                return false;
            }
            return true;
        },

        handleCameraError(err) {
            let msg = "Unable to access camera: " + err.message + ".";
            if (err.name === "NotAllowedError") {
                msg += " Please allow camera access in your browser settings, or use the file upload option below.";
            } else if (err.name === "NotFoundError" || err.name === "OverconstrainedError") {
                msg += " No camera device found, or it is in use. Try closing other apps or use the file upload option below.";
            }
            showError(msg);
            this.updateCameraUI(false);
        },

        updateCameraUI(isOpen) {
            elements.cameraPreview.style.display = isOpen ? 'block' : 'none';
            elements.openCameraBtn.style.display = isOpen ? 'none' : 'inline-block';
            elements.closeCameraBtn.style.display = isOpen ? 'inline-block' : 'none';
            elements.captureBtn.style.display = isOpen ? 'inline-block' : 'none';
            elements.scanBtn.disabled = !isOpen;
        },

        captureImage() {
            if (!elements.cameraPreview.srcObject) return;

            const canvas = document.createElement('canvas');
            canvas.width = elements.cameraPreview.videoWidth;
            canvas.height = elements.cameraPreview.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(elements.cameraPreview, 0, 0, canvas.width, canvas.height);

            imageData = canvas.toDataURL('image/png');
            elements.preview.src = imageData;
            elements.preview.style.display = 'block';
            elements.scanBtn.disabled = false;

            this.stopCamera();
            this.updateCameraUI(false);
        },

        stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
        }
    };

    // File Upload Handler
    elements.fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(ev) {
            imageData = ev.target.result;
            elements.preview.src = imageData;
            elements.preview.style.display = 'block';
            elements.scanBtn.disabled = false;
        };
        reader.readAsDataURL(file);
        cameraFunctions.stopCamera();
        cameraFunctions.updateCameraUI(false);
    });

    // OCR Scanning
    elements.scanBtn.addEventListener('click', function() {
        if (!imageData) {
            showError("No image to scan. Please capture or upload an ID photo.");
            return;
        }

        elements.scanBtn.disabled = true;
        elements.ocrStatus.textContent = "Scanning ID, please wait...";

        Tesseract.recognize(
            imageData,
            'eng', { logger: m => { elements.ocrStatus.textContent = "Scanning: " + Math.round(m.progress * 100) + "%"; } }
        ).then(({ data: { text } }) => {
            document.getElementById('ocr_text').value = text;
            document.getElementById('idScanForm').submit();
        }).catch(err => {
            showError("OCR failed: " + err.message);
            elements.scanBtn.disabled = false;
        });
    });

    // Event Listeners
    elements.openCameraBtn.addEventListener('click', () => cameraFunctions.openCamera());
    elements.captureBtn.addEventListener('click', () => cameraFunctions.captureImage());
    elements.closeCameraBtn.addEventListener('click', () => {
        cameraFunctions.stopCamera();
        cameraFunctions.updateCameraUI(false);
        elements.preview.style.display = 'none';
    });

    // Utility Functions
    function showError(msg) {
        if (elements.ocrStatus) elements.ocrStatus.textContent = msg;
        if (elements.cameraError) {
            elements.cameraError.textContent = msg;
            elements.cameraError.style.display = 'block';
        }
    }

    function hideError() {
        if (elements.ocrStatus) elements.ocrStatus.textContent = '';
        if (elements.cameraError) elements.cameraError.style.display = 'none';
    }
});