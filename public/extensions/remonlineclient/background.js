let clientId = null;

chrome.webRequest.onCompleted.addListener(
    function(details) {
        const url = new URL(details.url);
        const id = url.searchParams.get('id');
        if (id) {
            clientId = id;
            console.log("ClientId saved:", clientId);
        }
    },
    { urls: ["https://web.remonline.app/app/settings/get-client*"] }
);

chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
    if (message.type === 'GET_CLIENT_ID') {
        sendResponse({ clientId: clientId });
    }
});
