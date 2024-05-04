// Get the popup and button elements
const popup = document.getElementById('popup');
const popupForm = document.getElementById('popupForm');
const addProductBtn = document.getElementById('addProductBtn');
const closeBtn = document.getElementById('closeBtn');

// Function to open the popup
function openPopup(data) {
    popupForm.reset();
    if (data) {
        popupForm.productID.value = data.productID;
        popupForm.name.value = data.name;
        popupForm.description.value = data.description;
        popupForm.price.value = data.price;
    }
    popup.style.display = 'block';
}

// Function to close the popup
function closePopup() {
    popup.style.display = 'none';
}

// Event listeners for opening and closing the popup
addProductBtn.addEventListener('click', () => {
    openPopup();
});
closeBtn.addEventListener('click', closePopup);

// Close the popup when clicking outside the popup content
window.addEventListener('click', function (event) {
    if (event.target == popup) {
        closePopup();
    }
});

// Event listener for form submission
popupForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    const productID = popupForm.productID.value;
    const productData = {
        name: popupForm.name.value,
        description: popupForm.description.value,
        price: popupForm.price.value
    };
    /*
        If productID exists, update row
        Else create new row
    */
    await productID ? updateProduct(productID, productData) : createProduct(productData);
    // Close popup after successful submission
    closePopup();
});