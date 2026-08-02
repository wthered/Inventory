const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const productId = document.getElementById('product_id').value;

document.addEventListener('DOMContentLoaded', function () {
    attachDeleteListeners();
});

// Select all delete buttons and attach event listeners
function attachDeleteListeners() {
    const deleteButtons = document.querySelectorAll('button.btn.small.delete');
    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const imageUrl = this.dataset.image;
            console.log("Clicked to delete/remove", imageUrl);
            refreshPreview(imageUrl);
        });
    });
}

function refreshPreview(imageUrl) {
    fetch(`/products/${productId}/images/detach`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({image: imageUrl})
    }).then(function (response) {
        // Convert response to JSON
        return response.json();
    }).then(function (data) {
        if (data.success) {
            document.getElementById("product-images").innerHTML = data.code;
            attachDeleteListeners();
        } else {
            alert('Failed to remove image.');
        }
    }).catch(function (err) {
        console.error('Error removing image:', err);
        alert('Error removing image.');
    });
}

// Upload an Image and couple it with the selected product
// Upload an Image and couple it with the selected product
document.getElementById('image_upload').addEventListener('change', function (event) {
    const fileInput = event.target;
    const files = fileInput.files;

    if (!files.length) {
        alert('Please select at least one image.');
        return;
    }

    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('images[]', files[i]);
    }

    // UI Feedback: Show spinner & disable file input / update button
    const spinner = document.getElementById('upload-spinner');
    const updateButton = document.getElementById('updateButton');

    spinner.style.display = 'block';
    fileInput.disabled = true;
    if (updateButton) updateButton.disabled = true;

    fetch(`/products/${productId}/images/attach`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: formData
    }).then(async (response) => {
        const data = await response.json();

        if (!response.ok) {
            return Promise.reject(data);
        }

        return data;
    }).then(data => {
        if (data.success) {
            document.getElementById('product-images').innerHTML = data.code;
            attachDeleteListeners();
        }
    }).catch(error => {
        console.error('Upload error details:', error);

        if (error.errors) {
            const messages = Object.values(error.errors).flat().join('\n');
            alert('Validation Error:\n' + messages);
        } else if (error.message) {
            alert('Error: ' + error.message);
        } else {
            alert('Something went wrong while uploading.');
        }
    }).finally(() => {
        // UI Cleanup: Re-enable elements & hide spinner
        spinner.style.display = 'none';
        fileInput.disabled = false;
        fileInput.value = ''; // Reset input selection
        if (updateButton) updateButton.disabled = false;
    });
});

document.getElementById('category_id').addEventListener('change', function (event) {
    const selectedCategory = event.target.value;

    fetch(`/categories/${selectedCategory}/filter`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(response => {
        return response.json();
    }).then(data => {
        // For example, update a product list or the subcategories list
        document.getElementById('sub_category').innerHTML = data.options;
    }).catch(error => {
        console.error('Error:', error);
    });
});

document.getElementById('sub_category').addEventListener('change', function (event) {
    const productCategory = event.target.value;

    fetch(`/categories/${productCategory}/brands`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            parent_category: document.getElementById('category_id').value,
        })
    }).then(response => {
        return response.text();
    }).then(data => {
        console.log("Server response Data:", data);
        // For example, update a product list or the subcategories list
        document.getElementById('brand_id').innerHTML = data;
    }).catch(error => {
        console.error('Error:', error);
    });
});

// document.getElementById("updateButton").addEventListener('click', function (event) {
// 	event.preventDefault();
// });