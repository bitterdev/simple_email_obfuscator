document.addEventListener("DOMContentLoaded", function () {
    function obfuscateEmails() {
        const elements = document.body.getElementsByTagName('*');

        for (let element of elements) {
            if (element.nodeType === Node.TEXT_NODE) {
                let text = element.textContent;
                text = processEmails(text);
                element.textContent = text;
            }

            for (let attr of element.attributes) {
                if (attr.value) {
                    const updatedValue = processEmails(attr.value);
                    if (updatedValue !== attr.value) {
                        element.setAttribute(attr.name, updatedValue);
                    }
                }
            }

            if (element.innerHTML) {
                const updatedHTML = processEmails(element.innerHTML);
                if (updatedHTML !== element.innerHTML) {
                    element.innerHTML = updatedHTML;
                }
            }
        }
    }

    function processEmails(text) {
        const emailPattern = /([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9.-]+(?:\.[a-zA-Z]{2,})?)|([A-Za-z0-9+/=]+@[A-Za-z0-9+/=]+)/g;

        return text.replace(emailPattern, function (match, local, domain, encodedEmail) {
            if (encodedEmail) {
                return decodeBase64Email(encodedEmail);
            }

            return match;
        });
    }

    function decodeBase64Email(encodedEmail) {
        try {
            const parts = encodedEmail.split('@').map(part => {
                let cleanedPart = part.replace(/[^A-Za-z0-9+/=]/g, '');
                while (cleanedPart.length % 4 !== 0) {
                    cleanedPart += '=';
                }
                return atob(cleanedPart);
            });

            return parts.join('@');
        } catch (e) {
            return encodedEmail;
        }
    }

    obfuscateEmails();
});
