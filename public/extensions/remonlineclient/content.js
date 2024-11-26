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
            clientLink.href = `${hostUrl}/client/order/${orderNumber}`;
            clientLink.target = "_blank";
            clientLink.textContent = 'Клиент';
            clientLink.classList.add('client-link');
            clientLink.style.marginLeft = '10px'; // Add some spacing

            //
            // // Add click event listener
            // clientLink.addEventListener('click', function(event) {
            //     event.preventDefault();
            //     fetchClientInfo(orderNumber);
            // });

            // Insert the link after the div
            div.parentNode.insertBefore(clientLink, div.nextSibling);
        }
    }
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
