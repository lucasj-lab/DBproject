document.addEventListener("DOMContentLoaded", function () {
    const imageInput = document.querySelector("input[name='files[]']");
    const previewContainer = document.getElementById("imagePreviewContainer");

    imageInput.addEventListener("change", function () {
        previewContainer.innerHTML = ""; // Clear previous previews
        Array.from(imageInput.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.createElement("img");
                img.src = e.target.result;
                img.classList.add("preview-image"); // Ensure styling for .preview-image is in CSS
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });
});
