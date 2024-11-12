// image_preview.js

// Image preview functionality
document.addEventListener("DOMContentLoaded", function () {
    const imageInput = document.querySelector("input[name='images[]']");
    const previewContainer = document.getElementById("imagePreviewContainer");

    imageInput.addEventListener("change", function () {
        previewContainer.innerHTML = ""; // Clear previous previews
        Array.from(imageInput.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.createElement("img");
                img.src = e.target.result;
                img.classList.add("preview-image");
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });
});

// File names display functionality
document.getElementById('fileInput').addEventListener('change', function () {
    const fileNames = Array.from(this.files).map(file => file.name).join(', ');
    document.querySelector('.file-upload-text').textContent = fileNames || "No files chosen";
});
