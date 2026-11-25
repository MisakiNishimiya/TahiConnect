/**
 * Decart AI Real-Time Virtual Try-On
 * Handles camera, garment upload, and real-time try-on streaming
 */

import { createDecartClient, models } from '@decartai/sdk';

// Store garment blob and client globally
window.currentGarmentBlob = null;
window.currentRtClient = null;

/**
 * apiKeyProvider: Fetches a short-lived token from the server
 * Called automatically by the SDK when needed
 */
async function apiKeyProvider() {
    console.log('apiKeyProvider: Fetching short-lived token from server...');
    try {
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const response = await fetch('/api/decart/token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Token request failed: ${response.status} - ${errorText}`);
        }
        
        const data = await response.json();
        console.log('apiKeyProvider: Token received, expires at:', data.expiresAt);
        return data.clientToken;
    } catch (error) {
        console.error('apiKeyProvider: Failed to get token:', error);
        throw error;
    }
}

/**
 * Open camera modal and initialize try-on
 */
async function openCameraModal() {
    console.log('openCameraModal: Opening camera modal...');
    const modal = document.getElementById('decart-modal');
    if (!modal) {
        console.error('openCameraModal: Modal element not found');
        return;
    }

    modal.style.display = 'flex';
    await initializeDecartSession();
}

/**
 * Close camera modal and cleanup
 */
function closeCameraModal() {
    console.log('closeCameraModal: Closing modal...');
    const modal = document.getElementById('decart-modal');
    if (modal) {
        modal.style.display = 'none';
    }
    // Stop camera and disconnect
    if (window.currentRtClient) {
        try {
            window.currentRtClient.disconnect();
            console.log('closeCameraModal: Disconnected from Decart realtime session');
        } catch (e) {
            console.error('closeCameraModal: Error disconnecting:', e);
        }
        window.currentRtClient = null;
    }
}

/**
 * Initialize Decart realtime session
 */
async function initializeDecartSession() {
    const loadingSpinner = document.getElementById('loading-spinner');
    try {
        console.log('initializeDecartSession: Starting...');
        loadingSpinner.style.display = 'flex';

        // Check if getUserMedia is available
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error(
                'Camera access not available. This feature requires:\n' +
                '1. HTTPS connection (or localhost)\n' +
                '2. Modern browser with getUserMedia support\n' +
                '3. Camera connected to your device'
            );
        }

        // Create Decart client with apiKeyProvider for token management
        console.log('initializeDecartSession: Creating Decart client...');
        const client = createDecartClient({ apiKeyProvider });

        // Get camera stream from user's device
        console.log('initializeDecartSession: Requesting camera access...');
        const stream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'user',
                width: { ideal: 1280 },
                height: { ideal: 720 },
            },
            audio: false,
        });
        console.log('initializeDecartSession: Camera stream obtained');

        // Connect to Decart realtime model (lucy-vton-3.6)
        console.log('initializeDecartSession: Connecting to lucy-vton-3.6 realtime model...');
        window.currentRtClient = await client.realtime.connect(stream, {
            model: models.realtime('lucy-vton-3.6'),
            onRemoteStream: (remoteStream) => {
                console.log('initializeDecartSession: Remote stream received, rendering to video element');
                const video = document.getElementById('decart-output');
                if (video) {
                    video.srcObject = remoteStream;
                    loadingSpinner.style.display = 'none';
                    console.log('initializeDecartSession: Video stream connected, hiding loading spinner');
                }
            },
        });
        console.log('initializeDecartSession: Connected to realtime model');

        // Send garment image to the realtime client
        if (window.currentGarmentBlob) {
            console.log('initializeDecartSession: Sending garment image to realtime session...');
            await window.currentRtClient.set({
                image: window.currentGarmentBlob,
                prompt: 'Apply this garment to the person in the most realistic and natural way possible',
                enhance: false,
            });
            console.log('initializeDecartSession: Garment image sent successfully');
        } else {
            console.warn('initializeDecartSession: No garment image found');
        }
    } catch (error) {
        console.error('initializeDecartSession: Failed:', error);
        loadingSpinner.style.display = 'none';
        const errorMsg = error.message || String(error);
        alert(`Failed to start try-on:\n\n${errorMsg}\n\nPlease check:\n1. Camera is connected and allowed\n2. Internet connection is stable\n3. Decart API is reachable`);
        closeCameraModal();
    }
}

/**
 * Save try-on result (screenshot)
 */
async function saveDecartResult() {
    try {
        console.log('saveDecartResult: Saving try-on result...');
        const video = document.getElementById('decart-output');
        if (!video || !video.srcObject) {
            alert('No video stream to save');
            return;
        }

        // Create canvas and capture frame
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);

        // Convert canvas to blob
        canvas.toBlob(async (blob) => {
            console.log('saveDecartResult: Captured frame, sending to server...');
            const formData = new FormData();
            formData.append('image', blob, 'tryon-result.jpg');

            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch('/api/decart/save-tryon', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Save failed: ${response.status} - ${errorText}`);
                }

                const result = await response.json();
                console.log('saveDecartResult: Try-on saved successfully');
                alert('Try-on saved successfully!');
                closeCameraModal();
            } catch (error) {
                console.error('saveDecartResult: Failed to save:', error);
                alert('Failed to save try-on: ' + error.message);
            }
        }, 'image/jpeg', 0.95);
    } catch (error) {
        console.error('saveDecartResult: Error:', error);
        alert('Error saving try-on: ' + error.message);
    }
}

/**
 * Store garment blob when file is selected
 * Hook into Livewire's file upload system
 */
function setupGarmentUploadListener() {
    // Listen for Livewire updates
    document.addEventListener('livewire:updated', function (e) {
        console.log('Livewire updated event detected');
        const fileInput = document.querySelector('input[type="file"]');
        if (fileInput && fileInput.files && fileInput.files[0]) {
            window.currentGarmentBlob = fileInput.files[0];
            console.log('Garment blob stored:', window.currentGarmentBlob.name);
        }
    });

    // Store initial garment file if already uploaded
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput && fileInput.files && fileInput.files[0]) {
        window.currentGarmentBlob = fileInput.files[0];
        console.log('Initial garment blob stored:', window.currentGarmentBlob.name);
    }
}

/**
 * Initialize on DOM ready
 */
function initializeDecartTryOn() {
    console.log('Decart Virtual Try-On: Module loaded and initializing...');
    setupGarmentUploadListener();
    console.log('Decart SDK module: All functions registered globally');
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeDecartTryOn);
} else {
    initializeDecartTryOn();
}

// Re-initialize after Livewire navigation
document.addEventListener('livewire:navigated', initializeDecartTryOn);

// Export functions for HTML onclick handlers
window.openCameraModal = openCameraModal;
window.closeCameraModal = closeCameraModal;
window.saveDecartResult = saveDecartResult;
