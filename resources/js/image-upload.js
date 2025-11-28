// Image Upload with Drag & Drop and Paste Support
document.addEventListener('DOMContentLoaded', function() {
    const dropZones = document.querySelectorAll('.image-drop-zone');
    
    dropZones.forEach(dropZone => {
        // Skip if already initialized by inline script
        if (dropZone.dataset.uploadBound) return;
        dropZone.dataset.uploadBound = 'true';
        
        const input = dropZone.querySelector('input[type="file"]');
        const preview = dropZone.querySelector('.image-preview');
        const placeholder = dropZone.querySelector('.upload-placeholder');
        const removeBtn = dropZone.querySelector('.remove-image-btn');
        const browseBtn = dropZone.querySelector('.browse-file-btn');
        
        if (!input) return;
        
        // Browse button opens file picker
        if (browseBtn) {
            browseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                input.click();
            });
        }
        
        // File input change
        input.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });
        
        // Drag and drop
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--bs-primary)';
        });
        
        dropZone.addEventListener('dragleave', () => {
            dropZone.style.borderColor = '';
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                // Set files to input
                const dt = new DataTransfer();
                dt.items.add(files[0]);
                input.files = dt.files;
                
                handleFiles(files);
            }
        });
        
        // Paste support when zone is focused
        dropZone.addEventListener('paste', (e) => {
            const items = e.clipboardData?.items;
            if (!items) return;
            
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    e.preventDefault();
                    const blob = items[i].getAsFile();
                    
                    // Create a File object
                    const file = new File([blob], `pasted-image-${Date.now()}.png`, { type: blob.type });
                    
                    // Set to input
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                    
                    handleFiles([file]);
                    break;
                }
            }
        });

        // Focus visual feedback
        dropZone.addEventListener('focus', () => {
            dropZone.style.borderColor = 'var(--bs-primary)';
        });
        
        dropZone.addEventListener('blur', () => {
            dropZone.style.borderColor = '';
        });
        
        // Remove image
        if (removeBtn) {
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                input.value = '';
                preview.style.display = 'none';
                placeholder.style.display = 'flex';
                removeBtn.style.display = 'none';
            });
        }
        
        function handleFiles(files) {
            if (files.length === 0) return;
            
            const file = files[0];
            
            // Validate file type
            if (!file.type.match('image.*')) {
                alert('Please upload an image file');
                return;
            }
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Image must be less than 5MB');
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
                if (removeBtn) removeBtn.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
});
