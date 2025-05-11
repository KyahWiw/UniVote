<?php
session_start();
if (
    !isset($_SESSION['pending_2fa_idscan']) ||
    !isset($_SESSION['2fa_student_id']) ||
    !isset($_SESSION['2fa_fullname'])
) {
    header("Location: login.php");
    exit();
}
$student_id = strtoupper(str_replace([' ', '-', "\n", "\r"], '', $_SESSION['2fa_student_id']));
$fullname = strtoupper(str_replace([' ', '-', "\n", "\r"], '', $_SESSION['2fa_fullname']));

// Expected values for yellow box fields
$expected_university = "ARELLANO UNIVERSITY";
$expected_student_id = $student_id;
$expected_fullname = $fullname;

$error_message = "";
$ocr_debug = "";

function fuzzy_match($needle, $haystack, $threshold = 70) {
    similar_text($needle, $haystack, $percent);
    return $percent >= $threshold;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ocr_text = isset($_POST['ocr_text']) ? $_POST['ocr_text'] : '';
    $ocr_debug = $ocr_text;

    // Normalize OCR text and split into lines
    $ocr_lines = preg_split('/\r\n|\r|\n/', strtoupper($ocr_text));

    // 1. University Name (fuzzy match)
    $university_found = false;
    foreach ($ocr_lines as $line) {
        if (fuzzy_match($expected_university, $line, 70)) {
            $university_found = true;
            break;
        }
    }

    // 2. Full Name (fuzzy match, remove spaces/dashes)
    $fullname_found = false;
    foreach ($ocr_lines as $line) {
        $line_clean = str_replace([' ', '-', "\n", "\r"], '', $line);
        if (fuzzy_match($expected_fullname, $line_clean, 70)) {
            $fullname_found = true;
            break;
        }
    }

    // 3. Student Number (allow for missing dash or spaces)
    $student_found = false;
    $expected_digits = preg_replace('/\D/', '', $expected_student_id);
    foreach ($ocr_lines as $line) {
        $line_digits = preg_replace('/\D/', '', $line);
        if (strpos($line_digits, $expected_digits) !== false) {
            $student_found = true;
            break;
        }
    }

    // Feedback
    if ($university_found && $fullname_found && $student_found) {
        unset($_SESSION['pending_2fa_idscan']);
        $_SESSION['2fa_verified'] = true;
        header("Location: ../../Voters Page/Homepage (Php)/index.php");
        exit();
    } else {
        $error_message = "ID verification failed. ";
        if (!$university_found) $error_message .= "University name not found. ";
        if (!$fullname_found) $error_message .= "Full name not found. ";
        if (!$student_found) $error_message .= "Student number not found. ";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>2FA: Scan School ID</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="shadow p-4 bg-white rounded">
        <h2 class="mb-4">Verify Your School ID</h2>
        <div id="scan-instruction" class="mb-2">
            Please capture or upload a clear photo of your ID.<br>
            Make sure the Student No. and Name are readable.
        </div>
        <div id="scan-area">
    <div style="position:relative; width:340px; height:220px; margin: 0 auto;">
        <video id="camera-preview" autoplay playsinline style="width:340px; height:220px; display:none; background:#222;"></video>
        <div id="id-frame-overlay"></div>
    </div>
    <button class="btn btn-success" id="captureBtn" type="button" style="display:none; margin: 10px auto;">Capture</button>
    <img id="preview" src="#" alt="ID Preview" style="display:none; margin: 0 auto;">
    <!-- Add file upload option -->
    <div class="mb-2 text-center">
        <input type="file" id="fileInput" accept="image/*" class="form-control" style="width: 80%; margin: 0 auto;">
        <small class="text-muted">Or upload a photo of your ID</small>
    </div>
    <div class="d-flex gap-2 mb-2 justify-content-center">
        <button class="btn btn-primary" id="openCameraBtn" type="button">Open Camera</button>
        <button class="btn btn-secondary" id="closeCameraBtn" type="button" style="display:none;">Close Camera</button>
    </div>
    <button class="btn btn-success" id="scanBtn" disabled style="margin: 0 auto;">Scan ID</button>
    <div id="ocr-status" class="mt-2 text-secondary"></div>
    <div id="camera-error" class="alert alert-warning text-center py-2" style="display:none;"></div>
</div>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger text-center py-2"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($ocr_debug)): ?>
            <div class="alert alert-warning text-center py-2" style="white-space: pre-wrap;">
                <strong>OCR Result:</strong><br><?php echo htmlspecialchars($ocr_debug); ?>
            </div>
        <?php endif; ?>
        <form method="POST" id="idScanForm">
            <input type="hidden" name="ocr_text" id="ocr_text">
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5.0.1/dist/tesseract.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const cameraPreview = document.getElementById('camera-preview');
    const preview = document.getElementById('preview');
    const scanBtn = document.getElementById('scanBtn');
    const captureBtn = document.getElementById('captureBtn');
    const closeCameraBtn = document.getElementById('closeCameraBtn');
    const openCameraBtn = document.getElementById('openCameraBtn');
    const fileInput = document.getElementById('fileInput');
    const ocrStatus = document.getElementById('ocr-status');
    const cameraError = document.getElementById('camera-error');
    let stream = null;
    let imageData = null;

    // Open Camera (simple constraints for max compatibility)
    openCameraBtn.addEventListener('click', function() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showError("Camera not supported in this browser. Please use a supported browser or upload a photo instead.");
            return;
        }
        if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
            showError("Camera access requires HTTPS or localhost. Please use the file upload option below.");
            return;
        }
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function (streamObj) {
                stream = streamObj;
                cameraPreview.srcObject = stream;
                cameraPreview.style.display = 'block';
                preview.style.display = 'none';
                openCameraBtn.style.display = 'none';
                closeCameraBtn.style.display = 'inline-block';
                captureBtn.style.display = 'inline-block';
                scanBtn.disabled = true;
                imageData = null;
                hideError();
            })
            .catch(function (err) {
                let msg = "Unable to access camera: " + err.message + ".";
                if (err.name === "NotAllowedError") {
                    msg += " Please allow camera access in your browser settings, or use the file upload option below.";
                } else if (err.name === "NotFoundError" || err.name === "OverconstrainedError") {
                    msg += " No camera device found, or it is in use. Try closing other apps or use the file upload option below.";
                } else if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                    msg += " Camera access requires HTTPS or localhost. Please use the file upload option below.";
                }
                showError(msg);
                cameraPreview.style.display = 'none';
                openCameraBtn.style.display = 'inline-block';
                closeCameraBtn.style.display = 'none';
                captureBtn.style.display = 'none';
                scanBtn.disabled = true;
            });
    });

    // Capture from camera
    captureBtn.addEventListener('click', function() {
        if (!cameraPreview.srcObject) return;
        const canvas = document.createElement('canvas');
        canvas.width = cameraPreview.videoWidth;
        canvas.height = cameraPreview.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(cameraPreview, 0, 0, canvas.width, canvas.height);
        imageData = canvas.toDataURL('image/png');
        preview.src = imageData;
        preview.style.display = 'block';
        scanBtn.disabled = false;
        // Optionally stop camera after capture
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        cameraPreview.style.display = 'none';
        openCameraBtn.style.display = 'inline-block';
        closeCameraBtn.style.display = 'none';
        captureBtn.style.display = 'none';
    });

    // Close Camera
    closeCameraBtn.addEventListener('click', function() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        cameraPreview.style.display = 'none';
        openCameraBtn.style.display = 'inline-block';
        closeCameraBtn.style.display = 'none';
        captureBtn.style.display = 'none';
        scanBtn.disabled = true;
        preview.style.display = 'none';
    });

    // File upload
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(ev) {
            imageData = ev.target.result;
            preview.src = imageData;
            preview.style.display = 'block';
            scanBtn.disabled = false;
        };
        reader.readAsDataURL(file);
        // Hide camera if open
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        cameraPreview.style.display = 'none';
        openCameraBtn.style.display = 'inline-block';
        closeCameraBtn.style.display = 'none';
        captureBtn.style.display = 'none';
    });

    // Scan ID (OCR)
    scanBtn.addEventListener('click', function() {
        if (!imageData) {
            showError("No image to scan. Please capture or upload an ID photo.");
            return;
        }
        scanBtn.disabled = true;
        ocrStatus.textContent = "Scanning ID, please wait...";
        Tesseract.recognize(
            imageData,
            'eng',
            { logger: m => { ocrStatus.textContent = "Scanning: " + Math.round(m.progress * 100) + "%"; } }
        ).then(({ data: { text } }) => {
            document.getElementById('ocr_text').value = text;
            document.getElementById('idScanForm').submit();
        }).catch(err => {
            showError("OCR failed: " + err.message);
            scanBtn.disabled = false;
        });
    });

    function showError(msg) {
        if (ocrStatus) ocrStatus.textContent = msg;
        if (cameraError) {
            cameraError.textContent = msg;
            cameraError.style.display = 'block';
        }
    }
    function hideError() {
        if (ocrStatus) ocrStatus.textContent = '';
        if (cameraError) cameraError.style.display = 'none';
    }
});
</script>
</body>
</html>