function saveUsernameOnSubmit() {
    const usernameField = document.querySelector('#login');
    const loginForm = document.querySelector('form'); // Assuming there's only one form on the page

    if (usernameField && loginForm) {
        // Save username to local storage on form submission
        loginForm.addEventListener('submit', () => {
            const username = usernameField.value;
            chrome.storage.local.set({ savedUsername: username }).then(() => {
                console.log("Value is set");
            });
        });
    }
}

// Create a MutationObserver to detect changes in the DOM
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
            saveUsernameOnSubmit();
        }
    });
});

// Start observing the document for changes
observer.observe(document.body, {
    childList: true,
    subtree: true
});

// Try to save username on submit initially in case the elements are already loaded
saveUsernameOnSubmit();
