const form = document.querySelector('#uploadForm');
const uploadBox = document.querySelector('.upload-box');
const fileInput = document.querySelector('#fileToUpload');
const fileNameLabel = document.querySelector('.file-info strong');
const messageBox = document.querySelector('.message');
const submitBtn = document.querySelector('button[type="submit"]');

function setMessage(text, type = 'info') {
    messageBox.textContent = text;
    messageBox.classList.toggle('success', type === 'success');
    messageBox.classList.toggle('error', type === 'error');
}

function formatFileName(name) {
    return name || 'Belum ada file yang dipilih';
}

fileInput.addEventListener('change', () => {
    fileNameLabel.textContent = formatFileName(fileInput.files[0]?.name);
});

['dragenter', 'dragover'].forEach((eventName) => {
    uploadBox.addEventListener(eventName, (event) => {
        event.preventDefault();
        event.stopPropagation();
        uploadBox.classList.add('dragover');
    });
});

['dragleave', 'drop'].forEach((eventName) => {
    uploadBox.addEventListener(eventName, (event) => {
        event.preventDefault();
        event.stopPropagation();
        if (eventName === 'drop') {
            const files = event.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                fileNameLabel.textContent = formatFileName(files[0].name);
            }
        }
        uploadBox.classList.remove('dragover');
    });
});

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    if (!fileInput.files.length) {
        setMessage('Pilih file terlebih dahulu sebelum mengunggah.', 'error');
        return;
    }

    const file = fileInput.files[0];
    setMessage(`Mengunggah “${file.name}” ...`);
    submitBtn.disabled = true;

    try {
        const formData = new FormData();
        formData.append('fileToUpload', file);
        formData.append('submit', 'Unggah File');

        const response = await fetch('upload.php', {
            method: 'POST',
            body: formData,
        });

        const text = await response.text();
        const normalized = text.trim().toLowerCase();
        const resultType = normalized.includes('terupload') ? 'success' : 'error';
        setMessage(text, resultType);
    } catch (error) {
        setMessage('Terjadi kesalahan saat mengunggah. Silakan coba lagi.', 'error');
    } finally {
        submitBtn.disabled = false;
    }
});
