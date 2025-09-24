async function hash(algorithm, string) {
    const utf8 = new TextEncoder().encode(string);
    const hashBuffer = await crypto.subtle.digest(algorithm, utf8);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map((bytes) => bytes.toString(16).padStart(2, '0')).join('');
}

async function fetchApi(url, method = "GET", body = undefined, options = {}) {
    const request = await fetch(url, {
        method: method,
        ...(body ? {body: body} : {}),
        ...(options),
        headers: {
            ...(options.headers || {})
        }
    });
    const result = await request.text();
    try {
        return JSON.parse(result);
    } catch (e) {
        return result;
    }
}

/*--------------------*/
/*---Sandbox Script---*/
/*--------------------*/

$('#api-form').on('submit', function(e) {
    e.preventDefault();

    const endpoint = document.getElementById('endpoint').value;
    const method = document.getElementById('method').value;
    const body = document.getElementById('body').value;

    fetch(endpoint, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: method !== 'GET' ? body : null
    })
        .then(response => response.text())
        .then(data => {
            try {
                data = JSON.parse(data);
                data = JSON.stringify(data, null, 2);
            } catch (e) {}
            document.getElementById('response').textContent = data;
        })
        .catch(error => {
            document.getElementById('response').textContent = 'Error: ' + error;
        });
});
