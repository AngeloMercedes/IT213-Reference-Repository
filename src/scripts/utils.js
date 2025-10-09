// Initialize API url
const apiUrl = 'http://localhost:3000/products';

// Initialize table data
const data = [];

/* API calls */

// Function to fetch table data from server
async function fetchData() {
    // Make a GET request to the API
    return fetch(apiUrl)
        .then(response => response.json()) // Parse the JSON response
        .then(response => {
            // Push products into data array
            response.products.forEach((product) => {
                data.push(product);
            });
        })
        .catch(error => {
            console.error('Error fetching data:', error);
        });
}

// Function to create new record in server
async function createProduct(productData) {
    console.log('Creating product: ', productData);
    try {
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(productData),
        });

        // Parse the JSON response
        const data = await response.json();

        // Handle the response data
        if (response.ok) {
            console.log('Form submitted successfully:', data.message);
            // Reload page to update table data
            window.location.reload();
            /* 
                Note that you can update table data without reloading the page
                You can also perform other actions here, such as showing a success message
            */
        } else {
            console.error('Error submitting form:', data);
            // Handle the error response
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        // Handle any other errors
    }
}

async function deleteProduct(id) {
    try {
        const response = await fetch(`${apiUrl}/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        // Parse the JSON response
        const data = await response.json();

        // Handle the response data
        if (response.ok) {
            console.log('Successfully deleted record with id: ', id);
            // Close the popup after successful deletion
            window.location.reload();
            // You can also perform other actions here, such as showing a success message
        } else {
            console.error('Error deleting record:', data);
            // Handle the error response
        }
    } catch (error) {
        console.error('Error requesting delete:', error);
        // Handle any other errors
    }
}

async function updateProduct(id, productData) {
    try {
        const response = await fetch(`${apiUrl}/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(productData)
        });

        // Parse the JSON response
        const data = await response.json();

        // Handle the response data
        if (response.ok) {
            console.log('Form submitted successfully:', data.message);
            // Reload page to update table data
            window.location.reload();
            /* 
                Note that you can update table data without reloading the page
                You can also perform other actions here, such as showing a success message
            */
        } else {
            console.error('Error submitting form:', data);
            // Handle the error response
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        // Handle any other errors
    }
}