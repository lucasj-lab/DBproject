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


// JavaScript function to preview selected image files before upload
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById("imagePreview").src = e.target.result;
                    document.getElementById("imagePreview").style.display = "block";
                };
                reader.readAsDataURL(file);
            }
        }