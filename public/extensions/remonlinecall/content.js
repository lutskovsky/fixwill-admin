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

// Function to replace strings matching specific patterns
function replaceSensitiveData(node) {
    // if (node.nodeType === Node.ELEMENT_NODE && node.tagName.toLowerCase() === 'input' && node.type === 'tel') {
    //     // Set the value to '70000000000'
    //     node.value = '70000000000';
    // }

    const regex1 = /\+?encrypted-phone-[A-Za-z0-9+\/]*/g;
    // const regex2 = /\+7 \([0-9]{3}\) [0-9]{3}-[0-9]{2}-[0-9]{2}/g;

    if (node.nodeType === Node.TEXT_NODE) {
        if (!node.processed) {
            const originalText = node.textContent;
            node.textContent = originalText
                .replace(regex1, "Номер скрыт");
            node.processed = true; // Mark this node as processed
        }
    } else if (node.nodeType === Node.ELEMENT_NODE && node.childNodes) {
        node.childNodes.forEach(childNode => replaceSensitiveData(childNode));
    }
}

// Set up a MutationObserver to watch for changes in the DOM
const observer = new MutationObserver((mutationsList) => {

    // observer.disconnect();  // Disconnect to prevent infinite loop of mutations
    for (const mutation of mutationsList) {
        if (mutation.type === 'childList') {
            mutation.addedNodes.forEach(node => {
                replaceSensitiveData(node);
                if (node.nodeType === 1) { // Ensure it's an element node
                    if (node.tagName === 'A' && node.getAttribute('href') && node.getAttribute('href').startsWith('tel:')) {
                        node.addEventListener('click', handleTelLinkClick);
                    }
                    if (node.querySelectorAll) {
                        node.querySelectorAll('a[href^="tel:"]').forEach(link => {
                            link.addEventListener('click', handleTelLinkClick);
                        });
                    }

                }
            });
        } else if (mutation.type === 'characterData') {
            replaceSensitiveData(mutation.target);
        }
    }
    // observer.observe(document.body, {
    //     childList: true,
    //     subtree: true,
    //     characterData: true
    // });  // Reconnect after processing mutations
});

// Start observing the document body for changes
observer.observe(document.body, {
    childList: true,
    subtree: true,
    characterData: true
});

// Add event listeners to existing <a> elements with href="tel:"
document.querySelectorAll('a[href^="tel:"]').forEach(link => {
    link.addEventListener('click', handleTelLinkClick);
});

// Replace sensitive data in the initial DOM content
replaceSensitiveData(document.body);
