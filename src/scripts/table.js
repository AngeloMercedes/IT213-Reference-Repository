// Get table element
const dataBody = document.getElementById('data-body');

// Function to bind row buttons (Edit and Delete) to handlers
function bindRowButtons() {
    const editButtons = document.querySelectorAll('.data-row-edit-button');
    const deleteButtons = document.querySelectorAll('.data-row-delete-button');

    deleteButtons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            deleteProduct(e.target.getAttribute('data-row-id'));
        });
    });

    // Get the popup and button elements
    editButtons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            openPopup(data.find((product) => (product.productID == e.target.getAttribute('data-row-id'))));
        })
    });
}

// Function to render data in the table
function renderData() {
    console.log('Rendering table...');
    dataBody.innerHTML = '';
    data.forEach((item) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.productID}</td>
            <td>${item.name}</td>
            <td>${item.description}</td>
            <td>${item.price}</td>
            <td>
                <button data-row-id="${item.productID}" class="data-row-edit-button">Edit</button>
                <button data-row-id="${item.productID}" class="data-row-delete-button">Delete</button>
            </td>
        `;
        dataBody.appendChild(row);
    });
    bindRowButtons();
}

// Initial rendering
fetchData().then(() => {
    renderData();
});