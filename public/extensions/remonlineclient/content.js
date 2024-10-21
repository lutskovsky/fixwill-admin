let clientId = null;

function handleTelLinkClick(event) {
    let target = event.target;

    // Traverse up the DOM to find the <a> parent element
    while (target && target.tagName !== 'A') {
        target = target.parentElement;
    }

    if (target && target.getAttribute('href') && target.getAttribute('href').startsWith('tel:')) {
        event.preventDefault();
        const phoneNumber = target.getAttribute('href').substring(4); // Remove 'tel:'

        // Retrieve username from local storage
        chrome.storage.local.get('savedUsername', (data) => {
            if (data.savedUsername) {
                const username = data.savedUsername;

                // Show loading indicator
                const loadingMessage = document.createElement('div');
                loadingMessage.textContent = 'Loading virtual numbers...';
                loadingMessage.style.position = 'absolute';
                loadingMessage.style.backgroundColor = '#fff';
                loadingMessage.style.border = '1px solid #ccc';
                loadingMessage.style.padding = '10px';
                loadingMessage.style.zIndex = 9999;

                // Position the loading message near the target element
                const rect = target.getBoundingClientRect();
                loadingMessage.style.top = `${rect.bottom + window.scrollY}px`;
                loadingMessage.style.left = `${rect.left + window.scrollX}px`;

                document.body.appendChild(loadingMessage);

                // Fetch virtual numbers from the API
                fetch(`https://admin.jgjgjhs.site/api/employee/${username}/virtual-numbers`)
                    .then(response => response.json())
                    .then(virtualNumbers => {
                        // Remove loading indicator
                        document.body.removeChild(loadingMessage);

                        if (Array.isArray(virtualNumbers) && virtualNumbers.length > 0) {
                            // Show popup with virtual numbers
                            showVirtualNumbersPopup(target, virtualNumbers, phoneNumber, username);
                        } else {
                            alert('К этому пользователю не привязаны виртуальные номера.');
                        }
                    })
                    .catch(error => {
                        // Remove loading indicator
                        document.body.removeChild(loadingMessage);
                        console.error('Error fetching virtual numbers:', error);
                    });
            } else {
                alert('Username not found in local storage.');
            }
        });
    }
}

function showVirtualNumbersPopup(targetElement, virtualNumbers, phoneNumber, username) {
    // Create the popup element
    const popup = document.createElement('div');
    popup.style.position = 'absolute';
    popup.style.backgroundColor = '#fff';
    popup.style.border = '1px solid #ccc';
    popup.style.padding = '10px';
    popup.style.zIndex = 9999;
    popup.style.boxShadow = '0px 2px 6px rgba(0,0,0,0.2)';

    // Position the popup near the target element
    const rect = targetElement.getBoundingClientRect();
    popup.style.top = `${rect.bottom + window.scrollY}px`;
    popup.style.left = `${rect.left + window.scrollX}px`;

    // Create a title
    const title = document.createElement('div');
    title.textContent = 'Выбрать АОН:';
    title.style.fontWeight = 'bold';
    title.style.marginBottom = '5px';
    popup.appendChild(title);

    // Create a list of virtual numbers
    virtualNumbers.forEach(vn => {
        const vnItem = document.createElement('div');
        vnItem.textContent = `${vn.number} - ${vn.description}`;
        vnItem.style.cursor = 'pointer';
        vnItem.style.padding = '5px 0';
        vnItem.style.borderBottom = '1px solid #eee';

        vnItem.addEventListener('mouseover', () => {
            vnItem.style.backgroundColor = '#f0f0f0';
        });

        vnItem.addEventListener('mouseout', () => {
            vnItem.style.backgroundColor = '#fff';
        });

        vnItem.addEventListener('click', () => {
            // Send AJAX call with the selected virtual number
            sendEmployeeCall(phoneNumber, username, vn.number);
            // Remove the popup after selection
            removePopup();
        });
        popup.appendChild(vnItem);
    });

    // Add an option to cancel
    const cancelItem = document.createElement('div');
    cancelItem.textContent = 'Отмена';
    cancelItem.style.cursor = 'pointer';
    cancelItem.style.padding = '5px 0';
    cancelItem.style.color = 'red';

    cancelItem.addEventListener('click', removePopup);
    popup.appendChild(cancelItem);

    // Function to remove the popup
    function removePopup() {
        document.body.removeChild(popup);
        document.removeEventListener('click', outsideClickListener);
    }

    // Listener to detect outside clicks
    function outsideClickListener(event) {
        if (!popup.contains(event.target) && event.target !== targetElement) {
            removePopup();
        }
    }

    document.addEventListener('click', outsideClickListener);

    // Append the popup to the body
    document.body.appendChild(popup);
}

function sendEmployeeCall(phoneNumber, username, virtualNumber) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "https://admin.jgjgjhs.site/api/employee-call", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");

    // Handle the response
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status !== 200) {
                console.error('Error logging call');
            }
        }
    };

    // Prepare the payload
    const payload = JSON.stringify({
        phone: phoneNumber,
        username: username,
        virtual_number: virtualNumber
    });

    // Send the request
    xhr.send(payload);
}

// Function to extract order number from a given node and add the "Клиент" link
function processNode(node) {
    if (node.nodeType === Node.ELEMENT_NODE) {
        // Check if the node is a div with a title starting with "Заказ"
        if (node.matches('div[title^="Заказ"]')) {
            addClientLink(node);
        }

        // Also check the node's descendants in case the div is nested
        const divs = node.querySelectorAll('div[title^="Заказ"]');
        divs.forEach(div => {
            addClientLink(div);
        });
    }
}

// Function to add the "Клиент" link next to the div
function addClientLink(div) {
    const title = div.getAttribute('title');
    const orderNumberMatch = title.match(/Заказ\s*№\s*(.*)/);
    if (orderNumberMatch && orderNumberMatch[1]) {
        const orderNumber = orderNumberMatch[1].trim();

        // Check if the link already exists to prevent duplication
        if (!div.nextSibling || !div.nextSibling.classList || !div.nextSibling.classList.contains('client-link')) {
            // Create the "Клиент" link
            const clientLink = document.createElement('a');
            clientLink.href = '#';
            clientLink.textContent = 'Клиент';
            clientLink.classList.add('client-link');
            clientLink.style.marginLeft = '10px'; // Add some spacing
            clientLink.dataset.orderNumber = orderNumber; // Store order number in data attribute

            // Add click event listener
            clientLink.addEventListener('click', function(event) {
                event.preventDefault();
                fetchClientInfo(orderNumber);
            });

            // Insert the link after the div
            div.parentNode.insertBefore(clientLink, div.nextSibling);
        }
    }
}

// Function to fetch client info via AJAX
function fetchClientInfo(orderNumber) {
    const url = `https://admin.jgjgjhs.site/api/order/${orderNumber}/client`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            displayPopup(data);
        })
        .catch(error => {
            console.error('Error fetching client info:', error);
            alert('An error occurred while fetching client information.');
        });
}

// Function to display the popup with client info
function displayPopup(data) {
    // Create a popup container
    const popup = document.createElement('div');
    popup.classList.add('client-info-popup');
    popup.style.position = 'fixed';
    popup.style.top = '50%';
    popup.style.left = '50%';
    popup.style.transform = 'translate(-50%, -50%)';
    popup.style.backgroundColor = '#fff';
    popup.style.border = '1px solid #ccc';
    popup.style.padding = '20px';
    popup.style.zIndex = '10001';
    popup.style.maxWidth = '80%';
    popup.style.maxHeight = '80%';
    popup.style.overflow = 'auto';
    popup.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.5)';
    popup.style.borderRadius = '5px';

    // Prevent click events inside the popup from closing it
    popup.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    // Create a close "X" in the corner
    const closeButton = document.createElement('span');
    closeButton.textContent = '✕'; // Unicode multiplication sign
    closeButton.style.position = 'absolute';
    closeButton.style.top = '5px';
    closeButton.style.right = '8px';
    closeButton.style.cursor = 'pointer';
    closeButton.style.fontWeight = 'bold';
    closeButton.style.fontSize = '16px';
    closeButton.addEventListener('click', () => {
        document.body.removeChild(popupOverlay);
    });

    // Append the close button
    popup.appendChild(closeButton);

    // Display the data as "Key: Value"
    for (const key in data) {
        if (data.hasOwnProperty(key)) {
            if (key === 'phones') continue;
            const p = document.createElement('p');
            const b = document.createElement('b');
            const span = document.createElement('span');
            b.textContent = `${key}:`;
            span.textContent = ` ${data[key]}`;
            p.appendChild(b);
            p.appendChild(span);
            popup.appendChild(p);
        }
    }

    if (data.hasOwnProperty('phones')) {
        const phones = data.phones;
        for (const phone in phones) {
            const p = document.createElement('p');
            const span = document.createElement('span');
            span.textContent = phone.text + ' ';
            p.appendChild(span);
            const a = document.createElement('a');
            a.textContent = "Позвонить";
            a.dataset.encrypted = phone.encrypted;
            p.appendChild(a);
            popup.appendChild(p);
        }
    }

    // Create an overlay
    const popupOverlay = document.createElement('div');
    popupOverlay.style.position = 'fixed';
    popupOverlay.style.top = '0';
    popupOverlay.style.left = '0';
    popupOverlay.style.width = '100%';
    popupOverlay.style.height = '100%';
    popupOverlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    popupOverlay.style.zIndex = '10000';

    // Add click event to overlay to close the popup
    popupOverlay.addEventListener('click', () => {
        document.body.removeChild(popupOverlay);
    });

    // Append the popup to the overlay
    popupOverlay.appendChild(popup);

    // Append the overlay to the body
    document.body.appendChild(popupOverlay);
}


// Create a MutationObserver to watch for changes in the DOM
const observer = new MutationObserver((mutationsList) => {
    for (const mutation of mutationsList) {
        if (mutation.type === 'childList') {
            mutation.addedNodes.forEach(node => {
                processNode(node);
            });
        }
    }
});

// Start observing the document body for child list changes
observer.observe(document.body, {
    childList: true,
    subtree: true
});

// Initial check in case the div is already present
document.querySelectorAll('div[title^="Заказ"]').forEach(div => {
    processNode(div);
});
