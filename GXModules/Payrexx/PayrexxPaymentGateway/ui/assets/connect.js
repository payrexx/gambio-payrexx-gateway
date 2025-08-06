const connectButton = document.getElementById('connect-platform-button');
let connectingStarted = false;

connectButton.addEventListener('click', (e) => {
    e.preventDefault();
    connectingStarted = true;
    const platformElement = document.getElementById('platform-select');
    const platformUrl = platformElement.value;

    let popupWindow = createPopupWindow(platformUrl);
    connectButton.disabled = true;


    let popupCheck;
    popupCheck = setInterval(() => {
        if (popupWindow.closed) {
            popupWindow = null;
            connectButton.disabled = false;
            clearInterval(popupCheck);
            if (connectingStarted) {
                createMessageElement('Connecting cancelled', 'warning');
            }
        }
    }, 500);

});

window.addEventListener('message', (event) => {
    if (!event.data || !event.data.instance) {
        return;
    }

    connectingStarted = false;

    const instanceName = event.data.instance.name;
    const apiKey = event.data.instance.apikey;

    createMessageElement('Credentials imported successfully.', 'success');

    document.getElementById('instance_name').value = instanceName;
    document.getElementById('api_key').value = apiKey;
    document.getElementById('payrexx-settings-form').submit();
})

const createPopupWindow = (platformUrl) => {
    const popupWidth = 900;
    const popupHeight = 900;

    // Get the parent window's size and position
    const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
    const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;

    const width = window.innerWidth
        ? window.innerWidth
        : document.documentElement.clientWidth
            ? document.documentElement.clientWidth
            : screen.width;

    const height = window.innerHeight
        ? window.innerHeight
        : document.documentElement.clientHeight
            ? document.documentElement.clientHeight
            : screen.height;

    const left = dualScreenLeft + (width - popupWidth) / 2;
    const top = dualScreenTop + (height - popupHeight) / 2;

    const params = `width=${popupWidth},height=${popupHeight},top=${top},left=${left},resizable=no,scrollbars=yes`
    const url = `https://login.${platformUrl}?action=connect&plugin_id=30`;
    return window.open(url, 'Connect Payrexx', params);
}

const createMessageElement = (message, type = 'success') => {
    const messageStack = document.getElementsByClassName('message-stack')[0] || null;

    if (messageStack == null) {
        console.error(message);
        return;
    }

    const element = document.createElement('div');
    element.innerText = message;
    element.classList.add('alert', `alert-${type}`);
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'close';
    closeButton.setAttribute('data-dismiss', 'alert');
    closeButton.innerHTML = '×'
    element.appendChild(closeButton);
    messageStack.appendChild(element);
}